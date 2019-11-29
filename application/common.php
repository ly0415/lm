<?php

// 应用公共函数库文件

use think\Request;
use think\Log;

/**
 * 打印调试函数
 * @param $content
 * @param $is_die
 */
function pre($content, $is_die = true)
{
    header('Content-type: text/html; charset=utf-8');
    echo '<pre>' . print_r($content, true);
    $is_die && die();
}

/**
 * 递归查询
 * @author  luffy
 * @date    2019-08-23
 */
function toTree($array, $pid = 0 ){
    $tree = array();
    foreach ($array as $key => $value) {
        if ($value['pid'] == $pid) {
            $value['child'] = toTree($array, $value['id']);
            if (!$value['child']) {
                unset($value['child']);
            }
            $tree[$value['id']] = $value;
        }
    }
    return $tree;
}

/**
 * 递归获取最底层级ID
 * @author  luffy
 * @date    2019-08-23
 */
function getLastLevelId($array, $pid = 0 ){
    static $arr = array();
    foreach ($array as $key => $value) {
        if ($value['pid'] == $pid) {
            $value['child'] = getLastLevelId($array, $value['id']);
            if($value['level'] == 3){
                $arr = array_merge($arr, [$value['id']]);
            }
        }
    }
    return $arr;
}

/**
 * 驼峰命名转下划线命名
 * @param $str
 * @return string
 */
function toUnderScore($str)
{
    $dstr = preg_replace_callback('/([A-Z]+)/', function ($matchs) {
        return '_' . strtolower($matchs[0]);
    }, $str);
    return trim(preg_replace('/_{2,}/', '_', $dstr), '_');
}

/**
 * 生成密码hash值
 * @param $password
 * @return string
 */
function yoshop_hash($password)
{
    return md5($password);
}

/**
 * 获取当前域名及根路径
 * @return string
 */
function base_url()
{
    static $baseUrl = '';
    if (empty($baseUrl)) {
        $request = Request::instance();
        $subDir = str_replace('\\', '/', dirname($request->server('PHP_SELF')));
        $baseUrl = $request->scheme() . '://' . $request->host() . $subDir . ($subDir === '/' ? '' : '/');
    }
    return $baseUrl;
}

/**
 * 写入日志 (废弃)
 * @param string|array $values
 * @param string $dir
 * @return bool|int
 */
function write_log($values, $dir)
{
    if (is_array($values))
        $values = print_r($values, true);
    // 日志内容
    $content = '[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . $values . PHP_EOL . PHP_EOL;
    try {
        // 文件路径
        $filePath = $dir . '/logs/';
        // 路径不存在则创建
        !is_dir($filePath) && mkdir($filePath, 0755, true);
        // 写入文件
        return file_put_contents($filePath . date('Ymd') . '.log', $content, FILE_APPEND);
    } catch (\Exception $e) {
        return false;
    }
}

/**
 * 写入日志 (使用tp自带驱动记录到runtime目录中)
 * @param $value
 * @param string $type
 */
function log_write($value, $type = 'yoshop-info')
{
    $msg = is_string($value) ? $value : var_export($value, true);
    Log::record($msg, $type);
}

/**
 * curl请求指定url (get)
 * @param $url
 * @param array $data
 * @return mixed
 */
