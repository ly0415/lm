<?php

namespace app\common\model;

/**
 * 商品规格价格模型
 * Class GoodsImage
 * @package app\common\model
 */
class GoodsSpecItem extends BaseModel
{
    protected $name = 'goods_spec_item';
    protected $updateTime = false;

    /**
     * 关联商品
     * @return \think\model\relation\BelongsTo
     */
    public function spec()
    {
        return $this->belongsTo('StoreGoods', 'goods_id', 'id')->bind(['goods_name','original_img']);
    }
}
