<?php

namespace app\api\model;

use app\common\model\Business;
use app\common\model\Store      as StoreModel;
use app\common\model\StoreGoods as StoreGoodsModel;
use app\store\model\Store       as StoreStoreModel;
use app\common\model\GoodsSpec  as GoodsSpecModel;
use app\store\model\StoreGoods  as StoreStoreGoods;

/**
 * 用户收货地址模型
 * Class UserAddress
 * @package app\common\model
 */
class StoreGoods extends StoreGoodsModel
{
    /**
     * 通过id获取原始商品id
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-19
     * Time: 21:31
     */
    public static function getGoodsIdById($id = 0){
        return self::where('id','=',$id)
            ->value('goods_id');
    }

    /**
     * 获取商品配送费
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-12
     * Time: 16:56
     */
    public static function getDeliveryFee($id = 0){
        return self::where('id','=',$id)
            ->value('delivery_fee');
    }

    /**
     * 获取门店商品
     * @author  luffy
     * @date    2019-08-04
     */
    public function getStoreGoods($store_id, $rtid, $type, $page, $search_name, $type_id){

        $StoreModel = new StoreModel;

        //总仓直配、海外  获得总站商品 ly 19/11/21
        if($type == 3 || $type == 4){
            $store_id   = StoreStoreModel::getAdminStoreId();
            $store_info = $StoreModel::get($store_id);
        }else{
            $store_info = $StoreModel::get($store_id);
        }
        //根据一级业务类型获取有商品的二级业务类型
        $business_tree = Business::getCacheTree();
        if(isset($business_tree[$rtid])){
            $business   = $business_tree[$rtid]['child'];
            foreach ($business as $k => $v){
                $result = $this->alias('a')
                    ->join('goods_auxiliary_class b', 'a.goods_id = b.goods_id')
                    ->where(['b.business_id'=>$v['id'], 'a.mark' => 1, 'a.is_on_sale'=>1, 'a.store_id'=>$store_id, 'a.attributes'=>['like', "%$type%"]])
                    ->count();
                if($result == 0){
                    unset($business[$k]);
                }
            }
        }
        $where = [
            'a.mark' => 1, 'a.is_on_sale'=>1, 'a.store_id'=>$store_id
        ];
        if(!empty($search_name)){
            $where['a.goods_name']  = [ 'like', "%$search_name%"];
        }
        //配送方式
        $where['a.attributes']      = [ 'like', "%$type%"];
        //根据二级业务类型查找数据，囊括辅助分类
        if(empty($type_id) && $business){
            $type_id = current($business)['id'];
        }
        $this->field('b.business_id');
        $this->join('goods_auxiliary_class b', 'a.goods_id = b.goods_id');
        $this->where(['b.business_id' => $type_id]);

        if($store_id == 98){
            $this->whereOr('a.room_id', '=', $rtid);
        }

        //获取基础商品列表
        $new_data = [];
        $list = $this->alias('a')
            ->field('a.id,a.goods_id,a.cat_id,a.store_id,a.shop_price,a.market_price,a.goods_name,a.original_img,a.goods_storage,a.attributes,a.room_id as room_type_id,a.delivery_fee')
            ->where($where)
            ->limit(($page-1) * 20,20)
            ->order(['a.sort'=>'ASC', 'a.id'=>'DESC'])
            ->select()->toArray();
        //数据处理
        foreach ($list as $k => $v){
            //商品三个价格
            $v['sale_price']                = number_format($v['market_price'], 2);                                         //零售价
            $v['shop_price']                = number_format($v['shop_price'] * $store_info['store_discount'], 2); //折扣价
            $v['member_price']              = number_format($this->getPointAccount(), 2);                                   //会员返现
            $new_data[$v['id']]['basic']    = $v;
        }
        return ['goods'=>$new_data, 'room'=>$business, 'type_id' => $type_id];
    }

    /**
     * 睿积分抵扣金额
     * @author  luffy
     * @date    2019-11-19
     */
    public function  getPointAccount(){
        //得到门店折扣金额

        //得到个人折扣金额

        return 0;
    }

    /**
     * 获取门店商品规格
     * @author  luffy
     * @date    2019-11-19
     */
    public function  getStoreGoodsSpecData($store_goods_id){
        $resule = $first_key = [];
        //获取网格规格信息
        $GoodsSpecModel             = new GoodsSpecModel;
        $aaaModel                   = new StoreStoreGoods;
        $StoreGoodsSpecPriceModel   = new StoreGoodsSpecPrice;
        $spec_arr                   = $aaaModel->getPackageSpecData($store_goods_id);
        if(!empty($spec_arr)){
            foreach($spec_arr as $key => $value){
                $spec_info              = $GoodsSpecModel::get($key);
                $resule[$key]['_key']   = $spec_info['name'];
                foreach ($value as $k => $v){
                    //得到规格值名称
                    $spec_item_info     = GoodsSpecItem::get($v);
                    $resule[$key]['_value'][] = [
                        $v,
                        $spec_item_info['item_names'],
                    ];
                }
                $first_key[]  = $value[0];
            }
            sort($first_key);
            $spec_price_info        = $StoreGoodsSpecPriceModel->where(['store_goods_id'=>$store_goods_id, 'key'=>implode('_', $first_key), 'mark'=>1])->find();
        }
        return ['spec' => array_values($resule), 'price'=>$spec_price_info['price'], 'stock'=>$spec_price_info['goods_storage'], 'first_key'=>$first_key];
    }

    /**
     * 获取门店商品规格对应规格
     * @author  luffy
     * @date    2019-11-19
     */
    public function  getStoreGoodsSpecPrice($store_goods_id, $spec_str){
        $StoreGoodsSpecPriceModel   = new StoreGoodsSpecPrice;
        $spec_price_info            = $StoreGoodsSpecPriceModel->field('price,goods_storage')->where(['store_goods_id'=>$store_goods_id, 'key'=>$spec_str, 'mark'=>1])->find();
        return  ['price' => $spec_price_info['price'], 'stock' => $spec_price_info['goods_storage']];
    }
}