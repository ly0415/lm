<?php

namespace app\store\model;

use app\common\model\StoreGoodsSpecPrice as StoreGoodsSpecPriceModel;

/**
 * 商品规格关系模型
 * Class GoodsSpecRel
 * @package app\store\model
 */
class StoreGoodsSpecPrice extends StoreGoodsSpecPriceModel
{

    /**
     * 获取规格信息
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-29
     * Time: 14:07
     */
    public function detail($id){
        $model = new static;
        return $model::get($id,'storeGoods');
    }


    /**
     * 获取规格信息
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-09
     * Time: 14:20
     */
    public function getDetailByBarCode($bcode){
        $data = $this->alias('a')
            ->field('a.*,b.id bid,b.goods_name,b.is_on_sale')
            ->join('store_goods b','a.store_goods_id = b.id','LEFT')
            ->where('a.bar_code','=',$bcode)
            ->where('a.mark','=',1)
            ->where('b.store_id','=',STORE_ID)
            ->find();
        return $data;
//        $model = new static;
//        return $model::get(['bar_code' => $bcode,'mark'=>1],'storeGoods');
    }


    /**
     * 移除指定商品的所有规格价格
     * @param $id
     * @return int
     */
    public function remove($store_goods_id)
    {

        return $this->where('store_goods_id','=', $store_goods_id)->delete();
    }

    /**
     * 添加规格价格
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-20
     * Time: 20:05
     */
    public function add($store_goods_id,$spec_price){
        $data = array_map(function ($s)use($store_goods_id){
            $s['store_goods_id'] = $store_goods_id;
            return $s;
        },$spec_price);
        return $this->allowField(true)->saveAll($data);
    }




}
