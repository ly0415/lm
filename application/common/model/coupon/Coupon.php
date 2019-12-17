<?php

namespace app\common\model\coupon;
use app\common\model\BaseModel;

/**
 * 优惠券模型
 * Class Coupon
 * @package app\common\model
 */
class Coupon extends BaseModel
{
    protected $name = 'coupon';
    protected $append = ['desc'];

    protected $createTime = 'add_time';

    protected $updateTime = false;


    /**
     * 优惠券类型
     * @param $value
     * @return mixed
     */
    public function getTypeAttr($value)
    {
        $type = [1 => '抵扣券', 2 => '兑换券'];
        return ['text' => $type[$value], 'value' => $value];
    }

    /**
     * 优惠券状态 (是否可领取)
     * @param $value
     * @param $data
     * @return array
     */
    public function getDescAttr($value, $data)
    {

        if (isset($data['type']) && $data['type'] == 1) {
            return ['text' => '满'.$data['money'].'抵'.$data['discount'].'元', 'money' =>$data['money'],'discount'=>$data['discount'] ];
        }

        return ['text' => '不超过'.$data['money'].'即可使用', 'money' =>$data['money']];
    }


    /**
     * 有效期-开始时间
     * @param $value
     * @return mixed
     */
    public function getStartTimeAttr($value)
    {
        return ['text' => date('Y/m/d', $value), 'value' => $value];
    }

    /**
     * 有效期-结束时间
     * @param $value
     * @return mixed
     */
    public function getEndTimeAttr($value)
    {
        return ['text' => date('Y/m/d', $value), 'value' => $value];
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

    public static function getInfo($where){
        return self::field('coupon_name,money,discount')
            ->where($where)
            ->find();
    }

}
