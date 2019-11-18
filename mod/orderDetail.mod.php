<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/23
 * Time: 17:20
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class OrderDetailMod extends BaseMod{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct("order_goods");

    }

    //通过订单号获取订单商品的业务
    public function getRoomTypeIds($orderSn){
        $roomTypeMod=&m('roomType');
        $sql="SELECT goods_id FROM ".DB_PREFIX."order_goods WHERE order_id=".$orderSn;
        $storeGoodsIdData=$this->querySql($sql);
        foreach($storeGoodsIdData as $k=>$v){
           $roomTypeIdData[]= $roomTypeMod->getRoomTypeId($v['goods_id']);
        }
        return $roomTypeIdData;
    }
}