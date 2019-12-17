<?php

namespace app\common\model;

/**
 * 商品图片模型
 * Class GoodsImage
 * @package app\common\model
 */
class ActivityGoods extends BaseModel
{
    protected $name = 'activity_goods';
    protected $updateTime = false;

    /**
     * 关联商品
     * @return \think\model\relation\BelongsTo
     */
    public function goods()
    {
        return $this->belongsTo('StoreGoods', 'goods_id', 'id')->bind(['goods_name','original_img']);
    }
    /**
     * 关联商品图片表
     * @return \think\model\relation\HasMany
     */
    public function image()
    {
        return $this->hasMany('GoodsImage','goods_id','goods_id');
    }
    /**
     * 关联文件库
     * @return \think\model\relation\BelongsTo
     */
    public function file()
    {
        return $this->belongsTo('UploadFile', 'image_id', 'file_id')
            ->bind(['file_path', 'file_name', 'file_url']);
    }
}
