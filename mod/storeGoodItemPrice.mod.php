<?php
/**
 * 店铺分类模块模型
 * @author: jh
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class storeGoodItemPriceMod extends BaseMod {
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct("store_goods_spec_price");
    }

    /**
     * 规格商品的价格
     * @author: jh
     * @date: 2017/6/21
     */
    public function getSpecPrice($goods_id,$goods_key){
        $rs = $this->getOne(array('cond' =>"store_goods_id = '{$goods_id}' and `key` ='{$goods_key}'",'fields'=>"price"));
        return $rs['price'];
    }


    /**
     * 规格商品的库存
     * @author: jh
     * @date: 2017/6/21
     */
    public function getSpecAccount($store_goods_id,$goods_key){
        $rs = $this->getOne(array('cond' =>"store_goods_id = '{$store_goods_id}' and `key` ='{$goods_key}'",'fields'=>"goods_storage"));
        return $rs['goods_storage'];
    }

    //获取店铺商品规格
    public function getSpec($stoeGoodsId){
        $sql = "select `key` as item_key from  " . DB_PREFIX . "store_goods_spec_price where store_goods_id=" . $stoeGoodsId;
        $keyData=$this->querySql($sql);
        return $keyData;
    }



    //获取商品规格 1是goods表商品规格  2是store_goods表商品规格
    public function get_spec($goodsId, $storeGoodsId,$langId, $type = 1) {
        $storeGoodMod = &m("storeGoodItemPrice");
        //商品规格 价钱 库存表 找出 所有 规格项id
        if ($type == 1) {
            $sql = "select `key` as item_key from  " . DB_PREFIX . "goods_spec_price where goods_id=" . $goodsId;
        } else {
            $sql = "select `key` as item_key from  " . DB_PREFIX . "store_goods_spec_price where store_goods_id=" . $storeGoodsId;
        }
        $keys = $storeGoodMod->querySql($sql);
        $filter_spec = array();
        if ($keys) {
            $key_str = "";
            foreach ($keys as $k => $v) {
                $key_str .= $v['item_key'] . "_";
            }
            $res_item = substr($key_str, 0, strlen($key_str) - 1);
            $keys = str_replace('_', ',', $res_item);
            $specImage = array();
            $sql3 = "select spec_image_id,src from " . DB_PREFIX . "goods_spec_image where goods_id=" . $goodsId; // 规格对应的 图片表， 例如颜色
            $img_list = $storeGoodMod->querySql($sql3);
            foreach ($img_list as $k => $v) {
                $specImage[$v['spec_image_id']] = $v['src'];
            }
            $sql4 = "SELECT a.`order`,b.*,al.spec_name,bl.`item_name`  FROM " . DB_PREFIX . "goods_spec AS a
                     INNER JOIN " . DB_PREFIX . "goods_spec_item AS b ON a.id = b.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_lang AS al ON a.id=al.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_item_lang as bl ON b.id=bl.item_id
                     WHERE b.id IN($keys) and al.lang_id=" .$langId. " and bl.lang_id=" . $langId . " ORDER BY b.id";
            $filter_spec2 = $storeGoodMod->querySql($sql4);
            foreach ($filter_spec2 as $key => $val) {
                $filter_spec[$val['spec_name']][] = array(
                    'item_id' => $val['id'],
                    'item' => $val['item_name'],
                    'src' => $specImage[$val['id']],
                );
            }
        }
        return $filter_spec;
    }

    //获取商品规格 1是goods表商品规格  2是store_goods表商品规格
    public function get_relattion_spec($goodsId, $storeGoodsId,$langId, $type = 1) {
        $storeGoodMod = &m("storeGoodItemPrice");
        //商品规格 价钱 库存表 找出 所有 规格项id
        if ($type == 1) {
            $sql = "select `key` as item_key from  " . DB_PREFIX . "goods_spec_price where goods_id=" . $goodsId;
        } else {
            $sql = "select `key` as item_key from  " . DB_PREFIX . "store_goods_spec_price where store_goods_id=" . $storeGoodsId;
        }
        $keys = $storeGoodMod->querySql($sql);
        $filter_spec = array();
        if ($keys) {
            $key_str = "";
            foreach ($keys as $k => $v) {
                $key_str .= $v['item_key'] . "_";
            }
            $res_item = substr($key_str, 0, strlen($key_str) - 1);
            $keys = str_replace('_', ',', $res_item);
            $specImage = array();
            $sql3 = "select spec_image_id,src from " . DB_PREFIX . "goods_spec_image where goods_id=" . $goodsId; // 规格对应的 图片表， 例如颜色
            $img_list = $storeGoodMod->querySql($sql3);
            foreach ($img_list as $k => $v) {
                $specImage[$v['spec_image_id']] = $v['src'];
            }
            $sql4 = "SELECT a.`order`,b.*,al.spec_name,bl.`item_name`  FROM " . DB_PREFIX . "goods_spec AS a
                     INNER JOIN " . DB_PREFIX . "goods_spec_item AS b ON a.id = b.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_lang AS al ON a.id=al.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_item_lang as bl ON b.id=bl.item_id
                     WHERE b.id IN($keys) and al.lang_id=" .$langId. " and bl.lang_id=" . $langId . " ORDER BY b.id";
            $filter_spec2 = $storeGoodMod->querySql($sql4);
            foreach ($filter_spec2 as $key => $val) {
                $filter_spec[$key]['spec_name'] = $val['spec_name'];
                $filter_spec[$key]['spec_data']=  array(
                    'item_id' => $val['id'],
                    'item' => $val['item_name'],
                );
            }
        }
        $result=array();
        foreach ($filter_spec as $k => $v) {
            $result[$v['spec_name']]['spec_name']=$v['spec_name'];
            if($k==0){
                $result[$v['spec_name']]['mark']=0;
            }else{
                $result[$v['spec_name']]['mark']=0;
            }
            $result[$v['spec_name']]['spec_data'][]=$v['spec_data'];
        }
        $result=$this->toIndexArr($result);
        return $result;
    }

    //索引数组
    public function getSpecName($storeGoodsId){
        $storeGoodMod = &m("storeGoodItemPrice");
        $spec_data = $storeGoodMod->getData(array('cond' => "store_goods_id=" . $storeGoodsId));
        foreach ($spec_data as $k => $v) {
            $spec_arr[$v['key']] = $v;
        }
        $spec_arr=json_encode($spec_arr);
        return $spec_arr;
    }
    //关联数组
    public function getRelationSpecName($storeGoodsId){
        $storeGoodMod = &m("storeGoodItemPrice");
        $spec_data = $storeGoodMod->getData(array('cond' => "store_goods_id=" . $storeGoodsId));
        foreach ($spec_data as $k => $v) {
            $spec_arr[] = $v;
        }

        return $spec_arr;
    }

    //排序
    function toIndexArr($arr){
        $i=0;
        foreach($arr as $key => $value){
            $newArr[$i] = $value;
            $i++;
        }
        return $newArr;
    }

}
?>