function curl($url, $data = [])
{
    // 处理get数据
    if (!empty($data)) {
        $url = $url . '?' . http_build_query($data);
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}

/**
 * curl请求指定url (post)
 * @param $url
 * @param array $data
 * @return mixed
 */
function curlPost($url, $data = [])
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

if (!function_exists('array_column')) {
    /**
     * array_column 兼容低版本php
     * (PHP < 5.5.0)
     * @param $array
     * @param $columnKey
     * @param null $indexKey
     * @return array
     */
    function array_column($array, $columnKey, $indexKey = null)
    {
        $result = array();
        foreach ($array as $subArray) {
            if (is_null($indexKey) && array_key_exists($columnKey, $subArray)) {
                $result[] = is_object($subArray) ? $subArray->$columnKey : $subArray[$columnKey];
            } elseif (array_key_exists($indexKey, $subArray)) {
                if (is_null($columnKey)) {
                    $index = is_object($subArray) ? $subArray->$indexKey : $subArray[$indexKey];
                    $result[$index] = $subArray;
                } elseif (array_key_exists($columnKey, $subArray)) {
                    $index = is_object($subArray) ? $subArray->$indexKey : $subArray[$indexKey];
                    $result[$index] = is_object($subArray) ? $subArray->$columnKey : $subArray[$columnKey];
                }
            }
        }
        return $result;
    }
}

/**
 * 多维数组合并
 * @param $array1
 * @param $array2
 * @return array
 */
function array_merge_multiple($array1, $array2)
{
    $merge = $array1 + $array2;
    $data = [];
    foreach ($merge as $key => $val) {
        if (
            isset($array1[$key])
            && is_array($array1[$key])
            && isset($array2[$key])
            && is_array($array2[$key])
        ) {
            $data[$key] = array_merge_multiple($array1[$key], $array2[$key]);
        } else {
            $data[$key] = isset($array2[$key]) ? $array2[$key] : $array1[$key];
        }
    }
    return $data;
}

/**
 * 二维数组排序
 * @param $arr
 * @param $keys
 * @param bool $desc
 * @return mixed
 */
function array_sort($arr, $keys, $desc = false)
{
    $key_value = $new_array = array();
    foreach ($arr as $k => $v) {
        $key_value[$k] = $v[$keys];
    }
    if ($desc) {
        arsort($key_value);
    } else {
        asort($key_value);
    }
    reset($key_value);
    foreach ($key_value as $k => $v) {
        $new_array[$k] = $arr[$k];
    }
    return $new_array;
}

/**
 * 数据导出到excel(csv文件)
 * @param $fileName
 * @param array $tileArray
 * @param array $dataArray
 */
function export_excel($fileName, $tileArray = [], $dataArray = [])
{
    ini_set('memory_limit', '512M');
    ini_set('max_execution_time', 0);
    ob_end_clean();
    ob_start();
    header("Content-Type: text/csv");
    header("Content-Disposition:filename=" . $fileName);
    $fp = fopen('php://output', 'w');
    fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));// 转码 防止乱码(比如微信昵称)
    fputcsv($fp, $tileArray);
    $index = 0;
    foreach ($dataArray as $item) {
        if ($index == 1000) {
            $index = 0;
            ob_flush();
            flush();
        }
        $index++;
        fputcsv($fp, $item);
    }
    ob_flush();
    flush();
    ob_end_clean();
}

/**
 * 获取当前系统版本号
 * @return mixed|null
 * @throws Exception
 */
function get_version()
{
    static $version = null;
    if ($version) {
        return $version;
    }
    $file = dirname(ROOT_PATH) . '/version.json';
    if (!file_exists($file)) {
        throw new Exception('version.json not found');
    }
    $version = json_decode(file_get_contents($file), true);
    if (!is_array($version)) {
        throw new Exception('version cannot be decoded');
    }
    return $version['version'];
}

/**
 * 获取全局唯一标识符
 * @param bool $trim
 * @return string
 */
