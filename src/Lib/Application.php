<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/20
 * Time: 20:46
 */

namespace System\Lib;


use Pimple\Container;

/**
 * Class Application
 * @package System\Lib
 * @property \Pimple\Container $container
 */
class Application
{
    private static $container=null;
    public static function getInstance($key)
    {
        if(self::$container==null){
            self::$container=new Container();
        }
//        self::$container[$key]=function($c){
//            $app=new app();
//            return $app;
//        };
        if (!isset(self::$container[$key])) {
            self::$container[$key] = new $key;
        }
        return self::$container[$key];
    }

    public static function start($control,$method='index')
    {
        $class=new $control();
        if (!method_exists($class, $method)) {
            $method = 'error';
        }
        $rMethod = new \ReflectionMethod($control, $method);
        $params = $rMethod->getParameters();
        $dependencies = array();
        foreach ($params as $param) {
            if ($param->getClass()) {
                $_name = $param->getClass()->name;
                array_push($dependencies, new $_name());
            } elseif ($param->isDefaultValueAvailable()) {
                array_push($dependencies, $param->getDefaultValue());
            } else {
                array_push($dependencies, null);
            }
        }
        return call_user_func_array(array($class, $method), $dependencies);
    }
}