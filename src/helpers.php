<?php

if (!function_exists('myErrorHandler')) {
    function myErrorHandler($errno, $errstr, $errfile, $errline)
    {
        if ($errno == E_NOTICE) {
            return true;
        }
        $file_path = ROOT . "/public/data/logs/";
        if (!is_dir($file_path)) {
            mkdir($file_path, 0777, true);
        }
        $filename = $file_path . date("Ym") . ".log";
        $handler = null;
        if (($handler = fopen($filename, 'ab+')) !== false) {
            fwrite($handler, date('r') . "\t[$errno]$errstr\t$errfile\t$errline\n");
            fclose($handler);
        }
    }
    set_error_handler('myErrorHandler');
}

if (!function_exists('url')) {
    function url($path)
    {
        if (substr($path, 0, 1) != '/') {
            $path = $GLOBALS['system']['Controller']->base_url . $path;
        }
        return $path;
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
    function session($name=null)
    {
        $session=app('\System\Lib\Session');
        if($name===null){
            return $session;
        }
        else{
            return $session->get($name);
        }
    }
}
if (!function_exists('app')) {
    /**
     * @param $className
     * @return mixed
     */
//    function app($className)
//    {
//        if (file_exists(ROOT . '/app/Model/' . ucfirst($className) . '.php')) {
//            $className='\\app\\Model\\' . ucfirst($className);
//        }
//        return \System\Lib\App::getInstance($className);
//    }

    function app($className)
    {
        if (file_exists(ROOT . '/app/Model/' . ucfirst($className) . '.php')) {
            $className='\\app\\Model\\' . ucfirst($className);
        }
        return \System\Lib\Container::getInstance($className);
    }
}

if (!function_exists('ip')) {
    function ip()
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
    }
}

if (!function_exists('_token')) {
    function _token()
    {
        $token=session('_token');
        if(empty($token)){
            $token = md5(time());
        }
        session()->set('_token', $token);
        return $token;
    }
}

if (!function_exists('math')) {
    /**
     * @param $numA
     * @param $numB
     * @param $method
     * @param int $accuracy
     * @return float
     */
    function math($numA,$numB,$method,$accuracy=3)
    {
        if ($method == '+') {
            return floatval(bcadd($numA, $numB, $accuracy));

        } elseif ($method == '-') {
            return floatval(bcsub($numA, $numB, $accuracy));

        } elseif ($method == '*') {
            return floatval(bcmul($numA, $numB, $accuracy));

        } elseif ($method == '/') {
            return floatval(bcdiv($numA, $numB, $accuracy));
        }
    }
}

if (!function_exists('round_money')) {
    //处理小数位
    function round_money($money, $type = 1, $len = 2)
    {
        $money = (float)$money;
        if ($type == 1) {//舍
            //$pri=substr(sprintf("%.3f", $money), 0, -1);
            $_arr = explode('.', $money);
            if (isset($_arr[1])) {
                $_a = substr($_arr[1], 0, $len);
                $pri = $_arr[0] . '.' . $_a;
            } else {
                $pri = $_arr[0];
            }
        } else {
            $pri = ceil($money * pow(10, $len)) / pow(10, $len);
        }
        return $pri;
    }
}


/**
 * 将数据格式化成树形结构
 * @author Xuefen.Tong
 * @param array $items
 * @return array
 */
function genTree5($items)
{
    $tree = array(); //格式化好的树
    foreach ($items as $item)
        if (isset($items[$item['pid']]))
            $items[$item['pid']]['son'][] = &$items[$item['id']];
        else
            $tree[] = &$items[$item['id']];
    return $tree;
}


function check_var($post, $fields)
{
    foreach ($post as $i => $v) {
        if (!in_array($i, $fields)) {
            unset($post[$i]);
        }
    }
    return $post;
}

