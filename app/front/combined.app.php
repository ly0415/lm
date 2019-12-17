<?php

/**
 * 组合销售
 * @author lee
 *
 */
//include_once 'sms.app.php';
//include_once 'mail.app.php';
class combinedApp extends BaseFrontApp {

    private $groupMod;
    private $groupGoodsMod;
    private $goodsCommentMod;

    public function __construct() {
        parent::__construct();
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $this->assign('storeid', $storeid);
        $this->groupMod = &m('combinedSale');
        $this->groupGoodsMod = &m('combinedGoods');
        $this->goodsCommentMod = &m('goodsComment');
    }

    /*
     * 组合销售列表
     */

    public function c_index() {
        $comMod = $this->groupMod;
        $comGoodMod = $this->groupGoodsMod;
        $storeId = $this->storeid;
        $sql = 'select c.*,sgl.original_img as main_img  from  '
                . DB_PREFIX . "combined_sale as c left join  "
              . DB_PREFIX . "store_goods as sg on c.main_id = sg.id left join  "
               . DB_PREFIX . "goods as sgl on sg.goods_id = sgl.goods_id  where  c.status=1 and c.store_id =" . $storeId . ' and sg.mark=1 and sg.is_on_sale=1';
      /*  $list = $comMod->getData(array("cond" => 'store_id=' . $storeId . ' and status=1'));*/
      $list = $comMod->querySql($sql);
//        print_r($sql);exit;
        foreach ($list as $k => $v) {
        /*    $item = $comGoodMod->getData(array("cond" => "com_id=" . $v['id']));*/
           $sql = "select  c.*,sgl.original_img from " . DB_PREFIX . "combined_goods as c left join  "
                   . DB_PREFIX . "store_goods as sg on c.store_goods_id = sg.id left join  "
                   . DB_PREFIX . "goods as sgl on sg.goods_id = sgl.goods_id  where c.com_id =" . $v['id'] . ' and sg.mark=1 and sg.is_on_sale=1';
            $item = $comGoodMod->querySql($sql);
            $list[$k]['child'] = $item;
        }
        $this->assign('list', $list);
        //语言包
        $this->load($this->shorthand, 'Promotion/Promotion');
        $this->assign('langdata', $this->langData);
        $langguages = $this->shorthand;
        $this->assign('langguages', $langguages);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $this->display("combined/list1.html");
    }

    /*
     * 详情列表
     */

    public function goods_list() {
        $com_id = ($_REQUEST['com_id']) ? $_REQUEST['com_id'] : '';
        $sgoodMod = &m('areaGood');
        if (empty($com_id)) {
            $this->display('error/404.html');
        }
//        $sql = 'select   c.id,c.end_time,c.name,c.main_id,c.main_name,c.main_price,c.store_id,c.add_time,c.status,c.main_key,c.main_key_name,sgl.original_img as main_img  from  '
//                . DB_PREFIX . "combined_sale as c left join  "
//                . DB_PREFIX . "store_goods as sg on c.main_id = sg.id left join  "
//                . DB_PREFIX . "goods as sgl on sg.goods_id = sgl.goods_id  where c.id =" . $com_id;
//         $com_info = $this->groupMod->querySql($sql);
        $com_info = $this->groupMod->getOne(array("cond" => "id=" . $com_id));
        if ($com_info) {
            $com_lang = $this->getGoodsLang($com_info['main_id'], $this->langid);
            if ($com_lang) {
                $com_info['main_name'] = $com_lang['goods_name'];
            }
            if ($com_info['main_key']) {
                $k_info = $this->getkeyName($com_info['main_key']);
                if ($k_info) {
                    $com_info['main_key_name'] = $k_info;
                } else {
                    $com_info['main_key_name'] = str_replace(":", " ", $com_info['main_key_name']);
                }
            }
//            $sql = "select  c.*,sgl.original_img from " . DB_PREFIX . "combined_goods as c left join  "
//                    . DB_PREFIX . "store_goods as sg on c.store_goods_id = sg.id left join  "
//                    . DB_PREFIX . "goods as sgl on sg.goods_id = sgl.goods_id  where c.com_id =" . $com_id;
//            $item_child = $this->groupGoodsMod->querySql($sql);
            $item_child = $this->groupGoodsMod->getData(array("cond" => "com_id=" . $com_id));
            if ($item_child) {
                foreach ($item_child as $k => $v) {
                    $child_info = $sgoodMod->getLangInfo($v['store_goods_id'], $this->langid);
                    if ($v['item_key']) {
                        $k_info = $this->getkeyName($v['item_key']);
                    }
                    if ($child_info) {
                        $k_name = $child_info['goods_name'] . $k_info;
                        $item_child[$k]['item_name'] = $k_name;
                    }
                }
            }
            $com_info['item_child'] = $item_child;
        } else {
            $this->display('error/404.html');
        }
        $this->assign('info', $com_info);
        //语言包
        $this->load($this->shorthand, 'Promotion/Promotion');
        $this->assign('langdata', $this->langData);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $this->display('combined/goodslist.html');
    }

