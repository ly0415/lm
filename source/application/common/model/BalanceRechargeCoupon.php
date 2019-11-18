<?php

namespace app\common\model;

/**
 * 余额充值券模型
 * @author  luffy
 * @date    2019-07-10
 */
class BalanceRechargeCoupon extends BaseModel
{
    protected $name = 'balance_recharge_coupon';


    /**
     * 余额充值总额
     * @author  luffy
     * @date    2019-07-11
     */
    public function getRechargeTotal($store_id = 0)
    {
        if( $store_id ){
            $this->where(['b.store_id'=>$store_id]);
        }
        //获取指定店铺余额充值总额
        $list = $this->alias('a')
            ->field('a.id,a.money')
            ->join('store_user b' ,'a.store_user = b.id' , 'LEFT')
            ->where(['a.mark'=>1, 'a.is_use'=>2])
            ->order(['a.add_time' => 'desc'])
            ->select();
        if($list) $list = $list->toArray();

        //计算总额
        $total = 0;
        if(!empty($list)){
            foreach($list as $value){
                $total += $value['money'];
            }
        }
        return $total;
    }

}
