<?php

namespace System\Lib;

class Application
{
    private static $container=array();
    public static function getInstance($key)
    {
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
        define("ACTION",$method);
        return call_user_func_array(array($class, $method), $dependencies);
    }
}