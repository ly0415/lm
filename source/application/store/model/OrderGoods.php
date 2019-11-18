<?php

namespace app\store\model;

use app\common\model\OrderGoods as OrderGoodsModel;

/**
 * 订单商品模型
 * @author  luffy
 * @date    2019-07-15
 */
class OrderGoods extends OrderGoodsModel
{

    /**
     * 订单商品
     * @author  luffy
     * @date    2019-07-17
     */
    public function getOrderGoods($order_sn)
    {
        return $this->alias('c')
            ->field('c.goods_id, c.goods_image,c.goods_name,c.spec_key_name,c.goods_price,c.goods_num')
            ->where(['order_id'=>$order_sn])->select();
    }
}