<?php
// 应用公共文件

function bonus_random($total,$num,$min,$max){
    $data = array();
    if ($min * $num > $total) {
        return array();
    }
    if($max*$num < $total){
        return array();
    }
    while ($num >= 1) {
        $num--;
        $kmix = max($min, $total - $num * $max);
        $kmax = min($max, $total - $num * $min);
        $kAvg = $total / ($num + 1);
        //获取最大值和最小值的距离之间的最小值
        $kDis = min($kAvg - $kmix, $kmax - $kAvg);
        //获取0到1之间的随机数与距离最小值相乘得出浮动区间，这使得浮动区间不会超出范围
        $r = ((float)(rand(1, 10000) / 10000) - 0.5) * $kDis * 2;
        $k = sprintf("%.2f", $kAvg + $r);
        $total -= $k;
        $data[] = $k;
    }
    shuffle($data);
    return $data;
}

function getmicrotime(){
    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
}

//获取13位时间戳
function getMillisecond() {
    list($t1, $t2) = explode(' ', microtime());
    return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
}

function gen_params_sign($params){
    if(isset($params['sign'])) unset($params['sign']);
    $sign_time = $params['time'];
    unset($params['time']);
    ksort($params);//按照键名从低到高进行排序
    $sign_params_str = "";
    foreach ($params as $k => $v) {
        $sign_params_str.= "$k=$v&";
    }
    $sign_params_str = $sign_params_str.$sign_time.config('sign_key');
    $sign = md5($sign_params_str);
    return array('sign'=>$sign,'params'=>$sign_params_str);
}

function create_token($deviceid = '', $user){
    $secrt = md5($deviceid . config('sign_key'));
    $identity = encrypt_data($user);
    return $identity . '_' . $secrt;
}

/**
 * 接口请求参数加密
 * @param str $data
 * @return Ambigous <boolean, mixed>
 */
function encrypt_data($data, $key = ''){
    if (empty($key)) {
        $key = config('param_key');
    }
    $crypt = new \Crypt3Des($key);
    $result = $crypt->encrypt($data);
    return $result;
}

/**
 * 接口返回数据解密
 * @param str $data
 * @return Ambigous <boolean, mixed>
 */
function decrypt_data($data, $dejson = true, $key = ''){
    if (empty($key)) {
        $key = config('param_key');
    }
    $crypt = new \Crypt3Des($key);
    $result = $crypt->decrypt($data);
    if ($dejson) {
        $res_data = array();
        if ($result) {
            $res_data = json_decode($result, true);
        }
    } else {
        $res_data = $result;
    }
    return $res_data;
}

/**
 *
 * @param  $mobile
 *
 * 手机号验证
 */
function check_mobile($mobile, $pattern = false) {
    if (!$pattern) {
        $pattern = '/(^1[345678]\d{9}$)/';
    }
    preg_match($pattern, $mobile, $match);
    if (empty($match)) {
        return false;
    }
    return true;
}
/**
 * @desc 获取客户端的ip地址
 */
function get_client_ipaddr(){
    if (!empty($_SERVER ['HTTP_CLIENT_IP']) && filter_valid_ip($_SERVER ['HTTP_CLIENT_IP'])) {
        return $_SERVER ['HTTP_CLIENT_IP'];
    }
    if (!empty($_SERVER ['HTTP_X_FORWARDED_FOR'])) {
        $iplist = explode(',', $_SERVER ['HTTP_X_FORWARDED_FOR']);
        foreach ($iplist as $ip) {
            if (filter_valid_ip($ip)) {
                return $ip;
            }
        }
    }
    if (!empty($_SERVER ['HTTP_X_FORWARDED']) && filter_valid_ip($_SERVER ['HTTP_X_FORWARDED'])) {
        return $_SERVER ['HTTP_X_FORWARDED'];
    }
    if (!empty($_SERVER ['HTTP_X_CLUSTER_CLIENT_IP']) && filter_valid_ip($_SERVER ['HTTP_X_CLUSTER_CLIENT_IP'])) {
        return $_SERVER ['HTTP_X_CLUSTER_CLIENT_IP'];
    }
    if (!empty($_SERVER ['HTTP_FORWARDED_FOR']) && filter_valid_ip($_SERVER ['HTTP_FORWARDED_FOR'])) {
        return $_SERVER ['HTTP_FORWARDED_FOR'];
    }
    if (!empty($_SERVER ['HTTP_FORWARDED']) && filter_valid_ip($_SERVER ['HTTP_FORWARDED'])) {
        return $_SERVER ['HTTP_FORWARDED'];
    }
    return $_SERVER ['REMOTE_ADDR'];
}

/**
 *
 *@desc 验证IP地址有效性
 */
function filter_valid_ip($ip){
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false){
        return false;
    }
    return true;
}

/**
 * @desc 获取url的文件扩展名
 */
function getExt($url){
    if($url){
        return pathinfo( parse_url($url)['path'] )['extension'];
    }else {
        return '';
    }
}

function sortArrByOneField(&$array, $field, $desc = false){
    $fieldArr = array();
    foreach ($array as $k => $v) {
        $fieldArr[$k] = $v[$field];
    }
    $sort = $desc == false ? SORT_ASC : SORT_DESC;
    array_multisort($fieldArr, $sort, $array);
}

/**
 * @desc 秒转换为分秒
 */
function secToMinSec($secs){
    $secs = intval($secs);
    if($secs<=0){
        return "0″";
    }else if($secs>0 && $secs<=60){
        return $secs."″";
    }else if($secs > 60){
        $min = floor($secs / 60);
        $sec  = $secs % 60;
        if($sec==0){
            return $min."′";
        }else if($sec>0){
            return $min."′".$sec."″";
        }
    }
}

function changeTimeType($seconds){
    if ($seconds > 3600){
        $hours = intval($seconds/3600);
        $minutes = $seconds % 3600;
        $time = $hours.":".gmstrftime('%M:%S', $minutes);
    }else{
        $time = gmstrftime('%H:%M:%S', $seconds);
    }
    return $time;
}

function viewTimes($strtime){
    $now = time();
    $diff_time =  $now-$strtime;
    if($diff_time<=600){
        $view_time = '刚刚';
    }else if($diff_time<3600){
        $d_view = floor($diff_time/60);
        $view_time = $d_view.'分钟前';
    }else if($diff_time<=86400){
        $d_view = floor($diff_time/3600);
        $view_time = $d_view.'小时前';
    }else {
        $view_time = date('n月j日',$strtime);
    }
    return $view_time;
}

function getprovinceByip($ip){
    $url = "http://api.map.baidu.com/location/ip?ak=q1pQnjOG28z8xsCaoby2oqLTLaPgelyq&coor=bd09ll&ip=".$ip;
    $result = file_get_contents($url);
    $re = json_decode($result,true);

    if($re && $re['status'] == 0){
        $province_name = $re['content']['address_detail']['province'];

    }else{
        $province_name = '北京市';
    }
    return $province_name;
}

function getgeoByloa($lat,$lon){
    $ak = C('BAIDU_GEO_KEY');
    $url = 'http://api.map.baidu.com/geocoder/v2/?location='.$lat.','.$lon.'&output=json&pois=0&ak='.$ak;
    $result = file_get_contents($url);
    $re = json_decode($result,true);
    if($re && $re['status'] == 0){
        return $re['result'];
    }
}