<?php

/**
 * 扫码支付
 * @author wanyan
 * @date 2017/07/19
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class AliPayDkApp extends BaseStoreApp
{

    private $orderMod;
    private $storeMod;
    private $config;
    private $orderDetailMod;
    private $goodsSpecPriceMod;
    private $areaGoodMod;
    private $orderGoodsMod;
    private $giftGoodMod;
    private $storeGoodItemPriceMod;
    private $goodsMod;
    private $lang_id;


    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        /*    ini_set('date.timezone', 'Asia/Shanghai');
            include ROOT_PATH . '/includes/libraries/aliPay.lib.php';*/
        $this->orderMod = &m('order');
        $this->storeMod = &m('store');
        $this->orderDetailMod = &m('orderDetail');
        $this->goodsSpecPriceMod =&m('goodsSpecPrice');
        $this->storeGoodItemPriceMod = &m('storeGoodItemPrice');
        $this->areaGoodMod = &m('areaGood');
        $this->orderGoodsMod = &m('orderGoods');
        $this->giftGoodMod = &m('giftGood');
        $this->goodsMod =&m('goods');
        $this->lang_id = $_REQUEST['lang_id'] ? $_REQUEST['lang_id'] : 0;

    }

    /**
     * 初始化引入文件
     */
    public function init($store_id)
    {
        require_once ROOT_PATH . '/includes/alipay/config.php';
        require_once ROOT_PATH . '/includes/alipay/pagepay/service/AlipayTradeService.php';
        $this->config = $config;


        $payConfig = $this->getAliPayInfo($store_id);
        if ($payConfig) {
            $this->config['app_id'] = $payConfig['alipay_account'];
            $this->config['alipay_public_key'] = $payConfig['alipay_PID'];
            $this->config['merchant_private_key'] = $payConfig['alipay_KEY'];
        }
        //DEBUG模式
      /*  if (SYSTEM_DEBUG == TRUE) {
            //默认测试金额
            $this->config['pay_money'] = "0.01";
        }*/
        return $this->config;
    }


    /**
     * @comment 获取总站点的微信支付配置信息
     * @wanyan
     * @date 2018/1/8
     */
    public function getAliPayInfo($store_id)
    {
        $Tstore = '';
        $wxPay = array();

        $rs = $this->storeMod->getOne(array('cond' => "`id` = '{$store_id}'", 'fields' => "store_type,store_cate_id"));
        if ($rs['store_type'] == 1) {
            $Tstore = $store_id;
        } else {
            $info = $this->storeMod->getOne(array('cond' => "`store_cate_id` = '{$rs['store_cate_id']}' and `store_type` = 1", 'fields' => "id"));
            $Tstore = $info['id'];
        }

        $sql = "select pd.mkey,pd.key_name from " . DB_PREFIX . "pay as p left join " . DB_PREFIX . "pay_detail as pd on p.id = pd.pay_id where p.store_id = '{$Tstore}' and p.`code` = 'alipay' and p.is_use =1";
        $payInfo = $this->storeMod->querySql($sql);
        foreach ($payInfo as $k => $v) {
            $wxPay[$v['mkey']] = $v['key_name'];
        }
        return $wxPay;
    }

    /*
     * 支付宝支付接口
     * @auth wanyan
     * @date 2018-1-26
     */
    public function aliPay()
    {
        //初始化支付配置数据
        $this->init($this->storeId);
        require_once ROOT_PATH . '/includes/alipay/pagepay/buildermodel/AlipayTradePagePayContentBuilder.php';
        $order_id = !empty($_REQUEST['order_id']) ? htmlspecialchars($_REQUEST['order_id']) : '';
        $orderInfo = $this->orderMod->getOne(array('cond' => "`order_sn` = '{$order_id}'", 'fields' => "order_amount"));
        if($orderInfo['order_amount']<=0){
            $orderInfo['order_amount']=0.01;
        }
        $order_amoumt = $orderInfo['order_amount'];

        $out_trade_no =$order_id  ;
        $pay_title = !empty($_REQUEST['pay_title']) ? $_REQUEST['pay_title'] : '艾美商城';
        $pay_money = !empty($this->config['pay_money']) ? $this->config['pay_money'] : $order_amoumt;
        $goods_desc = !empty($_REQUEST['goods_desc']) ? $_REQUEST['goods_desc'] : '商品购买';
        //构造参数

        $payRequestBuilder = new AlipayTradePagePayContentBuilder();
        $payRequestBuilder->setSubject($pay_title);        //订单名称，必填
        $payRequestBuilder->setBody($goods_desc);          //商品描述，可空
        $payRequestBuilder->setTotalAmount($pay_money);    //付款金额，必填
        $payRequestBuilder->setOutTradeNo($out_trade_no);  //订单编号传值使用（订单id，用，隔开）
        $aop = new AlipayTradeService($this->config);

        /**
         * pagePay 电脑网站支付请求
         * @param $builder 业务参数，使用buildmodel中的对象生成。
         * @param $return_url 同步跳转地址，公网可以访问
         * @param $notify_url 异步通知地址，公网可以访问
         * @return $response 支付宝返回的信息
         */
        $response = $aop->pagePay($payRequestBuilder, $this->config['return_url'], $this->config['notify_url']);
        //输出表单
        return ($response);
    }

    /**
     * 支付宝同步回调
     * @author luffy
     * @date 2018-08-06
     */
    public function returnUrl()
    {
        //初始化支付配置数据

       $this->init(58);

        $arr = $_GET;
        unset($arr['app']);          //如果$_POST数据不为空的话
        unset($arr['act']);

        $alipaySevice = new AlipayTradeService($this->config);

        $alipaySevice->writeLog(var_export($arr, true));

        $result = $alipaySevice->check($arr);
        /* 实际验证过程建议商户添加以下校验。
        1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号，
        2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额），
        3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）
        4、验证app_id是否为该商户本身。
        */
//        $url="store.php?app=customerOrder&act=index";
        $url="store.php?app=order&act=index";
        if ($result) {//验证成功
            header("Location: ".$url);
        } else {
            //验证失败
            echo "验证失败";
        }
    }

    /**
     * 支付宝异步回调
     * @author luffy
     * @date 2018-08-06
     */
    public function notifyUrl()
    {
        $aliReturn = urldecode(file_get_contents("php://input"));
        $arr = $this->convertUrlArray($aliReturn);
        //根据订单号获取系统数据
        $orderInfo = $this->orderMod->getOne(array('cond' => "`order_sn` = '{$arr['out_trade_no']}'", 'fields' => "store_id"));
        $store_id = $orderInfo['store_id'];
        //初始化支付配置数据
        $this->init($store_id);
        unset($arr['app']);
        unset($arr['act']);
        $alipaySevice = new AlipayTradeService($this->config);
        $alipaySevice->writeLog(var_export($arr, true));
        $result = $alipaySevice->check($arr);
        /* 实际验证过程建议商户添加以下校验。
        1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号，
        2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额），
        3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）
        4、验证app_id是否为该商户本身。
        */
        if ($result) {//验证成功
            //file_put_contents(ROOT_PATH.'/app/front/log.txt',date ( "Y-m-d H:i:s" ) . "  " . var_export($_POST,true) . "\r\n", FILE_APPEND);
            //请在这里加上商户的业务逻辑程序代
            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            //商户订单号
            $out_trade_no = $_POST['out_trade_no'];
            //支付宝交易号
            $trade_no = $_POST['trade_no'];
            //交易状态
            $trade_status = $_POST['trade_status'];
            if ($_POST['trade_status'] == 'TRADE_FINISHED') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
                //如果有做过处理，不执行商户的业务程序
                //注意：
                //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
            } else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                //更新订单支付状态
                //商户订单号
                $out_trade_no = $arr['out_trade_no'];
                //支付宝交易号
                $trade_no = $arr['trade_no'];
                //交易状态
                $trade_status = $arr['trade_status'];
                // 支付时间
                $payTime = strtotime($arr['gmt_payment']);
                // 支付金额
                $total_amount = floatval($arr['total_amount']);
                $alipaySevice->writeLog($trade_no);


                if ($arr['trade_status'] == 'TRADE_FINISHED') {

                    //判断该笔订单是否在商户网站中已经做过处理
                    //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                    //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
                    //如果有做过处理，不执行商户的业务程序

                    //注意：
                    //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
                } else if ($trade_status == 'TRADE_SUCCESS') {
                    //判断该笔订单是否在商户网站中已经做过处理
                    //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                    //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
                    //如果有做过处理，不执行商户的业务程序
                    //注意：
                    //付款完成后，支付宝系统发送该交易状态通知
                    $rs = $this->orderMod->isExist($out_trade_no);

                    if (empty($rs['order_sn'])) {
                        echo '支付订单编号不存在';
                        exit;
                    }
                    if ($rs['order_state'] == 20) {
                        echo '该订单已被支付';
                        exit;
                    }
//            if($rs['order_amount'] != $total_amount){
//                echo '支付金额跟订单金额不一致';
//                exit;
//            }
                    // 主订单修改
                    $data = array(
                        'pay_sn' => $trade_no,
                        'payment_code' => 'aliPay',
                        'payment_time' => $payTime,
                        'order_state' => 20,
                        'Appoint' => 1, //1未被指定 2被指定
                        'install_time' => time(), //区域配送安装完成时间
                        'region_install' => 10, //10未配送 20已配送
                    );
                    // 子订单修改
                    $cond = array(
                        'order_sn' => $out_trade_no
                    );
                    $detail = array(
                        'order_state' => 20
                    );
                    $res = $this->orderMod->doEditSpec($cond, $data);
                    $alipaySevice->writeLog($res);
                    if ($res) {
                        //新订单表更新
                        $this->orderMod->update_pay_time($store_id, $out_trade_no, $trade_no, 1, $data['order_state']);
                        //分销订单
                        $fxOrderMod = &m('fxOrder');
                        $fxOrderMod->addFxOrderByOrderSn($out_trade_no, 1);

                        $detailRes = $this->orderDetailMod->doEditSpec(array('order_id' => $out_trade_no), $detail);
                        $alipaySevice->writeLog($detailRes);
                    }
                    //  更新库存
                    $sql = "SELECT sg.goods_id,sg.spec_key,sg.goods_num,sg.deduction,sg.good_id FROM " .
                        DB_PREFIX . "order as r LEFT JOIN " .
                        DB_PREFIX . "order_goods as sg ON  r.order_sn = sg.order_id WHERE sg.order_id =" . $out_trade_no;
                    $orderRes = $this->areaGoodMod->querySql($sql);
                    foreach ($orderRes as $k => $v) {
                        if (!empty($v['spec_key'])) {
                            if ($v['deduction'] == 1) {
                                $query_id = "select `id` from " . DB_PREFIX . "store_goods_spec_price where `goods_id` = '{$v['good_id']}' and `key` = '{$v['spec_key']}' ";
                                $res_query = $this->storeGoodItemPriceMod->querySql($query_id);
                                $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods_spec_price WHERE `goods_id` = '{$v['good_id']}' and `key` = '{$v['spec_key']}'";
                                $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                                foreach ($res_query as $key => $val) {
                                    $goodStorage = $specInfo[0]['goods_storage'] - $v['goods_num'];
                                    if ($goodStorage <= 0) {
                                        $goodStorage = 0;
                                    }
                                    $condition = array(
                                        'goods_storage' => $goodStorage
                                    );
                                    $res = $this->storeGoodItemPriceMod->doEdit($val['id'], $condition);
                                }
                                if ($res) {
                                    $infoSql = "select goods_storage,id from  " . DB_PREFIX . "store_goods WHERE `goods_id` = '{$v['good_id']}'";
                                    $Info = $this->areaGoodMod->querySql($infoSql);
                                    $goodsStorage = $Info[0]['goods_storage'] - $v['goods_num'];
                                    if ($goodsStorage <= 0) {
                                        $goodsStorage = 0;
                                    }
                                    $cond = array(
                                        'goods_storage' => $goodsStorage
                                    );
                                    foreach ($Info as $key1 => $val1) {
                                        $this->areaGoodMod->doEdit($val1['id'], $cond);
                                    }
                                }
                                $Sql = "select goods_storage from  " . DB_PREFIX . "goods_spec_price WHERE `goods_id` = '{$v['good_id']}' and `key` = '{$v['spec_key']}'";

                                $goodsSpec = $this->areaGoodMod->querySql($Sql);
                                $conditionalStorage = $goodsSpec[0]['goods_storage'] - $v['goods_num'];
                                if ($conditionalStorage <= 0) {
                                    $conditionalStorage = 0;
                                }
                                $conditional = array(
                                    'goods_storage' => $conditionalStorage
                                );
                                $goodsSpecSql = "update " . DB_PREFIX . "goods_spec_price set goods_storage = " . $conditional['goods_storage'] . " where goods_id=" . $v['good_id'] . " and `key` ='{$v['spec_key']}'";
                                $result = $this->goodsSpecPriceMod->doEditSql($goodsSpecSql);
                                if ($result) {
                                    $goodSql = "select goods_storage from  " . DB_PREFIX . "goods WHERE `goods_id` = '{$v['good_id']}'";
                                    $goodInfo = $this->areaGoodMod->querySql($goodSql);
                                    $goodCondStorage = $goodInfo[0]['goods_storage'] - $v['goods_num'];
                                    if ($goodCondStorage <= 0) {
                                        $goodCondStorage = 0;
                                    }
                                    $goodCond = array(
                                        'goods_storage' => $goodCondStorage
                                    );
                                    $this->goodsMod->doEdit($v['good_id'], $goodCond);
                                }
                            } else {
                                $query_id = "select `id` from " . DB_PREFIX . "store_goods_spec_price where `store_goods_id` = '{$v['goods_id']}' and `key` = '{$v['spec_key']}' ";
                                $res_query = $this->storeGoodItemPriceMod->querySql($query_id);
                                $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods_spec_price WHERE `store_goods_id` = '{$v['goods_id']}' and `key` = '{$v['spec_key']}'";
                                $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                                $conditionStorage = $specInfo[0]['goods_storage'] - $v['goods_num'];
                                if ($conditionStorage <= 0) {
                                    $conditionStorage = 0;
                                }
                                $condition = array(
                                    'goods_storage' => $conditionStorage
                                );
                                $res = $this->storeGoodItemPriceMod->doEdit($res_query[0]['id'], $condition);
                                if ($res) {
                                    $infoSql = "select goods_storage from  " . DB_PREFIX . "store_goods WHERE `id` = '{$v['goods_id']}'";
                                    $Info = $this->areaGoodMod->querySql($infoSql);
                                    $condStorage = $Info[0]['goods_storage'] - $v['goods_num'];
                                    if ($condStorage <= 0) {
                                        $condStorage = 0;
                                    }
                                    $cond = array(
                                        'goods_storage' => $condStorage
                                    );
                                    $this->areaGoodMod->doEdit($v['goods_id'], $cond);
                                }
                            }
                        } else {
                            if ($v['deduction'] == 1) {
                                $infoSql = "select goods_storage,id from  " . DB_PREFIX . "store_goods WHERE `goods_id` = '{$v['good_id']}'";
                                $Info = $this->areaGoodMod->querySql($infoSql);
                                $condStorage = $Info[0]['goods_storage'] - $v['goods_num'];
                                if ($condStorage <= 0) {
                                    $condStorage = 0;
                                }
                                $cond = array(
                                    'goods_storage' => $condStorage
                                );
                                foreach ($Info as $key1 => $val1) {
                                    $this->areaGoodMod->doEdit($val1['id'], $cond);
                                }
                                $goodSql = "select goods_storage from  " . DB_PREFIX . "goods WHERE `goods_id` = '{$v['good_id']}'";

                                $goodInfo = $this->areaGoodMod->querySql($goodSql);
                                $goodCondStorage = $goodInfo[0]['goods_storage'] - $v['goods_num'];
                                if ($goodCondStorage <= 0) {
                                    $goodCondStorage = 0;
                                }
                                $goodCond = array(
                                    'goods_storage' => $goodCondStorage
                                );
                                $this->goodsMod->doEdit($v['good_id'], $goodCond);
                            } else {
                                $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods WHERE `id` = '{$v['goods_id']}'";
                                $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                                $condition = $specInfo[0]['goods_storage'] - $v['goods_num'];
                                if ($condition <= 0) {
                                    $condition = 0;
                                }
                                $condition = array(
                                    'goods_storage' => $condition
                                );
                                $this->areaGoodMod->doEdit($v['goods_id'], $condition);
                            }

                        }
                    }
                }
                //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
                echo "success";    //请不要修改或删除
            } else {
                //验证失败
                echo "fail";
            }
        }
    }

    public  function convertUrlArray($str)
    {
        $queryParts = explode('&', $str);
        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }
        return $params;
    }

}
