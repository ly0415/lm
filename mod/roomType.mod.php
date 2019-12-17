<?php
/**
 * 业务类型模型
 * @author: luffy
 * @date  : 2018-01-19
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class roomTypeMod extends BaseMod{
    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct("room_type");
    }
    /**
     * 获取商品一级分类
     * @author wanyan
     * @date 2017-8-2
     */
    public function getParent(){
        $sql = "select `id`,`name`,`is_hot`,`parent_id`,`is_show`,`sort_order`,`add_time` from ".DB_PREFIX."goods_category where `parent_id` = '0'";
        $res =$this->querySql($sql);
        return $res;
    }

    //通过店铺商品来获取商品业务类型
    public function getRoomTypeId($storeGoodsId){
        $storeGoodsMod=&m('areaGood');
        $goodsData=$storeGoodsMod->getOne(array('cond'=>"`id` = '{$storeGoodsId}'",'fields'=>'room_id'));
        return $goodsData['room_id'];
    }
    /**
     * 获取一级业务类型
     * @author gao
     * @params $storeGoodsId              店铺商品id
     * @date   2019-01-21
     */
    public function getRoomParentId($storeGoodsId){
        $storeGoodsMod=&m('areaGood');
        $goodsData=$storeGoodsMod->getOne(array('cond'=>"`id` = '{$storeGoodsId}'",'fields'=>'room_id'));
        $roomParentData=$this->getOne(array('cond'=>"`id` = '{$goodsData['room_id']}'",'fields'=>'superior_id'));
        return $roomParentData['superior_id'];
    }


    /**
     * 获取业务类型
     * @author luffy
     * @params $langid              语言ID
     * @params $rtid
     * @params $is_goods_presence   是否要求获取存在商品的业务类型 （1 要求   0 不要求）
     * @date   2019-01-19
     */
    public function getBusinessType($langid, $storeid, $rtid, $is_goods_presence = 0){
        $where  = '    where  t.superior_id=' . $rtid . ' and  l.`lang_id`  = ' . $langid;
        $sql    = 'SELECT  t.`id`,l.`type_name`,t.`room_adv_img`  FROM  ' . DB_PREFIX . 'room_type AS t LEFT JOIN  '
            . DB_PREFIX . 'room_type_lang  AS  l  ON  t.`id` = l.`type_id`  '
            . $where . ' order by t.sort';
        $data   = $this->querySql($sql);

        //过滤无商品的业务类型
        if( $is_goods_presence ){
            foreach($data as $key => $value){
                $where = '  where s.store_id =' . $storeid . '   and rc.room_type_id = ' . $value['id'] . '  and s.mark=1 and s.is_on_sale =1 AND l.`lang_id` = ' . $langid;
                //所以子类的商品
                $rsql = 'SELECT s.id FROM  ' . DB_PREFIX . 'store_goods AS s LEFT JOIN  '
                    . DB_PREFIX . 'room_category AS rc on s.cat_id=rc.category_id LEFT JOIN '
                    . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id` ' . $where;
                $result = $this->querySql($rsql);
                if( empty($result) ){
                    unset($data[$key]);
                }
            }
        }
        return $data;
    }

    /**
     * 获取商品业务类型及辅助业务类型
     * @author zhangkx
     * @date 2019/4/8
     * @param $goodsId
     * @return mixed
     */
    public function getRoomType($goodsId){
        $goodsMod=&m('goods');
        $sql = 'select a.room_id, a.auxiliary_type,c.superior_id from '.DB_PREFIX.'goods as a 
                left join '.DB_PREFIX.'store_goods as b on a.goods_id=b.goods_id 
                left join '.DB_PREFIX.'room_type as c on a.room_id=c.id 
                where b.id='.$goodsId;
        $goodsData=$goodsMod->querySql($sql);
        if ($goodsData[0]['auxiliary_type'] != 0) {
            $result = $goodsData[0]['room_id'].','.$goodsData[0]['auxiliary_type'];
        } else {
            $result = $goodsData[0]['room_id'];
        }
        if ($goodsData[0]['superior_id']) {
            $result = $result.','.$goodsData[0]['superior_id'];
        }
        return $result;
    }
}
?>