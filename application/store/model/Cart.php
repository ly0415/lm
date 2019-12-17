<?php

namespace app\store\model;

use app\common\model\Cart as CartModel;
use app\common\service\Order as OrderService;
use app\common\enum\order\PayType as PayTypeEnum;
use app\store\model\StoreGoods as StoreGoodsModel;
use app\common\enum\DeliveryType as DeliveryTypeEnum;
use app\common\service\delivery\Express as ExpressService;
use app\common\enum\OrderType as OrderTypeEnum;
use app\store\model\StoreConsole as StoreConsoleModel;

/**
 * 商品分类模型
 * Class City
 * @package app\store\model
 */
class Cart extends CartModel
{


    /**
     * 购物车列表 (含商品信息)
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-28
     * Time: 11:31
     */
    public function getList($data,$store_id)
    {
        // 返回的数据
        $returnData = [];
        // 获取购物车商品列表
        $goodsList = $this->getGoodsList($data['uniquecode'],$data['cart_ids']);
        // 订单商品总数量
        $orderTotalNum = array_sum(array_column($goodsList, 'total_num'));
        // 订单商品总金额
        $orderTotalPrice = array_sum(array_column($goodsList, 'total_price'));
        return array_merge([
            'goods_list' => array_values($goodsList),         // 商品列表
            'order_total_num' => $orderTotalNum,              // 商品总数量
            'order_total_price' => sprintf('%.2f', $orderTotalPrice),   // 商品总金额 (不含运费)
            'order_pay_price' => sprintf('%.2f', $orderTotalPrice),     // 实际支付金额
            'pay_type' => isset($data['pay_type']) ? $data['pay_type'] : PayTypeEnum::WECHAT,        // 支付方式
            'order_sn' => $data['uniquecode'],
            'store_id' => $store_id,
            'sendout' => isset($data['sendout']) ? $data['sendout'] : 1,
            'source' => isset($data['source']) ? $data['source'] : 3,
            'intra_region' => true,
            'address_id' => isset($data['address_id']) ? $data['address_id'] : 0,
            'sendout_time' => isset($data['sendout_time']) ? strtotime($data['sendout_time']) : time() + 600,
            'seller_msg' => isset($data['seller_msg']) ? $data['seller_msg'] : '',
            'valet_order_user_id' => isset($data['valet_order_user_id']) ? $data['valet_order_user_id'] : USER_ID,
            'valet_order_time' => time(),
            'fx_user_id' => isset($data['fx_user_id']) ? $data['fx_user_id'] : 0,
            'fx_code' => isset($data['fx_code']) ? $data['fx_code'] : 0,
            //分销折扣
            'fx_discount' => isset($data['fx_discount']) ? $data['fx_discount'] : 0,
            //折扣数
            'discount_num' => (isset($data['discount_num']) && $data['discount_num'] > 0) ? $data['discount_num'] : 10,
            //优惠券id
            'coupon_id' => isset($data['coupon_id']) ? $data['coupon_id'] : 0,
            //用户优惠券id
            'user_coupon_id' => isset($data['user_coupon_id']) ? $data['user_coupon_id'] : 0,
            //优惠券金额
            'coupon_amount' => isset($data['coupon_amount']) ? $data['coupon_amount'] : 0,
            //1：折扣金:2：优惠金额
            'discount_type' => isset($data['discount_type']) ? $data['discount_type'] : 1,
            'reduced_price' => isset($data['reduced_price']) ? $data['reduced_price'] : 0,
            //平台来源
            'underline_pay_money'   => isset($data['payMoney']) ? $data['payMoney'] : '',
            'store_source_id'       => isset($data['store_source_id']) ? $data['store_source_id'] : 1,
            'source_delivery_fee'   => isset($data['source_delivery_fee']) ? $data['source_delivery_fee'] : '',
            'source_address'        => isset($data['source_address']) ? $data['source_address'] : '',
            'has_error' => $this->hasError(),
            'error_msg' => $this->getError(),
        ], $returnData);
    }

    /**
     * 获取购物车列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-02
     * Time: 17:00
     */
    public function getCartList($uniquecode = null,$cartIds = null){
        $query = [];
        if(!empty($uniquecode)){
            $query['uniquecode'] = $uniquecode;
        }
        if (!empty($cartIds) && !is_array($cartIds)){
            $query['id'] = ['IN',(strpos($cartIds, ',') !== false) ? explode(',', $cartIds) : [$cartIds]];
        }
        if (!empty($cartIds) && is_array($cartIds)){
            $query['id'] = ['IN',$cartIds];
        }
        return $this->where($query)->select()->toArray();
    }

