<?php
/**
* 跳转
* @param string - $url
**/
function h($url)
{
    if (!headers_sent()) {
        header('Location:' . $url);
    } else {
        echo "<script>window.location.href='$url';</script>";
    }
    exit;
}

/**
* @param $action - string 带路径的action
* @param $param  - array 参数
* @return $url   - string 访问地址
**/
function u($action, $param = array())
{
    $url = WEB_URL . $action;
    if ($param) {
        foreach ($param as $k => $v) {
            $url .= '-' . $k . '-' . $v;
        }
    }
    $url .= '.html';
    return $url;
}

/**
*转换微信 带unicode编码 emoji表情的字符串
* @param $emoji 必须是json_encode格式的字符串
*/
function emoji_decode($emoji){
	$str = '{"result_str":"'.trim($emoji,'"').'"}';	//组合成json格式
	$strarray = json_decode($str,true);	//json转换为数组，利用 JSON 对 \uXXXX 的支持来把转义符恢复为 Unicode 字符（by 梁海）
	return $strarray['result_str'];
}
/**
 * filter value
 *
 * @param mixed $val
 * @param string $type-int|string|html
 * @param string $method-post|get|request|cookie|server
 * @return unknown
 */
function filter_val($val, $type = 'int', $method = 'request') {
    $arr = [];
    switch (strtoupper($method)) {
        case 'POST' :
            $arr = $_POST;
            break;
        case 'GET' :
            $arr = $_GET;
            break;
        case 'REQUEST' :
            $arr = $_REQUEST;
            break;
        case 'COOKIE' :
            $arr = $_COOKIE;
            break;
        case 'SERVER' :
            $arr = $_SERVER;
            break;
        case 'SESSION' :
            $arr = $_SESSION;
            break;
        default :
            break;
    }

    if (isset ($arr["$val"])) {
        if ($type == 'int') {
            return intval($arr["$val"]) ? intval($arr["$val"]) : 0;
        }
        elseif ($type == 'string') {
            return empty ($arr["$val"]) ? false :  strip_tags($arr["$val"]) ;
        }
        elseif ($type == 'html') {
            return empty ($arr["$val"]) ? false :  strip_tags($arr["$val"], "<a><em><strong><p><br><font><img><b><div><span>") ;
        }
    } else {
        return false;
    }
}

/**
 * 转换图片的路径
 *
 * @param string $file
 * @param string $type=>'ltow|wto1'
 * @return $path
 */
function convert_web_url($file, $type = 'ltow') {
    switch ($type) {
        case 'ltow' :
            $path = str_replace(BS, IMG_URL, $file);
            break;
        case 'wtol' :
            $path = str_replace(IMG_URL, BS, $file);
            break;
        default :
            $path = $file;
            break;
    }
    $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
    return $path;
}

/**
 * 下载图片到本地
 *
 * @param string $url
 * @return string $header_file|false
 */
function download_image($url,$filename = '') {
    //存储图片到本地
    $request = new Http();
    $request->curlOpts = array(CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_SSL_VERIFYHOST=>false);
    $request->url = $url;
    $response = $request->get();
    $header_file = $response->save($filename);
    return $header_file;
}

/**
 * 获取数组中某个键值的内容
 *
 * @param array $array
 * @param string $string
 * @param string $key
 * @return array
 */
function array_get_by_key($data, $string, $key = '', $symbol = "'") {
    if (!is_array($data)) {
        return [];
    }
    if (function_exists('array_column')) {
        return array_column($data, $string, $key);
    }
    $res = [];
    foreach ($data as $item) {
        if ($key) {
            if (is_string($item[$string])) {
                $res[$item[$key]] = $symbol . $item[$string] . $symbol;
            } else {
                $res[$item[$key]] = $item[$string];
            }
        } else {
            if (is_string($item[$string])) {
                $res[] = $item[$string];
            } else {
                $res[] = $item[$string];
            }

        }
    }
    return $res;
}

/**
 * 根据某个键值排列数组
 *
 * @param array $array
 * @param string $key
 * @return array
 */
function array_sort_by_key($data, $key)
{
    if (!is_array($data)) {
        return [];
    }
    $res = [];
    foreach ($data as $item) {
        $res[$item[$key]] = $item;
    }
    return $res;
}

