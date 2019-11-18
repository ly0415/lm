<?php
/**
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class OrderGoodsMod extends BaseMod
{
    public function __construct()
    {
        parent::__construct("order_goods");
    }


    public function  getActivityOrderNum($source, $activityId, $storeGoodsId, $buyerId)
    {
        if (!empty($buyerId)) {
            $sql = "select sum(og.goods_num) as total from " . DB_PREFIX . 'order  as o  left join ' . DB_PREFIX . 'order_goods as og ON og.order_id = o.order_sn
            where og.prom_type=' . $source . ' and og.prom_id=' . $activityId . ' and og.goods_id=' . $storeGoodsId . ' and o.mark=1 and o.order_state >=20' . ' and og.buyer_id=' . $buyerId;
            $activityOrderData = $this->querySql($sql);
        } else {
            $sql = "select sum(og.goods_num) as total from " . DB_PREFIX . 'order  as o  left join ' . DB_PREFIX . 'order_goods as og ON og.order_id = o.order_sn
            where og.prom_type=' . $source . ' and og.prom_id=' . $activityId . ' and og.goods_id=' . $storeGoodsId . ' and o.mark=1 and o.order_state >=20';
            $activityOrderData = $this->querySql($sql);
        }
        return $activityOrderData[0]['total'];
    }

    /**
     * 通过order_sn获取订单对应商品的业务类型及其金额
     */
    public function getRoomtypeidByOrdersn($order_sn)
    {
        $sql = 'select a.member_goods_price,b.room_id,c.auxiliary_type from ' . DB_PREFIX . 'cart as a 
            left join ' . DB_PREFIX . 'store_goods as b on a.goods_id = b.id 
            left join ' . DB_PREFIX . 'goods as c on b.goods_id = c.goods_id ' .
            " where a.uniquecode= '{$order_sn}' ";
        $data = $this->querySql($sql);
        $res = array();
        foreach ($data as $v) {
            $temp['room_type_id'] = $v['room_id'];
            if ($v['auxiliary_type']) {
                if (strpos($v['auxiliary_type'], ',')) {
                    $auxiliary_type = explode(',', $v['auxiliary_type']);
                    foreach ($auxiliary_type as $key => $value) {
                        $temp['room_type_id'] .= ','.$value;
                    }
                } else {
                    $temp['room_type_id'] .= ','.$v['auxiliary_type'];
                }
            }
            $temp['money'] = $v['member_goods_price'];
            $res[] = $temp;
        }
        return $res;
    }


    /**
     * 通过订单号获取订单商品
     * @author gao
     * @date 2019-03-20
     */
    public function getOrderGoods($orderSn)
    {
        $cond= array(
           "cond" => " `order_id` = '{$orderSn}'"
        );
       $data = $this->getData($cond);
       return $data;
    }







}