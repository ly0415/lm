<?php

namespace app\task\model\recharge;

use app\common\model\recharge\Order as OrderModel;

use app\task\model\PointLog;
use app\task\model\User as UserModel;
use app\task\model\RechargePoint as RechargePointModel;
use app\common\enum\OrderType as OrderTypeEnum;
use app\common\enum\order\PayType as PayTypeEnum;
use app\common\enum\recharge\order\PayStatus as PayStatusEnum;

/**
 * 用户充值订单模型
 * Class Order
 * @package app\task\model\recharge
 */
class Order extends OrderModel
{
    /**
     * 获取订单详情(待付款状态)
     * @param $orderNo
     * @return OrderModel|null
     * @throws \think\exception\DbException
     */
    public function payDetail($orderNo)
    {
        return self::detail(['order_sn' => $orderNo, 'status' => PayStatusEnum::PENDING,'mark'=>1]);
    }

    /**
     * 订单支付成功业务处理
     * @param int $payType 支付类型
     * @param array $payData 支付回调数据
     * @return bool
     */
    public function paySuccess($payType, $payData)
    {

        $this->transaction(function () use ($payType, $payData) {
            // 更新订单状态
            $this->save([
                'status' => PayStatusEnum::SUCCESS,
                'pay_time' => time(),
                'transaction_id' => $payData['transaction_id']
            ]);
            // 累积用户余额
            $User = UserModel::detail($this['add_user']);
            //充值规则
            $new = RechargePointModel::detail($this->point_rule_id);
            $old = RechargePointModel::detail($User['recharge_id']);
            $User->setInc('amount', bcadd($new['c_money'],$new['s_money'],2));
            $User->setInc('point', $new['integral']);
            $User->recharge_id = $old['percent'] > $new['percent'] ? $User['recharge_id'] : $this->point_rule_id;
            $User->save();

            (new PointLog)->allowField(true)->save([
                'operator' => '--',
                'username' => $User['phone'],
                'add_time' => time(),
                'deposit'=>$new['integral'],
                'expend'=>'-',
                'note'=>'充值赠送'.$new['integral'].'睿积分',
                'userid'=>$this->add_user
            ]);
        });
        return true;
    }

}