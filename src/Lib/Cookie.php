<?php
namespace System\Lib;

class Cookie
{
    public function set($key, $value = null,$minute=60)
    {
        $expire=time()+60*$minute;
        if (!is_array($key)) {
            $key = array($key => $value);
        }
        foreach ($key as $k => $v) {
            $_key = base64_encode($k);
            $_val=$this->DeCode(serialize($v), 'E');
            setcookie($_key,$_val,$expire);
        }
    }

    public function get($key, $default = '')
    {
        $_key = base64_encode($key);
        if(isset($_COOKIE[$_key])){
            $_val = $_COOKIE[$_key];
            $value = unserialize($this->DeCode($_val, 'D'));
        }
        if (empty($value)) {
            return $default;
        }
        return $value;
    }

    public function remove($key)
    {
        setcookie(base64_encode($key), "", time()-3600);
    }

    public function push($key, $value)
    {
        $array = $this->get($key);
        if (!is_array($array)) {
            $array = array();
        }
        array_push($array, $value);
        $this->set($key, $array);
    }

    public function debug()
    {
        $arr = array();
        foreach ($_COOKIE as $k => $v) {
            $arr[base64_decode($k)] = unserialize($this->DeCode($v, 'D'));
        }
        print_r($arr);
    }

    /**
     * @param $string
     * @param string $operation D|E
     * @param string $key
     * @return mixed|string
     */
    private function DeCode($string, $operation = 'E', $key = '')
    {
        if(empty($key)){
            $key=MyPHP_KEY;
        }
        $key = md5($key);
        $key_length = strlen($key);
        $string = $operation == 'D' ? base64_decode($string) : substr(md5($string . $key), 0, 8) . $string;

        $string_length = strlen($string);
        $rndkey = $box = array();
        $result = '';
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($key[$i % $key_length]);
            $box[$i] = $i;
        }
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ($operation == 'D') {
            if (substr($result, 0, 8) == substr(md5(substr($result, 8) . $key), 0, 8)) {
                return substr($result, 8);
            } else {
                return '';
            }
        } else {
            return str_replace('=', '', base64_encode($result));
        }
    }
}