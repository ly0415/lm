<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-05
 * Time: 下午 3:12
 */

namespace app\ipad\model;


class StoreGoods extends BaseModel
{

    /**
     * 关联商品分类表
     * @return \think\model\relation\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('Category','cat_id','category_id');
    }
    /**
     * 关联商品规格表
     * @return \think\model\relation\HasMany
     */
    public function sku()
    {
        return $this->hasMany('GoodsSku','goods_id','goods_id')->order(['goods_sku_id' => 'asc']);
    }

    /**
     * 关联商品图片表
     * @return \think\model\relation\HasMany
     */
    public function image()
    {
        return $this->hasMany('GoodsImage','goods_id','goods_id')->order(['id' => 'asc']);
    }

    /**
     * 获取商品列表
     * @param int $status
     * @param int $category_id
     * @param string $search
     * @param string $sortType
     * @param bool $sortPrice
     * @param int $listRows
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($store_id){
        // 执行查询
        $list = $this
            ->with(['category', 'image.file', 'sku'])
            ->where('is_delete', '=', 0)
            ->where('is_on_sale','=',1)
            ->where('store_id','=',$store_id)
            ->order('sort')
            ->find();
        return $list;
    }
}