<?php

/**
 * 公用方法
 * User: jh
 * Date: 2019/3/4
 * Time: 14:50
 */
class Utility
{
    /**
     * 去掉数组中的空值
     */
    public static function noEmpty(&$data)
    {
        foreach ($data as $k => $v) {
            if (empty($v)) {
                unset($data[$k]);
            }
        }
    }

    /**
     * 获取签名域
     */
    public static function getSign($pairs = array())
    {
        $pairs['Key'] = Config::$Key;
        ksort($pairs);
        $str_arr = array();
        foreach ($pairs as $k => $v) {
            $str_arr[] = $k . '=' . $v;
        }
        $str_str = implode('&', $str_arr);
        $sign = hash('sha256', $str_str);
        return $sign;
    }

    /**
     * 远程调用接口
     * @author jh
     * @date 2019-03-04
     */
    public static function doPost($url, $data = array())
    {
        if (is_array($data)) {
            $data = http_build_query($data);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);

        $times = 0;
        do {
            $result = curl_exec($ch);
            $times++;
            if ($result !== FALSE) {
                break;
            }
        } while ($times < 3);
        if ($result === FALSE) {
            writeLog('ERROR!--URL:' . $url . '--INFO:' . $data, 'shijicloud');
            echo "CURL Error:" . curl_error($ch) . "<br>";
        }
        curl_close($ch);
        return $result;
    }
}