    /**
     * 商品详情页面
     * @author lee
     * @date 2017-11-7 09:29:04
     * @param $gid 活动ID $type 1 主商品 2 其他商品
     */
    public function goods_info() {

        $id = !empty($_REQUEST['gid']) ? $_REQUEST['gid'] : '';  //
        $type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : '';  //
        $fxCode = !empty($_REQUEST['fxCode']) ? $_REQUEST['fxCode'] : '';  //

        if (empty($id)) {
            $this->display('error/404.html');
        }
        if ($type == 1) {
            $sql = 'select   c.id,c.end_time,c.name,c.main_id,c.main_name,c.main_price,c.store_id,c.add_time,c.status,c.main_key,c.main_key_name,sgl.original_img as main_img  from  '
                    . DB_PREFIX . "combined_sale as c left join  "
                    . DB_PREFIX . "store_goods as sg on c.main_id = sg.id left join  "
                    . DB_PREFIX . "goods as sgl on sg.goods_id = sgl.goods_id  where c.id =" . $id;
//            $sql = 'select * from  ' . DB_PREFIX . 'combined_sale  where id =  ' . $id;
        } else {
            $sql = "select  c.id,c.com_id,c.item_key,c.item_name,c.price,c.item_num,c.store_goods_id,c.discount,c.z_pirce,c.sip_id,c.c_price,c.add_time,sgl.original_img as goods_img from " . DB_PREFIX . "combined_goods as c left join  "
                    . DB_PREFIX . "store_goods as sg on c.store_goods_id = sg.id left join  "
                    . DB_PREFIX . "goods as sgl on sg.goods_id = sgl.goods_id  where c.id =" . $id;
//            $sql = 'select * from  ' . DB_PREFIX . 'combined_goods  where id =  ' . $id;
        }
        $groupinfo = $this->groupMod->querySql($sql);

        //商品信息运费
        $storeGoodsMod = &m('areaGood');
        if ($type == 1) {  //167
            $sql2 = 'SELECT  id,goods_id,is_free_shipping,shipping_price,goods_storage  FROM  ' . DB_PREFIX . 'store_goods  WHERE id =' . $groupinfo[0]['main_id'];
        } else {
            $sql2 = 'SELECT  id,goods_id,is_free_shipping,shipping_price,goods_storage  FROM  ' . DB_PREFIX . 'store_goods  WHERE id =' . $groupinfo[0]['store_goods_id'];
        }
        $goodsinfo = $storeGoodsMod->querySql($sql2);

        //获取商品综合评分
        $good_rank_sql = "select goods_rank , count(1) as good_num from bs_goods_comment  where goods_id ={$goodsinfo[0]['id']}  group BY goods_rank";
        $good_rank_sta = $this->goodsCommentMod->querySql($good_rank_sql);
        $good_all_num = " select count(1) as all_num from bs_goods_comment  where goods_id ={$goodsinfo[0]['id']}";
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
                 where goods_id  = ' . $goodsinfo[0]['id'] . '  and  store_id = ' . $this->storeid;
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
                     where  goods_id  = ' . $goodsinfo[0]['id'] . ' and store_id = ' . $this->storeid . '   order by comment_id desc ' . $commlimit;
        $list = $this->goodsCommentMod->querySql($eva_sql);
        $new_list = array_map(function($vo) {
            $vo['img'] = explode(',', $vo['img']);
            return $vo;
        }, $list);

        $this->assign('all_rate', $all_rate);
        $this->assign('list', $new_list);
        if ($type == 2) {
            $cominfo = $this->groupMod->getOne(array("cond" => "id=" . $groupinfo[0]['com_id']));
            $groupinfo[0]['main_img'] = $groupinfo[0]['goods_img'];
            $groupinfo[0]['id'] = $groupinfo[0]['com_id'];
            $groupinfo[0]['name'] = $cominfo['name'];
            $groupinfo[0]['main_name'] = $groupinfo[0]['item_name'];
            $groupinfo[0]['main_price'] = $groupinfo[0]['c_price'];
            $groupinfo[0]['main_goods_price'] = $groupinfo[0]['price'];
            $groupinfo[0]['main_key'] = $groupinfo[0]['item_key'];
            $groupinfo[0]['main_id'] = $groupinfo[0]['store_goods_id'];
            $groupinfo[0]['shipping_price'] = $goodsinfo[0]['shipping_price'];
            $groupinfo[0]['store_id'] = $this->storeid;
            $groupinfo[0]['type'] = 2;
        }

        //翻译处理
        $child_info = $storeGoodsMod->getLangInfo($groupinfo[0]['main_id'], $this->langid);
        if ($groupinfo[0]['main_key']) {
            $k_info = $this->getkeyName($groupinfo[0]['main_key']);
        }
        if ($child_info) {
            $k_name = $child_info['goods_name'] . $k_info;
            $groupinfo[0]['main_name'] = $k_name;
        }
        //end
        //获取商品图片
        $goodsImgMod = &m('goodsImg');
        $sql3 = 'select  image_url,goods_id  from  ' . DB_PREFIX . 'goods_images  where goods_id =' . $goodsinfo[0]['goods_id'];
        $imgArr = $goodsImgMod->querySql($sql3);
        //商品规格
        $goodsSpecMod = &m('goodsSpec');
        $specItemLangMod = &m('goodsSpecItemLang');
        $goodsspec = $groupinfo[0]['goods_spec_key'];
        $specitems = array();
        if (!empty($goodsspec)) {
            $specArr = explode('_', $goodsspec);
            foreach ($specArr as $key => $val) {
                //
                $sqlspec = 'SELECT sp.`type_id`,sp.`id`,l.`spec_name`  FROM  ' . DB_PREFIX . 'goods_spec  AS sp
                            LEFT JOIN  ' . DB_PREFIX . 'goods_spec_item AS i ON sp.`id` = i.`spec_id`
                            LEFT JOIN  ' . DB_PREFIX . 'goods_spec_lang AS l ON sp.`id` = l.`spec_id`
                            WHERE i.`id` = ' . $val . ' AND l.`lang_id` = ' . $this->langid;
                $res = $goodsSpecMod->querySql($sqlspec);
                $specitems[$key] = $res[0];
                //
                $sqlitem = 'SELECT  item_id,item_name,lang_id  FROM  ' . DB_PREFIX . 'goods_spec_item_lang  WHERE item_id =' . $val . '  AND  lang_id = ' . $this->langid;
                $res2 = $specItemLangMod->querySql($sqlitem);
                $specitems[$key]['items'] = $res2[0];
            }
        }
        //商品简介
        $goodscnt = $this->getStoreGoodsLang($goodsinfo[0]['goods_id'], $this->langid);
        $gcontent = $goodscnt['goods_content'];
//        if (empty($gcontent)) {
//            //获取原始的goods的商品内容
//            $goodsMod = &m('goods');
//            $sqlg = 'select  goods_content from  ' . DB_PREFIX . 'goods where goods_id =' . $goodsinfo[0]['goods_id'];
//            $resg = $goodsMod->querySql($sqlg);
//            $gcontent = $resg[0]['goods_content'];
//        }
        //商品属性
        $goodsAttrMod = &m('goodsAttri');
        $sqlattr = 'SELECT  al.`name`,ga.`attr_value`  FROM   ' . DB_PREFIX . 'goods_attr  AS ga
                    LEFT JOIN  ' . DB_PREFIX . 'goods_attr_lang al  ON  ga.`attr_id` = al.`a_id`
                    WHERE goods_id = ' . $goodsinfo[0]['goods_id'] . '  AND  al.`lang_id` = ' . $this->langid;
        $attrArr = $goodsAttrMod->querySql($sqlattr);
        $this->assign('attrArr', $attrArr);
        $this->assign('gcontent', $gcontent);
        $this->assign('groupinfo', $groupinfo[0]);
        $this->assign('goodsinfo', $goodsinfo[0]);

        $this->assign('specitems', $specitems);
        $this->assign('imgArr', $imgArr);
        $this->assign('groupinfo', $groupinfo[0]);
        $this->assign('store_goods_id', $groupinfo[0]['main_id']);
        //语言包
        $this->load($this->shorthand, 'Promotion/Promotion');
        $this->assign('langdata', $this->langData);
        $this->assign('lang_id', $this->langid);
        $this->assign('store_id', $this->storeid);
        $this->assign('fxCode', $fxCode);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $this->display('combined/detail.html');
    }

    /**
     * 获取商品的多语言信息
     * @param $goodsId
     * @param $langid
     * @return mixed
     */
    public function getStoreGoodsLang($goodsId, $langid) {
        $storeGLMod = &m('goodsLang');
        $sql = 'SELECT  goods_name,goods_content  FROM  ' . DB_PREFIX . 'goods_lang   WHERE  goods_id = ' . $goodsId . ' AND   lang_id =' . $langid;
        $res = $storeGLMod->querySql($sql);
        return $res[0];
    }

}
