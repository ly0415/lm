<?php

namespace app\api\controller;
use app\api\model\AmountLog;
use app\common\enum\OrderType as OrderTypeEnum;
use app\common\enum\order\PayType as PayTypeEnum;
use app\api\model\Order as OrderModel;
/**
 * 用户管理
 * Class User
 * @package app\api
 */
class Balance extends Controller
{
 
     /**
     * 微信支付
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-15
     * Time: 20:46
     */
    public function wxPay($order_sn = 0,$open_id = '',$user_id = 0,$pay_type = PayTypeEnum::WECHAT){
        if(!$order_sn || !$user_id || !$open_id){
            return $this->renderError('缺少必要参数');
        }
//        $order = AmountLog::getDetail($order_sn,$user_id);
        $order['order_sn'] = time();
        $order['c_money'] = 1;
        $order['id'] = mt_rand();
        $model = new OrderModel;
        // 构建微信支付请求
        $payment = ($pay_type == PayTypeEnum::WECHAT) ? $model->paymentByWechat($order['order_sn'],$open_id,$order['c_money'], false,OrderTypeEnum::RECHARGE) : [];
        // 返回状态
        return $this->renderSuccess([
            'order_id' => $order['id'],   // 订单id
            'pay_type' => $pay_type,            // 支付方式
            'payment' => $payment               // 微信支付参数
        ]);
    }

    /**
     * 删除余额历史记录
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-23
     * Time: 16:06
     */
    public function delete($id = null,$user_id = null){
        $model = AmountLog::get(['id'=>$id,'add_user'=>$user_id]);
        if($model->setDelete()){
            return $this->renderSuccess();
        }
        return $this->renderError($model->getError() ? : '删除失败');
    }

}
