<?php

/**
 * 订单中心
 * @author lvji
 *
 */
//include_once 'sms.app.php';
//include_once 'mail.app.php';
class fxPaymentApp extends BaseWxApp {

    private $orderMod;
    private $orderGoodsMod;
    private $userMod;
    private $storeMod;

    public function __construct() {
        parent::__construct();
        ini_set('date.timezone', 'Asia/Shanghai');
        include ROOT_PATH . "/includes/libraries/WxPaysdk/phpqrcode/phpqrcode.php";
        include ROOT_PATH . "/includes/libraries/WxPaysdk/WxPay.Config.php";
        $this->orderMod = &m('order');
        $this->orderGoodsMod = &m('orderGoods');
        $this->userMod = &m('user');
        $this->storeMod = &m('store');
        $wxPayInfo = $this->getWxInfo();
        $wxConfig = new WxPayConfig();
        $wxConfig::$APPID = trim($wxPayInfo['weixin_APPID']);
        $wxConfig::$MCHID = trim($wxPayInfo['weixin_account']);
        $wxConfig::$KEY = trim($wxPayInfo['weixin_KEY']);
        $wxConfig::$APPSECRET = trim($wxPayInfo['weixin_APPSECRET']);
        require_once ROOT_PATH . "/includes/libraries/WxPaysdk/WxPay.Api.php";
        require_once ROOT_PATH . "/includes/libraries/WxPaysdk/WxPay.NativePay.php";
        require_once ROOT_PATH . "/includes/libraries/WxPaysdk/WxPay.JsApiPay.php";
    }

    /**
     * @comment 获取总站点的微信支付配置信息
     * @wangshuo
     * @date 2018/11/26
     */
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

    /*
     * 我的订单分享付款价格
     * @author wangs
     * @2017-10-24 13:59:10
     */

