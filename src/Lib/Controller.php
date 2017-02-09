<?php
namespace System\Lib;

class Controller
{
    public $template;
    public $user_id;
    public $username;

    public function __construct()
    {
        $this->user_id = session('user_id');
        $this->username = session('username');
        $this->user_typeid = session('usertype');
        $this->dbfix = DB::dbfix();
        $this->control	=application('control');
        $this->func	=application('method');
        $this->self_url=urlencode($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
    }

    //显示模板
    public function view($tpl, $data = array())
    {
        if (!empty($data)) {
            extract($data);
        }
        $file = ROOT . '/app/themes/' . $this->template . '/' . $tpl . '.tpl.php';
        if (file_exists($file)) {
            require($file);
        } else {
            echo 'Error:no file ' . $this->template . '/' . $tpl . '.tpl.php';
        }
    }

    public function base_url($path)
    {
        return application('base_url') . $path;
    }

    public function anchor($control, $title = '', $attributes = '')
    {
        $url = $this->base_url($control);
        if ($attributes != '') {
            if (is_array($attributes)) {
                $str = '';
                foreach ($attributes as $k => $v) {
                    $str .= " {$k}=\"{$v}\"";
                }
            } else {
                $str = $attributes;
            }
        }
        return '<a href="' . $url . '" ' . $str . '>' . $title . '</a>';
    }

    public function redirect($control)
    {
        $url = $this->base_url($control);
        header("location:$url");
        exit;
    }

    public function error()
    {
        echo 'not find page';
    }

    public function __destruct()
    {
        session()->flash_remove();
    }
}