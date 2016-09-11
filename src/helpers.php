<?php
if (!function_exists('url')) {
    function url($path)
    {
        global $_G;
        if (substr($path, 0, 1) != '/') {
            $path = $_G['Controller']->base_url . $path;
        }
        return $path;
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
            $className='\\app\\Model\\' . ucfirst($className);
        }
        return \System\Lib\Application::getInstance($className);
    }
}

if (!function_exists('ip')) {
    function ip()
    {
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip_address = $_SERVER["HTTP_CLIENT_IP"];
        } else if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip_address = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
        } else if (!empty($_SERVER["REMOTE_ADDR"])) {
            $ip_address = $_SERVER["REMOTE_ADDR"];
        } else {
            $ip_address = '';
        }
        return $ip_address;
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

if (!function_exists('math')) {
    /**
     * @param $numA
     * @param $numB
     * @param $method
     * @param int $accuracy
     * @return float
     */
    function math($numA,$numB,$method,$accuracy=3)
    {
        if ($method == '+') {
            return floatval(bcadd($numA, $numB, $accuracy));

        } elseif ($method == '-') {
            return floatval(bcsub($numA, $numB, $accuracy));

        } elseif ($method == '*') {
            return floatval(bcmul($numA, $numB, $accuracy));

        } elseif ($method == '/') {
            return floatval(bcdiv($numA, $numB, $accuracy));
        }
    }
}

if (!function_exists('round_money')) {
    //处理小数位
    function round_money($money, $type = 1, $len = 2)
    {
        $money = (float)$money;
        if ($type == 1) {//舍
            //$pri=substr(sprintf("%.3f", $money), 0, -1);
            $_arr = explode('.', $money);
            if (isset($_arr[1])) {
                $_a = substr($_arr[1], 0, $len);
                $pri = $_arr[0] . '.' . $_a;
            } else {
                $pri = $_arr[0];
            }
        } else {
            $pri = ceil($money * pow(10, $len)) / pow(10, $len);
        }
        return $pri;
    }
}