/*计算两个GPS点之间的距离*/
function distance($lon1, $lat1, $lon2, $lat2)
{
    return (2*ATAN2(SQRT(SIN(($lat1-$lat2)*PI()/180/2)
    *SIN(($lat1-$lat2)*PI()/180/2)+
    COS($lat2*PI()/180)*COS($lat1*PI()/180)
    *SIN(($lon1-$lon2)*PI()/180/2)
    *SIN(($lon1-$lon2)*PI()/180/2)),
    SQRT(1-SIN(($lat1-$lat2)*PI()/180/2)
    *SIN(($lat1-$lat2)*PI()/180/2)
    +COS($lat2*PI()/180)*COS($lat1*PI()/180)
    *SIN(($lon1-$lon2)*PI()/180/2)
    *SIN(($lon1-$lon2)*PI()/180/2))))*6378140;
}

/**
* 百度地图BD09坐标---->中国正常GCJ02坐标
* 腾讯地图用的也是GCJ02坐标
* @param double $lat 纬度
* @param double $lng 经度
* @return array();
*/
function convert_bd09_to_gcj02($lat, $lng)
{
    $x_pi = 3.14159265358979324 * 3000.0 / 180.0;
    $x = $lng - 0.0065;
    $y = $lat - 0.006;
    $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
    $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
    $lng = $z * cos($theta);
    $lat = $z * sin($theta);
    return array('lng'=>$lng,'lat'=>$lat);
}

/**
 * 字节数转换成带单位的
 * 原理是利用对数求出欲转换的字节数是1024的几次方。
 * 其实就是利用对数的特性确定单位。
*/
function size_change($size, $digits=2)
{
  $unit= array('','K','M','G','T','P');//单位数组，是必须1024进制依次的哦。
  $base= 1024;//对数的基数
  $i   = floor(log($size,$base));//字节数对1024取对数，值向下取整。
  return round($size/pow($base,$i),$digits).' '.$unit[$i] . 'B';
}

/*
 * JS形式返回数据格式
 * @param string $msg
 * @param int $url
 * @return void
 */
function exit_js($msg = 'msg',$url = -1)
{
    $str = "";
    $str .= '<script type="text/javascript">';
    $str .= 'alert("' . $msg . '");';
    if (is_numeric($url))
    {
        $str .= "history.go(-1);";
    }
    else if (is_string($url))
    {
        $str .= 'window.location.href="' . $url . '";';
    }
    $str .= '</script>';
    exit($str);
}

/**
* json转换
**/
function json_encode_str($str){
    return preg_replace("#\\\u([0-9a-f]+)#ie", "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))", $str);
}


/**
 * 获取客户端IP地址
 *
 * @return string $ip
 */
