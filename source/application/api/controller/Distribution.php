<?php

namespace app\api\controller;

use app\api\model\Distribution   as DistributionModel;

/**
 * 分销控制器
 * @author  luffy
 * @date    2019-10-15
 */
class Distribution extends Controller{

    /**
     * 分销订单
     * @author  luffy
     * @date    2019-08-08
     */
    public function getOrderList($user_id, $page = 1){
        $DistributionModel  = new DistributionModel;
        return $this->renderSuccess( ['page'=>$page, 'listData' => $DistributionModel->getList($user_id, $page)]);
    }

    /**
     * 获取分销人员
     * @author  luffy
     * @date    2019-10-17
     */
    public function getFxUserList($fx_user_id, $page = 1, $phone = ''){
        $DistributionModel  = new DistributionModel;
        return $this->renderSuccess( ['page'=>$page, 'listData' => $DistributionModel -> getFxUserList($fx_user_id, $page, $phone)]);
    }

    /**
     * 设置三级分销人员单独下单优惠比例页面
     * @author  luffy
     * @date    2019-10-17
     */
    public function setDiscountPage($fx_user_id){
        $DistributionModel  = new DistributionModel;
        $info               = $DistributionModel::get($fx_user_id);
        return $this->renderSuccess( ['listData' => floatval($info['discount'])]);
    }

    /**
     * 设置三级分销人员单独下单优惠比例
     * @author  luffy
     * @date    2019-10-17
     */
    public function setDiscount($user_id, $fx_user_id, $discount){
        $DistributionModel      =  new DistributionModel;
        if ($DistributionModel -> setDiscount($user_id, $fx_user_id, $discount)) {
            return $this->renderSuccess([],'操作成功');
        }
        return $this->renderError($DistributionModel->getError() ?: '操作失败');
    }

}