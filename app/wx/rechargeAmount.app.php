<?php

/**
 * 余额充值
 * @author gao
 *
 */
class RechargeAmountApp extends BaseWxApp {

    private $rechargeAmountMod;
    private $amountLogMod;
    private $userMod;
    private $orderMod;
    private  $orderDetailMod;
    private  $storeMod;
    private $areaGoodMod;
    private $storeGoodItemPriceMod;
    private  $goodsSpecPriceMod;
    private $goodsMod;

    public function __construct() {
        parent::__construct();
        $this->orderMod = &m('order');
        $this->orderDetailMod = &m('orderDetail');
        $this->storeMod = &m('store');
        $this->storeGoodItemPriceMod = &m('storeGoodItemPrice');
        $this->areaGoodMod = &m('areaGood');
        $this->goodsSpecPriceMod=&m('goodsSpecPrice');
        $this->goodsMod=&m('goods');
        $this->rechargeAmountMod = &m('rechargeAmount');
        $this->amountLogMod = &m('amountLog');
        $this->userMod=&m('user');
        $this->orderMod = &m('order');

    }
    //充值中心
    public function  index(){
        //会员基本信息
        $where=" where 1=1";
        $where.=' AND u.id='.$this->userId.' AND al.status = 2 AND al.type in (1,4,5)  ';
        $userData=$this->userMod->getOne(array('cond'=>"`id` = '{$this->userId}' and mark=1",'fields'=>'amount,recharge_id,username,headimgurl'));
        $percentData=$this->rechargeAmountMod->getOne(array('cond'=>"`id`= '{$userData['recharge_id']}'",'fields'=>'percent'));
        if(empty($percentData['percent'])){
            $percentData['percent']=0;
        }
        $userData['percent']=$percentData['percent'];
        $sql="SELECT sum(al.c_money) as accumulativeRecharge FROM ".DB_PREFIX.'amount_log AS al LEFT JOIN '.
            DB_PREFIX.'user AS u ON u.id=al.add_user'.$where;
        $accumulativeRecharge=$this->userMod->querySql($sql);
        if(empty($accumulativeRecharge[0]['accumulativeRecharge'])){
            $accumulativeRecharge[0]['accumulativeRecharge']=0;
        }
        $userData['accumulativeRecharge']=$accumulativeRecharge[0]['accumulativeRecharge'];
        //充值规则
        $ruleSql="SELECT id,c_money,s_money,integral,percent FROM ".DB_PREFIX.'recharge_point WHERE mark=1';
        $ruleData=$this->rechargeAmountMod->querySql($ruleSql);
        $last_names = array_column($ruleData,'c_money');
        array_multisort($last_names,SORT_ASC,$ruleData);
        //生成订单号
        $rand = $this->buildNo(1);
        $ordersn = date('YmdHis') . $rand[0];
        $this->assign('storeId',$this->storeid);
        $this->assign('userId',$this->userId);
        $this->assign('ruleData',$ruleData);
        $this->assign('userData',$userData);
        $this->assign('ordersn',$ordersn);
        $this->display('recharge/rechargeAmount.html');
    }

    /**
     * 充值记录「新」 by xt 2019.03.11
     */
    public function amountLog()
    {
        $sql = <<<SQL
                    SELECT
                        l.add_time,
                        l.c_money,
                        l.`type`,
                        l.point_rule_id,
                        l.status,
                        p.integral,
                        p.percent,
                        p.s_money,
                        l.id    
                    FROM
                        bs_amount_log l
                        LEFT JOIN bs_recharge_point p ON l.point_rule_id = p.id 
                        AND p.mark = 1 
                    WHERE
                        l.mark = 1 
                        AND l.add_user = {$this->userId} 
                    ORDER BY
                        l.add_time DESC
SQL;
        $amountLogData = $this->amountLogMod->querySql($sql);

        $this->load($this->shorthand, 'WeChat/goods');
        $this->assign('langdata', $this->langData);
        $this->assign('amountLogData',$amountLogData);
        $this->display('recharge/amountLog.html');
    }

    //充值记录
    // public function   amountLog(){
    //     $where='where 1=1';
    //     $where .=' and add_user='.$this->userId.' and mark=1 ';
    //     $csql="SELECT add_time,c_money,s_money,point,`type`,point_rule_id,status,class FROM ".DB_PREFIX."amount_log ".
    //         $where .'  order by add_time desc ';
    //     $amountLogData=$this->amountLogMod->querySql($csql);
    //     foreach($amountLogData as $k=>$v){
    //         $amountLogData[$k]['percent']=$this->getPercent($v['point_rule_id']);
    //     }
    //     $this->load($this->shorthand, 'WeChat/goods');
    //     $this->assign('langdata', $this->langData);
    //     $this->assign('amountLogData',$amountLogData);
    //     $this->display('recharge/amountLog.html');
    //
    // }


