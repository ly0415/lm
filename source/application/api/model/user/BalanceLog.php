<?php

namespace app\api\model\user;

use app\common\model\user\BalanceLog as BalanceLogModel;

/**
 * 用户余额变动明细模型
 * Class BalanceLog
 * @package app\api\model\user
 */
class BalanceLog extends BalanceLogModel
{

    /**
     * 获取账单明细列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-24
     * Time: 13:49
     */
    public function getList($userId)
    {
        // 获取列表数据
        return $this->with('rechargePoint')
            ->where('add_user', '=', $userId)
            ->where('mark', '=', 1)
            ->where(['type'=>['not in',[7,20]]])
            ->order(['add_time' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ])->each(function ($item){
                $item['desc'] = '';
                if($item['type']['value'] == 1 && !empty($item['recharge_point'])){
                    $item['desc'] = '充值规则：送'.$item['recharge_point']['s_money'].';'.'送'.$item['recharge_point']['integral'].'积分,'.$item['recharge_point']['percent'].'%积分抵扣比例';
                }
                return $item;
            });
    }

    /**
     *  删除余额记录
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-23
     * Time: 16:22
     */
    public function setDelete(){
        return $this->save(['mark'=>0]);
    }

}