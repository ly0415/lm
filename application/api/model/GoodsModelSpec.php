<?php

namespace app\api\model;

use app\common\model\GoodsModelSpec as GoodsModelSpecModel;
use think\Config;
use think\Db;
/**
 * 商品规格模型
 * @author  luffy
 * @date    2019-08-4
 */
class GoodsModelSpec extends GoodsModelSpecModel{

    /**
     * 获取店铺商品规格
     * @author  luffy
     * @date    2019-08-04
     */
    public static function getGoodsSpecName($spec_key){
        //条件组装
       $spec_key = explode('_', $spec_key);
       $spec_key = implode(',', $spec_key);
        //此处暂时用老表
        $prefix = Config::get('database.prefix');
        $result = Db::table( $prefix.'goods_spec_item_lang')
            ->field("group_concat(item_name separator ':') item_name")
            ->where(['item_id'=>['in',$spec_key],'lang_id'=>29])
            ->find();
        return $result['item_name'];
    }

    /**
     * 获取店铺商品规格（后期替换掉）
     * @author  luffy
     * @date    2019-08-05
     */
//    public static function getStorePointSite($store_id){
//        //此处暂时用老表
//        $prefix = Config::get('database.prefix');
//        $result = Db::table( $prefix.'store_point_site')
//            ->field("point_price")
//            ->where(['store_id'=>$store_id])
//            ->find();
//        return $result['point_price'];
//    }

}
