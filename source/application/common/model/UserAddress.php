<?php

namespace app\common\model;

/**
 * 用户收货地址模型
 * Class UserAddress
 * @package app\common\model
 */
class UserAddress extends BaseModel
{
    protected $name = 'user_address';

    /**
     * 设置手机号
     * author fup
     * date 2019-07-30
     */
    public function getPhoneAttr($value){
        return substr_replace($value, '****', 3, 4);
    }
}