//找回密码邮件内容
function GetpwdMsg($data = array())
{
    $user_id = $data['user_id'];
    $username = $data['username'];
    $webname = '找回密码';
    $email = $data['email'];
    $active_id = urlencode(authcode($user_id . "," . time(), "ENCODE"));
    if ($data['type'] == 'getpwd') {
        $_url = "http://{$_SERVER['HTTP_HOST']}/index.php/getpwd/updatepwd?id={$active_id}";
        $tital = "修改登录密码";
    } elseif ($data['type'] == 'getPayPwd') {
        $_url = "http://{$_SERVER['HTTP_HOST']}/index.php/member/password/resetPayPwd?id={$active_id}";
        $tital = "修改支付密码";
    } elseif ($data['type'] == 'sure_email') {
        $_url = "http://{$_SERVER['HTTP_HOST']}/index.php/register/sure_email?id={$active_id}";
        $tital = "验证邮箱";
    }
    $send_email_msg = '
	<div style="font-size:14px; ">
	<div style="padding: 10px 0px;">
		<h1 style="padding: 0px 15px; margin: 0px;">
			<a title="用户中心" href="http://' . $_SERVER['HTTP_HOST'] . '/" target="_blank" swaped="true">' . $webname . '</a>
		</h1>

		<div style="padding: 2px 20px 30px;">
			<p>亲爱的 <span style="color: rgb(196, 0, 0);">' . $username . '</span> , 您好！</p>
			<p>请点击下面的链接' . $tital . '。</p>
			<p style="overflow: hidden; width: 100%; word-wrap: break-word;"><a title="点击' . $tital . '" href="' . $_url . '" target="_blank" swaped="true">' . $_url . '</a>
			<br><span style="color: rgb(153, 153, 153);">(如果链接无法点击，请将它拷贝到浏览器的地址栏中)</span></p>

			<p style="text-align: right;"><br>用户中心 敬启</p>
			<p><br>此为自动发送邮件，请勿直接回复！如您有任何疑问，请点击<a title="点击联系我们" style="color: rgb(15, 136, 221);" href="http://' . $_SERVER['HTTP_HOST'] . '/" target="_blank" >联系我们</a></p>
		</div>
	</div>
</div>
		';
    return $send_email_msg;
}

//PHP加密解密函数 AUTHCODE
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
{
    // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
    $ckey_length = 4;
    // 密匙
    $key = md5($key ? $key : "jsiw982sjwo29apfmfka");
    // 密匙a会参与加解密
    $keya = md5(substr($key, 0, 16));
    // 密匙b会用来做数据完整性验证
    $keyb = md5(substr($key, 16, 16));
    // 密匙c用于变化生成的密文
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
    // 参与运算的密匙
    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);
    // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，解密时会通过这个密匙验证数据完整性
    // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    // 产生密匙簿
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    // 核心加解密部分
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        // 从密匙簿得出密匙进行异或，再转成字符
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if ($operation == 'DECODE') {
        // substr($result, 0, 10) == 0 验证数据有效性
        // substr($result, 0, 10) - time() > 0 验证数据有效性
        // substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16) 验证数据完整性
        // 验证数据有效性，请看未加密明文的格式
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
        // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

//发送EMAIL
function mail_send($to, $title, $body)
{
    $mail = new \PHPMailer();
    $mail->IsSMTP(); // telling the class to use SMTP
    $mail->SMTPDebug = false;                     // enables SMTP debug information (for testing)
    // 1 = errors and messages
    // 2 = messages only
    $mail->SMTPAuth = true;                  // enable SMTP authentication
    $mail->Host = "smtp.qq.com"; // sets the SMTP server
    $mail->Port = 25;                    // set the SMTP port for the GMAIL server
    $mail->Username = "353889718@qq.com"; // SMTP account username
    $mail->Password = "qqww112233";        // SMTP account password
    $mail->SetFrom('353889718@qq.com', '服务平台');
    $mail->AddReplyTo("353889718@qq.com", "AddReplyTo");
    $mail->Subject = "=?utf-8?B?" . base64_encode($title) . "?=";
    $mail->MsgHTML($body);
    $mail->AddAddress($to, "");
    if (!$mail->Send()) {
        //$str = "错误代码: " . $mail->ErrorInfo;
        return false;
    }
    return true;
}

function sock_open($url, $data = array())
{
    $row = parse_url($url);
    $host = $row['host'];
    $port = isset($row['port']) ? $row['port'] : 80;
    $post = '';//要提交的内容.
    foreach ($data as $k => $v) {
        //$post.=$k.'='.$v.'&';
        $post .= rawurlencode($k) . "=" . rawurlencode($v) . "&";    //转URL标准码
    }
    $fp = fsockopen($host, $port, $errno, $errstr, 30);
    if (!$fp) {
        echo "$errstr ($errno)<br />\n";
    } else {
        $header = "POST $url HTTP/1.1\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "User-Agent: MSIE\r\n";
        $header .= "Host: $host\r\n";
        $header .= "Content-Length: " . strlen($post) . "\r\n";
        $header .= "Connection: Close\r\n\r\n";
        $header .= $post . "\r\n\r\n";
        fputs($fp, $header);
        //$status = stream_get_meta_data($fp);
        $tmp = '';
        while (!feof($fp)) {
            $tmp .= fgets($fp, 128);
        }
        fclose($fp);
        $tmp = explode("\r\n\r\n", $tmp);
        unset($tmp[0]);
        $tmp = implode("", $tmp);
        /*while (!feof($fp))
        {
         if(($header = fgets($fp)) && ($header == "\r\n" ||  $header == "\n")) {
            break;
         }
        }
        $tmp = "";
        while (!feof($fp))
        {
            $tmp .= fgets($fp, 128);
        }
        fclose($fp); */
    }
    return $tmp;
}

//curl请求函数
function curl_url($url, $data = array())
{
    $ssl = substr($url, 0, 8) == "https://" ? TRUE : FALSE;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    if ($data) {
        if (is_array($data)) {
            curl_setopt($ch, CURLOPT_POST, 1);
        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data))
            );
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    if ($ssl) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    }
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}


