<?php
namespace System\Lib;

class Model
{
    //属性必须在这里声明
    protected $table;
    protected $dates=array('created_at');
    protected $fields = array();
    protected $attributes = array();
    protected $cols;
    protected $dbfix;
    protected $primaryKey = 'id';
    public $is_exist = false;

    public function __construct()
    {
        $this->dbfix = DB::dbfix();
    }

    public function __get($key)
    {
        if (isset($this->attributes[$key])) {
            $val= $this->attributes[$key];
        } else {
            if(isset($this->cols->$key)){
                $val= $this->cols->$key;
            }else{
                $val=null;
            }
        }
        if(in_array($key,$this->dates)){
            return date('Y-m-d H:i:s',$val);
        }else{
            return $val;
        }
    }

    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    public function __unset($key)
    {
        unset($this->attributes[$key]);
    }

    public function filterFields($post, $fields = array())//过滤字段
    {
        if (empty($fields)) {
            $fields = $this->fields;
        }
        if (!is_array($post)) {
            return array();
        }
        foreach ($post as $i => $v) {
            if (!in_array($i, $fields)) {
                unset($post[$i]);
            }
        }
        return $post;
    }
    
    /**
     * @param null int array $id
     * @return mixed
     */
    public function delete($id=null)
    {
        if($id==null){
            $primaryKey=$this->primaryKey;
            return DB::table($this->table)->where($this->primaryKey . "=?")->bindValues($this->$primaryKey)->delete();
        }else{
            if(is_array($id)){
                return DB::table($this->table)->where($id)->delete();
            }else{
                return DB::table($this->table)->where($this->primaryKey . "=?")->bindValues($id)->delete();
            }
        }
    }

    public function hasOne($class, $foreign_key, $local_key = 'id')
    {
        return app($class)->where("{$foreign_key}='{$this->$local_key}'")->first();
    }

    public function hasMany($class, $foreign_key, $local_key = 'id',$where='',$orderby='')
    {
        $whe="{$foreign_key}='{$this->$local_key}'";
        if($where!=''){
            $whe.=" and ".$where;
        }
        $order=$foreign_key;
        if($orderby!=''){
            $order=$orderby;
        }
        return app($class)->where($whe)->orderBy($order)->get();
    }

    //获取联动值
    public function getLinkPageName($code, $codeKey)
    {
        $result = app('\App\Model\LinkPage')->getLinkPage();
        return $result[$code][$codeKey];
    }

///////////////////////////////////////////////////////////

    public function find($id)
    {
        $this->attributes[$this->primaryKey] = $id;
        return $this->where($this->primaryKey . "=?")->bindValues($id)->first();
    }

    public function findOrFail($id)
    {
        $obj = $this->find($id);
        if (empty($obj->cols)) {
            die('find Fail !!!');
        }
        return $obj;
    }

    private function setObj($o)
    {
        if(empty($o)){
            $this->attributes=array();
            $this->cols =null;
            $this->is_exist = false;
            return $this;
        }else{
            $obj = clone $this;
            $id = $obj->primaryKey;
            $obj->attributes[$obj->primaryKey] = $o->$id;
            $obj->is_exist = true;
            $obj->cols = $o;
            return $obj;
        }
    }

    /**
     * 获取一个对象
     * @return $this
     */
    public function first()
    {
        $obj = DB::table($this->table)->row(\PDO::FETCH_OBJ);
        return $this->setObj($obj);
    }

    /**
     * 返回一个数组，每个元素是一个对象
     * @return array
     */
    public function get()
    {
        $result = DB::table($this->table)->all(\PDO::FETCH_OBJ);
        foreach ($result as $i => $v) {
            $result[$i] = $this->setObj($v);
        }
        return $result;
    }

    public function pager($page = 1, $pageSize = 10)
    {
        $result = DB::table($this->table)->page($page, $pageSize, \PDO::FETCH_OBJ);
        foreach ($result['list'] as $i => $v) {
            $result['list'][$i] = $this->setObj($v);
        }
        return array(
            'list' => $result['list'],
            'total' => $result['total'],
            'page' => $result['page']
        );
    }

    public function save($returnId=false)
    {
        if ($this->is_exist) {
            $primaryKey = $this->primaryKey;
            $id = $this->$primaryKey;
            unset($this->attributes[$this->primaryKey]);
            return DB::table($this->table)->where("{$primaryKey}=?")->bindValues($id)->limit('1')->update($this->attributes);
        } else {
            $this->attributes['created_at']=time();
            if($returnId){
                return DB::table($this->table)->insertGetId($this->attributes);
            }else{
                return DB::table($this->table)->insert($this->attributes);
            }
        }
    }
///////以下DB类方法/////////////////////////////////////////////////////////////////////////////////
    public function update($array)
    {
        return DB::table($this->table)->update($array);
    }
    public function insert($array)
    {
        return DB::table($this->table)->insert($array);
    }
    public function insertGetId($array)
    {
        return DB::table($this->table)->insertGetId($array);
    }
    //取一行中一列的值
    public function value($col = 'id', $type = 'int|float')
    {
        return DB::table($this->table)->value($col, $type);
    }
    
    public function lists($col, $key = null)
    {
        return DB::table($this->table)->lists($col,$key);
    }

    public function select($str)
    {
        DB::select($str);
        return $this;
    }

    public function distinct()
    {
        DB::distinct();
        return $this;
    }

    /**
     * @param array|string $str
     * @return $this
     */
    public function where($str)
    {
        DB::where($str);
        return $this;
    }

    public function orderBy($str)
    {
        DB::orderBy($str);
        return $this;
    }

    public function groupBy($str)
    {
        DB::groupBy($str);
        return $this;
    }

    public function having($str)
    {
        DB::having($str);
        return $this;
    }

    public function limit($str)
    {
        DB::limit($str);
        return $this;
    }

    public function bindValues($values = array())
    {
        DB::bindValues($values);
        return $this;
    }
}