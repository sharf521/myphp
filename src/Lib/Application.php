<?php

namespace System\Lib;

class Application
{
    public static function start($routes=array())
    {
        $request=app('\System\Lib\Request');
        $GLOBALS['_SYSTEM']['class']=($request->get(0) != '') ? $request->get(0) : 'index';
        $GLOBALS['_SYSTEM']['func']=($request->get(1) != '') ? $request->get(1) : 'index';

        $_path='';
        foreach ($routes as $k=>$v){
            if ($GLOBALS['_SYSTEM']['class'] == $k) {
                $_path=$v;
                break;
            }
        }
        if($_path==''){
            $GLOBALS['_SYSTEM']['houtai']=app('\App\Model\System')->getCode('houtai');
            if ($GLOBALS['_SYSTEM']['class'] == $GLOBALS['_SYSTEM']['houtai']){
                $_path='Admin';
            }
        }
        if($_path==''){
            if (file_exists(ROOT . '/app/Controller/' . ucfirst($GLOBALS['_SYSTEM']['class']) . 'Controller.php')) {
                $_classpath = "\\App\\Controller\\" . ucfirst($GLOBALS['_SYSTEM']['class']) . "Controller";
                $method = $GLOBALS['_SYSTEM']['func'];
            } else {
                $_classpath='\App\Controller\IndexController';
                $method = $GLOBALS['_SYSTEM']['class'];
            }
        }else{
            $GLOBALS['_SYSTEM']['class']=($request->get(1) != '') ? $request->get(1) : 'index';
            $GLOBALS['_SYSTEM']['func']=($request->get(2) != '') ? $request->get(2) : 'index';

            if (file_exists(ROOT . '/app/Controller/'.$_path.'/' . ucfirst($GLOBALS['_SYSTEM']['class']) . 'Controller.php')) {
                $_classpath = "\\App\\Controller\\" .$_path.'\\'. ucfirst($GLOBALS['_SYSTEM']['class']) . "Controller";
                $method = $GLOBALS['_SYSTEM']['func'];
            } else {
                $_classpath = "\\App\\Controller\\" .$_path."\\IndexController";
                $method = $GLOBALS['_SYSTEM']['class'];
            }
        }
        return self::starting($_classpath,$method);
    }

    public static function starting($control,$method='index')
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