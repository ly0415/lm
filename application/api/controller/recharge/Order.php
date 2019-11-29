<?php

namespace app\api\controller\recharge;

use app\api\controller\Controller;
use app\api\model\AmountLog;
use app\api\model\recharge\Order as OrderModel;
use app\api\model\RechargePoint;
use app\api\model\User;

/**
 * 充值记录
 * Class Order
 * @package app\api\controller\user\balance
 */
class Order extends Controller
{
    /**
     * 充值中心
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-23
     * Time: 16:55
     */
    public function index($user_id = null){
        $langData = array(
            '充值中心',
            '记录',
            '余额',
            '积分抵扣',
            '累计充值',
            '选择充值金额',
            '充值',
            '送',
            '送',
            '获得',
            '积分抵扣比例',
            '积分'
        );
        if($user = User::detail($user_id)){
            $user = $user->toArray();
        }
        $user['percent'] = is_null($user['percent']) ? 0 : $user['percent'];
        $user['accumulativeRecharge'] = array_sum(array_column($user['amount_log'], 'c_money'));
        $log['count'] = (AmountLog::where(['type'=>1,'status'=>1,'add_user'=>$user_id,'mark'=>1])->count('*')  ? 1 : 0) + (AmountLog::where(['type'=>4,'status'=>1,'add_user'=>$user_id,'mark'=>1])->count('*') ? 1 : 0);
        $rule = (new RechargePoint)->getList();
        return $this->renderSuccess(
            [
            'langData'=>$langData,
            'userData'=>$user,
            'ruleData'=>$rule,
            'logData' => $log
            ]
        );

    }


}