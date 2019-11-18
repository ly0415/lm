<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-13
 * Time: 下午 5:41
 */

namespace app\store\model;


use app\common\model\BaseModel;

class CouponLog extends BaseModel
{
    protected $append = ['state'];

    protected $createTime = 'add_time';

    protected $updateTime = false;

    /**
     * 优惠券状态 (是否可领取)
     * @param $value
     * @param $data
     * @return array
     */
    public function getStateAttr($value, $data)
    {
        if($data && empty($data)){
            return ['text' => '未使用', 'value' => 0];
        }
        return ['text' => '已使用', 'value' => 1];
    }
    /**
     * 获取用户已使用劵码id
     * @param $value
     * @param $data
     * @return array
     */
    public static function getCouponId($where = []){
        return self::where($where)->column('user_coupon_id');
    }
}