<?php

/**
 * 促销商品模块
 * @author wanyan
 * @date 2017-09-19
 */
class GoodPromotionApp extends BaseFrontApp {

    private $storeGoodsMod;
    private $goodPromMod;
    private $goodPromDetailMod;
    private $storeMod;
    private $goodsCommentMod;
    private $cartMod;

    public function __construct() {
        parent::__construct();
        $this->storeGoodsMod = &m('areaGood'); //store——goods
        $this->goodPromMod = &m('goodProm'); //promotion_sale
        $this->goodPromDetailMod = &m('goodPromDetail'); //promotiongoods
        $this->storeMod = &m('store');
        $this->goodsCommentMod = &m('goodsComment');
        $this->cartMod = &m('cart');
    }

    /**
     * 促销商品列表
     * @author wanyan
     * @date 2017-09-19
     */
    public function index() {
        //判断活动是否结束
        $this->checkOver();
        // 获取正在进行或者未开始的促销活动
        $sql = " select ps.*,pg.goods_id,pg.goods_key,pg.goods_key_name,pg.goods_name,sgl.original_img as goods_img from "
                . DB_PREFIX . "promotion_sale as ps left join "
                . DB_PREFIX . "promotion_goods as pg on  ps.id = pg.prom_id  left join  "
                . DB_PREFIX . "store_goods as sg on pg.goods_id = sg.id left join  "
                . DB_PREFIX . "goods as sgl on sg.goods_id = sgl.goods_id  where ps.`store_id` = $this->storeid and ps.`status` in (1,2) and sg.mark=1 and sg.is_on_sale=1 and ps.`mark` =1 order by ps.status desc,ps.id desc";
        $rs = $this->goodPromMod->querySqlPageData($sql);

        foreach ($rs['list'] as $k => $v) {
            $rs['list'][$k]['start_time'] = date('Y-m-d H:i', $v['start_time']);
            $rs['list'][$k]['end_time'] = date('Y-m-d H:i', $v['end_time']);
            if ($v['status'] == 2) {
                $rs['list'][$k]['countDown'] = $v['end_time'] - time();
            }
            if (empty($v['goods_key'])) {
                $rs['list'][$k]['path'] = $v['id'] . '-' . $v['goods_id']; // 活动ID + 商品ID
            } else {
                $rs['list'][$k]['path'] = $v['id'] . '-' . $v['goods_id'] . '-' . $v['goods_key']; // 活动ID + 商品ID + 规格Key
            }
            $rs['list'][$k]['goods_name'] = $this->cartMod->getGoodNameById($v['goods_id'], $this->langid);
            if ($v['goods_key']) {
                $info = explode('_', $v['goods_key']);
                foreach ($info as $k1 => $v1) {
                    $sql = "select `item_name` from " . DB_PREFIX . "goods_spec_item_lang where `item_id` = '{$v1}' and  `lang_id` = $this->langid";
                    $spec_1 = $this->cartMod->querySql($sql);
                    $spec[] = $spec_1[0]['item_name'];
                }
                $spec_key = implode(':', $spec);
                $rs['list'][$k]['goods_key_name'] = $spec_key;
                $spec = array();
            }
        }

        $this->assign('list', $rs['list']);
        $this->assign('page', $rs['ph']);
        //语言包
        $this->load($this->shorthand, 'Promotion/Promotion');
        $this->assign('langdata', $this->langData);
        $langguages = $this->shorthand;
        $this->assign('langguages', $langguages);
        $this->assign('lang_id', $this->langid);
        $this->assign('store_id', $this->storeid);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $this->display('goodPromotion/goodLists.html');
    }

    /**
     * 判断活动是否结束
     * @author wanyan
     * @date 2017-09-19
     */
    public function checkOver() {
        $sql = "select * from " . DB_PREFIX . "promotion_sale where mark =1";
        $rs = $this->goodPromMod->querySql($sql);
        foreach ($rs as $k => $v) {
            if ($v['start_time'] > time()) {
                $vstatus = 1;
            } elseif ($v['start_time'] <= time() && $v['end_time'] >= time()) {
                $vstatus = 2;
            } elseif ($v['end_time'] < time()) {
                $vstatus = 3;
            }
            $this->goodPromMod->doEdit($v['id'], array('status' => $vstatus));
        }
    }

