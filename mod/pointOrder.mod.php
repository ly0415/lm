<?php
/**
* 积分订单
*/
if (!defined('IN_ECM')) { die('Forbidden'); }
class pointOrderMod extends BaseMod{
    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct("order_point");
    }

    public function isExist($order_sn){
        $rs = $this->getOne(array('cond'=>"`order_sn` = '{$order_sn}'",'fields'=>"`order_sn`,id,status,amount,buyer_id,point"));
        return $rs;
    }
}
?>