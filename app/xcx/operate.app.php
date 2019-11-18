<?php
/**
 * 操作控制器
 * @author: luffy
 * @date:   2018-08-21
 */
class OperateApp extends BasePhApp{
    private $goodsCommentMod;
    private $cartMod;
    private $fxUserMod;
    private $userAddressMod;
    private $storeGoodMod;
    private $userMod;
    private $orderMod;
    private $orderGoodsMod;
    private $commentMod;
    private $userArticleMod;
    private $colleCtionMod;
    private  $orderDetailMod;
    private $areaGoodMod;
    private $ctgMod;
    private $storeGoodItemPriceMod;
    private  $goodsSpecPriceMod;
    private $goodsMod;
    private $amountLogMod;
    private $pointLogMod;
    private $rechargeAmountMod;

    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct();
        $this->ctgMod = &m('goodsClass');
        $this->goodsCommentMod = &m('goodsComment');
        $this->cartMod = &m('cart');
        $this->fxUserMod = &m('fxuser');
        $this->userAddressMod = &m('userAddress');
        $this->storeGoodMod = &m('storeGoods');
        $this->userMod = &m('user');
        $this->orderMod = &m('order');
        $this->orderGoodsMod = &m('orderGoods');
        $this->commentMod = &m('goodsComment');
        $this->userArticleMod = &m('userArticle');
        $this->colleCtionMod = &m('colleCtion');
        $this->orderDetailMod = &m('orderDetail');
        $this->areaGoodMod = &m('areaGood');
        $this->storeGoodItemPriceMod = &m('storeGoodItemPrice');
        $this->goodsSpecPriceMod=&m('goodsSpecPrice');
        $this->goodsMod=&m('goods');
        $this->amountLogMod=&m('amountLog');
        $this->pointLogMod = &m("pointLog");
        $this->rechargeAmountMod = &m('rechargeAmount');
    }

    /**
     * 析构函数
     */
    public function __destruct(){
    }

    /**
    * 商品列表
    * @author:tangp
    * @date:2018-09-17
    */
    public function getList()
    {
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : 29;
        //接受数据
        $cid = $_REQUEST['cid'];
        $page = !empty($_REQUEST['p']) ? $_REQUEST['p'] : 1;  //当前第几页
        $filter_param = array(); //筛选数组
        $brand = !empty($_REQUEST['b']) ? $_REQUEST['b'] : '';  //品牌
        $sort = !empty($_REQUEST['by']) ? $_REQUEST['by'] : 4; // 排序
        $start_price = !empty($_REQUEST['start_price']) ? trim($_REQUEST['start_price']) : 0;
        $end_price = !empty($_REQUEST['end_price']) ? trim($_REQUEST['end_price']) : '';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon=!empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '';
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        if (!empty($sort)) {
            $filter_param['by'] = $sort;
        }
//         $baseUrl = '?app=listPage&act=index&storeid=' . $this->storeid . '&lang=' . $this->langid . '&auxiliary=' . $auxiliary . '&cid=' . $cid . '&latlon='.$latlon.'&';
         //该分类下的商品
        $goodsList = $this->getGoodsList($lang_id, $store_id, $filter_param, $cid, $page);
         //商品排序
//        $goodsSort = $this->getGoodsSort($this->langid, $filter_param, $baseUrl);
        $this->setData($goodsList,1,'');
    }
    public function getGoodsList($langid, $storeid, $filter_param, $cid, $page) {
        $userId = $this->userId;
        $by = !empty($filter_param['by']) ? $filter_param['by'] : 1;
        $brandFilter = !empty($filter_param['b']) ? explode('_', $filter_param['b']) : array();
        $styleFilter = !empty($filter_param['s']) ? explode('_', $filter_param['s']) : array();
        $typeFilter = !empty($filter_param['t']) ? explode('_', $filter_param['t']) : array();
        $specFilter = !empty($filter_param['sp']) ? explode('_', $filter_param['sp']) : array();
        $priceFilter = !empty($filter_param['pr']) ? $filter_param['pr'] : array();
        $storeGoodsMod = &m('areaGood');

        if ($by == 1) {
            $orderBy = '  order by  shop_price  asc ';
        } else if ($by == 2) {
            $orderBy = '  order by  shop_price  desc ';
        } else if ($by == 3) {
            $orderBy = '  order by  s.add_time  desc ';
        }
        //获取分类下的商品
        if ($cid) {
            $goodsMod = &m('goods');
            $sql = ' SELECT goods_id, auxiliary_class  FROM   ' . DB_PREFIX . 'goods';
            $class = $goodsMod->querySql($sql);
            $goods = array();
            foreach ($class as $v) {
                if (empty($v['auxiliary_class']))
                    continue;
                $auxiliary_class = explode(":", $v['auxiliary_class']);
                foreach ($auxiliary_class as $ko => $vo) {
                    $cat_arrs = explode("_", $vo);
                    if (end($cat_arrs) == $cid) {
                        $goods[] = $v['goods_id'];
                    }
                }
            }
            $ids = implode(',', $goods);
            $where = ' where mark = 1 and store_id = ' . $storeid . ' and goods_id  in(' . $ids . ') and is_on_sale = 1';
            $sql = 'SELECT id FROM ' . DB_PREFIX . 'store_goods ' . $where;

            $dataD = $storeGoodsMod->querySql($sql);
            $good = $this->getYiweiArr($dataD);



            $where = '  where   mark=1  and  store_id =' . $storeid . '  and   cat_id  =' . $cid . '  and  is_on_sale =1';
            $sql = 'SELECT   id  FROM  ' . DB_PREFIX . 'store_goods  ' . $where;
            $dataC = $storeGoodsMod->querySql($sql);
            $goodsId = $this->getYiweiArr($dataC);
            $goodsId = array_merge($good, $goodsId);
        } else {
            $goodsId = array();
        }

        //品牌
        if (!empty($brandFilter)) {
            $brandids = implode(',', $brandFilter);
            $sql = 'SELECT  id   FROM   ' . DB_PREFIX . 'store_goods  WHERE   mark=1  and   store_id =' . $storeid . '   and  brand_id in(' . $brandids . ')';
            $dataB = $storeGoodsMod->querySql($sql);
            $arrB = $this->getYiweiArr($dataB);
            $goodsId = array_intersect($goodsId, $arrB);
        }
        //风格
        if (!empty($styleFilter)) {
            $styleids = implode(',', $styleFilter);
            $sql = 'SELECT  id   FROM   ' . DB_PREFIX . 'store_goods  WHERE   mark=1  and   store_id =' . $storeid . '   and  style_id in(' . $styleids . ')';
            $dataS = $storeGoodsMod->querySql($sql);
            $arrS = $this->getYiweiArr($dataS);
            $goodsId = array_intersect($goodsId, $arrS);
        }
        // 类型
        if (!empty($typeFilter)) {
            $typeids = implode(',', $typeFilter);
            $sql = 'SELECT  id   FROM   ' . DB_PREFIX . 'store_goods  WHERE  mark=1  and  store_id =' . $storeid . '   and  room_id in(' . $typeids . ')';
            $dataT = $storeGoodsMod->querySql($sql);
            $arrT = $this->getYiweiArr($dataT);
            $goodsId = array_intersect($goodsId, $arrT);
        }
        // 价格
        if (!empty($priceFilter)) {
            $arr2 = array();
            $arr1 = explode('-', $priceFilter);
            foreach ($arr1 as $key => $val) {
                $arr1[$key] = explode('_', $val);
            }
            foreach ($arr1 as $val) {
                if (in_array('*', $val)) {
                    $arr2[] = '  and  shop_price  >=' . $val[0];
                } else {
                    $arr2[] = '  and  shop_price  >=' . $val[0] . '  and  shop_price <= ' . $val[1];
                }
            }
            $res = array();
            foreach ($arr2 as $v) {
                $sql = 'SELECT  id   FROM   ' . DB_PREFIX . 'store_goods  WHERE   mark=1  and  store_id =' . $storeid . $v;
                $dataP = $storeGoodsMod->querySql($sql);
                $res = array_merge($res, $dataP);
            }
            $dataPr = $this->getYiweiArr($res);
            $goodsId = array_intersect($goodsId, $dataPr);
        }

        //规格筛选
        if (!empty($specFilter)) {
            $like = array();
            foreach ($specFilter as $val) {
                $like[] = '  `key` like "%' . $val . '%"  ';
            }
            $or = '(' . implode(' or', $like) . ')';

            $storeGoodsSpecPrice = &m('storeGoodItemPrice');
            $sql = 'select  store_goods_id  AS id  from  ' . DB_PREFIX . 'store_goods_spec_price  where  ' . $or;
            $dataSp = $storeGoodsSpecPrice->querySql($sql);
            $dataSp = $this->getYiweiArr($dataSp);
            $dataSp = array_unique($dataSp);
            $goodsId = array_intersect($goodsId, $dataSp);
        }
        //添加分页类
        include(ROOT_PATH . '/data/page/pageClass.php');
        //商品列表
        $total = count($goodsId);  //总条数
        $uri = urldecode(http_build_query($filter_param));
        $url = '?app=listPage&act=index&storeid=' . $storeid . '&lang=' . $langid . '&cid=' . $cid . '&' . $uri; //
        $pagesize = $this->pagesize; //每页显示条数
        $curpage = $page;  //当前页数
        $limit = '  limit ' . ($curpage - 1 ) * $pagesize . ',' . $pagesize;
        //实例化分页类
        $pageClass = new page2($total, $pagesize, $curpage, $url, 2);
        // 输出分页html
        $pagelink = $pageClass->myde_write();
        //
        $gids = implode(',', $goodsId);
        //多语言商品
        if ($by == 5) {
            // $this->load($this->shorthand, 'listpage/listpage');
            // $a = $this->langData;
            //秒杀商品
            $seckMod = &m('spikeActivity');
            $goodsByMod = &m('groupbuy');
            $goodPromMod = &m('goodProm');
            $promotionMod = &m('goodPromDetail');
            $cartMod = &m('cart');
            $curtime = time();
            $today = strtotime(date('Y-m-d', time()));
            $now = $curtime - $today;
            $where1 = 'WHERE s.store_id =' . $storeid . '  and  ' . $curtime . ' > s.start_time and g.mark=1 and g.is_on_sale=1 ';
            $sql1 = 'SELECT  s.id as cid,s.`name`,s.start_time,s.end_time,s.start_our,s.end_our,s.store_id,s.store_goods_id as id,gl.original_img,s.content,s.item_name,s.item_key,s.discount,s.o_price,s.price,s.goods_num,g.is_free_shipping FROM  '
                . DB_PREFIX . 'spike_activity as s left join '
                . DB_PREFIX . 'store_goods as g on  s.store_goods_id = g.id  left join '
                . DB_PREFIX . 'goods as gl on  g.goods_id = gl.goods_id ' . $where1;
            $spikeArr = $seckMod->querySql($sql1);
            foreach ($spikeArr as $k => $item) {
                if (($curtime > $item['start_time']) && ($curtime < $item['end_time'])) {
                    if (($now >= $item['start_our']) && ($now <= $item['end_our'])) {
                        $spikeArr[$k]['in_time'] = 2;
                    } else {
                        $spikeArr[$k]['in_time'] = 1;
                    }
                } else {
                    $spikeArr[$k]['in_time'] = 3;
                }

                $child_info = $storeGoodsMod->getLangInfo($item['id'], $langid);

                if ($child_info) {
                    $k_name = $child_info['goods_name'];
                    $spikeArr[$k]['goods_name'] = $k_name;
                }

            }

            foreach ($spikeArr as &$item) {
                //翻译处理

                $item['preferential'] = $a['spike'];
                $item['source'] = 1; //优惠商品标记
                //是否包邮
                switch ($item['is_free_shipping']) {
                    case 1:
                        $item['isfree'] = $a['Package'];  // Package mail
                        break;
                    case 2:
                        $item['isfree'] = $a['pack']; //Don't pack mail
                        break;
                    default:
                        $item['isfree'] = $a['pack'];
                }
            }


            //团购商品
            $where2 = '  where  b.store_id = ' . $storeid . ' and  b.mark =1 and g.mark=1 and g.is_on_sale=1';
            $sql2 = 'SELECT  b.id,b.goods_id,b.store_id,b.start_time,b.end_time,b.group_goods_price,b.virtual_num,l.original_img,b.goods_price,b.goods_name  FROM  '
                . DB_PREFIX . 'goods_group_buy  AS b  LEFT JOIN  '
                . DB_PREFIX . 'store_goods AS g ON b.`goods_id` = g.id  LEFT JOIN '
                . DB_PREFIX . 'goods AS l ON g.`goods_id` = l.`goods_id` ' . $where2;
            $data = $goodsByMod->querySql($sql2);

            foreach ($data as $k => $val) {
                //活动的状态的更改
                if ($curtime < $val['start_time']) {
                    //未开始
                    $goodsByMod->doEdit($val['id'], array('is_end' => 3));
                } else if (( $curtime > $val['start_time'] ) && ( $curtime < $val['end_time'] )) {
                    //进行中
                    $goodsByMod->doEdit($val['id'], array('is_end' => 1));
                } else if ($curtime > $val['end_time']) {
                    //结束
                    $goodsByMod->doEdit($val['id'], array('is_end' => 2));
                }
            }

            $where3 = 'WHERE  l.`lang_id` = ' . $langid . '  and  b.store_id =' . $storeid . '  AND b.is_end =1 AND b.mark = 1 and g.mark=1 and g.is_on_sale=1 ';
            $sql3 = 'SELECT  b.id as cid,b.goods_id as id,b.store_id,b.end_time,b.group_goods_price as price,b.virtual_num,gsl.original_img,b.goods_price as o_price,l.`goods_name`,b.goods_spec_key as item_key  FROM  '
                . DB_PREFIX . 'goods_group_buy   AS b  LEFT JOIN  '
                . DB_PREFIX . 'store_goods AS g ON b.`goods_id` = g.id LEFT JOIN  '
                . DB_PREFIX . 'goods_lang AS l ON g.`goods_id` = l.`goods_id`  LEFT JOIN  '
                . DB_PREFIX . 'goods AS gsl ON g.`goods_id` = gsl.`goods_id` ' . $where3;
            $groupByGoodArr = $goodsByMod->querySql($sql3);
            foreach ($groupByGoodArr as &$item) {
                $item['goods_name']= $cartMod->getGoodNameById($item['id'], $langid);
                $item['preferential'] = $a['groupBuy'];
                $item['source'] = 2; //优惠商品标记
                //是否包邮
                switch ($item['is_free_shipping']) {
                    case 1:
                        $item['isfree'] = $a['Package'];  // Package mail
                        break;
                    case 2:
                        $item['isfree'] = $a['pack']; //Don't pack mail
                        break;
                    default:
                        $item['isfree'] = $a['pack'];
                }
            }
            //促销商品
            $this->checkOver();
            // 获取正在进行或者未开始的促销活动
            $sql4 = " select ps.id as cid,ps.*,pg.goods_id as id,pg.goods_key as item_key,pg.goods_key_name,pg.goods_name,sgl.original_img,pg.goods_price as o_price,pg.discount_price as price,s.is_free_shipping from "
                . DB_PREFIX . "promotion_sale as ps left join "
                . DB_PREFIX . "promotion_goods as pg on  ps.id = pg.prom_id left join "
                . DB_PREFIX . "store_goods as s on pg.goods_id = s.id  left join "
                . DB_PREFIX . "goods as sgl on s.goods_id = sgl.goods_id  where ps.`store_id` = $this->storeid and ps.`status` in (1,2) and ps.`mark` =1 order by ps.status desc,ps.id desc";
            $promotionGoodsArr = $goodPromMod->querySql($sql4);
            foreach ($promotionGoodsArr as &$item) {
                $item['goods_name']= $cartMod->getGoodNameById($item['id'], $langid);
                $item['preferential'] = $a['promotion'];
                $item['source'] = 3; //优惠商品标记
                //是否包邮
                switch ($item['is_free_shipping']) {
                    case 1:
                        $item['isfree'] = $a['Package'];  // Package mail
                        break;
                    case 2:
                        $item['isfree'] = $a['pack']; //Don't pack mail
                        break;
                    default:
                        $item['isfree'] = $a['pack'];
                }
            }
            $res = array();
            $res['data'] = array_merge( $groupByGoodArr, $promotionGoodsArr,$spikeArr);
            return $res;
        } else {
            $where = '  where   s.store_id =' . $storeid . '  and   s.id  in(' . $gids . ')   and   s.mark=1   and   s.is_on_sale =1  AND l.`lang_id` = ' . $langid;

            $sql = 'SELECT s.id,s.`goods_id`,s.`cat_id`,s.`store_id`,l.`goods_name`,l.`lang_id`,s.`shop_price`,s.`market_price`,s.`brand_id`,gl.`original_img`,s.is_free_shipping
                FROM  '
                    . DB_PREFIX . 'store_goods AS s LEFT JOIN  '
                    . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id` LEFT JOIN '
                    . DB_PREFIX . 'goods AS gl ON s.`goods_id` = gl.`goods_id` ' . $where . $orderBy . $limit;
            $arr = $storeGoodsMod->querySql($sql);
            //加载语言包
            // $this->load($this->shorthand, 'listpage/listpage');
            $a = $this->langData;
            foreach ($arr as &$item) {
            //店铺商品打折
            $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $storeid;
            $store_arr = $storeGoodsMod->querySql($store_sql);
            $item['shop_price'] = number_format($item['shop_price'] * $store_arr[0]['store_discount'],2);
                //是否包邮
                switch ($item['is_free_shipping']) {
                    case 1:
                        $item['isfree'] = $a['Package'];  // Package mail
                        break;
                    case 2:
                        $item['isfree'] = $a['pack']; //Don't pack mail
                        break;
                    default:
                        $item['isfree'] = $a['pack'];
                }
                //收藏商品
                $sql_collection = 'select * from ' . DB_PREFIX . 'user_collection where user_id=' . $userId . ' and store_id=' . $storeid;
//            echo $sql_collection;exit;
                $data_collection = $storeGoodsMod->querySql($sql_collection);
//            var_dump($data_collection);exit;
                foreach ($data_collection as &$collertion) {
                    if ($collertion['good_id'] == $item['id']) {
                        $item['type'] = 1;
                    }
                }
            }
            //组装数据
            $res = array();
            $res['data'] = $arr;
            $res['pagelink'] = $pagelink;
            $res['count'] = $total;

            return $res;
        }
    }
     public function getYiweiArr($arr) {
        $data = array();
        foreach ($arr as $key => $val) {
            $data[] = $val['id'];
        }
        return $data;
    }
    public function getGoodsSort($langid, $filter_param, $baseUrl) {
        //加载语言包
        // $this->load($this->shorthand, 'listpage/listpage');
        $a = $this->langData;
        $sortFilter = !empty($filter_param['by']) ? $filter_param['by'] : 1;
        unset($filter_param['by']);
        $sort = array(
            // 1 => array('by' => 1, 'val' => $a['From']),
            // 2 => array('by' => 2, 'val' => $a['high']),
            3 => array('by' => 3, 'val' => '新品'),
            4 => array('by' => 4, 'val' => '综合'),
            5 => array('by' => 5, 'val' => '优惠')
        );
        $uri = urldecode(http_build_query($filter_param));
        foreach ($sort as $key => $val) {
            if (empty($uri)) {
                $sort[$key]['href'] = $baseUrl . $uri . 'by=' . $val['by'];
            } else {
                $sort[$key]['href'] = $baseUrl . $uri . '&by=' . $val['by'];
            }
            if ($val['by'] == $sortFilter) {
                $sort['selected'] = $val['val'];
            }
        }
        return $sort;
    }

    /**
     * 用户收藏
     * @author: tangp
     * @date:   2018-08-21
     */
    public function collection(){
        $style = $_REQUEST['style'];//获取收藏类型
        $userId = $this->userId;
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        if (empty($style)){
            $this->setData('',0,'请传递收藏类型！');
        }
        //收藏 1为商品收藏 2为店铺收藏
        if ($style == 1){
            $userStoreMod =&m('colleCtion');
            $storeid = $_REQUEST['store_id'];
            $good_id = $_REQUEST['good_id'];
            $id = $_REQUEST['id'];
            $type = $_REQUEST['type'];
            if (empty($id)){
                $this->setData('',0,'请传递店铺商品id!');
            }
            if (empty($good_id)){
                $this->setData('',0,'请传递商品id！');
            }
            if (empty($type)) {
                $data = array(
                    'table' => 'user_collection',
                    'user_id' => $userId,
                    'store_id' => $storeid,
                    'adds_time' => time(),
                    'good_id' => $good_id,
                    'store_good_id' => $id
                );
                $res = $userStoreMod->doInsert($data);

                if ($res){
                    $this->setData($id,1,'收藏成功');
                }

            }else{
                $where = ' store_id = ' . $storeid . ' AND user_id=' . $userId . ' AND good_id='.$good_id . ' AND store_good_id='.$id;
                $res = $userStoreMod->doDrops($where);

                if ($res){
                    $this->setData($id,0,'取消收藏');
                }

            }
        }elseif ($style == 2){
            $userStoreMod = &m('userStore');
            $storeid=$_REQUEST['store_id'];
            $type = $_REQUEST['type'];
            if (empty($storeid)){
                $this->setData('',0,'请传递店铺id！');
            }
            if (empty($type)){
                $data = array(
                    'table'   => 'user_store',
                    'user_id' => $userId,
                    'store_id'=> $storeid,
                    'add_time'=> time(),
                );
                $res = $userStoreMod->doInsert($data);
                if ($res){
                    $this->setData($storeid,1,'收藏成功');
                }
            }else{
                $where = ' store_id = ' . $storeid . ' AND user_id=' . $userId;
                $res = $userStoreMod->doDrops($where);

                if ($res){
                    $this->setData($storeid,0,'取消收藏');
                }
            }

        }
    }

    /**
     * 详情页商品收藏操作
     * @author:tangp
     * @date:2018-09-25
     */
    public function detailsCollect()
    {
        $type = $_REQUEST['type'];
        $good_id = $_REQUEST['id'];
        $userId = $this->userId;
        $storeid= !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $store_good_id = $_REQUEST['store_good_id'];
        if (empty($good_id)){
            $this->setData('',0,'请传递商品id');
        }
        if (empty($type)){
            $data = array(
                'table'         => 'user_collection',
                'user_id'       => $userId,
                'good_id'       => $good_id,
                'store_id'      => $storeid,
                'store_good_id' => $store_good_id,
                'adds_time'     => time()
            );
            $res = $this->colleCtionMod->doInsert($data);

            $this->setData($good_id,1,'收藏成功！');
        }else{
            $res = $this->colleCtionMod->doDrops('store_good_id =' . $good_id);
            $this->setData($good_id,0,'取消收藏！');
        }
    }
    /**
     * 文章详情页收藏操作
     * @author tangp
     * @date 2018-10-11
     */
    public function articleDetailsCollect()
    {
        $type = $_REQUEST['type'];
        $article_id = $_REQUEST['id'];
        $userId = $this->userId;
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        if (empty($article_id)){
            $this->setData('',0,'请传递文章id');
        }
        if (empty($type)){
            $data = array(
                'table'       => 'user_article',
                'user_id'     => $userId,
                'article_id'  => $article_id,
                'store_id'    => $storeid,
                'adds_time'   => time()
            );
//            var_dump($data);die;
            $res = $this->userArticleMod->doInsert($data);
            if ($res){
                $this->setData($article_id,1,'收藏成功！');
            }
        }else{
            $where = ' article_id = ' . $article_id . ' AND user_id=' . $userId;
//            var_dump($where);die;
            $res = $this->userArticleMod->doDrops($where);
            if ($res){
                $this->setData($article_id,0,'取消收藏');
            }
        }
    }
    /**
     * 文章收藏
     * @author:tangp
     * @date:2018-09-25
     */
    public function articleCollect()
    {
        $type = $_REQUEST['type'];
        $article_id = $_REQUEST['id'];
        $userId = $this->userId;
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        if (empty($article_id)){
            $this->setData('',0,'请传递文章id');
        }
        if (empty($type)){
            $data = array(
                'table'       => 'user_article',
                'user_id'     => $userId,
                'article_id'  => $article_id,
                'store_id'    => $storeid,
                'adds_time'   => time()
            );
//            var_dump($data);die;
            $res = $this->userArticleMod->doInsert($data);
            if ($res){
                $this->setData($article_id,1,'收藏成功！');
            }
        }else{
            $where = ' article_id = ' . $article_id . ' AND user_id=' . $userId;
//            var_dump($where);die;
            $res = $this->userArticleMod->doDrops($where);
            if ($res){
                $this->setData($article_id,0,'取消收藏');
            }
        }
    }
    /**
    * 商品评价显示全部
    * @author:tangp
    * @date:2018-09-05
    */
    public function commentIndex()
    {
      $id = !empty($_REQUEST['gid']) ? $_REQUEST['gid'] : 0;
      $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
      $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->store_id;  //所选的站点id
      $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
      $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
      //获取商品评价数量
      $good_all_num = " select count(1) as all_num from bs_goods_comment  where goods_id ={$id}";
      $good_all_num = $this->goodsCommentMod->querySql($good_all_num);
      //获取评价列表信息
      $eva_sql = 'select * from ' . DB_PREFIX . 'goods_comment
                   where  goods_id  = ' . $id . ' and store_id = ' . $storeid . '   order by comment_id desc ';
      $list = $this->goodsCommentMod->querySql($eva_sql);
      $new_list = array_map(function($vo){
        $vo['img'] = explode(',', $vo['img']);
        return $vo;
      },$list);
      $langData = array(

      );
      $data = array(
        'langData' => $langData,
        'listData' => $new_list
      );

      $this->setData($data,1,'');
    }

    /**
     * 加入购物车
     * @author:tangp
     * @date:2018-09-05
     */
    public function doCart()
    {

        $store_goods_id = !empty($_REQUEST['store_goods_id']) ? htmlspecialchars($_REQUEST['store_goods_id']) : '';
        $item_id = !empty($_REQUEST['item_id']) ? htmlspecialchars($_REQUEST['item_id']) : '';
        $goods_num = !empty($_REQUEST['goods_num']) ? htmlspecialchars($_REQUEST['goods_num']) : '';
        $laton = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';

        $prom_id = !empty($_REQUEST['prom_id']) ? htmlspecialchars($_REQUEST['prom_id']) : '0';
        $goods_price = !empty($_REQUEST['goods_price']) ? htmlspecialchars($_REQUEST['goods_price']) : '';
        $shipping_price = !empty($_REQUEST['shipping_price']) ? htmlspecialchars($_REQUEST['shipping_price']) : '0';
        $order_from = !empty($_REQUEST['order_from']) ? htmlspecialchars($_REQUEST['order_from']) : '0';
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars($_REQUEST['store_id']) : $this->store_id;
        $lang = !empty($_REQUEST['lang_id']) ? htmlspecialchars($_REQUEST['lang_id']) : $this->lang_id;
        $shipping_store_id = !empty($_REQUEST['shipping_store_id']) ? (int) ($_REQUEST['shipping_store_id']) : 0 ;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $type =!empty($_REQUEST['type']) ? $_REQUEST['type'] : 0;
        $deliveryType =!empty($_REQUEST['deliverytype']) ? $_REQUEST['deliverytype'] : 0;
        if(!$this->userId){
            $this->setData($info = array(),0, '参数错误');
        }
        $cart_data = array(
            'store_goods_id'    => $store_goods_id,
            'item_id'           => $item_id,
            'user_id'           => $this->userId,
            'prom_id'           => $prom_id,
            'store_id'          => $store_id,
            'goods_price'       => $goods_price,
            'shipping_price'    => $shipping_price,
            'order_from'        => $order_from,
            'fx_code'           => '',
            'shipping_store_id' => $shipping_store_id,
            'delivery_type' => $deliveryType
        );

        $rs = $this->cartMod->addCart($cart_data, $goods_num,$type);

        $data=array(
            'store_id'=>$store_id,
            'cart_id'=>$rs
        );
        if ($rs) {
            $this->setData($data, 1, '加入成功');
        } else {
            $this->setData($info = array(),0, '加入失败');
        }
    }

    /**
    * 优惠商品立即购买
    * @author:tangp
    * @date:2018-09-06
    */
    public function doBuy()
    {
        $lang              = !empty($_REQUEST['lang_id'])  ? intval($_REQUEST['lang_id']) : '29';
        $store_id          = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : $this->store_id;
        $store_goods_id    = !empty($_REQUEST['store_goods_id']) ? intval($_REQUEST['store_goods_id']) : '278';
        $good_keys         = !empty($_REQUEST['item_id']) ? $_REQUEST['item_id'] : 190;
        $goods_num         = !empty($_REQUEST['goods_num']) ? intval($_REQUEST['goods_num']) : '1';
        $goods_price       = !empty($_REQUEST['goods_price']) ? $_REQUEST['goods_price'] : '20.00';
        $shipping_price    = !empty($_REQUEST['shipping_price']) ? $_REQUEST['shipping_price'] : '0.00';
        $shipping_store_id = !empty($_REQUEST['shipping_store_id']) ? intval($_REQUEST['shipping_store_id']) : '47';
        $order_from        = !empty($_REQUEST['order_from']) ? intval($_REQUEST['order_from']) : '';
        $cid               = !empty($_REQUEST['cid']) ? intval($_REQUEST['cid']) : '26';
        $source            = !empty($_REQUEST['source']) ? intval($_REQUEST['source']) : '3';
        $auxiliary         = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon            = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : 0;
        $data = array(
            'goods_id' => $store_goods_id,
            'goods_keys' => $good_keys,
            'goods_num' => $goods_num,
            'goods_price' => $goods_price,
            'shipping_price' => $shipping_price,
            'shipping_store_id' => $shipping_store_id,
            'order_from' => $order_from,
            'source' => $source,
            'cid' => $cid
        );

//        var_dump($data);die;
        //睿积分兑换比例
        $pointSiteMod = &m('point');
        $point_price_site = $pointSiteMod->getOne(array("cond" => "1=1"));
        $rate = $point_price_site['point_rate'];
        $addr_id = !empty($_REQUEST['addr_id']) ? htmlspecialchars(trim($_REQUEST['addr_id'])) : '0';
        if ($addr_id) {
            $where = ' and id=' . $addr_id;
        } else {
            $where = ' and default_addr =1';
        }
        $addrSql = "select * from " . DB_PREFIX . 'user_address where user_id=' . $this->userId . $where;

        $userAddress = $this->userAddressMod->querySql($addrSql); // 获取用户的地址

        if ($addr_id == '0') {
            $addr_id = $userAddress[0]['id'];
        }
        $addresss = explode('_', $userAddress[0]['address']);
        $count = strpos($userAddress[0]['address'], "_");
        if($count==false){
            $str=$userAddress[0]['address'];
        }else{
            $str = substr_replace($userAddress[0]['address'], "", $count, 1);
        }


        foreach ($userAddress as $k => $v) {
            $userAddress[$k]['addressDetail'] = $str;
        }

        if ($data['goods_keys']) {
            $goods_keys_name = $this->getSpec($data['goods_keys'],$lang);
        }

        $totalMoney = ($data['goods_price'] * $data['goods_num']);
        $total = $totalMoney;
        $data['goods_keys'] = implode('_', $data['goods_keys']);
        $user_info = $this->userMod->getOne(array("cond"=>"id=" . $this->userId));
        //优惠券
        $sql = "select c_id from " . DB_PREFIX . 'user_coupon where user_id=' . $this->userId . ' and store_id= ' . $store_id;
        $info1 = $this->cartMod->querySql($sql);
        foreach ($info1 as $key => $val) {
            $cIds[] = $val['c_id'];
        }
        $cIds = implode(',', $cIds);
        //过期优惠券
        $cSql = "select * from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and end_time < ' . time();
        $ctSql = "select count(*) as ctotal from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and end_time < ' . time();

        $cData = $this->cartMod->querySql($cSql);
        $ctData = $this->cartMod->querySql($ctSql);
        foreach ($cData as $key => $val) {
            $cData[$key]['expire'] = 1;
            $cData[$key]['total'] = $ctData[0]['ctotal'];
        }

        //未过期优惠券
        $wSql = "select * from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and end_time > ' . time() . ' and start_time < ' . time();
        $wtSql = "select count(*) as wtotal from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and end_time > ' . time() . ' and start_time < ' . time();
        $wData = $this->cartMod->querySql($wSql);
        $wtData = $this->cartMod->querySql($wtSql);
        foreach ($wData as $key => $val) {
            $wData[$key]['expire'] = 0;
            $wData[$key]['total']  = $wtData[0]['wtotal'];
        }
        //睿积分抵扣
        $pointSiteMod = &m('point');
        $storePointMod = &m('storePoint');
        $storeMod = &m('store');
        $curMod = &m('currency');
        //获取订单总金额
        //获取最大积分支付比例
        $point_price_site = $pointSiteMod->getOne(array("cond" => "1=1"));
        $store_point_site = $storePointMod->getOne(array("cond" => "store_id=" . $store_id));
        $point_price_site['point_price'] = empty($store_point_site) ? 0 : $store_point_site['point_price'];
        if ($point_price_site) {
            $point_price = $point_price_site['point_price'] * $total / 100; //积分兑换最大金额
            $rmb_point = $point_price_site['point_rate']; //积分和RMB的比例
        } else {
            $point_price = 0;
            $rmb_point = 0;
        }

        //获取当前店铺币种以及兑换比例
        $store_info = $storeMod->getOne(array("cond" => "id=" . $store_id));
        //获取当前币种和RMB的比例
        $rate = $curMod->getCurrencyRate($store_info['currency_id']);
        //积分和RMB的比例
        if ($rate) {
            // $price_rmb = ceil(($point_price*$rate)/$rmb_point);
            //最大比例使用积分
            $price_rmb_point = ceil($point_price * $rate * $rmb_point);
            if ($price_rmb_point > $user_info['point']) {
                $point_price = $user_info['point'] * $rmb_point / 100;
                $price_rmb_point = ceil($point_price * $rate * $rmb_point);
            }
        }

        $maxAccount = number_format($point_price,2);
        $maxPoint   = $price_rmb_point;
        $ruiData=array('maxAccount'=>$maxAccount,'maxPoint'=>$maxPoint);
        $store_name = $this->storeName($store_id, $auxiliary, $lang);

        $shipping_store_name = $this->storeMod->getNameById($data['shipping_store_id'],$lang);
        $sku = $this->storeGoodMod->getSku($data['goods_id']);
        $goods_name = $this->storeGoodMod->getGoodsName($data['goods_id'],$lang);
//        var_dump($goods_name);die;
        $original_img = $this->storeGoodMod->getStoreGoodImg($data['goods_id'],$lang);

        $total = number_format($totalMoney,2);

        $order_Data = array(
            // 'langData'   => $langData,
            'goodsData'  => array(
                'goods_name' => $goods_name,
                'store_name' => $store_name,
                'shipping_store_name' => $shipping_store_name,
                'sku' => $sku,
                'original_img' => $original_img,
                'good_num' => $data['goods_num'],
                'goods_price' => $data['goods_price'],
                'money' =>number_format($total - $point_price, 2),
                'goods_key_name' => $goods_keys_name
            ),
            'userAddress' => $userAddress,
            'couponData'  => $cData,
            'expireCouponData' => $wData,
            'ruiData' => $ruiData,
            'storeName' => $store_name
        );

        $this->setData($order_Data,1,'');
    }

public function storeName($store_id, $auxiliary, $lang_id)
    {
        $sql = 'select gl.store_name  from  '
            . DB_PREFIX . 'store as g  left join '
            . DB_PREFIX . 'store_lang as gl on g.id = gl.store_id and gl.distinguish= ' . $auxiliary . ' and gl.lang_id= ' . $lang_id . ' where g.id  = ' . $store_id;
        $rs = $this->storeMod->querySql($sql);
        return $rs[0]['store_name'];
    }
    public function getSpec1($sp_key, $lang_id) {


        if ($sp_key) {


            foreach ($sp_key as $k1 => $v1) {
                $sql = "select `item_name` from " . DB_PREFIX . "goods_spec_item_lang where `item_id` = '{$v1}' and  `lang_id` ='{$lang_id}'";
                $spec_1 = $this->storeGoodMod->querySql($sql);
                $spec[] = $spec_1[0]['item_name'];
            }
//            var_dump($spec);die;
            $spec_key = implode(':', $spec);

            return $spec_key;
        }
    }
    public function getSpec($sp_key, $lang_id) {


        if ($sp_key) {

            $sp_key = explode('_', $sp_key);
            foreach ($sp_key as $k1 => $v1) {
                $sql = "select `item_name` from " . DB_PREFIX . "goods_spec_item_lang where `item_id` = '{$v1}' and  `lang_id` ='{$lang_id}'";
                $spec_1 = $this->storeGoodMod->querySql($sql);
                $spec[] = $spec_1[0]['item_name'];
            }
            $spec_key = implode(':', $spec);

            return $spec_key;
        }
    }
    /**
    * 购物车结算
    * @author:tangp
    * @date:2018-09-05
    */
    public function cartBuy()
    {
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $cart_ids = !empty($_REQUEST['item_id']) ? $_REQUEST['item_id'] : '';
        $userGoods = $this->cartMod->getGoodByCartId($cart_ids);
        $total = 0;
        $goods_num = 0;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $address = !empty($_REQUEST['address']) ? htmlspecialchars(trim($_REQUEST['address'])) : '';
        //睿积分兑换比例
        $pointSiteMod = &m('point');
        $point_price_site = $pointSiteMod->getOne(array("cond" => "1=1"));
        $rate = $point_price_site['point_rate'];
        // $this->assign('rate', $rate);
        // $this->assign('address', $address);
        // $this->assign('latlon', $latlon);
        // $this->assign('auxiliary', $auxiliary);
        foreach ($userGoods as $k => $v) {
            $userGoods[$k]['store_name'] = $this->storeMod->getNameById($v['store_id'], $this->lang_id);
            $userGoods[$k]['origin_img'] = $this->getGoodImg($v['goods_id'], $v['store_id']);
            $userGoods[$k]['totalMoney'] = number_format(($v['goods_price'] * $v['goods_num']), 2);
            $total += ($v['goods_price'] * $v['goods_num']);
            if ($v['spec_key']) {
                $info = explode('_', $v['spec_key']);
                foreach ($info as $k1 => $v1) {
                    $sql = "select `item_name` from " . DB_PREFIX . "goods_spec_item_lang where `item_id` = '{$v1}' and  `lang_id` = $this->lang_id";
                    $spec_1 = $this->cartMod->querySql($sql);
                    $spec[] = $spec_1[0]['item_name'];
                }
                $spec_key = implode(':', $spec);
                $userGoods[$k]['spec_key_name'] = $spec_key;
                $spec = array();
            }
            $goods_num += $v['goods_num'];
            $userGoods[$k]['shipping_store_name'] = $this->storeMod->getNameById($v['shipping_store_id'], $this->lang_id);
            $userGoods[$k]['goods_name'] = $this->cartMod->getGoodNameById($v['goods_id'], $this->lang_id);
        }
        $addr_id = !empty($_REQUEST['addr_id']) ? htmlspecialchars(trim($_REQUEST['addr_id'])) : '0';
        if ($addr_id) {
            $where = ' and id=' . $addr_id;
        } else {
            $where = ' and default_addr =1';
        }
        //获取收货地址
        $addrSql = "select * from " . DB_PREFIX . 'user_address where user_id=' . $this->userId . $where;
        $userAddress = $this->userAddressMod->querySql($addrSql); // 获取用户的地址
        if($addr_id=='0'){
            $addr_id=$userAddress[0]['id'];
        }
        $this->assign('nowlatlon', $userAddress[0]['latlon']);
        if (empty($userAddress[0])) { // 添加*/
            $this->assign('flag', 1);
        } else {
            $this->assign('flag', 2);
            $addresss = explode('_', $userAddress[0]['address']);
            // $this->assign('city', $addresss[0]);
            $count = strpos($userAddress[0]['address'], "_");
            $str = substr_replace($userAddress[0]['address'], "", $count, 1);
            // $this->assign('address1', $str);
            // $this->assign('userAddress', $userAddress[0]);
            /* $this->assign('detailAddress', $this->getAddress($userAddress['store_address'])); */
        }
        //优惠券
        $sql = "select c_id from " . DB_PREFIX . 'user_coupon where user_id=' . $this->userId . ' and store_id= ' . $storeid;
        $info = $this->cartMod->querySql($sql);
        foreach ($info as $key => $val) {
            $cIds[] = $val['c_id'];
        }
        $cIds = implode(',', $cIds);
        //过期优惠券
        $cSql = "select * from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and end_time < ' . time();
        $ctSql = "select count(*) as ctotal from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and end_time < ' . time();
        $cData = $this->cartMod->querySql($cSql);
        $ctData = $this->cartMod->querySql($ctSql);
        // $this->assign('ctotal', $ctData[0]['ctotal']);
        foreach ($cData as $key => $val) {
            $cData[$key]['expire'] = 1;
        }
        //未过期优惠券
        $wSql = "select * from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and end_time > ' . time() . ' and start_time < ' . time();
        $wtSql = "select count(*) as wtotal from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and end_time > ' . time() . ' and start_time < ' . time();
        $wData = $this->cartMod->querySql($wSql);
        $wtData = $this->cartMod->querySql($wtSql);
        // $this->assign('wtotal', $wtData[0]['wtotal']);
        foreach ($wData as $key => $val) {
            $wData[$key]['expire'] = 0;
        }
        // $this->assign('wData', $wData);
        // $this->assign('cData', $cData);
        $userMod = &m('user');
        $user_info = $userMod->getOne(array("cond" => "id = " . $this->userId));
        $this->assign('user_info', $user_info);
        $goodNum = count(explode(',', $cart_ids));
        //睿积分抵扣
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
        //积分和RMB的比例
        if ($rate) {
            // $price_rmb = ceil(($point_price*$rate)/$rmb_point);
            //最大比例使用积分
            $price_rmb_point = ceil($point_price * $rate * $rmb_point);
            if ($price_rmb_point > $user_info['point']) {
                $point_price = $user_info['point'] * $rmb_point / 100;
                $price_rmb_point = ceil($point_price * $rate * $rmb_point);
            }
        }

        $money = number_format($total - $point_price, 2);

        $totalMoney = $total;

        $total_num = $goods_num;

        $data = array(
          'userGoods' => $userGoods,
          'total'     => $totalMoney
        );


        $this->setData($data,1,'');
    }

    /**
     * 获取多商品运费
     */
    public function getShippingfee()
    {
        $storeId = !empty($_REQUEST['storeId']) ? htmlspecialchars(trim($_REQUEST['storeId'])) : 0;
        $goodsList = !empty($_REQUEST['goodsList']) ? htmlspecialchars(trim($_REQUEST['goodsList'])) : array();
        $storeFareRuleMod = &m('storeFareRule');
        if (!empty($storeId) && !empty($goodsList)) {
            $goodsIdNum = explode(',', $goodsList);
            $goodsInfo = array();
            $storeGoodsMod = &m('storeGoods');
            foreach($goodsIdNum as $v) {
                $temp = explode('-', $v);
                $goodsId = $storeGoodsMod->getOne(array("cond"=>"id = {$temp[0]}"));
                $goodsInfo[] = array(
                    'goods_id' => $goodsId['goods_id'],
                    'number' => $temp[1]
                );
            }
            $pei_discount = $storeFareRuleMod->getFare($goodsInfo, $storeId);
            $pei_discount = number_format($pei_discount, 2, '.', '');
        } else {
            $pei_discount = '0.00';
        }
        $this->setData($pei_discount, 1, '');
    }

    /**
    * 购物车提交商品结算页面配送方式切换
    * @author:tangp
    * @date:2018-09-05
    */
    public function getMoney()
    {
      $type = !empty($_REQUEST['type']) ? htmlspecialchars(trim($_REQUEST['type'])) : 1;
      $total = !empty($_REQUEST['total']) ? $_REQUEST['total'] : 0;
      $discount = !empty($_REQUEST['discount']) ? $_REQUEST['discount'] : 0;
      $point = !empty($_REQUEST['price']) ? $_REQUEST['price'] : 0;
      $youhui = !empty($_REQUEST['youhui']) ? $_REQUEST['youhui'] : 0;

      if ($type == 1) {
        $shipping_price = 0;
        $this->setData($shipping_price,1,'');
      }

      if ($type == 2) {
          $cart_id = !empty($_REQUEST['cart_ids']) ? htmlspecialchars($_REQUEST['cart_ids']) : '';
          $sql = "select `shipping_store_id` from " . DB_PREFIX . "cart where `id` in (" . $cart_id . ")";
          $info = $this->cartMod->querySql($sql);
          foreach ($info as $key => $val) {
              $store_ids[] = $val['shipping_store_id'];
          }
          $store_ids = array_unique($store_ids);
          $store_ids = implode(',', $store_ids);
          $sql = "select `fee` from " . DB_PREFIX . "store where `id` in (" . $store_ids . ")";
          $data = $this->cartMod->querySql($sql);

          foreach ($data as $k => $v) {
              $shipping_price += $v['fee'];
          }

          $this->setData($shipping_price,1,'');
      }

      if ($type == 3) {
          $cart_id = !empty($_REQUEST['cart_ids']) ? htmlspecialchars($_REQUEST['cart_ids']) : '';
          $sql = "select shipping_price,id from " . DB_PREFIX . "cart  where `id` in (" . $cart_id . ") order by shipping_price desc";
          $info = $this->cartMod->querySql($sql);
          $shipping_price = $info[0]['shipping_price'];
      }
      $price = number_format($shipping_price, 2);

      $totalMoney = $total + $shipping_price - $discount - $point - $youhui;
      if ($totalMoney <= 0) {
          $totalMoney = 0.01;
      }
      $totalMoney = number_format($totalMoney, 2);
      $info = array('price' => $price, 'totalMoney' => $totalMoney);

      $this->setData($price, '1', '');
    }

    public function getMoney1()
    {
        $type = !empty($_REQUEST['type']) ? htmlspecialchars(trim($_REQUEST['type'])) : 1;
        $total = !empty($_REQUEST['total']) ? $_REQUEST['total'] : 0;
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $shippingprice = !empty($_REQUEST['shippingprice']) ? $_REQUEST['shippingprice'] : 0;
        $discount = !empty($_REQUEST['discount']) ? $_REQUEST['discount'] : 0;
        $point = !empty($_REQUEST['price']) ? $_REQUEST['price'] : 0;
        $youhui = !empty($_REQUEST['youhui']) ? $_REQUEST['youhui'] : 0;

        if ($type == 1) {
            $shipping_price = 0;
        }
        if ($type == 2) {
            $sql = "select `fee` from " . DB_PREFIX . "store where `id` in (" . $storeid . ")";
            $data = $this->cartMod->querySql($sql);
            foreach ($data as $k => $v) {
                $shipping_price += $v['fee'];
            }
        }
        if ($type == 3) {
            $shipping_price = $shippingprice;
        }
        $price = number_format($shipping_price, 2);
        $totalMoney = $total + $shipping_price - $discount - $point - $youhui;
        if ($totalMoney < 0) {
            $totalMoney = 0.01;
        }
        $totalMoney = number_format($totalMoney, 2);
        $info = array('price' => $price);
        $this->setData($info, '1', '');
    }
    /**
    * 商品结算页优惠码多条商品
    * @author:tangp
    * @date:2018-09-06
    */
    public function getFxDiscount()
    {
        $fxuserMod      = &m('fxuser');
        $fxruleMod      = &m('fxrule');
        $storeGoodsMod  = &m('storeGoods');
        $storeFareRuleMod = &m('storeFareRule');
        $fxCode         = !empty($_REQUEST['fxPhone'])   ? htmlspecialchars(trim($_REQUEST['fxPhone'])) : '';
        $cart_ids       = !empty($_REQUEST['cart_ids']) ? $_REQUEST['cart_ids'] : '';
        $shippingfee    = !empty($_REQUEST['shippingfee']) ? $_REQUEST['shippingfee'] : 0;
        $point          = !empty($_REQUEST['point'])    ? $_REQUEST['point'] : 0;
        $youhui         = !empty($_REQUEST['youhui'])   ? $_REQUEST['youhui'] : 0; //订单总金额
        $totalMoney     = !empty($_REQUEST['totalMoney'])    ? $_REQUEST['totalMoney'] : 0; //订单总金额
        $goodsSendout   = !empty($_REQUEST['goodsSendout']) ? $_REQUEST['goodsSendout'] : 0; //运费    店铺商品id-配送方式-商品数量
        $storeId        = !empty($_REQUEST['storeId']) ? $_REQUEST['storeId'] : 0; //店铺id.
        $goodsSendout = explode(',',$goodsSendout);
        foreach($goodsSendout as $key=>$val){
            $temp=explode('-',$val);
            if($temp[1] == 2){
                $shippingFee[] = array(
                    "goods_id"=>$temp[0],
                    'number'=>$temp[2]
                );
            }
        }
        foreach($shippingFee as $key=>$val){
            $goodsId = $storeGoodsMod->getOne(array("cond"=>"`id` = {$val['goods_id']}"));
            $shippingFee[$key]['goods_id'] = $goodsId['goods_id'];
        }
        if(!empty($shippingFee)){
            $shippingPrice= $storeFareRuleMod->getFare($shippingFee,$storeId);
        }else{
            $shippingPrice = 0;
        }
        $shippingPrice = number_format($shippingPrice,2,".","");
        $info['shippingPrice'] = $shippingPrice;
        if( empty($fxCode) ){
            $info['discount'] = 0.00;
            $info['payMoney'] = $totalMoney - $point - $youhui +$shippingPrice;
            $this->setData($info, $status = 1, $message = '');
        }

        //获取分销人员信息
        $fxuserInfo     = $fxuserMod->getOne(array('cond' => "fx_code = '{$fxCode}' AND mark = 1"));
        if( $fxuserInfo['level'] != 3 ){
            $this->setData('', $status = 1, $message = '');
        }
        $discount_rate  = $fxuserInfo['discount'];
        $discount       = ($totalMoney - $point - $youhui) * $discount_rate * 0.01;
        $info['discount']   = $discount;    //推荐用户优惠折扣
        $info['payMoney']   = $totalMoney  - $discount - $point - $youhui + $shippingPrice;
        if ($info['payMoney'] <= 0) {
            $info['payMoney']   = 0;
        };
        $info['discount_rate']  = $discount_rate;
        $info['fx_user_id']     = $fxuserInfo['id'];
        //获取分销规则
        $info['rule_id']    = $fxruleMod->getFxRule($fxuserInfo['id']);
        $this->setData($info, $status = 1, $message = '');

    }
    /**
    * 商品结算页优惠码单个商品
    * @author:tangp
    * @date:2018-09-20
    */
    public function getFxDiscount1()
    {
        $fxUserMod = &m('fxuser');
        $fxUserTreeMod = &m('fxuserTree');
        $fxuserMod      = &m('fxuser');
        $fxruleMod      = &m('fxrule');
        $fxPhone = !empty($_REQUEST['fxPhone']) ? $_REQUEST['fxPhone'] : '';
        $goods_id = !empty($_REQUEST['goods_id']) ? $_REQUEST['goods_id'] : '';
        $goods_key = !empty($_REQUEST['goods_key']) ? $_REQUEST['goods_key'] : '';
        $goods_num = !empty($_REQUEST['goods_num']) ? $_REQUEST['goods_num'] : '';
        $shippingfee = !empty($_REQUEST['shippingfee']) ? $_REQUEST['shippingfee'] : '0.00';
        $point = !empty($_REQUEST['point']) ? $_REQUEST['point'] : 0;
        $youhui = !empty($_REQUEST['youhui']) ? $_REQUEST['youhui'] : 0;
        $totalMoney = !empty($_REQUEST['totalMoney']) ? $_REQUEST['totalMoney'] : 0;
//        var_dump($totalMoney);die;
        if (empty($fxPhone)){
            $info['discount'] =0.00;
            $info['payMoney'] = $totalMoney + $shippingfee  - $point - $youhui;
            $this->setData($info,$status='1',$message='');
        }
//获取分销人员信息
        $fxuserInfo     = $fxuserMod->getOne(array('cond' => "fx_code = '{$fxPhone}' AND mark = 1"));
        if( $fxuserInfo['level'] != 3 ){
            $this->setData(array(), $status = 1, $message = '');
        }


        $discount_rate = $fxuserInfo['discount'];;
        $discount       = ($totalMoney * $discount_rate * 0.01);
        $info['discount']   = $discount;
        $info['payMoney'] = $totalMoney + $shippingfee - $discount - $point - $youhui;
        if ($info['payMoney'] <= 0) {
            $info['payMoney'] = 0.01;
        }
        $info['discount_rate'] = $discount_rate;
        $info['fx_user_id']     = $fxuserInfo['id'];
        //获取分销规则
        $info['rule_id']    = $fxruleMod->getFxRule($fxuserInfo['id']);
        $this->setData($info, $status = 1, $message = '');
    }
    /**
    * 商品页勾选睿积分
    * @author:tangp
    * @date:2018-09-06
    */
    public function checkRui()
    {
      $userid =  $this->userId;

      $sql = "select point from " . DB_PREFIX . "user where id = " . $userid;
      // echo $sql;die;
      $user = &m('user');
      $info = $user->querySql($sql);
      // var_dump($info);die;
      $this->setData($info,1,'');
    }

    /**
    * 选择优惠卷
    * @author:tangp
    * @date:2018-09-06
    */
    public function checkYouhui()
    {
      $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
      $this->assign('auxiliary', $auxiliary);
      $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言商品
      $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->store_id;  //所选的站点id
      $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
      $this->assign('latlon', $latlon);
      $langData = array(
          $this->langData->project->not_expired,
          $this->langData->project->expired,
      );
      $userId = $this->userId;
      $sql = "select c_id from " . DB_PREFIX . 'user_coupon where user_id=' . $userId . ' and store_id= ' . $storeid;
      $info = $this->userMod->querySql($sql);
      foreach ($info as $key => $val) {
          $cIds[] = $val['c_id'];
      }
      $cIds = implode(',', $cIds);
      //过期优惠券
      $cSql = "select * from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and end_time < ' . time();
      $ctSql = "select count(*) as ctotal from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and end_time < ' . time();
      $cData = $this->userMod->querySql($cSql);
      $ctData = $this->userMod->querySql($ctSql);

      //未过期优惠券
      $wSql = "select * from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and end_time > ' . time() . ' and start_time < ' . time();
      $wtSql = "select count(*) as wtotal from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and end_time > ' . time() . ' and start_time < ' . time();
      $wData = $this->userMod->querySql($wSql);
      $wtData = $this->userMod->querySql($wtSql);
      $data = array(
          'langData' => $langData,
          'cData'    => $cData,
          'wData'    => $wData,
          'ctData'   => $ctData,
          'wtData'   => $wtData
      );


      $this->setData($data,1,'');
    }

    /**
     * 获取当前优惠订单总金额
     * @author wanyan
     * @date 2018-01-11
     */
    public function getOrderMoney($cart_ids) {
        $sql = "SELECT SUM(goods_price * goods_num) as orderAmount from " . DB_PREFIX . "cart where `id` IN ({$cart_ids})";
        $info = $this->cartMod->querySql($sql);
        return $info[0]['orderAmount'];
    }

    public function getGoodImg($goods_id, $store_id) {
        $sql = 'select gl.original_img  from  '
                . DB_PREFIX . 'store_goods as g  left join '
                . DB_PREFIX . 'goods as gl on g.goods_id = gl.goods_id where g.id  = ' . $goods_id;
        $rs = $this->storeMod->querySql($sql);
        return $rs[0]['original_img'];
    }

    /**
    * 申请退款页面
    * @author:tangp
    * @date:2018-09-07
    */
    public function qbRefund()
    {
      $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
      $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
      $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;  //所选的站点id
      $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言商品
      $langData = array(
        $this->langData->project->refund_order,
        $this->langData->project->refund_notes,
        $this->langData->project->to_refund_money
      );
      $userId = $this->userId;
      $where = ' buyer_id =' . $userId . " and order_sn = '{$_REQUEST['order_sn']}' " ;
      //列表页数据
      $sql = 'select * from ' . DB_PREFIX . 'order'
              . ' where' . $where . ' and store_id =' . $storeid;
      $data = $this->orderMod->querySql($sql);
      foreach ($data as $k => $v) {
          $sql = "select o.*,o.goods_id as ogoods_id,l.* from "
                  . DB_PREFIX . "order_goods as o left join "
                  . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
                  . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id "
                  . " where o.order_id=  '{$v['order_sn']}'  and o.refund_state = 0  and lang_id = " . $lang;
          $list = $this->orderGoodsMod->querySql($sql);
          foreach ($list as $k2 => $v2) {
              if ($v2['spec_key']) {
                  $k_info = $this->get_spec($v2['spec_key'], $lang);
                  foreach ($k_info as $k5 => $v5) {
                      $list[0][$k5]['spec_key_name'] = $v5['item_name'];
                  }
              }
          }
          $data[$k]['goods_list'] = $list;
          ;
      }
      $da = array(
        'listData' => $data,
        'langData' => $langData
      );
      $this->setData($da,1,'');
    }

    /**
     * 获取商品规格
     * @param $goods_id|商品id  $type=1 读取商品原有规格属性  2 读取区域编辑后的规格属性
     * @return array
     */
    public function get_spec($k, $lang) {
        $storeGoodMod = &m("storeGoodItemPrice");
        $k = str_replace('_', ',', $k);
        $sql4 = "SELECT a.`order`,b.*,al.spec_name,bl.`item_name`  FROM " . DB_PREFIX . "goods_spec AS a
                     INNER JOIN " . DB_PREFIX . "goods_spec_item AS b ON a.id = b.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_lang AS al ON a.id=al.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_item_lang as bl ON b.id=bl.item_id
                     WHERE b.id IN($k) and al.lang_id=" . $lang . " and bl.lang_id=" . $lang . " ORDER BY b.id";
        $filter_spec2 = $storeGoodMod->querySql($sql4);
        return $filter_spec2;
    }

    /**
    * 申请退款的操作
    * @author:tangp
    * @date:2018-09-07
    */
    public function refundGoods()
    {
      $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
      $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
      $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;  //所选的站点id
      $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim($_REQUEST['order_sn'])) : '';
      $reason_info = !empty($_REQUEST['reason_info']) ? htmlspecialchars(trim($_REQUEST['reason_info'])) : '';
      $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言商品
      if (empty($reason_info)) {
        $this->setData(array(),0,'申请理由不能为空');
      }
      if (!empty($order_sn)) {
        //查询订单是否存在有效   有效么判断
        $data = array(
            "table" => "order",
            "cond" => "order_sn= '{$order_sn}'" ,
        );
        $order_info = $this->orderMod->getData($data); //订单详细
        //2.退款退货表 插入
        if (is_array($order_info) && !empty($order_info)) {
            //2.退款退货表 插入
            $refund_return_data = array(
                "table" => "refund_return",
                "order_id" => $order_info[0]['order_id'], //订单ID
                "order_sn" => $order_info[0]['order_sn'], //订单编号
                "order_state" => $order_info[0]['order_state'], //订单状态：0(已取消)10(默认):未付款;20:已付款;30:已发货;40:已收货;',
                "reason_info" => $reason_info, //退货原因内容
                "store_id" => $order_info[0]['store_id'], //店铺ID
                "store_name" => $order_info[0]['store_name'], //店铺名称
                "buyer_id" => $order_info[0]['buyer_id'], //买家ID
                "buyer_name" => $order_info[0]['buyer_name'], //买家会员名
                "refund_amount" => $order_info[0]['order_amount'], //订单总价格
                "refund_amounts" => $order_info[0]['order_amount'], //退款金额
                "add_time" => time(), //添加时间
            );
            $refund_return_id = $this->orderMod->doInsert($refund_return_data);
            $this->orderMod->update_refund_time($storeid,$order_sn,1);
            $refund_state = 1;
            //3.订单商品表 更新
            $order_goods_data = array(
                "table" => "order_goods",
                'cond' => "order_id=  '{$order_sn}' and refund_state = 0",
                "set" => array(
                    "refund_state" => $refund_state,
                ),
            );
            $order_goods_result = $this->orderMod->doUpdate($order_goods_data);
            //4.订单表  更新
            $order_data = array(
                "table" => "order",
                'cond' => "order_sn= '{$order_sn}'",
                "set" => array(
                    "refund_state" => $refund_state,
                    "refund_amount" => $order_info[0]['order_amount'],
                ),
            );
            $order_goods_result = $this->orderMod->doUpdate($order_data);

            if ($order_goods_result) {
                //申请退款成功
                $this->setData(array(), 1, '申请退款成功');
            } else {
                //申请退款失败
                $this->setData(array(), 0, '申请退款失败');
            }
        } else {
            //提示订单错误
            $this->setData(array(), 0, '订单错误');
        }
      }
    }

    /**
    * 确认收货
    * @author:tangp
    * @date:2018-09-07
    */
    public function confirm()
    {
      $_data = explode("_", $_REQUEST['data']);
      $id = $_data[0];
      $state = $_data[1];
      $ops = $_data[2];
      $store_id = $_data[3];

      switch ($state) {
          case 10:
              if ($ops == "cancel") {
                  $set = array(
                      "order_state" => 0,
                  );
                  //取消订单退还积分
                  $res = $this->returnPoint($id);
                  }
          case 40:
              if ($ops == "receive") {
                  $set = array(
                      "order_state" => 50,
                      'finished_time' => time()
                  );
          /*        $reslut = $this->doOnePoint($id);*/

                      $this->doOrderPoint($id);



              }
              break;
      }

      $data = array(
          "table" => "order",
          'cond' => "order_sn = '{$id}'",
          'set' => $set
      );
      if ($ops == "cancel") {
          $data['set']['Appoint'] = 2;
          $data['set']['Appoint_store_id'] = $store_id;
      }

      $sql = " select `fx_phone` from " . DB_PREFIX . "order where `order_sn`='{$id}'";
      $ifo = $this->orderGoodsMod->querySql($sql);
      if ($ifo[0]['fx_phone']) {
          $rs = $this->distrCom($id); // 分销按钮
      }
      $res = $this->orderMod->doUpdate($data);
      $datas = array(
          "table" => "order_goods",
          'cond' => "order_id= '{$id}' ",
          'set' => $set,
      );
      $res_goods = $this->orderGoodsMod->doUpdate($datas);
      if ($res && $res_goods) {
          $this->setData(array(), 1, '操作成功');
      } else {
          $this->setData(array(), 0, '操作失败');
      }
    }
    public function confirmOrder()
    {
        $order_id = $_REQUEST['order_id'];

        // 订单信息 by xt 2018.01.24
        $orderMod = &m('order');
        $order = $orderMod->getOne(
            array(
                'cond' => "order_sn ='{$order_id}' "  ,
                'fields' => 'order_state',
            )
        );

        if ($order['order_state'] == 50) {
            $this->setData(array(),0,'订单已收货，请勿重复提交');
        }


        $user_id = $this->userId;
        $set = array(
            "order_state" => 50,
            'finished_time' => time(),
            'delivery_status'=>1
        );
        $data = array(
            "table" => "order",
            'cond' => "order_sn ='{$order_id}' "  ,
            'set' => $set
        );
        $datas = array(
            "table" => "order_goods",
            'cond' => "order_id ='{$order_id}' ",
            'set' => $set,
        );
        $this->doOrderPoint($order_id,$user_id);
        $this->fxUserMod->getAccount($order_id);
        $res = $this->orderMod->doUpdate($data);
//        var_dump($res);die;
        $res_goods = $this->orderGoodsMod->doUpdate($datas);
        $userOrderMod = &m('userOrder');
        $result = $userOrderMod->getOne(array('cond' => "`order_sn` = '{$order_id}'", 'fields' => "store_id"));
        $this->orderMod->update_receipt_time($result['store_id'],$order_id,2);
        $sql = " select `order_state`,order_id from " . DB_PREFIX . "order where `order_sn`='{$order_id}'";
        $orderInfo = $this->orderGoodsMod->querySql($sql);
        $orderRelationMod = &m('orderRelation');
        $orderRelationMod->insertOrderRelation($orderInfo[0]['order_id'], 2);

        if ($res && $res_goods){
            $this->setData(array(),1,'操作成功');
        }else{
            $this->setData(array(),0,'操作失败');
        }
    }
    /*
     * 取消订单退还积分
     * @author lee
     * @date 2018-6-22 15:03:17
     */
    public function returnPoint($id,$userId) {
        $userMod = &m('user');
        $pointLogMod = &m("pointLog");
        $point_log = $pointLogMod->getOne(array("cond" => "order_sn= '{$id}'"));
        //更新用户的积分值
        if ($point_log) {
            $user_id = $userId;
            $user_info = $userMod->getOne(array("cond" => "id=" . $user_id));
            $user_point = $user_info['point'] + $point_log['expend'];

            $res = $userMod->doEdit($user_id, array("point" => $user_point));
            //积分日志
            if ($res) {
                $logMessage = "取消订单：" . $id . " 获取：" . $point_log['expend'] . "睿积分";
                $this->addPointLog($user_info['phone'], $logMessage, $user_id, $point_log['expend'], '-');
            }
        }
    }
    /**
     * 根据分销码佣金分配
     * @author wanyan
     * @date 2017-11-21
     */
    public function distrCom($order_id) {
        $fxMainOrder = $this->orderMod->getOne(array('cond' => "`order_sn` = '{$order_id}'", 'fields' => '`order_id`,`order_sn`,`store_id`,buyer_id,buyer_name,buyer_email,buyer_address,store_id,order_amount,discount,fx_phone,fx_discount_rate'));
        $fxMainOrder['phone'] = $this->getUserPhone($fxMainOrder['buyer_id']);
        $res = $this->getRuler($fxMainOrder);

        return $res;
    }
    /**
     * 获取用户的电话号码
     * @author wanyan
     * @date 2017-11-21
     */
    public function getUserPhone($user_id) {
        $userAddress = &m('userAddress');
        $rs = $userAddress->getOne(array('cond' => "`user_id` = '{$user_id}'", 'fields' => "phone"));
        return $rs['phone'];
    }

    public function getRuler($mainInfo) {
        $sql = "SELECT fu.user_id,fu.real_name,fur.fx_level,fur.pid,fur.pidpid FROM " . DB_PREFIX . "fx_user as fu
            LEFT JOIN " . DB_PREFIX . "fx_usertree as fur ON fu.user_id = fur.user_id WHERE fu.telephone = '{$mainInfo['fx_phone']}'";
        $info = $this->fxRuleMod->querySql($sql);
        if ($info[0]['fx_level'] == 3) { // 如果三级分销商的分销码
            $firstUserId = $this->getUserTreeId($info[0]['pidpid']);
            $secondUserId = $this->getUserTreeId($info[0]['pid']);
            $fxRule = $this->getRuleDetail($firstUserId);
            $insert_data_main['lev1_revenue'] = ($fxRule['lev1_prop'] * 0.01 * $mainInfo['order_amount']); // 一级佣金
            $insert_data_main['lev2_revenue'] = ($fxRule['lev2_prop'] * 0.01 * $mainInfo['order_amount']); // 二级佣金
            $insert_data_main['lev3_revenue'] = (($fxRule['lev3_prop'] - $mainInfo['fx_discount_rate']) * 0.01 * $mainInfo['order_amount']); // 三级佣金
            $insert_data_main['lev2_user_id'] = $secondUserId; // 二级分销商ID
            $insert_data_main['lev2_user_name'] = $this->getDisUser($secondUserId); // 二级分销商姓名
            $insert_data_main['lev3_user_id'] = $info[0]['user_id']; // 三级分销商ID
            $insert_data_main['lev3_user_name'] = $this->getDisUser($info[0]['user_id']); //  三级分销商姓名
            //var_dump($firstUserId);die;
        } elseif ($info[0]['fx_level'] == 2) { // 如果二级分销商的分销码
            $firstUserId = $this->getUserTreeId($info[0]['pid']);  // 一级分销商ID
            $fxRule = $this->getRuleDetail($firstUserId);
            $insert_data_main['lev1_revenue'] = ($fxRule['lev1_prop'] * 0.01 * $mainInfo['order_amount']); // 一级佣金
            $insert_data_main['lev2_revenue'] = (($fxRule['lev2_prop'] - $mainInfo['fx_discount_rate']) * 0.01 * $mainInfo['order_amount']); // 二级佣金
            $insert_data_main['lev3_revenue'] = 0.00; // 三级佣金
            $insert_data_main['lev2_user_id'] = $info[0]['user_id']; // 二级分销商ID
            $insert_data_main['lev2_user_name'] = $this->getDisUser($info[0]['user_id']); // 二级分销商姓名
            $insert_data_main['lev3_user_id'] = 0; // 三级分销商ID
            $insert_data_main['lev3_user_name'] = ''; // 三级分销商姓名
        } elseif ($info[0]['fx_level'] == 1) {
//          $firstUserId = $this->getUserTreeId($info[0]['user_id']);  // 一级分销商ID
            $firstUserId = $info[0]['user_id'];
            $fxRule = $this->getRuleDetail($firstUserId);
            $insert_data_main['lev1_revenue'] = (($fxRule['lev1_prop'] - $mainInfo['fx_discount_rate']) * 0.01 * $mainInfo['order_amount']); // 一级佣金
            $insert_data_main['lev2_revenue'] = 0.00; // 二级佣金
            $insert_data_main['lev3_revenue'] = 0.00; // 三级佣金
            $insert_data_main['lev2_user_id'] = 0; // 二级分销商ID
            $insert_data_main['lev2_user_name'] = ''; // 二级分销商姓名
            $insert_data_main['lev3_user_id'] = 0; // 三级分销商ID
            $insert_data_main['lev3_user_name'] = ''; // 三级分销商姓名
        }
        $store_cate = $this->getCurStoreInfo($mainInfo['store_id']);
        $insert_data = array(
            'user_id' => $mainInfo['buyer_id'], // 购买人用户ID
            'user_name' => $mainInfo['buyer_name'],
            'phone' => $mainInfo['phone'],
            'fx_rule_id' => $fxRule['id'],
            'lev1_prop' => $fxRule['lev1_prop'],
            'lev2_prop' => $fxRule['lev2_prop'],
            'lev3_prop' => $fxRule['lev3_prop'],
            'lev1_user_id' => $firstUserId,
            'lev1_user_name' => $this->getDisUser($firstUserId),
//          'lev1_revenue' => ($fxRule['lev1_prop']*0.01*$goodInfo['goods_pay_price']), // 一级佣金
//          'lev2_user_id' => $secondUserId,
//          'lev2_user_name' => $this->getDisUser($secondUserId),
//          'lev2_revenue' => ($fxRule['lev2_prop']*0.01*$goodInfo['goods_pay_price']),// 二级佣金
//          'lev3_user_id' => $info[0]['user_id'],
//          'lev3_user_name' => $this->getDisUser($info[0]['user_id']),
            // 'lev3_revenue' => ($fxRule['lev3_prop']*0.01*$goodInfo['goods_pay_price']),// 三级佣金
            'order_id' => $mainInfo['order_id'],
            'order_sn' => $mainInfo['order_sn'],
            'order_money' => $mainInfo['order_amount'],
            'store_cate' => $store_cate['store_cate_id'],
            'store_id' => $mainInfo['store_id'],
            'discount' => $mainInfo['discount'],
            'discount_rate' => $mainInfo['fx_discount_rate'],
            'add_time' => time()
        );
        $insert_data_total = array_merge($insert_data, $insert_data_main);
        // var_dump($insert_data_total);die;
        $rs = $this->fxRevenueLogMod->doInsert($insert_data_total);
        return $rs;
    }
    //判断积分日志是否生成
    public function doOnePoint($id) {
        $pointLogMod = &m("pointLog");
        $res = $pointLogMod->getOne(array("cond" => "order_sn= '{$id}'" ));
        return $res;
    }

    /*
     * 订单积分获取
     * @author  lee
     * @date 2018-6-21 16:11:31
     */

    public function doOrderPoint($id,$user_id) {
        $pointSiteMod = &m('point');
        $storePointMod = &m('storePoint');
        $storeMod = &m('store');
        $curMod = &m('currency');
        $userMod = &m('user');
        $order_info = $this->orderMod->getOne(array("cond" => "order_sn= '{$id}'"));
        //获取该订单获取的积分值
        $point_price_site = $pointSiteMod->getOne(array("cond" => "1=1"));
        $store_point_site = $storePointMod->getOne(array("cond" => "store_id=" . $order_info['store_id']));
        $store_id = $order_info['store_id'];
        $store_info = $storeMod->getOne(array("cond" => "id=" . $store_id));
        $rate = $curMod->getCurrencyRate($store_info['currency_id']);

        $money = $store_point_site['order_point'] * $order_info['order_amount']/100;
        $point = ceil($money );
        //更新用户的积分值
//        $user_id = $user_id;
        $user_info = $userMod->getOne(array("cond" => "id=" . $user_id));
        $user_point = $user_info['point'] + $point;
        $res = $userMod->doEdit($user_id, array("point" => $user_point));

        //积分日志
        if ($res) {
            $logMessage = "消费订单：" . $order_info['order_sn'] . " 获取：" . $point . "睿积分";
            $this->addPointLog($user_info['phone'], $logMessage, $user_id, $point, '-', $order_info['order_sn']);
        }
    }

    //生成日志
    public function addPointLog($username, $note, $userid, $deposit, $expend, $order_sn = null) {
        $logData = array(
            'operator' => '--',
            'username' => $username,
            'add_time' => time(),
            'deposit' => $deposit,
            'expend' => $expend,
            'note' => $note,
            'userid' => $userid
        );
        if ($order_sn) {
            $logData['order_sn'] = $order_sn;
        }
        $pointLogMod = &m("pointLog");
        $pointLogMod->doInsert($logData);
    }
    /**
    * 收货评价页面
    * @author:tangp
    * @date:2018-09-07
    */
    public function evaluateDetails()
    {
      $userId = $this->userId;
      $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
      $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
      $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim($_REQUEST['order_sn'])) : '';
      $rec_id = !empty($_REQUEST['rec_id']) ? htmlspecialchars(trim($_REQUEST['rec_id'])) : '';
      $goods_id = !empty($_REQUEST['gid']) ? htmlspecialchars(trim($_REQUEST['gid'])) : '';
      $storeid = !empty($_REQUEST['store_id']) ? htmlspecialchars(trim($_REQUEST['store_id'])) : $this->store_id;
      $order_id = !empty($_REQUEST['order_id']) ? htmlspecialchars(trim($_REQUEST['order_id'])) : '';
      $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言商品
      $langData = array(
        $this->langData->public->evaluate,
        $this->langData->public->release,
        $this->langData->project->goods_evaluate,
        $this->langData->project->description_consistent,
        $this->langData->project->very_nice
      );
      $where = ' and o.buyer_id = ' . $userId . ' and  o.rec_id = ' . $rec_id;
      //列表页数据
      $sql = "select o.*,o.goods_id as ogoods_id,l.* from "
              . DB_PREFIX . "order_goods as o left join "
              . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
              . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id "
              . " where o.order_id= '{$order_sn}'   and lang_id = " . $lang . $where;
      $data = $this->orderGoodsMod->querySql($sql);
      $da = array(
        'langData' => $langData,
        'listData' => $data
      );
      $this->setData($da,1,'');
    }

    /**
    * 评价上传图片
    * @author:tangp
    * @date:2018-09-07
    */
    public function getUploadPicture()
    {
        $code = $_FILES['file'];
        if(is_uploaded_file($_FILES['file']['tmp_name'])) {
            $uploaded_file=$_FILES['file']['tmp_name'];

//            $user_path=$_SERVER['DOCUMENT_ROOT']."/text"."/m_pro/".$username;
            $user_path = 'upload/images/order/' . date('Ymd') ;
            //判断该用户文件夹是否已经有这个文件夹
            if(!file_exists($user_path)) {
                mkdir($user_path,0777,true);
            }

            //$move_to_file=$user_path."/".$_FILES['file']['name'];
            $file_true_name=$_FILES['file']['name'];
            $move_to_file=$user_path."/".time().rand(1,1000)."-".date("Y-m-d").substr($file_true_name,strrpos($file_true_name,"."));
            //echo "$uploaded_file   $move_to_file";
            if(move_uploaded_file($uploaded_file,iconv("utf-8","gb2312",$move_to_file))) {
//                echo $_FILES['file']['name']."--上传成功".date("Y-m-d H:i:sa");
                $filename= $move_to_file;


                $this->setData($filename,1,'上传成功');
            } else {
//                echo "上传失败".date("Y-m-d H:i:sa");
                $this->setData('',0,'上传失败');
            }
        } else {
//            echo "上传失败".date("Y-m-d H:i:sa");
                $this->setData('',0,'上传失败');
        }

    }
    /**
    * 收货评价发布
    * @author:tangp
    * @date:2018-09-07
    */
    public function addEvaluate()
    {
      $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
      $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //1中文，2英语
      $user_id = $this->userId;  //所选的站点登陆的id
      $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;  //所选的站点id
      $goods_id = !empty($_REQUEST['gid']) ? htmlspecialchars(trim($_REQUEST['gid'])) : '';
      $rec_id = !empty($_REQUEST['rec_id']) ? htmlspecialchars(trim($_REQUEST['rec_id'])) : '';
      $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim($_REQUEST['order_sn'])) : '';
      $order_id = !empty($_REQUEST['order_id']) ? htmlspecialchars(trim($_REQUEST['order_id'])) : '';
      $star_num = !empty($_REQUEST['star_num']) ? htmlspecialchars(trim($_REQUEST['star_num'])) : '';
      $evaluete_content = !empty($_REQUEST['evaluete_content']) ? htmlspecialchars(trim($_REQUEST['evaluete_content'])) : '';
      $goods_images = ($_POST['goods_images']) ? $_POST['goods_images'] : '';
      $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
      $arr = implode(',', $goods_images);
      $list = rtrim($arr, ',');
      $sql = "select username from bs_user where id=".$user_id;
      $res = $this->userMod->querySql($sql);
      $userName=$res[0]['username'];
      if(empty($evaluete_content)){
        $this->setData(array(),0,'评价内容不能为空');
      }
      $data = array(
          'goods_id' => $goods_id,
          'user_id' => $user_id,
          'order_id' => $order_id,
          'rec_id' => $rec_id,
          'store_id' => $store_id,
          'content' => $evaluete_content,
          'goods_rank' => $star_num,
          'img' => $list,
          'username' => $userName,
          'add_time' => time()
      );
      $res = $this->commentMod->doInsert($data);
      //3.订单商品表 更新
      $order = array(
          "table" => "order",
          'cond' => "order_sn= '{$order_sn}' " ,
          "set" => array(
              "evaluation_state" => 1,
          ),
      );
      $res_order = $this->orderMod->doUpdate($order);
      //3.订单商品表 更新
      $order_goods = array(
          "table" => "order_goods",
          'cond' => "order_id= '{$order_sn}'" ,
          "set" => array(
              "evaluation_state" => 1,
          ),
      );
      $res_order_goods = $this->orderGoodsMod->doUpdate($order_goods);
      $userOrderMod = &m('userOrder');
      //加入评价到新的店铺订单表
      $result = $userOrderMod->getOne(array('cond' => "`order_sn` = '{$order_sn}'", 'fields' => "store_id"));
      $this->orderMod->update_comment_time($result['store_id'],$order_sn,1);

      if ($res && $res_order && $res_order_goods) {
          $this->setData(array(), 1 ,'评价成功');
      } else {
          $this->setData(array(), 0, '评价失败');
      }
    }

    /**
    * 待付款取消订单
    * @author:tangp
    * @date:2018-09-07
    */
    public function cancel()
    {
      $_data = explode("_", $_REQUEST['data']);
      $id = $_data[0];
      $state = $_data[1];
      $ops = $_data[2];
      $store_id = $_data[3];

      switch ($state) {
          case 10:
              if ($ops == "cancel") {
                  $set = array(
                      "order_state" => 0,
                  );
                  //取消订单退还积分
                  $res = $this->returnPoint($id);
                  }
          case 40:
              if ($ops == "receive") {
                  $set = array(
                      "order_state" => 50,
                      'finished_time' => time()
                  );
          /*        $reslut = $this->doOnePoint($id);*/

                      $this->doOrderPoint($id);



              }
              break;
      }

      $data = array(
          "table" => "order",
          'cond' => "order_sn= '{$id}' ",
          'set' => $set
      );
      if ($ops == "cancel") {
          $data['set']['Appoint'] = 2;
          $data['set']['Appoint_store_id'] = $store_id;
      }

      $sql = " select `fx_phone` from " . DB_PREFIX . "order where `order_sn`='{$id}'";
      $ifo = $this->orderGoodsMod->querySql($sql);
      if ($ifo[0]['fx_phone']) {
          $rs = $this->distrCom($id); // 分销按钮
      }
      $res = $this->orderMod->doUpdate($data);
      $datas = array(
          "table" => "order_goods",
          'cond' => "order_id= '{$id}'" ,
          'set' => $set,
      );
      $res_goods = $this->orderGoodsMod->doUpdate($datas);
      if ($res && $res_goods) {
          $this->setData(array(), 1, '操作成功');
      } else {
          $this->setData(array(), 0 , '操作失败');
      }
    }
    public function cancel_order()
    {
        $order_id = !empty($_REQUEST['order_id']) ? $_REQUEST['order_id'] : '';

        $orderMod=&m('order');
        $orderSn = !empty($_REQUEST['orderSn']) ? htmlspecialchars(trim($_REQUEST['orderSn'])) : '';//订单编号
        $res=$orderMod->xcxCancleOrder($order_id);
        if($res==0){
            $this->setData('',0,'订单已失效');
        }else{
            $this->setData('',1,'订单已取消');
        }

    }
    /**
    * 删除订单
    * @author:tangp
    * @date:2018-09-07
    */
    public function dele()
    {
      $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
      $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;  //所选的站点id
      $lang = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;  //多语言商品
      $set = array(
          "mark" => 2,
      );
      $datas = array(
          "table" => "order",
          'cond' => "order_id=  '{$id}'",
          'set' => $set,
      );
      $data = $this->orderMod->doUpdate($datas);
      if ($data) {   //删除成功
          $this->setData(array(), 1, '删除成功');
      } else {
          $this->setData(array(), 0, '删除失败');
      }
    }

    /**
    * 立即付款(页面)
    * @author:tangp
    * @date:2018-09-07
    */
    public function orderPay()
    {
        $order_id = !empty($_REQUEST['order_id']) ? htmlspecialchars(trim($_REQUEST['order_id'])) : '';
        $lang = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : $this->lang_id;
        $langData = array(
          $this->langData->public->yuan,
          $this->langData->public->immediately_payment,
          $this->langData->project->need_pay_money
        );
        $sql = "select order_amount from bs_order where order_sn = '{$order_id}'";
        $res = $this->orderMod->querySql($sql);

        $order_amount = $res[0]['order_amount'];

        $data = array(
          'listData' => $order_amount,
          'langData' => $langData
        );
        $this->setData($data,1,'');
    }

    /**
     * 设置默认地址
     * @author:tangp
     * @date:2018-09-11
     */
    public function addr_default()
    {
      $data_id = !empty($_REQUEST['data_id']) ? htmlspecialchars(trim($_REQUEST['data_id'])) : '0';
      $latlon  = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
      $storeid = !empty($_REQUEST['store_id']) ? htmlspecialchars(trim($_REQUEST['store_id'])) : $this->store_id;
      $lang    = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
      $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
      $sql = "select * from " . DB_PREFIX . "user_address where user_id=" . $this->userId . ' and default_addr =1';
      $userAddressMod = &m('userAddress');
      $addrinfo = $userAddressMod->querySql($sql);
      if ($addrinfo[0]['default_addr'] == 1) {
          $data = array(
              'default_addr' => 0
          );
          $userAddressMod->doEdits($addrinfo[0]['id'], $data);
      }
      $datas = array(
          'default_addr' => 1
      );
      $res = $userAddressMod->doEdits($data_id, $datas);
      if ($res) {
          $this->setData(array(), 1, '设置成功');
      } else {
          $this->setData(array(), 0,'设置失败');
      }
    }

    /**
     * 购物车删除商品
     * @author:tangp
     * @author:2018-09-25
     */
    public function deleteCart()
    {
        $id = !empty($_REQUEST['id']) ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        $cartMod = &m('cart');
        $res = $cartMod->doDrop($id);
        if ($res){
            $this->setData('',1,'删除成功');
        }else{
            $this->setData('',0,'删除失败');
        }
    }
    /**
    * 微信小程序支付
    * @author:tangp
    * @date:2018-09-19
    */
    public function wxpay()
    {
        $appid = "wxd483c388c3d545f3";
//        $mch_id = "1515804821";
//        $key = 'Sem68GvhBu2ag5ncyJxsbDrXzZAHT3VK';
        $time = time();
        $notify_url = 'https://www.711home.net/xcxNotify.php';
        $openid = $_REQUEST['openid'];
        $out_trade_no = $_REQUEST['order_sn'];
        $money = $_REQUEST['money'];
        $store_id = $_REQUEST['store_id'];

        $params['body']   = '艾美睿零售';
        $params['openid'] = $openid;
        $params['out_trade_no'] = $out_trade_no;
        $params['total_fee'] = $money * 100;
        $params['trade_type'] = 'JSAPI';
        $cartMod = &m('cart');
        $config = $cartMod->querySql('SELECT * FROM '.DB_PREFIX . 'wxapp WHERE store_id = '.$store_id .' LIMIT 1');
        if(empty($config)){
            $this->setData(array(),0,'该店铺尚未添加支付配置');
        }
        //start
        $apiKey = 'DB4EED2130E6D0CAF383E6B9B66D5528'; //服务商apiKey
        $wOpt = array(
            'appid' => 'wxa07a37aef375add1', //服务商appid
            'body' => '艾美睿零售',
            'mch_id' => '1450526802',
            'sub_appid' => 'wxd483c388c3d545f3', //小程序appid
            'sub_mch_id' => $config[0]['mchid'], //特约商户号
            'nonce_str' => $this->createNoncestr(),
            'notify_url' => $notify_url,  // 异步通知地址
            'sub_openid' => $openid,
            'out_trade_no' => $out_trade_no,
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],
            'total_fee' => $money * 100, // 价格:单位分
            'trade_type' => 'JSAPI',
        );
        //end
        $sign = $this->MakeSign($wOpt,$apiKey);
        $wOpt['sign'] = $sign;
        $xml = $this->data_to_xml($wOpt);
        $response = $this->postXmlCurl($xml, "https://api.mch.weixin.qq.com/pay/unifiedorder");
        if( !$response ){
            return false;
        }
        $result = $this->xml_to_data( $response );

        $result['timeStamp'] = $time;
        $result['nonce_str'] = $wOpt['nonce_str'];
        $paySign =  MD5("appId=". $appid."&nonceStr=".$result['nonce_str']."&package=prepay_id=".$result['prepay_id']."&signType=MD5&timeStamp=".$time."&key=".$apiKey);
        $result['paySign']=strtoupper($paySign);
        $result['package'] = 'prepay_id='.$result['prepay_id'];
//        $result['out_trade_no']=$out_trade_no;
        $data = array(
            'result' => $result
        );
        $this->setData($data,1,'');
    }




    public function wxpay1()
    {
        $appid = "wxd483c388c3d545f3";
        $mch_id = "1515804821";
        $key = 'Sem68GvhBu2ag5ncyJxsbDrXzZAHT3VK';
        $time = time();
        $notify_url = 'https://www.711home.net/xcxNotify.php';
        $openid = $_REQUEST['openid'];
        $out_trade_no = $_REQUEST['order_sn'] ? : mt_rand(10000,99999);
        $money = $_REQUEST['money'] ? : 0.01;
        $store_id = $_REQUEST['store_id'] ? : 99;

        $params['body']   = '艾美睿零售';
        $params['openid'] = $openid;
        $params['out_trade_no'] = $out_trade_no;
        $params['total_fee'] = $money * 100;
        $params['trade_type'] = 'JSAPI';
        $cartMod = &m('cart');
        $config = $cartMod->querySql('SELECT * FROM '.DB_PREFIX . 'wxapp WHERE store_id = '.$store_id .' LIMIT 1');
        if(empty($config)){
            $this->setData(array(),0,'该店铺尚未添加支付配置');
        }
        //start
//        if(!empty($config)){
        $apiKey = 'DB4EED2130E6D0CAF383E6B9B66D5528';
        $wOpt = array(
            'appid' => 'wxa07a37aef375add1',
            'body' => '艾美睿零售',
            'mch_id' => '1450526802',
            'sub_appid' => 'wxd483c388c3d545f3',
            'sub_mch_id' => $config[0]['mchid'],
            'nonce_str' => $this->createNoncestr(),
            'notify_url' => $notify_url,  // 异步通知地址
            'sub_openid' => $openid,
            'out_trade_no' => $out_trade_no,
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],
            'total_fee' => $params['total_fee'], // 价格:单位分
            'trade_type' => 'JSAPI',
        );
//        }
//        else{
//            $apiKey = 'Sem68GvhBu2ag5ncyJxsbDrXzZAHT3VK';
//            $wOpt['appid'] = $appid;
//            $wOpt['body'] = $params['body'];
//            $wOpt['mch_id'] = $mch_id;
//            $wOpt['nonce_str'] = $this->createNoncestr();
//            $wOpt['out_trade_no'] = $params['out_trade_no'];
//            $wOpt['openid'] = $params['openid'];
//            $wOpt['total_fee'] = $params['total_fee'];
//            $wOpt['trade_type'] = $params['trade_type'];
//            $wOpt['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];
//            $wOpt['notify_url'] = $notify_url;
//        }

        //end


//        $wOpt['appid'] = $appid;
//        $wOpt['body'] = $params['body'];
//        $wOpt['mch_id'] = $mch_id;
//        $wOpt['nonce_str'] = $this->createNoncestr();
//        $wOpt['out_trade_no'] = $params['out_trade_no'];
//        $wOpt['openid'] = $params['openid'];
//        $wOpt['total_fee'] = $params['total_fee'];
//        $wOpt['trade_type'] = $params['trade_type'];
//        $wOpt['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];
//        $wOpt['notify_url'] = $notify_url;

        $sign = $this->MakeSign($wOpt,$apiKey);
        $wOpt['sign'] = $sign;
        $xml = $this->data_to_xml($wOpt);
        $response = $this->postXmlCurl($xml, "https://api.mch.weixin.qq.com/pay/unifiedorder");
        if( !$response ){
            return false;
        }
        $result = $this->xml_to_data( $response );

        $result['timeStamp'] = $time;
        $result['nonce_str'] = $wOpt['nonce_str'];
        $paySign =  MD5("appId=". $appid."&nonceStr=".$result['nonce_str']."&package=prepay_id=".$result['prepay_id']."&signType=MD5&timeStamp=".$time."&key=".$apiKey);
        $result['paySign']=strtoupper($paySign);
        $result['package'] = 'prepay_id='.$result['prepay_id'];
//        $result['out_trade_no']=$out_trade_no;
        $data = array(
            'result' => $result
        );
        $this->setData($data,1,'');
    }




    /**
     * 微信小程序支付
     * @author:tangp
     * @date:2018-09-19
     */
    public function amountpay()
    {
        $appid = "wxd483c388c3d545f3";
        $mch_id = "1515804821";
        $key = 'Sem68GvhBu2ag5ncyJxsbDrXzZAHT3VK';
        $time = time();
        $notify_url = 'https://www.711home.net/xcxamountNotify.php';
        $openid = $_REQUEST['openid'];
        $out_trade_no = $_REQUEST['order_sn'];
        $money = $_REQUEST['money'];

        $params['body']   = '艾美瑞零售';
        $params['openid'] = $openid;
        $params['out_trade_no'] = $out_trade_no;
        $params['total_fee'] = $money * 100;
        $params['trade_type'] = 'JSAPI';

        $wOpt['appid'] = $appid;
        $wOpt['body'] = $params['body'];
        $wOpt['mch_id'] = $mch_id;
        $wOpt['nonce_str'] = $this->createNoncestr();
        $wOpt['out_trade_no'] = $params['out_trade_no'];
        $wOpt['openid'] = $params['openid'];
        $wOpt['total_fee'] = $params['total_fee'];
        $wOpt['trade_type'] = $params['trade_type'];
        $wOpt['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];
        $wOpt['notify_url'] = $notify_url;

        $sign = $this->MakeSign($wOpt,$key);
        $wOpt['sign'] = $sign;

        $xml = $this->data_to_xml($wOpt);
        $response = $this->postXmlCurl($xml, "https://api.mch.weixin.qq.com/pay/unifiedorder");
        if( !$response ){
            return false;
        }
        $result = $this->xml_to_data( $response );

        $result['timeStamp'] = time();
        $result['nonce_str'] = $wOpt['nonce_str'];
        $paySign =  MD5("appId=". $appid."&nonceStr=".$result['nonce_str']."&package=prepay_id=".$result['prepay_id']."&signType=MD5&timeStamp=".$time."&key=".$key);
        $result['paySign']=strtoupper($paySign);
        $result['package'] = 'prepay_id='.$result['prepay_id'];
//        $result['out_trade_no']=$out_trade_no;
        $data = array(
            'result' => $result
        );
        $this->setData($data,1,'');
    }
    public function createNoncestr($length = 32 ){

        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";

        $str ="";

        for ( $i = 0; $i < $length; $i++ ) {

            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);

        }

        return $str;

    }
    public function notify()
    {
 //       Zlog::$logfile = 'zlog/luffy.txt';
 //       Zlog::write($_REQUEST);
 
//        Zlog::$logfile = 'zlog/jh.txt';
//        Zlog::write('notify-' . date('Y-m-d H:i:s'));
//        Zlog::write($_REQUEST);
//        die;
//        $xml = file_get_contents("php://input");
//        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
////        file_get_contents('zlog/wx.txt',$xml);
//        libxml_disable_entity_loader(true);
//        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
//        if (($data['return_code'] == 'SUCCESS') && ($data['result_code'] == 'SUCCESS')){
//            $data = array(
//                'pay_sn' => $data['transaction_id'],
//                'payment_code' => 'wxxcxpay',
//                'payment_time' => strtotime($data['time_end']),
//                'order_state'  => 20
//            );
//            $cond = array(
//                'order_sn' => $data['out_trade_no']
//            );
//            $detail = array(
//                'order_state' => 20
//            );
//            $res = $this->orderMod->doEditSpec($cond,$data);
//            if ($res){
//                $detailRes = $this->orderDetailMod->doEditSpec(array('order_id'=>$data['out_trade_no']),$detail);
//                $this->updateStock($data['out_trade_no']);
//            }
            $data = $_REQUEST;
//        Zlog::write($_REQUEST);die;
            if (($data['return_code'] == 'SUCCESS') && ($data['result_code'] == 'SUCCESS')) {
                $orderMod = &m('order');
                $childOrderData = $orderMod->getData(array("cond" => "`order_sn` like '{$data['out_trade_no']}%' and `mark` = 1"));
                $sql = "SELECT goods_num,goods_id,store_id,prom_id FROM bs_order_goods WHERE order_id = " .$data['out_trade_no'];
                $datas = $orderMod->querySql($sql);
                if ($datas[0]['prom_id'] !== 0){
                    $sqll = "SELECT * FROM bs_spike_goods WHERE spike_id = {$datas[0]['prom_id']} AND store_goods_id = {$datas[0]['goods_id']}";
                    $infos = $orderMod->querySql($sqll);
                    $conds = array(
                        "goods_num" => $infos[0]['goods_num'] - $datas[0]['goods_num']
                    );
                    $spikeActivitiesGoods = &m('spikeActiviesGoods');
//                    $spikeActivitiesGoods->doEdit($infos[0]['id'],$conds);
                }
                foreach($childOrderData as $key =>$val) {
                    $orderSn = $val['order_sn'];
                    $store_id = $val['store_id'];
                    $data = array(
                        'pay_sn' => $data['transaction_id'],
                        'payment_code' => 'wxpay',
                        'payment_time' => strtotime($data['time_end']),
                        'order_state' => 20, //已付款状态
                        'Appoint' => 1, //1未被指定 2被指定
                        'Appoint_store_id' => $store_id, //被指定的站点
                        'install_time' =>strtotime($data['time_end']),
                        'number_order' => $this->createNumberOrder($store_id)
                    ); //区域配送安装完成时间
                    $cond = array(
                        'order_sn' => $orderSn
                    );
                    $fxOrderMod = &m('fxOrder');
                    	$fxOrderMod->addFxOrderByOrderSn($orderSn,2);
                    	$res = $orderMod->doEditSpec($cond, $data);
                    	$orderMod->update_pay_time($store_id, $orderSn, $data['transaction_id'], 2, 20, 0,$data['number_order']);
                    	$this->updateStock($orderSn);
                  
                }
        	       if($res){
                    exit;
                }
            }
    }


    /**
     * 生成小票编号
     * @author: luffy
     * @date: 2018-08-09
     */
    public function createNumberOrder($storeid) {
        //获取当天开始结束时间
        $orderMod=&m('order');
        $startDay = strtotime(date('Y-m-d'));
        $endDay = strtotime(date('Y-m-d 23:59:59'));
        $sql = 'select order_sn,number_order from  '
            . DB_PREFIX . 'order where add_time BETWEEN ' . $startDay . ' AND ' . $endDay
            . ' AND mark = 1 AND number_order is NOT NULL and store_id = ' . $storeid . ' order by add_time DESC limit 1';
        $res =$orderMod->querySql($sql);
        file_put_contents('upload/a.php',json_encode($res));
        //不管订单存在与否直接加
        $number_order = (int) $res[0]['number_order'] + 1;
        $number_order = str_pad($number_order, 4, 0, STR_PAD_LEFT);
        return $number_order;
    }


    public function amountnotify()
    {
        $data = $_REQUEST;
//        Zlog::write($_REQUEST);die;
        if (($data['return_code'] == 'SUCCESS') && ($data['result_code'] == 'SUCCESS')) {
            $rs  = $this->amountLogMod->isExist($data['out_trade_no']);
            $recharge = $this->rechargeAmountMod->getOne(array('cond'=>"`id` = '{$rs['point_rule_id']}'",'fields'=>'id,c_money,s_money'));
            $amount=$rs['c_money'] + $recharge['s_money'];
            $result=$this->updateAmount($amount,$rs['point_rule_id'],$rs['add_user'],$rs['point'],$data['out_trade_no']);
            //生成积分日志
            $sql='SELECT point,username,phone FROM '.DB_PREFIX.'user WHERE is_kefu = 0 AND id = '.$rs['add_user'];
            $info = $this->userMod->querySql($sql);
            $note='充值赠送'.$rs['point'].'睿积分';
            $expend='-';
            $this->addPointLog1($info[0]['phone'],$note,$rs['add_user'],$rs['point'],$expend,$data['out_trade_no']);
           //修改记录状态
            $data1 =array(
                'transaction_id'    => $data['transaction_id'],
                'pay_time'          => strtotime($data['time_end']),
                'status'            => 2
            );
            $this->amountLogMod->doEdit($rs['id'], $data1);

        }
    }

    //更新用户的余额和睿积分抵扣规则
    public function  updateAmount($amount,$rechargeId,$userId,$point,$orderSn){
        $rs  = $this->amountLogMod->isExist($orderSn);
        if($rs['status']==1){
            $userData=$this->userMod->getOne(array('cond'=>"`id` = '{$userId} and mark=1'",'fields'=>'amount,point,recharge_id'));
            $ruleSql="SELECT id,c_money,s_money,integral,percent FROM ".DB_PREFIX.'recharge_point WHERE mark=1 and id='.$rechargeId;
            $newruleData=$this->rechargeAmountMod->querySql($ruleSql);
            $Sql="SELECT id,c_money,s_money,integral,percent FROM ".DB_PREFIX.'recharge_point WHERE mark=1 and id='.$userData['recharge_id'];
            $oldruleData=$this->rechargeAmountMod->querySql($Sql);
            if($oldruleData[0]['percent'] > $newruleData[0]['percent']){
                $rechargeId=$oldruleData[0]['id'];
            }
            $data=array(
                'recharge_id'=>$rechargeId,
                'amount'=>$userData['amount']+$amount,
                'point'=>$userData['point']+$point
            );
            $res=$this->userMod->doEdit($userId,$data);
            return $res;
        }
    }

    //生成睿积分充值日志
    public  function addPointLog1($username,$note,$userid,$deposit,$expend = "-",$orderSn){
        $rs  = $this->amountLogMod->isExist($orderSn);
        if($rs['status']==1){
        if(empty($this->accountName)){
            $accountName='--';
        }
        $logData = array(
            'operator' => $accountName,
            'username' => $username,
            'add_time' => time(),
            'deposit'=>$deposit,
            'expend'=>$expend,
            'note'=>$note,
            'userid'=>$userid
        );
        $this->pointLogMod->doInsert($logData);
        }
    }
    // 更新规格库存 和 无规格库存
    public function UpdateStock($out_trade_no){
        //  更新库存

        $sql = "SELECT sg.goods_id,sg.spec_key,sg.goods_num,sg.deduction,sg.good_id FROM ".
            DB_PREFIX."order as r LEFT JOIN ".
            DB_PREFIX."order_goods as sg ON  r.order_sn = sg.order_id WHERE sg.order_id = '{$out_trade_no}'";
        $orderRes = $this->areaGoodMod->querySql($sql);
        foreach ($orderRes as $k =>$v) {
            if (!empty($v['spec_key'])) {
                if($v['deduction']==1){
                    $query_id = "select `id` from " . DB_PREFIX . "store_goods_spec_price where `goods_id` = '{$v['good_id']}' and `key` = '{$v['spec_key']}' ";
                    $res_query = $this->storeGoodItemPriceMod->querySql($query_id);
                    $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods_spec_price WHERE `goods_id` = '{$v['good_id']}' and `key` = '{$v['spec_key']}'";
                    $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                    foreach($res_query as $key=>$val){
                        $condition = array(
                            'goods_storage' => $specInfo[0]['goods_storage'] - $v['goods_num']
                        );
                        $res = $this->storeGoodItemPriceMod->doEdit($val['id'], $condition);
                    }
                    if ($res) {
                        $infoSql = "select goods_storage,id from  " . DB_PREFIX . "store_goods WHERE `goods_id` = '{$v['good_id']}'";
                        $Info = $this->areaGoodMod->querySql($infoSql);
                        $cond = array(
                            'goods_storage' => $Info[0]['goods_storage'] - $v['goods_num']
                        );
                        foreach($Info as $key1=>$val1 ){
                            $this->areaGoodMod->doEdit($val1['id'], $cond);
                        }
                    }
                    $Sql = "select goods_storage from  " . DB_PREFIX . "goods_spec_price WHERE `goods_id` = '{$v['good_id']}' and `key` = '{$v['spec_key']}'";

                    $goodsSpec = $this->areaGoodMod->querySql($Sql);
                    $conditional=array(
                        'goods_storage'=>$goodsSpec[0]['goods_storage']-$v['goods_num']
                    );
                    $goodsSpecSql="update ".DB_PREFIX."goods_spec_price set goods_storage = ".$conditional['goods_storage']." where goods_id=".$v['good_id']." and `key` ='{$v['spec_key']}'" ;
                    $result=$this->goodsSpecPriceMod->doEditSql($goodsSpecSql);
                    if($result){
                        $goodSql="select goods_storage from  " . DB_PREFIX . "goods WHERE `goods_id` = '{$v['good_id']}'";

                        $goodInfo = $this->areaGoodMod->querySql($goodSql);
                        $goodCond = array(
                            'goods_storage' => $goodInfo[0]['goods_storage'] - $v['goods_num']
                        );
                        $this->goodsMod->doEdit($v['good_id'],$goodCond);
                    }
                }else{
                    $query_id = "select `id` from " . DB_PREFIX . "store_goods_spec_price where `store_goods_id` = '{$v['goods_id']}' and `key` = '{$v['spec_key']}' ";
                    $res_query = $this->storeGoodItemPriceMod->querySql($query_id);
                    $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods_spec_price WHERE `store_goods_id` = '{$v['goods_id']}' and `key` = '{$v['spec_key']}'";
                    $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                    $condition = array(
                        'goods_storage' => $specInfo[0]['goods_storage'] - $v['goods_num']
                    );
                    $res = $this->storeGoodItemPriceMod->doEdit($res_query[0]['id'], $condition);
                    if ($res) {
                        $infoSql = "select goods_storage from  " . DB_PREFIX . "store_goods WHERE `id` = '{$v['goods_id']}'";
                        $Info = $this->areaGoodMod->querySql($infoSql);
                        $cond = array(
                            'goods_storage' => $Info[0]['goods_storage'] - $v['goods_num']
                        );
                        $this->areaGoodMod->doEdit($v['goods_id'], $cond);
                    }
                }



            } else {
                if($v['deduction']==1){
                    $infoSql = "select goods_storage,id from  " . DB_PREFIX . "store_goods WHERE `goods_id` = '{$v['good_id']}'";
                    $Info = $this->areaGoodMod->querySql($infoSql);

                    $cond = array(
                        'goods_storage' => $Info[0]['goods_storage'] - $v['goods_num']
                    );
                    foreach($Info as $key1=>$val1 ){
                        $this->areaGoodMod->doEdit($val1['id'], $cond);
                    }
                    $goodSql="select goods_storage from  " . DB_PREFIX . "goods WHERE `goods_id` = '{$v['good_id']}'";

                    $goodInfo = $this->areaGoodMod->querySql($goodSql);
                    $goodCond = array(
                        'goods_storage' => $goodInfo[0]['goods_storage'] - $v['goods_num']
                    );
                    $this->goodsMod->doEdit($v['good_id'],$goodCond);
                }else{
                    $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods WHERE `id` = '{$v['goods_id']}'";
                    $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                    $condition = array(
                        'goods_storage' => $specInfo[0]['goods_storage'] - $v['goods_num']
                    );
                    $this->areaGoodMod->doEdit($v['goods_id'],$condition);
                }

            }
        }
    }
    public function MakeSign( $params ,$key){
        //签名步骤一：按字典序排序数组参数
//        $key = 'Sem68GvhBu2ag5ncyJxsbDrXzZAHT3VK';
        ksort($params);
        $string = $this->ToUrlParams($params);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".$key;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    public function ToUrlParams($params)
    {
        $string = '';
        if (!empty($params)){
            $array = array();
            foreach ($params as $key => $value){
                $array[] = $key.'='.$value;
            }
            $string = implode("&",$array);
        }
        return $string;
    }
    function xml_to_data($xml){
        if(!$xml){
            return false;
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $data;
    }
    function postXmlCurl($xml, $url, $useCert = false, $second = 30){
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if($useCert == true){
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            //curl_setopt($ch,CURLOPT_SSLCERT, WxPayConfig::SSLCERT_PATH);
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            //curl_setopt($ch,CURLOPT_SSLKEY, WxPayConfig::SSLKEY_PATH);
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            return false;
        }
    }
    public function data_to_xml($params)
    {
        if(!is_array($params)|| count($params) <= 0)
        {
            return false;
        }
        $xml = "<xml>";
        foreach ($params as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }
    /**
     * 改变购物车数量
     * @author tangp
     * @date 2018-09-29
     */
    public function doChangeNum()
    {
        $cartMod = &m('cart');
        $cart_id = !empty($_REQUEST['cart_id']) ? intval($_REQUEST['cart_id']) : "";
        $goods_num = !empty($_REQUEST['goods_num']) ? intval($_REQUEST['goods_num']) : "0";
        $store_id = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : $this->store_id;
        $a = array(
            "goods_num" => $goods_num
        );
        $rs = $cartMod->doEdit($cart_id,$a);
        $query = array(
            'cond' => "`id` = '{$cart_id}'",
            'fields' => 'selected,store_id'
        );
        $selected = $cartMod->getOne($query);
//        var_dump($selected);die;
        if ($rs && ($selected['selected'] == 0)) {
            $sql = "select sum((goods_num * goods_price)) as total from " . DB_PREFIX . "cart where user_id = $this->userId and store_id = {$store_id} and `selected` =1";
            $res = $cartMod->querySql($sql);
            if ($res[0]['total']) {
                $data['total'] = $res[0]['total'];
            } else {
                $data['total'] = '0.00';
            }
            $data1 = array(
                'total' => $data['total']
            );
            $this->setData($data1,1,'');
        }
        if ($rs && ($selected['selected'] == 1)) {
            $sql = "select sum((goods_num * goods_price)) as total from " . DB_PREFIX . "cart where user_id = $this->userId and store_id = {$store_id} and `selected` =1";
            $res = $cartMod->querySql($sql);
            $data=array(
                'total' => $res[0]['total']
            );
            $this->setData($data,1,'');
        }
    }
    /**
     * 获取配送店铺
     *
     */
    public function getStore() {
        $latitude   = isset($_REQUEST['latitude']) ? trim($_REQUEST['latitude']) : '32.0572355';
        $longitude  = isset($_REQUEST['longitude']) ? trim($_REQUEST['longitude']) : '118.77807441';
        $txlalton=$latitude.",".$longitude;
        $latlon = explode(',', $txlalton);
        $lng = $latlon[1]; //经度
        $lat = $latlon[0]; //纬度
        $latlon=$this->coordinate_switchf($lat,$lng);
        $lng=$latlon['Longitude'];
//        print_r($lng);die;
        $lat=$latlon['Latitude'];
        $goods_id = $_REQUEST['store_good_id'];
        $userid = $this->userId;
//        if ($_SESSION['userId']) {
//            $user_id = $_SESSION['userId'];
        $userMod = &m('user');
        $sql = 'SELECT *  FROM  ' . DB_PREFIX . 'user WHERE  id  =' . $userid; //odm_members
        $datas = $userMod->querySql($sql);
        if ($datas[0]['odm_members'] == 0) {
            $where = ' and c.store_type in (2,3)  ';
        } else {
            $where = '';
        }
//        } else {
//            $where = ' and c.store_type in（2,3） ';
//        }
        $mod = &m('store');
        $sql = 'SELECT  c.id,l.store_name,c.distance,c.longitude,c.latitude  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE c.is_open = 1  and  l.lang_id =' . $this->lang_id . ' and l.distinguish=0  and c.store_cate_id=' . $this->countryId . $where;
        $data = $mod->querySql($sql);
//        echo '<pre>';print_r($data);die;
        $sql1 = 'SELECT store_id,id FROM ' . DB_PREFIX . 'store_goods  WHERE goods_id=' . $goods_id . ' and mark =1  and is_on_sale =1  ';
        $gData = $mod->querySql($sql1);
//        echo '<pre>';print_r($gData);die;

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

    /** 根据业务类型查询三级分类
     * wangshuo
     * 2018-11-12
     */
    public function roomIndex() {
        $rtid = !empty($_REQUEST['rtid']) ? $_REQUEST['rtid'] :'';
        $langid = !empty($_REQUEST['langid']) ? $_REQUEST['langid'] : $this->lang_id;
        //1级业务类型
        $roomtypearr = $this->getRoomType($langid);
        foreach($roomtypearr as $k=>$v){
        //2级业务
        $roomTypeMod = &m('roomType');
         if (!empty($langid)) {
            $where = '    where  t.superior_id= '. $v['id'] .' and  l.`lang_id`  = ' . $langid;
        } else {
            $where = '    where  t.superior_id= '. $v['id'] .' and  l.`lang_id`  = ' . $this->mrlangid;
        }
        $sql = 'SELECT  t.`id`,l.`type_name`,t.`room_adv_img`,t.superior_id  FROM  ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN  ' . DB_PREFIX . 'room_type_lang  AS  l  ON  t.`id` = l.`type_id`  ' . $where . ' order by t.sort';
        $data = $roomTypeMod->querySql($sql);
         $roomtypearr[$k]['levelBusiness'] = $data;
           foreach($data as $key=>$vo){
           //3级分类业务
            $roomTypeMod = &m('roomType');
            $where = '  where  rc.room_type_id = '.$vo['id'].'   AND l.`lang_id` = ' . $langid . '  order by rc.`sort` ' ;
              //所以三级分类
             $hsql = 'SELECT rc.id,g.parent_id,g.parent_id_path,l.type_name,rc.category_id,rc.sort,rc.room_type_id,g.image FROM  '
            . DB_PREFIX . 'room_category AS rc LEFT JOIN '
            . DB_PREFIX . "room_type_lang as l on rc.room_type_id = l.type_id  left join "
            . DB_PREFIX . 'goods_category AS g on rc.category_id= g.id '.$where;
             $res=$roomTypeMod->querySql($hsql);
            $roomtypearr[$k]['levelBusiness'][$key]['classLevel']= $res;

           }
        }
         $data=array('rtid'=>$rtid,'roomtypearr'=>$roomtypearr);
        //三级分类
         if( $roomtypearr ) {
            $this->setData($data, 1);
          }
        }
     /**
     * 2级业务类型
     */
    public function getRoomType($langid) {
        $roomTypeMod = &m('roomType');
        if (!empty($langid)) {
            $where = '    where  t.superior_id= 0 and  l.`lang_id`  = ' . $langid;
        } else {
            $where = '    where  t.superior_id= 0 and  l.`lang_id`  = ' . $this->mrlangid;
        }
        $sql = 'SELECT  t.`id`,l.`type_name`,t.`room_adv_img`,t.room_img  FROM  ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN  ' . DB_PREFIX . 'room_type_lang  AS  l  ON  t.`id` = l.`type_id`  ' . $where . ' order by t.sort';
        $data = $roomTypeMod->querySql($sql);
        return $data;
    }
     //三级分类
    public function getHotGoods($roomIds,$langid){
        $roomTypeMod = &m('roomType');
        $where = '  where  rc.room_type_id in ('.$roomIds.')   AND l.`lang_id` = ' . $langid . '  order by rc.`sort` ' ;
        //所以子类的商品
        $hsql = 'SELECT rc.id,g.parent_id,g.parent_id_path,l.type_name,rc.category_id,rc.sort FROM  '
            . DB_PREFIX . 'room_category AS rc LEFT JOIN '
            . DB_PREFIX . "room_type_lang as l on rc.room_type_id = l.type_id  left join "
            . DB_PREFIX . 'goods_category AS g on rc.category_id= g.id '.$where;
        $res=$roomTypeMod->querySql($hsql);
          foreach ($res as &$val) {
            $val['ctgpath'] = $this->getCtgPath($val['parent_id_path'],$langid);
        }
          return $res;
    }
      /**
     * 获取分类导航
     * @path   0_2_21_844
     * @author ws
     * @date 2018/11/12
     */
    public function getCtgPath($path,$langid) {
        if (!empty($path)) {
            $arr = explode('_', $path);
            array_shift($arr);
        }
        $str = implode(',', $arr);
        $sql = 'select  c.id,l.category_name   from   ' . DB_PREFIX . 'goods_category  as c
                left join  ' . DB_PREFIX . 'goods_category_lang as l  on c.id =l.category_id
                where  l.lang_id =' . $langid . '  and  c.id in(' . $str . ')   ORDER BY  c.level  asc ';
        $data = $this->ctgMod->querySql($sql);
        $ctgpath = array();
        foreach ($data as $val) {
            $ctgpath[] = $val['category_name'];
        }
        $pathStr = implode(' > ', $ctgpath);
        return $pathStr;
    }










}
