<?php

namespace app\common\model;

/**
 * 优惠券模型
 * Class Coupon
 * @package app\common\model
 */
class RechargePoint extends BaseModel
{
    protected $name = 'recharge_point';
    protected $append = ['desc'];



    /**
     * 优惠券状态 (是否可领取)
     * @param $value
     * @param $data
     * @return array
     */
    public function getDescAttr($value, $data)
    {
        return ['text' => '充值￥'.$data['c_money'].'送￥'.$data['s_money'], 'c_money' =>$data['c_money'],'s_money'=>$data['s_money']];
    }


    /**
     * 优惠券详情
     * @param $coupon_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($coupon_id)
    {
        return self::get($coupon_id);
    }

}
