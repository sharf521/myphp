<?php
if (!function_exists('myErrorHandler')) {
    function myErrorHandler($errno, $errstr, $errfile, $errline)
    {
        if ($errno == E_NOTICE) {
            return true;
        }
        $file_path = ROOT . "/public/data/logs/";
        if (!is_dir($file_path)) {
            mkdir($file_path, 0777, true);
        }
        $filename = $file_path . date("Ym") . ".log";
        $handler = null;
        if (($handler = fopen($filename, 'ab+')) !== false) {
            fwrite($handler, date('r') . "\t[$errno]$errstr\t$errfile\t$errline\n");
            fclose($handler);
        }
    }
    set_error_handler('myErrorHandler');
}

if(!function_exists('myExceptionHandler')){
    function myExceptionHandler($e){
        $error= "<b>Exception：</b>" . $e->getMessage();
        //redirect()->back()->with('error',$error);
        echo $error;
        exit;
    }
    set_exception_handler('myExceptionHandler');
}

if (!function_exists('url')) {
    function url($path)
    {
        $_str=strtolower(substr($path,0,8));
        if (substr($_str, 0, 1) != '/' && $_str!='https://' && substr($_str,0,7)!='http://') {
            $path = application('base_url') . $path;
        }
        return $path;
    }
}

if (!function_exists('application')) {
    function application($param)
    {
        return \System\Lib\Application::$$param;
    }
}


if (!function_exists('redirect')) {
    /**
     * Get an instance of the redirector.
     *
     * @param  string|null $to
     * @return  \System\Lib\Redirect;
     */
    function redirect($to = null)
    {
        return new \System\Lib\Redirect($to);
    }
}

if (!function_exists('session')) {
    /**
     * @param string $name
     * @return \System\Lib\Session
     */
    function session($name=null)
    {
        $session=app('\System\Lib\Session');
        if($name===null){
            return $session;
        }
        else{
            return $session->get($name);
        }
    }
}
if (!function_exists('app')) {
    /**
     * @param $className
     * @return mixed
     */
//    function app($className)
//    {
//        if (file_exists(ROOT . '/app/Model/' . ucfirst($className) . '.php')) {
//            $className='\\app\\Model\\' . ucfirst($className);
//        }
//        return \System\Lib\App::getInstance($className);
//    }

    function app($className)
    {
        if (file_exists(ROOT . '/app/Model/' . ucfirst($className) . '.php')) {
            $className='\\App\\Model\\' . ucfirst($className);
        }
        return \System\Lib\Container::getInstance($className);
    }
}

if (!function_exists('_token')) {
    function _token()
    {
        $token=session('_token');
        if(empty($token)){
            $token = md5(time());
        }
        session()->set('_token', $token);
        return $token;
    }
}