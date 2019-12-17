<?php

/**
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class orderMod extends BaseMod
{

    public function __construct()
    {
        parent::__construct("order");
    }

    /**
     * 生成订单
     * @param $data          订单数据信息
     * @param $source        订单来源
     * @param $valetUser     下单人员
     * @author: gao
     * @date  : 2019-01-29
     */
    public function createOrder($data, $source, $valetUser = 0)
    {
        $initMod =& m('init');
        $nowTime = time();
        //订单主表信息
        $orderData = array(
            "order_sn" => !empty($data['order_sn']) ? $data['order_sn'] : '',
            "store_id" => !empty($data['store_id']) ? $data['store_id'] : 0,
            "buyer_id" => !empty($data['buyer_id']) ? $data['buyer_id'] : 0,
            "goods_amount" => !empty($data['goods_amount']) ? $data['goods_amount'] : 0,
            "order_amount" => !empty($data['order_amount']) ? $data['order_amount'] : 0,
            "order_state" => 10,
            "sendout" => !empty($data['sendout']) ? $data['sendout'] : 1,
            "evaluation_state" => 0,
            "source" => !empty($source) ? $source : 0,
            "add_time" => $nowTime,
        );
        $orderMod =& m("order" . $data['store_id']);
        $orderId = $orderMod->doInsert($orderData);
        //订单详情表信息
        $orderDetailsData = array(  
            "id" => $orderId,
            "order_id" => $orderId,
            'address_id' => !empty($data['address_id']) ? $data['address_id'] :0,
            "order_sn" => !empty($data['order_sn']) ? $data['order_sn'] : '',
            "shipping_fee" => !empty($data['shipping_fee']) ? $data['shipping_fee'] : 0,
            "seller_msg" => !empty($data['seller_msg']) ? $data['seller_msg'] : '',
            "fx_user_id" => !empty($data['fx_user_id']) ? $data['fx_user_id'] : 0,
            'fx_money' => !empty($data['fx_money']) ? $data['fx_money'] : 0,
            'point_discount' => !empty($data['pd_amount']) ? $data['pd_amount'] : 0,
            'coupon_discount' => !empty($data['cp_amount']) ? $data['cp_amount'] : 0,
            'number_order' => '',
            'sendout_time' => !empty($data['pei_time']) ? $data['pei_time'] : 0,
            'delivery' => !empty($data['delivery']) ? serialize($data['delivery']) : ''
        );
        if ($source == 3) {
            $orderDetailsData['valet_order_user_id'] = !empty($valetUser) ? $valetUser : 0;
            $orderDetailsData['valet_order_time'] = $nowTime;
            $orderDetailsData['discount_num'] = !empty($data['discount_num']) ? $data['discount_num'] : 0;
            $orderDetailsData['discount'] = !empty($data['discount']) ? $data['discount'] : 0;
            $orderDetailsData['delivery_lal'] = !empty($data['delivery_lal']) ? $data['delivery_lal'] : '';

        }
        if($data['store_id'] == 98){
            $orderDetailsData['table_type'] = $data['table_type'];
            $orderDetailsData['table_desc'] = $data['table_desc'];
        }

        $orderDetailsMod =& m("orderDetails" . $data['store_id']);
        $orderDetailsId = $orderDetailsMod->doInsert($orderDetailsData);
        //订单关联表信息
        $orderRelationData = array(
            "id" => $orderId,
            "order_id" => $orderId,
            "order_sn" => !empty($data['order_sn']) ? $data['order_sn'] : '',
            'payment_source' => !empty($data['payment_source']) ? $data['payment_source'] : 1758421,
        );
        //用户订单关联信息
        $userOrderMod =& m('userOrder');
        $userOrderData = array(
            'user_id' => !empty($data['buyer_id']) ? $data['buyer_id'] : 0,
            'store_id' => !empty($data['store_id']) ? $data['store_id'] : 0,
            'order_sn' => !empty($data['order_sn']) ? $data['order_sn'] : '',
            'pay_money' => !empty($data['order_amount']) ? $data['order_amount'] : 0,
            'add_time' => $nowTime
        );
        $userOrderId = $userOrderMod->doInsert($userOrderData);
        $orderRelationMod =& m("orderRelation" . $data['store_id']);
        $orderRelationId = $orderRelationMod->doInsert($orderRelationData);
        return $orderId && $orderDetailsId && $orderRelationId && $userOrderId;
    }

    /**
     * 分单生成新订单
     * @param $data          订单数据信息
     * @param $source        订单来源
     * @param $paymentType   支付方式
     * @author: gao
     * @date  : 2019-03-08
     */
    public function produceOrder($data, $paymentType, $paySn)
    {
        $nowTime = time();
        $fxuserMod = &m('fxuser');
        //订单主表信息
        $orderData = array(
            "order_sn" => !empty($data['order_sn']) ? $data['order_sn'] : '',
            "store_id" => !empty($data['store_id']) ? $data['store_id'] : 0,
            "buyer_id" => !empty($data['buyer_id']) ? $data['buyer_id'] : 0,
            "goods_amount" => !empty($data['goods_amount']) ? $data['goods_amount'] : 0,
            "order_amount" => !empty($data['order_amount']) ? $data['order_amount'] : 0,
            "order_state" => 10,
            "sendout" => !empty($data['sendout']) ? $data['sendout'] : 0,
            "evaluation_state" => 0,
            "source" => !empty($data['source']) ? $data['source'] : 0,
            "add_time" => $nowTime,
        );
        $orderMod =& m("order" . $data['store_id']);
        $orderId = $orderMod->doInsert($orderData);
        //订单详情表信息
        $fxuserInfo = $fxuserMod->getOne(array('cond' => "id={$data['fx_user_id']}"));
        $fxMoney = number_format(($data['goods_amount'] - $data['cp_amount'] - $data['pd_amount']) * $fxuserInfo['discount'] * 0.01, 2, '.', '');
        $orderDetailsData = array(
            "id" => $orderId,
            "order_id" => $orderId,
            "order_sn" => !empty($data['order_sn']) ? $data['order_sn'] : '',
            "shipping_fee" => !empty($data['shipping_fee']) ? $data['shipping_fee'] : 0,
            "seller_msg" => !empty($data['seller_msg']) ? $data['seller_msg'] : '',
            "fx_user_id" => !empty($data['fx_user_id']) ? $data['fx_user_id'] : 0,
            'fx_money' => !empty($fxMoney) ? $fxMoney : 0,
            'point_discount' => !empty($data['pd_amount']) ? $data['pd_amount'] : 0,
            'coupon_discount' => !empty($data['cp_amount']) ? $data['cp_amount'] : 0,
            'number_order' => !empty($data['number_order']) ? $data['number_order'] : 0,
            'shipping_fee' => !empty($data['shipping_fee']) ? $data['shipping_fee'] : 0,
        );
        $orderDetailsMod =& m("orderDetails" . $data['store_id']);
        $orderDetailsId = $orderDetailsMod->doInsert($orderDetailsData);
        //订单关联表信息
        $orderRelationData = array(
            "id" => $orderId,
            "order_id" => $orderId,
            "order_sn" => !empty($data['order_sn']) ? $data['order_sn'] : '',
            'payment_source' => !empty($data['payment_source']) ? $data['payment_source'] : 1758421,
        );
        //用户订单关联信息
        $userOrderMod =& m('userOrder');
        $userOrderData = array(
            'user_id' => !empty($data['buyer_id']) ? $data['buyer_id'] : 0,
            'store_id' => !empty($data['store_id']) ? $data['store_id'] : 0,
            'order_sn' => !empty($data['order_sn']) ? $data['order_sn'] : '',
            'pay_money' => !empty($data['order_amount']) ? $data['order_amount'] : 0,
            'add_time' => $nowTime
        );
        $userOrderMod->doInsert($userOrderData);
        $orderRelationMod =& m("orderRelation" . $data['store_id']);
        $orderRelationMod->doInsert($orderRelationData);
    }


    /**
     * 修改订单的支付时间和支付方式
     * @author tangp
     * @date 2019-01-29
     * @param int $store_id 区域店铺id
     * @param int $order_sn 订单号
     * @param int $pay_sn 支付单号
     * * @param int $source 订单来源
     * @param int $payment_type 支付方式 1、支付宝支付  2、微信支付  3、余额支付  4、线下支付  5、免费兑换
     * @param int $daifu 是否代付 1、是  2、否
     * @return mixed
     */
    public function update_pay_time($store_id, $order_sn, $pay_sn, $payment_type, $order_state = 20, $daifu = null,$number_order = 0)
    {
        $orderRelationMod = &m('orderRelation' . $store_id);
        $orderMod = &m('order' . $store_id);
        $orderDetailsMod = &m('orderDetails' . $store_id);
        $rs = $orderMod->getOne(array('cond' => "`order_sn` = '{$order_sn}'", 'fields' => "id"));
        $orderRelationInfo = $orderRelationMod->getOne(array('cond' => "`order_sn` = '{$order_sn}'", 'fields' => "payment_time"));
        $orderData = array(
            "order_state" => $order_state
        );
        $cond = array('order_sn'=>$order_sn);
        $daifu = empty($daifu) ? 0 : $daifu;
        if ($order_state == 40) {
            $relationData = array(   //关联表数据
                "table" => "order_relation_" . $store_id,
                'payment_type' => $payment_type,
                'payment_time' => time(),
                "ship_time" => time(),
                'delivery_time' => time(),
                'is_instead_pay' => $daifu,

            );
        } else {
            $relationData = array(   //关联表数据
                "table" => "order_relation_" . $store_id,
                'payment_type' => $payment_type,
                'payment_time' => time(),
                'is_instead_pay' => $daifu,
            );
        }

        if (in_array($payment_type, array(1, 2))) {
            $detailsData = array( //详情表数据
                "table" => "order_details_" . $store_id,
                "pay_sn" => $pay_sn,
                'number_order' => $this->createNumberOrder($store_id)
            );
        } else {
            $detailsData = array( //详情表数据
                "table" => "order_details_" . $store_id,
                "pay_sn" => '',
                'number_order' => $this->createNumberOrder($store_id)
            );
        }
        $orderMod->doEdit($rs['id'], $orderData);
        $orderDetailsMod->doEditSpec($cond,$detailsData);
        $res = '';
        if( !$orderRelationInfo['payment_time'] ){
            $res = $orderRelationMod->doEditSpec($cond,$relationData);
        }
        return $res;
    }

    /**
     * 修改订单的取消时间
     * @author tangp
     * @date 2019-01-29
     * @param int $store_id 区域店铺id
     * @param int $order_sn 订单号
     * @return void id
     */
    public function update_cancel_time($store_id, $order_sn)
    {
        $initMod =& m('init');
        $orderRelationMod = &m('orderRelation' . $store_id);
        $orderMod = &m('order' . $store_id);
        $rs = $orderMod->getOne(array('cond' => "`order_sn` = '{$order_sn}'", 'fields' => "id"));
//        $orderMod->doMark($rs['id']);
        $relationData = array(   //关联表数据
            "table" => "order_relation_" . $store_id,
            "cond" => "order_id=" . $rs['id'],
            "set" => array(
                'cancel_time' => time(),
            ),
        );
        $orderData = array(
            'order_state' => 0
        );
        $res = $orderRelationMod->doUpdate($relationData);
        $orderMod->doEdit($rs['id'], $orderData);
        return $res;
    }

    /**
     * 修改订单的发货时间
     * @author tangp
     * @date 2019-01-29
     * @param int $store_id 区域店铺id
     * @param int $order_sn 订单号
     * @return array
     */
    public function update_ship_time($store_id, $order_sn)
    {
        $initMod =& m('init');
        $orderRelationMod = &m('orderRelation' . $store_id);
        $orderMod = &m('order' . $store_id);
        $rs = $orderMod->getOne(array('cond' => "`order_sn` = '{$order_sn}'", 'fields' => "id"));
        $relationData = array(   //关联表数据
            "table" => "order_relation_" . $store_id,
            "cond" => "order_id=" . $rs['id'],
            "set" => array(
                'ship_time' => time(),
            ),
        );
        $orderData = array(
            'order_state' => 30
        );
        $orderMod->doEdit($rs['id'], $orderData);
        $res = $orderRelationMod->doUpdate($relationData);
        return $res;
    }

    /**
     * 修改订单的配送时间
     * @author tangp
     * @date  2019-01-29
     * @param int $store_id 区域店铺id
     * @param int $order_sn 订单号
     * @return mixed
     */
    public function update_delivery_time($store_id, $order_sn)
    {
        $orderRelationMod = &m('orderRelation' . $store_id);
        $orderMod = &m('order' . $store_id);
        $rs = $orderMod->getOne(array('cond' => "`order_sn` = '{$order_sn}'", 'fields' => "id"));
        $relationData = array(   //关联表数据
            "table" => "order_relation_" . $store_id,
            "cond" => "order_id=" . $rs['id'],
            "set" => array(
                'delivery_time' => time(),
            ),
        );
        $orderData = array(
            'order_state' => 40
        );
        $orderMod->doEdit($rs['id'], $orderData);
        $res = $orderRelationMod->doUpdate($relationData);
        return $res;
    }

    /**
     * 接单
     * @author gao
     * @date  2019-04-10
     * @param int $store_id 区域店铺id
     * @param int $order_sn 订单号
     * @return mixed
     */
    public function update_receive_time($store_id, $order_sn, $receiveUser)
    {
        $orderRelationMod = &m('orderRelation' . $store_id);
        $orderMod = &m('order' . $store_id);
        $rs = $orderMod->getOne(array('cond' => "`order_sn` = '{$order_sn}'", 'fields' => "id"));
        $relationData = array(   //关联表数据
            "table" => "order_relation_" . $store_id,
            "cond" => "order_id=" . $rs['id'],
            "set" => array(
                'receive_time' => time(),
                'receive_user' => $receiveUser
            ),
        );
        $orderData = array(
            'order_state' => 25
        );
        $orderMod->doEdit($rs['id'], $orderData);
        $res = $orderRelationMod->doUpdate($relationData);
        return $res;
    }

    /**
     * 自提接单
     * @author tangp
     * @date  2019-04-22
     * @param int $store_id 区域店铺id
     * @param int $order_sn 订单号
     * @param int $receiveUser 接单人
     * @return mixed
     */
    public function update_receive($store_id, $order_sn, $receiveUser)
    {
        $orderRelationMod = &m('orderRelation' . $store_id);
        $orderMod = &m('order' . $store_id);
        $rs = $orderMod->getOne(array('cond' => "`order_sn` = '{$order_sn}'", 'fields' => "id"));
        $relationData = array(   //关联表数据
            "table" => "order_relation_" . $store_id,
            "cond" => "order_id=" . $rs['id'],
            "set" => array(
                'receive_time' => time(),
                'receive_user' => $receiveUser
            ),
        );
        $orderData = array(
            'order_state' => 40
        );
        $orderMod->doEdit($rs['id'], $orderData);
        $res = $orderRelationMod->doUpdate($relationData);
        return $res;
    }

    /**
     * 修改订单的收货时间和收货来源
     * @author tangp
     * @date  2019-01-29
     * @param int $store_id 区域店铺id
     * @param int $order_sn 订单号
     * @param int $receipt_source 1、自动脚本 2、小程序  3、公众号
     * @return mixed
     */
    public function update_receipt_time($store_id, $order_sn, $receipt_source)
    {
        $orderRelationMod = &m('orderRelation' . $store_id);
        $orderMod = &m('order' . $store_id);
        $rs = $orderMod->getOne(array('cond' => "`order_sn` = '{$order_sn}'", 'fields' => "id"));
        $orderRelationData = $orderRelationMod->getOne(array('cond' => "`order_id` = '{$rs['id']}'", 'fields' => "payment_time"));
        $relationData = array(   //关联表数据
            "table" => "order_relation_" . $store_id,
            "cond" => "order_id=" . $rs['id'],
            "set" => array(
                'receipt_time' => time(),
                'receipt_time_difference' => time() - $orderRelationData['payment_time'],
                'receipt_source' => $receipt_source
            ),
        );
        $orderData = array(
            "table" => "order_" . $store_id,
            "cond" => "order_sn = '{$order_sn}'",
            "set" => array(
                "order_state" => 50
            ),
        );
        $orderMod->doUpdate($orderData);
        $res = $orderRelationMod->doUpdate($relationData);
        return $res;
    }

    /**
     * 用户提交订单的退款时间和退款来源
     * @author tangp
     * @date  2019-01-29
     * @param int $store_id 区域店铺id
     * @param int $order_sn 订单号
     * @param int $refund_source 退款来源 1、小程序  2、公众号
     * @return mixed
     */
    public function update_refund_time($store_id, $order_sn, $refund_source)
    {
        $orderRelationMod = &m('orderRelation' . $store_id);
        $orderMod = &m('order' . $store_id);
        $rs = $orderMod->getOne(array('cond' => "`order_sn` = '{$order_sn}'", 'fields' => "id"));
        $relationData = array(   //关联表数据
            "table" => "order_relation_" . $store_id,
            "cond" => "order_id=" . $rs['id'],
            "set" => array(
                'refund_time' => time(),
                'refund_source' => $refund_source
            ),
        );
        $orderData = array(
            "table" => "order_" . $store_id,
            "cond" => "order_sn = '{$order_sn}'",
            "set" => array(
                "order_state" => 60
            ),
        );
        $orderMod->doUpdate($orderData);
        $res = $orderRelationMod->doUpdate($relationData);
        return $res;
    }

    /**
     * 后台审核的退款时间和退款来源
     * @author tangp
     * @date  2019-01-29
     * @param int $store_id 区域店铺id
     * @param int $order_sn 订单号
     * @param int $refund_source 退款来源 1、小程序  2、公众号
     * @return mixed
     */
    public function set_refund_time($store_id, $order_sn, $refund_source)
    {
        $orderRelationMod = &m('orderRelation' . $store_id);
        $orderMod = &m('order' . $store_id);
        $rs = $orderMod->getOne(array('cond' => "`order_sn` = '{$order_sn}'", 'fields' => "id"));
        $relationData = array(   //关联表数据
            "table" => "order_relation_" . $store_id,
            "cond" => "order_id=" . $rs['id'],
            "set" => array(
                'refund_time' => time(),
                'refund_source' => $refund_source
            ),
        );
        $orderData = array(
            'order_state' => 60
        );
        $orderMod->doEdit($rs['id'], $orderData);
        $res = $orderRelationMod->doUpdate($relationData);
        return $res;
    }

    /**
     * 修改订单的评论时间和评论来源
     * @author tangp
     * @date  2019-01-29
     * @param int $store_id 区域店铺id
     * @param int $order_sn 订单号
     * @param int $comment_source 评论来源 1、小程序  2、公众号
     * @return mixed
     */
    public function update_comment_time($store_id, $order_sn, $comment_source)
    {
        $orderRelationMod = &m('orderRelation' . $store_id);
        $orderMod = &m('order' . $store_id);
        $rs = $orderMod->getOne(array('cond' => "`order_sn` = '{$order_sn}'", 'fields' => "id"));
        $relationData = array(   //关联表数据
            "table" => "order_relation_" . $store_id,
            "cond" => "order_id=" . $rs['id'],
            "set" => array(
                'comment_time' => time(),
                'comment_source' => $comment_source
            ),
        );
        $orderData = array(
            "table" => "order_" . $store_id,
            "cond" => "order_sn='{$order_sn}'",
            "set" => array(
                "evaluation_state" => 1
            ),
        );
        $orderMod->doUpdate($orderData);
        $res = $orderRelationMod->doUpdate($relationData);
        return $res;
    }

    /**
     * 修改订单的提示音
     * @author gao
     * @date  2019-01-31
     * @param int $store_id 区域店铺id
     * @param int $order_sn 订单
     * @return mixed
     */
    public function update_warning_tone($store_id, $order_sn)
    {
        //更新老表数据
        $sql = 'UPDATE bs_order SET warning_tone = 2 WHERE store_id= order_sn in ("' . str_replace(',', '","', $order_sn) . '")';
        $this->doEditSql($sql);
        //更新新表数据
        $orderStore = "bs_order_" . $store_id;
        $orderDetailsStore = "bs_order_details_" . $store_id;
        $select_sql = 'select id from ' . $orderStore . ' where order_sn in ("' . str_replace(',', '","', $order_sn) . '")';
        $orderIds = $this->querySql($select_sql);
        foreach ($orderIds as $v) {
            $orderIdArr[] = $v['id'];
        }
        $orderIdstr = implode(',', $orderIdArr);
        $upd_sql = 'UPDATE ' . $orderDetailsStore . ' SET warning_tone = 2 WHERE order_id in (' . $orderIdstr . ')';
        $this->doEditSql($upd_sql);
        return true;
    }

    /**
     * 修改订单的mark字段
     * @author gao
     * @date  2019-01-31
     * @param int $store_id 区域店铺id
     * @param int $order_sn 订单
     * @return mixed
     */
    public function updateOrderMark($store_id, $order_sn)
    {
        $orderMod = &m('order' . $store_id);
        $sql = "update bs_order_{$store_id} set mark = 0  where order_sn = '{$order_sn}' ";
        $res = $orderMod->doEditSql($sql);
        return $res;
    }


    /**
     * 分单
     * @author gao
     * @date  2019-03-07
     * @return mixed
     */
    public function separateOrder($order_sn, $paymentType, $paySn) //$paymentType 支付方式   $order_sn 订单号  $paySn支付码 $fxSource 分销订单来源
    {
        $orderGoodsMod =& m('orderGoods'); //订单商品模型
        $couponMod =& m('coupon'); //优惠劵模型
        $fxOrderMod =& m('fxOrder'); //分销订单模型
        $orderData = $this->getOne(array('cond' => "`order_sn` = '{$order_sn}'", 'fields' => "*"));
        if (strlen($orderData['sendout']) == 1) {
            /*      $fxOrderMod->addFxOrderByOrderSn($order_sn, 2);*/
            return false;
        }
        $couponDiscount = $orderData['cp_amount']; //订单优惠劵优惠金额
        $fxDiscount = $orderData['discount'];  //订单分销优惠金额
        $couponId = $orderData['cid'];  //优惠劵id
        $couponData = $couponMod->getOne(array('cond' => "`id` = '{$couponId}'", 'fields' => "type")); //1代表 抵扣劵 2是兑换券
        $couponType = $couponData['type']; //优惠劵类型  1代表 抵扣劵 2是兑换券
        $pointDiscount = $orderData['pd_amount']; //订单睿积分优惠金额
        $orderGoodsAmount = $orderData['goods_amount'];//订单商品金额
        $orderAmount = $orderData['order_amount'] - $orderData['shipping_fee']; //订单支付金额
        $storeId = $orderData['store_id']; //店铺id
        $goodsSendout = explode(',', $orderData['sendout']);
        $orderGoodsData = $orderGoodsMod->getData(array('cond' => "order_id = '{$order_sn}'")); //订单商品信息
        $couponFlag = 0;
        foreach ($orderGoodsData as $key => $val) {
            if ($couponType == 2 && $couponDiscount == $val['goods_pay_price'] && $couponFlag == 0) { //判断了是否使用了兑换劵
                $counponGoodsId = $val['goods_id']; //使用了兑换劵的商品id
                $orderGoodsAmount -= $couponDiscount;
                $couponFlag = 1;
            }
            $goodsKey = $orderGoodsData[$key]['goods_id'] . "-" . $orderGoodsData[$key]['spec_key'];
            $goodsData[$goodsKey] = $val;
        }
        $sort = array();
        foreach ($goodsSendout as $k => $v) {
            $sendoutTemp = explode('-', $v);
            $sort[] = $sendoutTemp[1];
        }
        array_multisort($sort, SORT_ASC, $goodsSendout);
        foreach ($goodsSendout as $key => $val) {
            $temp = explode('-', $val);
            $sendout[$temp[1]][] = $temp[0] . "-" . $temp[2];

        }
        $endSendout = end($sendout);
        $otherOrderAmount = 0;
        $otherCouponDiscount = 0;
        $otherFxDiscount = 0;
        $otherPointDiscount = 0;
        $shippingFee = $orderData['shipping_fee'];
        static $flag = 0;
        foreach ($sendout as $key => $val) {
            //配送费
            if ($key == 2) {
                $orderData['shipping_fee'] = $shippingFee;
            } else {
                $orderData['shipping_fee'] = 0;
            }
            $orderSn = $order_sn . "-" . $key;
            $orderData['order_sn'] = $orderSn;
            $goodsAmount = 0;
            $rate = 0;
            $oldGoodsAmount = 0;
            foreach ($val as $k => $v) {
                $goodsTemp = explode('-', $v);
                $oldGoodsAmount += $goodsData[$goodsTemp[0] . "-" . $goodsTemp[1]]['goods_pay_price'] * $goodsData[$goodsTemp[0] . "-" . $goodsTemp[1]]['goods_num'];
                $goodsAmount += $goodsData[$goodsTemp[0] . "-" . $goodsTemp[1]]['goods_pay_price'] * $goodsData[$goodsTemp[0] . "-" . $goodsTemp[1]]['goods_num'];
                if (!empty($counponGoodsId) && $goodsTemp[0] == $counponGoodsId && $flag != 1) {
                    $flag = 1;
                    $goodsAmount -= $couponDiscount;
                    $otherCouponDiscount += $couponDiscount;
                    $sql = "update bs_coupon_log set  order_sn = '{$orderSn}'  where order_sn ='{$order_sn}'"; //订单商品表状态修改
                    $couponLogMod =& m('couponLog');
                    $couponLogMod->doEditSql($sql);
                }
            }
            if ($flag == 1 && $couponType == 2) {
                $orderData['cp_amount'] = $couponDiscount; //使用了兑换券的订单
                $orderData['cid'] = $couponId;
                $flag = 2;
            } else {
                $orderData['cp_amount'] = 0; //使用了兑换券的订单
                $orderData['cid'] = 0;
            }
            if ($couponType == 1) {
                $orderData['cp_amount'] = $couponDiscount; //使用了兑换券的订单
                $orderData['cid'] = $couponId;
            }
            $rate = $goodsAmount / $orderGoodsAmount; //换算比例
            if ($val == $endSendout) {
                $orderData['goods_amount'] = $oldGoodsAmount;
                if ($key == 2) {
                    $orderData['order_amount'] = ($orderAmount - $otherOrderAmount + $shippingFee) < 0 ? 0 : $orderAmount - $otherOrderAmount + $shippingFee;
                } else {
                    $orderData['order_amount'] = ($orderAmount - $otherOrderAmount) < 0 ? 0 : $orderAmount - $otherOrderAmount;
                }
                if (empty($counponGoodsId)) { //没用兑换券
                    $orderData['cp_amount'] = $couponDiscount - $otherCouponDiscount;
                }
                $orderData['discount'] = $fxDiscount - $otherFxDiscount;
                $orderData['pd_amount'] = $pointDiscount - $otherPointDiscount;
                $orderData['sendout'] = $key;
            } else {
                $orderData['goods_amount'] = $oldGoodsAmount; //订单商品金额
                $rateOrderAmount = number_format($rate * $orderAmount, 2, ".", "");
                if ($key == 2) {
                    $orderData['order_amount'] = $rateOrderAmount + $shippingFee; //订单支付金额
                } else {
                    $orderData['order_amount'] = $rateOrderAmount;
                }
                $otherOrderAmount += $rateOrderAmount;
                if (empty($counponGoodsId)) { //没用兑换券
                    $rateCouponDiscount = number_format($rate * $couponDiscount, 2, ".", "");;
                    $orderData['cp_amount'] = $rateCouponDiscount; //订单优惠劵金额
                    $otherCouponDiscount += $rateCouponDiscount;
                }
                $rateFxDiscount = number_format($rate * $fxDiscount, 2, ".", "");;
                $orderData['discount'] = $rateFxDiscount; //订单分销金额
                $otherFxDiscount += $rateFxDiscount;
                $ratePointDiscount = number_format($rate * $pointDiscount, 2, ".", "");
                $orderData['pd_amount'] = $ratePointDiscount; //订单睿积分金额
                $otherPointDiscount += $ratePointDiscount;
                $orderData['sendout'] = $key;
            }
            unset($orderData['order_id']);
            $orderData['order_state'] = 10; //订单变成支付状态
            $this->doInsert($orderData);
            //获取新表信息
            $sql = "select * from bs_order_{$orderData['store_id']} where order_sn='{$order_sn}'";
            $newData = $this->querySql($sql);
            $orderData['source'] = $newData[0]['source'];
            $this->produceOrder($orderData, $paymentType, $paySn); //拆表生成新表的订单
            foreach ($val as $k1 => $v1) {
                $temp = explode('-', $v1);
                $sql = "update bs_order_goods set  order_id = '{$orderSn}'  where order_id ='{$order_sn}'  and goods_id = " . $temp[0] . " and spec_key = '{$temp[1]}'"; //订单商品表状态修改
                $orderGoodsMod->doEditSql($sql);
            }
        }
        $sql = "update bs_order set mark = 0  where order_sn = '{$order_sn}' "; //修改老表状态
        $this->doEditSql($sql);
        $this->updateOrderMark($storeId, $order_sn); //修改新表的状态
    }


    /**
     * 修改退款审核时间和退款审核人
     * @author tangp
     * @date 2019-02-26
     * @param int $store_id 区域店铺id
     * @param int $order_sn 订单号
     * @param int $user_id 操作员id
     * @return mixed
     */
    public function update_refund_review_time($store_id, $order_sn, $user_id)
    {

        $orderRelationMod = &m('orderRelation' . $store_id);
        $orderMod = &m('order' . $store_id);
        $rs = $orderMod->getOne(array('cond' => "`order_sn` = '{$order_sn}'", 'fields' => "id"));
        $relationData = array(   //关联表数据
            "table" => "order_relation_" . $store_id,
            "cond" => "order_id=" . $rs['id'],
            "set" => array(
                'refund_review_time' => time(),
                'refund_review_user' => $user_id
            ),
        );
        $orderData = array(
            "table" => "order_" . $store_id,
            "cond" => "order_sn= '{$order_sn}'",
            "set" => array(
                "order_state" => 60
            ),
        );
        $orderMod->doUpdate($orderData);
        $res = $orderRelationMod->doUpdate($relationData);
        return $res;
    }


    /**
     * @author gao
     * @date 2019-03-20
     * @param int $storeId 区域店铺id
     * @param  $data 查询条件
     * @return mixed
     */
    public function orderList($data, $p)
    {
        $orderGoodsMod = &m('orderGoods');
        $userCouponMod = &m('userCoupon');
        $systemConsoleMod =& m('systemConsole');
        $storeSourceMod =& m('storeSource');
        $storeId = $data['storeId']; //店铺id
        $orderSn = $data['orderSn']; //订单编号
        $buyerPhone = $data['buyerPhone']; //购买人电话
        $buyerName = $data['buyerName']; //购买人名称
        $goodsName = $data['goodsName'];  //商品名称
        $payMethod = $data['payMethod']; //支付方式
        $takeStatus = $data['takeStatus']; //接单状态
        $orderStatus = $data['orderStatus']; //订单状态
        $shippingMethod = $data['shippingMethod']; //配送方式
        $refundState = $data['refundState']; //退款状态
        $selectStoreId = $data['selectStoreId']; //选择的店铺
        $clickandview = !empty($data['clickandview']) ? htmlspecialchars(trim($data['clickandview'])) : 0;
        $where = " WHERE o.mark = 1 ";
        if ($clickandview == 1) {
            $where .= " and o.order_state = 20 and od.clickandview = 1 ";
        }
        if (!empty($selectStoreId)) {
            $storeId = $selectStoreId;
            $where .= " and o.store_id = {$selectStoreId}";
        }
        if (!empty($orderSn)) {
            $where .= " and o.order_sn like '%{$orderSn}%'";
        }
        if (!empty($buyerPhone)) {
            $where .= " and u.phone ={$buyerPhone} or ua.phone = {$buyerPhone} ";
        }
        if (!empty($buyerName)) {
            $where .= " and (u.username like '%{$buyerName}%' and od.address_id = 0) or (ua.`name`  like '%{$buyerName}%' and od.address_id != 0 ) ";
        }
        if (!empty($goodsName)) {
            $where .= " and og.goods_name like '%{$goodsName}%'";
        }
        if (!empty($payMethod)) {
            $where .= " and ore.payment_type = {$payMethod}";
        }
        if (!empty($takeStatus)) {
            if ($takeStatus == 1) {
                $where .= " and ore.delivery_time != 0 ";
            } else {
                $where .= " and ore.delivery_time = 0 and o.order_state >=20 ";
            }
        }
        if (!empty($orderStatus)) {
            if ($orderStatus == 1) {
                $orderStatus = 0;
            }
            $where .= " and o.order_state = {$orderStatus}";
        }
        if (!empty($shippingMethod)) {
            $where .= " and o.sendout = {$shippingMethod} ";
        }
        if (!empty($refundState)) {
            $where .= " ";
        }
        if (!empty($goodsName)) {
            $sql = <<<SQL
            SELECT
            DISTINCT
            o.order_sn,o.goods_amount,o.order_amount,o.sendout,o.add_time,o.order_state,o.evaluation_state,
            od.point_discount,od.coupon_discount,od.shipping_fee,od.fx_money,ore.delivery_time,
            o.store_id,ore.payment_source,od.fx_user_id,ua.phone as aphone,ua.name as aname,u.phone as uphone,u.username as uname,o.source,od.sendout_time
            FROM bs_order_{$storeId} AS o
            LEFT JOIN bs_order_goods AS og ON  o.order_sn = og.order_id
            LEFT JOIN bs_order_relation_{$storeId}  AS ore ON ore.order_id = o.id
            LEFT JOIN bs_order_details_{$storeId} AS od ON od.order_id = o.id
            LEFT JOIN bs_user_address AS ua ON ua.id = od.address_id
            LEFT JOIN bs_user AS  u ON  u.id=o.buyer_id
            {$where}
            ORDER BY o.id DESC
SQL;
        } else {
            $sql = <<<SQL
            SELECT
            DISTINCT
            o.order_sn,o.goods_amount,o.order_amount,o.sendout,o.add_time,o.order_state,o.evaluation_state,
            od.point_discount,od.coupon_discount,od.shipping_fee,od.fx_money,ore.delivery_time,
            o.store_id,ore.payment_source,od.fx_user_id,ua.phone as aphone,ua.name as aname,u.phone as uphone,u.username as uname,o.source,od.sendout_time
            FROM bs_order_{$storeId} AS o
            LEFT JOIN bs_order_relation_{$storeId}  AS ore ON ore.order_id = o.id
            LEFT JOIN bs_order_details_{$storeId} AS od ON od.order_id = o.id
            LEFT JOIN bs_user_address AS ua ON ua.id = od.address_id
            LEFT JOIN bs_user AS  u ON  u.id=o.buyer_id
            {$where}
            ORDER BY o.id DESC             
SQL;
        }

        $orderData = $this->querySqlPageData($sql);
        if (empty($p)) {
            $p = 1;
        }
        foreach ($orderData['list'] as $key => $val) {
            //赠送兑换劵条件
            $couponSql = <<<SQL
                    SELECT
	                c.id
                    FROM
	                bs_coupon AS c
                    LEFT JOIN bs_coupon_log AS cl ON c.id = cl.coupon_id
                    WHERE
	                c.type = 2  AND cl.order_sn = '{$val['order_sn']}'          
SQL;
            $isDui = $orderGoodsMod->querySql($couponSql);
            $sendVoucher = $userCouponMod->getOne(array('cond' => "`order_sn`='{$val['order_sn']}'", 'id'));//是否赠送了兑换券
            $timeData = $systemConsoleMod->getOne(array('cond' => "`type` =3 and status=1", 'fields' => 'start_time,end_time'));
            if (!empty($timeData)) {
                if ($timeData['start_time'] < time() && $timeData['end_time'] > time()) {
                    $activityStatus = 1;
                }
            }
            if (empty($isDui) && $activityStatus == 1 && empty($sendVoucher)) {
                $orderData['list'][$key]['isDui'] = 1;
            }
            //审核条件
            $appointSql = <<<SQL
                    SELECT
	                id
                    FROM
	                bs_appoint_log
	                WHERE
	                is_ckeck = 0 AND order_sn = '{$val['order_sn']}'         
SQL;
            $appointLog = $this->querySql($appointSql);

            if (!empty($appointLog)) {
                $orderData['list'][$key]['isCheck'] = 1;
            }
            $orderGoodsData = $orderGoodsMod->getOrderGoods($val['order_sn']);
            $num = count($orderGoodsData);
            if ($num > 3) {
                $fourGoodsData = array_slice($orderGoodsData, 0, 3);
                $orderData['list'][$key]['goods'] = $fourGoodsData;
                $orderData['list'][$key]['otherGoods'] = array_slice($orderGoodsData, 3);
            } else {
                $orderData['list'][$key]['goods'] = $orderGoodsData;
            }
            $sourceImg = $storeSourceMod->getOne(array('cond' => array("`id`={$val['payment_source']} and `store_id`= {$val['store_id']}")));
            $orderData['list'][$key]['num'] = count($orderGoodsData);
            $orderData['list'][$key]['sort'] = $orderData['total'] - ($p - 1) * 20 - $key;
            $orderData['list'][$key]['sourceImg'] = $sourceImg['img'];
            $orderData['list'][$key]['order_status'] = $this->getOrderStatusName($val['sendout'], $val['order_state'], $val['evaluation_state']);
            switch ($val['sendout']) {
                case 0:
                    $orderData['list'][$key]['sendout'] = '';
                    break;
                case 1:
                    $orderData['list'][$key]['sendout'] = '到店自提';
                    break;
                case 2:
                    $orderData['list'][$key]['sendout'] = '配送上门';
                    break;
                case 3:
                    $orderData['list'][$key]['sendout'] = '邮寄托运';
                    break;
                case 4:
                    $orderData['list'][$key]['sendout'] = '海外代购';
                    break;
            };
        }
        $info = array(
            'orderData' => $orderData,
            'coditionData' => $data
        );
        return $info;
    }

    /**
     * 通过sendout、evaluation_state和order_status获取订单状态
     */
    public function getOrderStatusName($sendout, $status, $evaluation)
    {
        $statusName = '';
        if (empty($sendout)) {
            $sendout = 0;
        }
        if (in_array($sendout, array(0, 1, 2))) {//暂时只考虑补单、自提、配送
            switch ($status) {
                case 0:
                    $statusName = '已取消';
                    break;
                case 10:
                    $statusName = '待付款';
                    break;
                case 20:
                    $statusName = '已付款';
                    break;
                case 25:
                    $statusName = '已接单';
                    break;
                case 30:
                    $statusName = $sendout == 1 ? '待收货' : '已发货';
                    break;
                case 40:
                    $statusName = $sendout == 1 ? '待收货' : '区域配送';
                    break;
                case 50:
                    $statusName = $evaluation == 1 ? '已评价' : '已收货';
                    break;
                case 60:
                    $statusName = '退款中';
                    break;
                case 70:
                    $statusName = '已退款';
                    break;
            }
        }
        return $statusName;
    }


    /**
     * 获取商品的价格
     * @param $store_id
     * @param $goods_id
     * @param $spec_key
     * @return mixed
     */
    public function getPrice($store_id, $goods_id, $spec_key)
    {
        $sql = "SELECT * FROM bs_store_goods_spec_price WHERE store_goods_id=" . $goods_id;
        $storeGoodsSpecPriceMod = &m('storeGoodsSpecPrice');
        $res = $storeGoodsSpecPriceMod->querySql($sql);
        if (empty($res)) {
            $sqll = "SELECT * FROM bs_store_goods WHERE id={$goods_id} AND store_id = {$store_id} AND mark=1";
            $storeGoodsMod = &m('storeGoods');
            $result = $storeGoodsMod->querySql($sqll);
            return $result[0]['shop_price'];
        } else {
            $key_arr = explode('_', $spec_key);
            $key_pailie = $this->arrangement($key_arr, count($key_arr));
            foreach ($key_pailie as $v) {
                $spec_arr[] = implode('_', $v);
            }
            $sqls = 'SELECT * FROM bs_store_goods_spec_price WHERE store_goods_id = ' . $goods_id . ' AND `key` in ("' . implode('","', $spec_arr) . '")';
            $result = $storeGoodsSpecPriceMod->querySql($sqls);
            return $result[0]['price'];
        }

    }

    /**
     * 获取商品的实际成交价
     * @author tangp
     * @date 2019-02-20
     * @param $store_id
     * @param $goods_id
     * @param $spec_key
     * @return string
     */
    public function getGoodsPayPrice($store_id, $goods_id, $spec_key)
    {
        $storeGoodsSpecPriceMod = &m('storeGoodsSpecPrice');
        $storeMod = &m('store');
        $storeGoodsMod = &m('storeGoods');
        $sql = "SELECT * FROM bs_store_goods_spec_price WHERE store_goods_id=" . $goods_id;
        $res = $storeGoodsSpecPriceMod->querySql($sql);
        $sqll = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $store_id;
        $discountInfo = $storeMod->querySql($sqll);
        if (empty($res)) {
            $sqls = "SELECT * FROM bs_store_goods WHERE id={$goods_id} AND store_id = {$store_id} AND mark=1";
            $result = $storeGoodsMod->querySql($sqls);
            $price = number_format($result[0]['shop_price'] * $discountInfo[0]['store_discount'], 2, '.', '');
        } else {
            $sqls = "SELECT * FROM bs_store_goods_spec_price WHERE store_goods_id={$goods_id} AND `key` = '{$spec_key}'";
            $result = $storeGoodsSpecPriceMod->querySql($sqls);
            $price = number_format($result[0]['price'] * $discountInfo[0]['store_discount'], 2, '.', '');

        }
        return $price;
    }

    /**
     * 订单取消
     * @author jh
     * @date 2019-02-20
     */
    public function cancleOrder($order_sn)
    {
        $userMod = &m('user');
        $pointLogMod = &m("pointLog");
        $orderGoodsMod = &m('orderGoods');
        //订单信息
        $orderInfo = $this->getOne(array("cond" => "order_sn= '{$order_sn}'"));

        //取消订单退还优惠劵
        $couponLogMod =& m('couponLog');
        // $couponCrond="order_id = ".$orderInfo['order_id'];
        $couponCrond = "order_sn = '{$order_sn}' ";  // by xt 2019.03.21
        $couponLogMod->doDrops($couponCrond);
        //返还用户的积分值
        $point_log = $pointLogMod->getOne(array("cond" => "order_sn='{$order_sn}'"));
        if ($point_log) {
            $user_id = $point_log['userid'];
            $user_info = $userMod->getOne(array("cond" => "id=" . $user_id));
            $user_point = $user_info['point'] + $point_log['expend'];
            $res = $userMod->doEdit($user_id, array("point" => $user_point));
            //积分日志
            if ($res) {
                $logMessage = "取消订单：" . $order_sn . " 获取：" . $point_log['expend'] . "睿积分";
                $pointLogMod->add($user_info['phone'], $logMessage, $user_id, $point_log['expend'], '-');
            }
        }
        //修改订单信息
        $this->doEditSpec(array('order_sn' => "{$order_sn}"), array('order_state' => 0));
        $orderGoodsMod->doEditSpec(array('order_id' => "{$order_sn}"), array('order_state' => 0));
        $this->update_cancel_time($orderInfo['store_id'], $order_sn);
        return true;
    }

    /**
     * 小程序取消订单
     * @param $order_sn
     * @return bool
     */
    public function xcxCancleOrder($order_sn)
    {
        $userMod = &m('user');
        $pointLogMod = &m("pointLog");
        $orderGoodsMod = &m('orderGoods');
        //订单信息
//        $orderInfo = $this->getOne(array("cond" => "order_sn = '{$order_sn}'"));
//        if ($orderInfo['order_state'] == 0) {
//            return $orderInfo['order_state'];
//        }
        $sql = "SELECT store_id FROM bs_order WHERE order_sn = '{$order_sn}'";
        $userOrderInfo = &m('userOrder')->querySql($sql);
        $order_sql = "SELECT * FROM bs_order_{$userOrderInfo[0]['store_id']} WHERE order_sn = '{$order_sn}'";
        $data = &m('userOrder')->querySql($order_sql);
        if ($data[0]['order_state'] == 0){
            return $data['order_state'];
        }
        //取消订单退还优惠劵
        $couponLogMod =& m('couponLog');
        // $couponCrond="order_id = ".$orderInfo['order_id'];
        $couponCrond = "order_sn = '{$order_sn}' ";  // by xt 2019.03.21
        $couponLogMod->doDrops($couponCrond);
        //返还用户的积分值
        $point_log = $pointLogMod->getOne(array("cond" => "order_sn = '{$order_sn}'"));
        if ($point_log) {
            $user_id = $point_log['userid'];
            $user_info = $userMod->getOne(array("cond" => "id=" . $user_id));
            $user_point = $user_info['point'] + $point_log['expend'];
            $res = $userMod->doEdit($user_id, array("point" => $user_point));
            //积分日志
            if ($res) {
                $logMessage = "取消订单：" . $order_sn . " 获取：" . $point_log['expend'] . "睿积分";
                $pointLogMod->add($user_info['phone'], $logMessage, $user_id, $point_log['expend'], '-');
            }
        }
        //修改订单信息
        $this->doEditSpec(array('order_sn' => "{$order_sn}"), array('order_state' => 0));
        $orderGoodsMod->doEditSpec(array('order_id' => "{$order_sn}"), array('order_state' => 0));
        $this->update_cancel_time($userOrderInfo[0]['store_id'], $order_sn);
        return true;
    }
    /**
     * w订单取消
     * @author jh
     * @date 2019-02-20
     */
    public function wxCancleOrder($order_sn)
    {
        $userMod = &m('user');
        $pointLogMod = &m("pointLog");
        $orderGoodsMod = &m('orderGoods');
        //订单信息
        $orderInfo = $this->getOne(array("cond" => "order_sn = '{$order_sn}'"));
        if ($orderInfo['order_state'] == 0) {
            return $orderInfo['order_state'];
        }
        //取消订单退还优惠劵
        $couponLogMod =& m('couponLog');
        // $couponCrond="order_id = ".$orderInfo['order_id'];
        $couponCrond = "order_sn = '{$order_sn}' ";  // by xt 2019.03.21
        $couponLogMod->doDrops($couponCrond);
        //返还用户的积分值
        $point_log = $pointLogMod->getOne(array("cond" => "order_sn = '{$order_sn}'"));
        if ($point_log) {
            $user_id = $point_log['userid'];
            $user_info = $userMod->getOne(array("cond" => "id=" . $user_id));
            $user_point = $user_info['point'] + $point_log['expend'];
            $res = $userMod->doEdit($user_id, array("point" => $user_point));
            //积分日志
            if ($res) {
                $logMessage = "取消订单：" . $order_sn . " 获取：" . $point_log['expend'] . "睿积分";
                $pointLogMod->add($user_info['phone'], $logMessage, $user_id, $point_log['expend'], '-');
            }
        }
        //修改订单信息
        $this->doEditSpec(array('order_sn' => "{$order_sn}"), array('order_state' => 0));
        $orderGoodsMod->doEditSpec(array('order_id' => "{$order_sn}"), array('order_state' => 0));
        $this->update_cancel_time($orderInfo['store_id'], $order_sn);
        return true;
    }

    /**
     * 获取订单信息
     * @param int $user_id
     * @param int $order_sn
     * @param int $storeid
     * @param int $lang
     * @return array
     */
    public function getOrderInfo($user_id, $order_sn, $storeid, $lang)
    {
        $orderGoodsMod = &m('orderGoods');
        $where = ' buyer_id =' . $user_id . " and order_sn = '{$order_sn}'";
        $sql = 'select * from ' . DB_PREFIX . 'order'
            . ' where' . $where . ' and store_id =' . $storeid;
        $data = $this->querySql($sql);

        foreach ($data as $k => $v) {
            $sql = "select o.*,o.goods_id as ogoods_id,l.* from "
                . DB_PREFIX . "order_goods as o left join "
                . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
                . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id "
                . " where o.order_id= '{$v['order_sn']}'" . " and o.refund_state = 0  and lang_id = " . $lang;
            $list = $orderGoodsMod->querySql($sql);

            foreach ($list as $kk => $vv) {
                $list[$kk]['refund_price'] = number_format(($list[$kk]['goods_pay_price'] / $data[$k]['goods_amount']) * $data[$k]['order_amount'], 2, '.', '');
            }
            $data[$k]['goods_list'] = $list;
        }
        return $data;
    }
    /*****************************************************************以上为新的逻辑代码****************************************************************************/
    /**
     * 统计--获取待办事项(默认未付款)
     * @param $order_state      订单状态
     * @param $store_cate_id    区域ID
     * @param $store_id         店铺ID
     * @param $return_id        退款ID,为TRUE 查询所有退款
     * @author: luffy
     * @date  : 2017-11-06
     */
    public function getBacklogCount($order_state = 10, $store_cate_id = 0, $store_id = 0, $return_id = false)
    {
        $sql = "  order_state = {$order_state}";

        if ($order_state == 20) {
            $sql .= " AND refund_state = 0";
        }

        if ($store_id > 0) {
            $sql .= " AND store_id = {$store_id}";
            $storeIds = $store_id;
        } else {
            //获取区域下店铺(不传区域则是获取可用店铺)
            $storeMod = &m('store');
            $storeIds = $storeMod->getStoreIds($store_cate_id, 1);
            $sql .= " AND store_id in ({$storeIds}) ";
        }
        if (is_bool($return_id) && $return_id == TRUE) {
            //$sql = "  (refund_state = 1 or refund_state = 2) ";
            //订单退款数量
            $re_sql = "select COUNT(DISTINCT(order_id)) as num from " . DB_PREFIX . "order_goods as og where refund_state =1 and  store_id in ({$storeIds})";
            $r = $this->querySql($re_sql);
            $orderCount = empty($r) ? 0 : $r[0]['num'];
            return $orderCount;
            //end
        }

        $query = array('cond' => $sql);
        $orderCount = $this->getCount($query);
        return $orderCount;
    }

    /**
     * 统计--获取交易状况
     * @param $order_state      订单状态
     * @param $store_cate_id    区域ID
     * @param $store_id         店铺ID
     * @param $op 0 订单  1 付款
     * @param $tm
     * @author: luffy
     * @date  : 2017-11-06
     */
    public function getUpOrderCount($order_state, $store_cate_id = 0, $store_id = 0, $op = 0, $tm = 'day', $timeSetArr,$storeUserId)
    {
        //获取时间组件
        switch ($tm) {
            case 'day':
                $timeArr = $this->getDayTimeArr();
                break;
            case 'yes':
                $timeArr = $this->getYesTimeArr();
                break;
            case 'week':
                $timeArr = $this->getWeekTimeArr();
                break;
            case 'month':
                $timeArr = $this->getMonthTimeArr();
                break;
            case 'year':
                $timeArr = $this->getYearTimeArr();
                break;
            case 'setting':
                $timeArr = $this->getSettingTimeArr($timeSetArr, $storeUserId);
                break;
        }
        $curCount = $this->toUpOrderCount($order_state, $store_cate_id, $store_id, $op, $timeArr['cur'],$storeUserId);
        $yesCount = $this->toUpOrderCount($order_state, $store_cate_id, $store_id, $op, $timeArr['yes'],$storeUserId);

        foreach ($curCount as $key => $value) {
            $diff = $curCount[$key] - $yesCount[$key];

            if ($diff == 0 || ($curCount[$key] == 0 && $yesCount[$key] == 0)) {
                $curCount[$key . 'Sort'] = '';
                $curCount[$key . 'Percent'] = '--';
            } else {
                if ($yesCount[$key] == 0) {
                    $curCount[$key . 'Sort'] = '<i class="inblock iu"></i>';
                    $curCount[$key . 'Percent'] = '100%';
                } else {
                    if ($diff > 0) {
                        $curCount[$key . 'Sort'] = '<i class="inblock iu"></i>';
                    }
                    if ($diff < 0) {
                        $curCount[$key . 'Sort'] = '<i class="inblock id"></i>';
                    }
                    $curCount[$key . 'Percent'] = sprintf("%.2f", abs($diff) / $yesCount[$key] * 100) . '%';
                }
            }
        }
        return $curCount;
    }

    /**
     * 统计--获取交易状况
     * @param $order_state      订单状态
     * @param $store_cate_id    区域ID
     * @param $store_id         店铺ID
     * @param $op 0 订单  1 付款
     * @param $timeConf         时间段
     * @author: luffy
     * @date  : 2017-11-06
     */
    public function toUpOrderCount($order_state, $store_cate_id, $store_id, $op, $timeConf = array())
    {
        $where = " where a.order_state >= {$order_state} and a.order_state != 70 and b.payment_time between {$timeConf[0]} and {$timeConf[1]}";
        if ($store_id > 0) {
            //下单/付款 笔数
            $sql = "select count(*) as total from ".DB_PREFIX."order_{$store_id} as a 
                left join ".DB_PREFIX."order_relation_{$store_id} as b on a.id = b.order_id ".$where;
            $count = $this->querySql($sql);
            $upOrderCount['upOrderNum'] = $count[0]['total'];
            //下单/付款 人数
            $sql2 = "select distinct a.buyer_id from ".DB_PREFIX."order_{$store_id} as a 
                left join ".DB_PREFIX."order_relation_{$store_id} as b on a.id = b.order_id ".$where;
            $member = $this->querySql($sql2);
            $upOrderCount['upOrderMem'] = count($member);
            //下单/付款 金额
            $sql3 = "select sum(a.order_amount) as amount from ".DB_PREFIX."order_{$store_id} as a 
                left join ".DB_PREFIX."order_relation_{$store_id} as b on a.id = b.order_id ".$where;
            $amount = $this->querySql($sql3);
            $upOrderCount['upOrderAmount'] = $amount[0]['amount'];
        } else {
            //获取订单中所有店铺
            $storeSql = "select DISTINCT(store_id) AS store_id from bs_order where mark = 1";
            $storeInfo = $this->querySql($storeSql);
            $count = array();
            $member = array();
            $amount = array();
            //循环获取订单列表
            foreach ($storeInfo as $value) {
                //下单/付款 笔数
                $sql1 = "select count(*) as total from bs_order_{$value['store_id']} as a 
                  left join bs_order_relation_{$value['store_id']} as b on a.id = b.order_id ".$where;
                $countTemp = $this->querySql($sql1);
                $count = array_merge($count, $countTemp);
                //下单/付款 人数
                $sql2 = "select distinct a.buyer_id  from bs_order_{$value['store_id']} as a 
                  left join bs_order_relation_{$value['store_id']} as b on a.id = b.order_id ".$where;
                $memberTemp = $this->querySql($sql2);
                $member = array_merge($member, $memberTemp);
                //下单/付款 金额
                $sql2 = "select sum(a.order_amount) as amount from bs_order_{$value['store_id']} as a 
                  left join bs_order_relation_{$value['store_id']} as b on a.id = b.order_id ".$where;
                $amountTemp = $this->querySql($sql2);
                $amount = array_merge($amount, $amountTemp);
            }
            $sum = 0;
            foreach ($count as $value) {
                $sum += $value['total'];
            }
            $amountTotal = 0;
            foreach ($amount as $value) {
                $amountTotal += $value['amount'];
            }
            $upOrderCount['upOrderNum'] = $sum;
            $upOrderCount['upOrderMem'] = count($member);
            $upOrderCount['upOrderAmount'] = $amountTotal;
        }

        if ($op) {
            //付款件数
            $order_sn = array();
            if ($store_id > 0) {
                $sql = "select a.order_sn from ".DB_PREFIX."order_{$store_id} as a 
                left join ".DB_PREFIX."order_relation_{$store_id} as b on a.id = b.order_id ".$where;
                $data = $this->querySql($sql);
                if ($data) {
                    foreach ($data as $key => $value) {
                        $order_sn[] = $value['order_sn'];
                    }
                }
            } else {
                $data = array();
                foreach ($storeInfo as $value) {
                    $sql1 = "select a.order_sn from bs_order_{$value['store_id']} as a 
                  left join bs_order_relation_{$value['store_id']} as b on a.id = b.order_id ".$where;
                    $temp = $this->querySql($sql1);
                    $data = array_merge($data, $temp);
                }
                if ($data) {
                    foreach ($data as $value) {
                        $order_sn[] = $value['order_sn'];
                    }
                }
            }
            if ($order_sn) {
                $order_sn = implode(',', $order_sn);
                $orderGoodsMod = &m('orderGoods');
                //订单商品详情
                $upOrderDetailDatas = $orderGoodsMod->getData(array(
                    'cond' => ' order_id in (' . $order_sn . ')',
                    'fields' => 'goods_num'
                ));
                $totalGoodsNum = 0;
                foreach ($upOrderDetailDatas as $key => $value) {
                    $totalGoodsNum += $value['goods_num'];
                }
                $upOrderCount['totalGoodsNum'] = $totalGoodsNum;
            } else {
                $upOrderCount['totalGoodsNum'] = 0;
            }
        }
        return $upOrderCount;
    }

    /**
     * 时间组件-本日
     * @author: luffy
     * @date  : 2017-11-09
     */
    public function getDayTimeArr($op = 0)
    {
        //本日
        $currentStartDay = strtotime(date('Y-m-d'));
        $currentEndDay = strtotime(date('Y-m-d 23:59:59'));
        if ($op != 1) {
            //前期
            $diff = 24 * 60 * 60;
            $startYesterday = $currentStartDay - $diff;
            $endYesterday = $currentEndDay - $diff;
        }
        $timeArr = array(
            'cur' => array($currentStartDay, $currentEndDay),
            'yes' => array($startYesterday, $endYesterday)
        );
        return $timeArr;
    }

    /**
     * 时间组件-昨日
     * @author: luffy
     * @date  : 2017-11-10
     */
    public function getYesTimeArr($op = 0)
    {
        //昨日
        $diff = 24 * 60 * 60;
        $yesTime = time() - $diff;
        $currentStartDay = strtotime(date('Y-m-d', $yesTime));
        $currentEndDay = strtotime(date('Y-m-d 23:59:59', $yesTime));
        if ($op != 1) {
            //前期
            $startYesterday = $currentStartDay - $diff;
            $endYesterday = $currentEndDay - $diff;
        }
        $timeArr = array(
            'cur' => array($currentStartDay, $currentEndDay),
            'yes' => array($startYesterday, $endYesterday)
        );
        return $timeArr;
    }

    /**
     * 时间组件-本周
     * @author: luffy
     * @date  : 2017-11-10
     */
    public function getWeekTimeArr($op = 0)
    {
        //昨日
        $y = date('Y', time());
        $w = date('W', time());
        $weekStart = date("Y-m-d", strtotime("{$y}-W{$w}-1"));
        $weekEnd = date("Y-m-d", strtotime("{$y}-W{$w}-7"));
        $currentStartDay = strtotime($weekStart);
        $currentEndDay = strtotime($weekEnd . ' 23:59:59');
        if ($op != 1) {
            $diff = 24 * 60 * 60 * 7;
            //前期
            $startYesterday = $currentStartDay - $diff;
            $endYesterday = $currentEndDay - $diff;
        }
        $timeArr = array(
            'cur' => array($currentStartDay, $currentEndDay),
            'yes' => array($startYesterday, $endYesterday)
        );
        return $timeArr;
    }

    /**
     * 时间组件-本月
     * @author: luffy
     * @date  : 2017-11-10
     */
    public function getMonthTimeArr($op = 0)
    {
        //本月
        $currentStartDay = mktime(0, 0, 0, date('m'), 1, date('Y'));
        $currentEndDay = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
        if ($op != 1) {
            //前期
            $startYesterday = mktime(0, 0, 0, date('m') - 1, 1, date('Y'));
            $endYesterday = mktime(0, 0, 0, date('m'), 1, date('Y')) - 1;
        }
        $timeArr = array(
            'cur' => array($currentStartDay, $currentEndDay),
            'yes' => array($startYesterday, $endYesterday)
        );
        return $timeArr;
    }

    /**
     * 时间组件-本年
     * @author: luffy
     * @date  : 2017-11-10
     */
    public function getYearTimeArr($op = 0)
    {
        //本年
        $currentStartDay = mktime(0, 0, 0, 1, 1, date('Y'));
        $currentEndDay = mktime(0, 0, 0, 1, 1, date('Y') + 1) - 1;
        if ($op != 1) {
            //前期
            $startYesterday = mktime(0, 0, 0, 1, 1, date('Y') - 1);
            $endYesterday = mktime(0, 0, 0, 1, 1, date('Y')) - 1;
        }
        $timeArr = array(
            'cur' => array($currentStartDay, $currentEndDay),
            'yes' => array($startYesterday, $endYesterday)
        );
        return $timeArr;
    }

    /**
     * 时间组件-自定义
     * @author: luffy
     * @date  : 2017-11-10
     */
    public function getSettingTimeArr($timeSetArr, $op = 0)
    {
        $start_time = $timeSetArr['start_time'];
        $end_time = $timeSetArr['end_time'];
        //自定义
        $currentStartDay = strtotime($start_time);
        $currentEndDay = strtotime($end_time);
        if ($op != 1) {
            $diff = $currentEndDay - $currentStartDay;
            //前期
            $startYesterday = $currentStartDay - $diff;
            $endYesterday = $currentEndDay - $diff;
        }
        $timeArr = array(
            'cur' => array($currentStartDay, $currentEndDay),
            'yes' => array($startYesterday, $endYesterday)
        );
        return $timeArr;
    }

    /**
     * 图表--获取交易趋势
     * @param $store_cate_id    区域ID
     * @param $store_id         店铺ID
     * @param $tm
     * @param $op 1 字符串  0 数组
     * @author: luffy
     * @date  : 2017-11-13
     */
    public function getTransactionTrend($store_cate_id = 0, $store_id = 0, $tm = 'day', $op = 0, $timeSetArr = array())
    {
        //获取时间组件
        $result = array();
        switch ($tm) {
            case 'day':
                if ($op) {
                    $timeArr = "'00:00','02:00','04:00','06:00','08:00','10:00','12:00','14:00','16:00','18:00','20:00','22:00'";
                } else {
                    $timeArr = array('00:00', '02:00', '04:00', '06:00', '08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00', '22:00');
                }
                $timesArr = array();
                $curTime = strtotime(date('Y-m-d 00:00:00'));
                for ($i = 0; $i <= 11; $i++) {
                    $timesArr[$i][0] = $curTime - 7200 + ($i * 7200);
                    $timesArr[$i][1] = $curTime + ($i * 7200) - 1;
                }
                $result = $this->toTransactionTrend($timesArr, $store_cate_id, $store_id, $op);
                $result['xAxis'] = $timeArr;
                break;
            case 'yes':
                $timeArr = array('00:00', '02:00', '04:00', '06:00', '08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00', '22:00');
                $timesArr = array();
                $curTime = strtotime(date('Y-m-d 00:00:00')) - (24 * 3600);
                for ($i = 0; $i <= 11; $i++) {
                    $timesArr[$i][0] = $curTime - 7200 + ($i * 7200);
                    $timesArr[$i][1] = $curTime + ($i * 7200);
                }
                $result = $this->toTransactionTrend($timesArr, $store_cate_id, $store_id);
                $result['xAxis'] = $timeArr;
                break;
            case 'week':
                $timeArr = array('周一', '周二', '周三', '周四', '周五', '周六', '周日');
                $timesArr = array();
                $y = date('Y', time());
                $w = date('W', time());
                $weekStart = date("Y-m-d", strtotime("{$y}-W{$w}-1"));
                $weekStart_1 = strtotime($weekStart);
                $weekStart_2 = strtotime($weekStart . ' 23:59:59');
                $diff = 24 * 3600;
                for ($i = 0; $i <= 6; $i++) {
                    $timesArr[$i][0] = $weekStart_1 + $diff * $i;
                    $timesArr[$i][1] = $weekStart_2 + $diff * $i;
                }
                $result = $this->toTransactionTrend($timesArr, $store_cate_id, $store_id);
                $result['xAxis'] = $timeArr;
                break;
            case 'month':
                $timeArr = array();
                $month_1 = mktime(0, 0, 0, date('m'), 1, date('Y'));
                $diff = 24 * 3600;
                for ($i = 1; $i <= date('t'); $i++) {
                    $timeArr = array_merge($timeArr, array(date('Y/m/') . $i));
                    $timesArr[$i][0] = $month_1 + $diff * ($i - 1);
                    $timesArr[$i][1] = ($month_1 + $diff * $i) - 1;
                }
                $result = $this->toTransactionTrend($timesArr, $store_cate_id, $store_id);
                $result['xAxis'] = $timeArr;
                break;
            case 'year':
                $timeArr = array();
                for ($i = 1; $i <= 12; $i++) {
                    $timeArr = array_merge($timeArr, array(date('Y/') . $i));
                    $timesArr[$i][0] = mktime(0, 0, 0, $i, 1, date('Y'));
                    $timesArr[$i][1] = mktime(23, 59, 59, $i, date('t'), date('Y'));
                }
                $result = $this->toTransactionTrend($timesArr, $store_cate_id, $store_id);
                $result['xAxis'] = $timeArr;
                break;
            case 'setting':
                $timeArr = array();
                $start_time = strtotime($timeSetArr['start_time']);
                $end_time = strtotime($timeSetArr['end_time'] . '23:59:55');
                $sy = date('Y', $start_time);
                $ey = date('Y', $end_time);
                $sm = date('m', $start_time);
                $em = date('m', $end_time);
                $sd = date('d', $start_time);
                $ed = date('d', $end_time);

                //不是同一年
                if ($sy != $ey) {
                    for ($i = 0; $i <= ($ey - $sy); $i++) {
                        $timeArr = array_merge($timeArr, array($sy + $i));
                        if ($i == 0) {
                            $timesArr[$i][0] = $start_time;
                        } else {
                            $timesArr[$i][0] = mktime(0, 0, 0, 1, 1, $sy + $i);
                        }
                        if ($i == ($ey - $sy)) {
                            $timesArr[$i][1] = $end_time;
                        } else {
                            $timesArr[$i][1] = mktime(0, 0, 0, 1, 1, $sy + $i + 1) - 1;
                        }
                    }
                    //同年不同月
                } elseif ($sy == $ey && $sm != $em) {
                    for ($i = 0; $i <= ($em - $sm); $i++) {
                        $timeArr = array_merge($timeArr, array($sm + $i));
                        if ($i == 0) {
                            $timesArr[$i][0] = $start_time;
                        } else {
                            $timesArr[$i][0] = mktime(0, 0, 0, $sm + $i, 1, $sy);
                        }
                        if ($i == ($em - $sm)) {
                            $timesArr[$i][1] = $end_time;
                        } else {
                            $timesArr[$i][1] = mktime(0, 0, 0, $sm + $i + 1, 1, $sy) - 1;
                        }
                    }
                    //同月不同日
                } elseif ($sy == $ey && $sm == $em) {
                    $diff = 24 * 3600;
                    for ($i = 0; $i <= ($ed - $sd); $i++) {
                        $timeArr = array_merge($timeArr, array($sd + $i));
                        $timesArr[$i][0] = $start_time + ($i * $diff);
                        $timesArr[$i][1] = $start_time + (($i + 1) * $diff) - 1;
                    }
                }
                $result = $this->toTransactionTrend($timesArr, $store_cate_id, $store_id);
                $result['xAxis'] = $timeArr;
//                echo '<pre>';print_r($timesArr  );die;
                break;
        }
        return $result;
    }

    /**
     * 图标--获取交易趋势
     * @param $store_cate_id    区域ID
     * @param $store_id         店铺ID
     * @param $op
     * @author: luffy
     * @date  : 2017-11-13
     */
    public function toTransactionTrend($timesArr, $store_cate_id, $store_id, $op = 0)
    {
        $result = $a = $b = $c = $d = $e = $f = $g = array();
        foreach ($timesArr as $key => $value) {
            $t1 = $this->toUpOrderCount(10, $store_cate_id, $store_id, 0, $value);
            $a = array_merge($a, array($t1['upOrderNum']));
            $b = array_merge($b, array($t1['upOrderMem']));
            $c = array_merge($c, array($t1['upOrderAmount']));

            $t2 = $this->toUpOrderCount(20, $store_cate_id, $store_id, 1, $value);
            $d = array_merge($d, array($t2['upOrderNum']));
            $e = array_merge($e, array($t2['upOrderMem']));
            $f = array_merge($f, array($t2['upOrderAmount']));
            $g = array_merge($g, array($t2['totalGoodsNum']));
        }
        if ($op) {
            $result['a'] = implode(',', $a);
            $result['b'] = implode(',', $b);
            $result['c'] = implode(',', $c);
            $result['d'] = implode(',', $d);
            $result['e'] = implode(',', $e);
            $result['f'] = implode(',', $f);
            $result['g'] = implode(',', $g);
        } else {
            $result['a'] = $a;
            $result['b'] = $b;
            $result['c'] = $c;
            $result['d'] = $d;
            $result['e'] = $e;
            $result['f'] = $f;
            $result['g'] = $g;
        }
        return $result;
    }

    /**
     * 图表--商品Top10
     * @param $store_cate_id    区域ID
     * @param $store_id         店铺ID
     * @param $tm
     * @param $op 1 字符串  0 数组
     * @author: luffy
     * @date  : 2017-11-16
     */
    public function getGoodsTop10($store_cate_id = 0, $store_id = 0, $tm = 'day', $op = 0, $timeSetArr = array(), $lang_id = 29)
    {
        //获取时间组件
        switch ($tm) {
            case 'day':
                $timeArr = $this->getDayTimeArr(1);
                break;
            case 'yes':
                $timeArr = $this->getYesTimeArr(1);
                break;
            case 'week':
                $timeArr = $this->getWeekTimeArr(1);
                break;
            case 'month':
                $timeArr = $this->getMonthTimeArr(1);
                break;
            case 'year':
                $timeArr = $this->getYearTimeArr(1);
                break;
            case 'setting':
                $timeArr = $this->getSettingTimeArr($timeSetArr, 1);
                break;
        }
        $result = $this->toGoodsTop10($timeArr['cur'], $store_cate_id, $store_id, $op, $lang_id);
        return $result;
    }

    /**
     * 图表--商品Top10逻辑处理
     * @param $store_cate_id    区域ID
     * @param $store_id         店铺ID
     * @param $op
     * @author: luffy
     * @date  : 2017-11-16
     */
    public function toGoodsTop10($timesArr, $store_cate_id, $store_id, $op = 0, $lang_id)
    {
        $all = "1 =1";
        $areaSql = " ";
        if ($store_id > 0) {
            $areaSql .= " AND store_id = {$store_id}";
        } else {
            //获取区域下店铺(不传区域则是获取可用店铺)
            $storeMod = &m('store');
            $storeIds = $storeMod->getStoreIds($store_cate_id, 1);
            $areaSql .= " AND store_id in ({$storeIds}) ";
        }
        $goodsMod = &m("goods");
        $sql = 'SELECT COUNT(order_id) goods_num,goods_id,goods_name from '
            . '(select goods_name,order_id,goods_id,COUNT(distinct order_id,goods_id) from ' . DB_PREFIX . 'order_goods '
            . 'WHERE NOT (refund_state = 0 AND order_state = 0) '
            . 'AND add_time BETWEEN ' . $timesArr[0] . ' AND ' . $timesArr[1]
            . $areaSql
            . ' GROUP BY order_id,goods_id '
            . ') AS a GROUP BY a.goods_id ORDER BY goods_num DESC LIMIT 10';

        $orderGoodsMod = &m('orderGoods');
        $storeGoodsMod = &m('storeGoods');
        $orderGoodsData = $orderGoodsMod->querySql($sql);

        $nameArr = array();
        $goodsNumArr = array();
        if ($orderGoodsData) {
            foreach ($orderGoodsData as $key => $value) {
                $lang_info = $storeGoodsMod->getGoodsInfo($value['goods_id'], $lang_id);

                //获取翻译的商品名称
                if ($op) {
                    if (strstr($lang_info['goods_name'], '\'')) {
                        $goods_name = str_replace('\'', '’', $lang_info['goods_name']);
                    } else {
                        $goods_name = $lang_info['goods_name'];
                    }
                    $nameArr = array_merge($nameArr, array('\'' . $goods_name . '\''));
                } else {
                    $nameArr = array_merge($nameArr, array($lang_info['goods_name']));
                }
                $goodsNumArr = array_merge($goodsNumArr, array($value['goods_num']));
            }
            if ($op) {
                $result['xAxis'] = implode(',', $nameArr);
                $total_1 = array_sum($goodsNumArr);
                $result['goods_num'] = implode(',', $goodsNumArr);
            } else {
                $result['xAxis'] = $nameArr;
                $result['goods_num'] = $goodsNumArr;
                $total_1 = array_sum($result['goods_num']);
            }


            //全体总量
            $query = array('cond' => $all . $areaSql);
            $total_2 = $storeGoodsMod->getCount($query);

            foreach ($orderGoodsData as $k => $val) {
//                $goodsInfo = $storeGoodsMod->getRow($val['goods_id'], 'id, original_img');
                $original_img = 'select  c.id, g.original_img  from '
                    . DB_PREFIX . 'store_goods  AS c left join '
                    . DB_PREFIX . 'goods  AS g   ON c.goods_id =  g.goods_id  where c.id=' . $val['goods_id'];
                $goodsInfo = $storeGoodsMod->querySql($original_img);
                $lang_info = $storeGoodsMod->getGoodsInfo($val['goods_id'], $lang_id);
                if ($lang_info) {
                    $orderGoodsData[$k]['goods_name'] = $lang_info['goods_name'];
                }
                $orderGoodsData[$k]['original_img'] = $goodsInfo[0]['original_img'];
                //top10占比
                $orderGoodsData[$k]['percent_1'] = sprintf('%.2f', $val['goods_num'] / $total_1 * 100) . ' %';
                //全体商品占比
                $orderGoodsData[$k]['percent_2'] = sprintf('%.2f', $val['goods_num'] / $total_2 * 100) . ' %';
            }

            $result['goodsData'] = $orderGoodsData;
        } else {
            if ($op) {
                $result['xAxis'] = '\'无数据\'';
                $result['goods_num'] = '0';
            } else {
                $result['xAxis'] = array('无数据');
                $result['goods_num'] = array(0);
            }
        }
        return $result;
    }

    /*
    * 取消超时未付款订单
    * by xt 2019.02.20
    * @param $mod  模型实例
    * @param $orders   订单数据
    * @param $expectTime   预存时间
    * @param $isNew    是否新表
    */
    public function cancelUnpaidOrder($mod, $orders, $expectTime, $isNew)
    {
        $id = empty($isNew) ? 'order_id' : 'id';  // 判断老表和新表的主键
        foreach ($orders as $order) {
            $endTime = $order['add_time'] + $expectTime;
            // 取消超时未付款订单
            if ($endTime < time()) {
                $orderData = array(
                    'key' => $id,
                    'order_state' => 0,
                );
                $mod->doEdit($order[$id], $orderData);
            }
        }
    }

    /**
     *
     *  wanyan判断订单是否支付和订单编号是否存在
     */
    public function isExist($order_sn)
    {
        $rs = $this->getOne(array('cond' => "`order_sn` = '{$order_sn}'", 'fields' => "`order_sn`,order_state,order_amount"));
        return $rs;
    }

    /**
     * 生成小票编号
     * @author: luffy
     * modified fup
     * @date: 2018-08-09
     */
    public function createNumberOrder($storeid)
    {
        //获取当天开始结束时间
        $startDay = strtotime(date('Y-m-d'));
        $endDay = strtotime(date('Y-m-d 23:59:59'));
        $sql = 'select a.order_sn,b.number_order from  '
            . DB_PREFIX . 'order_'.$storeid.' a LEFT JOIN ' . DB_PREFIX .'order_details_'.$storeid.' b on a.order_sn = b.order_sn where a.add_time BETWEEN ' . $startDay . ' AND ' . $endDay
            . ' AND a.mark = 1 and a.order_state > 10 order by add_time DESC limit 1';
        $res = $this->querySql($sql);
        //不管订单存在与否直接加
        $number_order = (int)$res[0]['number_order'] + 1;
        $number_order = str_pad($number_order, 4, 0, STR_PAD_LEFT);
        return $number_order;
    }


    /**
     * 获取未指定订单和未手动接单的订单数量
     * @author: luffy
     * @date: 2018-12-08
     */
    public function getPublicHeadOrderNum($storeid)
    {
        $sql = "select a.id from bs_order_{$storeid} as a left join bs_order_details_{$storeid} as b on a.id = b.order_id where a.order_state = 20 and a.mark = 1 and b.clickandview = 1 ";
        $data = $this->querySql($sql);
        $num = count($data);
        return $num;
    }

    /**
     * 统计门店
     * by xt 2019.02.02
     * @param $buyer_id
     * @param null $start_time
     * @param null $end_time
     * @return array
     */
    public function getStoreCount($buyer_id, $start_time = null, $end_time = null)
    {
        $cond = '';

        if ($buyer_id) {
            $cond = "and buyer_id = {$buyer_id}";
        }

        if ($start_time) {
            $cond .= ' and payment_time >= ' . $start_time;
        }

        if ($end_time) {
            $cond .= ' and payment_time < ' . $end_time;
        }

        $sql = 'select buyer_id,store_name,count(store_id) as store_count from bs_order where order_state = 50 ' . $cond . ' group by buyer_id,store_id order by buyer_id asc,store_count desc';
        $stores = $this->querySql($sql);

        if ($buyer_id) {
            return array_slice($stores, 0, 3);
        }

        return $stores;
    }

    /**
     * 统计商品
     * by xt 2019.02.02
     * @param $buyer_id
     * @param null $start_time
     * @param null $end_time
     * @return array
     */
    public function getGoodsCount($buyer_id, $start_time = null, $end_time = null)
    {
        $cond = '';

        if ($buyer_id) {
            $cond = "and o.buyer_id = {$buyer_id}";
        }

        if ($start_time) {
            $cond .= ' and o.payment_time >= ' . $start_time;
        }

        if ($end_time) {
            $cond .= ' and o.payment_time < ' . $end_time;
        }

        $sql = 'select o.buyer_id,goods_name,count(g.goods_id) as goods_count from bs_order as o left join bs_order_goods as g on o.order_sn = g.order_id where o.order_state = 50 ' . $cond . ' group by o.buyer_id,g.goods_id order by o.buyer_id asc,goods_count desc';
        $goods = $this->querySql($sql);

        if ($buyer_id) {
            return array_slice($goods, 0, 3);
        }

        return $goods;
    }

    /**
     * 根据商品id返回商品配送属性
     * @author zhangkx
     * @date 2019/3/1
     * @param string $goodsId 商品id,多个使用逗号隔开
     * @return array type:1.整体(total),2.单独(delivery)
     */
    public function goodsDelivery($goodsId)
    {
        //查询商品配送属性
        $storeGoodsMod = &m('storeGoods');
        $data = $storeGoodsMod->getData(array(
            'cond' => 'id in (' . $goodsId . ')',
            'fields' => 'id, attributes'
        ));
        $attribute = array();
        $return = array();
        foreach ($data as $key => $value) {
            $attribute[] = $value['attributes'];
        }
        //商品配送属性信息全部相等
        if (count($attribute) == count(array_unique($attribute))) {
            $return['type'] = 1;
            foreach ($data as $key => $value) {
                $return['total'] = $value['attributes'];
            }
        } else {  //商品配送属性信息不相等
            $return['type'] = 2;
            foreach ($data as $key => $value) {
                $return['delivery'][$key]['id'] = $value['id'];
                $return['delivery'][$key]['attributes'] = $value['attributes'];
            }
        }
        return $return;
    }

    /**
     * 查找订单的信息
     * @param $order_sn
     * @return mixed
     */
    public function selectOrderInfo($order_sn)
    {
        $userOrderMod = &m('userOrder');
        $orderGoodsMod = &m('orderGoods');
        $couponMod = &m('coupon');
        $sql = "SELECT store_id FROM bs_user_order WHERE order_sn = '{$order_sn}' ";
        $data = $userOrderMod->querySql($sql);
        $orderMod = &m('order' . $data[0]['store_id']);
        $orderRelationMod = &m('orderRelation' . $data[0]['store_id']);
        $orderDetailMod = &m('orderDetails' . $data[0]['store_id']);
        $sqls = "SELECT a.source,b.phone,a.evaluation_state,a.buyer_id,a.id,a.order_sn,a.add_time,b.username,a.order_sn,a.order_state,a.sendout,a.order_amount,a.goods_amount FROM bs_order_{$data[0]['store_id']} AS a LEFT JOIN bs_user AS b ON a.buyer_id = b.id WHERE order_sn = '{$order_sn}'";
//        echo $sqls;die;
        $res = $orderMod->querySql($sqls);
        foreach ($res as $k => $v) {
            $res[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            $res[$k]['store_id'] = $data[0]['store_id'];
            switch ($v['sendout']) {
                case 0:
                    $res[$k]['sendout'] = '补单';
                    break;
                case 1:
                    $res[$k]['sendout'] = '到店自提';
                    break;
                case 2:
                    $res[$k]['sendout'] = '配送上门';
                    break;
                case 3:
                    $res[$k]['sendout'] = '邮寄托运';
                    break;
                case 4:
                    $res[$k]['sendout'] = '海外代购';
                    break;
            }
            switch ($v['source']) {
                case 1:
                    $res[$k]['sourceName'] = '小程序下单';
                    break;
                case 2:
                    $res[$k]['sourceName'] = '公众号下单';
                    break;
                case 3:
                    $res[$k]['sourceName'] = '代客下单';
                    break;
                case 4:
                    $res[$k]['sourceName'] = 'pc前台下单';
                    break;
            }
            switch ($v['evaluation_state']) {
                case 0:
                    $res[$k]['evaluation_state_name'] = '未评价';
                    break;
                case 1:
                    $res[$k]['evaluation_state_name'] = '已评价';
                    break;
                case 2:
                    $res[$k]['evaluation_state_name'] = '已过期未评价';
                    break;
            }
            switch ($v['order_state']) {
                case 0:
                    $res[$k]['order_state_name'] = '已取消';
                    break;
                case 10:
                    $res[$k]['order_state_name'] = '未付款';
                    break;
                case 20:
                    $res[$k]['order_state_name'] = '已付款';
                    break;
                case 25:
                    $res[$k]['order_state_name'] = '已接单';
                    break;
                case 30:
                    $res[$k]['order_state_name'] = '已发货';
                    break;
                case 40:
                    $res[$k]['order_state_name'] = '区域配送';
                    break;
                case 50:
                    $res[$k]['order_state_name'] = '已收货';
                    break;
                case 60:
                    $res[$k]['order_state_name'] = '退款中';
                    break;
                case 70:
                    $res[$k]['order_state_name'] = '已退款';
                    break;
            }
            $sqlss = "SELECT a.goods_id,a.goods_name,a.spec_key_name,a.goods_num,a.goods_price,b.original_img FROM bs_order_goods AS a
            LEFT JOIN bs_store_goods AS b
            ON a.goods_id = b.id WHERE a.order_id = '{$v['order_sn']}'";
            $orderGoodsData = $orderGoodsMod->querySql($sqlss);
            $res[$k]['goods'] = $orderGoodsData;
            foreach ($orderGoodsData as $kk => $vv) {
                $res[$k]['goods'][$kk]['spec_key_arr'] = explode(':', $vv['spec_key_name']);
                foreach ($res[$k]['goods'][$kk]['spec_key_arr'] as $key => $value) {
                    if (!$value) {
                        unset($res[$k]['goods'][$kk]['spec_key_arr'][$key]);
                    }
                }
                $res[$k]['goods'][$kk]['spec_key_names'] = implode('、', $res[$k]['goods'][$kk]['spec_key_arr']);
            }

            $paySql = "SELECT payment_type,payment_time FROM bs_order_relation_{$data[0]['store_id']} WHERE order_id = '{$res[0]['id']}'";
            $result = $orderRelationMod->querySql($paySql);
            $res[$k]['orderRelation'] = $result;
            foreach ($result as $kkk => $vvv) {
                switch ($vvv['payment_type']) {
                    case 0:
                        $res[$k]['orderRelation'][$kkk]['payment_type'] = '----';
                        break;
                    case 1:
                        $res[$k]['orderRelation'][$kkk]['payment_type'] = '支付宝支付';
                        break;
                    case 2:
                        $res[$k]['orderRelation'][$kkk]['payment_type'] = '微信支付';
                        break;
                    case 3:
                        $res[$k]['orderRelation'][$kkk]['payment_type'] = '余额支付';
                        break;
                    case 4:
                        $res[$k]['orderRelation'][$kkk]['payment_type'] = '线下支付';
                        break;
                    case 5:
                        $res[$k]['orderRelation'][$kkk]['payment_type'] = '免费兑换';
                        break;
                }
                $res[$k]['orderRelation'][$kkk]['payment_time'] = date('Y-m-d H:i:s', $vvv['payment_time']);
            }

            $detailsSql = "SELECT seller_msg,fx_money,discount_num,fx_money,pay_sn,shipping_fee,point_discount FROM bs_order_details_{$data[0]['store_id']} WHERE order_id = '{$res[0]['id']}'";
            $orderDetailsData = $orderDetailMod->querySql($detailsSql);
            $res[$k]['orderDetail'] = $orderDetailsData;

            $sourceSql = "SELECT a.payment_source FROM bs_order_relation_{$data[0]['store_id']} AS a 
                WHERE a.order_id = '{$res[0]['id']}'";
            $results = $orderRelationMod->querySql($sourceSql);

            $sourceSqls = "SELECT img FROM bs_store_source WHERE id = {$results[0]['payment_source']} AND store_id = {$data[0]['store_id']}";
            $sourceImg = &m('storeSource')->querySql($sourceSqls);

            $res[$k]['sourceImg'] = $sourceImg;
        }

        return $res;
    }

    /**
     * 退货信息
     * @param $order_sn
     * @return mixed
     */
    public function getRefundGoods($order_sn)
    {
        $sql = "SELECT * FROM bs_refund_return WHERE order_sn = " . $order_sn;
        $refundReturnMod = &m('refundReturn');
        $res = $refundReturnMod->querySql($sql);

        return $res;
    }

    /**
     * 获取分销码
     * @param $order_sn
     * @return mixed
     */
    public function getFxCode($order_sn)
    {
        $userOrderMod = &m('userOrder');
        $sql = "SELECT store_id FROM bs_user_order WHERE order_sn = '{$order_sn}' ";
        $data = $userOrderMod->querySql($sql);
        $sqls = "SELECT b.fx_user_id FROM bs_order_{$data[0]['store_id']} AS a LEFT JOIN bs_order_details_{$data[0]['store_id']} AS b ON a.id = b.order_id WHERE a.`order_sn` = '{$order_sn}'";

        $orderMod = &m('order' . $data[0]['store_id']);
        $res = $orderMod->querySql($sqls);
//        echo '<pre>';print_r($result);die;
        $fxSql = "SELECT fx_code FROM bs_fx_user WHERE id = {$res[0]['fx_user_id']}";
        $result = $userOrderMod->querySql($fxSql);

        return $result[0]['fx_code'];
    }

    /**
     * 获取排列数组
     */
    public function arrangement($a, $m)
    {
        $r = array();

        $n = count($a);
        if ($m <= 0 || $m > $n) {
            return $r;
        }

        for ($i = 0; $i < $n; $i++) {
            $b = $a;
            $t = array_splice($b, $i, 1);
            if ($m == 1) {
                $r[] = $t;
            } else {
                $c = $this->arrangement($b, $m - 1);
                foreach ($c as $v) {
                    $r[] = array_merge($t, $v);
                }
            }
        }

        return $r;
    }

    /**
     * 获取订单的配送地址
     */
    public function getOrderAddress($order_sn, $store_id)
    {
        //todo调整
        $sql = "select a.source,b.delivery,c.address,c.name,c.phone,d.username,d.phone as userphone from bs_order_{$store_id} a left join bs_order_details_{$store_id} b on a.id = b.order_id left join bs_user_address c on b.address_id = c.id left join bs_user d on a.buyer_id = d.id where a.order_sn = '{$order_sn}'";
        $info = $this->querySql($sql);
        $data = $info[0];
        if ($data['source'] == 3) {//代客下单
            $data['address'] = $data['delivery'];
            $data['name'] = $data['username'];
            $data['phone'] = $data['userphone'];
        }
        if (!empty($data['address'])) {
            $data['address'] = str_replace('_', '', $data['address']);
        }
        return $data;
    }

    /**
     * 获取提示音和打印的订单
     */
    public function getPrintOrders()
    {
        $store_id = $_SESSION['store']['storeId'];
        $sql = "select a.order_sn from bs_order_{$store_id} as a left join bs_order_details_{$store_id} as b on a.id = b.order_id ";
        $where = " where a.mark = 1 and a.order_state = 20 and b.warning_tone = 1 ";
        $data = $this->querySql($sql . $where);
        $ordersnArr = array();
        foreach ($data as $v) {
            $ordersnArr[] = $v['order_sn'];
        }
        return $ordersnArr;
    }

    /*
     * 公众号订单详情的数据
     * @author tangp
     * @date 2019-04-30
     * @param $order_sn
     * @param $lang
     * @return mixed
     */
    public function getWxOrderDetails($order_sn, $lang)
    {
        $userOrderMod = &m('userOrder');
        $orderGoodsMod = &m('orderGoods');
        $storeMod = &m('store');
        $sql = "SELECT store_id FROM bs_user_order WHERE order_sn = '{$order_sn}' ";
        $data = $userOrderMod->querySql($sql);
        $orderMod = &m('order' . $data[0]['store_id']);
        $sqls = "SELECT b.fx_money,c.payment_source,a.add_time,a.store_id,a.goods_amount,a.order_amount,a.order_state,a.sendout,b.discount,b.discount_num,b.fx_money,b.point_discount,b.shipping_fee,b.seller_msg FROM bs_order_{$data[0]['store_id']} AS a 
        LEFT JOIN bs_order_details_{$data[0]['store_id']} AS b ON a.id = b.order_id
        LEFT JOIN bs_order_relation_{$data[0]['store_id']} AS c ON a.id = c.order_id
        WHERE order_sn = '{$order_sn}'";
        $res = $orderMod->querySql($sqls);
        foreach ($res as $k => $v) {
            $res[$k]['store_name'] = $storeMod->getNameById($v['store_id'], $lang);
            $res[$k]['format_time'] = date('Y-m-d H:i:s', $v['add_time']);
            $storeImage = &m('store')->getOne(array('cond' => "id = '{$v['store_id']}'"));
            $res[$k]['store_image'] = $storeImage['logo'];
            $res[$k]['address_info'] = $this->getOrderAddress($order_sn, $data[0]['store_id']);
            $res[$k]['orderGoodsData'] = $orderGoodsMod->getData(array('cond' => "order_id = '{$order_sn}'"));
            switch ($res[$k]['sendout']) {
                case 1:
                    $res[$k]['sendOutName'] = '到店自提';
                    break;
                case 2:
                    $res[$k]['sendOutName'] = '配送上门';
                    break;
                case 3:
                    $res[$k]['sendOutName'] = '邮寄托运';
                    break;
                case 4:
                    $res[$k]['sendOutName'] = '海外代购';
                    break;
            }
        }

        return $res;
    }

    /**
     * 获取折扣信息
     * @author tangp
     * @date 2019-05-07
     * @param $storeId
     * @param $order_sn
     * @return array
     */
    public function getDiscountInfo($storeId, $order_sn)
    {
        $sql = "SELECT `id` FROM bs_order_{$storeId} WHERE `order_sn` = '{$order_sn}'";
        $res = $this->querySql($sql);
        $detailsSql = "SELECT discount,coupon_discount,point_discount FROM bs_order_details_{$storeId} WHERE `order_id` = {$res[0]['id']}";
        $data = $this->querySql($detailsSql);

        return $data;
    }

    public function getPaymentCodeData($storeId, $order_sn)
    {
        $sql = "SELECT `id` FROM bs_order_{$storeId} WHERE `order_sn` = '{$order_sn}'";
        $res = $this->querySql($sql);
        $paySql = "SELECT payment_type FROM bs_order_relation_{$storeId} WHERE `order_id` = {$res[0]['id']}";
        $data = $this->querySql($paySql);

        return $data;
    }

    /**
     * 获取订单退款信息
     * @author zhangkx
     * @date 2019/5/5
     * @param $orderSn
     * @param $storeId
     * @param $orderGoods
     * @return mixed
     */
    public function getRefundRecord($orderSn, $storeId, $orderGoods)
    {
        //订单退款信息
        $sql = "select a.*, b.order_state, c.payment_type from " . DB_PREFIX . "order_refund as a
                left join " . DB_PREFIX . "order_{$storeId} as b on a.order_sn = b.order_sn
                left join " . DB_PREFIX . "order_relation_{$storeId} as c on b.id = c.order_id
                where a.order_sn = '{$orderSn}' order by a.id desc limit 1";
        $data = $this->querySql($sql);

        if ($data) {
            $data = $data[0];
            switch ($data['payment_type']) {
                case 1:  //支付宝
                    $data['refund_type'] = '支付宝';
                    break;
                case 2:  //微信
                    $data['refund_type'] = '微信';
                    break;
                case 3:  //余额
                    $data['refund_type'] = '余额';
                    break;
                case 4:  //线下
                    $data['refund_type'] = '线下';
                    break;
            }
            if ($data['refund_images']) {
                if (strpos($data['refund_images'], ',')) {
                    $imgList = explode(',', $data['refund_images']);
                    $data['images'] = $imgList;
                } else {
                    $data['images'] = array(0 => $data['refund_images']);
                }
            } else {
                $data['images'] = array();
            }
            $data['reason_info'] = urldecode($data['reason_info']);
            $goodsIdList = array();
            if (strpos($data['refund_goods_ids'], ',')) {
                $goodsId = explode(',', $data['refund_goods_ids']);
                foreach ($goodsId as $key => $value) {
                    $goodsIdList[] = $value;
                }
            } else {
                $goodsIdList[] = $data['refund_goods_ids'];
            }
            foreach ($orderGoods as $key => &$value) {
                foreach ($goodsIdList as $k => $v) {
                    if ($value['goods_id'] == $v) {
                        $value['refund'] = 1;
                    }
                }
            }
            $data['goods'] = $orderGoods;

        }
        return $data;
    }

    /**
     * 订单退款
     * @author zhangkx
     * @date 2019/5/5
     * @param $orderSn
     * @param $storeId
     * @param $operatorId
     * @return bool
     */
    public function orderRefund($orderSn, $storeId, $operatorId)
    {
        $orderMod = &m("order{$storeId}");
        $orderRelationMod = &m("orderRelation{$storeId}");
        $sql = "select a.id, a.order_state, a.buyer_id, c.refund_amount, b.id as relation_id, b.payment_type, b.refund_source from " . DB_PREFIX . "order_{$storeId} as a 
                left join " . DB_PREFIX . "order_relation_{$storeId} as b on a.id = b.order_id 
                left join " . DB_PREFIX . "order_refund as c on a.order_sn = c.order_sn where a.order_sn = '{$orderSn}'";
        //订单信息
        $orderData = $orderMod->querySql($sql);
        $orderData = $orderData[0];
        //买家信息
        $userMod = &m('user');
        $userData = $userMod->getRow($orderData['buyer_id']);
        switch ($orderData['payment_type']) {
            case 1:  //支付宝
                break;
            case 2:  //微信
                break;
            case 3:  //余额
                $newAmount = $userData['amount'] + $orderData['refund_amount'];
                //生成余额日志
                $source = 0;
                switch ($orderData['refund_source']) {
                    case 1:  //小程序
                        $source = 2;
                        break;
                    case 2:  //公众号
                        $source = 1;
                        break;
                    case 3:  //代客下单
                        $source = 4;
                        break;
                    case 4:  //pc前台下单
                        $source = 3;
                        break;
                }
                //生成余额日志
                $amountData = array(
                    'order_sn' => $orderSn,
                    'type' => 6,
                    'status' => 1,
                    'c_money' => $orderData['refund_amount'],
                    'old_money' => $userData['amount'],
                    'new_money' => $newAmount,
                    'source' => $source,
                    'add_user' => $orderData['buyer_id'],
                    'add_time' => time(),
                );
                $amountLogMod = &m('amountLog');
                $amountLogResult = $amountLogMod->doInsert($amountData);
                if (!$amountLogResult) {
                    $this->setData('', 0, '余额日志生成失败');
                }
                //增加用户余额
                $user = array(
                    'amount' => $newAmount
                );
                $userResult = $userMod->doEdit($userData['id'], $user);
                if (!$userResult) {
                    $this->setData('', 0, '用户余额变更失败');
                }
                //变更余额日志状态
                $amountStatus = array(
                    'status' => 2,
                    'check_user' => $operatorId,
                    'pay_time' => time(),
                );
                $amountStatusResult = $amountLogMod->doEdit($amountLogResult, $amountStatus);
                if (!$amountStatusResult) {
                    $this->setData('', 0, '余额表状态变更失败');
                }
                break;
            case 4:  //线下
                break;
            case 5:  //免费兑换
                break;
        }
        //更改订单状态
        $orderResult = $orderMod->doEdit($orderData['id'], array('order_state' => 70,));
        $relationData = array(
            'cancel_time' => time(),
            'refund_review_time' => time(),
            'refund_review_user' => $operatorId,
        );
        $relationResult = $orderRelationMod->doEdit($orderData['relation_id'], $relationData);
        if (!$orderResult || !$relationResult) {
            $this->setData('', 0, '余额表状态变更失败');
        }
        return true;
    }

    /**
     * 获取退款金额
     * @param $store_id
     * @param $order_sn
     * @return array
     */
    public function getRefundMoney($store_id, $order_sn)
    {
        $sql = "SELECT goods_amount,order_amount FROM bs_order_{$store_id} WHERE `order_sn` = '{$order_sn}'";

        $data = $this->querySql($sql);

        return $data;
    }

    /**
     * 获取商品数量
     * @param $order_sn
     * @return array
     */
    public function getGoodsCounts($order_sn)
    {
        $sql = "SELECT SUM(goods_num) AS sums   FROM bs_order_goods WHERE order_id = '{$order_sn}'";
        $data = $this->querySql($sql);

        return $data;
    }

    /**
     * 获取店铺商品id
     * @param $order_sn
     * @return array
     */
    public function getStoreGoodsId($order_sn)
    {
        $sql = "SELECT `goods_id` FROM bs_order_goods WHERE `order_id` = '{$order_sn}'";
        $data = $this->querySql($sql);
        return $data;
    }
}