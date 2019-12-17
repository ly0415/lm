<?php

namespace app\store\model\dealer;

use app\common\model\dealer\Order as OrderModel;
use app\store\model\FxUser;
use app\store\model\FxRule;

/**
 * 分销商订单模型
 * Class Apply
 * @package app\store\model\dealer
 */
class Order extends OrderModel
{

    /**
     * 创建分销订单记录
     * @param $order
     * @param int $order_type 订单类型 (1商城订单)
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-11
     * Time: 11:14
     */
    public static function createOrder(&$order)
    {
        // 分销订单模型
        $model = new self;
        if(isset($order['fx_user_id']) && $order['fx_user_id'] > 0){
            //购买单个商品使用兑换券，不生成分销订单
            if (($order['order_amount'] <= 0) && ($order['coupon_id'] > 0)) {
                if (count($order['order_goods']) <= 1 && ($order['coupon_type'] == 2)) {
                    return true;
                }
            }
            $fxuserInfo = FxUser::get($order['fx_user_id']);
            //获取一级分销信息
            $fxPInfo = FxUser::getLevel1Info($fxuserInfo['id']);
            $fxRuleInfo = FxRule::get($fxPInfo['rule_id']);
            $fxCommissionPercent = bcsub($fxRuleInfo['lev3_prop'], $fxuserInfo['discount'],2);
            //分销fx_order订单表source 1、 代客下单   2 、微信下单  3、前台下单  4、小程序下单  5、指定订单
            //新order表：source 1、小程序 2、公众号 3、代课下单 4、PC前台下单
            $fxOrderData = array(
                'order_id' => $order['order_id'],
                'order_sn' => $order['order_sn'],
                'pay_money' => $order['order_amount'],
                'fx_money' => number_format(($order['goods_amount']-$order['coupon_discount']) * $fxuserInfo['discount'] * 0.01, 2, '.', ''),
                'source' => self::$source[$order['source']],
                'user_id' => $order['buyer_id'],
                'fx_user_id' => $order['fx_user_id'],
                'rule_id' => $fxPInfo['rule_id'],
                'store_cate' => 17,
                'store_id' => $order['store_id'],
                'add_time' => time(),
                'add_user' => $order['buyer_id'],
                'fx_discount'=>$fxuserInfo['discount'],
                'fx_commission_percent'=>$fxCommissionPercent,
                'fx_commission' => number_format($order['order_amount'] * $fxCommissionPercent * 0.01, 2, '.', ''),
                'fx_commission_1' => number_format($order['order_amount'] * $fxRuleInfo['lev1_prop'] * 0.01, 2, '.', ''),
                'fx_commission_2' => number_format($order['order_amount'] * $fxRuleInfo['lev2_prop'] * 0.01, 2, '.', '')
            );
            $fxOrder = $model::get(['order_sn'=>$order['order_sn']]);
            if(!$fxOrder){
//                return $fxOrder->allowField(true)->save($fxOrderData);
                return $model->allowField(true)->save($fxOrderData);
            }
            return true;
        }

    }

}