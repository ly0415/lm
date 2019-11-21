<?php
/**
 * 商品列表接口控制器
 * @author: gao
 * @date: 2018-08-14
 */
class GoodsApp extends BasePhApp{
    private $goodsCommentMod;
    private $footPrintMod;

    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct();
        $this->goodsCommentMod = &m('goodsComment');
        $this->footPrintMod = &m('footprint');
    }

    /**
     * 析构函数
     */
    public function __destruct(){
    }

    /**
     * 更多推荐商品列表/限时优惠接口
     * @author: luffy
     * @date  : 2018-08-15
     */
    public function index(){
        $select_1 = !empty($_REQUEST['select_1']) ? intval($_REQUEST['select_1']) : 0; //新品
        $select_2 = !empty($_REQUEST['select_2']) ? intval($_REQUEST['select_2']) : 0; //优惠
        $select_3 = !empty($_REQUEST['select_3']) ? intval($_REQUEST['select_3']) : 0; //价格
        $select_4 = !empty($_REQUEST['select_4']) ? intval($_REQUEST['select_4']) : 0; //商品类型
        $select_5 = !empty($_REQUEST['select_5']) ? intval($_REQUEST['select_5']) : 0; //价格区间最小
        $select_6 = !empty($_REQUEST['select_6']) ? intval($_REQUEST['select_6']) : 99999999;  //价格区间最大


        $storeGoodsMod  = &m('areaGood');
        $goodsList      = $storeGoodsMod -> getGoodsList(array(
            'store_id'  =>  $this->store_id,
            'lang_id'   =>  $this->lang_id,
            'shorthand' =>  $this->shorthand,
            'is_recom'  =>  1,           //推荐
            'select_1'  =>  $select_1,
            'select_2'  =>  $select_2,
            'select_3'  =>  $select_3,
            'select_4'  =>  $select_4,
            'select_5'  =>  $select_5,
            'select_6'  =>  $select_6,
        ));

        if( $goodsList ) {
            $this->setData($goodsList, 1);
        }
    }

    /**
     * 限时优惠商品列表接口
     * @author: luffy
     * @date  : 2018-08-15
     */
    public function timeLimitGoods(){
        $storeGoodsMod  = &m('areaGood');
        $goodsList      = $storeGoodsMod -> getGoodsList(array(
            'store_id'  =>  $this->store_id,
            'lang_id'   =>  $this->lang_id,
            'shorthand' =>  $this->shorthand,
            'time_limit'=>  1,
        ));

        if( $goodsList ) {
            $this->setData($goodsList, 1);
        }
    }

    /**
     * 商品类型接口
     * @author gao
     * @date 2018/08/14
     */
    public function getFilter() {
        $goodsbandMod = &m('goodsBrand');
        $langData=array(
            $this->langData->project->height_price,
            $this->langData->project->low_price,
            $this->langData->project->goods_type,
            $this->langData->project->price_range,
            $this->langData->public->reset,
            $this->langData->public->complete,
            $this->langData->public->screen,
        );
        $where = '    where  l.`lang_id`  = ' . $this->lang_id;
        $bsql = 'SELECT  b.id,l.`brand_name`as bname  FROM  ' . DB_PREFIX . 'goods_brand AS b
                LEFT JOIN  ' . DB_PREFIX . 'goods_brand_lang AS l ON b.id=l.`brand_id`  ' . $where;
        $brandData = $goodsbandMod->querySql($bsql);
        $data=array('langData'=>$langData,'brandData'=>$brandData);
        if($brandData){
            $this->setData($data,'1','');
        }
    }




    /**
     * 二级业务页面接口
     * @author gao
     * @date 2018/08/15
     */
    public  function goodsList(){
        $storeGoodsMod = &m('areaGood');
        $rtid = !empty($_REQUEST['rtid']) ? $_REQUEST['rtid'] : 83;
        $langData=array(
            $this->langData->project->preferential_goods,
            $this->langData->project->hot_selling_goods,
            $this->langData->project->monthly_sale,
            $this->langData->project->select_spec,
        );
        $roomtypearr = $this->getgoodRoomTypearr($this->lang_id, $rtid);
        foreach($roomtypearr as $kk=>$vv){
            $roomId[]=$vv['id'];
        }
        $roomIds=implode(',',$roomId);
        $hotGoods=$this->getHotGoods($roomIds,$this->store_id);

        foreach ($roomtypearr as $key => $val) {
            $where = '  where   s.store_id =' . $this->store_id . '   and rc.room_type_id = ' . $val['id'] . '  and   s.mark=1   and   s.is_on_sale =1  AND l.`lang_id` = ' . $this->lang_id;
            //所以子类的商品
            $rsql = 'SELECT s.id,rc.sort,s.`goods_id`,s.`cat_id`,s.`store_id`,l.`goods_name`,l.`lang_id`,s.`shop_price`,s.`market_price`,s.`brand_id`,gl.`original_img`,s.is_free_shipping,s.add_time,l.goods_remark
                FROM  ' . DB_PREFIX . 'store_goods AS s LEFT JOIN  '
                . DB_PREFIX . 'room_category AS rc on s.cat_id=rc.category_id LEFT JOIN '
                . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id`  left join '
                . DB_PREFIX . 'goods as gl on s.goods_id = gl.goods_id ' . $where;
            $roomtypearr[$key]['goods'] =$storeGoodsMod->querySql($rsql);
        }
        foreach($roomtypearr as $k=>$v){
            foreach($v['goods'] as $k1=>$v1){
                //店铺商品打折
                $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $this->store_id;
                $store_arr = $storeGoodsMod->querySql($store_sql);
                $roomtypearr[$k]['goods'][$k1]['shop_price'] =number_format($v1['shop_price'] * $store_arr[0]['store_discount'],2);
                $oSql="SELECT rec_id,goods_num  FROM ".DB_PREFIX.'order_goods WHERE goods_id='.$v1['id']." and order_state in (20,30,40,50)";
                $oData=$storeGoodsMod->querySql($oSql);
                if(!empty($oData)){
                    $sum=0;
                    foreach($oData as $k2=>$v2){
                        $sum +=$v2['goods_num'];
                    }
                    $roomtypearr[$k]['goods'][$k1]['order_num']=$sum;
                }else{
                    $roomtypearr[$k]['goods'][$k1]['order_num']=0;
                }
                $goodsCommentMod = &m('goodsComment');
                $sql = 'select  sum(goods_rank) / count(goods_rank) as res,count(*) as num from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $v1['id'];
                $trance = $goodsCommentMod->querySql($sql);
                $roomtypearr[$k]['goods'][$k1]['rate'] = (int)$trance[0]['res'];
                $roomtypearr[$k]['goods'][$k1]['num'] = $trance[0]['num'];
            }
        }
        ;
        $activiData=$this->getACtivity($roomIds,$this->store_id);
        foreach($activiData as $key=>$val){
            $oSql="SELECT rec_id,goods_num FROM ".DB_PREFIX.'order_goods WHERE goods_id='.$val['gid']." and order_state in (20,30,40,50)";
            $oData=$storeGoodsMod->querySql($oSql);
            if(!empty($oData)){
                $num=0;
                foreach($oData as $k=>$v){
                    $num+=$v['goods_num'];
                }
                $activiData[$key]['order_num']=$num;
            }else{
                $activiData[$key]['order_num']=0;
            }
            $goodsCommentMod = &m('goodsComment');
            $sql = 'select  sum(goods_rank) / count(goods_rank) as res,count(*) as num from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $val['gid'];
            $trance = $goodsCommentMod->querySql($sql);
            $activiData[$key]['rate']=(int)$trance[0]['res'];
        }
        $data=array(
            'langData'=>   $langData,
            'activeData'=>$activiData,
            'roomtypData'=> $roomtypearr,
            'hotGoods'=>$hotGoods);
        if($data){
            $this->setData($data,'1','');
        }


    }


    /**
     * 2级业务类型
     */
    public function getgoodRoomTypearr($langid, $rtid) {
        $roomTypeMod = &m('roomType');
        if (!empty($langid)) {
            $where = '    where  t.superior_id=' . $rtid . ' and  l.`lang_id`  = ' . $langid;
        } else {
            $where = '    where  t.superior_id=' . $rtid . ' and  l.`lang_id`  = ' . $this->mrlangid;
        }
        $sql = 'SELECT  t.`id`,l.`type_name`,t.`room_adv_img`  FROM  ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN  ' . DB_PREFIX . 'room_type_lang  AS  l  ON  t.`id` = l.`type_id`  ' . $where . ' order by t.sort';
        $data = $roomTypeMod->querySql($sql);
        return $data;
    }


    //热销商品
    public function getHotGoods($roomIds,$storeid){
        $storeGoodsMod = &m('areaGood');
        $where = '  where     s.store_id =' . $storeid . '   and rc.room_type_id in ('.$roomIds.')  and   s.mark=1    and   s.is_on_sale =1  AND l.`lang_id` = ' . $this->lang_id ;
        //所以子类的商品
        $hsql = 'SELECT s.id,s.`goods_id`,l.`goods_name`,l.`lang_id`,s.`shop_price`,s.`market_price`,gl.`original_img`
                FROM  ' . DB_PREFIX . 'store_goods AS s LEFT JOIN  '
            . DB_PREFIX . 'room_category AS rc on s.cat_id=rc.category_id LEFT JOIN '
            . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id`  left join '
            . DB_PREFIX . 'goods as gl on s.goods_id = gl.goods_id' . $where;
        $hotData=$storeGoodsMod->querySql($hsql);

        foreach($hotData as $k=>$v){
            $oSql="SELECT rec_id,goods_num  FROM ".DB_PREFIX.'order_goods WHERE goods_id='.$v['id']." and order_state in (20,30,40,50)";
            $oData=$storeGoodsMod->querySql($oSql);
            if(!empty($oData)){
                $sum=0;
                foreach($oData as $k1=>$v1){
                    $sum +=$v1['goods_num'];
                }
                $hotData[$k]['goods_num']=$sum;
            }else{
                $hotData[$k]['goods_num']=0;
            }
            $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $storeid;
            $store_arr = $storeGoodsMod->querySql($store_sql);
            $hotData[$k]['shop_price'] = number_format($v['shop_price'] * $store_arr[0]['store_discount'],2);
        }
        $sort = array(
            'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
            'field' => 'goods_num', //排序字段
        );
        $arrSort = array();
        foreach ($hotData AS $uniqid => $row) {
            foreach ($row AS $key => $value) {
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if ($sort['direction']) {
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $hotData);
        }
        $hotData=array_slice($hotData,0,4);
        return $hotData;
    }


    //活动商品
    public function getACtivity($roomIds,$storeid){
        //秒杀商品
        $seckMod = &m('spikeActivity');
        $goodsByMod = &m('groupbuy');
        $goodPromMod = &m('goodProm');
        $storeGoodsMod = &m('areaGood');
        $curtime = time();
        $today = strtotime(date('Y-m-d', time()));
        $now = $curtime - $today;
        $where1 = 'GROUP BY s.id   HAVING s.store_id =' . $storeid . '  and  ' . $curtime . ' > stime  AND   etime > ' . $curtime .' and rc.room_type_id in ('.$roomIds.') and g.is_on_sale =1 and g.mark=1 ' ;
        $sql1 = 'SELECT  s.id as cid,s.`name`,s.start_time,s.end_time,s.start_our,s.end_our,s.store_id,s.store_goods_id as id,gl.original_img,s.content,s.item_name,s.item_key,s.discount,s.o_price,s.price,s.goods_num,g.is_free_shipping,(s.start_time+s.start_our) as stime,(s.end_time+s.end_our) as etime,l.goods_remark,gl.goods_id as good_id,g.id as gid,rc.room_type_id,g.is_on_sale,g.mark FROM  '
            . DB_PREFIX . 'spike_activity as s left join '
            . DB_PREFIX . 'store_goods as g on  s.store_goods_id = g.id  LEFT JOIN  '
            . DB_PREFIX . 'room_category AS rc on g.cat_id=rc.category_id LEFT JOIN '
            . DB_PREFIX . 'goods AS gl ON g.`goods_id` = gl.`goods_id` LEFT JOIN  bs_goods_lang AS l ON g.`goods_id` = l.`goods_id`  '. $where1;
        $spikeArr = $seckMod->querySql($sql1);
        foreach($spikeArr as $key=>$val){
            $spikeArr[$key]['source']=1;
            $child_info = $storeGoodsMod->getLangInfo($val['id'], $this->lang_id);
            if ($child_info) {
                $k_name = $child_info['goods_name'];
                $spikeArr[$key]['goods_name'] = $k_name;
            }

        }

        //团购商品
        $where3 = 'WHERE  l.`lang_id` = ' . $this->lang_id . '  and  b.store_id =' . $this->store_id . '  AND b.is_end =1 AND b.mark = 1  and rc.room_type_id in ('.$roomIds.') and g.is_on_sale=1 and g.mark=1 ';
        $sql3 = 'SELECT b.title as name, b.id as cid,b.goods_id as id,b.store_id,b.end_time,b.group_goods_price as price,b.virtual_num,gl.original_img,b.goods_price as o_price,l.goods_name,b.goods_spec_key as item_key,gl.goods_id as good_id,g.id as gid
                FROM  bs_goods_group_buy  AS b  LEFT JOIN   bs_store_goods AS g ON b.`goods_id` = g.id
                LEFT JOIN  bs_goods_lang AS l ON g.`goods_id` = l.`goods_id`   LEFT JOIN  '
            . DB_PREFIX . 'room_category AS rc on g.cat_id=rc.category_id LEFT JOIN '
            . DB_PREFIX . 'goods AS gl ON g.`goods_id` = gl.`goods_id`'. $where3;
        $groupByGoodArr = $goodsByMod->querySql($sql3);
        foreach($groupByGoodArr as $key=>$val){
            $groupByGoodArr[$key]['source']=2;
        }



        $this->checkOver();
        // 获取正在进行或者未开始的促销活动
        $sql4 = " select  ps.prom_name as name, ps.id as cid,ps.*,pg.goods_id as id,pg.goods_key as item_key,pg.goods_key_name,l.goods_name,gl.original_img,pg.goods_price as o_price,pg.discount_price as price,s.is_free_shipping,gl.goods_id as good_id,s.id as gid,pg.limit_amount,l.goods_remark  from "
            . DB_PREFIX . "promotion_sale as ps left join "
            . DB_PREFIX . "promotion_goods as pg on  ps.id = pg.prom_id left join "
            . DB_PREFIX . "store_goods as s on pg.goods_id = s.id  LEFT JOIN "
            . DB_PREFIX . "goods AS gl ON s.`goods_id` = gl.`goods_id` LEFT JOIN  bs_goods_lang AS l ON s.`goods_id` = l.`goods_id` LEFT JOIN "
            . DB_PREFIX . "room_category AS rc on s.cat_id=rc.category_id
             where ps.`store_id` = $this->store_id and ps.`status` in (1,2) and ps.`mark` =1  and rc.room_type_id in ($roomIds) and s.is_on_sale = 1 and s.mark = 1  and l.lang_id = ".$this->lang_id."  order by ps.status desc,ps.id desc";
        $promotionGoodsArr = $goodPromMod->querySql($sql4);
        foreach($promotionGoodsArr as $key=>$val){
            $promotionGoodsArr[$key]['source']=3;
        }


        $res = array();
        $res = array_merge($spikeArr, $groupByGoodArr, $promotionGoodsArr);

        return $res;
    }


    //检查促销商品是否过期
    public function checkOver() {
        $goodPromMod = &m('goodProm');
        $sql = "select * from " . DB_PREFIX . "promotion_sale where mark =1";
        $rs = $goodPromMod->querySql($sql);
        foreach ($rs as $k => $v) {
            if ($v['start_time'] > time()) {
                $vstatus = 1;
            } elseif ($v['start_time'] <= time() && $v['end_time'] >= time()) {
                $vstatus = 2;
            } elseif ($v['end_time'] < time()) {
                $vstatus = 3;
            }
            $goodPromMod->doEdit($v['id'], array('status' => $vstatus));
        }
    }

    /**
     * 选择规格页面接口
     * @author gao
     * @date 2018/08/21
     */
    public  function  getSpec(){
        $storeGoods = &m('areaGood');
        $id=!empty($_REQUEST['id']) ? $_REQUEST['id'] : 124;//区域商品id
        $goods_id=!empty($_REQUEST['goods_id']) ? $_REQUEST['goods_id'] : 46;//商品id
        $store_id=!empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $lang=$this->lang_id;
        $source=!empty($_REQUEST['source']) ? $_REQUEST['source'] : 3;//活动类型
        $cid=!empty($_REQUEST['cid'])? $_REQUEST['cid'] : 25;//活动id
        $latlon=!empty($_REQUEST['latlon']) ?  $_REQUEST['latlon'] : '118.77807441,32.0572355';
        $goodinfo = $storeGoods->getLangInfo1($id, $lang);
        $langData=array(
            $this->langData->public->information,
            $this->langData->public->name,
            $this->langData->project->buy_now,
            $this->langData->project->select_spec,
            $this->langData->project->add_to_cart,
            $this->langData->project->current_inventory,
            $this->langData->project->choose_distribution_shop,
            $this->langData->public->num,
        );
        //促销
        if ($source == 3) {
            $sql = " select ps.*,pg.goods_key as item_id,pg.goods_price,pg.discount_price,pg.limit_amount,ps.status  from " . DB_PREFIX . "promotion_sale as ps left join " . DB_PREFIX . "promotion_goods as pg on
                ps.id = pg.prom_id  where ps.`store_id` = $this->store_id and ps.`status` in (1,2) and ps.`mark` =1 and ps.id =$cid  and pg.goods_id=".$id;
            $arr = $storeGoods->querySql($sql);
            foreach ($arr as $k => $v) {
                $arr1 = explode('_', $v['item_id']);
            }
        }
        // 团购
        if ($source == 2) {
            $where2 = '  where  store_id = ' . $this->store_id . ' and  mark =1 and id=' . $cid;
            $sql2 = 'SELECT  goods_spec_key as item_id,group_goods_price,goods_price,group_goods_num,virtual_num,end_time,title as name,start_time
                FROM  ' . DB_PREFIX . 'goods_group_buy ' . $where2;
            $arr = $storeGoods->querySql($sql2);
            foreach ($arr as $v) {
                $arr1 = explode('_', $v['item_id']);
            }}
        $spec_img1 = $this->get_spec($goodinfo['goods_id'], $id, 2);

        if (!empty($arr1)) {
            foreach ($spec_img1 as $key => $value) {

                foreach($value['spec_data'] as $k1=>$v1){
                    if (!in_array($v1['item_id'], $arr1))
                        unset($spec_img1[$key][$k1]);

                }

            }
        }
        $sql11 = "select * from bs_store where id=".$store_id;
        $storeMod = &m('store');
        $r = $storeMod->querySql($sql11);
        $storeList = $this->getCountryStore($this->countryId,$goods_id,$latlon);
        $info=array('langData'=>$langData,'spec_img'=>$spec_img1,'good_info'=>$goodinfo,'lang'=>$lang,'storeList'=>$storeList,'store_discount'=>$r[0]['store_discount']);
        if($info){
            $this->setData($info,1,'');
        }
    }


    /**
     * 获取商品规格
     * @param $goods_id|商品id  $type=1 读取商品原有规格属性  2 读取区域编辑后的规格属性
     * @return array
     */
    public function get_spec($goods_id, $store_goods_id, $type = 1) {

        $storeGoodMod = &m("storeGoodItemPrice");
        //商品规格 价钱 库存表 找出 所有 规格项id
        //$keys = M('SpecGoodsPrice')->where("goods_id", $goods_id)->getField("GROUP_CONCAT(`key` SEPARATOR '_') ");
        if ($type == 1) {
            $sql = "select `key` as item_key from  " . DB_PREFIX . "goods_spec_price where goods_id=" . $goods_id;
        } else {
            $sql = "select `key` as item_key from  " . DB_PREFIX . "store_goods_spec_price where store_goods_id=" . $store_goods_id;
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
            $sql3 = "select spec_image_id,src from " . DB_PREFIX . "goods_spec_image where goods_id=" . $goods_id; // 规格对应的 图片表， 例如颜色
            $img_list = $storeGoodMod->querySql($sql3);
            foreach ($img_list as $k => $v) {
                $specImage[$v['spec_image_id']] = $v['src'];
            }
            $sql4 = "SELECT a.`order`,b.*,al.spec_name,bl.`item_name`  FROM " . DB_PREFIX . "goods_spec AS a
                     INNER JOIN " . DB_PREFIX . "goods_spec_item AS b ON a.id = b.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_lang AS al ON a.id=al.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_item_lang as bl ON b.id=bl.item_id
                     WHERE b.id IN($keys) and al.lang_id=" . $this->lang_id . " and bl.lang_id=" . $this->lang_id . " ORDER BY b.id";
            $filter_spec2 = $storeGoodMod->querySql($sql4);

            foreach ($filter_spec2 as $key => $val) {

                $filter_spec[$key]['spec_name'] = $val['spec_name'];
                $filter_spec[$key]['spec_data']=  array(
                    'item_id' => $val['id'],
                    'item' => $val['item_name']
                );
            }
        }

        $result=array();
        foreach ($filter_spec as $k => $v) {
            $result[$v['spec_name']]['spec_name']=$v['spec_name'];
            $result[$v['spec_name']]['spec_data'][]=$v['spec_data'];
        }

        $result=$this->toIndexArr($result);

        return $result;
    }


    function toIndexArr($arr){
        $i=0;
        foreach($arr as $key => $value){
            $newArr[$i] = $value;
            $i++;
        }
        return $newArr;
    }


    //获取配送店铺
    public function getCountryStore($country_id,$goods_id,$latlon) {
        if ($_SESSION['userId']) {
            $user_id = $_SESSION['userId'];
            $userMod = &m('user');
            $sql = 'SELECT *  FROM  ' . DB_PREFIX . 'user WHERE  id  =' . $user_id; //odm_members
            $datas = $userMod->querySql($sql);
            if ($datas[0]['odm_members'] == 0) {
                $where = ' and c.store_type < 4  ';
            } else {
                $where = '';
            }
        } else {
            $where = ' and c.store_type < 4  ';
        }
        $mod = &m('store');
        $sql = 'SELECT  c.id,l.store_name,c.distance,c.longitude,c.latitude  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE c.is_open = 1  and  l.lang_id =' . $this->lang_id . ' and l.distinguish=0  and c.store_cate_id=' . $country_id . $where;
        $data = $mod->querySql($sql);
        $sql1='SELECT store_id FROM '.DB_PREFIX.'store_goods  WHERE goods_id='.$goods_id . ' and mark =1  and is_on_sale =1 ';
        $gData=$mod->querySql($sql1);
        foreach($gData as $key=>$val){
            $val=join(',',$val);
            $temp[]=$val;
        }
        $temp=array_unique($temp);
        foreach($data as $k=>$v){
            foreach($temp as $k1=>$v1){
                if($v['id']==$v1){
                    $arr[$k1]['id']=$v['id'];
                    $arr[$k1]['store_name']=$v['store_name'];
                    $arr[$k1]['distance']=$v['distance'];
                    $arr[$k1]['latitude']=$v['latitude'];
                    $arr[$k1]['longitude']=$v['longitude'];
                }
            }
        }

        $latlon = explode(',', $latlon);
        $lng = $latlon[0]; //经度
        $lat = $latlon[1]; //纬度
        foreach ($arr as $key => $val) {
            $s = $this->getdistance($lng, $lat, $val['longitude'], $val['latitude']);
            $distance = number_format(($s / 1000), 2, '.', '');
            $arr[$key]['dis'] = $distance;
            $busSql = "SELECT buss_id FROM " . DB_PREFIX . 'store_business WHERE  store_id=' .$val['id'];
            $busData = $mod->querySql($busSql);
            $arr[$key]['b_id']=$busData[0]['buss_id'];
            if($val['distance'] < $distance){
                unset($arr[$key]);
            }
        }
        $sort = array(
            'direction' => 'SORT_ASC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
            'field' => 'dis', //排序字段
        );
        $arrSort = array();
        foreach ($arr AS $uniqid => $row) {
            foreach ($row AS $key => $value) {
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if ($sort['direction']) {
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $arr);
        }
        return $arr;
    }


    //转化距离
    function getdistance($lng1, $lat1, $lng2, $lat2) {
        // 将角度转为狐度
        $radLat1 = deg2rad($lat1); //deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;
        return $s;
    }


    /**
     * 选择规格库存价格页面接口
     * @author gao
     * @date 2018/08/21
     */
    public function getSpecPrice(){
        $goods_id=!empty($_REQUEST['goods_id']) ? $_REQUEST['goods_id'] : 46;//商品id
        $store_id=!empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $storeMod=&m('storeGoods');
        $store = &m('store');
        $where=' and  mark=1   and   is_on_sale =1';
        $sql="SELECT id FROM ".DB_PREFIX.'store_goods WHERE store_id='.$store_id.' AND goods_id='.$goods_id.$where;
        $data=$storeMod->querySql($sql);
        $sqll = "select store_discount from bs_store where id=".$store_id;
        $res = $store->querySql($sqll);
        $id=$data[0]['id'];
        $storeGoodMod = &m("storeGoodItemPrice");
        $spec_data = $storeGoodMod->getData(array('cond' => "store_goods_id=" . $id));
        foreach ($spec_data as $k => $v) {
            $spec_arr[] = $v;
//            $spec_arr['store_discount'] = $res[0]['store_discount'];
        }
        if($spec_arr){
            $this->setData($spec_arr,1,'');
        }
    }



    /**
     * 购物车页面接口
     * @author gao
     * @date 2018/08/27
     */
    public  function cart(){
        $cartMod=&m('cart');
        $langData=array(
            $this->langData->public->settlement,
            $this->langData->public->free_of_freight,
            $this->langData->public->total,
            $this->langData->public->administration,
            $this->langData->public->car,
            $this->langData->public->total_selection,
        );

        $sql = "select * from " . DB_PREFIX . "cart where `user_id` = '{$this->userId}' and `shipping_store_id` = ".$this->store_id;

        $rsSql = $cartMod->querySql($sql);
        foreach ($rsSql as $k1 => $v1) {
            $status = $this->checkOnSale($v1['goods_id']);
            if ($status == 2) {
                $cartMod->doDelete(array('cond' => "`goods_id`='{$v1['goods_id']}'"));
            }
            $mark = $this->checkDelete($v1['goods_id']);
            if ($mark == 0) {
                $cartMod->doDelete(array('cond' => "`goods_id` in ({$v1['goods_id']})"));
            }
        }
        $sql = "select * from " . DB_PREFIX . "cart where `user_id` = '{$this->userId}' and `shipping_store_id` = ".$this->store_id;
        $rs =$cartMod->querySql($sql);

        // 统计购车商品数量
        // 如果有规格，就去当前商品的规格图片，没有去主商品图片，是组合商品 组合商品的图片
        foreach ($rs as $k => $v) {
            $rs[$k]['original_img'] = $this->getGoodImg($v['goods_id']);
            $rs[$k]['store_name'] = $this->getStoreName($v['store_id']);
            $rs[$k]['short'] = $this->short;
            $rs[$k]['totalMoney'] = number_format(round(($v['goods_price'] * $v['goods_num']), 2), 2);
            if ($v['spec_key']) {
                $info = explode('_', $v['spec_key']);
                foreach ($info as $k1 => $v1) {
                    $sql = "select `item_name` from " . DB_PREFIX . "goods_spec_item_lang where `item_id` = '{$v1}' and  `lang_id` = $this->lang_id";
                    $spec_1 = $cartMod->querySql($sql);
                    $spec[] = $spec_1[0]['item_name'];
                }
                $spec_key = implode(':', $spec);
                $rs[$k]['spec_key_name'] = $spec_key;
                $spec = array();
            }
            $rs[$k]['shipping_store'] = $this->getStoreName($v['shipping_store_id']);
            $rs[$k]['goods_name'] = $cartMod->getGoodNameById($v['goods_id'], $this->lang_id);
        }
        $cartData=array('langData'=>$langData,'cartData'=>$rs);
        // if($rs){
        $this->setData($cartData,1,'');
        // }
    }

    /**
     * 购物车页面接口
     * @author gao
     * @date 2018/08/27
     */
    public function dele() {
        $cartMod=&m('cart');
        $cart_id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
        $query = array(
            'cond' => "`id` in ({$cart_id})"
        );
        $rs = $cartMod->doDelete($query);
        if($rs){
            $this->setData(array(),1,'');
        }
    }
    /**
     * 获取当前商品是否下架
     * @author wanyan
     * @date 2017-11-09
     */
    public function checkOnSale($goods_id) {
        $storeGoodsMod=&m('areaGood');
        $query = array(
            'cond' => "`id`='{$goods_id}'",
            'fields' => "is_on_sale"
        );
        $rs = $storeGoodsMod->getOne($query);
        return $rs['is_on_sale'];
    }


    /**
     * 获取当前商品是否删除
     * @author wanyan
     * @date 2017-11-09
     */
    public function checkDelete($goods_id) {
        $storeGoodsMod=&m('areaGood');
        $query = array(
            'cond' => "`id`='{$goods_id}'",
            'fields' => "mark"
        );
        $rs = $storeGoodsMod->getOne($query);
        return $rs['mark'];
    }



    /**
     * 获取当前商品图片
     * @author wanyan
     * @date 2017-09-21
     */
    public function getGoodImg($goods_id) {
        $storeMod = &m('store');
        $sql = 'select gl.original_img  from  '
            . DB_PREFIX . 'store_goods as g  left join '
            . DB_PREFIX . 'goods as gl on g.goods_id = gl.goods_id where g.id  = ' . $goods_id;
        $rs = $storeMod->querySql($sql);
        return $rs[0]['original_img'];
    }


    /**
     * 获取店铺名称
     * @author wanyan
     * @date 2017-09-21
     */
    public function getStoreName($store_id) {
        $storeMod= &m('store');
        $sql = 'SELECT  l.store_name  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE l.distinguish=0 and  l.lang_id =' . $this->lang_id . '  and  c.id=' . $store_id;
        $res = $storeMod->querySql($sql);

        return $res[0]['store_name'];
    }


    /**
     * 限时优惠商品详情
     * @author:tangp
     * @date:2018-09-04
     */
    public function goodInfo()
    {
        $source = !empty($_REQUEST['source']) ? intval($_REQUEST['source']) : 0;
        $cid = !empty($_REQUEST['cid']) ? intval($_REQUEST['cid']) : 0;
        $goods_key = !empty($_REQUEST['goods_key']) ? $_REQUEST['goods_key'] : '';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $userId = $this->userId;
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $langData = array(
            $this->langData->public->mail,
            $this->langData->project->countdown_for_sale,
            $this->langData->public->size,
            $this->langData->public->temperature,
            $this->langData->project->buy_num,
            $this->langData->project->goods_detail,
            $this->langData->project->goods_params,
            $this->langData->public->by,
            $this->langData->project->collection,
            $this->langData->public->car
        );
        $storeGoods = &m('areaGood');

        if ($_REQUEST['goods_id']) {
            $g_info = $storeGoods->getOne(array("cond" => "goods_id=" . $_REQUEST['goods_id'] . " and store_id=" . $storeid . " and mark =1"));
            $id = $g_info['id'];
        } else {
            $id = ($_REQUEST['gid']) ? $_REQUEST['gid'] : 0;
        }
        if (empty($id)) {
            $this->setData(array(),0,'该商品已下架');
        }
        $fxCode = ($_REQUEST['fxCode']) ? $_REQUEST['fxCode'] : '';
        $goodMod = &m('goods');
        $goodClassMod = &m('goodsClass');
        $goodAttrMod = &m('goodsAttriInfo');
        $goodImgMod = &m('goodsImg');
        $storeGoodMod = &m("storeGoodItemPrice");

        //商品信息
        $info = $storeGoods->getLangInfo($id, $lang);
        if (empty($info)) {
            $this->setData(array(),0,'该商品已下架');
        }

        /*
         * modify by lee
         */
        //绑定销售
        $comMainMod = &m('combinedSale');
        $comGoodMod = &m('combinedGoods');
        $has_main = $comMainMod->getOne(array("cond" => "main_id =" . $id . " and status = 1"));
        $has_com = $comGoodMod->getOne(array("cond" => "store_goods_id =" . $id));

        if ($has_main || $has_com) {
            if ($has_main) {
                $com_id = $has_main['id'];
            }
            if ($has_com) {
                $com_id = $has_com['com_id'];
            }
            //$com_list = $comGoodMod->getData(array("cond" =>"com_id =".$com_id." and store_goods_id!=".$id,"group by"=>"store_goods_id"));
            $com_sql = "select c.* from " . DB_PREFIX . "combined_goods as c
                        left join  " . DB_PREFIX . "combined_sale as s on s.id = c.com_id
                        where com_id =" . $com_id . " and c.store_goods_id!= " . $id . " and s.status = 1 group by c.store_goods_id";
            $com_list = $comGoodMod->querySql($com_sql);
            foreach ($com_list as $k => $v) {
                $new_info = $storeGoods->getLangInfo($v['store_goods_id'], $lang);
                $com_list[$k]['goods_name'] = $new_info['goods_name'];
            }

            $com_num = count($com_list);
            $promGood = array();
            switch ($com_num) {
                case $com_num < 4:
                    $promGood[] = $com_list;
                    break;
                case $com_num > 3:
                    $promGood[0] = array_slice($com_list, 0, 3);
                    $promGood[1] = array_slice($com_list, 3, 3);
            }
        }
        //print_r($promGood);exit;

        $name = $info['goods_name']; //详情名称
        $cat = $info['cat_id']; //分类
        $style_id = $info ['style_id']; //类型
        $brand_id = $info ['brand_id']; //类型
        $where = "s.goods_name like '%$name%'  or s.cat_id =" . $cat;
        if ($style_id) {
            $where .= " or s.style_id = " . $style_id;
        }
        if ($brand_id) {
            $where .= " or s.brand_id = " . $brand_id;
        }
        $store_sql = "select s.id,gl.original_img from "
            . DB_PREFIX . "store_goods as s LEFT JOIN  "
            . DB_PREFIX . "goods AS gl ON s.`goods_id` = gl.`goods_id` where  (store_id = {$storeid} and s.is_on_sale = 1 and s.mark = 1 and id !={$id}) and
                       (" . $where . ")";

        $store_good = $goodMod->querySql($store_sql);
        foreach ($store_good as $k => $v) {
            $new_info_2 = $storeGoods->getLangInfo($v['id'], $lang);
            $store_good[$k]['goods_name'] = $new_info_2['goods_name'];
        }
        $store_goods_num = count($store_good);
        $storeGood = array();
        switch ($store_goods_num) {
            case $store_goods_num < 4:
                $storeGood[] = $store_good;
                break;
            case $store_goods_num > 3:
                $storeGood[0] = array_slice($store_good, 0, 3);
                $storeGood[1] = array_slice($store_good, 3, 3);
        }
        //推荐
        if (empty($promGood[0])) {
            $promGood = array();
        }
        if (empty($storeGood[0])) {
            $storeGood = array();
        }
//        $this->assign('promGood', $promGood); //组合销售
//        $this->assign('storeGood', $storeGood); //推荐商品
        //end
        //收藏商品
        $sql_collection = 'select * from ' . DB_PREFIX . 'user_collection where user_id=' . $userId . ' and store_id=' . $storeid;
        $data_collection = $storeGoods->querySql($sql_collection);
        foreach ($data_collection as &$collertion) {
            if ($collertion['store_good_id'] == $info['id']) {
                $info['type'] = 1;
            }
        }
        $goods_info = $goodMod->getOne(array("cond" => "goods_id=" . $info['goods_id']));
        //分类信息
        $info['original_img'] = $goods_info['original_img'];

        $cat_3 = $goodClassMod->getLangInfo($goods_info['cat_id'], $lang);
        $cat_2 = $goodClassMod->getLangInfo($cat_3[0]['parent_id'], $lang);
        $cat_1 = $goodClassMod->getLangInfo($cat_2[0]['parent_id'], $lang);
        //商品图片页
        $img_arr = $goodImgMod->getData(array('cond' => "goods_id=" . $info['goods_id']));
        //商品规格
        if (!empty($source)) {
            $seckMod = &m('spikeActivity');
            $goodsByMod = &m('groupbuy');
            $goodPromMod = &m('goodProm');
            $promotionMod = &m('goodPromDetail');
            $curtime = time();
            $tody = strtotime(date('Y-m-d', time()));
            $now = $curtime - $tody;
            //秒杀
            if ($source == 1) {
                $where1 = 'WHERE store_id =' . $storeid. '  and  ' . $curtime . ' > start_time  and id=' . $cid;
                $sql = 'SELECT  store_goods_id,o_price,price,goods_num,start_time,end_time,start_our,end_our,goods_name,name  FROM  ' . DB_PREFIX . 'spike_activity ' . $where1;
                $arr = $seckMod->querySql($sql);

                /*   $spec_img1 = $this->get_spec(0, $arr[0]['store_goods_id'], 2); */

                foreach ($arr as $k => $v) {
                    /* $arr1 = explode('_', $v['item_id']); */
                    $info['shop_price'] = $v['price'];
                    $info['market_price'] = $v['o_price'];
                    $info['goods_storage'] = $v['goods_num'];
                    $info['goods_name'] = $v['goods_name'];
                    if ($curtime > $v['start_time'] && $curtime < $v['end_time']) {
                        if ($now > $v['start_our'] && $now < $v['end_our']) {
                            $arr[$k]['in_time'] = 1;
                        } else {
                            $arr[$k]['in_time'] = 2;
                        }
                    } else {
                        $arr[$k]['in_time'] = 3;
                    }
                    $arr[$k]['end_timea'] = $arr[$k]['end_our'] - $now;
                    $arr[$k]['start_our'] = (mktime(0, 0, 0, date("m"), date("d"), date("Y")) + $arr[$k]['start_our']);
                    $arr[$k]['end_our'] = (mktime(0, 0, 0, date("m"), date("d"), date("Y")) + $arr[$k]['end_our']);
                }
                $this->assign('arr', $arr[0]);
                // $this->assign('arr1', $arr1);
                // $str = $arr[0]['item_id'];
            }
            //优惠
            if ($source == 3) {
                if (!empty($goods_key)) {
                    $sql = " select ps.*,pg.goods_key as item_id,pg.goods_price,pg.discount_price,pg.limit_amount,ps.status  from " . DB_PREFIX . "promotion_sale as ps left join " . DB_PREFIX . "promotion_goods as pg on
                ps.id = pg.prom_id  where ps.`store_id` = $this->storeid and ps.`status` in (1,2) and ps.`mark` =1 and ps.id =$cid and pg.goods_id=" . $id . " and pg.goods_key=" . $goods_key . "   order by ps.status desc,ps.id desc";
                    $arr = $seckMod->querySql($sql);
                } else {
                    $sql = " select ps.*,pg.goods_key as item_id,pg.goods_price,pg.discount_price,pg.limit_amount,ps.status  from " . DB_PREFIX . "promotion_sale as ps left join " . DB_PREFIX . "promotion_goods as pg on
                ps.id = pg.prom_id  where ps.`store_id` = $this->storeid and ps.`status` in (1,2) and ps.`mark` =1 and ps.id =$cid and pg.goods_id=" . $id . " order by ps.status desc,ps.id desc";
                    $arr = $seckMod->querySql($sql);
                }

                foreach ($arr as $k => $v) {
                    $arr1 = explode('_', $v['item_id']);
                    $info['shop_price'] = $v['discount_price'];
                    $info['market_price'] = $v['goods_price'];
                    if ($v['status'] == 2) {
                        $arr[$k]['end_timea'] = $v['end_time'] - $curtime;
                    }
                }


//                $this->assign('arr', $arr[0]);
//                $this->assign('arr1', $arr1);
                $str = $arr[0]['item_id'];
                /*  var_dump($str); */
            }
            // 团购
            if ($source == 2) {
                $where2 = '  where  store_id = ' . $storeid. ' and  mark =1 and id=' . $cid;
                $sql2 = 'SELECT  goods_spec_key as item_id,group_goods_price,goods_price,group_goods_num,virtual_num,end_time,title as name,start_time
                FROM  ' . DB_PREFIX . 'goods_group_buy ' . $where2;
                $arr = $seckMod->querySql($sql2);
                foreach ($arr as $v) {
                    $arr1 = explode('_', $v['item_id']);
                    $info['shop_price'] = $v['group_goods_price'];
                    $info['market_price'] = $v['goods_price'];
                    $info['goods_storage'] = $v['group_goods_num'];
                }
//                $this->assign('arr', $arr[0]);
//                $this->assign('arr1', $arr1);
                $str = $arr[0]['item_id'];
            }
            //组合销售
            if ($source == 4) {
                $where4 = '  where  com_id=' . $cid;
                $sql2 = "";
                $arr = $seckMod->querySql($sql2);
            }
        } else {
            //店铺商品打折
            $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $storeid;
            $store_arr = $goodAttrMod->querySql($store_sql);
            $info['shop_price'] = number_format($info['shop_price'] * $store_arr[0]['store_discount'],2);
            $this->assign("store_arr", $store_arr[0]);
        }


        //商品属性(暂时死数据展示，参数一 原始商品ID  参数二 语言ID)
        $attr_arr = $goodAttrMod->getLangData($info['goods_id'], $lang);

        //获取区域商品规格价格
        $spec_data = $storeGoodMod->getData(array('cond' => "store_goods_id=" . $id));
        foreach ($spec_data as $k => $v) {
            $spec_arr[$v['key']] = $v;
        }

        if (!empty($str)) {
            foreach ($spec_arr as $k => $v) {
                if ($k != $str) {
                    unset($spec_arr[$k]);
                } else {
                    $spec_arr[$k]['price'] = $info['shop_price'];
                    $spec_arr[$k]['goods_storage'] = $info['goods_storage'];
                }
            }
        }
        $spec_img = $this->get_spec($info['goods_id'], $id, 2);
        if (!empty($arr1) && $source != 1) {
            foreach ($spec_img as $key => $value) {
                foreach ($value as $k => $v) {
                    if (!in_array($v['item_id'], $arr1))
                        unset($spec_img[$key][$k]);
                }
            }
        }
        //足迹
        $this->infoFootPrint($info['goods_id'], $info['id']);
        //获取币种信息
        $sql = "select c.* from " . DB_PREFIX . "currency as c inner join " . DB_PREFIX . "store as s on c.id=s.currency_id where s.id=" . $info['store_id'];
        $cur_info = $storeGoods->querySql($sql);

        //获取商品评价数量
        $good_all_num = " select count(1) as all_num from bs_goods_comment  where goods_id ={$info['id']}";
        $good_all_num = $this->goodsCommentMod->querySql($good_all_num);
        $this->assign('good_all_num', $good_all_num[0]);
        //获取评价列表信息
        $eva_sql = 'select  comment_id, username, goods_rank, add_time, content, img, revert  from ' . DB_PREFIX . 'goods_comment
                     where  goods_id  = ' . $info['id'] . ' and store_id = ' . $this->store_id . '   order by comment_id desc ';
        $list = $this->goodsCommentMod->querySql($eva_sql);
        $new_list = array_map(function($vo) {
            $vo['img'] = explode(',', $vo['img']);
            return $vo;
        }, $list);

        //获取国家下属所有店铺
        $storeList = $this->getCountryStore1($this->countryId);
        //客服聊天参数
        $imMod = &m('user');
        $kf_cond = "is_kefu = 1 and kf_status = 1 and store_id = " . $info['store_id'];
        $kf_arr = $imMod->getData(array("cond" => $kf_cond));
        if (is_array($kf_arr)) {
            $key = array_rand($kf_arr);
            $kf_id = $kf_arr[$key]['id'];
        } else {
            $kf_id = "no";
        }
        //组合销售活动
        $zhhdsql = 'SELECT gs.store_goods_id FROM  ' . DB_PREFIX . 'combined_sale AS cs LEFT JOIN  ' . DB_PREFIX . 'combined_goods as gs on gs.com_id=cs.id  where cs.status=1 and  gs.store_goods_id =' . $id;
        $zhhdData = $imMod->querySql($zhhdsql);

        //限时秒杀活动
        $xsmssql = 'SELECT store_goods_id FROM  ' . DB_PREFIX . 'spike_activity  where store_goods_id =' . $id;
        $xsmsData = $imMod->querySql($xsmssql);

        //促销活动
        $cxql = 'SELECT gs.goods_id FROM  ' . DB_PREFIX . 'promotion_sale AS cs LEFT JOIN  ' . DB_PREFIX . 'promotion_goods as gs on gs.prom_id=cs.id  where cs.status=1 and  gs.goods_id =' . $id;
        $cxData = $imMod->querySql($cxql);

        //团购活动
        $tgql = 'SELECT goods_id FROM  ' . DB_PREFIX . 'goods_group_buy where is_end =1 and goods_id =' . $id;
        $tgData = $imMod->querySql($tgql);

        $data = array(
            'langData'=> $langData,
            'info'    => $info,
            'img_arr' => $img_arr,
            'cur_info'=> $cur_info[0],
            'spec_img'=> $spec_img,
            'attr_arr'=> $attr_arr,
            // 'spec_key'=> $str,
            'arr'     => $arr[0],
            // 'list'    => $list,
            'store_good' => $store_good,
            'promGood'=> $promGood,
            'good_all_num' => $good_all_num[0]
        );

        $this->setData($data,1,'');

    }
    public function getCountryStore1($country_id) {
        if ($_SESSION['userId']) {
            $user_id = $_SESSION['userId'];
            $userMod = &m('user');
            $sql = 'SELECT *  FROM  ' . DB_PREFIX . 'user WHERE  id  =' . $user_id; //odm_members
            $datas = $userMod->querySql($sql);
            if ($datas[0]['odm_members'] == 0) {
                $where = ' and c.store_type <4 ';
            } else {
                $where = '';
            }
        } else {
            $where = ' and c.store_type<4 ';
        }
        $mod = &m('store');
//        $data = $mod->getData(array("cond" => "store_cate_id=" . $country_id . " and  is_open=1"));
        $sql = 'SELECT  c.id,l.store_name  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE c.is_open = 1  and  l.lang_id =' . $this->lang_id . ' and l.distinguish=0  and c.store_cate_id=' . $country_id . $where;
        $data = $mod->querySql($sql);
        return $data;
    }
    /**
     * 为您推荐的商品详情
     * @author:tangp
     * @date:2018-09-04
     */
    public function tuiInfo()
    {
        $source = !empty($_REQUEST['source']) ? intval($_REQUEST['source']) : 0;
        $cid = !empty($_REQUEST['cid']) ? intval($_REQUEST['cid']) : 0;
        $goods_key = !empty($_REQUEST['goods_key']) ? $_REQUEST['goods_key'] : '';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $userId = $this->userId;
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->store_id;
        $langData = array(
            $this->langData->public->mail,
            $this->langData->project->select_goods_spec,
            $this->langData->public->size,
            $this->langData->public->clolor,
            $this->langData->project->delivery_cycle,
            $this->langData->project->choose_distribution_shop,
            $this->langData->project->buy_num,
            $this->langData->project->current_inventory,
            $this->langData->project->material_distribution,
            $this->langData->public->ok,
            $this->langData->project->combined_sales,
            $this->langData->project->relation_recommend,
            $this->langData->project->Intelligent_recommend,
            $this->langData->project->goods_evaluate,
            $this->langData->project->no_time_evaluate,
            $this->langData->project->goods_detail,
            $this->langData->project->goods_params,
            $this->langData->project->collection,
            $this->langData->public->car,
            $this->langData->project->add_to_cart,
            $this->langData->public->by

        );
        $storeGoods = &m('areaGood');

        if ($_REQUEST['goods_id']) {
            $g_info = $storeGoods->getOne(array("cond" => "goods_id=" . $_REQUEST['goods_id'] . " and store_id=" . $storeid . " and mark =1"));
            $id = $g_info['id'];
        } else {
            $id = ($_REQUEST['gid']) ? $_REQUEST['gid'] : 0;
        }
        if (empty($id)) {
            $this->setData(array(),0,'该商品已下架');
        }
        $fxCode = ($_REQUEST['fxCode']) ? $_REQUEST['fxCode'] : '';
        $goodMod = &m('goods');
        $goodClassMod = &m('goodsClass');
        $goodAttrMod = &m('goodsAttriInfo');
        $goodImgMod = &m('goodsImg');
        $storeGoodMod = &m("storeGoodItemPrice");

        //商品信息
        $info = $storeGoods->getLangInfo($id, $lang);
        if (empty($info)) {
            $this->setData(array(),0,'该商品已下架');
        }

        /*
         * modify by lee
         */
        //绑定销售
        $comMainMod = &m('combinedSale');
        $comGoodMod = &m('combinedGoods');
        $has_main = $comMainMod->getOne(array("cond" => "main_id =" . $id . " and status = 1"));
        $has_com = $comGoodMod->getOne(array("cond" => "store_goods_id =" . $id));

        if ($has_main || $has_com) {
            if ($has_main) {
                $com_id = $has_main['id'];
            }
            if ($has_com) {
                $com_id = $has_com['com_id'];
            }
            //$com_list = $comGoodMod->getData(array("cond" =>"com_id =".$com_id." and store_goods_id!=".$id,"group by"=>"store_goods_id"));
            $com_sql = "select c.* from " . DB_PREFIX . "combined_goods as c
                        left join  " . DB_PREFIX . "combined_sale as s on s.id = c.com_id
                        where com_id =" . $com_id . " and c.store_goods_id!= " . $id . " and s.status = 1 group by c.store_goods_id";
            $com_list = $comGoodMod->querySql($com_sql);
            foreach ($com_list as $k => $v) {
                $new_info = $storeGoods->getLangInfo($v['store_goods_id'], $lang);
                $com_list[$k]['goods_name'] = $new_info['goods_name'];
            }

            $com_num = count($com_list);
            $promGood = array();
            switch ($com_num) {
                case $com_num < 4:
                    $promGood[] = $com_list;
                    break;
                case $com_num > 3:
                    $promGood[0] = array_slice($com_list, 0, 3);
                    $promGood[1] = array_slice($com_list, 3, 3);
            }
        }
        //print_r($promGood);exit;

        $name = $info['goods_name']; //详情名称
        $cat = $info['cat_id']; //分类
        $style_id = $info ['style_id']; //类型
        $brand_id = $info ['brand_id']; //类型
        $where = "s.goods_name like '%$name%'  or s.cat_id =" . $cat;
        if ($style_id) {
            $where .= " or s.style_id = " . $style_id;
        }
        if ($brand_id) {
            $where .= " or s.brand_id = " . $brand_id;
        }
        $store_sql = "select s.id,gl.original_img,s.goods_id from "
            . DB_PREFIX . "store_goods as s LEFT JOIN  "
            . DB_PREFIX . "goods AS gl ON s.`goods_id` = gl.`goods_id` where  (store_id = {$storeid} and s.is_on_sale = 1 and s.mark = 1 and id !={$id}) and
                       (" . $where . ")";

        $store_good = $goodMod->querySql($store_sql);
        foreach ($store_good as $k => $v) {
            $new_info_2 = $storeGoods->getLangInfo($v['id'], $lang);
            $store_good[$k]['goods_name'] = $new_info_2['goods_name'];
        }
        $store_goods_num = count($store_good);
        $storeGood = array();
        switch ($store_goods_num) {
            case $store_goods_num < 4:
                $storeGood[] = $store_good;
                break;
            case $store_goods_num > 3:
                $storeGood[0] = array_slice($store_good, 0, 3);
                $storeGood[1] = array_slice($store_good, 3, 3);
        }
        //推荐
        if (empty($promGood[0])) {
            $promGood = array();
        }
        if (empty($storeGood[0])) {
            $storeGood = array();
        }
        $this->assign('promGood', $promGood); //组合销售
        $this->assign('storeGood', $storeGood); //推荐商品
        //end
        //收藏商品
        $sql_collection = 'select * from ' . DB_PREFIX . 'user_collection where user_id=' . $userId . ' and store_id=' . $storeid;
        $data_collection = $storeGoods->querySql($sql_collection);
        foreach ($data_collection as &$collertion) {
            if ($collertion['store_good_id'] == $info['id']) {
                $info['type'] = 1;
            }
        }
        $goods_info = $goodMod->getOne(array("cond" => "goods_id=" . $info['goods_id']));
        //分类信息
        $info['original_img'] = $goods_info['original_img'];

        $cat_3 = $goodClassMod->getLangInfo($goods_info['cat_id'], $lang);
        $cat_2 = $goodClassMod->getLangInfo($cat_3[0]['parent_id'], $lang);
        $cat_1 = $goodClassMod->getLangInfo($cat_2[0]['parent_id'], $lang);
        //商品图片页
        $img_arr = $goodImgMod->getData(array('cond' => "goods_id=" . $info['goods_id']));
        //商品规格
        if (!empty($source)) {
            $seckMod = &m('spikeActivity');
            $goodsByMod = &m('groupbuy');
            $goodPromMod = &m('goodProm');
            $promotionMod = &m('goodPromDetail');
            $curtime = time();
            $tody = strtotime(date('Y-m-d', time()));
            $now = $curtime - $tody;
            //秒杀
            if ($source == 1) {
                $where1 = 'WHERE store_id =' . $storeid. '  and  ' . $curtime . ' > start_time  and id=' . $cid;
                $sql = 'SELECT  store_goods_id,o_price,price,goods_num,start_time,end_time,start_our,end_our,goods_name,name  FROM  ' . DB_PREFIX . 'spike_activity ' . $where1;
                $arr = $seckMod->querySql($sql);

                /*   $spec_img1 = $this->get_spec(0, $arr[0]['store_goods_id'], 2); */

                foreach ($arr as $k => $v) {
                    /* $arr1 = explode('_', $v['item_id']); */
                    $info['shop_price'] = $v['price'];
                    $info['market_price'] = $v['o_price'];
                    $info['goods_storage'] = $v['goods_num'];
                    $info['goods_name'] = $v['goods_name'];
                    if ($curtime > $v['start_time'] && $curtime < $v['end_time']) {
                        if ($now > $v['start_our'] && $now < $v['end_our']) {
                            $arr[$k]['in_time'] = 1;
                        } else {
                            $arr[$k]['in_time'] = 2;
                        }
                    } else {
                        $arr[$k]['in_time'] = 3;
                    }
                    $arr[$k]['end_timea'] = $arr[$k]['end_our'] - $now;
                    $arr[$k]['start_our'] = (mktime(0, 0, 0, date("m"), date("d"), date("Y")) + $arr[$k]['start_our']);
                    $arr[$k]['end_our'] = (mktime(0, 0, 0, date("m"), date("d"), date("Y")) + $arr[$k]['end_our']);
                }
                $this->assign('arr', $arr[0]);
                // $this->assign('arr1', $arr1);
                // $str = $arr[0]['item_id'];
            }
            //优惠
            if ($source == 3) {
                if (!empty($goods_key)) {
                    $sql = " select ps.*,pg.goods_key as item_id,pg.goods_price,pg.discount_price,pg.limit_amount,ps.status  from " . DB_PREFIX . "promotion_sale as ps left join " . DB_PREFIX . "promotion_goods as pg on
                ps.id = pg.prom_id  where ps.`store_id` = $this->storeid and ps.`status` in (1,2) and ps.`mark` =1 and ps.id =$cid and pg.goods_id=" . $id . " and pg.goods_key=" . $goods_key . "   order by ps.status desc,ps.id desc";
                    $arr = $seckMod->querySql($sql);
                } else {
                    $sql = " select ps.*,pg.goods_key as item_id,pg.goods_price,pg.discount_price,pg.limit_amount,ps.status  from " . DB_PREFIX . "promotion_sale as ps left join " . DB_PREFIX . "promotion_goods as pg on
                ps.id = pg.prom_id  where ps.`store_id` = $this->storeid and ps.`status` in (1,2) and ps.`mark` =1 and ps.id =$cid and pg.goods_id=" . $id . " order by ps.status desc,ps.id desc";
                    $arr = $seckMod->querySql($sql);
                }

                foreach ($arr as $k => $v) {
                    $arr1 = explode('_', $v['item_id']);
                    $info['shop_price'] = $v['discount_price'];
                    $info['market_price'] = $v['goods_price'];
                    if ($v['status'] == 2) {
                        $arr[$k]['end_timea'] = $v['end_time'] - $curtime;
                    }
                }


                $this->assign('arr', $arr[0]);
                $this->assign('arr1', $arr1);
                $str = $arr[0]['item_id'];
                /*  var_dump($str); */
            }
            // 团购
            if ($source == 2) {
                $where2 = '  where  store_id = ' . $storeid. ' and  mark =1 and id=' . $cid;
                $sql2 = 'SELECT  goods_spec_key as item_id,group_goods_price,goods_price,group_goods_num,virtual_num,end_time,title as name,start_time
                FROM  ' . DB_PREFIX . 'goods_group_buy ' . $where2;
                $arr = $seckMod->querySql($sql2);
                foreach ($arr as $v) {
                    $arr1 = explode('_', $v['item_id']);
                    $info['shop_price'] = $v['group_goods_price'];
                    $info['market_price'] = $v['goods_price'];
                    $info['goods_storage'] = $v['group_goods_num'];
                }
                $this->assign('arr', $arr[0]);
                $this->assign('arr1', $arr1);
                $str = $arr[0]['item_id'];
            }
            //组合销售
            if ($source == 4) {
                $where4 = '  where  com_id=' . $cid;
                $sql2 = "";
                $arr = $seckMod->querySql($sql2);
            }
        } else {
            //店铺商品打折
            $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $storeid;
            $store_arr = $goodAttrMod->querySql($store_sql);
            $info['shop_price'] = number_format($info['shop_price'] * $store_arr[0]['store_discount'],2);
            $this->assign("store_arr", $store_arr[0]);
        }


        //商品属性(暂时死数据展示，参数一 原始商品ID  参数二 语言ID)
        $attr_arr = $goodAttrMod->getLangData($info['goods_id'], $lang);

        //获取区域商品规格价格
        $spec_data = $storeGoodMod->getData(array('cond' => "store_goods_id=" . $id));
        foreach ($spec_data as $k => $v) {
            $spec_arr[$v['key']] = $v;
        }

        if (!empty($str)) {
            foreach ($spec_arr as $k => $v) {
                if ($k != $str) {
                    unset($spec_arr[$k]);
                } else {
                    $spec_arr[$k]['price'] = $info['shop_price'];
                    $spec_arr[$k]['goods_storage'] = $info['goods_storage'];
                }
            }
        }
        $spec_img = $this->get_spec1($info['goods_id'], $id, 2);
        if (!empty($arr1) && $source != 1) {
            foreach ($spec_img as $key => $value) {
                foreach ($value as $k => $v) {
                    if (!in_array($v['item_id'], $arr1))
                        unset($spec_img[$key][$k]);
                }
            }
        }
        //足迹
        $this->infoFootPrint($info['goods_id'], $info['id']);
        $this->assign('store_goods_id', $id);
        //获取币种信息
        $sql = "select c.* from " . DB_PREFIX . "currency as c inner join " . DB_PREFIX . "store as s on c.id=s.currency_id where s.id=" . $info['store_id'];
        $cur_info = $storeGoods->querySql($sql);

        //获取商品评价数量
        $good_all_num = " select count(1) as all_num from bs_goods_comment  where goods_id ={$info['id']}";
        $good_all_num = $this->goodsCommentMod->querySql($good_all_num);
        $this->assign('good_all_num', $good_all_num[0]);
        //获取评价列表信息
        $eva_sql = 'select  comment_id, username, goods_rank, add_time, content, img, revert  from ' . DB_PREFIX . 'goods_comment
                     where  goods_id  = ' . $info['id'] . ' and store_id = ' . $this->store_id . '   order by comment_id desc ';
        $list = $this->goodsCommentMod->querySql($eva_sql);
        $new_list = array_map(function($vo) {
            $vo['img'] = explode(',', $vo['img']);
            return $vo;
        }, $list);

        //获取国家下属所有店铺
        $storeList = $this->getCountryStore1($this->countryId);
        //客服聊天参数
        $imMod = &m('user');
        $kf_cond = "is_kefu = 1 and kf_status = 1 and store_id = " . $info['store_id'];
        $kf_arr = $imMod->getData(array("cond" => $kf_cond));
        if (is_array($kf_arr)) {
            $key = array_rand($kf_arr);
            $kf_id = $kf_arr[$key]['id'];
        } else {
            $kf_id = "no";
        }
        //组合销售活动
        $zhhdsql = 'SELECT gs.store_goods_id FROM  ' . DB_PREFIX . 'combined_sale AS cs LEFT JOIN  ' . DB_PREFIX . 'combined_goods as gs on gs.com_id=cs.id  where cs.status=1 and  gs.store_goods_id =' . $id;
        $zhhdData = $imMod->querySql($zhhdsql);

        //限时秒杀活动
        $xsmssql = 'SELECT store_goods_id FROM  ' . DB_PREFIX . 'spike_activity  where store_goods_id =' . $id;
        $xsmsData = $imMod->querySql($xsmssql);

        //促销活动
        $cxql = 'SELECT gs.goods_id FROM  ' . DB_PREFIX . 'promotion_sale AS cs LEFT JOIN  ' . DB_PREFIX . 'promotion_goods as gs on gs.prom_id=cs.id  where cs.status=1 and  gs.goods_id =' . $id;
        $cxData = $imMod->querySql($cxql);

        //团购活动
        $tgql = 'SELECT goods_id FROM  ' . DB_PREFIX . 'goods_group_buy where is_end =1 and goods_id =' . $id;
        $tgData = $imMod->querySql($tgql);

        $data = array(
            'langData'=> $langData,
            'info'    => $info,
            'img_arr' => $img_arr,
            'cur_info'=> $cur_info[0],
            'spec_img'=> $spec_img,
            'attr_arr'=> $attr_arr,
            'spec_key'=> $str,
            'promGood'=> $promGood,
            'storeGood'=> $storeGood,
            'list'    => $list,
            'good_all_num' => $good_all_num[0]
        );

        $this->setData($data,1,'');


    }

    /**
     * 图片商品详情
     * @author:tangp
     * @date:2018-09-04
     */
    public function goodsDetails()
    {
        $storeGoodsMod = &m('areaGood');
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->store_id;
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->lang_id;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? $_REQUEST['auxiliary'] : 0;
        $rtid = !empty($_REQUEST['rtid']) ? $_REQUEST['rtid'] : 0;
        $latlon = !empty($_REQUEST['latlon']) ? $_REQUEST['latlon'] : 0;
        $order=!empty($_REQUEST['order']) ? $_REQUEST['order'] : 0;
        $roomtypearr = $this->getgoodRoomTypearr1($lang, $rtid);
        foreach($roomtypearr as $kk=>$vv){
            $roomId[]=$vv['id'];
        }
        $roomIds=implode(',',$roomId);
        $hotGoods=$this->getHotGoods($roomIds,$storeid);
        $this->assign('hotGoods',$hotGoods);
        foreach ($roomtypearr as $key => $val) {
            $where = '  where   s.store_id =' . $storeid . '   and rc.room_type_id = ' . $val['id'] . '  and   s.mark=1   and   s.is_on_sale =1  AND l.`lang_id` = ' . $lang;
            //所以子类的商品
            $rsql = 'SELECT s.id,rc.sort,s.`goods_id`,s.`cat_id`,s.`store_id`,l.`goods_name`,l.`lang_id`,s.`shop_price`,s.`market_price`,s.`brand_id`,gl.`original_img`,s.is_free_shipping,s.add_time,l.goods_remark
                FROM  ' . DB_PREFIX . 'store_goods AS s LEFT JOIN  '
                . DB_PREFIX . 'room_category AS rc on s.cat_id=rc.category_id LEFT JOIN '
                . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id`  left join '
                . DB_PREFIX . 'goods as gl on s.goods_id = gl.goods_id ' . $where;
            $roomtypearr[$key]['goods'] =$storeGoodsMod->querySql($rsql);
        }
        foreach($roomtypearr as $k=>$v){
            foreach($v['goods'] as $k1=>$v1){
//                 print_r($v1['shop_price']);exit;
                //店铺商品打折
                $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $storeid;
                $store_arr = $storeGoodsMod->querySql($store_sql);
                $roomtypearr[$k]['goods'][$k1]['shop_price'] =number_format($v1['shop_price'] * $store_arr[0]['store_discount'],2);
                $oSql="SELECT rec_id,goods_num  FROM ".DB_PREFIX.'order_goods WHERE goods_id='.$v1['id']." and order_state in (20,30,40,50)";
                $oData=$storeGoodsMod->querySql($oSql);
                if(!empty($oData)){
                    $sum=0;
                    foreach($oData as $k2=>$v2){
                        $sum +=$v2['goods_num'];
                    }
                    $roomtypearr[$k]['goods'][$k1]['order_num']=$sum;
                }else{
                    $roomtypearr[$k]['goods'][$k1]['order_num']=0;
                }
                $goodsCommentMod = &m('goodsComment');
                $sql = 'select  sum(goods_rank) / count(goods_rank) as res,count(*) as num from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $v1['id'];
                $trance = $goodsCommentMod->querySql($sql);
                $roomtypearr[$k]['goods'][$k1]['rate'] = (int)$trance[0]['res'];
                $roomtypearr[$k]['goods'][$k1]['num'] = $trance[0]['num'];
            }
        }


        $this->setData($roomtypearr,1,'');

    }
    public function getgoodRoomTypearr1($langid, $rtid) {
        $roomTypeMod = &m('roomType');
        if (!empty($langid)) {
            $where = '    where  t.superior_id=' . $rtid . ' and  l.`lang_id`  = ' . $langid;
        } else {
            $where = '    where  t.superior_id=' . $rtid . ' and  l.`lang_id`  = ' . $this->mrlangid;
        }
        $sql = 'SELECT  t.`id`,l.`type_name`,t.`room_adv_img`  FROM  ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN  ' . DB_PREFIX . 'room_type_lang  AS  l  ON  t.`id` = l.`type_id`  ' . $where . ' order by t.sort';
        $data = $roomTypeMod->querySql($sql);
        return $data;
    }

    public function infoFootPrint($goods_id, $id) {
        $userId = $this->userId;
        $sql = "select id,good_id from  " . DB_PREFIX . "user_footprint where user_id=" . $userId . " and store_good_id=" . $id . " order by adds_time desc";
        $keys = $this->footPrintMod->querySql($sql);
        if (empty($keys)) {
            if ($goods_id != $keys[0]['good_id']) {
                $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->store_id;  //所选的站点id
                $data['user_id'] = $userId;
                $data['good_id'] = $goods_id;
                $data['store_id'] = $storeid;
                $data['adds_time'] = time();
                $data['store_good_id'] = $id;
                $re = $this->footPrintMod->doInsert($data);
            }
        } else {
            $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->store_id;  //所选的站点id
            $data['table'] = "user_footprint";
            $data['cond'] = "id=" . $keys[0]['id'];
            $data['set'] = array(
                'adds_time' => time(),
            );
            $re = $this->footPrintMod->doUpdate($data);
        }
    }

    public function get_spec1($goods_id, $store_goods_id, $type = 1) {
        $storeGoodMod = &m("storeGoodItemPrice");
        //商品规格 价钱 库存表 找出 所有 规格项id
        //$keys = M('SpecGoodsPrice')->where("goods_id", $goods_id)->getField("GROUP_CONCAT(`key` SEPARATOR '_') ");
        if ($type == 1) {
            $sql = "select `key` as item_key from  " . DB_PREFIX . "goods_spec_price where goods_id=" . $goods_id;
        } else {
            $sql = "select `key` as item_key from  " . DB_PREFIX . "store_goods_spec_price where store_goods_id=" . $store_goods_id;
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
            $sql3 = "select spec_image_id,src from " . DB_PREFIX . "goods_spec_image where goods_id=" . $goods_id; // 规格对应的 图片表， 例如颜色
            $img_list = $storeGoodMod->querySql($sql3);
            foreach ($img_list as $k => $v) {
                $specImage[$v['spec_image_id']] = $v['src'];
            }
            $sql4 = "SELECT a.`order`,b.*,al.spec_name,bl.`item_name`  FROM " . DB_PREFIX . "goods_spec AS a
                     INNER JOIN " . DB_PREFIX . "goods_spec_item AS b ON a.id = b.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_lang AS al ON a.id=al.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_item_lang as bl ON b.id=bl.item_id
                     WHERE b.id IN($keys) and al.lang_id=" . $this->lang_id . " and bl.lang_id=" . $this->lang_id . " ORDER BY b.id";
            $filter_spec2 = $storeGoodMod->querySql($sql4);

            foreach ($filter_spec2 as $key => $val) {

                $filter_spec[$key]['spec_name'] = $val['spec_name'];
                $filter_spec[$key]['spec_data']=  array(
                    'item_id' => $val['id'],
                    'item' => $val['item_name']
                );
            }
        }

        $result=array();
        foreach ($filter_spec as $k => $v) {
            $result[$v['spec_name']]['spec_name']=$v['spec_name'];
            $result[$v['spec_name']]['spec_data'][]=$v['spec_data'];
        }

        $result=$this->toIndexArr($result);

        return $result;
    }

    /**
     * 文章详情
     * @author:tangp
     * @date:2018-09-11
     */
    public function article_details()
    {
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';

        $articleMod = &m('article');
        //接受数据
        $artid = !empty($_REQUEST['artid']) ? $_REQUEST['artid'] : '';  // 文章id
        //文章所以分类
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;

        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $langData = array(
            $this->langData->project->information_summary,
            $this->langData->project->cancel_collection
        );
        $artctg = $this->getArticleCtg($lang);

        $sql = 'SELECT * FROM ' . DB_PREFIX . 'article AS a LEFT JOIN ' . DB_PREFIX . 'article_lang AS al ON a.id=al.article_id where a.id=' . $artid . '
       AND al.lang_id=' . $lang;
        $detail = $articleMod->querySql($sql);
        $recommGoods = $this->getRcommGoods($this->storeid);
        // 商品评价星级
        foreach ($recommGoods as $k => $v) {
            $good_id = $v['id'];
            $sql = 'select  sum(goods_rank) / count(goods_rank) as res,count(*) as num from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $good_id;
            $trance = $this->goodsCommentMod->querySql($sql);
            $recommGoods[$k]['rate'] = $trance[0]['res'];
            $recommGoods[$k]['num'] = $trance[0]['num'];
        }
        //更多精彩
        $catid = $detail[0]['cat_id']; //该文章的分类
        $moreArticles = $this->getMoreArticle($this->store_id, $catid, $artid, $limit = 5);
        //收藏文章
        $userId = $this->userId;
        $sql_collection = 'select * from ' . DB_PREFIX . 'user_article where user_id=' . $this->userId . ' and store_id=' . $this->store_id;
//            echo $sql_collection;exit;
        $data_collection = $articleMod->querySql($sql_collection);
//            var_dump($data_collection);exit;
        foreach ($data_collection as &$collertion) {
            if ($collertion['article_id'] == $detail[0]['article_id']) {
                $detail[0]['type'] = 1;
            }
        }
        $data = array(
            'langData' => $langData,
            'listData' => $detail[0]
        );
        $this->setData($data,1,'');
    }
    /**
     * 更多精彩 5条
     * @author wangh
     * @date 2017/09/13
     */
    public function getMoreArticle($storeid, $catid, $artid, $limit = 5) {
        $articleMod = &m('article');

        if (!empty($catid)) {
            $where = '  where   a.store_id =' . $storeid . '  and   a.cat_id  in(' . $catid . ')  and  a.id !=' . $artid;
        } else {
            $where = '  where   a.store_id =' . $storeid . '  and  a.id !=' . $artid;
        }

        $sql = 'SELECT  a.id,a.`title`,a.`english_title`,a.`cover_photo`,a.`brief`,a.store_id,a.br_num,c.`id`  AS  cid,c.`name`
                 FROM   ' . DB_PREFIX . 'article AS a LEFT JOIN  ' . DB_PREFIX . 'article_category AS c ON a.`cat_id` = c.`id` ' . $where;
        $sql .= '  order  by  a.id  desc  limit ' . $limit;
        $res = $articleMod->querySql($sql);
        $data = array();
        $total = 0;
        if (!empty($res)) {
            foreach ($res as $key => $val) {
                $data[] = $val;
                $total++;
            }
        }
        //如果不够5条
        if ($total < 5) {
            $sql2 = 'SELECT  a.id,a.`title`,a.`english_title`,a.`cover_photo`,a.`brief`,a.store_id,a.br_num,c.`id`  AS  cid,c.`name`
                 FROM   ' . DB_PREFIX . 'article AS a LEFT JOIN  ' . DB_PREFIX . 'article_category AS c ON a.`cat_id` = c.`id`
                 where  a.store_id =' . $storeid . '   and  a.id!=' . $artid . '  and  a.cat_id  not in(' . $catid . ')  order by a.id  desc  limit ' . ($limit - $total );

            $res2 = $articleMod->querySql($sql2);
            if (!empty($res2)) {
                foreach ($res2 as $v) {
                    $data[] = $v;
                }
            }
        }

        return $data;
    }
    /**
     * 商品推荐 取销量前5的
     * @author wangh
     * @date 2017/09/13
     */
    public function getRcommGoods($storeid) {
        $storeGoodsMod = &m('areaGood');
        $limit = '  limit  5';
        $where = '  where   mark =1  and  store_id =' . $storeid . '  and   is_on_sale =1';
        $sql = 'select g.*,l.*, g.id,gl.original_img  from  '
            . DB_PREFIX . 'store_goods as g inner join '
            . DB_PREFIX . 'goods as gl on g.goods_id = gl.goods_id inner join '
            . DB_PREFIX . 'goods_lang as l on l.goods_id = g.goods_id and l.lang_id=' . $this->langid
            . $where;
        $sql .= '  order by l.id  desc' . $limit;
        $data = $storeGoodsMod->querySql($sql);
        foreach ($data as &$item) {
            //是否包邮
            switch ($item['is_free_shipping']) {
                case 1:
                    $item['isfree'] = $a['article_Free'];
                    break;
                case 2:
                    $item['isfree'] = $a['article_No'];
                    break;
                default:
                    $item['isfree'] = $a['article_No'];
            }
            //
        }
        return $data;
    }

    /**
     * 文章分类
     * @author wangh
     * @date 2017/09/19
     */
    public function getArticleCtg($lang) {
        $artCtgMod = &m('articleCate');
        $sql = 'SELECT a.id,ac.article_cate_name,ac.lang_id FROM ' . DB_PREFIX . 'article_category AS a LEFT JOIN ' . DB_PREFIX . 'article_category_lang AS ac ON a.id=ac.article_cate_id where ac.lang_id=' . $lang;
        $data = $artCtgMod->querySql($sql);

        return $data;
    }

    public function getType(){
        /*        $goodMod = &m('goods');
                $storeGoodMod = &m("storeGoodItemPrice");*/
        $storeGoods = &m('areaGood');
        $id=$_REQUEST['id'];
        $goods_id=$_REQUEST['goods_id'];
        /*  $storeid=$_REQUEST['storeid'];*/
        $lang=$_REQUEST['lang_id'];
        $source=$_REQUEST['source'];
        $cid=$_REQUEST['cid'];
        $latlon=$_REQUEST['latlon'];
        $store_id = $_REQUEST['store_id'];
        $goodinfo = $storeGoods->getLangInfo1($id, $lang);
        if ($source == 3) {
            $sql = " select ps.*,pg.goods_key as item_id,pg.goods_price,pg.discount_price,pg.limit_amount,ps.status  from " . DB_PREFIX . "promotion_sale as ps left join " . DB_PREFIX . "promotion_goods as pg on 
                ps.id = pg.prom_id  where ps.`store_id` = $this->storeid and ps.`status` in (1,2) and ps.`mark` =1 and ps.id =$cid  and pg.goods_id=".$id;
            $arr = $storeGoods->querySql($sql);
            foreach ($arr as $k => $v) {
                $arr1 = explode('_', $v['item_id']);

            }


        }
        // 团购
        if ($source == 2) {
            $where2 = '  where  store_id = ' . $store_id . ' and  mark =1 and id=' . $cid;
            $sql2 = 'SELECT  goods_spec_key as item_id,group_goods_price,goods_price,group_goods_num,virtual_num,end_time,title as name,start_time
                FROM  ' . DB_PREFIX . 'goods_group_buy ' . $where2;
            $arr = $storeGoods->querySql($sql2);
            foreach ($arr as $v) {
                $arr1 = explode('_', $v['item_id']);
            }}
        $spec_img1 = $this->get_spec1($goodinfo['goods_id'], $id, 2);

        if (!empty($arr1)) {
            foreach ($spec_img1 as $key => $value) {
                foreach ($value as $k => $v) {
                    if (!in_array($v['item_id'], $arr1))
                        unset($spec_img1[$key][$k]);
                }
            }
        }

        foreach($spec_img1 as $key1=>$value1){
            foreach($value1 as $k1=>$v1){
                $spec_img[$key1][]=$v1;
            }
        }

        $storeList = $this->getCountryStore($this->countryId,$goods_id,$latlon);
        $info=array('spec_img'=>$spec_img,'info'=>$goodinfo,'lang'=>$lang);
        $this->setData($info,1,'');

    }
        //
    public function  getRoomGoods(){
        $roomId=!empty($_REQUEST['roomId']) ? $_REQUEST['roomId'] : 85;
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : 47;
        $roomIds=!empty($_REQUEST['roomids']) ? $_REQUEST['roomids']: '84,85,110,122,128';
        $type=!empty($_REQUEST['type'])? $_REQUEST['type']: 2; //type=1 促销商品   2：热卖商品 3;分类商品

        $storeGoodsMod = &m('areaGood');
        if($type==1){
            $activiData=$this->getACtivity1($roomIds,$storeid);
            $goodsCommentMod = &m('goodsComment');
            foreach($activiData['data'] as $key=>$val){
                $oSql="SELECT rec_id,goods_num FROM ".DB_PREFIX.'order_goods WHERE goods_id='.$val['gid']." and order_state in (20,30,40,50)";
                $oData=$storeGoodsMod->querySql($oSql);
                if(!empty($oData)){
                    $num=0;
                    foreach($oData as $k=>$v){
                        $num+=$v['goods_num'];
                    }
                    $activiData['data'][$key]['order_num']=$num;
                }else{
                    $activiData['data'][$key]['order_num']=0;
                }

                $sql = 'select  sum(goods_rank) / count(goods_rank) as res,count(*) as num from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $val['gid'];
                $trance = $goodsCommentMod->querySql($sql);
                $activiData['data'][$key]['rate']=(int)$trance[0]['res'];
            }
            $gooodInfo=array('data'=>$activiData,'type'=>1);
            if($activiData){
                $this->setData($gooodInfo,1,'');
            }
        }
        if($type==2){
            $hotGoods=$this->getHotGoods1($roomIds,$storeid);
            $gooodInfo=array('data'=>$hotGoods,'type'=>2);
            if($hotGoods){
                $this->setData($gooodInfo,1,'');
            }
        }
        if($type==3){
            $where = '  where   s.store_id =' . $storeid . '   and rc.room_type_id = ' . $roomId . '  and   s.mark=1   and   s.is_on_sale =1  AND l.`lang_id` = ' . $this->lang_id;
            //所以子类的商品
            $rsql = 'SELECT s.id,rc.sort,s.`goods_id`,s.`cat_id`,s.`store_id`,l.`goods_name`,l.`lang_id`,s.`shop_price`,s.`market_price`,s.`brand_id`,gl.`original_img`,s.is_free_shipping,s.add_time,l.goods_remark,s.goods_storage
                FROM  ' . DB_PREFIX . 'store_goods AS s LEFT JOIN  '
                . DB_PREFIX . 'room_category AS rc on s.cat_id=rc.category_id LEFT JOIN '
                . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id`  left join '
                . DB_PREFIX . 'goods as gl on s.goods_id = gl.goods_id ' . $where;
            $goods =$storeGoodsMod->querySql($rsql);

            foreach($goods as $k1=>$v1){
                //店铺商品打折
                $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $this->store_id;
                $store_arr = $storeGoodsMod->querySql($store_sql);
                $goods[$k1]['shop_price'] =number_format($v1['shop_price'] * $store_arr[0]['store_discount'],2);
                $member_price=$v1['shop_price'] * $store_arr[0]['store_discount']-($this->getPointAccount($v1['shop_price'] * $store_arr[0]['store_discount'],$storeid));
                $goods[$k1]['member_price'] =number_format($member_price,2);
                $goods[$k1]['sale_price'] =number_format($v1['shop_price'],2);
                $oSql="SELECT rec_id,goods_num  FROM ".DB_PREFIX.'order_goods WHERE goods_id='.$v1['id']." and order_state in (20,30,40,50)";
                $oData=$storeGoodsMod->querySql($oSql);
                if(!empty($oData)){
                    $sum=0;
                    foreach($oData as $k2=>$v2){
                        $sum +=$v2['goods_num'];
                    }
                    $goods[$k1]['order_num']=$sum;
                }else{
                    $goods[$k1]['order_num']=0;
                }
                $goodsCommentMod = &m('goodsComment');
                $sql = 'select  sum(goods_rank) / count(goods_rank) as res,count(*) as num from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $v1['id'];
                $trance = $goodsCommentMod->querySql($sql);
                $goods[$k1]['rate'] = (int)$trance[0]['res'];
                $goods[$k1]['num'] = $trance[0]['num'];
            }
            $gooodInfo=array('data'=>$goods,'type'=>3);
            if($goods){
                $this->setData($gooodInfo,1,'');
            }
        }


    }

    //活动商品
    public function getACtivity1($roomIds,$storeid){
        //秒杀商品
        $seckMod = &m('spikeActivity');
        $goodsByMod = &m('groupbuy');
        $goodPromMod = &m('goodProm');
        $storeGoodsMod = &m('areaGood');
        $curtime = time();
        $today = strtotime(date('Y-m-d', time()));
        $now = $curtime - $today;


        $where1 =  ' where s.store_id =' . $storeid . '  and   s.start_time <= ' . $curtime . ' and   s.end_time  >= ' . $curtime .' and rc.room_type_id in ('.$roomIds.') and g.is_on_sale =1 and g.mark=1 and l.lang_id ='.$this->lang_id ;
        $sql1 = 'SELECT  s.id as cid,s.`name`,s.start_time,s.end_time,s.start_our,s.end_our,s.store_id,s.store_goods_id as id,gl.original_img,s.content,s.item_name,s.item_key,s.discount,s.o_price,s.price,s.goods_num,g.is_free_shipping,l.goods_remark,gl.goods_id as good_id,g.id as gid,rc.room_type_id,g.is_on_sale,g.mark,g.goods_storage FROM  '
            . DB_PREFIX . 'spike_activity as s left join '
            . DB_PREFIX . 'store_goods as g on  s.store_goods_id = g.id  LEFT JOIN  '
            . DB_PREFIX . 'room_category AS rc on g.cat_id=rc.category_id LEFT JOIN '
            . DB_PREFIX . 'goods AS gl ON g.`goods_id` = gl.`goods_id` LEFT JOIN  bs_goods_lang AS l ON g.`goods_id` = l.`goods_id`  '. $where1;

        $spikeArr = $seckMod->querySql($sql1);
        foreach($spikeArr as $key=>$val){
            $spikeArr[$key]['source']=1;
            $child_info = $storeGoodsMod->getLangInfo($val['id'], $this->lang_id);
            if ($child_info) {
                $k_name = $child_info['goods_name'];
                $spikeArr[$key]['goods_name'] = $k_name;
            }
            $member_price=$val['price']-($this->getPointAccount($val['price'],$storeid));
            $spikeArr[$key]['member_price']=number_format($member_price,2);

        }

        //团购商品
        $where3 = 'WHERE  l.`lang_id` = ' . $this->lang_id . '  and  b.store_id =' . $this->store_id . '  AND b.is_end =1 AND b.mark = 1  and rc.room_type_id in ('.$roomIds.') and g.is_on_sale=1 and g.mark=1 ';
        $sql3 = 'SELECT b.title as name, b.id as cid,b.goods_id as id,b.store_id,b.end_time,b.group_goods_price as price,b.virtual_num,gl.original_img,b.goods_price as o_price,l.goods_name,b.goods_spec_key as item_key,gl.goods_id as good_id,g.id as gid,g.goods_storage
                FROM  bs_goods_group_buy  AS b  LEFT JOIN   bs_store_goods AS g ON b.`goods_id` = g.id 
                LEFT JOIN  bs_goods_lang AS l ON g.`goods_id` = l.`goods_id`   LEFT JOIN  '
            . DB_PREFIX . 'room_category AS rc on g.cat_id=rc.category_id LEFT JOIN '
            . DB_PREFIX . 'goods AS gl ON g.`goods_id` = gl.`goods_id`'. $where3;

        $groupByGoodArr = $goodsByMod->querySql($sql3);
        foreach($groupByGoodArr as $key=>$val){
            $groupByGoodArr[$key]['source']=2;
            $member_price=$val['price']-($this->getPointAccount($val['price'],$storeid));
            $groupByGoodArr[$key]['member_price']=number_format($member_price,2);
        }
        $this->checkOver();
        // 获取正在进行或者未开始的促销活动
        $sql4 = " select  ps.prom_name as name, ps.id as cid,ps.*,pg.goods_id as id,pg.goods_key as item_key,pg.goods_key_name,l.goods_name,gl.original_img,pg.goods_price as o_price,pg.discount_price as price,s.is_free_shipping,gl.goods_id as good_id,s.id as gid,pg.limit_amount,l.goods_remark,s.goods_storage  from "
            . DB_PREFIX . "promotion_sale as ps left join "
            . DB_PREFIX . "promotion_goods as pg on  ps.id = pg.prom_id left join "
            . DB_PREFIX . "store_goods as s on pg.goods_id = s.id  LEFT JOIN "
            . DB_PREFIX . "goods AS gl ON s.`goods_id` = gl.`goods_id` LEFT JOIN  bs_goods_lang AS l ON s.`goods_id` = l.`goods_id` LEFT JOIN "
            . DB_PREFIX . "room_category AS rc on s.cat_id=rc.category_id 
             where ps.`store_id` = $this->store_id and ps.`status` in (1,2) and ps.`mark` =1  and rc.room_type_id in ($roomIds) and s.is_on_sale = 1 and s.mark = 1  and l.lang_id = ".$this->lang_id."  order by ps.status desc,ps.id desc";
        $promotionGoodsArr = $goodPromMod->querySql($sql4);
        foreach($promotionGoodsArr as $key=>$val){
            $promotionGoodsArr[$key]['source']=3;
            $member_price=$val['price']-($this->getPointAccount($val['price'],$storeid));
            $promotionGoodsArr[$key]['member_price']=number_format($member_price,2);
        }
        $res = array();
        $res['data'] = array_merge($spikeArr, $groupByGoodArr, $promotionGoodsArr);
        return $res;
    }

    //热销商品
    public function getHotGoods1($roomIds,$storeid){
        $storeGoodsMod = &m('areaGood');
        $where = '  where     s.store_id =' . $storeid . '   and rc.room_type_id in ('.$roomIds.')  and   s.mark=1    and   s.is_on_sale =1  AND l.`lang_id` = ' . $this->lang_id ;
        //所以子类的商品
        $hsql = 'SELECT s.id,s.`goods_id`,l.`goods_name`,l.`lang_id`,s.`shop_price`,s.`market_price`,gl.`original_img`,s.goods_storage,l.goods_remark
                FROM  ' . DB_PREFIX . 'store_goods AS s LEFT JOIN  '
            . DB_PREFIX . 'room_category AS rc on s.cat_id=rc.category_id LEFT JOIN '
            . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id`  left join '
            . DB_PREFIX . 'goods as gl on s.goods_id = gl.goods_id' . $where;
        $hotData=$storeGoodsMod->querySql($hsql);
        foreach($hotData as $k=>$v){
            $oSql="SELECT rec_id,goods_num  FROM ".DB_PREFIX.'order_goods WHERE goods_id='.$v['id']." and order_state in (20,30,40,50)";
            $oData=$storeGoodsMod->querySql($oSql);
            if(!empty($oData)){
                $sum=0;
                foreach($oData as $k1=>$v1){
                    $sum +=$v1['goods_num'];
                }
                $hotData[$k]['order_num']=$sum;
            }else{
                $hotData[$k]['order_num']=0;
            }
            $goodsCommentMod = &m('goodsComment');
            $sql = 'select  sum(goods_rank) / count(goods_rank) as res,count(*) as num from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $v['gid'];
            $trance = $goodsCommentMod->querySql($sql);
            $hotData[$k]['rate']=(int)$trance[0]['res'];;
            $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $storeid;
            $store_arr = $storeGoodsMod->querySql($store_sql);
            $member_price=$v['shop_price'] * $store_arr[0]['store_discount']-($this->getPointAccount($v['shop_price'] * $store_arr[0]['store_discount'],$storeid));
            $hotData[$k]['member_price']=number_format($member_price,2);
            $hotData[$k]['shop_price'] = number_format($v['shop_price'] * $store_arr[0]['store_discount'],2);
            $hotData[$k]['sale_price']=number_format($v['shop_price'],2);
        }
        $sort = array(
            'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
            'field' => 'order_num', //排序字段
        );
        $arrSort = array();
        foreach ($hotData AS $uniqid => $row) {
            foreach ($row AS $key => $value) {
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if ($sort['direction']) {
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $hotData);
        }
        $hotData=array_slice($hotData,0,4);
        return $hotData;
    }

    public function  getPointAccount($total,$storeid){
        $pointSiteMod = &m('point');
        $storePointMod = &m('storePoint');
        $storeMod = &m('store');
        $curMod = &m('currency');
        //获取订单总金额
        //获取最大积分支付比例
        $point_price_site = $pointSiteMod->getOne(array("cond" => "1=1"));
        $store_point_site = $storePointMod->getOne(array("cond" => "store_id=" . $storeid));
        $point_price_site['point_price'] = empty($store_point_site) ? 0 : $store_point_site['point_price'];
        if ($point_price_site) {
            $point_price = $point_price_site['point_price'] * $total / 100; //积分兑换最大金额
            $rmb_point = $point_price_site['point_rate']; //积分和RMB的比例
        } else {
            $point_price = 0;
            $rmb_point = 0;
        }
//获取当前店铺币种以及兑换比例
        $store_info = $storeMod->getOne(array("cond" => "id=" . $storeid));
//获取当前币种和RMB的比例
        $rate = $curMod->getCurrencyRate($store_info['currency_id']);
        return $point_price;
    }

    //获取规格价格库存
    public function getGoodsSpec(){
        $id=!empty($_REQUEST['store_goods_id']) ? $_REQUEST['store_goods_id'] : '6937';
        $storeGoodMod = &m("storeGoodItemPrice");
        $storeMod=&m('areaGood');
        $spec_data = $storeGoodMod->getData(array('cond' => "store_goods_id=" . $id));
        if(!empty($spec_data)){
            $info=array('id'=>$id,'goodInfo'=>$spec_data);
            $this->setData($info,1,'');
        }else{
            $goods_data=$storeMod->getData(array('cond'=>"id=".$id));
            $info=array('id'=>$id,'goodInfo'=>$goods_data);
            $this->setData($info,1,'');
        }

    }

    public function spikeActivityGoodsDetail()
    {
        $id = !empty($_REQUEST['store_goods_id']) ? $_REQUEST['store_goods_id'] : '';//store_goods_id
        $time_point = !empty($_REQUEST['time_point']) ? $_REQUEST['time_point'] : '';//time_point
        $activityId = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '';//活动id
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;//区域店铺id
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;//语言id
        if (empty($id)){
            $this->setData(array(),0,'请传递区域商品id！');
        }
        if (empty($activityId)){
            $this->setData(array(),0,'请传递秒杀活动id！');
        }
        if (empty($store_id)){
            $this->setData(array(),0,'请传递店铺id！');
        }
        if (empty($time_point)){
            $this->setData(array(),0,'请传递商品所属时间段！');
        }
        if (empty($lang_id)){
            $this->setData(array(),0,'请传递语言id！');
        }

        $goodsImgMod = &m('goodsImg');
        $goodMod = &m('goods');
        $storeGoodsMod = &m('storeGoods');
        $storeGoods = &m('areaGood');
        $spikeActiviesGoodsMod = &m('spikeActiviesGoods');
        $_time = $spikeActiviesGoodsMod::$time;
        if(!array_key_exists($time_point,$_time)){
            $this->setData(array(),0,'时间段不存在！');
        }
        $spikeActivityMod = &m('spikeActivity');
        $goodAttrMod = &m('goodsAttriInfo');
        $sql = "SELECT goods_id FROM bs_store_goods WHERE id = {$id}";
        $res = $storeGoodsMod->querySql($sql);
        $img_arr = $goodsImgMod->getData(array('cond'=>"goods_id=".$res[0]['goods_id']));//拿轮播图

        if(empty($res)){
            $this->setData(array(),0,'商品已下架！');
        }

        $sql_test = 'select * from bs_spike_activity where id = '. $activityId;
        $data_test = $spikeActivityMod->querySql($sql_test);
        if(empty($data_test)){
            $this->setData(array(),0,'活动不存在！');
        }
        $end = $data_test[0]['end_time'];
        $_time = $spikeActiviesGoodsMod::$time;
        $_end = strtotime(date('Y-m-d'). ' ' . $_time[$time_point] . ':00:00') + 3600 * 2 - 1;
//        var_dump($data_test);
        $end_time= $data_test[0]['end_time'];
        $start_time = $data_test[0]['start_time'];
        $time = time();

        $goods_name = $spikeActiviesGoodsMod->getData(array('cond'=>"store_goods_id={$id} and spike_id = {$activityId} and mark=1"));
        if(empty($goods_name)){
            $this->setData(array(),0,'活动商品不存在');
        }
        $goods_key_name = explode(':',$goods_name[0]['goods_key_name']);
        foreach ($goods_key_name as $key => $value){
            if (!$value){
                unset($goods_key_name[$key]);
            }
        }

        $spec_arrs = array_values($goods_key_name);

        $goods_keys = array(
            'goods_key' => $goods_name[0]['goods_key'],
            'goods_key_name' => $goods_name[0]['goods_key_name']
        );
//        var_dump($goods_name);die;
        $info = $storeGoods->getLangInfo($id,$this->lang_id);

        $attr_arr = $goodAttrMod->getLangData($info['goods_id'],$this->lang_id);
        $attr = $goods_name[0]['goods_key_name'];
        $rand = $this->buildNo(1);
        $orderNo = date('YmdHis') . $rand[0];
        $listData = array(
            'spec_arrs'      => $spec_arrs,
            'goods_keys'     => $goods_keys,
            'img_arr'        => $img_arr,
            'goods_name'     => $goods_name[0]['goods_name'],
            'goods_num' => $goods_name[0]['goods_num'],
            'goods_price'    => $goods_name[0]['goods_price'],
            'limit_num'      => $goods_name[0]['limit_num'],
            'info'           => $info,
            'symbol'         => $this->symbol,
            'discount_price' => $goods_name[0]['discount_price'],
            'id'             => $id,
            'source'         => 1,
            'activityId'     => $activityId,
            'activityGoodsId'=> $goods_name[0]['id'],
            'lang_id'        => $lang_id,
            'store_id'       => $store_id,
            'end'            => $_end,
            'attr_arr'       => $attr_arr,
            'orderSn'       =>$orderNo
        );
        $this->setData($listData,1,'');
    }

    public function getBuyNums()
    {
        $activityGoodsId = $_REQUEST['activityGoodsId'];
        $num = $_REQUEST['num'];
        $id = $_REQUEST['id'];
        $activityId = $_REQUEST['activityId'];
        $userId = $this->userId;
        $source = $_REQUEST['source'];
        $spikeActiviesGoodsMod = &m('spikeActiviesGoods');
        $orderGoodsMod = &m('orderGoods');
        $storeGoodsMod = &m('storeGoods');
        $storeGoodsSpecPriceMod = &m('storeGoodsSpecPrice');
        $sql = "select * from bs_spike_goods where id=".$activityGoodsId;
        $res = $spikeActiviesGoodsMod->querySql($sql);
        //限购判断
        if ($num > $res[0]['limit_num']){
            $this->setData(array(),0,'你选择的数量大于该商品的限购数量！');
        }
//        $nums = $orderGoodsMod->getActivityOrderNum($source,$activityId,$id,$userId);
        if (!empty($userId)){
            $sql4="select sum(og.goods_num) as total from ".DB_PREFIX.'order  as o  left join '.DB_PREFIX.'order_goods as og ON og.order_id = o.order_sn 
            where og.prom_type='.$source.' and og.prom_id='.$activityId.' and og.goods_id='.$id.' and o.mark=1 and o.order_state >=20'.' and og.buyer_id='.$userId;
            $ac = $orderGoodsMod->querySql($sql4);
            $nums = $ac[0]['total'];
            //购买限购
            if ($res[0]['limit_num'] - $nums < $num){
                $this->setData(array(),0,"限购{$res[0]['limit_num']}件");
            }
        }
        //查库存足不足
        if (empty($res[0]['goods_key'])  && empty($res[0]['goods_key_name'])){
            $sql2 = "SELECT goods_storage FROM bs_store_goods WHERE id=".$id;//无规格的查库存
            $goodsInfo = $storeGoodsMod->querySql($sql2);
            if ($goodsInfo[0]['goods_storage'] == 0){
                $this->setData(array(),0,'该商品的库存不足！');
            }
            if ($num > $goodsInfo[0]['goods_storage']){
                $this->setData(array(),0,'该商品的库存不足！');
            }
        }else{
//            $sql3 = "SELECT goods_storage FROM bs_store_goods_spec_price WHERE store_goods_id={$id} AND `key`='{$res[0]['goods_key']}'";//有规格查库存
            $goods_key = $res[0]['goods_key'];
            $info = $storeGoodsSpecPriceMod->getOne(array('cond' =>"store_goods_id = '{$id}' and `key` ='{$goods_key}'"));

            if ($info['goods_storage'] == 0){
                $this->setData(array(),0,'该商品的库存不足！');
            }
            if ($num > $info['goods_storage']){
                $this->setData(array(),0,'该商品的库存不足！');
            }
        }

        $this->setData(array(),1,'');
    }

    /**
     * 退款首页
     * @author tangp
     * @date 2019-02-28
     */
    public function refundIndex()
    {
        $orderMod = &m('order');
        $orderGoodsMod = &m('orderGoods');
        $storeMod = &m('store');
        $storeid = !empty($_REQUEST['store_id']) ? htmlspecialchars($_REQUEST['store_id']) : $this->store_id;
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars($_REQUEST['order_sn']) : '';
        $lang = !empty($_REQUEST['lang_id']) ? htmlspecialchars($_REQUEST['lang_id']) : '';
        $user_id = $this->userId;
        if (empty($storeid)){
            $this->setData(array(),0,'请传递店铺id！');
        }
        if (empty($order_sn)){
            $this->setData(array(),0,'请传递订单号！');
        }
        if (empty($lang)){
            $this->setData(array(),0,'请传递语言id！');
        }
//        $data = $orderMod->getOrderInfo($user_id,$order_sn,$storeid,$lang);
        $where = ' buyer_id =' . $user_id . " and order_sn = '{$order_sn}'";
        $sql = 'select * from ' . DB_PREFIX . 'order'
            . ' where' . $where . ' and store_id =' . $storeid;

        $data = $orderMod->querySql($sql);
        if ($data[0]['payment_code'] == 'wxpay'){
            $data[0]['refund_path'] = '微信';
        }else if($data[0]['payment_code'] == 'aliPay'){
            $data[0]['refund_path'] = '支付宝';
        }else if($data[0]['payment_code'] == '现金付款'){
            $data[0]['refund_path'] = '现金';
        }else if($data[0]['payment_code'] == '余额支付'){
            $data[0]['refund_path'] = '余额';
        }else if($data[0]['payment_code'] == '免费兑换'){
            $data[0]['refund_path'] = '免费兑换';
        }
        foreach ($data as $k => $v) {
            $sql = "select o.*,o.goods_id as ogoods_id,l.* from "
                . DB_PREFIX . "order_goods as o left join "
                . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
                . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id "
                . " where o.order_id='{$v['order_sn']}'  and o.refund_state = 0  and lang_id = " . $lang;
            $list = $orderGoodsMod->querySql($sql);

            foreach ($list as $kk => $vv){
                $list[$kk]['refund_price'] = number_format(($list[$kk]['goods_pay_price']/$data[$k]['goods_amount']) * $data[$k]['order_amount'],2,'.','');
            }
            $data[$k]['goods_list'] = $list;


        }
        $storeImage = $storeMod->getOne(array('cond' => "id = '{$storeid}'"));

        $listData = array(
            'data' => $data,
            'storeImage' => $storeImage['logo']
        );

        $this->setData($listData,1,'');
    }

    public function submit()
    {
        $orderRefundMod = &m('orderRefund');
        $orderMod = &m('order');
        $orderGoodsMod = &m('orderGoods');
        $orderRefundGoodsMod = &m('orderRefundGoods');
        $refund_amount = !empty($_REQUEST['refund_amount']) ? $_REQUEST['refund_amount'] : '';
        $reason_info      = !empty($_REQUEST['reason_info']) ? $_REQUEST['reason_info'] : '';
        $order_id      = !empty($_REQUEST['order_id']) ? $_REQUEST['order_id'] : '';
        $refund_goods_ids  = !empty($_REQUEST['refund_goods_ids']) ? $_REQUEST['refund_goods_ids'] : '';
        $refund_amounts = !empty($_REQUEST['refund_amounts']) ? $_REQUEST['refund_amounts'] : '';
        $images = !empty($_REQUEST['images']) ? $_REQUEST['images'] : '';
        $sql = "SELECT * FROM bs_order_refund WHERE order_sn = {$order_id}";
        $info = $orderMod->querySql($sql);
        if ($info){
            $this->setData(array(),0,'该订单已申请退款！不可重复提交！');
        }
        if (empty($refund_amount)){
            $this->setData(array(),0,'请传递退款总金额！');
        }
        if (empty($reason_info)){
            $this->setData(array(),0,'请传递退款理由！');
        }
        if (empty($order_id)){
            $this->setData(array(),0,'请传递订单id！');
        }
        if (empty($refund_goods_ids)){
            $this->setData(array(),0,'请传递退款商品ID！');
        }
        if (empty($refund_amounts)){
            $this->setData(array(),0,'请传递单独退款金额！');
        }

        $storeGoodsArr = explode(',',$refund_goods_ids);
//        echo '<pre>';print_r($storeGoodsArr);die;
        $orderInfo = $orderMod->getOne(array('cond'=>"order_id = '{$order_id}'"));

        $arrs = array();
        $newArr = array();
        foreach ($storeGoodsArr as $k => $v){
            $sql = "SELECT goods_id,goods_name,goods_image,spec_key_name,goods_pay_price,goods_num FROM bs_order_goods 
                    WHERE `order_id`='{$order_id}' AND `goods_id`=".$v;
            $result = $orderGoodsMod->querySql($sql);

            $arrs[] = $result;
        }
//        echo '<pre>';print_r($arrs);die;
        foreach ($arrs as $k => $val){
            $newArr[] = $val[0];
        }
        foreach ($newArr as $k => $v){
            $newArr[$k]['market_price'] = $orderRefundMod->getMarketPrice($v['goods_id']);
        }
        $refundData = array(
            'refund_amount'   => $refund_amount,
            'reason_info'     => urlencode($reason_info),
            'order_sn'        => $order_id,
            'refund_goods_ids' => $refund_goods_ids,
//            'refund_amounts'  => $refund_amounts,
            'add_user'        => $this->userId,
            'add_time'        => time(),
            'refund_images'   => $images
        );

        $insert_id = $orderRefundMod->doInsert($refundData);

        foreach ($newArr as $key => $val){
            $refundGoodsData = array(
                'order_refund_id' => $insert_id,
                'goods_name'      => $val['goods_name'],
                'goods_price'     => $val['market_price'],
                'goods_num'       => $val['goods_num'],
                'goods_image'     => $val['goods_image'],
                'goods_pay_price' => $val['goods_pay_price'],
                'spec_key_name'   => $val['spec_key_name']
            );

            $result = $orderRefundGoodsMod->doInsert($refundGoodsData);
        }

        $order_data = array(
            "table" => "order",
            "cond"  => "order_sn= '{$order_id}'",
            "set"   => array(
                "refund_state" => 1,
                "refund_amount"=> $refund_amount
            )
        );

        $order_goods_data = array(
            "table" => "order_goods",
            "cond"  => "order_id='{$order_id}'". " and refund_state = 0",
            "set"   => array(
                "refund_state" => 1
            )
        );
        $sql = "SELECT `store_id` FROM bs_user_order WHERE order_sn = {$order_id}";
        $userOrderData = $orderMod->querySql($sql);

        $ress = $orderMod->doUpdate($order_data);

        $rr = $orderGoodsMod->doUpdate($order_goods_data);

        $rrs = $orderMod->update_refund_time($userOrderData[0]['store_id'],$order_id,1);

        if($insert_id && $result && $ress && $rr && $rrs){
            $this->setData(array(),1,'提交成功！');
        }else{
            $this->setData(array(),0,'提交失败！');
        }
    }
}
