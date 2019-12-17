<?php

/**
 * 扫码支付
 * @author wanyan
 * @date 2017/07/19
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class JsapiApp extends BaseWxApp
{

    private $storeMod;
    private $storeId;
    private $orderMod;
    private $pointOrderMod;
    private $orderGoodsMod;
    private $cartMod;
    private $amountLogMod;
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
        $this->orderMod = &m('order');
        $this->pointOrderMod=&m('pointOrder');
        $this->orderGoodsMod=&m('orderGoods');
        $this->cartMod=&m('cart');
        $this->amountLogMod = &m('amountLog');
        $this->storeId = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;
        $wxPayInfo = $this->getWxInfo();
        $wxConfig = new  WxPayConfig();
        $wxConfig::$APPID = trim($wxPayInfo['weixin_APPID']);
        $wxConfig::$MCHID = trim($wxPayInfo['weixin_account']);
        $wxConfig::$KEY = trim($wxPayInfo['weixin_KEY']);
        $wxConfig::$APPSECRET = trim($wxPayInfo['weixin_APPSECRET']);
        require_once  ROOT_PATH."/includes/libraries/WxPaysdk/WxPay.Api.php";
        require_once  ROOT_PATH."/includes/libraries/WxPaysdk/WxPay.NativePay.php";
        require_once  ROOT_PATH."/includes/libraries/WxPaysdk/WxPay.JsApiPay.php";
    }
    /**
     * @comment 获取总站点的微信支付配置信息
     * @wanyan
     * @date 2018/1/8
     */
    public function getWxInfo() {
        $Tstore = '';
        $wxPay = array();
        $rs = $this->storeMod->getOne(array('cond' => "`id` = '{$this->storeId}'", 'fields' => "store_type,store_cate_id"));
        if ($rs['store_type'] == 1) {
            $Tstore = $this->storeId;
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
        $order_id = !empty($_REQUEST['order_id']) ? htmlspecialchars($_REQUEST['order_id']) :'';
        $lang = !empty($_REQUEST['lang']) ? htmlspecialchars($_REQUEST['lang']) :'';
        $orderInfo =$this->orderMod->getOne(array('cond'=>"`order_sn` = '{$order_id}'",'fields'=>"order_amount,store_id"));
    /*    $query=array(
            'cond' =>"`order_id` = '{$order_id}' '",
            'fields' =>"*"
        );

        $orderGoodsInfo=$this->orderGoodsMod->getData($query);
        foreach($orderGoodsInfo as $k=>$v){
            $invalid=$this->cartMod->isInvalid($v['goods_id'],$v['spec_key']);
            if(empty($invalid)){
                $this->setData(array(),'0',$v['goods_name'].'商品库存不足');
            }else{
                if($invalid<$v['goods_num']){
                    $this->setData(array(),'0',$v['goods_name'].'商品库存不足');
                }
            }

        }*/
        //①、获取用户openid
        $tools = new JsApiPay();
        // if(isset($_COOKIE['wx_openid'])){
        //     $openId = $_COOKIE['wx_openid'];
        // }else{
            $openId = $tools->GetOpenid();
        // }
        //②、统一下单
        $order_amount = (float)$orderInfo['order_amount'] * 100;
        $number = buildNo(1);
        $order_id =  $order_id.'_'.$number[0];
        $input = new WxPayUnifiedOrder();
        $input->SetBody("商品购买");
        $input->SetAttach($this->storeId);
        $input->SetOut_trade_no($order_id);
        $input->SetTotal_fee($order_amount);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
//        $input->SetNotify_url(SITE_URL . "/index/wxNotify/notify_{$orderInfo['store_id']}.html");
        $input->SetNotify_url(SITE_URL."/wx/wxNotify/wxnotify.html");
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);
        $order = WxPayApi::unifiedOrder($input);

//        echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
//        printf_info($order);exit;
        $jsApiParameters = $tools->GetJsApiParameters($order);
        //获取共享收货地址js函数参数
        $editAddress = $tools->GetEditAddressParameters();

        $this->assign('jsApiParameters',$jsApiParameters);
        $this->assign('editAddress',$editAddress);
        $this->assign('order_sn',$order_id);
        $this->assign('store_id',$this->storeId);
        $this->assign('order_amount',number_format($order_amount/100,2));
        $this->assign('lang',$lang);
        $this->display('native/pay.html');
    }

    public function pointJsapi(){
        $order_id = !empty($_REQUEST['order_id']) ? htmlspecialchars($_REQUEST['order_id']) :'';
        $lang = !empty($_REQUEST['lang']) ? htmlspecialchars($_REQUEST['lang']) :'';
        $auxiliary=!empty($_REQUEST['auxiliary']) ? $_REQUEST['auxiliary'] : 0;

        $orderInfo =$this->pointOrderMod->getOne(array('cond'=>"`order_sn` = '{$order_id}'",'fields'=>"amount"));

        //①、获取用户openid
        $tools = new JsApiPay();
        // if(isset($_COOKIE['wx_openid'])){
        //     $openId = $_COOKIE['wx_openid'];
        // }else{
        $openId = $tools->GetOpenid();
        // }
        //②、统一下单
        $order_amount = (float)$orderInfo['amount']* 100;
        $input = new WxPayUnifiedOrder();
        $input->SetBody("睿积分购买");
        $input->SetAttach("711商城");
        $input->SetOut_trade_no($order_id);
        $input->SetTotal_fee($order_amount);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
        $input->SetNotify_url(SITE_URL . "/index/pointwxNotify/notify_{$this->storeId}.html");
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);
        $order = WxPayApi::unifiedOrder($input);

