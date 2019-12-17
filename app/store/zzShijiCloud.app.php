<?php

/**
 * 石基支付平台，测试入口文件
 * User: jh
 * Date: 2019/3/5
 * Time: 15:26
 */
class ZzShijiCloudApp extends BaseStoreApp
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
     * 聚合支付测试入口
     */
    public function juhepay()
    {
        include_once ROOT_PATH . "/includes/shijicloud/Juhepay.php";
        $payMod = new Juhepay();
        $res = $payMod->setMerchantTxnNo(rand(100, 999) . time())
            ->setTxnAmt(0.01)
            ->setNotifyUrl(SITE_URL . "/store.php?app=zzShijiCloud&act=notifyUrl")
            ->setRedirectUrl(SITE_URL . "/store.php?app=zzShijiCloud&act=test2")
            ->index();
        if ($res === false) {
            //错误日志
        } else {
            //订单入库
            echo $res;
        }
    }

    /**
     * 电脑网页支付测试入口
     */
    public function diannaowebpay()
    {
        include_once ROOT_PATH . "/includes/shijicloud/Diannaowebpay.php";
        $payMod = new Diannaowebpay();
        $res = $payMod->setMerchantTxnNo(rand(100, 999) . time())
            ->setChannelID(2)
            ->setTxnAmt(0.01)
            ->setNotifyUrl(SITE_URL . "/store.php?app=zzShijiCloud&act=notifyUrl")
            ->setRedirectUrl(SITE_URL . "/store.php?app=zzShijiCloud&act=test2")
            ->index();
        if ($res === false) {
            //错误日志
        } else {
            //订单入库
            echo $res;
        }
    }

    /**
     * 公众号/口碑支付测试入口
     */
    public function gongzhhpay()
    {
        include_once ROOT_PATH . "/includes/shijicloud/Gongzhhpay.php";
        $payMod = new Gongzhhpay();
        $res = $payMod->setMerchantTxnNo(rand(100, 999) . time())
            ->setTxnAmt(0.01)
//            ->setNotifyUrl(SITE_URL . "/store.php?app=zzShijiCloud&act=notifyUrl")
            ->setNotifyUrl("http://www.njbsds.cn/bspm711/store.php?app=zzShijiCloud&act=notifyUrl")
//            ->setRedirectUrl(SITE_URL . "/store.php?app=zzShijiCloud&act=test2")
            ->setRedirectUrl("http://www.njbsds.cn/bspm711/store.php?app=zzShijiCloud&act=test2")
            ->index();
        if ($res === false) {
            //错误日志
        } else {
            //订单入库
            echo $res;
        }
    }

    /**
     * 手机网页支付测试入口
     */
    public function shoujiwebpay()
    {
        include_once ROOT_PATH . "/includes/shijicloud/Shoujiwebpay.php";
        $payMod = new Shoujiwebpay();
        $res = $payMod->setMerchantTxnNo(rand(100, 999) . time())
            ->setChannelID(6)
            ->setTxnAmt(0.01)
//            ->setNotifyUrl(SITE_URL . "/store.php?app=zzShijiCloud&act=notifyUrl")
            ->setNotifyUrl("http://www.lmeri.com/store.php?app=zzShijiCloud&act=notifyUrl")
//            ->setRedirectUrl(SITE_URL . "/store.php?app=zzShijiCloud&act=test2")
            ->setRedirectUrl("http://www.lmeri.com/store.php?app=zzShijiCloud&act=test2")
            ->index();
        if ($res === false) {
            //错误日志
        } else {
            //订单入库
            echo 'success';
        }
    }

    /**
     * 刷卡支付测试入口
     */
    public function shuakapay()
    {
        include_once ROOT_PATH . "/includes/shijicloud/Shuakapay.php";
        $payMod = new Shuakapay();
        $res = $payMod->setMerchantTxnNo(rand(100, 999) . time())
            ->setChannelID(6)
            ->setQRCode('')
            ->setTxnAmt(0.01)
            ->setTxnReqTime(date('Y/m/d H:i:s'))
            ->index();
        if ($res === false) {
            //错误日志
            echo 'ERROR';
        } else {
            echo 'SUCCESS';
            //订单入库
            writeLog($res);
        }
    }

    /**
     * 扫码支付测试入口
     */
    public function saomapay()
    {
        include_once ROOT_PATH . "/includes/libraries/phpqrcode.php";
        include_once ROOT_PATH . "/includes/shijicloud/Saomapay.php";

        $order_amount   = !empty($_REQUEST['order_amount']) ? $_REQUEST['order_amount'] : '';
        $order_sn       = !empty($_REQUEST['order_sn']) ? $_REQUEST['order_sn'] : '';

        $payMod = new Saomapay();
        $res = $payMod->setMerchantTxnNo($order_sn.rand(1000, 9999))
//            ->setOrgTxnNo('123434545drewrwer')
            ->setChannelID(80)
            ->setTxnAmt($order_amount)
            ->setTxnReqTime(date('Y/m/d H:i:s'))
            ->setNotifyUrl("http://www.lmeri.com/store.php?app=zzShijiCloud&act=notifyUrl")
            ->index();
        QRcode::png($res['QRCode'], false, QR_ECLEVEL_L, 4, 1);
    }

    /**
     * 微信小程序支付测试入口
     */
    public function xiaochxpay()
    {
        include_once ROOT_PATH . "/includes/shijicloud/Xiaochxpay.php";
        $payMod = new Xiaochxpay();
        $res = $payMod->setMerchantTxnNo(rand(100, 999) . time())
            ->setTxnAmt(0.01)
            ->setTxnReqTime(date('Y/m/d H:i:s'))
            ->setPayerRealID('oDXF90v31ySNlcNqdjGZDnz8AYRE')
//            ->setNotifyUrl("http://www.njbsds.cn/bspm711/store.php?app=zzShijiCloud&act=notifyUrl")
            ->setNotifyUrl(SITE_URL . "/store.php?app=zzShijiCloud&act=notifyUrl")
            ->index();
        if ($res === false) {
            //错误日志
            echo 'ERROR';
        } else {
            echo 'SUCCESS';
            //订单入库
            writeLog($res);
        }
    }

    /**
     * 退款测试入口
     */
    public function tuikuan()
    {
        $OrgTxnNo = !empty($_REQUEST['OrgTxnNo']) ? htmlspecialchars(trim($_REQUEST['OrgTxnNo'])) : '';//原消费交易的MerchantTxnNo
        include_once ROOT_PATH . "/includes/shijicloud/Tuikuan.php";
        $payMod = new Tuikuan();
        $res = $payMod->setRefundTxnNo(rand(100, 999) . time())
            ->setOrgTxnNo($OrgTxnNo)
            ->setRefundAmt(0.01)
            ->setTxnReqTime(date('Y/m/d H:i:s'))
            ->setCashierID('1')
            ->index();
        if ($res) {
            //修改订单
            echo 'SUCCESS';
        } else {
            echo 'ERROR';
        }
    }

    /**
     * 交易结果查询测试入口
     */
    public function paycheck()
    {
        include_once ROOT_PATH . "/includes/shijicloud/Paycheck.php";
        $paycheckMod = new Paycheck();
        $res_paycheck = $paycheckMod->setOrgTxnNo('3861551855986')
            ->setTxnAmt(0.01)
            ->setTxnReqTime(date('Y/m/d H:i:s'))
            ->index();
        var_dump($res_paycheck);
    }

    /**
     * 跳转页面入口
     */
    public function test2()
    {
        writeLog('test2');
        header("Location: ?app=orderDk&act=index");
    }

    /**
     * 异步回调入口
     */
    public function notifyUrl()
    {
        $res_json = file_get_contents('php://input');
        $res_arr = json_decode($res_json, true);
        if (isset($res_arr['OrgTxnNo']) && !empty($res_arr['OrgTxnNo'])) {
            //修改订单
            writeLog($res_arr);

            $order_sn = substr($res_arr['OrgTxnNo'], 0, -4);

            // 主订单修改
            $data = array(
                'payment_code' => 'aliPay',
                'payment_time' => time(),
                'order_state' => 20,
                'Appoint' => 1, //1未被指定 2被指定
                'install_time' => time(), //区域配送安装完成时间
                'region_install' => 10, //10未配送 20已配送
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
                //获取当前订单所属店铺ID
                $orderInfo = $this->orderMod->getOne(array('cond'=>'order_sn = '.$order_sn));
                //新订单表更新
                $this->orderMod->update_pay_time($orderInfo['store_id'], $order_sn, $res_arr['BranchChannelID'], 1, $data['order_state']);
                //分销订单
                $fxOrderMod = &m('fxOrder');
                $fxOrderMod->addFxOrderByOrderSn($order_sn, 1);

                $this->orderDetailMod->doEditSpec(array('order_id' => $order_sn), $detail);
            }
            //  更新库存
            $sql = "SELECT sg.goods_id,sg.spec_key,sg.goods_num,sg.deduction,sg.good_id FROM " .
                DB_PREFIX . "order as r LEFT JOIN " .
                DB_PREFIX . "order_goods as sg ON  r.order_sn = sg.order_id WHERE sg.order_id =" . $order_sn;
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
            echo '{"RespCode":"00"}';
            exit();
        } else {
            writeLog('ERROR');
        }
    }
}