    public function getPercent($rechargeId){
        $Sql="SELECT id,c_money,s_money,integral,percent FROM ".DB_PREFIX.'recharge_point WHERE mark=1 and id='.$rechargeId;
        $oldruleData=$this->rechargeAmountMod->querySql($Sql);
        return $oldruleData[0]['percent'];
    }

    //更新用户的余额和睿积分抵扣规则
    /*    public function  updateAmount($amount,$rechargeId,$userId){
            $userData=$this->userMod->getOne(array('cond'=>"`id` = '{$userId} and mark=1'",'fields'=>'amount'));
            $data=array(
                'recharge_id'=>$rechargeId,
                'amount'=>$userData['amount']+$amount
            );
            $res=$this->userMod->doEdit($userId,$data);
            return $res;
        }*/

    //生成充值记录
    public  function  createAmountlog($data){
        $amountLogId=$this->amountLogMod->doInsert($data);
        return $amountLogId;
    }

    /**
     * 生成不重复的四位随机数
     * @author wanyan
     * @date 2017-10-23
     */
    public function buildNo($limit) {
        $begin = pow(10,3);
        $end = (pow(10, 4) - 1);
        $rand_array = range($begin, $end);
        shuffle($rand_array); //调用现成的数组随机排列函数
        return array_slice($rand_array, 0, $limit); //截取前$limit个
    }

    //充值订单
    public function amountOrder(){
        $rule_id=!empty($_REQUEST['rule_id']) ? intval($_REQUEST['rule_id']) : 0;
        $userId=!empty($_REQUEST['userId']) ? intval($_REQUEST['userId']) : 0;
        $storeId=!empty($_REQUEST['storeId']) ? intval($_REQUEST['storeId']) : $this->storeid;
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $lang = 29;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        //gao  判断用户是否有未付款的充值订单
        $amountLogMod = &m('amountLog');
        $amountLogStatus = $amountLogMod -> getOne(array("cond"=>"`add_user` = {$userId} and `mark` = 1 and `type` in (1,4) and `status` = 1 "));
        if(!empty($amountLogStatus)){
            $this->setData(array(),'0','您有尚未付款的充值记录，请勿重复提交');
        }
        //
        if(empty($rule_id)){
            $this->setData(array(),'0','请选择充值规格');
        }
        $ruleData=$this->rechargeAmountMod->getOne(array('cond'=>"`id`= '{$rule_id}' and mark=1",'fields'=>'id,c_money,s_money,integral,percent'));
        if(empty($ruleData)){
            $this->setData(array(),'0','充值规则不存在');
        }
        $userData=$this->userMod->getOne(array('cond'=>"`id` = '{$userId}' and mark=1",'fields'=>'amount'));
        $rand = $this->buildNo(1);
        $ordersn = date('YmdHis') . $rand[0];
        $data=array(
            'c_money'=>$ruleData['c_money'],
            'old_money'=>$userData['amount'],
            'point_rule_id'=>$ruleData['id'],
            'new_money'=>$userData['amount']+$ruleData['c_money']+$ruleData['s_money'],
            'source'=>1,
            'add_user'=>$userId,
            'add_time'=>time(),
            'mark'=>1,
            'order_sn'=>$ordersn,
            'status'=>1,
            'point'=>$ruleData['integral'],
            'type'=>1
        );
        $res=$this->createAmountlog($data);
        if ($res) {
            $info['url'] = "?app=jsapi&act=amountJsapi&order_id={$ordersn}&storeid={$storeId}&lang={$lang}&auxiliary={$auxiliary}&latlon={$latlon}";
            $this->setData($info, $status = 1, '提交订单成功，请往支付');
        } else {
            $this->setData($info = array(), $status = 0, '订单提交失败');
        }
    }