    public function index() {
       //语言包
        $this->load($this->shorthand, 'WeChat/goods');
        $this->assign('langdata', $this->langData);
        //币种符号
        $symbol = $this->symbol;
        $this->assign('symbol', $symbol);
        $userId = $this->userId;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $daifu = !empty($_REQUEST['daifu']) ? $_REQUEST['daifu'] : 0;  //是否代付
        $orderid = $_REQUEST['orderid']; //订单id
        $where = '  order_id = ' . $orderid;
         //查找店铺是总代理还是经销商，总代理显示所有订单，
        $storeMod = &m('store');
        $sqlStore = 'select store_type  from  ' . DB_PREFIX . 'store where id  = ' . $storeid;
        $dataStore = $storeMod->querySql($sqlStore);
        if ($dataStore[0]['store_type'] == 1) {
            //总代理
            //列表页数据
            $sql = 'select * from ' . DB_PREFIX . 'order'
                    . ' where' . $where . ' order by order_id desc';
        } else {
            //经销商
            //列表页数据
            $sql = 'select * from ' . DB_PREFIX . 'order'
                    . ' where' . $where . ' and store_id =' . $storeid
                    . ' order by order_id desc';
        }
        $data = $this->orderMod->querySql($sql);
        //获取订单所有商品
        $cond= array(
            "cond" => " `order_id` like '{$data[0]['order_sn']}%'"
        );


        $orderGoodsMod =&m('orderGoods');
        $list = $orderGoodsData = $orderGoodsMod->getData($cond);
/*        $sql = "select o.*,o.goods_id as ogoods_id,l.* from "
                . DB_PREFIX . "order_goods as o left join "
                . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
                . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id "
                . " where o.order_id= '{$data[0]['order_sn']}' and lang_id = " . $lang;
        $list = $this->orderGoodsMod->querySql($sql);*/

        $data[0]['goods_list'] = $list;
        //查找用户表昵称
         $user_sql = 'select username from ' . DB_PREFIX . 'user' . ' where  id =' . $data[0]['buyer_id'];
         $username = $this->userMod->querySql($user_sql);
         $this->assign('username', $username[0]);
        $this->assign('info', $data[0]);
      //支付二维码生成
        $order_id = $data[0]['order_sn'];
        $wxConfig = $this->getWxInfo();
        if (empty($wxConfig)) {
            $data['message'] = $a['WxPay_close'];
            $this->error_404($data, 'public/error.html');
        }
        $rs = $this->orderMod->isExist($order_id);

        if (empty($order_id)) {
            $data['message'] = '支付参数缺少';
            $this->error_404($data, 'public/error.html');
        } else {
            if (empty($rs['order_sn'])) {
                $data['message'] = '订单编号不存在，请重新下单';
                $this->error_404($data, 'public/error.html');
            }
        }
        $store_id = $data[0]['store_id'];
        if ($daifu == 1) {
            $attach = $store_id.',daifu';
        } else {
            $attach = $store_id;
        }
        $notify = new NativePay();
        $input = new WxPayUnifiedOrder();
        $input->SetBody("商品购买");
        $input->SetAttach($attach);
        //$out_trade_no = WxPayConfig::$MCHID.date("YmdHis");
        $number = buildNo(1);
        $order_id =  $order_id.'_'.$number[0];
        $out_trade_no = $order_id;

        $order_amount = $rs['order_amount'] * 100;  //$rs['order_amount']

        $input->SetOut_trade_no($out_trade_no);
        $input->SetTotal_fee($order_amount);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
        #$input->SetNotify_url("http://www.711home.net/wx/wxNotify/notify_{$storeid}.html");
	    $input->SetNotify_url(SITE_URL."/wx/wxNotify/wxnotify.html");
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id("123456789");
        $result = $notify->GetPayUrl($input);
        $url2 = $result["code_url"];
        $url2 = urlencode($url2);
        $this->assign('url2', $url2);
        $this->assign('order_sn', $order_id);
         $this->assign('id', $orderid);
        
        //按钮支付
//        $type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : 1;
//        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;
//        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->lang_id;
//        $orderid = $_REQUEST['orderid'];
//        $where = '  order_id =' . $orderid;
//        $where .= ' and mark =' . 1;
//        $storeMod = &m('store');
//        $sqlStore = 'select store_type  from  ' . DB_PREFIX . 'store where id  = ' . $storeid;
//        $dataStore = $storeMod->querySql($sqlStore);
//        if ($dataStore[0]['store_type'] == 1) {
//            //总代理
//            //列表页数据
//            $sql = 'select order_id,order_sn,store_id,store_name,buyer_id,add_time,order_amount from ' . DB_PREFIX . 'order'
//                    . ' where' . $where . ' order by order_id desc';
//        } else {
//            //经销商
//            //列表页数据
//            $sql = 'select order_id,order_sn,store_id,store_name,buyer_id,add_time,order_amount from ' . DB_PREFIX . 'order'
//                    . ' where' . $where . ' and store_id =' . $storeid
//                    . ' order by order_id desc';
//        }
//        $data = $this->orderMod->querySql($sql);
//        //获取用户的头像
//        $sql_uname = 'select headimgurl from ' . DB_PREFIX . 'user'
//                . ' where  id =' . $data[0]['buyer_id'];
//        $res_url = $this->orderGoodsMod->querySql($sql_uname);
//        $data[0]['buyer_url'] = $res_url[0]['headimgurl'];
//        //获取订单所有商品
//        $sql = "select l.goods_name,o.goods_image,o.goods_pay_price,o.goods_num from "
//                . DB_PREFIX . "order_goods as o left join "
//                . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
//                . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id "
//                . " where o.order_id=" . $data[0]['order_sn'] . " and lang_id = " . $lang;
//        $list = $this->orderGoodsMod->querySql($sql);
//        $data[0]['goods_list'] = $list;
//        $this->assign('info', $data[0]);
//        $this->assign('type', $type);
//        $this->assign('storeid', $storeid);
//        $this->assign('lang', $lang);
//        $this->assign('symbol', $this->symbol);
//        if($type ==2){
//             $orderInfo =$this->orderMod->getOne(array('cond'=>"`order_sn` = '{$data[0]['order_sn']}'",'fields'=>"order_amount,store_id"));
//             $tools = new JsApiPay();
//             $openId = $tools->GetOpenid();
//        }
        $this->display("order/payfor.html");
    }

    public function qrcode() {
        $url = urldecode($_GET["data"]);
        QRcode::png($url);
    }

    /**
     *  查询订单
     */
    public function checkStatus() {
        //加载语言包
        $this->load($this->shorthand, 'comfirmOrder/index');
        $a = $this->langData;
        $order_sn = !empty($_REQUEST['out_trade_no']) ? htmlspecialchars($_REQUEST['out_trade_no']) : '';
        $rs = $this->orderMod->isExist($order_sn);
        if ($rs['order_state'] == 20) {
            $data = array(
                'status' => 1,
                'url' => "?app=order&act=orderindex&storeid=".$this->storeid,
                'message' => $a['Pay_success'],
            );
            echo json_encode($data);
        }
    }

}
