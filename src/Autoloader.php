<?php
namespace System;

class Autoloader
{
    public static function loadByNamespace($name)
    {
        $class_path = str_replace('\\', DIRECTORY_SEPARATOR, $name);
        if (strpos($name, 'System\\') === 0) {
            $class_file = __DIR__ . substr($class_path, strlen('System')) . '.php';
        } else {
            $arr=explode(DIRECTORY_SEPARATOR,$class_path);
            $arr[0]=strtolower($arr[0]);
            $class_path=implode(DIRECTORY_SEPARATOR,$arr);
            $class_file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . "$class_path.php";
        }
        //echo $class_file.'<br><br><br>';
        if (is_file($class_file)) {
            include_once $class_file;
            if (class_exists($name, false)) {
                return true;
            }
        }
        return false;
    }
}

spl_autoload_register('\System\Autoloader::loadByNamespace');