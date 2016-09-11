<?php

namespace System\Lib;

class DB
{
    //实例数组
    protected static $instance = array();
    protected static $instance_name;
    /**
     * @param null $config_name
     * @return \System\Lib\DbConnection
     */
    public static function instance($config_name = null)
    {
        if ($config_name == null) {
            $config_name = self::$instance_name;
        }else{
            self::$instance_name=$config_name;
        }
        if (!isset(\App\Config::$$config_name)) {
            echo "Config::$config_name not set\n";
        }
        if (empty(self::$instance[$config_name])) {
            $config = \App\Config::$$config_name;
            self::$instance[$config_name] = new DbConnection($config['host'], $config['port'], $config['user'], $config['password'], $config['dbname'], $config['charset'], $config['dbfix']);
        }
        return self::$instance[$config_name];
    }

    public static function dbfix()
    {
        $config_name=self::$instance_name;
        $config = \App\Config::$$config_name;
        return $config['dbfix'];
    }

    /**
     * @param $table
     * @param null $connection
     * @return DbConnection
     * @throws Exception
     */
    public static function table($table, $connection = null)
    {
        return self::instance($connection)->table($table);
    }

    /**
     * @param $method
     * @param $parameters
     * @return DbConnection
     * @throws Exception
     */
    public static function __callStatic($method, $parameters)
    {
        return call_user_func_array(array(self::instance(), $method), $parameters);
    }

    //关闭数据库实例
    public static function close($config_name)
    {
        if (isset(self::$instance[$config_name])) {
            self::$instance[$config_name]->closeConnection();
            self::$instance[$config_name] = null;
        }
    }

    //关闭所有数据库实例
    public static function closeAll()
    {
        foreach (self::$instance as $connection) {
            $connection->closeConnection();
        }
        self::$instance = array();
    }
}

class DbConnection
{
    protected $pdo;
    protected $dbfix;
    protected $sQuery;
    protected $join = array();
    protected $bindValues = array();
    protected $select = '';
    protected $distinct = '';
    protected $table = '';
    protected $where = '';
    protected $orderBy = '';
    protected $groupBy = '';
    protected $having = '';
    protected $limit = '';

    public function __construct($host, $port, $user, $password, $db_name, $charset = 'utf8', $dbfix = '')
    {
        $this->settings = array(
            'host' => $host,
            'port' => $port,
            'user' => $user,
            'password' => $password,
            'dbname' => $db_name,
            'charset' => $charset
        );
        $this->dbfix = $dbfix;
        $this->connect();
    }

