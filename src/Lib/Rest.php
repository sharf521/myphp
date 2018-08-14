<?php

namespace System\Lib;

class Rest
{
    private static $method_type = array('get', 'post', 'put', 'delete');

    public static function start()
    {
        $request_method = strtolower($_SERVER['REQUEST_METHOD']);
        if (!in_array($request_method, self::$method_type)) {
            echo 'request method error';
            exit;
        }
        $request = app('\System\Lib\Request');
        $control = ($request->get(1) != '') ? $request->get(1) : 'index';
        $method  = ($request->get(2) != '') ? $request->get(2) : 'index';
        if (file_exists(ROOT . '/app/Logic/' . ucfirst($control) . 'Logic.php')) {
            $_classpath = "\\App\\Logic\\" . ucfirst($control) . "Logic";
            //$method     = $request_method . ucfirst($method);
        } else {
            $_classpath = '\App\Logic\Logic';
            //$method     = $request_method . ucfirst($control);
            $method     = $control;
        }
        $class = new $_classpath();
        return self::starting($class, $method);
    }

    private static function starting($class, $method = 'index')
    {
        if (!method_exists($class, $method)) {
            $method = 'error';
        }
        return call_user_func_array(array($class, $method), array());
    }
}