    //余额扣除
    public function deductAmount(){
        $couponLogMod=&m('couponLog');//优惠劵记录表
        $order_id=!empty($_REQUEST['order_id']) ? $_REQUEST['order_id'] : '';
        $store_id = $_REQUEST['store_id'];
        $orderInfo =$this->orderMod->getOne(array('cond'=>"`order_sn` = '{$order_id}'",'fields'=>"order_amount,order_id,order_state,buyer_id"));

        $orderMod = &m('order');
        $childOrderData = $orderMod->getData(array("cond" => "`order_sn` like '{$order_id}%' and `mark` = 1"));
        foreach($childOrderData as $key =>$val){
            if($val['buyer_id'] !=$this->userId){
                $this->setData(array(),0,'你不是购买者');
            }
            if($val['order_state'] >=20){
                $this->setData(array(),0,'订单已支付');
            }
            $userData=$this->userMod->getOne(array('cond'=>"`id` = '{$this->userId}' and mark=1",'fields'=>'amount'));
            $orderSn =$val['order_sn'];
            $fxOrderMod = &m('fxOrder');
            $fxOrderMod->addFxOrderByOrderSn($orderSn, 2);
            $amountLogData=array(
                'order_sn'=>$orderSn,
                'type'=>2,
                'status'=>0,
                'c_money'=>$val['order_amount'],
                'old_money'=>$userData['amount'],
                'new_money'=>$userData['amount']-$val['order_amount'],
                'source'=>1,
                'add_user'=>$this->userId,
                'add_time'=>time()
            );
            $userdata=array(
                'amount'=>$userData['amount']-$val['order_amount']
            );
            $data =array(
                'pay_sn' =>'余额支付' ,
                'payment_code' => '余额支付',
                'payment_time' => time(),
                'order_state' => 20
            );
            $cond =array(
                'order_sn' => "{$orderSn}"
            );
            $detail =array(
                'order_state' =>20
            );
            //分单功能
            $userRes=$this->userMod->doEdit($this->userId,$userdata);
            $amountLogId = $this->createAmountlog($amountLogData);
            $this->UpdateStock($orderSn);
            $this->orderDetailMod->doEditSpec(array('order_id' =>"{$orderSn}"),$detail);
            $res =$this->orderMod->doEditSpec($cond,$data);
            $updateRes=$this->orderMod->update_pay_time($store_id,$orderSn,'余额支付',3);
            $this->amountLogMod->doEdit($amountLogId, array('status' => 2));
        };
        if ($res && $userRes && $updateRes) {
            $info['url'] ="?app=rechargeAmount&act=amountLog";
            $this->setData($info, $status = 1, '支付成功');
        } else {
            $this->setData($info = array(), $status = 0, '支付失败');
        }
    }


    public function wxpay(){
        $order_id=!empty($_REQUEST['order_id']) ? $_REQUEST['order_id'] : '0';
        $storeId = !empty($_REQUEST['storeId']) ? $_REQUEST['storeId'] : 0;//选择的商品的店铺id
        $store_cate = !empty($_REQUEST['store_cate']) ? $_REQUEST['store_cate'] : '';//站点国家
        $fx_user_id = $_REQUEST['fx_user_id'] ;//分销用户的id
        $rule_id =  $_REQUEST['rule_id'];//分销规则id
        $orderId=!empty($_REQUEST['orderId']) ? $_REQUEST['orderId'] : '';//订单id
        $state=$this->orderMod->getOne(array('cond'=>"`order_sn`='{$order_id}'",'fields'=>'order_amount,order_state,buyer_id'));
        if($state['buyer_id'] !=$this->userId){
            $this->setData(array(),0,'你不是购买者');
        }
        if($state['order_state'] >=20){
            $this->setData(array(),0,'订单已支付');
        }

        $this->setData(array(), $status = 1, '前往微信支付');

    }

    //支付页面
    public function payment(){
//        echo '<pre>';print_r($_REQUEST);
        $order_id=!empty($_REQUEST['order_id']) ? $_REQUEST['order_id'] : 0;
        $storeid=!empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : 0;
        $store_id = $_REQUEST['store_id'];
        $lang=!empty($_REQUEST['lang']) ? $_REQUEST['lang'] :0;
        $auxiliary=!empty($_REQUEST['latlon']) ? $_REQUEST['latlon'] : 0;
        $orderInfo =$this->orderMod->getOne(array('cond'=>"`order_sn` = '{$order_id}'",'fields'=>"order_amount"));
        $userData=$this->userMod->getOne(array('cond'=>"`id` = '{$this->userId}' and mark=1",'fields'=>'amount'));
        $cartMod=&m('cart');
        $expectTime=$cartMod->expectTime();//模型里面定义的过期时间
        $expectTime=ceil($expectTime/3600);
        if($userData['amount']<$orderInfo['order_amount']){
            $this->assign('display',1);
        }
        $this->assign('expectTime',$expectTime);
        $this->assign('storeid',$storeid);
        $this->assign('lang',$lang);
        $this->assign('auxiliary',$auxiliary);
        $this->assign('order_id',$order_id);
        $this->assign('orderInfo',$orderInfo);
        $this->assign('userData',$userData);
        $this->assign('store_id',$store_id);
        $this->display('recharge/payment.html');
    }


