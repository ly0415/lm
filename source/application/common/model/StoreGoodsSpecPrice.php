<?php

namespace app\common\model;

use think\Db;

/**
 * 商品规格关系模型
 * Class StoreGoodsSpecPrice
 * @package app\common\model
 */
class StoreGoodsSpecPrice extends BaseModel
{
    protected $name = 'store_goods_spec_price';
    protected $autoWriteTimestamp = false;


    //关联店铺商品
    public function storeGoods(){
        return $this->belongsTo('StoreGoods','store_goods_id','id');
    }

    /**
     * 根据店铺id获取商品规格
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-21
     * Time: 20:51
     */
    public static function getSpecKey($storeGoodsId = 0){
        $data =  self::field('key')
            ->where('store_goods_id','=',$storeGoodsId)
            ->select()->toArray();
        return (new static)->getKeyArray($data);
    }

    /**
     * 拼凑key_id
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-26
     * Time: 17:18
     */
    public function getKeyArray($specKey){
        $keyIds = array();
        foreach ($specKey as $v) {
            $keyIds = array_merge($keyIds, explode('_', $v['key']));
        }
        return array_unique($keyIds);
    }


    /**
     * 根据商品的扣除方式获取规格对应的价格,
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-22
     * Time: 10:32
     */
    public static function getSpecPriceStock($store_goods_id = 0,$spec = []){

        $storeGoods = StoreGoods::get($store_goods_id);
        /**
         * 1.同步扣除：有规格的情况下取对应规格对应店铺商品的价格，取对应总站商品的库存。
         * 无规格的情况下取对应总站商品的库存，取对应店铺商品价格。
         *
         * 2.异步扣除：有规格的情况下取对应规格对应店铺商品的价格和库存，
         * 无规格的情况下取对应店铺商品的库存和价格。
         */
        //同步扣除
        $data = [];
        if($storeGoods && $storeGoods['deduction'] == 1){
            if(!StoreGoods::where('goods_id','=',$storeGoods['goods_id'])
                ->where('store_id','=',\app\store\model\Store::getAdminStoreId())
                ->where('mark','=',1)
                ->value('goods_storage')){
                return $data;
            }
            //有规格情况
            if(!empty($spec)){
                $data['stock'] = StoreGoods::where('goods_id','=',$storeGoods['goods_id'])
                    ->where('store_id','=',\app\store\model\Store::getAdminStoreId())
                    ->where('mark','=',1)
                    ->value('goods_storage');
                $data['price'] = self::where('key','IN',$spec)
                    ->where('store_goods_id','=',$store_goods_id)
                    ->value('price');
            }else{
                //无规格情况
                $data['stock'] = StoreGoods::where('goods_id','=',$storeGoods['goods_id'])
                    ->where('store_id','=',\app\store\model\Store::getAdminStoreId())
                    ->where('mark','=',1)
                    ->value('goods_storage');
                $data['price'] = $storeGoods['shop_price'];
//                $data = StoreGoods::alias('a')
//                    ->field('a.shop_price price,b.goods_storage stock')
//                    ->join('store_goods b','a.goods_id = b.goods_id','LEFT')
//                    ->where('b.mark','=',1)
//                    ->where('a.id','=',$store_goods_id)
//                    ->where('b.store_id','=',\app\store\model\Store::getAdminStoreId())
//                    ->find();
            }

            //异步扣除
        }elseif ($storeGoods && $storeGoods['deduction'] == 2){
            if($storeGoods['goods_storage'] <= 0){
                return $data;
            }
            //有规格情况
            !empty($spec) && $data = self::field('goods_storage as stock,price')
                ->where('key','IN',$spec)
                ->where('store_goods_id','=',$store_goods_id)
                ->find();
            //无规格情况
            empty($spec) && $data = StoreGoods::getSpecPriceStock($store_goods_id);

        }

        return $data;

    }

}
