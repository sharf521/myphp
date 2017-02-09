<?php

namespace System\Lib;

class Application
{
    public static $control;
    public static $method;
    public static $base_url;
    public static function start($routes=array())
    {
        $request=app('\System\Lib\Request');
        self::$control=($request->get(0) != '') ? $request->get(0) : 'index';
        self::$method=($request->get(1) != '') ? $request->get(1) : 'index';
        $base_url="/";
        $_path='';
        foreach ($routes as $k=>$v){
            if (self::$control == $k) {
                $_path=$v;
                $base_url="/{$k}/";
                break;
            }
        }
        if($_path==''){
            if (self::$control == app('\App\Model\System')->getCode('houtai')){
                $_path='Admin';
                $base_url="/".self::$control."/";
            }
        }
        if($_path==''){
            if(file_exists(ROOT . '/app/Controller/Home') && is_dir(ROOT . '/app/Controller/Home')){
                if (file_exists(ROOT . '/app/Controller/Home/' . ucfirst(self::$control) . 'Controller.php')) {
                    $_classpath = "\\App\\Controller\\Home\\" . ucfirst(self::$control) . "Controller";
                    $method = self::$method;
                } else {
                    $_classpath='\App\Controller\Home\IndexController';
                    $method = self::$control;
                }
            }else{
                if (file_exists(ROOT . '/app/Controller/' . ucfirst(self::$control) . 'Controller.php')) {
                    $_classpath = "\\App\\Controller\\" . ucfirst(self::$control) . "Controller";
                    $method = self::$method;
                } else {
                    $_classpath='\App\Controller\IndexController';
                    $method = self::$control;
                }
            }
        }else{
            self::$control=($request->get(1) != '') ? $request->get(1) : 'index';
            self::$method=($request->get(2) != '') ? $request->get(2) : 'index';
            if (file_exists(ROOT . '/app/Controller/'.$_path.'/' . ucfirst(self::$control) . 'Controller.php')) {
                $_classpath = "\\App\\Controller\\" .$_path.'\\'. ucfirst(self::$control) . "Controller";
                $method = self::$method;
            } else {
                $_classpath = "\\App\\Controller\\" .$_path."\\IndexController";
                $method = self::$control;
            }
        }
        self::$base_url=$base_url;//构造方法执行不完整时需要
        $class=new $_classpath();
        //$class->control=self::$control;
        //$class->func=self::$method;
        return self::starting($class,$method);
    }

    public static function starting($class,$method='index')
    {
        if (!method_exists($class, $method)) {
            $method = 'error';
        }
        $rMethod = new \ReflectionMethod($class, $method);
        $params = $rMethod->getParameters();
        $dependencies = array();
        foreach ($params as $param) {
            if ($param->getClass()) {
                $_name = $param->getClass()->name;
                array_push($dependencies, app($_name));
            } elseif ($param->isDefaultValueAvailable()) {
                array_push($dependencies, $param->getDefaultValue());
            } else {
                array_push($dependencies, null);
            }
        }
        define("APP_ACTION",$method);
        return call_user_func_array(array($class, $method), $dependencies);
    }
}