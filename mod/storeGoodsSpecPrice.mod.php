<?php
if (!defined('IN_ECM')) { die('Forbidden');}
class storeGoodsSpecPriceMod extends BaseMod{
    public function __construct()
    {
        parent::__construct('store_goods_spec_price');
    }

    /**
     * 求库存和
     * @author tangp
     * @date   2018-09-12
     * @return boolean
     */
    public function sumStorage()
    {
        $storeMod = &m('store');
        $storeGoodsSpecPriceMod = &m('storeGoodsSpecPrice');
        $storeGoodsMod = &m('storeGoods');
        //查出所有的店铺
        $sql = "select * from bs_store";
        $res = $storeMod->querySql($sql);
        //根据店铺id查出每个店铺所有的商品
        foreach ($res as $key => $value){
            $sql2 = "select * from bs_store_goods where store_id=".$value['id'];
            $data = $storeGoodsMod->querySql($sql2);
            //根据店铺商品的id查询对应的规格库存和
            foreach ($data as $k => $v){
                $sql3 = "select sum(goods_storage) from bs_store_goods_spec_price where store_goods_id=".$v['id'];
                $sum = $storeGoodsSpecPriceMod->querySql($sql3);
                if(isset($sum[0]["sum(goods_storage)"])){
                    $total = $sum[0]["sum(goods_storage)"];
                }else{
                    $total = $v['goods_storage'];
                }
                $data1 = array(
                    'goods_storage' => $total
                );
                $result = $storeGoodsMod->doEdit($v['id'],$data1);
            }
        }

    }
}