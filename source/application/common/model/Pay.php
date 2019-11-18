<?php

namespace app\common\model;

/**
 * 支付配置信息
 * Class Pay
 * @package app\common\model
 */
class Pay extends BaseModel
{
    protected $name = 'pay';

    //关联配置信息
    public function payDetail(){
        return $this->hasMany('PayDetail','pay_id','id');
    }

}
