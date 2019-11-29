<?php

namespace app\common\model;

/**
 * 组合商品模型
 * Class StoreGoodsJoint
 * @package app\common\model
 */
class StoreGoodsJoint extends BaseModel
{
    protected $name = 'store_goods_joint';
    protected $createTime = false;
    protected $updateTime = false;


    public function storeGoods(){
        return $this->belongsTo('StoreGoods','store_goods_ids','id');
    }

}
