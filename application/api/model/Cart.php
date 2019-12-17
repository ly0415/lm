<?php

namespace app\api\model;

use think\Db;
use app\common\model\Cart       as CartModel;
use app\common\model\Order      as OrderModel;
use app\api\model\StoreGoods    as StoreGoodsModel;
use app\api\model\StoreConsole  as StoreConsoleModel;

/**
 * 购物车管理
 * Class Cart
 * @package app\api\model
 */
class Cart extends CartModel{

    const EXPECT_TIME = 86400;

    /**
     * 立即购买加入购物车
     * @author  luffy
     * @date    2019-07-31
     */
    public function addCart($user_id, $store_goods_id, $key, $price, $num, $delivery_type, $store_goods_spec_price_info){
        $store_goods_info       = StoreGoodsModel::get($store_goods_id);
        Db::startTrans();
        try{
            $this->allowField(true)->save([ 
                'user_id'       => $user_id,
                'store_id'      => $store_goods_info['store_id'],
                'goods_id'      => $store_goods_id,
                'goods_sn'      => $store_goods_info['goods_sn'],
                'goods_name'    => $store_goods_info['goods_name'],
                'market_price'  => $store_goods_info['market_price'],
                'goods_price'   => $price,
                'goods_num'     => $num,
                'spec_key'      => $key,
                'spec_key_name' => ($store_goods_spec_price_info ? $store_goods_spec_price_info['key_name'] : ''),
                'is_buy'        => 1,
                'add_time'      => time(),
                'order_from'    => 2,
                'delivery_type' => $delivery_type,
                'shipping_store_id' => $store_goods_info['store_id'],
            ]);
            Db::commit();
            return $this->id;
        }catch (\Exception $e){
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 加入购物车
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-10
     * Time: 10:49
     */
    public function checkAdd($userId,$storeId,$shippingStoreId,$storeGoodsId, $goodsNum, $specKey,$orderFrom,$deliveryType,$isBuy = 0)
    {
        $cart = self::detail(['user_id'=>$userId,'store_id'=>$storeId,'goods_id'=>$storeGoodsId,'spec_key'=>$specKey,'delivery_type'=>$deliveryType,'is_buy'=>$isBuy]);
        // 加入购物车后的商品数量
        $cartGoodsNum = $goodsNum + ($cart ? $cart['goods_num'] : 0);
        // 获取商品信息
        $goods = StoreGoodsModel::detail($storeGoodsId);
        // 验证商品能否加入
        if (!$this->checkGoods($goods, $specKey, $cartGoodsNum)) {
            return false;
        }
        if($cart){
            $cart->setInc('goods_num',$goodsNum);
            return $cart['id'];
        }
        $cart = [];
        // 购物车信息
        $cart['user_id'] = $userId;
        $cart['store_id'] = $storeId;
        $cart['goods_id'] = $storeGoodsId;
        $cart['goods_sn'] = $goods['goods_sn'];
        $cart['goods_name'] = $goods['goods_name'];
        $cart['sku'] = $goods['sku'];
        $cart['goods_price'] = $goods['goods_sku']['price'];
        $cart['market_price'] = $goods['market_price'];
        $cart['goods_num'] = $cartGoodsNum;
        $cart['spec_key'] = $goods['goods_sku']['key'];
        $cart['spec_key_name'] = $goods['goods_sku']['key_name'];
        $cart['shipping_store_id'] = $shippingStoreId;
        $cart['add_time'] = time();
        $cart['order_from'] = $orderFrom;
        $cart['delivery_type'] = $deliveryType;
        // 记录到购物车列表
        return $this->add($cart,false);
    }

    /**
     * 验证商品是否可以购买
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-10
     * Time: 10:54
     */
    private function checkGoods($goods, $spec_key, $cartGoodsNum)
    {
        // 判断商品是否下架
        if (!$goods || !$goods['mark'] || $goods['is_on_sale']['value'] != 1) {
            $this->setError('很抱歉，商品信息不存在或已下架');
            return false;
        }
        // 商品sku信息
        if(!$goods['goods_sku'] =  StoreGoodsSpecPrice::getSpecPriceStock($goods['id'],$spec_key)){
            $this->setError('很抱歉，商品库存不足');
            return false;
        }
        // 判断商品库存
        if ($cartGoodsNum > $goods['goods_sku']['stock']) {
            $this->setError('很抱歉，商品库存不足');
            return false;
        }
        return true;
    }


    /**
     * 购物车详情
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-10
     * Time: 11:54
     */
    public static function detail($query,$is_buy = 0){
        $filter['is_buy'] = $is_buy;
        if(is_array($query)){
            $filter = array_merge($filter,$query);
        }else{
            $filter['id'] = (int)$query;
        }
        return self::get($filter);
    }


    /**
     * 加入购物车
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-05
     * Time: 17:56
     */
    public function add($data,$multiple = true){
        try{
            //批量插入
            if($multiple){

                return $this->allowField(true)->saveAll($data);

            }
            //单条插入并返回id
            $this->allowField(true)->save($data);
            return $this['id'];
        }catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }


    /**
     * 改变购物车数量
     * Created by PhpStorm.
     * Author: ly
     * Date: 2019-12-10
     * Time:
     */
    public function doChangeNum($goods_num='',$cart_id='',$store_id='',$user_id=''){

        $list = $this->get($cart_id);
        if(empty($cart_id) || empty($list)){
            $this->setError('参数错误');
            return false;
        }
        $data['goods_num'] = !empty($goods_num) ? intval($goods_num) : "0";
        $cart_id = !empty($cart_id) ? intval($cart_id) : "";
        $store_id = !empty($store_id) ? intval($store_id) : '';
        try{

            $this->where('id',$cart_id)->update($data);
            return true;
        }catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 获得用户购物车总数
     * @author ly
     * @date  2019-12-10
     *
     */
    public function getStoreTableNumber($user_id=''){
        if(empty($user_id)){
            return 0;
        }
        $list = $this->where(['user_id'=>$user_id,'is_buy'=>0])->column('goods_num');
        if(empty($list)){
            return 0;
        }
        return array_sum($list);
    }

    /**
     * 获取提交购物信息
     * @author  luffy
     * @date    2019-07-31
     */
    public function getCartInfos($cart_ids,$user_id = 0,$address_id = 0){
        $data   = self::all(['id'=>['in', $cart_ids]]);

        return $this->_data(!empty($data) ? $data->toArray() : [],$user_id,$address_id);
    }

    /**
     * 根据需要转换-------前面一步检验商品规格、价格、库存是否失效(购物车展示商品以及点击提交都需要校验)
     * @author  luffy
     * @date    2019-08-01
     */
    public function _data($info, $user_id = 0, $address_id = 0){

        if(empty($info)){
            return false;
        }

        $delivery_type  = $info[0]['delivery_type'];
        $store_id       = $info[0]['store_id'];
        $cacheStore     = Store::getCacheAll()[$store_id];
        //自提获取自提时间范围
        if($delivery_type !== 2){
            $timeArea   = $this->getTineArea($cacheStore['store_start_time'], $cacheStore['store_end_time']);
        }
        //获取配送属性名称
        $OrderModel     = new OrderModel();
        //获取用户所属分销人员信息
        $fxInfo         = FxUser::getBeloneFxUser($info[0]['user_id'], 'a.id,a.fx_code,a.discount,b.discounts');
        $discount_rate = 0;
        if(!empty($fxInfo)){
            $discount_rate = $fxInfo['discount'];
            if($fxInfo['discounts'] > 0){
                $discount_rate = $fxInfo['discounts'];
            }
        }
        $address        = UserAddress::detail($user_id,$address_id);
        if(!empty($address)){
            if($address['pays']==1){
                $address['address_default']=$address['city'].$address['mailing_address'];
            }else{
                $address['address_default']=$address['mailing_address'];
            }
        }

        //获取最大抵扣睿积分和对应金额
//        $point_price  = GoodsModelSpec::getStorePointSite($info[0]['store_id']);   //获取店铺可抵扣比例（后期替换掉）
        //初始化返回数组
        $pubArr = [
            'data'              => [],
            'delivery_type'     => $delivery_type,
            'format_delivery_type'  => $OrderModel->delivery_type[$delivery_type],
            'total_goods_num'   => 0,
            'delivery_fee' => 0,
            'percent' => [],
            'rule_fee' => 0,
            'shipping_fee' => 0,
            'total_goods_price' => 0,
            'discount_rate' => $discount_rate  ,
            'fx_code'           => (!empty($fxInfo) ? $fxInfo['fx_code'] : ''),
            'fx_user_id'           => (!empty($fxInfo) ? $fxInfo['id'] : ''),
            'fx_discount_money' => 0,
            'userAddress'       => $address,
            'timeArea'          => (!empty($timeArea) ? $timeArea : ''),
            'store_name'        => $cacheStore['store_name'] //获取店铺名称
        ];

        $goodsList  = [];
        foreach ($info as $key => $value){

            //获取原始商品运费
            $pubArr['delivery_fee'] += StoreGoodsModel::getDeliveryFee($value['goods_id']) * $value['goods_num'];

            //获取商品图片
            $info[$key]['format_goods_image']   = (new StoreGoods)->where('id','=',$value['goods_id'])->value('original_img');
            $is_joint = (new StoreGoods)->where('id','=',$value['goods_id'])->value('store_goods_type');
            if($is_joint == 3){
                $info[$key]['format_goods_image'] = 'web/uploads/small/'.$info[$key]['format_goods_image'];
            }

            $goods_price_all                    = ($value['goods_price'] * $value['goods_num']);

            //商品总价
            $pubArr['total_goods_price']        += $goods_price_all;
            $info[$key]['totalMoney']           = $goods_price_all;
            //商品总数
            $pubArr['total_goods_num']          += $value['goods_num'];
            //获取规格名称
            if($value['spec_key']){
                $info[$key]['format_spec_name'] = GoodsModelSpec::getGoodsSpecName($value['spec_key']);
            }
        }
        $pubArr['data']         = $info;

        if(isset($delivery_type) && $delivery_type == 2){
            $shipping_fee       = StoreFare::getFare($pubArr['total_goods_num'], $store_id);
            $pubArr['rule_fee'] = $shipping_fee;
                $percent = StoreConsoleModel::getStorePercent($store_id);
                if($percent == 0){
                    $pubArr['percent'] = ['text'=>'活动期间，配送费全额减免','value'=>$percent];
                }elseif ($percent > 0 && $percent < 1){
                    $pubArr['percent'] = ['text'=>sprintf('活动期间，配送费打%s折',$percent * 10),'value'=>$percent];
                }
                $pubArr['shipping_fee']         = $pubArr['delivery_fee'] * $shipping_fee * $percent;
                $pubArr['total_goods_price']    += $pubArr['delivery_fee'] * $shipping_fee * $percent;

        }
        if(!empty($fxInfo))
            $pubArr['fx_discount_money'] = number_format(($pubArr['total_goods_price'] - $pubArr['shipping_fee']) * $fxInfo['discount'] * 0.01, 2, '.', '');
        return $pubArr;
    }

    /**
     * 自提获取自提时间范围
     * @author  luffy
     * @date    2019-08-12
     */
    private function getTineArea($start_time, $end_time)
    {
        if(empty($start_time) || empty($end_time)){
            return [
                'start_time'=> '00:00',
                'end_time'  => '23:59',
                'add_time' => '00:10',
                'sub_time' => '23:49'
            ];
        }
        return [
            'start_time'=>  $start_time,
            'end_time'  => $end_time,
            'add_time' => date('H:i',strtotime(date('Y-m-d').$start_time) + 600),
            'sub_time' => date('H:i',strtotime(date('Y-m-d').$end_time) - 600)
        ];
//        $current_str    = date('Y-m-d ');
//        $start_time     = strtotime($current_str.$start_time);
//        $end_time       = strtotime($current_str.$end_time);
        //当前时间加上10小时
//        $maxTime        = time() + 10*60*60;
//        if( $maxTime <= $end_time){
//            return [
//                'start_time'=> date('H:i', $start_time),
//                'end_time'  => date('H:i', $maxTime)
//            ];
//        } else {
//            return [
//                'start_time'=> date('H:i', $start_time),
//                'end_time'  => date('H:i', $end_time)
//            ];
//        }
    }

    /**
     *  获取购物车中的商品列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-02
     * Time: 17:05
     */
    public function getList($cart_ids,$user_id)
    {
        // 购物车商品列表
        $goodsList = [];
        // 获取购物车列表
        $cartList = $this->getCartList($cart_ids,$user_id);
        if (empty($cartList)) {
            $this->setError('当前购物车没有商品');
            return false;
        }
        // 购物车中所有商品id集
        $goodsIds = array_unique(array_column($cartList, 'goods_id'));
        // 获取并格式化商品数据
        $goodsData = [];
        foreach ((new StoreGoodsModel)->getListByIds($goodsIds) as $item) {
            $goodsData[$item['id']] = $item;
        }
//        dump($goodsData);die;
        // 格式化购物车数据列表
        foreach ($cartList as $cart) {
            // 判断商品不存在则自动删除
            if (!isset($goodsData[$cart['goods_id']])) {
                $this->setError('很抱歉，商品 [' . $cart['goods_name'] . '] 已售空');
                return false;
            }
            /* @var GoodsModel $goods */
            $goods = $goodsData[$cart['goods_id']];
            // 判断商品是否已删除
            if (!$goods['mark']) {
                $this->setError('很抱歉，商品 [' . $goods['goods_name'] . '] 已售空');
                return false;
            }
            // 商品sku信息
            $goods['spec_key'] = $cart['spec_key'];
            $goods['member_goods_price'] = $cart['member_goods_price'];
            $goods['cart_id'] = $cart['id'];
            $goods['spec_key_name'] = $cart['spec_key_name'];
//            $goods['key'] = $this->formatSpec($cart['spec_key']);
            // 判断商品是否下架
            if ($goods['is_on_sale']['value'] != 1) {
                $this->setError('很抱歉，商品 [' . $goods['goods_name'] . '] 已下架');
                return false;
            }

            // 商品sku不存在则自动删除
            if (!$goods['goods_sku'] = StoreGoodsSpecPrice::getSpecPriceStock($cart['goods_id'],$this->formatSpec($cart['spec_key']))) {
                $this->setError('很抱歉，商品 [' . $goods['goods_name'] . '] 库存不足');
                return false;
            }

            // 判断商品库存
            if ($cart['goods_num'] > $goods['goods_sku']['stock']) {

                $this->setError('很抱歉，商品 [' . $goods['goods_name'] . '] 库存不足');
                return false;
            }

            if($goods['store_goods_type'] && $goods['store_goods_joint']){

                    foreach ($goods['store_goods_joint'] as &$joint){
                        // 商品sku不存在则自动删除
                        if (!$joint['goods_sku'] = StoreGoodsSpecPrice::getSpecPriceStock($joint['store_goods_ids'],$this->formatSpec($joint['key']))) {
                            $this->setError('很抱歉，组合商品 [' . $goods['goods_name'] . '] 中 ['.$joint['store_goods']['goods_name'].']库存不足');
                            return false;
                        }
                        // 判断商品库存
                        if ($joint['num'] > $joint['goods_sku']['stock']) {

                            $this->setError('很抱歉，组合商品 [' . $goods['goods_name'] . '] 中 ['.$joint['store_goods']['goods_name'].'--'.($joint['key_name'] ? : '无规格').']库存不足');
                            return false;
                        }
                    }

            }

            // 商品单价
            $goods['goods_price'] = $goods['goods_sku']['price'];
            // 购买数量
            $goods['total_num'] = $cart['goods_num'];

            // 商品总价
            $goods['total_price'] = $total_price = bcmul($goods['member_goods_price'], $cart['goods_num'], 2);
            $goodsList[] = $goods->toArray();
        }
        return $goodsList;
    }



    /**
     * 获取购物车列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-02
     * Time: 17:00
     */
    public function getCartList($cartIds,$user_id){
        if(!$cartIds || !$user_id){
            return [];
        }
        $query['user_id'] = $user_id;
        if (!empty($cartIds) && !is_array($cartIds)){
            $query['id'] = ['IN',(strpos($cartIds, ',') !== false) ? explode(',', $cartIds) : [$cartIds]];
        }
        if (!empty($cartIds) && is_array($cartIds)){
            $query['id'] = ['IN',$cartIds];
        }
        return $this->where($query)->select()->toArray();
    }


    /**
     * 设置错误信息
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-27
     * Time: 10:51
     */
    private function setError($error)
    {
        empty($this->error) && $this->error = $error;
    }

    /**
     * 是否存在错误
     * @return bool
     */
    public function hasError()
    {
        return !empty($this->error);
    }

    /**
     * 删除购物车中指定商品
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-02
     * Time: 17:40
     */
    public function delete($cartIds = null)
    {
        $indexArr = strpos($cartIds, ',') !== false ? explode(',', $cartIds) : [$cartIds];
        $this->where('id','IN',$indexArr)
            ->delete();
    }

    /**
     * 格式化规格
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-02
     * Time: 18:19
     */
    public function formatSpec($specKey){
        $spec_arr = [];
        if ($specKey) {
            $key_arr = explode('_', $specKey);
            $key_pailie = arrangement($key_arr, count($key_arr));
            foreach ($key_pailie as $v) {
                $spec_arr[] = implode('_', $v);
            }
        }
        return $spec_arr;
    }


    /**
     * 购物车列表 (含商品信息)
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-28
     * Time: 11:31
     */
    public function getUserList($data)
    {
        // 返回的数据
        $returnData = [];
        // 获取购物车商品列表
        $goodsList = $this->getGoodsList($data['cart_ids'],$data['user_id']);
        $address = '';
        if(isset($data['sendout']) && in_array($data['sendout'],[2,3,4]) && isset($data['addressId'])){
            $address = UserAddress::detail($data['user_id'],$data['addressId']);
        }
        // 订单商品总数量
        $orderTotalNum = array_sum(array_column($goodsList, 'total_num'));
        // 订单商品总金额
        $orderTotalPrice = array_sum(array_column($goodsList, 'total_price'));
        return array_merge([
            'goods_list' => array_values($goodsList),         // 商品列表
            'delivery' => $address ? serialize($address->toArray()) : '',
            'order_total_num' => $orderTotalNum,              // 商品总数量
            'order_total_price' => sprintf('%.2f', $orderTotalPrice),   // 商品总金额 (不含运费)
            'order_pay_price' => sprintf('%.2f', $orderTotalPrice),     // 实际支付金额
            'order_sn' => \app\common\service\Order::createOrderNo(),
            'store_id' => isset($data['storeid']) ? $data['storeid'] : 0,
            'sendout' => isset($data['sendout']) ? $data['sendout'] : 1,
            'sendout_time' => (isset($data['pei_time']) && $data['sendout'] == 1) ? strtotime(date('Y-m-d').' '.$data['pei_time']) : 0,
            'post_sendout' => isset($data['post_sendout']) ? $data['post_sendout'] : 0,
            'seller_msg' => isset($data['seller_msg']) ? $data['seller_msg'] : '',
            'address_id' => isset($data['addressId']) ? $data['addressId'] : 0,
            'fx_code' => isset($data['fxPhone']) ? $data['fxPhone'] : 0,
            'fx_discount' => isset($data['discount_rate']) ? $data['discount_rate'] : 0,
            'shippingfee' => isset($data['shippingfee']) ? $data['shippingfee'] : 0,
            'fx_user_id' => isset($data['fx_user_id']) ? $data['fx_user_id'] : 0,
            'daifu' => isset($data['daifu']) ? $data['daifu'] : 0,
            //优惠券id
            'coupon_id' => isset($data['couponId']) ? $data['couponId'] : 0,
            //用户优惠券id
            'user_coupon_id' => isset($data['userCouponId']) ? $data['userCouponId'] : 0,
            //优惠券金额
            'coupon_amount' => isset($data['discount_price']) ? $data['discount_price'] : 0,
            'source' => isset($data['source']) ? $data['source'] : 1,
            'sendout_sign' => in_array($data['sendout'],[3,4]) ? 1 :  0,
            'table_type' => isset($data['table_type']) ? $data['table_type'] : 0,
            'table_number' => isset($data['table_number']) ? $data['table_number'] : 0,
            'table_num' => isset($data['table_num']) ? $data['table_num'] : 0,
            'intra_region' => true,
            'has_error' => $this->hasError(),
            'error_msg' => $this->getError(),
        ], $returnData);
    }

    /**
     * 获取购物车中的商品列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-02
     * Time: 11:41
     */
    private function getGoodsList($cart_ids,$user_id)
    {
        // 购物车商品列表
        $goodsList = [];
        // 获取购物车列表
        $cartList = $this->getCartList($cart_ids,$user_id);
        if (empty($cartList)) {
            $this->setError('当前购物车没有商品');
            return $goodsList;
        }
        // 购物车中所有商品id集
        $goodsIds = array_unique(array_column($cartList, 'goods_id'));
        // 获取并格式化商品数据
        $goodsData = [];
        foreach ((new StoreGoodsModel)->getListByIds($goodsIds) as $item) {
            $goodsData[$item['id']] = $item;
        }
        // 格式化购物车数据列表
        foreach ($cartList as $cart) {
            // 判断商品不存在则自动删除
            if (!isset($goodsData[$cart['goods_id']])) {
                $this->delete($cart['id']);
                continue;
            }
            /* @var GoodsModel $goods */
            $goods = $goodsData[$cart['goods_id']];
            // 判断商品是否已删除
            if (!$goods['mark']) {
                $this->delete($cart['id']);
                continue;
            }
            // 商品sku信息
            $goods['spec_key'] = $cart['spec_key'];
            $goods['goods_price'] = $cart['goods_price'];
            $goods['member_goods_price'] = $cart['member_goods_price'];
            $goods['cart_id'] = $cart['id'];
            $goods['spec_key_name'] = $cart['spec_key_name'];
//            $goods['key'] = $this->formatSpec($cart['spec_key']);
            // 商品sku不存在则自动删除
            if (!$goods['goods_sku'] = StoreGoodsSpecPrice::getSpecPriceStock($cart['goods_id'],$this->formatSpec($cart['spec_key']))) {
                $this->delete($cart['id']);
                continue;
            }
            // 判断商品是否下架
            if ($goods['is_on_sale']['value'] != 1) {
                $this->setError('很抱歉，商品 [' . $goods['goods_name'] . '] 已下架');
            }

            // 判断商品库存
            if ($cart['goods_num'] > $goods['goods_sku']['stock']) {
                $this->setError('很抱歉，商品 [' . $goods['goods_name'] . '] 库存不足');
            }
//            dump($goods->toArray());dump($cart);die;
            // 商品单价
            $goods['goods_price'] = $goods['goods_sku']['price'];
            // 购买数量
            $goods['total_num'] = $cart['goods_num'];
            // 商品总价
            $goods['total_price'] = $total_price = number_format($goods['goods_price'] * $cart['goods_num'], 2, '.', '');

            $goodsList[] = $goods->toArray();
        }
        return $goodsList;
    }



    /**
     * 清空当前用户购物车
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-02
     * Time: 10:34
     */
    public function clearAll($cartIds = null)
    {
        if(!empty($cartIds) && is_array($cartIds)){
            $cartIds = implode(',',$cartIds);
        }
        $this->delete($cartIds);
    }




}
