<?php

require(ROOT_PATH . '/includes/libraries/paypal/vendor/autoload.php');

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\ShippingAddress;
//use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Exception\PayPalConnectionException;

/**
 * paypal 支付接口
 * @author wanyan
 * @date 2017/07/19
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class PayPalApp extends BaseFrontApp {

    private $orderMod;
    private $orderDetailMod;
    private $storeMod;
    private $storeId;
    private $langId;
    private $areaGoodMod;
    private $storeGoodItemPriceMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->orderMod = &m('order');
        $this->orderDetailMod = &m('orderDetail');
        $this->storeMod = &m('store');
        $this->storeId = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : '';
        $this->langId = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : '';
        $this->storeGoodItemPriceMod = &m('storeGoodItemPrice');
        $this->areaGoodMod = &m('areaGood');
    }

    /**
     * @comment 获取总站点的微信支付配置信息
     * @wanyan
     * @date 2018/1/8
     */
    public function getPayPalInfo() {
        $Tstore = '';
        $wxPay = array();
        $rs = $this->storeMod->getOne(array('cond' => "`id` = '{$this->storeId}'", 'fields' => "store_type,store_cate_id"));
        if ($rs['store_type'] == 1) {
            $Tstore = $this->storeid;
        } else {
            $info = $this->storeMod->getOne(array('cond' => "`store_cate_id` = '{$rs['store_cate_id']}' and `store_type` = 1", 'fields' => "id"));
            $Tstore = $info['id'];
        }

        $sql = "select pd.mkey,pd.key_name from " . DB_PREFIX . "pay as p left join " . DB_PREFIX . "pay_detail as pd on p.id = pd.pay_id where p.store_id = '{$Tstore}' and p.`code` = 'paypal' and p.is_use =1";
        $payInfo = $this->storeMod->querySql($sql);
        foreach ($payInfo as $k => $v) {
            $wxPay[$v['mkey']] = $v['key_name'];
        }
        return $wxPay;
    }

    /*
     *
     * start.php 启动
     *
     */

    public function start() {
        $payPalInfo = $this->getPayPalInfo();
//        $clientId = 'AbZ0kbMsjodCITJeDK5VyBg_xK7kYL83ghI8CmTJRrn1uoHWDhHYAXLG8CQz9nzZeZqaiVzwg_bXmNGN';
//        $clientSecret = 'ELq7DW-60eceGAWQ22IV-cPCAAGMggwLr1ydBdzse26203cz8hQDWq9BLt59M4depO4l6-NEo5wyqiZ7';
        $clientId = $payPalInfo['paypal_APPID'];
        $clientSecret = $payPalInfo['paypal_APPSECRET'];
        $apiContext = new ApiContext(
                new OAuthTokenCredential(
                $clientId, $clientSecret
                )
        );
        $apiContext->setConfig(
                array(
//                'mode' => 'sandbox',
                    'mode' => 'live',
                )
        );
        return $apiContext;
    }

    /**
     * 创建paypal 支付接口
     * @author wanyan
     * @date 2017/01/19
     */
    public function payment() {
        $apiContext = $this->start();
        $order_sn = $_REQUEST['order_id'];
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $payer = new Payer();
        $payer->setPaymentMethod("paypal");
        //$order_sn = "201801291609452987";
        $mainOrder = $this->orderMod->getOne(array('cond' => "`order_sn` = {$order_sn}", 'fields' => "`order_sn`,order_state,order_amount,shipping_fee,discount"));


        //        $fxOrder = $this->orderDetailMod->getData(array('cond'=>"`order_id` = {$order_sn}",'fields'=>"*"));
//        $sql = "SELECT SUM(rg.goods_pay_price*rg.goods_num) as subPrice from bs_order as r
//              LEFT JOIN bs_order_goods as rg ON r.order_sn = rg.order_id WHERE r.order_sn = '{$order_sn}'";
//        $priceInfo = $this->orderMod->querySql($sql);
//        $priceDiscount =sprintf("%.2f",($mainOrder['discount'] / 2)) ;
        $shipping = $mainOrder['shipping_fee']; //运费
        $price = $mainOrder['order_amount'];
        $total = $price + $shipping;
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $item = new Item();
        $item->setName('商品购买')
                ->setCurrency('USD')
                ->setQuantity(1)
                ->setPrice($total);

        $itemList = new ItemList();
        $itemList->setItems(array($item));

        $details = new Details();
        $details->setSubtotal($total);

        $amount = new Amount();
        $amount->setCurrency('USD')
                ->setTotal($total)
                ->setDetails($details);
        //$order_sn = 'wy'.date('YmdHis',time());
        $transaction = new Transaction();
        $transaction->setAmount($amount)
                ->setItemList($itemList)
                ->setDescription("iMei paypal made")
                ->setInvoiceNumber($order_sn); // 发票信息

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl("http://www.711home.net/index.php?app=paypal&act=exec&success=true&storeid=" . $this->storeId . "&lang=" . $this->langId . "&auxiliary=" . $auxiliary)
                ->setCancelUrl("http://www.711home.net/index.php?app=paypal&act=cancel&storeid=" . $this->storeId . "&lang=" . $this->langId . "&auxiliary=" . $auxiliary);


        $payment = new Payment();
        $payment->setIntent('sale')
//            ->setId($order_id)
                ->setPayer($payer)
                ->setRedirectUrls($redirectUrls)
                ->setTransactions(array($transaction));

        try {
            $payment->create($apiContext);
        } catch (PayPalConnectionException $e) {
            echo $e->getData();
            die();
        }

        $approvalUrl = $payment->getApprovalLink();
        header("Location: {$approvalUrl}");
    }

    /**

     * 创建paypal 回调成功地址
     * @author wanyan
     * @date 2017/01/19
     */
    public function exec() {
        $paypal = $this->start();
        if (!isset($_GET['success'], $_GET['paymentId'], $_GET['PayerID'])) {
            die();
        }
        if ((bool) $_GET['success'] === 'false') {

            echo 'Transaction cancelled!';
            die();
        }
        $this->assign('storeid', $_GET['storeid']);
        $this->assign('lang', $_GET['lang']);
        $langInfo = $this->getShorthand($this->langid);
        $this->load($langInfo['shorthand'], 'goods/goods');
        $this->assign('langdata', $this->langData);
        $paymentID = $_GET['paymentId'];
        $payerId = $_GET['PayerID'];
        $payment = Payment::get($paymentID, $paypal);
        $execute = new PaymentExecution();
        $execute->setPayerId($payerId);

        try {
            $result = $payment->execute($execute, $paypal);
            $returnData = json_decode($result, true);
            $order_sn = $returnData['transactions'][0]['invoice_number'];
            $state = $returnData['transactions'][0]['related_resources'][0]['sale']['state'];
            $create_time = $returnData['transactions'][0]['related_resources'][0]['sale']['create_time'];
            $trade_no = $returnData['transactions'][0]['related_resources'][0]['sale']['id'];
            $create_time = str_replace('T', ' ', $create_time);
            $create_time = str_replace('Z', '', $create_time);
            $create_time = strtotime($create_time);
            if ($state == 'completed') {
                // 主订单修改
                $data = array(
                    'pay_sn' => $trade_no,
                    'payment_code' => 'paypal',
                    'payment_time' => $create_time,
                    'order_state' => 20
                );
                // 子订单修改
                $cond = array(
                    'order_sn' => $order_sn
                );
                $detail = array(
                    'order_state' => 20
                );
                $res = $this->orderMod->doEditSpec($cond, $data);
                if ($res) {
                    $detailRes = $this->orderDetailMod->doEditSpec(array('order_id' => $order_sn), $detail);
                }
                // 更新库存
                $this->updateStock($order_sn);
                if ($detailRes) {
                    $this->display('public/success.html');
                }
            }
        } catch (Exception $e) {
            die($e);
        }
//        // echo '<script>alert("支付成功！感谢支持!");window.location.href="index"</script>';
        echo '支付成功！感谢支持!';
    }

    /**
     * 创建paypal 回调失败地址
     * @author wanyan
     * @date 2017/01/19
     */
    public function cancel() {
        $this->assign('storeid', $_GET['storeid']);
        $this->assign('lang', $_GET['lang']);
        $langInfo = $this->getShorthand($this->langid);
        $this->load($langInfo['shorthand'], 'goods/goods');
        $this->assign('langdata', $this->langData);
        $this->display('public/fail.html');
    }

    /**
     * 创建paypal 异步回调地址
     * @author wanyan
     * @date 2017/01/19
     */
    public function notifyUrl() {
        $raw_post_data = file_get_contents('php://input');
        file_put_contents(ROOT_PATH . "/logs/wanyan.txt", date("Y-m-d H:i:s") . "  " . $raw_post_data . "\r\n", FILE_APPEND);
        // STEP 1: read POST data
        // Reading POSTed data directly from $_POST causes serialization issues with array data in the POST.
        // Instead, read raw POST data from the input stream.
        // $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $myPost = array();
        foreach ($raw_post_array as $keyval) {
            $keyval = explode('=', $keyval);
            if (count($keyval) == 2)
                $myPost[$keyval[0]] = urldecode($keyval[1]);
        }
        // read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
        $req = 'cmd=_notify-validate';
        if (function_exists('get_magic_quotes_gpc')) {
            $get_magic_quotes_exists = true;
        }
        foreach ($myPost as $key => $value) {
            if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                $value = urlencode(stripslashes($value));
            } else {
                $value = urlencode($value);
            }
            $req .= "&$key=$value";
        }

        // Step 2: POST IPN data back to PayPal to validate
