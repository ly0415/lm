<?php

/**
 * 扫码支付
 * @author wanyan
 * @date 2017/07/19
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class AliPayApp extends BaseStoreApp {

    private $orderMod;
    private $storeMod;
    private $config;
    private $orderDetailMod;
    private $goodsSpecPriceMod;
    private $areaGoodMod;
    private $storeGoodItemPriceMod;
    private $storeid;
    private $lang_id;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        ini_set('date.timezone', 'Asia/Shanghai');
        include ROOT_PATH . '/includes/libraries/aliPay.lib.php';
        $this->orderMod = &m('order');
        $this->storeMod = &m('store');
        $this->orderDetailMod = &m('orderDetail');
        $this->goodsSpecPriceMod = &m('goodsSpecPrice');
        $this->storeGoodItemPriceMod = &m('storeGoodItemPrice');
        $this->areaGoodMod = &m('areaGood');
        $this->storeid = $_REQUEST['storeid'] ? $_REQUEST['storeid'] : $this->storeId;
        $this->lang_id = $_REQUEST['lang_id'] ? $_REQUEST['lang_id'] : 0;
        $aliPayInfo = $this->getAliPayInfo();
        $this->config = array(
            //'app_id' =>'2017011905254523',
            'app_id' => $aliPayInfo['alipay_account'],
            //'merchant_private_key' =>'MIIEpQIBAAKCAQEAyEey5fKlvjsXDsfsZalPuejF8G2Abt/CJx9zZmFNBFJeXezkdBo1644OwLyUfdNXce88v4MnLYtSD202B/BVRbL+A9hbvvBJ2SS0hbgzhlZ3txnoUeBqIuPYKnIAWja7r6WWAABQZP3NB4DK3Tbs1zTTm7qXYs2Nn7xc2Dv9qN2tOSkDEwvSUBquLyyfmcuQD+lW8/w2IA3iDo7X7y98hTRw+RtWP3FR3UbvVcwPAQjoPu3bOCptcHHpFFG/lAe/eycRYCe/KHM8J4QI41mFUijmuo7GEUTlWUSvRZhmbmwQmpgwXW1Te8+47L/uj5lai3eEcbMMAd3LY/ATiHeVdwIDAQABAoIBAQCWPpNKRXlo99MYV4pTyWvxv1meP8c1Zc60ordjemLYdyIru+a14mPIzczrcYzDx6O16Q7dbHobhISO5hK+aeDOZLSCFfDdkE0WBJ8YIVMl1//+8ASER6HXgq1LSjJRevXZkpKwdYvZ9zu0AT4uLWIHH64PCS9AA4vW5OuRm49y+WV0vtxCtz5zja+12N9i4fD/j0tbpy7thgDEEo8myOMyAyK5H6+9b1AniJnBaJkYA2HqFXI6UlEZCWGLdY2naW8afeG493ZtvdRjJxFnWyy5TF9p3Mj7Y0yUeOxdSQW+dTKO3RDvMu1gET3Z0prUMVM0n/oAt12T6j4lvoZPLnhhAoGBAOkuqEy1xVWDjydp0nDOdgLaFyhnQlcffVI7DIzwSuIzP7/iI6s3fiaUlB0l7yOc6yBCPnBekv5Tx/LYAnx9DeFQGCEZMPiDP7FXiSEb+J79x5WoOpoRWKwdVSdlXjrfPbNq72vZ4ffIricgrr0W9uqYF/K9lsXLOOYAKpXIdlaVAoGBANvg0uU4Psu5NzewQA7IU/gieFWbsDbUzLkDgDF+seWEDnPVHed9V8LgwvogBXAwVbdBG/5Qr0O6527zpQGUcz3lLKjM93y9jFfB3IOaaEXDHkRQJWB3FGbED69qz6jYCzix28xCtO2rWTvCsSvv6sbmAX6/CcRPuOa5Ce68AHTbAoGAdsnH/twSnQ+aG6/y/niO8cD8Tx7bUtq90ug16o5292i4Lx4aoZxxbWH/WiH7Ax9rQFG+0Su8okc38uRLz/M84O0WfbYBlnf2OHepae5/5y7NP9YllFsF2xhOSvV+3WrgWcg+E6k5TiszXMdvfPB39OZqPMSMTFLMt46aIxeuAUkCgYEAz9xLIty4KJlQxPl+pTrmfqX/glarEDq9yo2vq5qtDF951jD7kzKgO5+FUzXgTj2zWolXsGSQO4Q+c1orfEHda/7x6CXUNP1v5ipjj5nxxzl8rHHj07ze8YOZnGhqJaEPgqpJMmFBb4lT0zXrjbDCDwOzGCH7VrVTA/KbnC/oco8CgYEA4YHENoeR+k5pIs/+GYnu4DoEd5mNQPTFwOLUJZCp2xywQ6z6NxKGQIF0mdm6Qf0jOu+kNcUE/ZSyNhEPrIQC4vBymWS71VrTaRgsSuohGcaA0FisTrJYjDwBpv7i9Xq2fZOCv4aZtVzbHk1SsNGL67nUdf7EHrNOjAlMlUoqXfI=',
            //'merchant_private_key' =>'MIIEowIBAAKCAQEA1/oxCP3C+S/0iJT2XBrljiwD2REn909bf+9kWsnIFpw4GwtWr59no6EQ/BU9LiaC0jDJOd9RIXbNkdIMVTI4fKG5yde3Dkc8pr1v5I+y7IDKGEiTrcL3yQWaO9bOCWKqQyOzX4/eF5GFw8Ih23oXSEPd2QWspJmxVWAFCQDVu+Tikx3cu1mIMa7z9a7lN/tA9ctYafLqCli4iApEWmcXeE5fCMjSYnTwD2fSu48MBBCGSBWwZs3wM3bMI3KP06r5jA5o/AJQKMSH5CQEJ09sLJyeosdYiPHWbG7zdyKH1PZNhUIcOfEm2+a/keTqJ2ZEI01M80H2LSCYC5JKbO6KXwIDAQABAoIBAG5+sMGR2kNUdn2+AEBk/lZ7TEisj07micBtQGF2ZGi06btkVKgrHIHJcIAXeaJ3z2wry3dROhetyUQ2O1sHA4E32G5cb2ndpjkEKA++OOLojPxZfTxjyBNPS3Yb0nNYyBTrWeSlHRHfwJjDZED+OJUfK4vRbF8VxnUQV+MgSzkB0v5DxbX9CflUsELusCbhfU0a0H6MuHlVdh4c8Fsmf1xRPtz/tpZwLU4+FetAll2NJoE35lvoJF+jiCw5AY1PY6Y0SaJIdM/dr4UuSzFXHCkAq/0OHRGKR/ajNGDSBhq9iINhU/sUYT87G9b09MBJ2ySM0d9U3w0Fx1EhAXXvQqkCgYEA8XAqj9JHTNi/TMxLmUWFRV18Qo2QS6Atm4GD4edKal8sBnSKMqTlstlf5cNTZ6ak2cATMiuaCHswBpmAtw9q1U7rpZ19NBVrUHKLG2UU1YAIQQ5O5Ct3C/mPqtYT3W18mKzZ5p8wL9tk9OwjY89c6RpFseRfvHk/LLEtGdUzgIMCgYEA5QDoKyRQgyVVIK2w0Obdovn8I8zI8Ay2204atawl32ZxgZX1SJlXC87oPv3o4S2XAsSRpATtlT8Ltwa3rQ93FaqWK4/rPLS05ZpughlOSJSlAK9OsmF4Xdqp4CBPTj/WGQrgd+fCa7GEsUKPrUoBjooC2IM3HEUUgPYcwgsZr/UCgYBSCBU99mkpT/93XXZWJkvIrKG6jxS2zT6RtmiTyZz8FUgFDXWjDWnJ4Zd2nm3pKrKaFWuwQSY9uXUw2Njl2cQno3/nLmJK3vguRizDaw2wGKc1S2I8nhP9qpZIqiHnuvp5eUkz1WRu7jEYEl9X2y2rObTyYzCv/dYcHjq/qzOrdwKBgBvNyWJ7jT7vCG/oRsCGV0CTY3ahRYBHuufTitCl7w85q+xU3awL2hK382C6iUzVsTEH1rr4UjQ9rFlzeleLuiSqSoNNfP0o35HE90fadLPBQGtd3Ysw5GFYzClHIvnYLFFsDabhP6y9p+OxtioPAzNgNEo/XDCVfpDN0N4KZPsFAoGBALDwQ7AvmEFARfx0mBI5Q577MqVaVvLwhoiEMtL/X6aGTAxQdv2QryYfa2fjRMUBZiNu1am93NkwkWAs6+geDRNqhdRHWp10lesWcj6Eb9xsLbe4oQsd1U71nz6UrZQtmUNyRV+BZ0Bxbh98Jz33KdEl/pkTncl23zASRVMAud+j',
            'merchant_private_key' => $aliPayInfo['alipay_KEY'],
            //'alipay_public_key' =>'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAlXu1h1RberMaWRVVuTEeS0ed9L8LqAHSYmTI83r0qk8Xw2LsPVQ0t/XthzdCA09Z3njrsdUc1w4AGo5ktJ5F6myMmyqrMcdLZv/JKw85DFMPuXgbe16Ss8lvn9W90ChaX+RYHUzzByLoUEmhD7BOvS+cmFo67zalZo6h1Z1Mj7lvFVRU/7KjxXZgbhqs0unrZ6yfESnpu6revngL8n4Q+mNGz5GEnEs1ZWVjn7AfEL8ZxArSCGudute2/lhcG16X4kT/cz74lNcocYkGjk2oz3v5dSaRGkbL/PRMrEaj6YsktodH7ktx9KQVrIRJ0xVzxTiDHzwfwp4Xv1+x0Kr9zQIDAQAB',
            //'alipay_public_key' =>'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnJ5Z9Bo7XsApaPKVl3xgzGxJjCMYjWXMS1dA6hCDkBVb/bMeY/Ta7KVwjhbKO3PZW59bZD7t8BAcsA1/7FUsjZUYJQMh9+FbOclv5pbSShjBzFpS5C7ZdctUxk3AOwHbyXHeh39rY4RX2a/lnLDvt3SFKzLu9ebA2hSeKLkz1m35D9cdbS8Ypxg4XjoJdDe1cwS0VmJrcJ8otxibCZPBTc0vp24d2j/9AZUMTAff/MgeoGQV+R5llKAtd5j48bDZqEKBo+RGjzquJRY1eSuCxHPMfSj4/KXHr+JRHb9+g9hTLT+hYYrOwQKRXDQzBbOG50NkqiACQdhJbnQBeVeqtwIDAQAB',
            'alipay_public_key' => $aliPayInfo['alipay_PID'],
            'charset' => 'UTF-8',
            'sign_type' => 'RSA2',
            'gatewayUrl' => 'https://openapi.alipay.com/gateway.do',
            'return_url' => "http://www.lmeri.com/store.php?app=aliPay&act=returnUrl&storeid=" . $this->storeid,
            'notify_url' => 'http://www.lmeri.com/store.php?app=aliPay&act=notifyUrl',
        );
    }

    /**
     * @comment 获取总站点的微信支付配置信息
     * @wanyan
     * @date 2018/1/8
     */
    public function getAliPayInfo() {
        $Tstore = '';
        $wxPay = array();

        $rs = $this->storeMod->getOne(array('cond' => "`id` = '{$this->storeid}'", 'fields' => "store_type,store_cate_id"));
        if ($rs['store_type'] == 1) {
            $Tstore = $this->storeid;
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

    public function pay() {
        $order_id = !empty($_REQUEST['order_id']) ? htmlspecialchars($_REQUEST['order_id']) : '';
        $pay_v = !empty($_REQUEST['pay_v']) ? htmlspecialchars(trim($_REQUEST['pay_v'])) : '';
        $orderInfo = $this->orderMod->getOne(array('cond' => "`order_sn` = '{$order_id}'", 'fields' => "order_amount"));
        $order_amoumt = $orderInfo['order_amount'];
        $canShu = array(
            'out_trade_no' => $order_id,
            'subject' => '商品购买',
            'total_amount' => $order_amoumt, //$order_amoumt
            'body' => '艾美商城',
        );
        if ($pay_v == 2) {
            $this->config['return_url'] .= '&pay_v=2';
        }
        $alipay = new AliPay($this->config, $canShu);
        $alipay->buildHtml();
    }

    /*
     * 支付宝同步回调地址
     * @auth wanyan
     * @date 2018-1-26
     */

    public function returnUrl() {
        $arr = $_GET;
        $pay_v = !empty($_REQUEST['pay_v']) ? htmlspecialchars(trim($_REQUEST['pay_v'])) : '';
        unset($arr['app']);
        unset($arr['act']);
        unset($arr['storeid']);
        unset($arr['pay_v']);
        $aliPayService = new AlipayTradeService($this->config);
//        $aliPayService->writeLog('wanyanshaofeng');
        $result = $aliPayService->check($arr);

        /* 实际验证过程建议商户添加以下校验。
          1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号，
          2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额），
          3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）
          4、验证app_id是否为该商户本身。
         */
        if ($result) {//验证成功
            //商户订单号
            $trade_no = htmlspecialchars($_GET['trade_no']);
            $total_amount = floatval($_GET['total_amount']);
            $out_trade_no = htmlspecialchars($_GET['out_trade_no']);
            $timestamp = htmlspecialchars($_GET['timestamp']);
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
                'payment_time' => strtotime($timestamp),
                'order_state' => 20, //付款成功状态
                'Appoint' => 2, //1未被指定 2被指定
                'Appoint_store_id' => $this->storeid, //被指定的站点
                'install_time' => strtotime($timestamp), //区域配送安装完成时间
//                'region_install' => 20 //10未配送 20已配送
            );
            // 子订单修改
            $cond = array(
                'order_sn' => $out_trade_no
            );
            $detail = array(
                'order_state' => 20,
                'shipping_store_id' => $this->storeid
            );
            $res = $this->orderMod->doEditSpec($cond, $data);
            if ($res) {
                $detailRes = $this->orderDetailMod->doEditSpec(array('order_id' => $out_trade_no), $detail);
            }
            //$out_trade_no = '201803151611313930';
            //  更新库存
            $sql = "SELECT sg.goods_id,sg.spec_key,sg.goods_num FROM ".DB_PREFIX."order as r LEFT JOIN ".DB_PREFIX."order_goods as sg ON  r.order_sn = sg.order_id WHERE sg.order_id =".$out_trade_no;
            $orderRes = $this->areaGoodMod->querySql($sql);
            foreach ($orderRes as $k =>$v) {
                if (!empty($v['spec_key'])) {
                    if($v['deduction']==1){
                        $query_id = "select `id` from " . DB_PREFIX . "store_goods_spec_price where `goods_id` = '{$v['good_id']}' and `key` = '{$v['spec_key']}' ";
                        $res_query = $this->storeGoodItemPriceMod->querySql($query_id);
                        $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods_spec_price WHERE `goods_id` = '{$v['good_id']}' and `key` = '{$v['spec_key']}'";
                        $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                        foreach($res_query as $key=>$val){
                            $condition = array(
                                'goods_storage' => $specInfo[0]['goods_storage'] - $v['goods_num']
                            );
                            $res = $this->storeGoodItemPriceMod->doEdit($val['id'], $condition);
                        }
                        if ($res) {
                            $infoSql = "select goods_storage,id from  " . DB_PREFIX . "store_goods WHERE `goods_id` = '{$v['good_id']}'";
                            $Info = $this->areaGoodMod->querySql($infoSql);
                            $cond = array(
                                'goods_storage' => $Info[0]['goods_storage'] - $v['goods_num']
                            );
                            foreach($Info as $key1=>$val1 ){
                                $this->areaGoodMod->doEdit($val1['id'], $cond);
                            }
                        }
                        $Sql = "select goods_storage from  " . DB_PREFIX . "goods_spec_price WHERE `goods_id` = '{$v['good_id']}' and `key` = '{$v['spec_key']}'";
                        $goodsSpec = $this->areaGoodMod->querySql($Sql);
                        $conditional=array(
                            'goods_storage'=>$goodsSpec[0]['goods_storage']-$v['goods_num']
                        );
                        $goodsSpecSql="update ".DB_PREFIX."goods_spec_price set goods_storage = ".$conditional['goods_storage']." where goods_id=".$v['good_id']." and `key` ='{$v['spec_key']}'" ;
                        $result=$this->goodsSpecPriceMod->doEditSql($goodsSpecSql);
                        if($result){
                            $goodSql="select goods_storage from  " . DB_PREFIX . "goods WHERE `goods_id` = '{$v['good_id']}'";
                            $goodInfo = $this->areaGoodMod->querySql($goodSql);
                            $goodCond = array(
                                'goods_storage' => $goodInfo[0]['goods_storage'] - $v['goods_num']
                            );
                            $this->goodsMod->doEdit($v['good_id'],$goodCond);
                        }
                    }else{
                        $query_id = "select `id` from " . DB_PREFIX . "store_goods_spec_price where `store_goods_id` = '{$v['goods_id']}' and `key` = '{$v['spec_key']}' ";
                        $res_query = $this->storeGoodItemPriceMod->querySql($query_id);
                        $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods_spec_price WHERE `store_goods_id` = '{$v['goods_id']}' and `key` = '{$v['spec_key']}'";
                        $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                        $condition = array(
                            'goods_storage' => $specInfo[0]['goods_storage'] - $v['goods_num']
                        );
                        $res = $this->storeGoodItemPriceMod->doEdit($res_query[0]['id'], $condition);
                        if ($res) {
                            $infoSql = "select goods_storage from  " . DB_PREFIX . "store_goods WHERE `id` = '{$v['goods_id']}'";
                            $Info = $this->areaGoodMod->querySql($infoSql);
                            $cond = array(
                                'goods_storage' => $Info[0]['goods_storage'] - $v['goods_num']
                            );
                            $this->areaGoodMod->doEdit($v['goods_id'], $cond);
                        }
                    }



                } else {
                    if($v['deduction']==1){
                        $infoSql = "select goods_storage,id from  " . DB_PREFIX . "store_goods WHERE `goods_id` = '{$v['good_id']}'";
                        $Info = $this->areaGoodMod->querySql($infoSql);

                        $cond = array(
                            'goods_storage' => $Info[0]['goods_storage'] - $v['goods_num']
                        );
                        foreach($Info as $key1=>$val1 ){
                            $this->areaGoodMod->doEdit($val1['id'], $cond);
                        }
                        $goodSql="select goods_storage from  " . DB_PREFIX . "goods WHERE `goods_id` = '{$v['good_id']}'";

                        $goodInfo = $this->areaGoodMod->querySql($goodSql);
                        $goodCond = array(
                            'goods_storage' => $goodInfo[0]['goods_storage'] - $v['goods_num']
                        );
                        $this->goodsMod->doEdit($v['good_id'],$goodCond);
                    }else{
                        $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods WHERE `id` = '{$v['goods_id']}'";
                        $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                        $condition = array(
                            'goods_storage' => $specInfo[0]['goods_storage'] - $v['goods_num']
                        );
                        $this->areaGoodMod->doEdit($v['goods_id'],$condition);
                    }

                }
            }
            if ($detailRes) {
                $sql = "SELECT order_id FROM " . DB_PREFIX . "order where order_sn = " . $out_trade_no;
                $orderid = $this->orderMod->querySql($sql);
                $this->assign('id', $orderid[0]['order_id']);
                $this->assign('lang_id', $this->lang_id);
                if ($pay_v == 2) {
                    $this->display('guestList/success2.html');
                } else {
                    $this->display('guestList/success.html');
                }
            } else {
                echo '支付失败！';
            }

            //echo "验证成功<br />支付宝交易号：".$trade_no;
        } else {
            //验证失败
            echo "验证失败";
        }
    }

    /*
     * 支付宝同步回调地址
     * @auth wanyan
     * @date 2018-1-28
     */

    public function notifyUrl() {
        $arr = $_POST;
        unset($arr['app']);
        unset($arr['act']);
        $alipaySevice = new AlipayTradeService($this->config);
        $result = $alipaySevice->check($arr);

        /* 实际验证过程建议商户添加以下校验。
          1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号，
          2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额），
          3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）
          4、验证app_id是否为该商户本身。
         */
        if ($result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代
            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
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
                    'payment_time' => strtotime($payTime),
                    'order_state' => 20, //收货状态
                    'orplist_name' => '自取', //物流名称
                    'shipping_code' => '自取',
                    'Appoint' => 2, //1未被指定 2被指定
                    'Appoint_store_id' => $this->storeid, //被指定的站点
                    'install_time' => strtotime($payTime), //区域配送安装完成时间
//                    'region_install' => 20 //10未配送 20已配送
                );
                // 子订单修改
                $cond = array(
                    'order_sn' => $out_trade_no
                );
                $detail = array(
                    'order_state' => 20,
                    'shipping_store_id' => $this->storeid
                );
                $res = $this->orderMod->doEditSpec($cond, $data);
                $alipaySevice->writeLog($res);
                if ($res) {
                    $detailRes = $this->orderDetailMod->doEditSpec(array('order_id' => $out_trade_no), $detail);
                    $alipaySevice->writeLog($detailRes);
                }

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
            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
            echo "success"; //请不要修改或删除
        } else {
            //验证失败
            echo "fail";
        }
    }

}