    // 更新规格库存 和 无规格库存
    public function UpdateStock($out_trade_no){
        //  更新库存
        $sql = "SELECT sg.goods_id,sg.spec_key,sg.goods_num,sg.deduction,sg.good_id,sg.prom_id,sg.prom_type FROM ".
            DB_PREFIX."order as r LEFT JOIN ".
            DB_PREFIX."order_goods as sg ON  r.order_sn = sg.order_id WHERE sg.order_id = '{$out_trade_no}'";
        $orderRes = $this->areaGoodMod->querySql($sql);
        foreach ($orderRes as $k =>$v) {
            if($v['prom_type']==1){
                $spikeActiviesMod=&m('spikeActivies');
                $spikeActiviesGoodsMod=&m('spikeActiviesGoods');
                $activitySql="select id,goods_num from ".DB_PREFIX.'spike_goods where store_goods_id='.$v['goods_id'].' and spike_id='.$v['prom_id'];
                $spikeActiviesGoodsData=$spikeActiviesGoodsMod->querySql($activitySql);
                $activityNum=$spikeActiviesGoodsData[0]['goods_num']-$v['goods_num'];
                if($activityNum<=0){
                    $activityNum=0;
                }
                $activityArr=array(
                    'goods_num'=>$activityNum
                );
                $spikeActiviesGoodsMod->doEdit($spikeActiviesGoodsData[0]['id'], $activityArr);
            }
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
    }
//绾夸笅鐢宠
    public function cashPayment(){
        $rule_id=!empty($_REQUEST['rule_id']) ? intval($_REQUEST['rule_id']) : '';
        $userId=!empty($_REQUEST['userId']) ? intval($_REQUEST['userId']) : 0;
        $storeId=!empty($_REQUEST['storeId']) ? intval($_REQUEST['storeId']) : 0;
        $ordersn = !empty($_REQUEST['ordersn']) ? htmlspecialchars(trim($_REQUEST['ordersn'])) : '';
        if(empty($rule_id)){
            $this->setData(array(),'0','请选择充值规格');
        }
        $ruleData=$this->rechargeAmountMod->getOne(array('cond'=>"`id`= '{$rule_id}' and mark=1",'fields'=>'id,c_money,s_money,integral,percent'));
        if(empty($ruleData)){
            $this->setData(array(),'0','充值规则不存在');
        }
        $userData=$this->userMod->getOne(array('cond'=>"`id` = '{$userId}' and mark=1",'fields'=>'amount'));
        if (empty($ordersn)) {
            $rand = $this->buildNo(1);
            $ordersn = date('YmdHis') . $rand[0];
        }
        //判断订单号是否存在
        $oldInfo = $this->amountLogMod->getOne(array('cond' => "order_sn={$ordersn}"));
        if (!empty($oldInfo)) {
            $this->setData(array(),'0','请勿重复提交');
        }
        $data=array(
            'c_money'=>$ruleData['c_money'],
            'old_money'=>$userData['amount'],
            'point_rule_id'=>$ruleData['id'],
            'new_money'=>$userData['amount']+$ruleData['c_money']+$ruleData['s_money'],
            'source'=>1,
            'add_user'=>$userId,
            'add_time'=>time(),
            'mark'=>1,
            'order_sn'=>$ordersn,
            'status'=>1,
            'point'=>$ruleData['integral'],
            'type'=>4,
        );
        $res=$this->createAmountlog($data);
        if ($res) {
            $info['url'] = "?app=rechargeAmount&act=amountLog&storeid={$storeId}";
            $this->setData($info, $status = 1, '提交订单成功，等待审核');
        } else {
            $this->setData($info = array(), $status = 0, '提交订单失败');
        }

    }

    //免费兑换
    public function voucherPay(){
        $order_id=!empty($_REQUEST['order_id']) ? $_REQUEST['order_id'] : '0';
        $couponLogMod=&m('couponLog');//优惠劵记录表
        $store_id = $_REQUEST['store_id'];
        $state=$this->orderMod->getOne(array('cond'=>"`order_sn`='{$order_id}'",'fields'=>'order_amount,order_state,buyer_id'));
        if($state['buyer_id'] !=$this->userId){
            $this->setData(array(),0,'你不是购买者');
        }
        if($state['order_state'] >=20){
            $this->setData(array(),0,'订单已支付');
        }
        $data =array(
            'pay_sn' =>'免费兑换' ,
            'payment_code' => '免费兑换',
            'payment_time' => time(),
            'order_state' => 20
        );
        $cond =array(
            'order_sn' =>$order_id
        );
        $detail =array(
            'order_state' =>20
        );
        $this->UpdateStock($order_id);
        $this->orderDetailMod->doEditSpec(array('order_id' =>$order_id ),$detail);
        $res =$this->orderMod->doEditSpec($cond,$data);
        $this->orderMod->update_pay_time($store_id,$order_id,'免费兑换',5);
        if ($res) {
            $info['url'] ="?app=order&act=orderHair";
            $this->setData($info, $status = 1, '支付成功');
        } else {
            $this->setData($info = array(), $status = 0, '支付失败');
        }
    }

    /**
     * 余额充值券充值
     * by xt 2019.03.08
     */
    public function rechargeCouponPay()
    {
        $sn = empty($_REQUEST['sn']) ? '' : htmlspecialchars(trim($_REQUEST['sn']));
        if (empty($sn)) {
            $this->setData(array(), 0, '券码不能为空');
        }

        $balanceRechargeCouponMod = &m('balanceRechargeCoupon');
        $balanceRechargeCouponData = $balanceRechargeCouponMod->getOne(array(
            'cond' => "mark = 1 and is_use = 1 and sn = '{$sn}'",
            'fields' => 'id,money',
        ));

        if (empty($balanceRechargeCouponData)) {
            $this->setData(array(), 0, '券码无效');
        }
       	//增加限制卷码使用次数
        if($balanceRechargeCouponData['money'] == 88 || $balanceRechargeCouponData['money'] == 199){
	        $times = $balanceRechargeCouponMod->getCount(
	        	array(
	            'cond' => "mark = 1 and is_use = 2 and money = {$balanceRechargeCouponData['money']} and use_user = {$this->userId}"
	        	)
	        );
	        if($times >= 2){
	        	$this->setData(array(), 0, '当前面额的活动已超限、只能参加2次！');
	        }
        }


        $user = $this->userMod->getOne(array(
            'cond' => "id = {$this->userId}",
            'fields' => 'amount',
        ));

        // bs_amount_log 表，插入记录
        $res = $this->amountLogMod->addAmountLog($this->userId, 5, 2, 1, '', $balanceRechargeCouponData['money'], $user['amount'], $user['amount'] + $balanceRechargeCouponData['money'], '');

        if ($res) {
            // 更新 bs_balance_recharge_coupon 表
            $balanceRechargeCouponMod->doEdit($balanceRechargeCouponData['id'], array(
                'is_use' => 2,
                'use_source' => 1,
                'use_user' => $this->userId,
                'use_time' => time(),
            ));

            // 更新 bs_user 表
            $this->userMod->doEdit($this->userId, array(
                'amount' => $user['amount'] + $balanceRechargeCouponData['money'],
            ));

            $this->setData(array(), 1, '充值成功');
        }

        $this->setData(array(), 0, '充值失败');
    }


    /**
     * 取消和去支付充值订单
     * by gao 2019.04.03
     */
    public function operateAmountOrder()
    {
        $amountLogMod = &m('amountLog');
        $amountLogId = !empty($_REQUEST['amountLogId']) ? $_REQUEST['amountLogId'] : 0;
        $type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : '';
        if(empty($amountLogId) && empty($type)){
            $this->setData('',0,'发生不可预知的错误！');
        }
        if($type == 'cancle'){
            $res=$amountLogMod->doMark($amountLogId);
            if(!empty($res)){
                $info['url'] ="wx.php?app=rechargeAmount&act=amountLog";
                $this->setData($info, $status = 1, '取消充值订单成功');
            }else{
                $this->setData('', $status = 0, '取消充值订单失败');
            }
        }
        if($type == 'pay'){
            $amountLogData = $amountLogMod->getOne(array(
                "cond"=>"`id` = {$amountLogId}"));
            if($amountLogData['status'] == 2){
                $this->setData('',0,'该订单已支付');
            }
            $info['url'] = "wx.php?app=jsapi&act=amountJsapi&order_id={$amountLogData['order_sn']}&storeid={$this->storeid}&lang={$this->langid}";
            $this->setData($info, $status = 1, '');
        }
    }
}
