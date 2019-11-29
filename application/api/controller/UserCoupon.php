<?php

namespace app\api\controller;

use app\common\model\coupon\UserCoupon as UserCouponModel;

/**
 * 优惠券中心
 * @author  luffy
 * @date    2019-09-03
 */
class UserCoupon extends Controller{

    /**
     * 获取用户可用优惠券
     * @author  luffy
     * @date    2019-09-03
     */
    public function all($user_id = 0, $store_id = 0, $money = 0, $good_ids = []){
        // 商品结算信息
        if(empty($user_id) || empty($store_id) || empty($money) || empty($good_ids)){
            return $this->renderError('参数错误！');
        }

        //获取购物车商品对应原始商品ID
        $model      = new UserCouponModel;
        $good_arr   = $model->getGoodsAll($good_ids);
        $list       = $model->getCouponAll($user_id, $store_id, $money, $good_arr);
        return $this->renderSuccess(compact('list'));
    }

}