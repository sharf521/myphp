<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/9
 * Time: 12:32
 */

namespace System\Lib;


class Log
{
    public static function log($type='log',$data)
    {
        $path = ROOT . "/public/data/logs/";
        if (!file_exists($path)) {
            mkdir($path,0777,true);
        }
        $logFile = fopen($path.'log'.$type.'_'.date('Ym').".txt", "a+");
        if(is_array($data)){
            $data=json_encode($data);
        }
        fwrite($logFile, '【'.date('Y-m-d H:i:s').'】'.$data."\r\n");
        fclose($logFile);
    }

/*    private function ip()
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
    }*/
}