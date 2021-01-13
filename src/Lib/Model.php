<?php

namespace System\Lib;

//Model 应优先使用静态方法

class Model
{
    //属性必须在这里声明
    protected $table;
    protected $dates = array('created_at');
    protected $fields = array();
    protected $attributes = array();
    protected $cols;
    protected $dbfix = '';
    protected $primaryKey = 'id';
    protected $primaryKeyOldVal = '';//修改主键 保存原主键值
    //protected $forceDeleting=false;//设置软删除 deleted_at  forceDelete()
    public $is_exist = false;

    public function __construct()
    {

    }

    public function __get($key)
    {
        if (isset($this->attributes[$key])) {
            $val = $this->attributes[$key];
        } else {
            if (isset($this->cols->$key)) {
                $val = $this->cols->$key;
            } else {
                $val = null;
            }
        }
        if (in_array($key, $this->dates)) {
            if ($val != 0) {
                return date('Y-m-d H:i:s', $val);
            }
        } else {
            return $val;
        }
    }

    public function __set($key, $value)
    {
        if ($key == $this->primaryKey) {
            //修改主键 保存原主键值
            $this->primaryKeyOldVal = $this->attributes[$this->primaryKey];
        }
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

    public function hasOne($class, $foreign_key, $local_key = 'id')
    {
        return app($class)->where("{$foreign_key}='{$this->$local_key}'")->first();
    }

    public function hasMany($class, $foreign_key, $local_key = 'id', $where = '', $orderby = '')
    {
        $whe = "{$foreign_key}='{$this->$local_key}'";
        if ($where != '') {
            $whe .= " and " . $where;
        }
        $order = $foreign_key;
        if ($orderby != '') {
            $order = $orderby;
        }
        return app($class)->where($whe)->orderBy($order)->get();
    }

    //获取联动值 应移出
    public function getLinkPageName($code, $codeKey)
    {
        $result = app('\App\Model\LinkPage')->getLinkPage();
        if (isset($result[$code][$codeKey])) {
            return $result[$code][$codeKey];
        } else {
            return $codeKey;
        }
    }

///////////////////////////////////////////////////////////

    /**
     * @param $id
     * @return $this
     */
    public function find($id)
    {
        $this->attributes[$this->primaryKey] = $id;
        return $this->where($this->primaryKey . "=?")->bindValues($id)->first();
    }

    /**
     * @param $id
     * @return $this
     */
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
        if (empty($o)) {
            $this->attributes = array();
            $this->cols       = null;
            $this->is_exist   = false;
            return $this;
        } else {
            $obj = clone $this;
            //$obj=new static();//弃用：构造方法里调用自己 会死循环
            $id                   = $obj->primaryKey;
            $obj->attributes[$id] = $o->$id;
            $obj->is_exist        = true;
            $obj->cols            = $o;
            return $obj;
        }
    }

    /**
     * @param bool $returnArr
     * @return $this|array|Model
     */
    public function first($returnArr = false)
    {
        if ($returnArr) {
            return DB::table($this->table)->row();
        } else {
            $obj = DB::table($this->table)->row(\PDO::FETCH_OBJ);
            return $this->setObj($obj);
        }
    }

    /**
     * 返回一个数组，默认每个元素是一个对象
     * @param bool $returnArr
     * @return array
     */
    public function get($returnArr = false)
    {
        if ($returnArr) {
            return DB::table($this->table)->all();
        } else {
            $result = DB::table($this->table)->all(\PDO::FETCH_OBJ);
            foreach ($result as $i => $v) {
                $result[$i] = $this->setObj($v);
            }
            return $result;
        }
    }

    public function pager($page = 1, $pageSize = 10)
    {
        $result = DB::table($this->table)->page($page, $pageSize, \PDO::FETCH_OBJ);
        foreach ($result['list'] as $i => $v) {
            $result['list'][$i] = $this->setObj($v);
        }
        return array(
            'list'  => $result['list'],
            'total' => $result['total'],
            'page'  => $result['page']
        );
    }

    public function save($returnId = false)
    {
        $primaryKey = $this->primaryKey;
        if ($this->is_exist) {
            $new_key = 0;//新的primaryKey
            if ($this->primaryKeyOldVal != '') {
                $id      = $this->primaryKeyOldVal;
                $new_key = $this->attributes[$this->primaryKey];
            } else {
                $id = $this->$primaryKey;
                unset($this->attributes[$this->primaryKey]);
            }
            $num = DB::table($this->table)->where("{$primaryKey}=?")->bindValues($id)->limit('1')->update($this->attributes);
            if ($new_key != 0) {
                $this->$primaryKey = $new_key;
            }
            if ($num > 0) {
                $this->primaryKeyOldVal = '';
                return true;
            } else {
                return false;
            }
        } else {
            $this->attributes['created_at'] = time();
            $num                            = DB::table($this->table)->insertGetId($this->attributes);
            if ($num > 0) {
                if (array_key_exists($primaryKey, $this->attributes)) {
                    $this->$primaryKey = $this->attributes[$this->primaryKey];
                } else {
                    $this->$primaryKey = $num;
                }
                $this->is_exist = true;
            }
            return $num;
        }
    }

///////以下DB类方法////////////

    public function delete()
    {
        if ($this->is_exist) {
            $primaryKey = $this->primaryKey;
            return DB::table($this->table)->where($this->primaryKey . "=?")->bindValues($this->$primaryKey)->delete();
        } else {
            $id = func_get_arg(0);
            if (!empty($id)) {
                if (is_array($id)) {
                    return DB::table($this->table)->where($id)->delete();
                } else {
                    return DB::table($this->table)->where($this->primaryKey . "=?")->bindValues($id)->delete();
                }
            } else {
                return DB::table($this->table)->delete();
            }
        }
    }

    public function update($array)
    {
        return DB::table($this->table)->update($array);
        /*        if($this->is_exist){
                    $primaryKey=$this->primaryKey;
                    return DB::table($this->table)->where($this->primaryKey . "=?")->bindValues($this->$primaryKey)->update($array);
                }else{
                    return DB::table($this->table)->update($array);
                }*/
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
        return DB::table($this->table)->lists($col, $key);
    }

    public function select($str)
    {
        if (trim($str) != '*' && strpos($str, '(') === false) {
            //没有方法的一般查询必须加上主键, save() delete()的时候
            $arr = explode(',', $str);
            if (!in_array($this->primaryKey, $arr)) {
                $str = $this->primaryKey . ',' . $str;
            }
        }
        DB::select($str);
        return $this;
    }

    public function distinct($columns = array())
    {
        DB::distinct($columns);
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

    public function lockForUpdate()
    {
        DB::lockForUpdate();
        return $this;
    }

    public function bindValues($values = array())
    {
        DB::bindValues($values);
        return $this;
    }
}