//        echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
//        printf_info($order);exit;
        $jsApiParameters = $tools->GetJsApiParameters($order);

      /*  //获取共享收货地址js函数参数
        $editAddress = $tools->GetEditAddiressParameters();*/

        $this->assign('auxiliary',$auxiliary);
        $this->assign('jsApiParameters',$jsApiParameters);
       /* $this->assign('editAddress',$editAddress);*/
        $this->assign('order_sn',$order_id);
        $this->assign('store_id',$this->storeId);
        $this->assign('order_amount',number_format($order_amount/100,2));
        $this->assign('lang',$lang);
        $this->display('native/pointPay.html');
    }

    public function amountJsapi(){
        $order_id = !empty($_REQUEST['order_id']) ? htmlspecialchars($_REQUEST['order_id']) :'';
        $lang = !empty($_REQUEST['lang']) ? htmlspecialchars($_REQUEST['lang']) :'';
        $auxiliary=!empty($_REQUEST['auxiliary']) ? $_REQUEST['auxiliary'] : 0;
        $orderInfo =$this->amountLogMod->getOne(array('cond'=>"`order_sn` = '{$order_id}'",'fields'=>"c_money"));
        //①、获取用户openid
        $tools = new JsApiPay();
        $openId = $tools->GetOpenid();
        //②、统一下单
        $order_amount = (float)$orderInfo['c_money']* 100;
        $input = new WxPayUnifiedOrder();
        $input->SetBody("余额充值");
        $input->SetAttach("711商城");
        $input->SetOut_trade_no($order_id);
        $input->SetTotal_fee($order_amount);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
        $input->SetNotify_url(SITE_URL . "/index/amountwxNotify/notify_{$this->storeId}.html");
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);
        $order = WxPayApi::unifiedOrder($input);
        $jsApiParameters = $tools->GetJsApiParameters($order);
        $this->assign('auxiliary',$auxiliary);
        $this->assign('jsApiParameters',$jsApiParameters);
        $this->assign('order_sn',$order_id);
        $this->assign('store_id',$this->storeId);
        $this->assign('order_amount',number_format($order_amount/100,2));
        $this->assign('lang',$lang);
        $this->display('recharge/amountPay.html');
    }

}

