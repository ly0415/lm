<?php

namespace app\common\model;


/**
 * 商品属性
 * Class GoodsAttribute
 * @package app\common\model
 */
class GoodsAttribute extends BaseModel
{
    protected $name = 'goods_attribute';

    // 关联模型
    public function goodsModel(){
        return $this->belongsTo('GoodsModel','type_id','id');
    }

}
