<?php
namespace System\Lib;

class Redirect
{
    private $path;
    private $is_back=false;
    public function __construct($path='')
    {
        $this->path=$path;
        return $this;
    }
    /**
     * @return \System\Lib\Redirect
     */
    public function back()
    {
        $this->is_back=true;
        return $this;
    }

    /**
     *
     * @param  string|array  $key
     * @param  mixed  $value
     * @return $this
     */
    public function with($key, $value = null)
    {
        $key = is_array($key) ? $key : array($key => $value);
        foreach ($key as $k => $v) {
            session()->flash($k,$v);
        }
    }
    
    public function __destruct()
    {
        if($this->is_back){
            echo '<script>history.go(-1);</script>';
            exit;
        }elseif (strtolower(substr($this->path, 0, 7))=='http://' || strtolower(substr($this->path, 0, 8))=='https://'){
            $url = $this->path;
        }else{
            $url = url($this->path);
        }
        header("location:$url");
        exit;
    }
}
