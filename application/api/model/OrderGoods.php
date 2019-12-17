<?php

namespace app\api\model;

use app\common\model\OrderGoods as OrderGoodsModel;
use think\Db;

/**
 * 订单商品模型
 * Class OrderGoods
 * @package app\api\model
 */
class OrderGoods extends OrderGoodsModel
{
    /**
     * 获取秒杀商品已购数量
     * Created by PhpStorm.
     * Author: fup
     * $storeGoodsId -- int 店铺商品id
     * $spikeId -- int 秒杀活动id
     * $PromType -- int 商品类型
     * Date: 2019-08-14
     * Time: 14:05
     */
    public static function getGoodsRemain($storeGoodsId = 0,$spikeId = 0,$store_id=0,$PromType = 1,$where = []){
        return self::alias('a')
            ->join('order_'.$store_id.' b','a.order_id = b.order_sn','LEFT')
            ->where('a.prom_id','=',$spikeId)
            ->where('a.prom_type','=',$PromType)
            ->where('a.goods_id','=',$storeGoodsId)
            ->where('a.mark','=',1)
//            ->where('b.order_state','GT',10)
            ->where($where)
            ->sum('a.goods_num');
    }

    /**
     * 获取当天秒杀次数
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-30
     * Time: 16:50
     */
    public static function checkActivityTimes($userId,$PromType = 1){
        $start = strtotime(date('Y-m-d'));
        $end = strtotime(date("Y-m-d",strtotime("+1 day"))) - 1;
        $data = self::where('prom_type','=',$PromType)
            ->where('buyer_id','=',$userId)
            ->where('mark','=',1)
            ->where(['add_time'=>['BETWEEN',[$start,$end]]])
            ->order('add_time DESC')
            ->find();
        if($data && $order = Db::name('order_'.$data['store_id'])
                ->where('order_sn','=',$data['order_id'])
                ->where('order_state','>=',10)
                ->find()) return false;
                return true;
    }

    /**
     * 订单商品
     * @author  luffy
     * @date    2019-07-17
     */
    public function getOrderGoods($order_sn)
    {
        return $this->alias('c')
            ->field(' c.goods_id as store_goods_id,c.good_id as goods_id, c.goods_image,c.goods_name,c.spec_key,c.spec_key_name,c.goods_price,goods_pay_price,c.goods_num')
            ->where(['order_id'=>$order_sn])->select()->toArray();
    }

}