    /**
     * 商品详情页面
     * @author wanyan
     * @date 2017-09-19
     */
    public function goodDetail() {
        $goodImgMod = &m('goodsImg');
        $goodAttrMod = &m('goodsAttriInfo');
        $this->checkOver();
        $path = !empty($_REQUEST['path']) ? $_REQUEST['path'] : '';
        $fxCode = !empty($_REQUEST['fxCode']) ? $_REQUEST['fxCode'] : '';
        $pathInfo = explode('-', $path);
        $prom_id = $pathInfo[0];
        $goods_id = $pathInfo[1];
        $shippingPrice = $this->getShippingPrice($goods_id);
        if (!empty($pathInfo[2])) {
            $goods_key = $pathInfo[2];
            $spec_img = $this->get_spec($shippingPrice['goods_id'], $goods_id, 2, explode('_', $goods_key));
            $this->assign('spec_img', $spec_img);
        } else {
            $goods_key = '';
        }
        $goodInfo = $this->getGoodInfo($prom_id, $goods_id, $goods_key);
        //获取商品综合评分
        $good_rank_sql = "select goods_rank , count(1) as good_num from bs_goods_comment  where goods_id ={$goodInfo['goods_id']}  group BY goods_rank";
        $good_rank_sta = $this->goodsCommentMod->querySql($good_rank_sql);
        $good_all_num = " select count(1) as all_num from bs_goods_comment  where goods_id ={$goodInfo['goods_id']}";
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
                 where goods_id  = ' . $goodInfo['goods_id'] . '  and  store_id = ' . $this->storeid;
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
                     where  goods_id  = ' . $goodInfo['goods_id'] . ' and store_id = ' . $this->storeid . '   order by comment_id desc ' . $commlimit;
        $list = $this->goodsCommentMod->querySql($eva_sql);
        $new_list = array_map(function($vo) {
            $vo['img'] = explode(',', $vo['img']);
            return $vo;
        }, $list);

        $this->assign('all_rate', $all_rate);
        $this->assign('list', $new_list);

        $nav = $this->getGoodCate($goods_id); // 获取详情导航信息
        $attr_arr = $goodAttrMod->getLangData($shippingPrice['goods_id'], $this->langid);
        //商品图片页
        // 获取原商品品ID
        $origin_good_id = $this->storeGoodsMod->getOne(array('cond' => "`id`={$goods_id}", 'fields' => 'goods_id'));
        $img_arr = $goodImgMod->getData(array('cond' => "goods_id=" . $origin_good_id['goods_id']));
        // 获取是否包邮状态
        $storeGoodInfo = $this->storeGoodsMod->getOne(array('cond' => "`id`='{$goods_id}'"));
        $sql = "select gl.goods_name from " . DB_PREFIX . "goods as g left join " . DB_PREFIX . "goods_lang as gl on g.goods_id = gl.goods_id where g.goods_id = '{$origin_good_id['goods_id']}' and gl.lang_id = '{$this->langid}' ";
        $realGoods = $this->storeGoodsMod->querySql($sql);
        $goodInfo['realGoodsName'] = $realGoods[0]['goods_name'];
        $this->assign('is_free_shipping', $storeGoodInfo['is_free_shipping']);
        $this->assign('img_arr', $img_arr);
        $this->assign('shippingPrice', $shippingPrice);
        $this->assign('goodInfo', $goodInfo);
        $this->assign('store_goods_id', $goods_id);
        $this->assign('storeGoodsInfo', $storeGoodInfo);
        $this->assign('prom_id', $prom_id);
        $this->assign('nav', $nav);
        $this->assign('good_keys', $goods_key);
        //语言包
        $this->load($this->shorthand, 'Promotion/Promotion');
        $this->assign('langdata', $this->langData);
        $this->assign('lang_id', $this->langid);
        $this->assign('symbol', $this->symbol);
        $this->assign('store_id', $this->storeid);
        $this->assign('attr_arr', $attr_arr);
        $this->assign('fx_code', $fxCode);
        // $this->assign('activityTitle', '促销活动');
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $this->display('goodPromotion/goodDetail.html');
    }

    /**
     * 获取商品的信息
     * @author wanyan
     * @date 2017-11-2
     */
    public function getGoodInfo($prom_id, $goods_id, $goods_key) {
        $sql = " select ps.*,pg.goods_id,pg.goods_key,pg.goods_key_name,pg.goods_name,sgl.original_img as goods_img,pg.goods_price,pg.discount_price,pg.discount_rate,pg.reduce from "
                . DB_PREFIX . "promotion_sale as ps left join "
                . DB_PREFIX . "promotion_goods as pg on  ps.id = pg.prom_id   left join  "
                . DB_PREFIX . "store_goods as sg on pg.goods_id = sg.id left join  "
                . DB_PREFIX . "goods as sgl on sg.goods_id = sgl.goods_id where ps.`store_id` = $this->storeid  and ps.`mark` =1  and pg.goods_id = '{$goods_id}' and pg.goods_key ='{$goods_key}' and ps.id = '{$prom_id}'";
        $rs = $this->goodPromMod->querySql($sql);
        return $rs[0];
    }