function get_client_ip() {
    $ip = $_SERVER['REMOTE_ADDR'];
    if (isset ($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset ($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
        foreach ($matches[0] AS $xip) {
            if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                $ip = $xip;
                break;
            }
        }
    }
    return $ip;
}

/**
发送邮件-待完善
**/
function send_mail($array = array()){
    $mail             = new PHPMailer(); //new一个PHPMailer对象出来
    $mailWay = isset($array['mail_way']) ? $array['mail_way'] : 'smtp';
    switch(strtolower($mailWay)) {
        case 'smtp':
            $mail->isSMTP();                           // 设定使用SMTP服务
	        if(isset($array['debug'])) {
		        $mail->SMTPDebug = $array['debug'];
	        }
            // 1 = errors and messages
            // 2 = messages only
            $mail->SMTPAuth   = true;                   // 启用 SMTP 验证功能
            $mail->Host       = isset($array['host']) ? $array['host'] : Config::item('mail_host');         // SMTP 服务器
            if(strpos($mail->Host,'qq.com')) {
                $mail->SMTPSecure = 'tsl';
            }
            $mail->Port       = isset($array['port']) ? $array['port'] : Config::item('mail_port');         // SMTP服务器的端口号
            $mail->Username   = isset($array['username']) ? $array['username'] : Config::item('mail_user');   // SMTP服务器用户名 注 普通用户不加 @
            $mail->Password   = isset($array['password']) ? $array['password'] : Config::item('mail_password');            // SMTP服务器密码
            break;
        case 'mail':
            $mail->isMail();
            $mail->Sender = isset($array['mail_sender']) ? $array['mail_sender'] : 'E.U.B';
            $mail->addCustomHeader('X-Sender',isset($array['send_mailname']) ? $array['send_mailname'] : Config::item('mail_name'));
        default:
            break;
    }
    $mail->CharSet = "utf-8"; // 这里指定字符集！
    $mail->Encoding = "base64";
	if(isset($array['send_mailname']) && isset($array['send_nickname'])) {
    	$mail->SetFrom($array['send_mailname'],$array['send_nickname']);
    	$mail->AddReplyTo($array['send_mailname'], $array['send_nickname']);
	} else {
		$mail->SetFrom(Config::item('mail_name'), Config::item('mail_nickname'));
		$mail->AddReplyTo(Config::item('mail_name'), Config::item('mail_nickname'));
	}
    $mail->Subject    = $array['subject'];
    $mail->AltBody    = 'To view the message, please use an HTML compatible email viewer!'; // optional, comment out and test
    $mail->MsgHTML($array['body']);

    //发送附件
	if(isset($array['attachment'])) {
		$mail->addAttachment($array['attachment'], $array['subject'] . "." . $array['attachtype']);
	}
    //如果是数组循环添加到收件人
    if(is_array($array['to'])){
        foreach($array['to'] as $v){
            $mail->AddAddress($v, '');
        }
        if($mail->Send()) {
            return true;
        } else {
            return false;
        }
    }

    $mail->AddAddress($array['to'], '');
    if($mail->Send()) {
        return true;
    } else {
        return false;
    }
}

/**
下载文件-带修改
**/
function force_download($filename = '', $data = '')
{
    if ($filename == '' OR $data == '')
    {
        return FALSE;
    }

    // Try to determine if the filename includes a file extension.
    // We need it in order to set the MIME type
    if (FALSE === strpos($filename, '.'))
    {
        return FALSE;
    }

    // Grab the file extension
    $x = explode('.', $filename);
    $extension = end($x);

    // Load the mime types
    if (defined('ENVIRONMENT') AND is_file(BS . 'conf/mimes.php'))
    {
        include(BS . 'conf/mimes.php');
    }
    elseif (is_file(BS . 'conf/mimes.php'))
    {
        include(BS . 'conf/mimes.php');
    }

    // Set a default mime if we can't find it
    if ( ! isset($mimes[$extension]))
    {
        $mime = 'application/octet-stream';
    }
    else
    {
        $mime = (is_array($mimes[$extension])) ? $mimes[$extension][0] : $mimes[$extension];
    }

    // Generate the server headers
    if (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") !== FALSE)
    {
        header('Content-Type: "'.$mime.'"');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header("Content-Transfer-Encoding: binary");
        header('Pragma: public');
        header("Content-Length: ".strlen($data));
    }
    else
    {
        header('Content-Type: "'.$mime.'"');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header("Content-Transfer-Encoding: binary");
        header('Expires: 0');
        header('Pragma: no-cache');
        header("Content-Length: ".strlen($data));
    }
    exit($data);
}

/**
 * 计算时间差 
 * @author Jerry Shen <haifei.shen@eub-inc.com>
 * @version 2017-10-13
 *
 * @param mixed $stime
 * @param mixed $etime
 * @return void
 */
function get_time_diff($stime, $etime)
{
    $diff = $etime - $stime;
    $hour = $diff/3600;
    $min  = ($diff%3600)/60;
    $sec  = $diff - $hour*3600 - $min*60;
    return str_pad($hour,2,0,STR_PAD_LEFT) . ':' . str_pad($min,2,0,STR_PAD_LEFT) . ':' . str_pad($sec,2,0,STR_PAD_LEFT);
}

/**
 * 获取url地址中的某个参数值
 * @param string - $url
 * @param string - $key
 * @return mixed - $result
 **/
function get_url_vars($url, $key = ''){
    if($query = parse_url($url, PHP_URL_QUERY)) {
        parse_str($query, $params);
        if($key) {
            return isset($params[$key]) ? $params[$key] : '';
        } else {
            return $params;
        }
    } else {
        return false;
    }
}

/**
 * 腾讯地图API获取经纬度 - 待完善
 * @author Jerry Shen <haifei.shen@eub-inc.com>
 * @version 2017-10-13
 *
 * @param mixed $address
 * @return void
 */
function get_geo_by_address($address)
{
    $http = new Http();
    $mapUrl = 'http://apis.map.qq.com/ws/geocoder/v1/?address=%s&key=HLDBZ-67ERU-WVMVZ-2YK7Z-WY7C3-PTBDW';
    $http->url = sprintf($mapUrl, trim($address));
    $response = $http->get();
    $infoList = json_decode($response->body,true);
    return $infoList;
}

/**
 * 腾讯API根据经纬度获取位置信息
 * @author Jerry Shen <haifei.shen@eub-inc.com>
 * @version 2017-10-13
 *
 * @param mixed $latitude
 * @param mixed $longitude
 * @return void
 */
function get_address_by_geo($latitude, $longitude){
    $http = new Http();
    $mapUrl = 'http://apis.map.qq.com/ws/geocoder/v1/?location=%s,%s&key=HLDBZ-67ERU-WVMVZ-2YK7Z-WY7C3-PTBDW';
    $http->url = sprintf($mapUrl, $latitude, $longitude);
    $response = $http->get();
    $addressInfo = json_decode($response->body,true);
    return $addressInfo;
}

/**
 * 根据IP获取经纬度
 * @author Jerry Shen <haifei.shen@eub-inc.com>
 * @version 2017-10-13
 *
 * @param mixed $ip
 * @param string $needle
 * @return void
 */
function get_geo_by_ip_info($ip = null, $needle = 'position'){
    $http = new Http();
    $mapUrl = 'http://apis.map.qq.com/ws/location/v1/ip?key=HLDBZ-67ERU-WVMVZ-2YK7Z-WY7C3-PTBDW';
    $http->url = $ip === null ? $mapUrl : $mapUrl . '&ip=' . $ip;
    $response = $http->get();
    $info = json_decode($response->body,true);
    if($needle == 'position'){
        if(isset($info['result']['location']['lat']) && isset($info['result']['location']['lng'])){
            return ['latitude' => $info['result']['location']['lat'], 'longitude' => $info['result']['location']['lng']];
        }else{
            return ['latitude' => '', 'longitude' => ''];
        }
    }elseif($needle == 'address'){
        if(isset($info['result']['ad_info'])){
            return $info['result']['ad_info'];
        }else{
            return [];
        }
    }
    return $info;
}

/** 读取excel-待完善
 * @author huanglike
 * @param string $filename 文件路径
 * @param string $encode 编码格式，默认"utf-8"
 * @return mixed excel数据
 */
function read_excel($filename, $encode='utf-8'){
    try {
        $ext_list  = explode(".",$filename);
        $ext = end($ext_list);
        if($ext == "xls"){
            $objReader = PHPExcel_IOFactory::createReader('Excel5');
        }else{
            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        }
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($filename);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelData = array();
        //第0列是空数据
        for ($row = 0; $row < $highestRow; $row++) {
            for($col = 0; $col <$highestColumnIndex;$col++){
                $excelData[$row][] =(string)$objWorksheet->getCellByColumnAndRow($col, $row+1)->getValue();
            }
        }
        return $excelData;
    } catch (Exception $e) {
        return false;
    }
}
/**
 * csv转码
 * @param string $str 字符串
 * @param string $type  td则去逗号,tr则不转逗号
 * @return string
 *
 */
function iconv_str($str, $type = 'td') {

	$returnStr = mb_convert_encoding(escape_csv($str), 'gbk', 'utf-8');

	if ($type == 'td') {
		$douhao=iconv('utf-8','gbk','，');
		$returnStr = str_replace(',', $douhao, $returnStr);
	}
	if ($returnStr == '') {
		$returnStr = " ";
	}
	return $returnStr;

}

/**
 * escape_csv
 * @author Jerry Shen <haifei.shen@eub-inc.com>
 * @version 2017-10-13
 *
 * @param mixed $document
 * @return void
 */
function escape_csv($document)
{
	$document = urlencode($document);
	$document = urldecode(preg_replace("'%0D'i", ' ', $document));

	$search = array (
			"'<script[^>]*?>.*?</script>'si", // 去掉 javascript
			"'<[\/\!]*?[^<>]*?>'si", // 去掉 HTML 标记
			"'([\r\n])[\s]+'", // 去掉空白字符
			"'&(quot|#34);'i", // 替换 HTML 实体
			"'&(amp|#38);'i",
			"'&(lt|#60);'i",
			"'&(gt|#62);'i",
			"'&(nbsp|#160);'i",
			"'&(iexcl|#161);'i",
			"'&(cent|#162);'i",
			"'&(pound|#163);'i",
			"'&(copy|#169);'i",
			"'&#(\d+);'e",
			"'\n'"
	); // 作为 PHP 代码运行

	$replace = array (
		"",
		"",
		"\\1",
		"\"",
		"&",
		"<",
		">",
		" ",
		chr(161
	), chr(162), chr(163), chr(169), "chr(\\1)", "");

	return trim(preg_replace($search, $replace, $document));
}

/**
* Convert BR tags to newlines and carriage returns.
*
* @param string The string to convert
* @param string The string to use as line separator
* @return string The converted string
*/
function br2nl ( $string, $separator = PHP_EOL )
{
    $separator = in_array($separator, array("\n", "\r", "\r\n", "\n\r", chr(30), chr(155), PHP_EOL)) ? $separator : PHP_EOL;  // Checks if provided $separator is valid.
    return preg_replace('/\<br(\s*)?\/?\>/i', $separator, $string);
}

/**
* 整理分词后排序
* asort() - 根据值，以升序对关联数组进行排序
* arsort() - 根据值，以降序对关联数组进行排序
**/
function statistics_word($words_list, $sequence='asort'){
    foreach($words_list as $k=>$words){
        $tmp[$words] = mb_strlen($words, 'utf8');
    }
    $sequence($tmp);
    foreach($tmp as $k=>$v){
        $ret[] = $k;
    }
    return $ret;
}


/**
* 微信符号表情
**/
function emoji($content, $flag='') {
    $emoji = array(
        "/::)",  //微笑
         "/::~",     //撇嘴
         "/::B",     //色
         "/::|",     //发呆
         "/:8-)",    //得意
         "/::<",     //流泪
         "/::$",     //害羞
         "/::X",     //闭嘴
         "/::Z",     //睡
         "/::'(",    //大哭
         "/::-|",    //尴尬
         "/::@",     //发怒
         "/::P",     //调皮
         "/::D",     //呲牙
         "/::O",     //惊讶
         "/::(",     //难过
         "/::+",     //酷
         "/:--b",    //冷汗
         "/::Q",     //抓狂
         "/::T",     //吐
         "/:,@P",    //偷笑
         "/:,@-D",   //可爱
         "/::d",     //白眼
         "/:,@o",    //傲慢
         "/::g",     //饥饿
         "/:|-)",    //困
         "/::!",     //惊恐
         "/::L",     //流汗
         "/::>",     //憨笑
         "/::,@",    //大兵
         "/:,@f",    //努力
         "/::-S",    //咒骂
         "/:?",  //疑问
         "/:,@x",    //嘘
         "/:,@@",    //晕
         "/::8",     //折磨
         "/:,@!",    //衰
         "/:!!!",    //骷髅
         "/:xx",     //敲打
         "/:bye",    //再见
         "/:wipe",   //擦汗
         "/:dig",    //抠鼻
         "/:handclap",   //鼓掌
         "/:&-(",    //溴大了
         "/:B-)",    //坏笑
         "/:<@",     //左哼哼
         "/:@>",     //右哼哼
         "/::-O",    //哈欠
         "/:>-|",    //鄙视
         "/:P-(",    //委屈
         "/::'|",    //快哭了
         "/:X-)",    //阴险
         "/::*",     //亲亲
         "/:@x",     //吓
         "/:8*",     //可怜
         "/:pd",     //菜刀
         "/:<W>",    //西瓜
         "/:beer",   //啤酒
         "/:basketb",    //篮球
         "/:oo",     //乒乓
         "/:coffee",     //咖啡
         "/:eat",    //饭
         "/:pig",    //猪头
         "/:rose",   //玫瑰
         "/:fade",   //凋谢
         "/:showlove",   //示爱
         "/:heart",  //爱心
         "/:break",  //心碎
         "/:cake",   //蛋糕
         "/:li",     //闪电
         "/:bome",   //炸弹
         "/:kn",     //刀
         "/:footb",  //足球
         "/:ladybug",    //瓢虫
         "/:shit",   //便便
         "/:moon",   //月亮
         "/:sun",    //太阳
         "/:gift",   //礼物
         "/:hug",    //拥抱
         "/:strong",     //强
         "/:weak",   //弱
         "/:share",  //握手
         "/:v",  //胜利
         "/:@)",     //抱拳
         "/:jj",     //勾引
         "/:@@",     //拳头
         "/:bad",    //差劲
         "/:lvu",    //爱你
         "/:no",     //No
         "/:ok",     //Ok
         "/:love",   //爱情
         "/:<L>",    //飞吻
         "/:jump",   //跳舞
         "/:shake",  //发抖
         "/:<O>",    //怄火
         "/:circle",     //转圈
         "/:kotow",  //磕头
         "/:turn",   //回头
         "/:skip",   //跳绳
         "/:oY",     //挥手
         "/:#-0",    //激动
         "/:hiphot",     //街舞 // hiphot doesnot work!
         "/:kiss",   //献吻
         "/:<&",     //左太极
         "/:&>"  //右太极
         );


    // $flag 为空的时候 是 链接 有值时是表情符号
    if (empty($flag)) {
        $arr = explode('<img', $content);
        // print_r(count($arr));
        if (count($arr)<=1) {
            $data = $content;
        } else {
            foreach ($arr as $key => $value) {
                if ($key == 0) {
                    $data = $value;
                } else {
                    $info = '<img' . $value;
                    // print_r($data);
                    $num = strpos($info, '>');
                    $after = substr_replace($info,'','',$num+1); // 非表情部分
                    $before = substr($info, 0, $num+1); // 表情部分

                    preg_match("/\w+.gif|jpg|png$/", $before, $arr);
                    $str = str_replace('.gif', '', $arr[0]); // key
                    $data .= preg_replace('(<img.*>)', $emoji[$str], $before) . $after; // $emoji[$str]; // 符号
                }
            }
        }
        return $data;
    } else {
        $arr = explode('/', $content);
        if (count($arr)<=1) {
            $data = $content;
        } else {
            foreach ($arr as $key => $value) {
                if ($key == 0) {
                    $data = $value;
                } else {
                    $info = '/' . $value;
                    // print_r($data);
                    $key = array_search($info, $emoji);
                    if ($key) {
                        $data .= '<img src="https://res.wx.qq.com/mpres/htmledition/images/icon/emotion/'.$key.'.gif" width="20px" height="20px">';
                    } else {
                        $data .= $info;
                    }
                }
            }
        }
        return $data;
    }
}

/**
* 发送验证码
* @param $mobile - string 手机号码
* @param $content - string 短信内容
* @return boolean
**/
function send_mms($mobile, $content, $sign = '', $templateId = '', $key = '', $secret = '')
{
    $mmsConfig = config('mms');
    if (empty($mmsConfig) 
        && empty($sign) && empty($templateId) 
        && empty($key) && empty($secret)) {
        return false;
    }
    $sign = empty($sign) ? $mmsConfig['sign'] : $sign;
    $templateId = empty($templateId) ? $mmsConfig['template_id'] : $templateId;
    $key = empty($key) ? $mmsConfig['key'] : $key;
    $secret = empty($secret) ? $mmsConfig['secret'] : $secret; 

    if (empty($sign) || empty($templateId) 
        || empty($key) || empty($secret)) {
        return false;
    }
    if ('dayu' == $mmsConfig['type']) {
        $AliSmsApi = new \util\AliDyApi($key, $secret);
    } else {
        $AliSmsApi = new \util\AliSmsApi($key, $secret);
    }
    $result = $AliSmsApi->send($sign, $templateId, $mobile, $content);
    //需要根据不同的平台调整
    if (isset($result['error_response'])) {
        return false;
    } else {
        return true;
    }
}

