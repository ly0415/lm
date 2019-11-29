<?php

namespace app\common\model;
/**
 * 拼团商品分类模型
 * Class Category
 * @package app\common\model
 */
class GoodsBrand extends BaseModel
{
    protected $name = 'goods_brand';

    /**
     * 分类图片
     * @return \think\model\relation\HasOne
     */
    public function logo()
    {
        return $this->hasOne('uploadFile', 'file_id', 'logo');
    }

}
