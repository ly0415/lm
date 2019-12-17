<?php

/**
 * 企业付款到零钱
 * @author wanyan
 * @date 2018/2/06
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class EPayApp extends BaseWxApp
{

    private $storeMod;
    private $storeId;
    /**
     * 构造函数
     */
    public function __construct()
    {

    }

    /**
     * 企业付款到零钱
    */
    public function pay()
    {

        $open_id = $this->__GetOpenid();
        //结算
        $data = array(
            'mch_appid' => 'wxa07a37aef375add1',//商户账号appid
            'mchid' => '1334480801',//商户号
            'nonce_str' => $this->getNonceStr(32),//随机字符串
            'partner_trade_no' => uniqid(),//商户订单号
            'openid' => $open_id,//用户openid
            'check_name' => 'NO_CHECK',//校验用户姓名选项,
//            're_user_name' => '蒲松林',//收款用户姓名
            'amount' => '1',//金额
            'desc' => '企业付款到零钱测试1',//企业付款描述信息
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],//Ip地址
        );
        $secrect_key = 'Sem68GvhBu2ag5ncyJxsbDrXzZAHT3VK';///这个就是个API密码。32位的。。随便MD5一下就可以了
        $data = array_filter($data);
        ksort($data);
        $str = '';
        foreach ($data as $k => $v) {
            $str .= $k . '=' . $v . '&';
        }
        $str .= 'key=' . $secrect_key;
        $data['sign'] = strtoupper(md5($str));
        $xml = $this->arraytoxml($data);
        // echo $xml;
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        $res = $this->curl($xml, $url);
        $return = $this->xmltoarray($res);
        print_r($return);
    }
    // echo getcwd().'/cert/apiclient_cert.pem';die;
    public function unicode() {
        $str = uniqid(mt_rand(),1);
        $str=sha1($str);
        return md5($str);
    }
    public function arraytoxml($data){
        $str='<xml>';
        foreach($data as $k=>$v) {
            $str.='<'.$k.'>'.$v.'</'.$k.'>';
        }
        $str.='</xml>';
        return $str;
    }
    public function xmltoarray($xml) {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring),true);
        return $val;
    }

    public function curl($param="",$url) {

        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();                                      //初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);                 //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);                    //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);            //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);                      //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);           // 增加 HTTP Header（头）里的字段
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);        // 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch,CURLOPT_SSLCERT,ROOT_PATH.'/includes/libraries/WxPaysdk/cert/apiclient_cert.pem'); //这个是证书的位置绝对路径
        curl_setopt($ch,CURLOPT_SSLKEY,ROOT_PATH.'/includes/libraries/WxPaysdk/cert/apiclient_key.pem'); //这个也是证书的位置绝对路径
        $data = curl_exec($ch);                                 //运行curl
        curl_close($ch);
        return $data;
    }

    /**
     *
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return 产生的随机字符串
     */
    public function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

}