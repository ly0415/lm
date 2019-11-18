<?php

namespace app\api\model;

use app\common\model\StoreGoodsSpecPrice as StoreGoodsSpecPriceModel;

/**
 * 商品规格
 * @author  fup
 * @date    2019-08-14
 */
class StoreGoodsSpecPrice extends StoreGoodsSpecPriceModel
{
    /**
     * 获取规格信息
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-09
     * Time: 14:20
     */
    public function getDetailByBarCode($bcode,$store_id){
        $data = $this->alias('a')
            ->field('a.*,b.id bid,b.goods_name,b.is_on_sale')
            ->join('store_goods b','a.store_goods_id = b.id','LEFT')
            ->where('a.bar_code','=',$bcode)
            ->where('a.mark','=',1)
            ->where('b.store_id','=',$store_id)
            ->where('b.mark','=',1)
            ->find();
        return $data;
    }

}