//        $ch = curl_init('https://ipnpb.sandbox.paypal.com/cgi-bin/webscr');
        $ch = curl_init('https://ipnpb.paypal.com/cgi-bin/webscr');
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
        // In wamp-like environments that do not come bundled with root authority certificates,
        // please download 'cacert.pem' from "https://curl.haxx.se/docs/caextract.html" and set
        // the directory path of the certificate as shown below:
        // curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
        if (!($res = curl_exec($ch))) {
            // error_log("Got " . curl_error($ch) . " when processing IPN data");
            curl_close($ch);
            exit;
        }
        curl_close($ch);


        // inspect IPN validation result and act accordingly
        if (strcmp($res, "VERIFIED") == 0) {
            $trade_no = $_POST['txn_id'];
            $order_sn = $_POST['invoice'];
            $payment_date = time() - (16 * 3600);
            $str = 'paypal订单号:' . $trade_no . "\r\n 订单号:" . $order_sn . " \r\n  支付时间:" . $payment_date;
            file_put_contents(ROOT_PATH . "/logs/wanyan.txt", date("Y-m-d H:i:s") . "  " . $str . "\r\n", FILE_APPEND);
            //主订单修改
            $data = array(
                'pay_sn' => $trade_no,
                'payment_code' => 'paypal',
                'payment_time' => $payment_date,
                'order_state' => 20
            );
            // 子订单修改
            $cond = array(
                'order_sn' => $order_sn
            );
            $detail = array(
                'order_state' => 20
            );
            $res = $this->orderMod->doEditSpec($cond, $data);
            if ($res) {
                $detailRes = $this->orderDetailMod->doEditSpec(array('order_id' => $order_sn), $detail);
                // 更新库存
                $this->updateStock($order_sn);
            }
        } else if (strcmp($res, "INVALID") == 0) {
            $error = '错误';
            file_put_contents(ROOT_PATH . "/logs/wanyan.txt", date("Y-m-d H:i:s") . "  " . $error . "\r\n", FILE_APPEND);
        }
    }

    // 更新规格库存 和 无规格库存
    public function updateStock($out_trade_no) {
        //  更新库存
        $sql = "SELECT sg.goods_id,sg.spec_key,sg.goods_num FROM " . DB_PREFIX . "order as r LEFT JOIN " . DB_PREFIX . "order_goods as sg ON  r.order_sn = sg.order_id WHERE sg.order_id =" . $out_trade_no;
        $orderRes = $this->areaGoodMod->querySql($sql);
        foreach ($orderRes as $k => $v) {
            if (!empty($v['spec_key'])) {
                $query_id = "select `id` from " . DB_PREFIX . "store_goods_spec_price where `store_goods_id` = '{$v['goods_id']}' ";
                $res_query = $this->storeGoodItemPriceMod->querySql($query_id);
                $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods_spec_price WHERE `store_goods_id` = '{$v['goods_id']}' and `key` = '{$v['spec_key']}'";
                $specInfo = $this->areaGoodMod->querySql($specInfoSql);

                $condition = array(
                    'goods_storage' => $specInfo[0]['goods_storage'] - $v['goods_num']
                );
                $this->storeGoodItemPriceMod->doEdit($res_query[0]['id'], $condition);
            } else {
                $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods WHERE `id` = '{$v['goods_id']}'";
                $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                $condition = array(
                    'goods_storage' => $specInfo[0]['goods_storage'] - $v['goods_num']
                );
                $this->areaGoodMod->doEdit($v['goods_id'], $condition);
            }
        }
    }

}
