<?php

namespace app\common\model;

/**
 * 用户模型类
 * Class User
 * @package app\common\model
 */
class User extends BaseModel
{
    protected $name = 'user';

    protected $createTime = 'add_time';

    protected $updateTime = false;

    // 性别
    private $gender = ['未知', '男', '女'];

    /**
     * 关联收货地址表
     * @return \think\model\relation\HasMany
     */
    public function address()
    {
        return $this->hasMany('UserAddress');
    }

    /**
     * 获取性别
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-10
     * Time: 11:09
     */
    public function getSexAttr($val){
        return $this->gender[$val];
    }

    /**
     * 关联收货地址表 (默认地址)
     * @return \think\model\relation\BelongsTo
     */
    public function addressDefault()
    {
        return $this->belongsTo('UserAddress', 'address_id');
    }




    /**
     * 获取用户信息
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-29
     * Time: 21:22
     */
    public static function detail($where)
    {
        $filter['mark'] = 1;
        $filter['is_use'] = 1;
        if (is_array($where)) {
            $filter = array_merge($filter, $where);
        } else {
            $filter['id'] = (int)$where;
        }
        return self::get($filter,['rechargePoint','amountLog']);
    }

    //关联用户优惠价
    public function userCoupon(){
        return $this->hasMany('UserCoupon','user_id','id');
    }

    //关联日志
    public function pointLog(){
        return $this->hasMany('PointLog','userid','id');
    }

    //关联余额充值表
    public function rechargePoint(){
        return $this
            ->belongsTo('RechargePoint','recharge_id','id')
            ->bind('percent');
    }

    //关联余额记录表
    public function amountLog(){
        return $this->hasMany('AmountLog','add_user','id')
            ->where('status','=',2)
            ->where('mark','=',1);
    }



}
