<?php

namespace System\Lib;


class Container
{
    private static $container=array();
    public static function getInstance($key)
    {
        if (!isset(self::$container[$key])) {
            self::$container[$key] = new $key;
        }
        return self::$container[$key];
    }
}