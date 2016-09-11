<?php
namespace System\Lib;

class Controller
{
    public $base_url;
    public $template;
    public $user_id;
    public $username;

    public function __construct()
    {
        global $_G;
        $this->base_url = '/index.php/';
        $this->control	=$_G['class'];
        $this->func		=$_G['func'];
        $this->user_id = session('user_id');
        $this->username = session('username');
        $this->user_typeid = session('usertype');
        $this->dbfix = DB::dbfix();
        $_G['Controller'] = $this;
    }

    //显示模板
    public function view($tpl, $data = array())
    {
        global $_G;//模板要用
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

    public function base_url($control = '')
    {
        return $this->base_url . $control;
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