    /**
     * 获取商品运费
     * @author wanyan
     * @date 2017-11-2
     */
    public function getShippingPrice($goods_id) {
        $sql = "select `goods_id`,`is_free_shipping`,`shipping_price` from " . DB_PREFIX . "store_goods where `id` ='{$goods_id}' and mark =1";
        $rs = $this->storeMod->querySql($sql);
        return $rs[0];
    }

    /**
     * 获取商品的分类信息
     * @author wanyan
     * @date 2017-11-2
     */
    public function getGoodCate($goods_id) {
        $sql_3 = "select gc.`id`,gcl.`category_name`,gc.parent_id,sg.sku from " . DB_PREFIX . "goods_category as gc 
          left join " . DB_PREFIX . "goods_category_lang as gcl on gc.id = gcl.category_id  
          left join " . DB_PREFIX . "store_goods as sg on sg.cat_id = gc.id 
          where gcl.lang_id = $this->langid and sg.id = '{$goods_id}'";
        $three = $this->storeMod->querySql($sql_3);
        $sql_2 = "select gc.`id`,gcl.`category_name`,gc.parent_id from " . DB_PREFIX . "goods_category as gc 
          left join " . DB_PREFIX . "goods_category_lang as gcl on gc.id = gcl.category_id 
          where gcl.lang_id = $this->langid and gc.id = " . $three[0]['parent_id'];
        $two = $this->storeMod->querySql($sql_2);
        $sql_1 = "select gc.`id`,gcl.`category_name`,gc.parent_id from " . DB_PREFIX . "goods_category as gc 
          left join " . DB_PREFIX . "goods_category_lang as gcl on gc.id = gcl.category_id 
          where gcl.lang_id = $this->langid and gc.id = " . $two[0]['parent_id'];
        $one = $this->storeMod->querySql($sql_1);
        $arr = array(
            'one' => $one[0]['category_name'],
            'two' => $two[0]['category_name'],
            'three' => $three[0]['category_name'],
            'sku' => $three[0]['sku'],
        );
        return $arr;
    }

    /**
     * 获取商品规格
     * @param $goods_id|商品id  $type=1 读取商品原有规格属性  2 读取区域编辑后的规格属性
     * @return array
     */
    public function get_spec($goods_id, $store_goods_id, $type = 1, $goods_key) {
        $goods_key = implode(',', $goods_key);
        $storeGoodMod = &m("storeGoodItemPrice");
        //商品规格 价钱 库存表 找出 所有 规格项id
        //$keys = M('SpecGoodsPrice')->where("goods_id", $goods_id)->getField("GROUP_CONCAT(`key` SEPARATOR '_') ");
        if ($type == 1) {
            $sql = "select GROUP_CONCAT(`key` SEPARATOR '_') from  " . DB_PREFIX . "goods_spec_price where goods_id=" . $goods_id;
        } else {
            $sql = "select GROUP_CONCAT(`key` SEPARATOR '_') from  " . DB_PREFIX . "store_goods_spec_price where store_goods_id=" . $store_goods_id;
        }
        $keys = $storeGoodMod->querySql($sql);
        $filter_spec = array();
        if ($keys) {
            $keys = str_replace('_', ',', $keys[0]["GROUP_CONCAT(`key` SEPARATOR '_')"]);
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
                     WHERE b.id IN($keys) and al.lang_id=" . $this->langid . " and bl.lang_id=" . $this->langid . " AND bl.item_id in ($goods_key) ORDER BY b.id";
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

    /**
     * 获取当前商品的限量
     * @author wanyan
     * @date 2017-1-17
     */
    public function checkLimit() {
        $lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '';
        $langinfo = $this->getShorthand($lang_id);
        $this->load($langinfo['shorthand'], 'Promotion/Promotion');
        $a = $this->langData;
        $prom_id = !empty($_REQUEST['prom_id']) ? intval($_REQUEST['prom_id']) : '';
        $store_goods_id = !empty($_REQUEST['store_goods_id']) ? intval($_REQUEST['store_goods_id']) : '';
        $good_keys = !empty($_REQUEST['good_keys']) ? htmlspecialchars($_REQUEST['good_keys']) : '';
        $goods_num = !empty($_REQUEST['goods_num']) ? intval($_REQUEST['goods_num']) : '';
        $sql = "select limit_amount from " . DB_PREFIX . "promotion_goods WHERE `prom_id` = '{$prom_id}' and `goods_id` = '{$store_goods_id}' and `goods_key` = '{$good_keys}'";
        $rs = $this->goodPromDetailMod->querySql($sql);
        if (!empty($rs[0]['limit_amount'])) {
            if ($rs[0]['limit_amount'] < $goods_num) {
                $this->setData($info = array(), $status = 0, $message = "" . $a['notice'] . ".{$rs[0]['limit_amount']}");
            }
        }
    }

}
