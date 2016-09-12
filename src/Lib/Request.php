<?php
namespace System\Lib;

class Request
{
    function __construct()
    {
        //index.php/class/func
        $_path=$_SERVER['PATH_INFO'];
        $arr=explode("/",trim($_path,'/'));
        //index.php/class/func/a/1/b/2  --> $_GET[a]=1 $_GET[b]=2
        foreach ($arr as $i => $v) {
            $v = strip_tags(trim($v));
            $_GET[$i] = $v;
            //index.php/class/func/a/1/b/2
            //a和b位置 不能为数字
            if ($i > 1 && $i % 2 == 0 && !is_numeric($v)) {
                $_GET[$arr[$i]] =$this->safe_str($arr[$i + 1]);
            }
        }
    }
    public function get($key,$safe = true)
    {
        return $this->safe_str($_GET[$key],$safe);
    }
    public function post($key,$safe = true){
        return $this->safe_str($_POST[$key],$safe);
    }

    public function __get($name)
    {
        return $this->safe_str($_REQUEST[$name]);
    }

    private function safe_str($str, $safe = true)
    {
        if (is_array($str)) {
            foreach ($str as $key => $value) {
                $str[$key] = $this->safe_str($value,$safe);
            }
        } else {
            if ($safe) {
                $str = strip_tags(trim($str));
            }
            if (!get_magic_quotes_gpc()) {
                $str = addslashes($str);
            }
        }
        return $str;
    }

    public function checkToken()
    {
        if($this->post('_token')!=session('_token')){
            echo 'token error,请重试！';
            exit;
        }
    }
}