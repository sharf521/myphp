<?php
namespace System\Lib;

class Controller
{
    public $template;

    public function __construct()
    {
        $this->control	=application('control');
        $this->func	=application('method');
        $this->self_url=urlencode($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
    }

    //显示模板
    public function view($sysTplFileName, $data = array())
    {
        if (!empty($data)) {
            extract($data);
        }
        $sysFilePath = ROOT . '/app/themes/' . $this->template . '/' . $sysTplFileName . '.tpl.php';
        if (file_exists($sysFilePath)) {
            require($sysFilePath);
        } else {
            echo 'Error:no file ' . $this->template . '/' . $sysTplFileName . '.tpl.php';
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
        $file='404.php';
        if(file_exists($file)){
            echo file_get_contents($file);
        }else{
            echo '404 error not find page';
        }
    }

    public function __destruct()
    {
        session()->flash_remove();
    }
}