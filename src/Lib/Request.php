<?php

namespace System\Lib;

class Request
{
    private $data = array();

    function __construct()
    {
        $_path = $_SERVER['PATH_INFO']; //index.php/class/func
        if ($_path !== 'PATH_INFO') {
            $arr        = explode("/", trim($_path, '/'));
            $this->data = $_GET;
            foreach ($arr as $i => $v) {
                $v              = strip_tags(trim($v));
                $this->data[$i] = $v;
                //index.php/class/func/a/1/b/2  a和b位置 不能为数字
//                if ($i > 1 && $i % 2 == 0 && !is_numeric($v)) {
//                    $this->data[$arr[$i]] =$this->safe_str($arr[$i + 1]);
//                }
            }
        }
    }

    public function get($key, $safe = true)
    {
        return $this->safe_str($this->data[$key], $safe);
    }

    public function post($key, $safe = true)
    {
        return $this->safe_str($_POST[$key], $safe);
    }

    public function __get($name)
    {
        return $this->safe_str($_REQUEST[$name]);
    }

    private function safe_str($str, $safe = true)
    {
        if (is_array($str)) {
            foreach ($str as $key => $value) {
                $str[$key] = $this->safe_str($value, $safe);
            }
        } else {
            if ($safe) {
                //$str = strip_tags(trim($str));
                $str = htmlspecialchars($str, ENT_QUOTES);
                $str = preg_replace('/</', '&lt;', $str);
                $str = preg_replace('/>/', '&gt;', $str);
                //$str = preg_replace('/\'/', '&#39;', $str);
                //$str = preg_replace('/"/', '&quot;', $str);
            }
            if (!get_magic_quotes_gpc()) {
                $str = addslashes($str);
            }
        }
        return $str;
    }

    public function checkToken()
    {
        if ($this->post('_token') != session('_token')) {
            echo 'token error,请重试！';
            exit;
        }
    }

    /**
     * 当前是否ssl
     * @access public
     * @return bool
     */
    public function isSsl()
    {
        $server = $_SERVER;
        if (isset($server['HTTPS']) && ('1' == $server['HTTPS'] || 'on' == strtolower($server['HTTPS']))) {
            return true;
        } elseif (isset($server['REQUEST_SCHEME']) && 'https' == $server['REQUEST_SCHEME']) {
            return true;
        } elseif (isset($server['SERVER_PORT']) && ('443' == $server['SERVER_PORT'])) {
            return true;
        } elseif (isset($server['HTTP_X_FORWARDED_PROTO']) && 'https' == $server['HTTP_X_FORWARDED_PROTO']) {
            return true;
        }
        return false;
    }

    /**
     * 当前URL地址中的scheme参数
     * @access public
     * @return string
     */
    public function scheme()
    {
        return $this->isSsl() ? 'https' : 'http';
    }

    /**
     * 设置或获取当前包含协议的域名
     * @access public
     * @return string
     */
    public function domain()
    {
        return $this->scheme() . '://' . $this->host();
    }

    /**
     * 当前请求的host
     * @access public
     * @param bool $port false 仅仅获取HOST
     * @return string
     */
    public function host($port = true)
    {
        if (isset($_SERVER['HTTP_X_REAL_HOST'])) {
            $host = $_SERVER['HTTP_X_REAL_HOST'];
        } else {
            $host = $_SERVER['HTTP_HOST'];
        }
        return false === $port && strpos($host, ':') ? strstr($host, ':', true) : $host;
    }


    private function isCLI()
    {
        return PHP_SAPI == 'cli' ? true : false;
    }

    /**
     * 设置或获取当前完整URL 包括QUERY_STRING
     * @access public
     * @param string|true $domain true 带域名获取
     * @return string
     */
    public function url($domain = true)
    {
        if ($this->isCLI()) {
            $url = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '';
        } elseif (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            $url = $_SERVER['HTTP_X_REWRITE_URL'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $url = $_SERVER['REQUEST_URI'];
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
            $url = $_SERVER['ORIG_PATH_INFO'] . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
        } else {
            $url = '';
        }
        return true === $domain ? $this->domain() . $url : $url;
    }

    /**
     * 设置或获取当前URL 不含QUERY_STRING
     * @access public
     * @param string|true $domain true 带域名获取
     * @return string
     */
    public function baseUrl($domain = true)
    {
        $str     = $this->url($domain);
        $baseUrl = strpos($str, '?') ? strstr($str, '?', true) : $str;
        return $baseUrl;
    }

    /**
     * 检测是否使用手机访问
     * @access public
     * @return bool
     */
    public function isMobile()
    {
        if (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], "wap")) {
            return true;
        } elseif (isset($_SERVER['HTTP_ACCEPT']) && strpos(strtoupper($_SERVER['HTTP_ACCEPT']), "VND.WAP.WML")) {
            return true;
        } elseif (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
            return true;
        } elseif (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $_SERVER['HTTP_USER_AGENT'])) {
            return true;
        } else {
            return false;
        }
    }
}