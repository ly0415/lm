<?php

/**
 * 和退款有关的控制器
 */
class RefundApp extends BaseWxApp{
    public function __construct()
    {
        parent::__construct();
    }
    public function __destruct()
    {
        // TODO: Implement __destruct() method.
    }

    /**
     * 退款商品展示页面
     * @author tangp
     * @date 2019-02-26
     */
    public function index()
    {
        $orderMod = &m('order');
        $storeMod = &m('store');
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : '';
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim($_REQUEST['order_sn'])) : '';
        $lang = !empty($_REQUEST['lang']) ? htmlspecialchars($_REQUEST['lang']) : '';
        $user_id = $this->userId;
        $data = $orderMod->getOrderInfo($user_id,$order_sn,$storeid,$lang);
        $orderData = $orderMod->getDiscountInfo($storeid,$order_sn);
        $storeImage = $storeMod->getOne(array('cond' => "id = '{$storeid}'"));
        $payment = $orderMod->getPaymentCodeData($storeid,$order_sn);
        $money = $orderMod->getRefundMoney($storeid,$order_sn);
        $goodsCount = $orderMod->getGoodsCounts($order_sn);
        $getStoreGoodsId = $orderMod->getStoreGoodsId($order_sn);
        foreach ($getStoreGoodsId as $val) {
            $val = join(",",$val);
            $temp_array[] = $val;
        }
        $str = implode(",", $temp_array);
        $this->assign('data',$data);
        $this->assign('str',$str);
        $this->assign('goodsCount',$goodsCount);
        $this->assign('money',$money);
        $this->assign('orderData',$orderData);
        $this->assign('payment',$payment);
        $this->assign('order_sn',$order_sn);
        $this->assign('storeImage',$storeImage['logo']);
        $this->display('refund/index.html');
    }

    /**
     * 提交退款申请
     * @author tangp
     * @date 2019-02-27
     */
    public function submit()
    {
        $orderRefundMod = &m('orderRefund');
        $orderMod = &m('order');
        $orderGoodsMod = &m('orderGoods');
        $orderRefundGoodsMod = &m('orderRefundGoods');
        $imArry   = !empty($_REQUEST['imArry']) ? $_REQUEST['imArry'] : '';
        $money    = !empty($_REQUEST['money']) ? $_REQUEST['money']: '';
        $textarea = !empty($_REQUEST['textarea']) ? $_REQUEST['textarea'] : '';
        $order_id = !empty($_REQUEST['order_id']) ? $_REQUEST['order_id'] : '';
        $store_goods_id = !empty($_REQUEST['store_goods_id']) ? $_REQUEST['store_goods_id'] : '';
//        $refund_amounts = !empty($_REQUEST['refund_amounts']) ? $_REQUEST['refund_amounts'] : '';
        $sql = "SELECT store_id FROM bs_user_order WHERE order_sn = '{$order_id}' "  ;
        $datas = $orderMod->querySql($sql);
        $sql = "SELECT `order_state` FROM bs_order_{$datas[0]['store_id']} WHERE `order_sn` = '{$order_id}'";
        $res = $orderMod->querySql($sql);
        if ($res[0]['order_state'] == 60){
            $this->setData(array(),0,'订单已申请退款！');
        }
        if ($res[0]['order_state'] == 70){
            $this->setData(array(),0,'订单已申请退款！');
        }
        //处理base64位图片
        $newArr = array();
        foreach ($imArry as $v){
            $imgArrs = $orderRefundMod->base64_upload($v);
            $newArr[] = $imgArrs;
        }
        $newArrs = implode(',',$newArr);
//        echo '<pre>';print_r($newArrs);die;
        $storeGoodsArr = explode(',',$store_goods_id);
        //处理orderRefundGoods表的插入数据
//        $orderInfo = $orderMod->getOne(array('cond'=>"order_id = '{$order_id}'"));
        $arr = array();
        $newArr = array();
        foreach ($storeGoodsArr as $k => $v){
            $sql = "SELECT goods_id,goods_name,goods_image,spec_key_name,goods_pay_price,goods_num FROM bs_order_goods 
                    WHERE `order_id`={$order_id} AND `goods_id`=".$v;
            $result = $orderGoodsMod->querySql($sql);

            $arr[] = $result;
        }
        foreach ($arr as $k => $val){
            $newArr[] = $val[0];
        }
        foreach ($newArr as $k => $v){
            $newArr[$k]['market_price'] = $orderRefundMod->getMarketPrice($v['goods_id']);
        }
        $refundData = array(
            'refund_amount'=> $money,
            'reason_info' => urlencode($textarea),
            'order_sn' => $order_id,
            'refund_goods_ids' => $store_goods_id,
//            'refund_amounts' => $refund_amounts,
            'add_user' => $this->userId,
            'add_time' => time(),
            'refund_images'=>$newArrs
        );
//        echo '<pre>';print_r($refundData);die;
        $res = $orderRefundMod->doInsert($refundData);

        foreach ($newArr as $key => $val){
            $refundGoodsData = array(
                'order_refund_id' => $res,
                'goods_name'      => $val['goods_name'],
                'goods_price'     => $val['market_price'],
                'goods_num'       => $val['goods_num'],
                'goods_image'     => $val['goods_image'],
                'goods_pay_price' => $val['goods_pay_price'],
                'spec_key_name'   => $val['spec_key_name']
            );

            $result = $orderRefundGoodsMod->doInsert($refundGoodsData);
        }

        //订单表更新
        $order_data = array(
            "table" => "order",
            "cond"  => "order_sn = '{$order_id}'",
            "set"   => array(
                "refund_state" => 1,
                "refund_amount"=> $money[1]
            )
        );
        //订单商品表更新
        $order_goods_data = array(
            "table" => "order_goods",
            "cond"  => "order_id = '{$order_id}' and refund_state = 0",
            "set"   => array(
                "refund_state"=> 1
            )
        );
        $sql = "SELECT `store_id` FROM bs_user_order WHERE order_sn = {$order_id}";
        $userOrderData = $orderMod->querySql($sql);
        $orderGoodsMod->doUpdate($order_goods_data);

        $orderMod->doUpdate($order_data);

        $orderMod->update_refund_time($userOrderData[0]['store_id'],$order_id,2);

        if ($res && $result){
            $info['url'] = "?app=order&act=orderRefund";
            $this->setData($info,1,'提交成功！');
        }else{
            $this->setData(array(),0,'提交失败！');
        }
    }


}