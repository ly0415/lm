<?php

/**
 * 用户中心
 * @author lvji
 *
 */
//include_once 'sms.app.php';
//include_once 'mail.app.php';
class GoodsApp extends BaseWxApp {

    private $goodsCommentMod;

    public function __construct() {
        parent::__construct();
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $this->assign('storeid', $storeid);
        $this->footPrintMod = &m('footprint');
        $this->goodsCommentMod = &m('goodsComment');
    }

    /*
     * 商品详情页
     * @author lee
     * @date 2017-8-11 10:22:12
     */

    public function goodInfo() {
        $source = !empty($_REQUEST['source']) ? intval($_REQUEST['source']) : 0;
        $cid = !empty($_REQUEST['cid']) ? intval($_REQUEST['cid']) : 0;
        $goods_key = !empty($_REQUEST['goods_key']) ? $_REQUEST['goods_key'] : '';
        $storeGoods = &m('areaGood');
        $this->load($this->shorthand, 'WeChat/goods');
        $tp = !empty($_REQUEST['tp']) ? $_REQUEST['tp'] : 0;  //区别授权的
        if( $tp ){
            //判断是否登录
            header('Location: wx.php?app=user&act=loginOuth&back_app=' . APP . '&back_act=' . ACT . '&storeid=' . $this->storeid . '&lang=' . $this->langid . '&latlon=' . $_REQUEST['latlon']. '&auxiliary=' . $_REQUEST['auxiliary']. '&source=' . $source. '&cid=' . $cid. '&gid=' . $_REQUEST['gid']. '&goods_tp=1');
            exit;
        }

        if ($_REQUEST['goods_id']) {
            $g_info = $storeGoods->getOne(array("cond" => "goods_id=" . $_REQUEST['goods_id'] . " and store_id=" . $this->storeid . " and mark =1"));
            $id = $g_info['id'];
        } else {
            $id = ($_REQUEST['gid']) ? $_REQUEST['gid'] : 0;
        }
        if (empty($id)) {
            $this->display("public/goods-error.html");
        }

        $fxCode = ($_REQUEST['fxCode']) ? $_REQUEST['fxCode'] : '';
        $goodMod = &m('goods');
        $goodClassMod = &m('goodsClass');
        $goodAttrMod = &m('goodsAttriInfo');
        $goodImgMod = &m('goodsImg');
        $storeGoodMod = &m("storeGoodItemPrice");
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        //商品信息
        $info = $storeGoods->getLangInfo($id, $this->langid);
//        echo '<pre>';print_r($info);die;
        if (empty($info)) {
            $this->display("public/goods-error.html");
        }
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
                $new_info = $storeGoods->getLangInfo($v['store_goods_id'], $this->langid);
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
            . DB_PREFIX . "goods AS gl ON s.`goods_id` = gl.`goods_id` where  (store_id = {$this->storeid} and s.is_on_sale = 1 and s.mark = 1 and id !={$id}) and
                       (" . $where . ")";

        $store_good = $goodMod->querySql($store_sql);
        foreach ($store_good as $k => $v) {
            $new_info_2 = $storeGoods->getLangInfo($v['id'], $this->langid);
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
        $sql_collection = 'select * from ' . DB_PREFIX . 'user_collection where user_id=' . $this->userId . ' and store_id=' . $this->storeid;
        $data_collection = $storeGoods->querySql($sql_collection);
        foreach ($data_collection as &$collertion) {
            if ($collertion['store_good_id'] == $info['id']) {
                $info['type'] = 1;
            }
        }
        $goods_info = $goodMod->getOne(array("cond" => "goods_id=" . $info['goods_id']));
        //分类信息
        $info['original_img'] = $goods_info['original_img'];

        $cat_3 = $goodClassMod->getLangInfo($goods_info['cat_id'], $this->langid);
        $cat_2 = $goodClassMod->getLangInfo($cat_3[0]['parent_id'], $this->langid);
        $cat_1 = $goodClassMod->getLangInfo($cat_2[0]['parent_id'], $this->langid);
        $this->assign("cat_3", $cat_3[0]);
        $this->assign("cat_2", $cat_2[0]);
        $this->assign("cat_1", $cat_1[0]);
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
                $where1 = 'WHERE store_id =' . $this->storeid . '  and  ' . $curtime . ' > start_time  and id=' . $cid;
                $sql = 'SELECT  store_goods_id,o_price,price,goods_num,start_time,end_time,start_our,end_our,goods_name,`name`,limit_num,discount  FROM  ' . DB_PREFIX . 'spike_activity ' . $where1;
                $arr = $seckMod->querySql($sql);
                foreach ($arr as $k => $v) {
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
                    $arr[$k]['user_num']=$this->getUserNum($source,$cid,$v['store_goods_id']);
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
                $where2 = '  where  store_id = ' . $this->storeid . ' and  mark =1 and id=' . $cid;
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
            $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $this->storeid;
            $store_arr = $goodAttrMod->querySql($store_sql);
            $info['shop_price'] = number_format($info['shop_price'] * $store_arr[0]['store_discount'],2);
            $this->assign("store_arr", $store_arr[0]);
        }


        //商品属性(暂时死数据展示，参数一 原始商品ID  参数二 语言ID)
        $attr_arr = $goodAttrMod->getLangData($info['goods_id'], $this->langid);

        //获取区域商品规格价格
        $spec_data = $storeGoodMod->getData(array('cond' => "store_goods_id=" . $id));
        foreach ($spec_data as $k => $v) {
            $spec_arr[$v['key']] = $v;
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
                     where  goods_id  = ' . $info['id'] . ' and store_id = ' . $this->storeid . '   order by comment_id desc ';
        $list = $this->goodsCommentMod->querySql($eva_sql);
        $new_list = array_map(function($vo) {
            $vo['img'] = explode(',', $vo['img']);
            return $vo;
        }, $list);

        //获取国家下属所有店铺
        $storeList = $this->getCountryStore($this->countryId);
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
        $this->assign('zhhdData', $zhhdData);
        //限时秒杀活动
        $xsmssql = 'SELECT store_goods_id FROM  ' . DB_PREFIX . 'spike_activity  where store_goods_id =' . $id;
        $xsmsData = $imMod->querySql($xsmssql);
        $this->assign('xsmsData', $xsmsData);
        //促销活动
        $cxql = 'SELECT gs.goods_id FROM  ' . DB_PREFIX . 'promotion_sale AS cs LEFT JOIN  ' . DB_PREFIX . 'promotion_goods as gs on gs.prom_id=cs.id  where cs.status=1 and  gs.goods_id =' . $id;
        $cxData = $imMod->querySql($cxql);
        $this->assign('cxData', $cxData);
        //团购活动
        $tgql = 'SELECT goods_id FROM  ' . DB_PREFIX . 'goods_group_buy where is_end =1 and goods_id =' . $id;
        $tgData = $imMod->querySql($tgql);
        $this->assign('tgData', $tgData);
        $this->assign('langdata', $this->langData);
        $this->assign('spec_key', $str);
        $this->assign('source', $source);
        $this->assign('cid', $cid);
        $this->assign('gid', $_REQUEST['gid']);
        $this->assign('kf_id', $kf_id);
        $this->assign('user_id', $this->userId);
        $this->assign("storelist", $storeList);
        $this->assign('list', $new_list);
        $this->assign("store_id", $this->storeid);
        $this->assign("attr_arr", $attr_arr);
        $this->assign("cur_info", $cur_info[0]);
        $this->assign("spec_img", $spec_img);
        $this->assign("spec_arr", json_encode($spec_arr));
        $this->assign("info", $info);
        $this->assign("data", $data);
        $this->assign("img_arr", $img_arr);
        $this->assign('langId', $this->langid);
        $this->assign('fxCode', $fxCode);

        if ($this->syshort == "人民币") {
            if (!empty($source)) {
                $this->display("goods/youhuiproduct-detail.html");
            } else {
                $this->display("goods/product-detail.html");
            }
        } else {
            if (!empty($source)) {
                $this->display("goods/youhuiproduct-detail.html");
            } else {
                $this->display("goods/product-detail_e.html");
            }
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
                     WHERE b.id IN($keys) and al.lang_id=" . $this->langid . " and bl.lang_id=" . $this->langid . " ORDER BY b.id";
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
    //获取用户购买数量
    public function  getUserNum($source,$prom_id,$store_goods_id){
        $storeGoodsMod = &m('areaGood');
        $sql="select sum(og.goods_num) as total from ".DB_PREFIX.'order  as o  left join '.DB_PREFIX.'order_goods as og ON og.order_id = o.order_sn 
        where o.buyer_id='.$this->userId.' and  og.prom_type='.$source.' and og.prom_id='.$prom_id.' and og.goods_id='.$store_goods_id.' and o.mark=1 and o.order_state >=20';
        $sum=$storeGoodsMod->querySql($sql);
        if(empty($sum[0]['total'])){
            $sum[0]['total']=0;
        }

        return $sum[0]['total'];
    }


//  获取店铺打折信息
    public function getStoreDiscount($storid){
        $storeGoodsMod=&m('areaGood');
        $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' .$storid;
        $store_arr = $storeGoodsMod->querySql($store_sql);
        return $store_arr[0]['store_discount'];
    }

    //获取配送店铺
    public function getStore() {
        // var_dump($_SESSION['latlon']);

        $latlon = explode(',', $_SESSION['latlon']);
        $lng = $latlon[1]; //经度
        $lat = $latlon[0]; //纬度
        $latlon=$this->coordinate_switchf($lat,$lng);
        $lng=$latlon['Longitude'];
        $lat=$latlon['Latitude'];
        $goods_id = $_REQUEST['store_good_id'];
        if ($_SESSION['userId']) {
            $user_id = $_SESSION['userId'];
            $userMod = &m('user');
            $sql = 'SELECT *  FROM  ' . DB_PREFIX . 'user WHERE  id  =' . $user_id; //odm_members
            $datas = $userMod->querySql($sql);
                $where = ' and c.store_type in (2,3)  ';

        } else {
            $where = ' and c.store_type in（2,3） ';
        }
        $mod = &m('store');
        $sql = 'SELECT  c.id,l.store_name,c.distance,c.longitude,c.latitude  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE c.is_open = 1  and  l.lang_id =' . $this->langid . ' and l.distinguish=0  and c.store_cate_id=' . $this->countryId . $where;
        $data = $mod->querySql($sql);
        $sql1 = 'SELECT store_id,id FROM ' . DB_PREFIX . 'store_goods  WHERE goods_id=' . $goods_id . ' and mark =1  and is_on_sale =1  ';
        $gData = $mod->querySql($sql1);
        $temp = $this->array_unset_tt($gData, store_id);
        foreach ($data as $k => $v) {
            foreach ($temp as $k1 => $v1) {
                if ($v['id'] == $v1['store_id']) {
                    $arr[$k1]['id'] = $v['id'];
                    $arr[$k1]['store_name'] = $v['store_name'];
                    $arr[$k1]['distance'] = $v['distance'];
                    $arr[$k1]['latitude'] = $v['latitude'];
                    $arr[$k1]['longitude'] = $v['longitude'];
                    $arr[$k1]['store_goods_id'] = $v1['id'];
                    $arr[$k1]['store_discount']=$this->getStoreDiscount($v['id']);
                }
            }
        }
        foreach ($arr as $key => $val) {
            $s = $this->getdistance($lng, $lat, $val['longitude'], $val['latitude']);
            $distance = number_format(($s / 1000), 2, '.', '');
            $arr[$key]['dis'] = $distance;
            $busSql = "SELECT buss_id FROM " . DB_PREFIX . 'store_business WHERE  store_id=' . $val['id'];
            $busData = $mod->querySql($busSql);
            $arr[$key]['b_id'] = $busData[0]['buss_id'];
            if ($val['distance'] < $distance) {
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
        if(empty($arr)){$arr=array();}
        $this->setData($arr, $status = 1, '');
    }

    //腾讯转百度坐标转换
    function coordinate_switchf($a, $b){
        $x = (double)$b ;
        $y = (double)$a;
        $x_pi = 3.14159265358979324;
        $z = sqrt($x * $x+$y * $y) + 0.00002 * sin($y * $x_pi);
        $theta = atan2($y,$x) + 0.000003 * cos($x*$x_pi);
        $gb = number_format($z * cos($theta) + 0.0065,6);
        $ga = number_format($z * sin($theta) + 0.006,6);

        return array(
            'Latitude'=>$ga,
            'Longitude'=>$gb
        );
    }

    function  getdistance($lng1, $lat1, $lng2, $lat2) {
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

    //2维数组取重

    function array_unset_tt($arr, $key) {
        //建立一个目标数组
        $res = array();
        foreach ($arr as $value) {
            //查看有没有重复项

            if (isset($res[$value[$key]])) {
                //有：销毁

                unset($value[$key]);
            } else {

                $res[$value[$key]] = $value;
            }
        }
        return $res;
    }

    /*
     * 当前国家下的所有区域店铺
     */

    public function getCountryStore($country_id) {
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
                 WHERE c.is_open = 1  and  l.lang_id =' . $this->langid . ' and l.distinguish=0  and c.store_cate_id=' . $country_id . $where;
        $data = $mod->querySql($sql);
        return $data;
    }

    /*
     * 我的足迹
     * @author wangs
     * @date 2017-09-20
     * @param $id 商品ID
     */

    public function infoFootPrint($goods_id, $id) {
        $userId = $this->userId;
        $sql = "select id,good_id from  " . DB_PREFIX . "user_footprint where user_id=" . $userId . " and store_good_id=" . $id . " order by adds_time desc";
        $keys = $this->footPrintMod->querySql($sql);
        if (empty($keys)) {
            if ($goods_id != $keys[0]['good_id']) {
                $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;  //所选的站点id
                $data['user_id'] = $userId;
                $data['good_id'] = $goods_id;
                $data['store_id'] = $storeid;
                $data['adds_time'] = time();
                $data['store_good_id'] = $id;
                $re = $this->footPrintMod->doInsert($data);
            }
        } else {
            $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
            $data['table'] = "user_footprint";
            $data['cond'] = "id=" . $keys[0]['id'];
            $data['set'] = array(
                'adds_time' => time(),
            );
            $re = $this->footPrintMod->doUpdate($data);
        }
    }

    /*
     * 评价详情页
     * @author wangs
     * @date 2018-1-18
     * @param $id 商品ID
     */

    public function commentIndex() {
        $id = ($_REQUEST['gid']) ? $_REQUEST['gid'] : 0;
        //加载语言包
        $this->load($this->shorthand, 'WeChat/goods');
        $this->assign('langdata', $this->langData);
        //获取商品评价数量
        $good_all_num = " select count(1) as all_num from bs_goods_comment  where goods_id ={$id}";
        $good_all_num = $this->goodsCommentMod->querySql($good_all_num);
        $this->assign('good_all_num', $good_all_num[0]);
        //获取评价列表信息
        $eva_sql = 'select * from ' . DB_PREFIX . 'goods_comment
                     where  goods_id  = ' . $id . ' and store_id = ' . $this->storeid . '   order by comment_id desc ';
        $list = $this->goodsCommentMod->querySql($eva_sql);
        $new_list = array_map(function($vo) {
            $vo['img'] = explode(',', $vo['img']);
            return $vo;
        }, $list);
        $this->assign('list', $new_list);
        $this->display('goods/comment-detail.html');
    }

//姗姗测试
    public function mycart() {
        $this->display('goods/mycart.html');
        //$this->display('goods/addcart.html');
    }

    /*
     * 通过判断该店铺下面的该商品是否存在/库存够
     * @author lee
     * @date 2017-12-27 16:44:08
     */

    public function isStoreInfo() {
        //加载语言包
        $langId = $_REQUEST['lang_id'] ? $_REQUEST['lang_id'] : '29';
        $short = $this->getShorthand($langId);
        $this->load($short, 'goods/goods');
        $a = $this->langData;
        $goods_id = $_REQUEST['good_id'] ? $_REQUEST['good_id'] : '';
        $store_id = $_REQUEST['store_id'] ? $_REQUEST['store_id'] : '';
        $spec_key = $_REQUEST['spec_key'] ? $_REQUEST['spec_key'] : '';
        $goods_num = $_REQUEST['goods_num'] ? $_REQUEST['goods_num'] : '';
        $source = $_REQUEST['source'] ? $_REQUEST['source'] : '';
        $cid = $_REQUEST['cid'] ? $_REQUEST['cid'] : '';
        if (!empty($source) && !empty($cid)) {

        } else {
            if (empty($goods_id)) {
                $this->setData(array(), $status = '0', $a['goods_region']);
            }
            if (empty($goods_num)) {
                $this->setData(array(), $status = '0', $a['goods_region']);
            }
            if (empty($store_id)) {
                $this->setData(array(), $status = '0', $a['goods_region']);
            }
            if ($spec_key) {
                $sql = "select g.id,s.goods_storage from " . DB_PREFIX . "store_goods as g left join " . DB_PREFIX . "store_goods_spec_price as s on s.store_goods_id=g.id
                where g.goods_id=" . $goods_id . " and g.store_id=" . $store_id . " and s.`key`='" . $spec_key . "' and s.goods_storage>=" . $goods_num;
            } else {
                $sql = "select * from " . DB_PREFIX . "store_goods where goods_id=" . $goods_id . " and store_id=" . $store_id . " and goods_storage>=" . $goods_num;
            }


            $res = $this->goodsCommentMod->querySql($sql);
            if ($res) {
                $this->setData($res[0], $status = '1', $a['goods_Obtain']);
            } else {
                $this->setData(array(), $status = '0', $a['goods_region']);
            }
        }
    }

    public function checkNum() {
        $cid = !empty($_REQUEST['cid']) ? intval($_REQUEST['cid']) : 0;
        $source = !empty($_REQUEST['source']) ? intval($_REQUEST['source']) : 0;
        $num = !empty($_REQUEST['num']) ? intval($_REQUEST['num']) : 0;
        $lang_id = !empty($_REQUEST['lang']) ? intval($_REQUEST['lang']) : 0;
        //1 秒杀 2 团购 3 促销

        if ($source == 1) {
            $sql = 'select goods_num from ' . DB_PREFIX . 'spike_activity where id=' . $cid;
            $spikeData = $this->goodsCommentMod->querySql($sql);
            if ($num > $spikeData[0]['goods_num']) {
                if ($lang_id == 29) {
                    $this->setData(array(), $status = '1', '库存不足');
                } else {
                    $this->setData(array(), $status = '1', 'Lack of stock');
                }
            } else {
                if ($lang_id = 29) {
                    $this->setData(array(), $status = '1', '只能秒杀一件');
                } else {
                    $this->setData(array(), $status = '1', 'Only one piece of the second can be killed');
                }
            }
        }
        if ($source == 2) {
            $sql = 'select group_goods_num from ' . DB_PREFIX . 'goods_group_buy where id=' . $cid;
            $groupData = $this->goodsCommentMod->querySql($sql);
            $info = array('num' => $groupData[0]['group_goods_num']);
            if ($num > $groupData[0]['group_goods_num']) {
                if ($lang_id == 29) {
                    $this->setData($info, $status = '2', '库存不足');
                } else {
                    $this->setData($info, $status = '2', 'Lack of stock');
                }
            }
        }
        if ($source == 3) {
            $sql = 'select pg.limit_amount from ' . DB_PREFIX . 'promotion_goods as pg left join ' . DB_PREFIX . 'promotion_sale as ps on ps.id=pg.prom_id  where ps.id =' . $cid;
            $promotionData = $this->goodsCommentMod->querySql($sql);
            $info = array('limit_amount' => $promotionData[0]['limit_amount']);
            if ($num >= $promotionData[0]['limit_amount'] && $promotionData[0]['limit_amount'] != 0) {
                if ($lang_id == 29) {
                    $this->setData($info, $status = '3', '限购' . $promotionData[0]['limit_amount'] . '件');
                } else {
                    $this->setData($info, $status = '3', $promotionData[0]['limit_amount'] . 'pieces of limited purchase');
                }
            }
        }
    }

    public function isStoreInfo1() {
        //加载语言包
        $langId = $_REQUEST['lang_id'] ? $_REQUEST['lang_id'] : '29';
        $this->load($this->shorthand, 'goods/goods');
        $a = $this->langData;

        $goods_id = $_REQUEST['good_id'] ? $_REQUEST['good_id'] : '';
        $store_id = $_REQUEST['store_id'] ? $_REQUEST['store_id'] : '';
        $spec_key = $_REQUEST['spec_key'] ? $_REQUEST['spec_key'] : '';
        $goods_num = $_REQUEST['goods_num'] ? $_REQUEST['goods_num'] : '';
        $source = $_REQUEST['source'] ? $_REQUEST['source'] : '';
        $cid = $_REQUEST['cid'] ? $_REQUEST['cid'] : '';
        if (!empty($source) && !empty($cid)) {

        } else {
            if (empty($goods_id)) {
                $this->setData(array(), $status = '0', $a['goods_region']);
            }
            if (empty($goods_num)) {
                $this->setData(array(), $status = '0', $a['goods_region']);
            }
            if (empty($store_id)) {
                $this->setData(array(), $status = '0', $a['goods_region']);
            }
            if ($spec_key) {
                $sql = "select g.id,s.goods_storage from " . DB_PREFIX . "store_goods as g left join " . DB_PREFIX . "store_goods_spec_price as s on s.store_goods_id=g.id
                where g.goods_id=" . $goods_id . " and g.store_id=" . $store_id . " and s.`key`='" . $spec_key . "' and s.goods_storage>=" . $goods_num;
            } else {
                $sql = "select * from " . DB_PREFIX . "store_goods where goods_id=" . $goods_id . " and store_id=" . $store_id . " and goods_storage>=" . $goods_num;
            }


            $res = $this->goodsCommentMod->querySql($sql);
            if ($res) {
                $this->setData($res[0], $status = '1', $a['goods_Obtain']);
            } else {
                $this->setData(array(), $status = '0', $a['goods_region']);
            }
        }
    }

    //获取规格价格库存
    public function getSpec(){
        $id=$_REQUEST['store_goods_id'];
        $storeGoodMod = &m("storeGoodItemPrice");
        $storeMod=&m('areaGood');
        $spec_data = $storeGoodMod->getData(array('cond' => "store_goods_id=" . $id));

        if(!empty($spec_data)){
            foreach ($spec_data as $k => $v) {
                $spec_arr[$v['key']] = $v;
            }
            $spec_arr=json_encode($spec_arr);
            $info=array('id'=>$id,'spec_arr'=>$spec_arr);
            $this->setData($info,1,'');
        }else{

            $goods_data=$storeMod->getData(array('cond'=>"id=".$id));
            $spec_arr=json_encode($goods_data[0]);
            $info=array('id'=>$id,'goodInfo'=>$spec_arr);
            $this->setData($info,1,'');
        }

    }

}
