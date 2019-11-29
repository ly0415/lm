<?php

namespace app\store\model;

use app\common\model\AmountLog as AmountLogModel;

/**
 * 店铺余额记录模型
 * @author  luffy
 * @date    2019-07-09
 */
class AmountLog extends AmountLogModel
{

    /**
     * 余额充值总额
     * @author  luffy
     * @date    2019-07-10
     */
    public function getRechargeTotal($store_id = 0)
    {
        if( $store_id ){
            //获取指定店铺余额充值总额

        }
        echo '<pre>';print_r( 1 );die;
//        $recharge = $this -> getRechargeTotal
        return $recharge;
    }

    /**
     * 获取业务类型列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        $list = $this->where('mark', '=', 1)
            ->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->select();
        if($list)$list = $list->toArray();
        $data = $this->tree($list);
        return $data;
    }

}