    //创建pdo实例
    protected function connect()
    {
        try {
            $dsn = 'mysql:dbname=' . $this->settings["dbname"] . ';host=' . $this->settings["host"] . ';port=' . $this->settings['port'];
            $this->pdo = new \PDO($dsn, $this->settings["user"], $this->settings["password"], array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . (!empty($this->settings['charset']) ? $this->settings['charset'] : 'utf8')));
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        } catch (\PDOException $e) {
            //$this->error_msg($e->getMessage());
            die('数据库连接失败！');
        }
    }

    //关闭连接
    public function closeConnection()
    {
        $this->pdo = null;
    }

    public function query($query, $params = null)
    {
        if ($params == null) {
            $params = $this->bindValues;
        }
//        echo $query . '<br>';
//        print_r($params);
//        echo '<br>';
        $this->sQuery = $this->pdo->prepare($query);
        if (is_array($params)) {
            foreach ($params as $k => &$v) {
                if (is_string($k)) {
                    $this->sQuery->bindParam(':' . $k, $v);
                } else {
                    $this->sQuery->bindParam($k + 1, $v);
                }
            }
        }
        $this->reset();
        return $this->sQuery->execute();
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

    public function get_one($sql, $param = null,$mode=\PDO::FETCH_ASSOC)
    {
        $this->query($sql, $param);
        $this->sQuery->setFetchMode($mode);
        $result = $this->sQuery->fetch();
        if($result){
            return $result;
        }else{
            return array();
        }
    }

    public function get_all($sql, $param = null,$mode=\PDO::FETCH_ASSOC)
    {
        $this->query($sql, $param);
        $this->sQuery->setFetchMode($mode);
        $result = $this->sQuery->fetchAll();
        if($result){
            return $result;
        }else{
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

    function error_msg($msg)
    {
        $mysql_dir = 'data';
        $dtime = date("Y-m-d", time());
        $ip = ip();
        $file = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"];
        if (!file_exists($mysql_dir . "/mysql_error")) {
            mkdir($mysql_dir . "/mysql_error", 0777);
        }
        $fp = @fopen($mysql_dir . "/mysql_error/" . $dtime . ".log", "a+");
        $time = date("H:i:s");
        //debug_print_backtrace();
        $str = "{time:$time}\t{ip:" . $ip . "}\t{error:" . $msg . "}\t{file:" . $file . "}\t\r\n";
        @fputs($fp, $str);
        @fclose($fp);
        echo $str;
        return false;
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
        $this->distinct = '';
        $this->select = '';
        $this->table = '';
        $this->join = array();
        $this->bindValues = array();
        $this->where = '';
        $this->groupBy = '';
        $this->having = '';
        $this->orderBy = '';
        $this->limit = '';
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
            . $this->limit;
       // echo $sql;
        return $sql;
    }

    public function page($page = 1, $pageSize = 10,$mode=\PDO::FETCH_ASSOC)
    {
        $sql = $this->buildSelect();
        $pageSql = "SELECT {$this->distinct} count(1) as num FROM {$this->table}"
            . $this->buildJoin()
            . $this->where
            . $this->groupBy
            . $this->having;
//        echo $pageSql . '<br>';
        $row = $this->get_one($pageSql);
        $total = $row['num'];
        $pageSize = empty($pageSize)?10:(int)$pageSize;
        $page=(int)$page;
        if ($page > 0) {
            $index = $pageSize * ($page - 1);
        } else {
            $index = 0;
            $page = 1;
        }
        if ($index > $total) {
            $index = 0;
            $page = 1;
        }
        $sql .= " limit {$index}, {$pageSize}";
 //       echo $sql;
        $list = $this->get_all($sql,null,$mode);
        global $pager;
        $pager->page = $page;
        $pager->epage = $pageSize;
        $pager->total = $total;
        return array(
            'list' => $list,
            'total' => $total,
            'page' => $pager->show()
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

    public function distinct()
    {
        $this->distinct = 'distinct';
        return $this;
    }

    /**
     * @param array|string $where
     * @return $this
     */
    public function where($where)
    {
        if(is_array($where)){
            $str=" 1=1";
            $params=array();
            foreach($where as $field=>$v){
                $str.=" and {$field}=:{$field}";
                $params["{$field}"]=$v;
            }
            $this->where=' where ' . $str;
            $this->bindValues($params);
        }else{
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

    public function bindValues($values = array())
    {
        //$this->bindValues = $arr;
        if(is_array($values)){
            foreach ($values as $key => $val) {
                $this->bindValues[$key]=$val;
            }
        }else{
            array_push($this->bindValues,$values);
        }
        return $this;
    }

    public function getSql()
    {
        return $this->buildSelect();
    }

    //取一行
    public function row($mode=\PDO::FETCH_ASSOC)
    {
        $sql = $this->buildSelect() . " limit 1";
        return $this->get_one($sql,null,$mode);
    }

    //取多行
    public function all($mode=\PDO::FETCH_ASSOC)
    {
        $sql = $this->buildSelect();
        //echo $sql;
        return $this->get_all($sql,null,$mode);
    }

    //取一行中一列的值
    public function value($col, $type = 'int|float')
    {
        $this->select = $col;
        $row = $this->row();
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
        $sql = $this->buildSelect();
        $result = $this->get_all($sql);
        $arr = array();
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
        return $this->query($sql);
    }

    public function update($data = array())
    {
        $_sql = array();
        foreach ($data as $key => $value) {
            $_sql[] = "`$key`='$value'";
        }
        $value = implode(',', $_sql);
        $sql = "UPDATE " . $this->table . " SET $value " . $this->where . $this->limit;
//        echo $sql;
        return $this->query($sql);
    }

    public function insert($data = array())
    {
        $field = $value = '';
        foreach ($data as $key => $val) {
            $field .= "`$key`,";
            $value .= "'$val',";
        }
        $field = substr($field, 0, -1);
        $value = substr($value, 0, -1);
        $sql = "INSERT INTO " . $this->table . " ($field) VALUES ($value)";
        return $this->query($sql);
    }

    public function insertGetId($data = array())
    {
        $this->insert($data);
        return $this->pdo->lastInsertId();
    }
}



/*
 *
 *


$mysql = DB::instance('db1');
$mysql->get_all($sql);//DB::get_all($sql);
$row = DB::table('user a')->select('a.*')
    ->leftJoin('rebate_user c', 'c.user_id=a.user_id')
    ->leftJoin('fbb b', 'b.user_id=a.user_id')
    ->limit(1)->orderBy('a.user_id desc')->where("a.user_id> ? ")->bindValues(10)->all();
print_r($row);


echo DB::table('user_test')->insert(array('user_id'=>1,'name'=>1111));
echo DB::table('user_test')->insertGetId(array('user_id'=>2,'name'=>2222));
echo DB::table('user_test')->where("id=3")->update(array('name'=>'333333'));
echo DB::table('user_test')->where("id>?")->bindValues(1)->limit(1)->update(array('name'=>'55555'));
$row=DB::table('user_test')->where('id=?')->bindValues(array(1))->row();
print_r($row);

$user_id=DB::table('user_test')->where('id=?')->bindValues(array(1))->value('user_id');
print_r($user_id);

$list=DB::table('user_test')->where('id>2')->lists('name','id');
print_r($list);


try {
            $this->mysql->beginTransaction();

,........

            $this->mysql->commit();
        } catch (Exception $e) {
            $this->mysql->rollBack();
            echo "Failed: " . $e->getMessage();
            return false;
        }
        return true;

*/