function getGuidV4($trim = true)
{
    // Windows
    if (function_exists('com_create_guid') === true) {
        $charid = com_create_guid();
        return $trim == true ? trim($charid, '{}') : $charid;
    }
    // OSX/Linux
    if (function_exists('openssl_random_pseudo_bytes') === true) {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    // Fallback (PHP 4.2+)
    mt_srand((double)microtime() * 10000);
    $charid = strtolower(md5(uniqid(rand(), true)));
    $hyphen = chr(45);                  // "-"
    $lbrace = $trim ? "" : chr(123);    // "{"
    $rbrace = $trim ? "" : chr(125);    // "}"
    $guidv4 = $lbrace .
        substr($charid, 0, 8) . $hyphen .
        substr($charid, 8, 4) . $hyphen .
        substr($charid, 12, 4) . $hyphen .
        substr($charid, 16, 4) . $hyphen .
        substr($charid, 20, 12) .
        $rbrace;
    return $guidv4;
}

//腾讯转百度坐标转换
function coordinate_switchf($a, $b){
    $x = (double)$b ;
    $y = (double)$a;
    $x_pi = 3.14159265358979324;
    $z = sqrt($x * $x+$y * $y) + 0.00002 * sin($y * $x_pi);
    $theta = atan2($y,$x) + 0.000003 * cos($x*$x_pi);
    $gb = number_format($z * cos($theta) + 0.0065,6);
    $ga = number_format($z * sin($theta) + 0.006,6);
    return array(
        'Latitude'=>$ga,
        'Longitude'=>$gb
    );
}

//转化距离
function getdistance($address,$locations){
//    $location = explode(",",$address);
    $lat1 = $address['Latitude'];
    $lat2 = $locations['longitude'];
    $lng1 = $address['Longitude'];
    $lng2 = $locations['latitude'];
//    $lat1 = 118.7961268281444;
//    $lat2 = 118.781161;
//    $lng1 = 31.97631632521686;
//    $lng2 = 32.013023;
    $radLat1 = deg2rad($lat1);// deg2rad()函数将角度转换为弧度
    $radLat2 = deg2rad($lat2);
    $radLng1 = deg2rad($lng1);
    $radLng2 = deg2rad($lng2);
    $a = $radLat1 - $radLat2;
    $b = $radLng1 - $radLng2;
    $s = 2 * asin(sqrt(pow(sin($a / 2), 2)+cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137;
    $s = round($s,2);
    return $s;
}

/**
 * 加载语言包
 * @author: luffy
 * @date  : 2018-08-15
 */
function languageFun($shorthand) {
    $data = (object)array();
    $file = ROOT_PATH . '../lang/'.$shorthand.'.json';

    if ( is_file($file) ) {
        // 从文件中读取数据到PHP变量
        $json_string= file_get_contents($file);
        //获取json对象
        $data       = json_decode($json_string);
    }
    return $data;
}


/**
 * 获取排列数组
 * Created by PhpStorm.
 * Author: fup
 * Date: 2019-08-22
 * Time: 16:19
 */
function arrangement($a, $m)
{

    $r = array();

    $n = count($a);
    if ($m <= 0 || $m > $n) {
        return $r;
    }

    for ($i = 0; $i < $n; $i++) {
        $b = $a;
        $t = array_splice($b, $i, 1);
        if ($m == 1) {
            $r[] = $t;
        } else {
            $c = arrangement($b, $m - 1);
            foreach ($c as $v) {
                $r[] = array_merge($t, $v);
            }
        }
    }

    return $r;
}

/**
 * 获取对应门店代码
 * @author: luffy
 * @date  : 2019-07-06
 */
function getMerchantID($store_id) {
    //门店代码
    $store_code = array(
        '100110000044034',       //浙江衢州艾美家居有限公司衢州柯城上街分公司
        '100110000044035',       //浙江衢州艾美家居有限公司衢州柯城花园分公司
        '100110000044036',       //浙江衢州艾美家居有限公司衢州柯城南区分公司
        '100110000044037',       //浙江衢州亓茶餐饮有限公司
        '100110000044038',       //浙江衢州亓茶餐饮有限公司柯城中河沿分公司
        '100110000044039',       //浙江衢州亓茶餐饮有限公司西区分公司
        '100110000044040',       //浙里美
        '100110000048221',       //衢州艾美睿品牌管理有限公司吾悦广场分公司
        '100110000048222'        //衢州艾美睿品牌管理有限公司衢江海力大道分公司
    );
    $MerchantID = '';
    if( $store_id == 82 ){
        $MerchantID = $store_code[2];
    } elseif( $store_id == 78 ) {
        $MerchantID = $store_code[5];
    } elseif( $store_id == 94 || $store_id == 93 ) {
        $MerchantID = $store_code[1];
    } elseif( $store_id == 76 ) {
        $MerchantID = $store_code[0];
    } elseif( $store_id == 98 || $store_id == 72 ) {
        $MerchantID = $store_code[3];
    } elseif( $store_id == 99 || $store_id == 188 ) {
        $MerchantID = $store_code[6];
    } elseif( $store_id == 80 ) {
        $MerchantID = $store_code[7];
    } elseif( $store_id == 92 ) {
        $MerchantID = $store_code[8];
    } elseif( $store_id == 100 ) {
        $MerchantID = $store_code[4];
    }
    return $MerchantID;
} 

/**
 * 生成验证码
 * Created by PhpStorm.
 * Author: fup
 * Date: 2019-09-12
 * Time: 11:32
 */
function getCode($length = 6){
    $min = pow(10, ($length - 1));
    $max = pow(10, $length) - 1;
    return mt_rand($min, $max);
}