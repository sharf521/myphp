<?php

namespace System\Lib;

class DbConnection
{
    /**
     * @var \PDO
     */
    private $pdo = null;
    private $dbfix='';
    /**
     * @var \PDOStatement
     */
    private $sQuery;
    private $join = [];
    private $bindValues = [];
    private $expValues=[];
    private $select = '';
    private $distinct = '';
    private $table = '';
    private $where = '';
    private $orderBy = '';
    private $groupBy = '';
    private $having = '';
    private $limit = '';
    private $lockForUpdate='';
    private $debug = [];
    private $settings=[];

    public function __construct($host, $port, $user, $password, $db_name, $charset = 'utf8', $dbfix = '')
    {
        $this->settings = [
            'host'     => $host,
            'port'     => $port,
            'user'     => $user,
            'password' => $password,
            'dbname'   => $db_name,
            'charset'  => $charset
        ];
        $this->dbfix    = $dbfix;
        $this->connect();
    }

    //创建pdo实例
    protected function connect()
    {
        try {
            $dsn       = 'mysql:dbname=' . $this->settings["dbname"] . ';host=' . $this->settings["host"] . ';port=' . $this->settings['port'];
            $this->pdo = new \PDO($dsn, $this->settings["user"], $this->settings["password"], array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . (!empty($this->settings['charset']) ? $this->settings['charset'] : 'utf8')));
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        } catch (\PDOException $e) {
            $this->error_msg($e->getMessage());
            die('数据库连接失败！');
        }
    }

    //关闭连接
    public function closeConnection()
    {
        $this->pdo = null;
    }

    public function debug()
    {
        return $this->debug;
    }

    public function query($query, $params = null)
    {
        $tag=false;
        if ($params == null) {
            $params = $this->bindValues;
        }
        $this->debug['sql']    = $query;
        $this->debug['params'] = $params;
        try {
            $this->sQuery = $this->pdo->prepare($query);
            if (is_array($params)) {
                foreach ($params as $k => $v) {
                    $_param = is_numeric($k) ? $k + 1 : ':' . $k;
                    $this->sQuery->bindValue($_param, (string)$v);
                }
            }
            $tag=$this->sQuery->execute();
        } catch (\PDOException $e) {
            $this->error_msg("1:{$this->getRealSql($query,$params)}" .',msg:'.$e->getMessage().'，info:'.json_encode($e->errorInfo));
            if ($e->errorInfo[1] == 2006 || $e->errorInfo[1] == 2013) {
                $this->closeConnection();
                $this->connect();
                try{
                    $this->sQuery = $this->pdo->prepare($query);
                    if (is_array($params)) {
                        foreach ($params as $k => $v) {
                            $_param = is_numeric($k) ? $k + 1 : ':' . $k;
                            $this->sQuery->bindValue($_param, (string)$v);
                        }
                    }
                    $tag=$this->sQuery->execute();
                }catch (\PDOException $e){
                    $this->error_msg("2:{$this->getRealSql($query,$params)}" .',msg:'.$e->getMessage().'，info:'.json_encode($e->errorInfo));
                    $this->rollBack();
                    throw $e;
                }
            }else{
                $this->error_msg("3:{$this->getRealSql($query,$params)}" .',msg:'.$e->getMessage().'，info:'.json_encode($e->errorInfo));
                $this->rollBack();
                throw $e;
            }
        }
        $this->reset();
        return $tag;
//        $rawStatement = explode(" ", trim($query));
//        $statement = strtolower($rawStatement[0]);
//        if ($statement === 'select' || $statement === 'show') {
//            $this->sQuery->setFetchMode(\PDO::FETCH_ASSOC);
//        } elseif ($statement === 'update' || $statement === 'delete') {
//            return $this->sQuery->rowCount();
//        } elseif ($statement === 'insert') {
//            if ($this->sQuery->rowCount() > 0) {
//                return $this->pdo->lastInsertId();
//            }
//        }
    }

    private function error_msg($msg)
    {
        $file_path = ROOT . "/public/data/logs/";
        if (!is_dir($file_path)) {
            mkdir($file_path, 0777, true);
        }
        $filename = $file_path . date("Ym") . ".log";
        $fp       = fopen($filename, "a+");
        $time     = date('Y-m-d H:i:s');
        $ip       = $this->ip();
        $file     = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"];
        $str      = "time:{$time}\t ip:{$ip}}\t{error:" . $msg . "}\t file:{$file}\t\r\n";
        fputs($fp, $str);
        fclose($fp);
    }

    public function get_one($sql, $param = null, $mode = \PDO::FETCH_ASSOC)
    {
        $this->query($sql, $param);
        $this->sQuery->setFetchMode($mode);
        $result = $this->sQuery->fetch();
        if ($result) {
            return $result;
        } else {
            return array();
        }
    }

    public function get_all($sql, $param = null, $mode = \PDO::FETCH_ASSOC)
    {
        $this->query($sql, $param);
        $this->sQuery->setFetchMode($mode);
        $result = $this->sQuery->fetchAll();
        if ($result) {
            return $result;
        } else {
            return array();
        }
    }

    //开始事务
    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    //提交事务
    public function commit()
    {
        $this->pdo->commit();
    }

    //事务回滚
    public function rollBack()
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }


    public function transaction(callable $callback)
    {
        $this->beginTransaction();
        try {
            $result = null;
            if (is_callable($callback)) {
                $result = $callback($this);
            }
            $this->commit();
            return $result;
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }


    //禁止克隆
    final public function __clone()
    {

    }

    //析构函数-资源回收
    function __destruct()
    {
        $this->closeConnection();
    }

    //////////////////////////////////////
    protected function reset()
    {
        $this->distinct   = '';
        $this->select     = '';
        $this->table      = '';
        $this->join       = array();
        $this->bindValues = array();
        $this->where      = '';
        $this->groupBy    = '';
        $this->having     = '';
        $this->orderBy    = '';
        $this->limit      = '';
        $this->lockForUpdate='';
    }

    private function buildSelect()
    {
        if (empty($this->select)) {
            $this->select = '*';
        }
        $sql = "SELECT {$this->distinct} {$this->select} FROM {$this->table}"
            . $this->buildJoin()
            . $this->where
            . $this->groupBy
            . $this->having
            . $this->orderBy
            . $this->limit
            . $this->lockForUpdate;
        return $sql;
    }

    public function page($page = 1, $pageSize = 10, $mode = \PDO::FETCH_ASSOC)
    {
        $_sql  = $this->buildSelect() ;
        if ($this->groupBy != '') {
            $sql1             = "SELECT {$this->distinct} 1 FROM {$this->table}"
                . $this->buildJoin()
                . $this->where
                . $this->groupBy
                . $this->having;
            $sql              = "select count(1) as num from ($sql1) as t";
            $params           = $this->bindValues;
            $row              = $this->get_one($sql);
            $this->bindValues = $params;
            $total            = $row['num'];
        } else {
            $sql              = "SELECT {$this->distinct} count(1) as num FROM {$this->table}"
                . $this->buildJoin()
                . $this->where
                //. $this->groupBy
                . $this->having;
            $params           = $this->bindValues;
            $row              = $this->get_one($sql);
            $this->bindValues = $params;
            $total            = $row['num'];
        }
        $pageSize = empty($pageSize) ? 10 : (int)$pageSize;
        $page     = (int)$page;
        if ($page > 0) {
            $index = $pageSize * ($page - 1);
        } else {
            $index = 0;
            $page  = 1;
        }
        /*        if ($index > $total) {
                    $index = 0;
                    $page  = 1;
                }*/
        if ($total > 0) {
            $sql  = $_sql . " limit {$index}, {$pageSize}";
            $list = $this->get_all($sql, null, $mode);
        } else {
            $list = array();
        }
        $pager        = app('\System\Lib\Page');
        $pager->page  = $page;
        $pager->epage = $pageSize;
        $pager->total = $total;
        return array(
            'list'      => $list,
            'total'     => $total,
            'pageCount' => ceil($total / $pageSize),
            'pageSize'  => $pageSize,
            'page'      => $pager->show()
        );
    }

    private function buildJoin()
    {
        return implode(' ', $this->join);
    }

    public function table($table)
    {
        $this->table = $this->dbfix . $table;
        return $this;
    }

    public function select($str)
    {
        $this->select = $str;
        return $this;
    }

    public function join($table, $cond = null)
    {
        $this->joinInternal('INNER', $table, $cond);
        return $this;
    }

    public function leftJoin($table, $cond = null)
    {
        $this->joinInternal('LEFT', $table, $cond);
        return $this;
    }

    public function rightJoin($table, $cond = null)
    {
        $this->joinInternal('RIGHT', $table, $cond);
        return $this;
    }

    private function joinInternal($join, $table, $cond = null)
    {
        $table = $this->dbfix . $table;
        array_push($this->join, " {$join} JOIN {$table} ON {$cond} ");
    }

    public function distinct($columns=array())
    {
        if(is_array($columns) && count($columns)>0){
            $this->distinct = 'distinct '.implode(', ',$columns);
        }else{
            $this->distinct = 'distinct';
        }
        return $this;
    }

    /**
     * @param array|string $where
     * @return $this
     */
    public function where($where)
    {
        if (is_array($where)) {
            $str    = " 1=1";
            $params = array();
            foreach ($where as $field => $v) {
                $str                .= " and {$field}=:{$field}";
                $params["{$field}"] = $v;
            }
            $this->where = ' where ' . $str;
            $this->bindValues($params);
        } else {
            $this->where = ' where ' . $where;
        }
        return $this;
    }

    public function orderBy($str)
    {
        $this->orderBy = ' order by ' . $str;
        return $this;
    }

    public function groupBy($str)
    {
        $this->groupBy = ' group by ' . $str;
        return $this;
    }

    public function having($str)
    {
        $this->having = ' having ' . $str;
        return $this;
    }

    public function limit($str)
    {
        $this->limit = ' limit ' . $str;
        return $this;
    }

    public function lockForUpdate()
    {
        $this->lockForUpdate=' for update';
        return $this;
    }

    public function bindValues($values = array())
    {
        if (is_array($values)) {
            foreach ($values as $key => $val) {
                $this->bindValues[$key] = $val;
            }
        } else {
            $this->bindValues[]=$values;
        }
        return $this;
    }

    //更新的数据需要使用SQL函数或者其它字段
    public function exp(string $field, string $value)
    {
        $this->expValues[$field]=$value;
        return $this;
    }

    //自增一个字段的值
    public function inc($name,$step=1)
    {
        $step=floatval($step);
        $this->exp($name,"`{$name}` + {$step}");
        return $this;
    }

    //自减一个字段的值
    public function dec($name,$step=1)
    {
        $step=floatval($step);
        $this->exp($name,"`{$name}` - {$step}");
        return $this;
    }

    public function getSql()
    {
        return $this->buildSelect();
    }

    public function getRealSql($sql,$bind=[])
    {
        if(empty($bind)){
            $bind = $this->bindValues;
        }
        foreach ($bind as $key => $value) {
            $value = '\'' . addslashes($value) . '\'';
            // 判断占位符
            $sql = is_numeric($key) ?
                substr_replace($sql, $value, strpos($sql, '?'), 1) :
                substr_replace($sql, $value, strpos($sql, ':' . $key), strlen(':' . $key));
        }
        return rtrim($sql);
    }

    //取一行
    public function row($mode = \PDO::FETCH_ASSOC)
    {
        if($this->limit==''){
            $this->limit('1');//只能返回一行
        }
        $sql = $this->buildSelect();
        return $this->get_one($sql, null, $mode);
    }

    //取多行
    public function all($mode = \PDO::FETCH_ASSOC)
    {
        $sql = $this->buildSelect();
        //echo $sql;
        return $this->get_all($sql, null, $mode);
    }

    //取一行中一列的值
    public function value($col, $type = 'int|float')
    {
        $this->select = $col;
        $row          = $this->row();
        if (isset($row[$col])) {
            $v = $row[$col];
        } else {
            $v = current($row);
        }
        if ($type == 'int') {
            return (int)$v;
        } elseif ($type == 'float') {
            return (float)$v;
        } else {
            return $v;
        }
    }

    //取一列
    public function lists($col, $key = null)
    {
        $this->select = $col;
        if ($key !== null) {
            $this->select .= ',' . $key;
        }
        $sql    = $this->buildSelect();
        $result = $this->get_all($sql);
        $arr    = array();
        foreach ($result as $k => $v) {
            if ($key == null) {
                $arr[$k] = $v[$col];
            } else {
                $arr[$v[$key]] = $v[$col];
            }
        }
        return $arr;
    }

    //清空表
    public function truncate()
    {
        return $this->query('TRUNCATE TABLE ' . $this->table);
    }

    public function delete()
    {
        $sql = "DELETE FROM {$this->table}" . $this->where . $this->limit;
        $this->query($sql);
        return $this->sQuery->rowCount();
    }

    public function update(array $data = [])
    {
        $_sql =[];
        if(!empty($this->expValues)){
            foreach ($this->expValues as $key => $value) {
                $_sql[] = "`$key`={$value}";
            }
        }else{
            foreach ($data as $key => $value) {
                $_sql[] = "`$key`='".addslashes($value)."'";
            }
        }
        $value = implode(',', $_sql);
        $sql   = "UPDATE " . $this->table . " SET $value " . $this->where . $this->limit;
        $this->query($sql);
        return $this->sQuery->rowCount();
    }

    //没有绑定参数时可以使用
    public function updateForNoBind(array $data)
    {
        $_sql = array();
        foreach ($data as $key => $value) {
            $_sql[] = "`$key`=:{$key}";
        }
        $_str = implode(',', $_sql);
        $sql   = "UPDATE " . $this->table . " SET $_str " . $this->where . $this->limit;
        $this->query($sql,$data);
        return $this->sQuery->rowCount();
    }

    public function insert(array $data)
    {
        $field = $value = '';
        foreach ($data as $key => $val) {
            if ($field == '') {
                $field = "`{$key}`";
                $value = ":{$key}";
            } else {
                $field .= ",`{$key}`";
                $value .= ",:{$key}";
            }
        }
        $sql = "INSERT INTO " . $this->table . " ($field) VALUES ($value)";
        $this->query($sql, $data);
        return $this->sQuery->rowCount();
    }

    public function insertGetId(array $data)
    {
        $num=$this->insert($data);
        if($num>0){
            return $this->pdo->lastInsertId();
        }
        return 0;
    }

    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    private function ip()
    {
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip_address = $_SERVER["HTTP_CLIENT_IP"];
        } else if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip_address = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
        } else if (!empty($_SERVER["REMOTE_ADDR"])) {
            $ip_address = $_SERVER["REMOTE_ADDR"];
        } else {
            $ip_address = '';
        }
        return $ip_address;
    }
}