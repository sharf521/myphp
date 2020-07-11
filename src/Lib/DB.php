<?php

namespace System\Lib;

/**
 * Class DB
 * @package System\Lib;
 *
 * 添加IDE提示
 *
 * @method static beginTransaction()    开始事务
 * @method static commit()    提交事务
 * @method static rollBack()  事务回滚
 *
 */

class DB
{
    //实例数组
    private static $instance = array();
    private static $config = array();

    /**
     * @param array $config
     * @return \System\Lib\DbConnection
     */
    public static function instance($config = array())
    {
        if ($config == array()) {
            if (self::$config != array()) {
                $config = self::$config;
            } else {
                self::$config = DB_CONFIG;
                $config       = self::$config;
            }
        } elseif (empty(self::$config) || $config['default']) {
            //不改变config，再次使用mode时 不需要切换config.
            self::$config = $config;
        }
        $config_name = $config['host'] . $config['port'] . $config['dbname'];
        if (!isset(self::$instance[$config_name])) {
            self::$instance[$config_name] = new DbConnection($config['host'], $config['port'], $config['user'], $config['password'], $config['dbname'], $config['charset'], $config['dbfix']);
        }
        return self::$instance[$config_name];
    }

    public static function dbfix()
    {
        return self::$config['dbfix'];
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

/*

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
            DB::beginTransaction();
            $this->calFbbDo();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            echo "Failed: " . $e->getMessage();
            return false;
        }

*/