    /**
     *  获取购物车中的商品列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-02
     * Time: 17:05
     */
    private function getGoodsList($uniquecode,$cart_ids)
    {
        // 购物车商品列表
        $goodsList = [];
        // 获取购物车列表
        $cartList = $this->getCartList($uniquecode,$cart_ids);
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
     * 添加购物车
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-27
     * Time: 10:16
     */
    public function add($data){
        if(!isset($data['store_goods_id']) || empty($data['store_goods_id'])){
            $this->error = '请选择商品';
            return false;
        }
        $uniquecode = OrderService::createOrderNo();
        $insertData = [];
        foreach ($data['store_goods_id'] as $k => $v){
            $storeGoods = StoreGoods::detail($v);
            if(!$this->checkStoreGoods($storeGoods,$data['store_goods_spec_key'][$k],$data['store_goods_number'][$k])){
                return false;
            }
            $insertData[] = [
              'store_id' => STORE_ID,
              'goods_id' => $v,
              'goods_name' => $data['store_goods_name'][$k],
              'goods_price' => $this->getPrice($v,$data['store_goods_spec_key'][$k]),
              'member_goods_price' => $data['store_goods_price'][$k],
              'goods_num' => $data['store_goods_number'][$k],
                'spec_key' => $data['store_goods_spec_key'][$k], //规格
                'spec_key_name' => $data['store_goods_spec_name'][$k], //规格名
                'shipping_store_id' => STORE_ID, //配送区域站点ID
                'uniquecode' => $uniquecode, //唯一标识
                'delivery_type' => 1
            ];
        }
        return $this->allowField(true)->insertAll($insertData) ? $uniquecode : false;
    }

    /**
     * 订单配送-快递配送
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-03
     * Time: 19:00
     */
    private function orderExpress(&$returnData, $orderTotalPrice,$orderTotalNum,$storeId)
    {
            $shipping_fee = StoreFare::getFare($orderTotalNum, $storeId);
            $percent = StoreConsoleModel::getStorePercent($storeId);
            if($percent == 0){
                $returnData['percent'] = ['text'=>'活动期间，配送费全额减免','value'=>$percent];
            }elseif ($percent > 0 && $percent < 1){
                $returnData['percent'] = ['text'=>sprintf('活动期间，配送费打%s折',$percent * 10),'value'=>$percent];
            }
        $returnData['shipping_fee']         = $returnData['delivery_fee'] * $shipping_fee * $percent;

        // 订单总金额 (含运费)
        $returnData['order_pay_price'] = bcadd($orderTotalPrice, $returnData['delivery_fee'] * $shipping_fee * $percent, 2);
    }


    /**
     * 验证商品是否可以购买
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-27
     * Time: 10:47
     */
    public function checkStoreGoods($storeGoods,$spec_key,$cartGoodsNum){
        //商品存在或是否下架
        if(!$storeGoods || 1 != $storeGoods['mark'] || 1 != $storeGoods['is_on_sale']['value']){
            $this->setError('很抱歉，'.$storeGoods['goods_name'].'商品信息不存在或已下架');
            return false;
        }
        $goodsPriceStock = StoreGoodsSpecPrice::getSpecPriceStock($storeGoods['id'],$this->formatSpec($spec_key));
        // 判断商品库存
        if ($cartGoodsNum > $goodsPriceStock['stock']) {
            $this->setError('很抱歉，'.$storeGoods['goods_name'].'商品库存不足');
            return false;
        }
        return true;
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
     * 获取规格对应价格
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-27
     * Time: 17:34
     */
    public function getPrice($storeGoodsId,$spec_key){
        $spec_arr = [];
        if ($spec_key) {
            $key_arr = explode('_', $spec_key);
            $key_pailie = arrangement($key_arr, count($key_arr));
            foreach ($key_pailie as $v) {
                $spec_arr[] = implode('_', $v);
            }
        }
        $goodsPriceStock = StoreGoodsSpecPrice::getSpecPriceStock($storeGoodsId,$spec_arr);
        return $goodsPriceStock['price'];
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
     * 清空当前用户购物车
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-04
     * Time: 10:34
     */
    public function clearAll($cartIds = null)
    {
        if(!empty($cartIds) && is_array($cartIds)){
            $cartIds = implode(',',$cartIds);
        }
        $this->delete($cartIds);
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



}
