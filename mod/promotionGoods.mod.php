<?php
if (!defined('IN_ECM')) { die('Forbidden'); }
class promotionGoodsMod extends BaseMod{
    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct("promotion_goods");
    }

    public  function gePromotionGoods($promId){
        $promotionGoods=&m('promotionGoods');
        $sql="SELECT * FROM ".DB_PREFIX."promotion_goods WHERE `prom_id`={$promId}";
        $promotionGoodsData=$promotionGoods->querySql($sql);
        return $promotionGoodsData;
    }
}