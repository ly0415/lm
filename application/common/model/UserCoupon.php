<?php

namespace app\common\model;


/**
 * 用户优惠券模型
 * Class UserCoupon
 * @package app\common\model
 */
class UserCoupon extends BaseModel
{
    protected $name = 'user_coupon';

    protected $createTime = 'add_time';

    protected $updateTime = false;



    /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('User','user_id','id')->field('id,username,phone')->where(['mark'=>1]);
    }

    /**
     * 关联卷码
     * @return \think\model\relation\BelongsTo
     */
    public function coupon()
    {
        return $this->belongsTo('Coupon','c_id','id');
    }

    /**
     * 卷码使用记录
     * @return \think\model\relation\BelongsTo
     */
    public function log()
    {
        return $this->belongsTo('CouponLog','id','user_coupon_id');
    }



    /**
     * 有效期-开始时间
     * @param $value
     * @return mixed
     */
    public function getStartTimeAttr($value)
    {
        return ['text' => date('Y-m-d', $value), 'value' => $value];
    }

    /**
     * 有效期-开始时间
     * @param $value
     * @return mixed
     */
    public function getAddTimeAttr($value)
    {
        return ['text' => date('Y-m-d', $value), 'value' => $value];
    }

    /**
     * 有效期-结束时间
     * @param $value
     * @return mixed
     */
    public function getEndTimeAttr($value)
    {
        return ['text' => date('Y-m-d', $value), 'value' => $value];
    }

}