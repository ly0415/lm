<?php

/**
 * 用户中心
 * @author lvji
 *
 */
//include_once 'sms.app.php';
//include_once 'mail.app.php';
class GoodsApp extends BaseFrontApp {

    private $goodsCommentMod;

    public function __construct() {
        parent::__construct();
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $this->assign('storeid', $storeid);
        $this->footPrintMod = &m('footprint');
        $this->goodsCommentMod = &m('goodsComment');
        $this->storeGoodItemPriceMod = &m('storeGoodItemPrice');
        $this->areaGoodMod = &m('areaGood');
    }

    /*
     * 商品详情页
     * @author lee
     * @date 2017-8-11 10:22:12
     */

    public function goodInfo() {
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $ip = $_SERVER['REMOTE_ADDR'];
        $this->assign('ip', $ip);
        $ipUrl = "http://api.map.baidu.com/location/ip?ip=" . $ip . "&ak=CmfcOlGRQE7OztyHtDGLoiiNGYUu37Te&coor=bd09ll";
        $ipData = file_get_contents($ipUrl);
        $ipData = json_decode($ipData);
        $lng = $ipData->content->point->x;
        $lat = $ipData->content->point->y;
        $swhere = " where sl.distinguish=" . $auxiliary;
        $storeMod = &m('store');
        if (empty($this->userId)) {
            $swhere .= ' AND store_type in (1,2,3)';
        } else {
            $uSql = "SELECT odm_members FROM " . DB_PREFIX . "user WHERE id=" . $this->userId;
            $uData = $storeMod->querySql($uSql);
            if ($uData[0]['odm_members'] == 1) {
                $swhere .= '';
            } else {
                $swhere .= ' AND store_type in (1,2,3)';
            }
        }
        $ssql = 'SELECT  s.id,s.logo,s.longitude,s.latitude,sl.`store_name` AS sltore_name,sl.distinguish FROM  '
                . DB_PREFIX . 'store AS s LEFT JOIN  '
                . DB_PREFIX . 'store_lang AS sl ON sl.`store_id` = s.`id` and  sl.lang_id=' . $this->langid . ' LEFT JOIN  '
                . DB_PREFIX . 'language AS l ON s.`lang_id` = l.`id` LEFT JOIN  '
                . DB_PREFIX . 'currency AS c ON s.`currency_id` = c.`id`' . $swhere;
        $sData = $storeMod->querySql($ssql);
        foreach ($sData as $key => $val) {
            $s = $this->getdistance($lng, $lat, $val['longitude'], $val['latitude']);
            $distance = number_format(($s / 1000), 2, '.', '');
            $sData[$key]['distance'] = $distance;
        }
        $sort = array(
            'direction' => 'SORT_ASC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
            'field' => 'distance', //排序字段
        );
        $arrSort = array();
        foreach ($sData AS $uniqid => $row) {
            foreach ($row AS $key => $value) {
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if ($sort['direction']) {
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $sData);
        }
        $ssData = array_slice($sData, 0, 3);
        $this->assign('sData', $ssData);


        $id = ($_REQUEST['gid']) ? $_REQUEST['gid'] : 0;
        $fxCode = ($_REQUEST['fxCode']) ? $_REQUEST['fxCode'] : '';
        $goodMod = &m('goods');
        $goodClassMod = &m('goodsClass');
        $goodAttrMod = &m('goodsAttriInfo');
        $goodImgMod = &m('goodsImg');
        $storeGoods = &m('areaGood');
        $storeGoodMod = &m("storeGoodItemPrice");
        //组合销售活动
        $zhhdsql = 'SELECT gs.store_goods_id FROM  ' . DB_PREFIX . 'combined_sale AS cs LEFT JOIN  ' . DB_PREFIX . 'combined_goods as gs on gs.com_id=cs.id  where cs.status=1 and  gs.store_goods_id =' . $id;
        $zhhdData = $storeMod->querySql($zhhdsql);
        $this->assign('zhhdData', $zhhdData);
        //限时秒杀活动
        $xsmssql = 'SELECT store_goods_id FROM  ' . DB_PREFIX . 'spike_activity  where store_goods_id =' . $id;
        $xsmsData = $storeMod->querySql($xsmssql);
        $this->assign('xsmsData', $xsmsData);
        //促销活动
        $cxql = 'SELECT gs.goods_id FROM  ' . DB_PREFIX . 'promotion_sale AS cs LEFT JOIN  ' . DB_PREFIX . 'promotion_goods as gs on gs.prom_id=cs.id  where cs.status=1 and  gs.goods_id =' . $id;
        $cxData = $storeMod->querySql($cxql);
        $this->assign('cxData', $cxData);
        //团购活动
        $tgql = 'SELECT goods_id FROM  ' . DB_PREFIX . 'goods_group_buy where is_end =1 and goods_id =' . $id;
        $tgData = $storeMod->querySql($tgql);
        $this->assign('tgData', $tgData);
        //加载语言包
        $this->load($this->shorthand, 'goods/goods');
        $this->assign('langdata', $this->langData);

        if (empty($id)) {
            $this->display("error/404.html");
        }
        //商品信息
        $info = $storeGoods->getLangInfo($id, $this->langid, $this->storeid);

        if (empty($info)) {
            $this->display("error/404.html");
        }
        if ($info['is_on_sale'] != 1) {
            $this->display("error/goodserror.html");
        }
        //收藏商品
        $sql_collection = 'select * from ' . DB_PREFIX . 'user_collection where user_id=' . $this->userId . ' and store_id=' . $this->storeid;
        $data_collection = $storeGoods->querySql($sql_collection);
        foreach ($data_collection as &$collertion) {
            if ($collertion['store_good_id'] == $info['id']) {
                $info['type'] = 1;
            }
        }
        $goods_info = $goodMod->getOne(array("cond" => "goods_id=" . $info['goods_id']));
        if ($goods_info['is_on_sale'] != 1) {
            $this->display("error/goodserror.html");
        }
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
        $spec_data = $storeGoodMod->getData(array('cond' => "store_goods_id=" . $id));
        foreach ($spec_data as $k => $v) {
            $spec_arr[$v['key']] = $v;
        }
        $item_has = 1;
        if (!empty($spec_arr)) {
            $item_has = 2;
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
                $new_info = $storeGoods->getLangInfo($v['store_goods_id'], $this->langid);
                $com_list[$k]['goods_name'] = $new_info['goods_name'];
            }
            $com_num = count($com_list);
            $promGood = array();
            switch ($com_num) {
                case $com_num < 5:
                    $promGood[] = $com_list;
                    break;
                case $com_num > 4:
                    $promGood[0] = array_slice($com_list, 0, 4);
                    $promGood[1] = array_slice($com_list, 4, 4);
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
        $store_sql = "select s.id,gs.original_img from " . DB_PREFIX . "store_goods as s  inner join " . DB_PREFIX . "goods as gs on s.goods_id = gs.goods_id 
                        where  (store_id = {$this->storeid} and s.is_on_sale = 1 and s.mark = 1 and id !={$id}) and
                       (" . $where . ")";
        $store_good = $goodMod->querySql($store_sql);

        foreach ($store_good as $k => $v) {
            $new_info_2 = $storeGoods->getLangInfo($v['id'], $this->langid);
            $store_good[$k]['goods_name'] = $new_info_2['goods_name'];
        }
        $store_goods_num = count($store_good);
        $storeGood = array();
        switch ($store_goods_num) {
            case $store_goods_num < 5:
                $storeGood[] = $store_good;
                break;
            case $store_goods_num > 4:
                $storeGood[0] = array_slice($store_good, 0, 4);
                $storeGood[1] = array_slice($store_good, 4, 4);
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
        //商品属性(暂时死数据展示，参数一 原始商品ID  参数二 语言ID)
        $attr_arr = $goodAttrMod->getLangData($info['goods_id'], $this->langid);
        //获取区域商品规格价格
        $spec_img = $this->get_spec($info['goods_id'], $id, 2);
        //获取足迹
        $where = ' f.user_id =' . $this->userId . ' and f.store_good_id =g.id';
        $sql = 'select distinct f.*,g.*,l.*,l.goods_name,gs.original_img,f.store_good_id  from '
                . DB_PREFIX . 'user_footprint as f inner join '
                . DB_PREFIX . 'store_goods as g on f.good_id = g.goods_id inner join '
                . DB_PREFIX . 'goods as gs on f.good_id = gs.goods_id inner join '
                . DB_PREFIX . 'goods_lang as l on l.goods_id = g.goods_id and l.lang_id=' . $this->langid . ' where '
                . $where . ' and g.mark = 1  and f.store_id =' . $this->storeid .
                ' group by f.good_id order by f.adds_time desc limit 0, 4 ';
        //获取足迹商品评论
        $data = $this->footPrintMod->querySql($sql);
        foreach ($data as $k => $v) {
            $store_good_id = $v['store_good_id'];
            $sql = 'select  sum(goods_rank) / count(goods_rank) as res from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $store_good_id;
            $trance = $this->goodsCommentMod->querySql($sql);
            $data[$k]['rate'] = $trance[0]['res'];
        }
        //足迹
        $this->infoFootPrint($info['goods_id'], $info['id']);
        $this->assign('store_goods_id', $id);
        //获取币种信息
        $sql = "select c.* from " . DB_PREFIX . "currency as c inner join " . DB_PREFIX . "store as s on c.id=s.currency_id where s.id=" . $info['store_id'];
        $cur_info = $storeGoods->querySql($sql);
        //获取商品综合评分
        $good_rank_sql = "select goods_rank , count(1) as good_num from bs_goods_comment  where goods_id ={$info['id']}  group BY goods_rank";
        $good_rank_sta = $this->goodsCommentMod->querySql($good_rank_sql);
        $good_all_num = " select count(1) as all_num from bs_goods_comment  where goods_id ={$info['id']}";
        $good_all_num = $this->goodsCommentMod->querySql($good_all_num);
        $this->assign('good_all_num', $good_all_num[0]);
        $all_star = 0;
        foreach ($good_rank_sta as $key => $value) {
            $all_star = $all_star + ($value['goods_rank'] * $value['good_num']);
            $pre = $good_all_num[0]['all_num'] ? $value['good_num'] / $good_all_num[0]['all_num'] : 0;
            $good_comment_sta[$value['goods_rank']]['good_num'] = $value['good_num'];
            $good_comment_sta[$value['goods_rank']]['pre'] = round($pre, 2) * 100;
        }
        $all_rate = round($all_star / $good_all_num[0]['all_num']);
        $this->assign('good_comment_sta', $good_comment_sta);
        //获取评价列表信息
        //1.统计多少页数
        $sqlt = 'select  count(*)  as total  from ' . DB_PREFIX . 'goods_comment
                 where goods_id  = ' . $info['id'] . '  and  store_id = ' . $this->storeid;
        $totalD = $this->goodsCommentMod->querySql($sqlt);
        $total = $totalD[0]['total'];
        $totalpage = ceil($total / $this->commpagesize);
        if (empty($totalpage)) {
            $totalpage = 1;
        }
        $this->assign('commtotalpage', $totalpage);
        //2.获取第一页的信息
        $commlimit = '  limit 0,' . $this->commpagesize;
        $eva_sql = 'select  comment_id, username, goods_rank, add_time, content, img, revert  from ' . DB_PREFIX . 'goods_comment
                     where  goods_id  = ' . $info['id'] . ' and store_id = ' . $this->storeid . '   order by comment_id desc ' . $commlimit;
        $list = $this->goodsCommentMod->querySql($eva_sql);
        $new_list = array_map(function($vo) {
            $vo['img'] = explode(',', $vo['img']);
            return $vo;
        }, $list);
        $returnUrl = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"];
        $this->assign('returnUrl', $returnUrl);

        //包含改商品的区域店铺
        $storeList = $this->getTrueCountryStore($this->countryId, $info['goods_id']);
        $this->assign("storelist", $storeList);
        //店铺商品打折
        $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $this->storeid;
        $store_arr = $this->goodsCommentMod->querySql($store_sql);
        $this->assign('store_arr', $store_arr[0]);
        $this->assign('all_rate', $all_rate);
        $this->assign('list', $new_list);
        $this->assign("store_id", $this->storeid);
        $this->assign("user_id", $this->userId);
        $this->assign("attr_arr", $attr_arr);
        $this->assign("cur_info", $cur_info[0]);
        $this->assign("spec_img", $spec_img);
        $this->assign("spec_arr", json_encode($spec_arr));
        $this->assign("info", $info);
        $this->assign("data", $data);
        $this->assign("img_arr", $img_arr);
        $this->assign('langId', $this->langid);
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang_id);
        $this->assign('fxCode', $fxCode);
        $this->display("goods/product-detail.html");
    }

    //经纬度转化为距离
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
                     WHERE b.id IN($keys) and al.lang_id=" . $this->langid . " and bl.lang_id=" . $this->langid;
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
                $where = ' and s.store_type <4 ';
            } else {
                $where = '';
            }
        } else {
            $where = ' and s.store_type<4 ';
        }
        $mod = &m('store');
        $sql = 'SELECT s.id,l.store_name,s.currency_id,s.lang_id  FROM  ' . DB_PREFIX . 'store AS s
                 LEFT JOIN  ' . DB_PREFIX . 'store_lang AS l ON s.`id` = l.`store_id` 
                  WHERE l.lang_id =' . $this->langid . "  and s.is_open =1  and s.store_cate_id =" . $country_id . $where;
        $data = $mod->querySql($sql);
        return $data;
    }

    public function getTrueCountryStore($country_id, $goods_id) {
        if ($_SESSION['userId']) {
            $user_id = $_SESSION['userId'];
            $userMod = &m('user');
            $sql = 'SELECT *  FROM  ' . DB_PREFIX . 'user WHERE  id  =' . $user_id; //odm_members
            $datas = $userMod->querySql($sql);
            if ($datas[0]['odm_members'] == 0) {
                $where = ' and s.store_type < 4 ';
            } else {
                $where = '';
            }
        } else {
            $where = ' and s.store_type < 4 ';
        }
        $mod = &m('store');
        $sql = 'SELECT s.id,l.store_name,s.currency_id,s.lang_id FROM ' . DB_PREFIX . 'store AS s LEFT JOIN '
                . DB_PREFIX . 'store_lang AS l ON s.`id` = l.`store_id` LEFT JOIN '
                . DB_PREFIX . 'store_goods AS a ON s.`id` = a.`store_id` '
                . ' WHERE l.lang_id =' . $this->langid . "  and s.is_open =1  and s.store_cate_id =" . $country_id . " and l.distinguish = '0' AND a.goods_id = {$goods_id} " . $where . ' GROUP BY s.id ORDER BY s.sort ASC,s.add_time DESC';
        $data = $mod->querySql($sql);
        return $data;
    }

    /*
     * 通过判断该店铺下面的该商品是否存在/库存够
     * @author lee
     * @date 2017-12-27 16:44:08
     */

    public function isStoreInfo() {

        //加载语言包
        $this->load($this->shorthand, 'goods/goods');
        $a = $this->langData;
        $goods_id = $_REQUEST['good_id'] ? $_REQUEST['good_id'] : '';
        $store_id = $_REQUEST['store_id'] ? $_REQUEST['store_id'] : '';
        $spec_key = $_REQUEST['spec_key'] ? $_REQUEST['spec_key'] : '';
        $goods_num = $_REQUEST['goods_num'] ? $_REQUEST['goods_num'] : '';

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
            $this->setData($res[0], $status = '1', $a['purchase']);
        } else {
            $this->setData(array(), $status = '0', $a['goods_no_storage']);
        }
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



    public  function  getAccount(){
        $fxUserMod=&m('fxuser');
        $fxUserMod->getAccount();
    }

}
