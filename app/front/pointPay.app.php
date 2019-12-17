<?php

/**
 * 扫码支付
 * @author wanyan
 * @date 2017/07/19
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class pointPayApp extends BaseFrontApp
{
    private $mstored;
    private $storeMod;
    private $lang;
    private $config;
    private $pointOrderMod;
    private $userMod;
    private $pointLogMod;


    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        ini_set('date.timezone', 'Asia/Shanghai');
        include  ROOT_PATH.'/includes/libraries/aliPay.lib.php';
        $this->mstored = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : '';
        $this->lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : '';
        $this->pointOrderMod=&m('pointOrder');
        $this->userMod = &m('user');
        $this->pointLogMod = &m("pointLog");
        $this->storeMod = &m('store');
        $aliPayInfo = $this->getAliPayInfo();

        $this->config = array(
            //'app_id' =>'2017011905254523',
            'app_id' =>$aliPayInfo['alipay_account'],
           //'merchant_private_key' =>'MIIEpQIBAAKCAQEAyEey5fKlvjsXDsfsZalPuejF8G2Abt/CJx9zZmFNBFJeXezkdBo1644OwLyUfdNXce88v4MnLYtSD202B/BVRbL+A9hbvvBJ2SS0hbgzhlZ3txnoUeBqIuPYKnIAWja7r6WWAABQZP3NB4DK3Tbs1zTTm7qXYs2Nn7xc2Dv9qN2tOSkDEwvSUBquLyyfmcuQD+lW8/w2IA3iDo7X7y98hTRw+RtWP3FR3UbvVcwPAQjoPu3bOCptcHHpFFG/lAe/eycRYCe/KHM8J4QI41mFUijmuo7GEUTlWUSvRZhmbmwQmpgwXW1Te8+47L/uj5lai3eEcbMMAd3LY/ATiHeVdwIDAQABAoIBAQCWPpNKRXlo99MYV4pTyWvxv1meP8c1Zc60ordjemLYdyIru+a14mPIzczrcYzDx6O16Q7dbHobhISO5hK+aeDOZLSCFfDdkE0WBJ8YIVMl1//+8ASER6HXgq1LSjJRevXZkpKwdYvZ9zu0AT4uLWIHH64PCS9AA4vW5OuRm49y+WV0vtxCtz5zja+12N9i4fD/j0tbpy7thgDEEo8myOMyAyK5H6+9b1AniJnBaJkYA2HqFXI6UlEZCWGLdY2naW8afeG493ZtvdRjJxFnWyy5TF9p3Mj7Y0yUeOxdSQW+dTKO3RDvMu1gET3Z0prUMVM0n/oAt12T6j4lvoZPLnhhAoGBAOkuqEy1xVWDjydp0nDOdgLaFyhnQlcffVI7DIzwSuIzP7/iI6s3fiaUlB0l7yOc6yBCPnBekv5Tx/LYAnx9DeFQGCEZMPiDP7FXiSEb+J79x5WoOpoRWKwdVSdlXjrfPbNq72vZ4ffIricgrr0W9uqYF/K9lsXLOOYAKpXIdlaVAoGBANvg0uU4Psu5NzewQA7IU/gieFWbsDbUzLkDgDF+seWEDnPVHed9V8LgwvogBXAwVbdBG/5Qr0O6527zpQGUcz3lLKjM93y9jFfB3IOaaEXDHkRQJWB3FGbED69qz6jYCzix28xCtO2rWTvCsSvv6sbmAX6/CcRPuOa5Ce68AHTbAoGAdsnH/twSnQ+aG6/y/niO8cD8Tx7bUtq90ug16o5292i4Lx4aoZxxbWH/WiH7Ax9rQFG+0Su8okc38uRLz/M84O0WfbYBlnf2OHepae5/5y7NP9YllFsF2xhOSvV+3WrgWcg+E6k5TiszXMdvfPB39OZqPMSMTFLMt46aIxeuAUkCgYEAz9xLIty4KJlQxPl+pTrmfqX/glarEDq9yo2vq5qtDF951jD7kzKgO5+FUzXgTj2zWolXsGSQO4Q+c1orfEHda/7x6CXUNP1v5ipjj5nxxzl8rHHj07ze8YOZnGhqJaEPgqpJMmFBb4lT0zXrjbDCDwOzGCH7VrVTA/KbnC/oco8CgYEA4YHENoeR+k5pIs/+GYnu4DoEd5mNQPTFwOLUJZCp2xywQ6z6NxKGQIF0mdm6Qf0jOu+kNcUE/ZSyNhEPrIQC4vBymWS71VrTaRgsSuohGcaA0FisTrJYjDwBpv7i9Xq2fZOCv4aZtVzbHk1SsNGL67nUdf7EHrNOjAlMlUoqXfI=',
            //'merchant_private_key' =>'MIIEowIBAAKCAQEA1/oxCP3C+S/0iJT2XBrljiwD2REn909bf+9kWsnIFpw4GwtWr59no6EQ/BU9LiaC0jDJOd9RIXbNkdIMVTI4fKG5yde3Dkc8pr1v5I+y7IDKGEiTrcL3yQWaO9bOCWKqQyOzX4/eF5GFw8Ih23oXSEPd2QWspJmxVWAFCQDVu+Tikx3cu1mIMa7z9a7lN/tA9ctYafLqCli4iApEWmcXeE5fCMjSYnTwD2fSu48MBBCGSBWwZs3wM3bMI3KP06r5jA5o/AJQKMSH5CQEJ09sLJyeosdYiPHWbG7zdyKH1PZNhUIcOfEm2+a/keTqJ2ZEI01M80H2LSCYC5JKbO6KXwIDAQABAoIBAG5+sMGR2kNUdn2+AEBk/lZ7TEisj07micBtQGF2ZGi06btkVKgrHIHJcIAXeaJ3z2wry3dROhetyUQ2O1sHA4E32G5cb2ndpjkEKA++OOLojPxZfTxjyBNPS3Yb0nNYyBTrWeSlHRHfwJjDZED+OJUfK4vRbF8VxnUQV+MgSzkB0v5DxbX9CflUsELusCbhfU0a0H6MuHlVdh4c8Fsmf1xRPtz/tpZwLU4+FetAll2NJoE35lvoJF+jiCw5AY1PY6Y0SaJIdM/dr4UuSzFXHCkAq/0OHRGKR/ajNGDSBhq9iINhU/sUYT87G9b09MBJ2ySM0d9U3w0Fx1EhAXXvQqkCgYEA8XAqj9JHTNi/TMxLmUWFRV18Qo2QS6Atm4GD4edKal8sBnSKMqTlstlf5cNTZ6ak2cATMiuaCHswBpmAtw9q1U7rpZ19NBVrUHKLG2UU1YAIQQ5O5Ct3C/mPqtYT3W18mKzZ5p8wL9tk9OwjY89c6RpFseRfvHk/LLEtGdUzgIMCgYEA5QDoKyRQgyVVIK2w0Obdovn8I8zI8Ay2204atawl32ZxgZX1SJlXC87oPv3o4S2XAsSRpATtlT8Ltwa3rQ93FaqWK4/rPLS05ZpughlOSJSlAK9OsmF4Xdqp4CBPTj/WGQrgd+fCa7GEsUKPrUoBjooC2IM3HEUUgPYcwgsZr/UCgYBSCBU99mkpT/93XXZWJkvIrKG6jxS2zT6RtmiTyZz8FUgFDXWjDWnJ4Zd2nm3pKrKaFWuwQSY9uXUw2Njl2cQno3/nLmJK3vguRizDaw2wGKc1S2I8nhP9qpZIqiHnuvp5eUkz1WRu7jEYEl9X2y2rObTyYzCv/dYcHjq/qzOrdwKBgBvNyWJ7jT7vCG/oRsCGV0CTY3ahRYBHuufTitCl7w85q+xU3awL2hK382C6iUzVsTEH1rr4UjQ9rFlzeleLuiSqSoNNfP0o35HE90fadLPBQGtd3Ysw5GFYzClHIvnYLFFsDabhP6y9p+OxtioPAzNgNEo/XDCVfpDN0N4KZPsFAoGBALDwQ7AvmEFARfx0mBI5Q577MqVaVvLwhoiEMtL/X6aGTAxQdv2QryYfa2fjRMUBZiNu1am93NkwkWAs6+geDRNqhdRHWp10lesWcj6Eb9xsLbe4oQsd1U71nz6UrZQtmUNyRV+BZ0Bxbh98Jz33KdEl/pkTncl23zASRVMAud+j',
            'merchant_private_key' =>$aliPayInfo['alipay_KEY'],
           //'alipay_public_key' =>'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAlXu1h1RberMaWRVVuTEeS0ed9L8LqAHSYmTI83r0qk8Xw2LsPVQ0t/XthzdCA09Z3njrsdUc1w4AGo5ktJ5F6myMmyqrMcdLZv/JKw85DFMPuXgbe16Ss8lvn9W90ChaX+RYHUzzByLoUEmhD7BOvS+cmFo67zalZo6h1Z1Mj7lvFVRU/7KjxXZgbhqs0unrZ6yfESnpu6revngL8n4Q+mNGz5GEnEs1ZWVjn7AfEL8ZxArSCGudute2/lhcG16X4kT/cz74lNcocYkGjk2oz3v5dSaRGkbL/PRMrEaj6YsktodH7ktx9KQVrIRJ0xVzxTiDHzwfwp4Xv1+x0Kr9zQIDAQAB',
            //'alipay_public_key' =>'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnJ5Z9Bo7XsApaPKVl3xgzGxJjCMYjWXMS1dA6hCDkBVb/bMeY/Ta7KVwjhbKO3PZW59bZD7t8BAcsA1/7FUsjZUYJQMh9+FbOclv5pbSShjBzFpS5C7ZdctUxk3AOwHbyXHeh39rY4RX2a/lnLDvt3SFKzLu9ebA2hSeKLkz1m35D9cdbS8Ypxg4XjoJdDe1cwS0VmJrcJ8otxibCZPBTc0vp24d2j/9AZUMTAff/MgeoGQV+R5llKAtd5j48bDZqEKBo+RGjzquJRY1eSuCxHPMfSj4/KXHr+JRHb9+g9hTLT+hYYrOwQKRXDQzBbOG50NkqiACQdhJbnQBeVeqtwIDAQAB',
            'alipay_public_key' =>$aliPayInfo['alipay_PID'],
            'charset' => 'UTF-8',
            'sign_type' => 'RSA2',
            'gatewayUrl' => 'https://openapi.alipay.com/gateway.do',
            'return_url' => "http://www.711home.net/index.php?app=pointPay&act=returnUrl&storeid=".$this->mstored."&lang=".$this->lang,
            'notify_url' => 'http://www.711home.net/index.php?app=pointPay&act=notifyUrl',
        );

    }
    /**
     * @comment 获取总站点的微信支付配置信息
     * @wanyan
     * @date 2018/1/8
     */
    public function getAliPayInfo()
    {
        $Tstore = '';
        $wxPay = array();
        $rs = $this->storeMod->getOne(array('cond' => "`id` = '{$this->mstored}'", 'fields' => "store_type,store_cate_id"));

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
    public function pay(){

        $order_id = !empty($_REQUEST['order_id']) ? htmlspecialchars($_REQUEST['order_id']) :'';
        $orderInfo =$this->pointOrderMod->getOne(array('cond'=>"`order_sn` = '{$order_id}'",'fields'=>"amount"));

        $order_amoumt = $orderInfo['amount'] ;
        $canShu =array(
            'out_trade_no' => $order_id,
            'subject' => '睿积分购买',
            'total_amount' =>$order_amoumt,
            'body' => '艾美商城',
        );

        $alipay = new AliPay($this->config,$canShu);
        $alipay -> buildHtml();
    }
    /*
     * 支付宝同步回调地址
     * @auth wanyan
     * @date 2018-1-26
     */
    public function returnUrl(){
        $arr=$_GET;
        $this->assign('storeid',$arr['storeid']);
        $this->assign('lang',$arr['lang']);
        $langInfo = $this->getShorthand($this->langid);
        $this->load($langInfo['shorthand'], 'goods/goods');
        $this->assign('langdata', $this->langData);
        unset($arr['app']);
        unset($arr['act']);
        unset($arr['storeid']);
        unset($arr['lang']);
        unset($arr['auxiliary']);

        $aliPayService = new AlipayTradeService($this->config);
//        $aliPayService->writeLog('wanyanshaofeng');
        $result = $aliPayService->check($arr);

        /* 实际验证过程建议商户添加以下校验。
        1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号，
        2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额），
        3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）
        4、验证app_id是否为该商户本身。
        */
        if($result) {//验证成功
            //商户订单号
            $trade_no = htmlspecialchars($_GET['trade_no']);
            $total_amount = floatval($_GET['total_amount']);
            $out_trade_no = htmlspecialchars($_GET['out_trade_no']);
            $timestamp = htmlspecialchars($_GET['timestamp']);
            $rs  = $this->pointOrderMod->isExist($out_trade_no);
            if(empty($rs['order_sn'])){
                echo '支付订单编号不存在';
                exit;
            }
            if($rs['status'] == 1){
                echo '该订单已被支付';
                exit;
            }
//            if($rs['order_amount'] != $total_amount){
//                echo '支付金额跟订单金额不一致';
//                exit;
//            }
            // 主订单修改
            $data =array(
                'pay_sn' => $trade_no,
                'payment_code' => 'aliPay',
                'pay_time' => strtotime($timestamp),
                'status' => 1
            );
            $res =$this->pointOrderMod->doEdit($rs['id'], $data);
            if($res){
                //生成睿积分日志
                $sql='SELECT point,username,phone FROM '.DB_PREFIX.'user WHERE is_kefu = 0 AND id = '.$rs['buyer_id'];
                $info = $this->userMod->querySql($sql);
                $note='充值'.$rs['point'].'睿积分';
                $expend='-';
                 $this->addPointLog($info[0]['phone'],$note,$rs['buyer_id'],$rs['point'],$expend);
            }
           //$out_trade_no = '201803151611313930';
            //  更新用户睿积分
            $point=$info[0]['point']+$rs['point'];
             $userData= array(
                "table" => "user",
                'cond' => 'id = ' . $rs['buyer_id'],
                'set' => "point ='".$point."'",
            );
            $detailRes=$this->userMod->doUpdate($userData);
            if($detailRes){
                $this->display('public/success.html');
            }else{
                echo  '支付失败！';
            }

            //echo "验证成功<br />支付宝交易号：".$trade_no;
        }
        else {
            //验证失败
            echo "验证失败";
        }
    }

    /*
   * 支付宝同步回调地址
   * @auth wanyan
   * @date 2018-1-28
   */
    public function notifyUrl(){
        $arr=$_POST;
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
        if($result) {//验证成功
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


            if($arr['trade_status'] == 'TRADE_FINISHED') {

                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
                //如果有做过处理，不执行商户的业务程序

                //注意：
                //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
            }
            else if ($trade_status == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
                //如果有做过处理，不执行商户的业务程序
                //注意：
                //付款完成后，支付宝系统发送该交易状态通知
                $rs  = $this->pointOrderMod->isExist($out_trade_no);
                if(empty($rs['order_sn'])){
                    echo '支付订单编号不存在';
                    exit;
                }
                if($rs['status'] == 1){
                    echo '该订单已被支付';
                    exit;
                }
//            if($rs['order_amount'] != $total_amount){
//                echo '支付金额跟订单金额不一致';
//                exit;
//            }
                // 主订单修改
                $data =array(
                    'pay_sn' => $trade_no,
                    'payment_code' => 'aliPay',
                    'pay_time' => strtotime($payTime),
                    'status' => 1
                );
                $res =$this->pointOrderMod->doEdit($rs['id'], $data);
                if($res){
                    //生成睿积分日志
                    $sql='SELECT point,username,phone FROM '.DB_PREFIX.'user WHERE is_kefu = 0 AND id = '.$rs['buyer_id'];
                    $info = $this->userMod->querySql($sql);
                   /* $this->doGive($rs['buyer_id'],$info[0]);*/
                    $note='充值'.$rs['point'].'睿积分';
                    $expend='-';
                    $detailRes =  $this->addPointLog($info[0]['phone'],$note,$rs['buyer_id'],$rs['point'],$expend);
                    $alipaySevice->writeLog($detailRes);
                }
                //$out_trade_no = '201803151611313930';
                //  更新用户睿积分
                $point=$info[0]['point']+$rs['point'];
                $userData= array(
                    "table" => "user",
                    'cond' => 'id = ' . $rs['buyer_id'],
                    'set' => "point ='".$point."'",
                );
                $this->userMod->doUpdate($userData);

            }
            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
            echo "success";	//请不要修改或删除
        }else {
            //验证失败
            echo "fail";

        }
    }



    //生成睿积分充值日志
    public  function addPointLog($username,$note,$userid,$deposit,$expend){

        if(empty($this->accountName)){
            $accountName='--';
        }
        $logData = array(
            'operator' => $accountName,
            'username' => $username,
            'add_time' => time(),
            'deposit'=>$deposit,
            'expend'=>$expend,
            'note'=>$note,
            'userid'=>$userid
        );
        $pointLogMod = &m("pointLog");
        $pointLogMod->doInsert($logData);
    }

    /*
 * 判断充值送
 * @author lee
 * @date 2018-6-21 09:48:29
 */
    public function doGive($userid,$info){
        $pointMod = &m('point');
        $site = $pointMod->getOne(array("cond"=>"1=1"));
        if(empty($info)){
            $info = $this->userMod->getOne(array("cond"=>"id=".$userid));
        }
        if($site){
            //判断是否为第一次充值
            $res = $this->pointLogMod->getCount(array("cond"=>"userid=".$userid));
            if($res >= 1){
                $arr = array(
                    "msg" =>"充值送".$site['recharge'],
                    "point"=> $site['recharge']
                );
            }else{
                $arr = array(
                    "msg" =>"充值送".$site['first_recharge'],
                    "point"=> $site['first_recharge']
                );
            }
            $point = $info['point']+$arr['point'];
            $user_point = array(
                "point"=>$point
            );
            $this->addPointLog($info['phone'],$arr['msg'],$userid,$arr['point']);
            $res = $this->userMod->doEdit($userid,$user_point);
        }
    }




}