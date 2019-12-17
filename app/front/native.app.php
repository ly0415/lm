<?php

/**
 * 扫码支付
 * @author wanyan
 * @date 2017/07/19
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class NativeApp extends BaseFrontApp {

    private $orderMod;
    private $mstored;
    private $storeMod;
    private $lang;
    private $orderGoodsMod;
    private $cartMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        ini_set('date.timezone', 'Asia/Shanghai');
        include ROOT_PATH . "/includes/libraries/WxPaysdk/phpqrcode/phpqrcode.php";
        include ROOT_PATH . "/includes/libraries/WxPaysdk/WxPay.Config.php";
        // var_dump(ROOT_PATH."/includes/libraries/WxPaysdk/WxPay.Config.php");die;
        $this->mstored = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : '';
        $this->lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : '';
        $this->orderMod = &m('order');
        $this->storeMod = &m('store');
        $this->orderGoodsMod=&m('orderGoods');
        $this->cartMod=&m('cart');
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
        $rs = $this->storeMod->getOne(array('cond' => "`id` = '{$this->mstored}'", 'fields' => "store_type,store_cate_id"));
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

    // 创建支付链接 -- 扫码支付模式二
    public function native() {
        //加载语言包
        $this->load($this->shorthand, 'comfirmOrder/index');
        $a = $this->langData;
        $this->assign('langdata', $this->langData);
        $order_id = !empty($_REQUEST['order_id']) ? $_REQUEST['order_id'] : '';
        $data['storeid'] = $this->mstored;
        $data['lang'] = $this->lang;
        $orderInfo =$this->orderMod->getOne(array('cond'=>"`order_sn` = '{$order_id}'",'fields'=>"order_amount"));
        $query=array(
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

        }
        $wxConfig = $this->getWxInfo();
        if (empty($wxConfig)) {
            $data['message'] = $a['WxPay_close'];
            $this->error_404($data, 'public/error.html');
        }
        $rs = $this->orderMod->isExist($order_id);
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
        $order_amount = $rs['order_amount'] * 100;
        $input->SetOut_trade_no($out_trade_no);
        $input->SetTotal_fee($order_amount);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
//          $input->SetNotify_url("http://www.njbsds.cn/bspm711/index.php?app=native&act=notify");
//          $input->SetNotify_url("http://www.njbsds.cn/bspm711/wxNotify/notify.php");
//        $input->SetNotify_url("http://59.110.220.255/bspm711/index/wxNotify/notify.html");
        $input->SetNotify_url("http://www.711home.net/index/wxNotify/notify_{$this->storeid}.html");
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id("123456789");
        $result = $notify->GetPayUrl($input);
        $url2 = $result["code_url"];
        $url2 = urlencode($url2);
        $this->assign('url2', $url2);
        $this->assign('order_sn', $order_id);
        $this->assign('storeid', $this->mstored);
        $this->assign('lang', $this->lang);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $this->display('native/orderPayWechat.html');
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
        //加载语言包
        $this->load($this->shorthand, 'comfirmOrder/index');
        $a = $this->langData;
        $order_sn = !empty($_REQUEST['out_trade_no']) ? htmlspecialchars($_REQUEST['out_trade_no']) : '';
        $storeid = !empty($_REQUEST['storeid']) ? intval($_REQUEST['storeid']) : '';
        $lang = !empty($_REQUEST['lang']) ? intval($_REQUEST['lang']) : '';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $rs = $this->orderMod->isExist($order_sn);
        if ($rs['order_state'] == 20) {
            $data = array(
                'status' => 1,
                'url' => "?app=userCenter&act=myOrder&storeid={$storeid}&lang={$lang}&auxiliary={$auxiliary}",
                'message' => $a['Pay_success'],
            );
            echo json_encode($data);
        }
    }

}
