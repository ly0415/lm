<?php
/**
 * 管理员模块模型
 * @author: jh
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class AreaGoodMod extends BaseMod {
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct("store_goods");
    }
    /*
     * 获取区域商品详情
     */
    public function getLangInfo($id,$lang_id,$store_id=null){
        $cond="id=".$id;
        if($store_id){
            $cond.=" and store_id=".$store_id;
        }
        $info=$this->getOne(array("cond"=>$cond));
        $sqlLang="select * from ".DB_PREFIX."goods_lang where goods_id=".$info['goods_id']." and lang_id=".$lang_id;
        $langInfo=$this->querySql($sqlLang);
        if($langInfo){
            $info['goods_name']=$langInfo[0]['goods_name'];
            $info['goods_remark']=$langInfo[0]['goods_remark'];
            $info['keywords']=$langInfo[0]['keywords'];
            $info['goods_content']=$langInfo[0]['goods_content'];
        }
        return $info;
    }

    public function getLangInfo1($id,$lang_id,$store_id=null){
        $cond="id=".$id;
        if($store_id){
            $cond.=" and store_id=".$store_id;
        }
        $info=$this->getOne(array("cond"=>$cond));
        $sqlLang="select  goods_name,shop_price , goods_storage,id,goods_id store_id ,shipping_price  from ".DB_PREFIX."goods_lang where goods_id=".$info['goods_id']." and lang_id=".$lang_id;
        $langInfo=$this->querySql($sqlLang);
        if($langInfo){
            $info['goods_name']=$langInfo[0]['goods_name'];
        }
        return $info;
    }

    /*
     * 获取区域商品对应语言列表
     * @author lee
     * @date 2017-10-25 16:12:51
     */
    public function getLangList($cond,$lang_id){
        $list=$this->getData($cond);
        foreach($list as $k=>$v){
            $infoSql="select * from ".DB_PREFIX."store_goods_lang where store_good_id=".$v['id']." and lang_id=".$lang_id;
            $langInfo=$this->querySql($infoSql);
            if($langInfo){
                $list[$k]['goods_name']=$langInfo[0]['goods_name'];
                $list[$k]['goods_remark']=$langInfo[0]['goods_remark'];
                $list[$k]['keywords']=$langInfo[0]['keywords'];
                $list[$k]['goods_content']=$langInfo[0]['goods_content'];
            }
        }
        return $list;
    }
    /**
     * 获取当前店铺已有商品
     * @author: wanyan
     * @date  : 2017-12-18
     */
    public function  getAreaGood($store_id){
        $query =array(
            'cond' => " `store_id` in ({$store_id}) and mark = '1' ",
            'fields' => "`goods_id`"
        );
        $rs = $this->getData($query);
        foreach($rs as  $k =>$v){
            $data[] = $v['goods_id'];
        }
        return  $data;
    }
    /**
     * 获取多个店铺没有的数据
     * @author: wanyan
     * @date  : 2017-12-19
     */
    public function getNuAreaGood($store_id,$language_id){
        $goodMod = &m('goods');
        $store_ids = explode(',',$store_id);

        foreach ($store_ids as $k=>$sd){
            $data =array();
            $query =array(
                'cond' => " `store_id` = {$sd} and mark = '1' ",
                'fields' => "`goods_id`"
            );
            $arr= $this->getData($query);
            if(count($arr) > 0) {
                foreach ($arr as $k1 => $v1) {
                    $data[] = $v1['goods_id'];
                }// 把当前店铺ID查询到商品放到一个数组里
                $params = implode(',', $data);
                $sql = "select g.`goods_id`  from " . DB_PREFIX . "goods as g left join " . DB_PREFIX . "goods_lang as gl on g.goods_id = gl.goods_id where g.goods_id not in ({$params}) and gl.lang_id =" . $language_id;
            }else{
                $sql = "select g.`goods_id`  from " . DB_PREFIX . "goods as g left join " . DB_PREFIX . "goods_lang as gl on g.goods_id = gl.goods_id where gl.lang_id =" . $language_id;
            }
            $result[] = $goodMod->querySql($sql);
        }

        foreach($result as $key => $val){
            foreach($val as $v2){
                $res[] = $v2['goods_id'];
            }
        }

         return implode(',',array_unique($res));


    }

    /**
     * 获取商品列表
     * @author: luffy
     * @date  : 2018-08-15
     */
    public function getGoodsList($cond) {
        extract($cond);
        $order = ' s.add_time desc ';
        $where = ' WHERE s.mark = 1 AND s.is_on_sale = 1 AND s.store_id = ' . $store_id . ' AND l.`lang_id` = ' . $lang_id;
        $limit          && $limit =  ' LIMIT '.$limit;
        $is_recom       && $where .= ' AND s.is_recom = '. $is_recom;

        if( $select_1 == 1 ){
            $order = ' order by s.goods_id desc ';
        } elseif ( $select_2 == 1 ) {    //优惠商品---和其他筛选没有任何关系
            $goodsList = $this->getSecondKillGood($store_id, $lang_id, $shorthand);
            return  $goodsList;
        }
        $sql = 'SELECT s.id,s.`cat_id`,s.`store_id`,l.`goods_name`,s.`shop_price`,s.`market_price`,s.is_free_shipping,slg.`original_img` FROM '
            . DB_PREFIX . 'store_goods AS s LEFT JOIN  '
            . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id` LEFT JOIN  '
            . DB_PREFIX . 'goods AS slg ON s.`goods_id` = slg.`goods_id`'
            . $where . ' ORDER BY ' .$order . $limit;
        $goodsList = $this->querySql($sql);

        //逻辑处理
        $storeMod = &m('store');
        $langObj = languageFun($shorthand);
        foreach ($goodsList as $key => $value) {
            //获取店铺折扣
            $store_info = $storeMod -> getRow($store_id);
            $goodsList[$key]['sale_price'] = number_format($value['shop_price'] * $store_info['store_discount'], 2);
            if( $is_recom ){
                ///是否包邮
                $goodsList[$key]['free_shipping']    = $this->isFreeShipping(array(
                    'a' => $value['is_free_shipping'],
                    'b' => $langObj->project->free_shipping,
                    'c' => $langObj->project->no_free_shipping
                ));
                $user_id=18088888888;
                if( $is_collection && $user_id ){   //需要获取收藏情况
                    //为你推荐的收藏商品
                    $sql_collection     = ' select * from ' . DB_PREFIX . 'user_collection where user_id = ' . $user_id . ' and store_id = '.$store_id ;
                    $collection_data    = $storeMod->querySql($sql_collection);
                    foreach ($collection_data as &$val) {
                        if ($val['store_good_id'] == $value['id']) {
                            $goodsList[$key]['is_collection'] = 1;
                        }
                    }
                }
            }
        }
        return $goodsList;
    }

    /**
     * 获取优惠的商品
     * @author: luffy
     * @date  : 2018-08-15
     */
    public function getSecondKillGood($store_id, $lang_id, $shorthand){
        $langObj = languageFun($shorthand);

        //秒杀商品
        $seckMod = &m('spikeActivity');
        $curtime = time();
        $where1 = ' WHERE s.store_id =' . $store_id . ' and ' . $curtime . ' > s.start_time and g.mark = 1 and g.is_on_sale = 1 AND c.lang_id = '.$lang_id;
        $sql1 = 'SELECT s.id as prom_id,s.name,s.o_price,s.price,c.goods_name,g.is_free_shipping,gl.original_img,g.id FROM  '
            . DB_PREFIX . 'spike_activity as s left join '
            . DB_PREFIX . 'store_goods as g on s.store_goods_id = g.id  left join '
            . DB_PREFIX . 'goods_lang as c on c.goods_id = g.goods_id  left join '
            . DB_PREFIX . 'goods as gl on g.goods_id = gl.goods_id ' . $where1;
        $spikeArr = $seckMod->querySql($sql1);
        //逻辑处理
        foreach ($spikeArr as $key => $value) {
//            $spikeArr[$key]['source']           = $langObj->project->second_kill;  //秒杀
            $spikeArr[$key]['source'] = 1;
            //是否包邮
            $spikeArr[$key]['free_shipping']    = $this->isFreeShipping(array(
                'a' => $value['is_free_shipping'],
                'b' => $langObj->project->free_shipping,
                'c' => $langObj->project->no_free_shipping
            ));
        }

//        //团购商品
//        $goodsByMod = &m('groupbuy');
//        $where2 = '  where  b.store_id = ' . $store_id . ' and  b.mark =1 and g.mark=1 and g.is_on_sale=1';
//        $sql2 = 'SELECT  b.id,b.goods_id,b.store_id,b.start_time,b.end_time,b.group_goods_price,b.virtual_num,l.original_img,b.goods_price,b.goods_name  FROM  '
//            . DB_PREFIX . 'goods_group_buy  AS b  LEFT JOIN  '
//            . DB_PREFIX . 'store_goods AS g ON b.`goods_id` = g.id  LEFT JOIN '
//            . DB_PREFIX . 'goods AS l ON g.`goods_id` = l.`goods_id` ' . $where2;
//        $data = $goodsByMod->querySql($sql2);
//
//        $where3 = 'WHERE  l.`lang_id` = ' . $this->langid . '  and  b.store_id =' . $this->storeid . '  AND b.is_end =1 AND b.mark = 1 and g.mark=1 and g.is_on_sale=1 ';
//        $sql3 = 'SELECT  b.id as cid,b.goods_id as id,b.store_id,b.end_time,b.group_goods_price as price,b.virtual_num,gsl.original_img,b.goods_price as o_price,l.`goods_name`,b.goods_spec_key as item_key  FROM  '
//            . DB_PREFIX . 'goods_group_buy   AS b  LEFT JOIN  '
//            . DB_PREFIX . 'store_goods AS g ON b.`goods_id` = g.id LEFT JOIN  '
//            . DB_PREFIX . 'goods_lang AS l ON g.`goods_id` = l.`goods_id`  LEFT JOIN  '
//            . DB_PREFIX . 'goods AS gsl ON g.`goods_id` = gsl.`goods_id` ' . $where3;
//        $groupByGoodArr = $goodsByMod->querySql($sql3);

        //促销商品
        $goodPromMod = &m('goodProm');
        // 获取正在进行或者未开始的促销活动
        $sql4 = " select pg.goods_key,ps.id as prom_id,ps.id,ps.prom_name as name,pg.goods_price as o_price,pg.discount_price as price,c.goods_name,s.is_free_shipping,sgl.original_img,s.id from "
            . DB_PREFIX . "promotion_sale as ps left join "
            . DB_PREFIX . "promotion_goods as pg on  ps.id = pg.prom_id left join "
            . DB_PREFIX . "store_goods as s on pg.goods_id = s.id  left join "
            . DB_PREFIX . 'goods_lang as c on c.goods_id = s.goods_id left join '
            . DB_PREFIX . "goods as sgl on s.goods_id = sgl.goods_id
            where c.lang_id = {$lang_id} AND ps.`store_id` = $store_id and ps.`status` in (1,2) and s.mark=1 and s.is_on_sale =1 and ps.`mark` =1 order by ps.status desc,ps.id desc";
        $promotionGoodsArr = $goodPromMod->querySql($sql4);
        //逻辑处理
        foreach ($promotionGoodsArr as $key => $value) {
//            $promotionGoodsArr[$key]['source']           = $langObj->project->sales_promotion;  //促销
            $promotionGoodsArr[$key]['source'] = 3;
            //是否包邮
            $promotionGoodsArr[$key]['free_shipping']    = $this->isFreeShipping(array(
                'a' => $value['is_free_shipping'],
                'b' => $langObj->project->free_shipping,
                'c' => $langObj->project->no_free_shipping
            ));
        }

        return  array_merge( $promotionGoodsArr, $spikeArr);
    }

    /**
     * 是否包邮
     * @author: luffy
     * @date  : 2018-08-16
     */
    public function isFreeShipping($cond){
        $result = '';
        extract($cond);
        if( $a == 1 ){
            $result = $b;
        } elseif( $a == 2 ){
            $result = $c;
        }
        return  $result;
    }

    //获取无规格商品库存
    public  function  getStorage($id){
        $rs = $this->getOne(array('cond' =>"id = '{$id}'",'fields'=>"goods_storage"));
        return $rs['goods_storage'];
    }

    //获取商品的规格


}

?>