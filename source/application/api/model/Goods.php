<?php

namespace app\api\model;

use app\common\model\Goods as GoodsModel;
use app\api\model\StoreGoods as StoreGoodsModel;

/**
 * 商品模型
 * @author  luffy
 * @date    2019-08-1
 */
class Goods extends GoodsModel
{

    /**
     * 获取店铺商品图片
     * @author  luffy
     * @date    2019-08-04
     */
    public static function getGoodsImage($store_goods_id){
        //获取原始商品ID
        $StoreGoodsModel    = new StoreGoodsModel();
        $store_goods_info   = $StoreGoodsModel::get(function($query)use($store_goods_id){
            $query->where('id', $store_goods_id);
        });
        //查询原始商品图片
        $goodsImage = self::where('goods_id', $store_goods_info->goods_id)->column('original_img');
        return $goodsImage[0];
    }

    /**
     * 获取原始商品配送费
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-12
     * Time: 16:56
     */
    public static function getDeliveryFee($goods_id = 0){
        return self::where('goods_id','=',$goods_id)
            ->value('delivery_fee');
    }

}
