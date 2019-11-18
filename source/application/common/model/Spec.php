<?php

namespace app\common\model;

/**
 * 规格/属性(组)模型
 * Class Spec
 * @package app\common\model
 */
class Spec extends BaseModel
{
    protected $name = 'goods_model_spec';

    //关联规格值
    public function specValue(){
        return $this->hasMany('SpecValue','spec_id','spec_id');
    }
}
