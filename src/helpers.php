<?php
if (!function_exists('myErrorHandler')) {
    function myErrorHandler($errNo, $errStr, $errFile, $errLine)
    {
        if ($errNo == E_NOTICE) {
            return true;
        }
        \System\Lib\Log::log("error", "[{$errNo}]{$errStr}\t{$errFile}\t{$errLine}\n");
    }

    set_error_handler('myErrorHandler');
}

if (!function_exists('myShutdownFunction')) {
    function myShutdownFunction()
    {
        if ($error = error_get_last()) {
            \System\Lib\Log::log("myShutdown", "Type:{$error['type']}\t{$error['message']}\t{$error['file']}\t{$error['line']}\n");
        }
    }

    register_shutdown_function('myShutdownFunction');
}

if (!function_exists('myExceptionHandler')) {
    function myExceptionHandler($e)
    {
        $data = array(
            'return_code' => 'fail',
            'return_msg'  => $e->getMessage()
        );
        echo json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        $file  = (new \System\Lib\Request())->url();
        $error = $e->getFile() . " Line " . $e->getLine() . " " . $e->getMessage();
        \System\Lib\Log::log("exception", "url:{$file} \t[{$error}\n");
    }

    set_exception_handler('myExceptionHandler');
}

if (!function_exists('url')) {
    function url($path = '')
    {
        $_str = strtolower(substr($path, 0, 8));
        if ($_str == 'https://' || substr($_str, 0, 7) == 'http://') {
            return $path;
        }
        if (substr($path, 0, 1) == '/') {
            if (\System\Lib\Application::$isRewrite == false) {
                $path = '/' . basename($_SERVER['SCRIPT_NAME']) . $path;
            }
        } else {
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
    function session($name = null)
    {
        $session = app('\System\Lib\Session');
        if ($name === null) {
            return $session;
        } else {
            return $session->get($name);
        }
    }
}

if (!function_exists('cookie')) {
    /**
     * @param string $name
     * @return \System\Lib\Cookie
     */
    function cookie($name = null)
    {
        $cookie = app('\System\Lib\Cookie');
        if ($name === null) {
            return $cookie;
        } else {
            return $cookie->get($name);
        }
    }
}
if (!function_exists('app')) {
    /**
     * @param $className
     * @return mixed
     */
    function app($className)
    {
        if (file_exists(ROOT . '/app/Model/' . ucfirst($className) . '.php')) {
            $className = '\\App\\Model\\' . ucfirst($className);
        }
        return \System\Lib\Container::getInstance($className);
    }
}

if (!function_exists('_token')) {
    function _token()
    {
        $token = session('_token');
        if (empty($token)) {
            $token = md5(time());
        }
        session()->set('_token', $token);
        return $token;
    }
}