//此函数完成带汉字的字符串取串
function substr_cn($str, $mylen)
{
    $len = strlen($str);
    $content = '';
    $count = 0;
    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($str, $i, 1)) > 127) {
            $content .= substr($str, $i, 2);
            $i++;
        } else {
            $content .= substr($str, $i, 1);
        }
        if (++$count == $mylen) {
            break;
        }
    }
    return $content;
}

//导出excel格式表
function excel($filename, $title, $data)
{
    $filename .= '.xls';
    header("Content-type: application/vnd.ms-excel");
    header("Content-disposition: attachment; filename={$filename}");
    header("Pragma: no-cache");
    header("Expires: 0");
    if (is_array($title)) {
        foreach ($title as $key => $value) {
            echo iconv('utf-8', 'gb2312', $value) . "\t";
            //echo utf8_to_gbk($value)."\t";
        }
    }
    echo "\n";
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            foreach ($value as $_key => $_value) {
                $_value = str_replace(array("\r\n", "\r", "\n"), " ", $_value);
                if (!empty($_value)) echo iconv('utf-8', 'gb2312', $_value);//echo utf8_to_gbk($_value);
                echo "\t";
            }
            echo "\n";
        }
    }
}

//生成二维码
function qrcode($data, $dir, $filename, $level = 'L', $size = 4, $marg = 0, $col = '#000000')
{
    global $color;
    $color = $col;
    if (empty($data)) return '';
    if (!in_array($level, array('L', 'M', 'Q', 'H'))) $level = 'L';
    $size = min(max((int)$size, 1), 10);
    if (!file_exists($dir)) mkdir($dir);
    $filename = $dir . $filename;
    if (!file_exists($filename)) {
        include_once(ROOT . "/core/phpqrcode/qrlib.php");
        QRcode::png($data, $filename, $level, $size, $marg);
    }
    return $filename;
}

//DeCode(密文,'D',密钥); 解密
//DeCode(明文,'E',密钥); 加密
function DeCode($string, $operation, $key = 'cgqhcYpp')
{
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


//返回首字母
function getCharFirst($s0)
{
    $fchar = ord($s0{0});
    if ($fchar >= ord("A") and $fchar <= ord("z")) return strtoupper($s0{0});
    $s1 = iconv("UTF-8", "gb2312", $s0);
    $s2 = iconv("gb2312", "UTF-8", $s1);
    if ($s2 == $s0) {
        $s = $s1;
    } else {
        $s = $s0;
    }
    $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
    if ($asc >= -20319 and $asc <= -20284) return "A";
    if ($asc >= -20283 and $asc <= -19776) return "B";
    if ($asc >= -19775 and $asc <= -19219) return "C";
    if ($asc >= -19218 and $asc <= -18711) return "D";
    if ($asc >= -18710 and $asc <= -18527) return "E";
    if ($asc >= -18526 and $asc <= -18240) return "F";
    if ($asc >= -18239 and $asc <= -17760) return "G";
    if ($asc >= -17759 and $asc <= -17248) return "H";
    if ($asc >= -17247 and $asc <= -17418) return "H";
    if ($asc >= -17417 and $asc <= -16475) return "J";
    if ($asc >= -16474 and $asc <= -16213) return "K";
    if ($asc >= -16212 and $asc <= -15641) return "L";
    if ($asc >= -15640 and $asc <= -15166) return "M";
    if ($asc >= -15165 and $asc <= -14923) return "N";
    if ($asc >= -14922 and $asc <= -14915) return "O";
    if ($asc >= -14914 and $asc <= -14631) return "P";
    if ($asc >= -14630 and $asc <= -14150) return "Q";
    if ($asc >= -14149 and $asc <= -14091) return "R";
    if ($asc >= -14090 and $asc <= -13319) return "S";
    if ($asc >= -13318 and $asc <= -12839) return "T";
    if ($asc >= -12838 and $asc <= -12557) return "W";
    if ($asc >= -12556 and $asc <= -11848) return "X";
    if ($asc >= -11847 and $asc <= -11056) return "Y";
    if ($asc >= -11055 and $asc <= -10247) return "Z";
    return null;
}