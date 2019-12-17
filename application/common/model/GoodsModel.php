<?php

namespace app\common\model;

use think\Cache;

/**
 * 拼团商品分类模型
 * Class Category
 * @package app\common\model
 */
class GoodsModel extends BaseModel
{
    protected $name = 'goods_model';


    //规格
    public function goodsSpec(){
        return $this->hasMany('GoodsSpec','type_id','id');
    }


    //属性
    public function goodsAttribute(){
        return $this->hasMany('GoodsAttribute','type_id','id');
    }
}
