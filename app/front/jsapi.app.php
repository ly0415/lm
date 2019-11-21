<?php

/**
 * 扫码支付
 * @author wanyan
 * @date 2017/07/19
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class JsapiApp extends BaseFrontApp
{

    private $storeMod;
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        ini_set('date.timezone','Asia/Shanghai');
        include ROOT_PATH."/includes/libraries/WxPaysdk/WxPay.Config.php";
        // var_dump(ROOT_PATH."/includes/libraries/WxPaysdk/WxPay.Config.php");die;
        $this->storeMod = &m('store');
//        $wxPayInfo = $this->getWxInfo();
        $wxConfig = new  WxPayConfig();
        $wxConfig::$APPID = "wxa07a37aef375add1";
        $wxConfig::$MCHID = "1334480801";
        $wxConfig::$KEY = "Sem68GvhBu2ag5ncyJxsbDrXzZAHT3VK";
        $wxConfig::$APPSECRET = "ce3e519287e84c68fbd63b74b7ea501f";
//        $wxConfig::$APPID = trim($wxPayInfo['weixin_APPID']);
//        $wxConfig::$MCHID = trim($wxPayInfo['weixin_account']);
//        $wxConfig::$KEY = trim($wxPayInfo['weixin_KEY']);
//        $wxConfig::$APPSECRET = trim($wxPayInfo['weixin_APPSECRET']);
        require_once  ROOT_PATH."/includes/libraries/WxPaysdk/WxPay.Api.php";
        require_once  ROOT_PATH."/includes/libraries/WxPaysdk/WxPay.NativePay.php";
        require_once  ROOT_PATH."/includes/libraries/WxPaysdk/WxPay.JsApiPay.php";
    }

    public function getWxInfo() {
        $Tstore = '';
        $wxPay = array();
        $rs = $this->storeMod->getOne(array('cond' => "`id` = '{$this->storeid}'", 'fields' => "store_type,store_cate_id"));
        if ($rs['store_type'] == 1) {
            $Tstore = $this->storeid;
        } else {
            $info = $this->storeMod->getOne(array('cond' => "`store_cate_id` = '{$rs['store_cate_id']}' and `store_type` = 1", 'fields' => "id"));
            $Tstore = $info['id'];
        }

        $sql = "select pd.mkey,pd.key_name from " . DB_PREFIX . "pay as p left join " . DB_PREFIX . "pay_detail as pd on p.id = pd.pay_id where p.store_id = '{$Tstore}' and p.`code` = 'weixin' and p.is_use =1";
        $payInfo = $this->storeMod->querySql($sql);
        foreach ($payInfo as $k => $v) {
            $wxPay[$v['mkey']] = $v['key_name'];
        }
        return $wxPay;
    }
    // 创建jsapi 支付
    public function jsapi(){
            echo '1111';die;
        //①、获取用户openid
        $tools = new JsApiPay();
        $openId = $tools->GetOpenid();
        //②、统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetBody("test");
        $input->SetAttach("test");
        $input->SetOut_trade_no(WxPayConfig::$MCHID.date("YmdHis"));
        $input->SetTotal_fee("1");
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
        $input->SetNotify_url("http://www.711home.net/index/wxNotify/notify.html");
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);
        $order = WxPayApi::unifiedOrder($input);
//        echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
//        printf_info($order);
        $jsApiParameters = $tools->GetJsApiParameters($order);

        //获取共享收货地址js函数参数
        $editAddress = $tools->GetEditAddressParameters();
        $this->assign('jsApiParameters',$jsApiParameters);
        $this->assign('editAddress',$editAddress);
        $this->display('native/jsapi.html');
    }
}

