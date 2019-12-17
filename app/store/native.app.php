<?php

/**
 * 扫码支付
 * @author wanyan
 * @date 2017/07/19
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class NativeApp extends BaseStoreApp {

    private $orderMod;
    private $storeMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        ini_set('date.timezone', 'Asia/Shanghai');
        include ROOT_PATH . "/includes/libraries/WxPaysdk/phpqrcode/phpqrcode.php";
        include ROOT_PATH . "/includes/libraries/WxPaysdk/WxPay.Config.php";
        // var_dump(ROOT_PATH."/includes/libraries/WxPaysdk/WxPay.Config.php");die;
        $this->orderMod = &m('order');
        $this->storeMod = &m('store');
        $wxPayInfo = $this->getWxInfo();
//        if(count($wxPayInfo) == 0){
//            echo "<script>alert('".$this->aLang['WxPay_close']."');</script>";
////            echo "<script>window.location.reload(); </script>";
//        }
//        var_dump($wxPayInfo);
//        die;
        $wxConfig = new WxPayConfig();
//        $wxConfig::$APPID = "wx80d07d72079c04db";
//        $wxConfig::$MCHID = "1415524702";
//        $wxConfig::$KEY = "83iJNv8SsrqLTPpWKV4Z8JZElkN9aknb";
//        $wxConfig::$APPSECRET = "cb06dfe09354ada01688cea7173b3c45";
        $wxConfig::$APPID = trim($wxPayInfo['weixin_APPID']);
        $wxConfig::$MCHID = trim($wxPayInfo['weixin_account']);
        $wxConfig::$KEY = trim($wxPayInfo['weixin_KEY']);
        $wxConfig::$APPSECRET = trim($wxPayInfo['weixin_APPSECRET']);
        require_once ROOT_PATH . "/includes/libraries/WxPaysdk/WxPay.Api.php";
        require_once ROOT_PATH . "/includes/libraries/WxPaysdk/WxPay.NativePay.php";
    }

    // 创建支付链接 -- 扫码支付模式二
    public function index() {
        $this->display('native/index.html');
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

    // 创建支付链接 -- 扫码支付模式二
    public function native() {
        //加载语言包
        $this->load($this->shorthand, 'comfirmOrder/index');
        $a = $this->langData;
        $this->assign('langdata', $this->langData);
        $pay_v = !empty($_REQUEST['pay_v']) ? htmlspecialchars(trim($_REQUEST['pay_v'])) : '';
        $order_id = !empty($_REQUEST['order_id']) ? $_REQUEST['order_id'] : '';
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '';
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : '';
        $wxConfig = $this->getWxInfo();
        if (empty($wxConfig)) {
            $data['message'] = $a['WxPay_close'];
            $this->error_404($data, 'public/error.html');
        }
        $rs = $this->orderMod->isExist($order_id);
        if($rs['order_amount']<=0){
            $rs['order_amount']=0.01;
        }
        if (empty($order_id)) {
            $data['message'] = $a['Payment_parameters'];
            $this->error_404($data, 'public/error.html');
        } else {
            if (empty($rs['order_sn'])) {
                $data['message'] = $a['order_order_sn'];
                $this->error_404($data, 'public/error.html');
            }
        }
        $notify = new NativePay();
        $input = new WxPayUnifiedOrder();
        $input->SetBody("商品购买");
        $input->SetAttach("艾美商城提供");
        //$out_trade_no = WxPayConfig::$MCHID.date("YmdHis");
        $out_trade_no = $order_id;
        $order_amount = $rs['order_amount'] * 100;  //$rs['order_amount']
        $input->SetOut_trade_no($out_trade_no);
        $input->SetTotal_fee($order_amount);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
//          $input->SetNotify_url("http://www.njbsds.cn/bspm711/index.php?app=native&act=notify");
//          $input->SetNotify_url("http://www.njbsds.cn/bspm711/wxNotify/notify.php");
//        $input->SetNotify_url("http://59.110.220.255/bspm711/index/wxNotify/notify.html");
        $input->SetNotify_url(SITE_URL . "/store/wxNotify/notify_{$this->storeId}.html");
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id("123456789");
        $result = $notify->GetPayUrl($input);
        $url2 = $result["code_url"];
        $url2 = urlencode($url2);
        $this->assign('url2', $url2);
        $this->assign('order_sn', $order_id);
         $this->assign('id', $id);
        $this->assign('lang_id', $lang_id);
        if ($pay_v == 2) {
            $this->display('guestList/orderPayWechat2.html');
        } else {
            $this->display('guestList/orderPayWechat.html');
        }
    }

    public function qrcode() {
        $url = urldecode($_GET["data"]);
        QRcode::png($url);
    }

//    // 微信回调
//    public function notify(){
//        require_once  ROOT_PATH."/includes/libraries/WxPaysdk/log.php";
//        require_once  ROOT_PATH."/includes/libraries/WxPaysdk/WxPay.Notify.php";
//        $logHandler= new CLogFileHandler(ROOT_PATH."/app/logs/".date('Y-m-d').'.log');
//        $log = Log::Init($logHandler, 15);
//        $rs = $GLOBALS['HTTP_RAW_POST_DATA'] ;
//        Log::DEBUG("begin notify".$rs);
//        $notify = new PayNotifyCallBack();
//        $notify->Handle(true);
//    }
    /**
     *  查询订单
     */
    public function checkStatus() {
        //加载语言
        $this->load($this->shorthand, 'comfirmOrder/index');
        $a = $this->langData;
        $lang_id = !empty($_REQUEST['lang_id']) ? htmlspecialchars($_REQUEST['lang_id']) : '0';
        $order_sn = !empty($_REQUEST['out_trade_no']) ? htmlspecialchars($_REQUEST['out_trade_no']) : '';
        $rs = $this->orderMod->isExist($order_sn);
        if (in_array($rs['order_state'], array(20,40))) {
            $data = array(
                'status' => 1,
//                'url' => "?app=customerOrder&act=index&lang_id={$lang_id}",
                'url' => "?app=order&act=index&lang_id={$lang_id}",
                'message' => $a['Pay_success'],
            );
            echo json_encode($data);
        }
    }

}
