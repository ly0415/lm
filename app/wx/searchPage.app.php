<?php

/**
 * 搜索页面
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class SearchPageApp extends BaseWxApp {

    private $filterMenu = array();  //筛选菜单

    /**
     * 构造函数
     */

    public function __construct() {
        parent::__construct();
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    public function index() {
        $page = !empty($_REQUEST['p']) ? $_REQUEST['p'] : 1;  //当前第几页
        $filter_param = array(); //筛选数组
        $gname = !empty($_REQUEST['gname']) ? $_REQUEST['gname'] : '';
        $brand = !empty($_REQUEST['b']) ? $_REQUEST['b'] : '';
        $price = !empty($_REQUEST['pr']) ? $_REQUEST['pr'] : '';  // 价格
        $sort = !empty($_REQUEST['by']) ? $_REQUEST['by'] : 1; // 排序
        $start_price = !empty($_REQUEST['start_price']) ? trim($_REQUEST['start_price']) : 0;
        $end_price = !empty($_REQUEST['end_price']) ? trim($_REQUEST['end_price']) : '';

        $this->assign('sprice', $start_price);
        $this->assign('eprice', $end_price);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $this->assign('latlon', $latlon);

        $start_price = abs(intval($_REQUEST['start_price']));
        $end_price = abs(intval($_REQUEST['end_price']));

        if ($start_price != 0 || $end_price != 0) {
            if ($end_price < $start_price) {
                $price = $end_price . '_' . $start_price;
            } else {
                $price = $start_price . '_' . $end_price;
            }
        }
        /* 当前页面的 url */
        $query_string = $_SERVER["QUERY_STRING"];
        $query_arr = explode('&', $query_string);
        foreach ($query_arr as $key => $val) {
            if (strstr($val, 'start_price')) {
                unset($query_arr[$key]);
            }
            if (strstr($val, 'end_price')) {
                unset($query_arr[$key]);
            }
        }
        $curUrl = '?' . implode('&', $query_arr);

        $baseUrl = '?app=searchPage&act=index&storeid=' . $this->storeid . '&lang=' . $this->langid . '&latlon' . $latlon . '&';
        $clearAll = '?app=searchPage&act=index&storeid=' . $this->storeid . '&lang=' . $this->langid . '&gname=' . $gname . '&latlon' . $latlon;

        //加入筛选条件
        if (!empty($gname)) {
            $filter_param['gname'] = $gname;
        }
        if (!empty($brand)) {
            $filter_param['b'] = $brand;
        }
        if (!empty($price)) {
            $filter_param['pr'] = $price;
        }
        if (!empty($sort)) {
            $filter_param['by'] = $sort;
        }


        //价格区间
        $piceQujian = $this->getPriceQujian($this->langid, $filter_param, $baseUrl, $start_price, $end_price);
        //品牌
        $brand = $this->getGoodsBrand($this->langid, $filter_param, $baseUrl);
        //商品列表
        $goodslist = $this->getGoodsList($this->langid, $this->storeid, $filter_param, $page);
        //商品排序
        $goodsSort = $this->getGoodsSort($this->langid, $filter_param, $baseUrl);

        $this->load($this->shorthand, 'WeChat/goods');
        $this->assign('langdata', $this->langData);

        $this->assign('curUrl', $curUrl);

        $this->assign('goodslist', $goodslist);

        $this->assign('piceQujian', $piceQujian);

        $this->assign('goodsSort', $goodsSort);
        $this->assign('brand', $brand);

        $this->assign('by', $sort);

        $this->display('searchPage/searchPage.html');
    }

    /**
     * 价格区间
     * @author wangh
     * @date 2017/09/13
     */
    public function getPriceQujian($langid, $filter_param, $baseUrl, $start_price, $end_price) {
        $data = array();
        if ($this->syshort == 'RMB') {
            $data[1] = array('priceQj' => '价格 ' . $this->symbol . '500 以下', 'val' => '0_500');
            $data[] = array('priceQj' => '价格 ' . $this->symbol . '500  到 ' . $this->symbol . '1,000', 'val' => '500_1000');
            $data[] = array('priceQj' => '价格 ' . $this->symbol . '1,000 到 ' . $this->symbol . '2,000', 'val' => '1000_2000');
            $data[] = array('priceQj' => '价格 ' . $this->symbol . '3,000 到 ' . $this->symbol . '4,000', 'val' => '3000_4000');
            $data[] = array('priceQj' => '价格 ' . $this->symbol . '4,000 到 ' . $this->symbol . '5,000', 'val' => '4000_5000');
            $data[] = array('priceQj' => '价格 ' . $this->symbol . '5000 以上', 'val' => '5000_*');
        } else {
            $data[1] = array('priceQj' => 'Under price ' . $this->symbol . '100', 'val' => '0_100');
            $data[] = array('priceQj' => 'price ' . $this->symbol . '100 to ' . $this->symbol . '250', 'val' => '100_250');
            $data[] = array('priceQj' => 'price ' . $this->symbol . '250 to ' . $this->symbol . '500', 'val' => '250_500');
            $data[] = array('priceQj' => 'price ' . $this->symbol . '500 to ' . $this->symbol . '750', 'val' => '500_750');
            $data[] = array('priceQj' => 'price ' . $this->symbol . '750 to ' . $this->symbol . '1000', 'val' => '750_1000');
            $data[] = array('priceQj' => 'price ' . $this->symbol . '1000 & Above ', 'val' => '1000_*');
        }
        //
        $priceFilter = !empty($filter_param['pr']) ? $filter_param['pr'] : array();

        unset($filter_param['pr']);
        $uri = urldecode(http_build_query($filter_param));

        $filterArr = explode('-', $priceFilter);

        if ($start_price && $end_price) {

            foreach ($data as $key => $val) {
                $data[$key]['href'] = $baseUrl . $uri . '&pr=' . $val['val'];
            }

            //加入筛选菜单
            $menu = array(' <a href=' . $baseUrl . $uri . ' class="tag">
                                  ' . '价格：' . $start_price . '_' . $end_price . '<span class="del or">X</span>
                                </a>');
            $this->filterMenu = array_merge($this->filterMenu, $menu);
        } else {

            foreach ($data as $key => $val) {  //当没有 价格 筛选的 时候
                if (empty($priceFilter)) {
                    $data[$key]['href'] = $baseUrl . $uri . '&pr=' . $val['val'];
                } else { //当有 价格 筛选的 时候
                    if (in_array($val['val'], $filterArr)) {
                        $data[$key]['ison'] = 1;
                        $a = array();
                        $a[] = $val['val'];
                        $dif = array_diff($filterArr, $a);
                        $itemd = implode('-', $dif);
                        if (!empty($itemd)) {
                            $data[$key]['href'] = $baseUrl . $uri . '&pr=' . $itemd;
                        } else {
                            $data[$key]['href'] = $baseUrl . $uri;
                        }

                        //加入筛选菜单
                        $menu = array(' <a href=' . $data[$key]['href'] . ' class="tag">
                                  ' . '价格：' . $val['val'] . '<span class="del or">X</span>
                                </a>');
                        $this->filterMenu = array_merge($this->filterMenu, $menu);
                    } else {
                        $data[$key]['ison'] = 0;
                        $b = array();
                        $b[] = $val['val'];
                        $meg = array_merge($filterArr, $b); //取合集
                        $itemm = implode('-', $meg);
                        $data[$key]['href'] = $baseUrl . $uri . '&pr=' . $itemm;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * 商品品牌
     * @author wangh
     * @date 2017/09/13
     */
    public function getGoodsBrand($langid, $filter_param, $baseUrl) {
        $goodsbandMod = &m('goodsBrand');
        $brandFilter = !empty($filter_param['b']) ? explode('_', $filter_param['b']) : array();

        unset($filter_param['b']);
        $uri = urldecode(http_build_query($filter_param));

        if (!empty($langid)) {
            $where = '    where  l.`lang_id`  = ' . $langid;
        } else {
            $where = '    where  l.`lang_id`  = ' . $this->mrlangid;
        }

        $sql = 'SELECT  b.id,l.`brand_name`as bname  FROM  ' . DB_PREFIX . 'goods_brand AS b
                LEFT JOIN  ' . DB_PREFIX . 'goods_brand_lang AS l ON b.id=l.`brand_id`  ' . $where;
        $data = $goodsbandMod->querySql($sql);

        foreach ($data as $key => $val) {
            if (empty($brandFilter)) {  //当没有品牌 筛选的 时候
                $data[$key]['href'] = $baseUrl . $uri . '&b=' . $val['id'];
            } else {  //当有品牌 筛选的 时候
                if (in_array($val['id'], $brandFilter)) {  // 减去
                    $data[$key]['ison'] = 1;
                    $a = array();
                    $a[] = $val['id'];
                    $dif = array_diff($brandFilter, $a); //取差集
                    $itemd = implode('_', $dif);
                    if (!empty($itemd)) {
                        $data[$key]['href'] = $baseUrl . $uri . '&b=' . $itemd;
                    } else {
                        $data[$key]['href'] = $baseUrl . $uri;
                    }
                    //加入筛选菜单
                    $menu = array(' <a href=' . $data[$key]['href'] . ' class="tag">
                                  ' . $val['bname'] . '<span class="del or">X</span>
                                </a>');
                    $this->filterMenu = array_merge($this->filterMenu, $menu);
                } else {  //加上
                    $data[$key]['ison'] = 0;
                    $b = array();
                    $b[] = $val['id'];
                    $meg = array_merge($brandFilter, $b); //取合集
                    $itemm = implode('_', $meg);
                    $data[$key]['href'] = $baseUrl . $uri . '&b=' . $itemm;
                }
            }
        }

        return $data;
    }

    /** 获取商品类别
     * @param $langid
     * @param $filer_param
     */
    public function getGoodsList($langid, $storeid, $filter_param, $page) {
        $by = !empty($filter_param['by']) ? $filter_param['by'] : 1;
        $gname = !empty($filter_param['gname']) ? htmlspecialchars($filter_param['gname']) : '';
        $brandFilter = !empty($filter_param['b']) ? explode('_', $filter_param['b']) : array();
        $styleFilter = !empty($filter_param['s']) ? explode('_', $filter_param['s']) : array();
        $typeFilter = !empty($filter_param['t']) ? explode('_', $filter_param['t']) : array();
        $priceFilter = !empty($filter_param['pr']) ? $filter_param['pr'] : array();

        $storeGoodMod = &m('areaGood');

        if ($by == 1) {
            $orderBy = '  order by  s.shop_price  asc ';
        } else {
            $orderBy = '  order by  s.shop_price  desc ';
        }

        //模糊查询
        if (!empty($gname)) {
            $sql = 'SELECT  s.id   FROM   ' . DB_PREFIX . 'store_goods  as s  LEFT JOIN  ' . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id`
                    WHERE  s.store_id =' . $storeid . '   and  l.lang_id = ' . $this->langid . '  and  s.mark=1  '
                    . 'and   l.goods_name  like "%' . $gname . '%" or l.keywords like "%' . $gname . '%"';
            $dataG = $storeGoodMod->querySql($sql);
            $goodsId = $this->getYiweiArr($dataG);
        }

        //品牌
        if (!empty($brandFilter)) {
            $brandids = implode(',', $brandFilter);
            $sql = 'SELECT  id   FROM   ' . DB_PREFIX . 'store_goods  WHERE  store_id =' . $storeid . '   and  mark=1  and   brand_id in(' . $brandids . ')';
            $dataB = $storeGoodMod->querySql($sql);
            $arrB = $this->getYiweiArr($dataB);
            $goodsId = array_intersect($goodsId, $arrB);
        }
        //风格
        if (!empty($styleFilter)) {
            $styleids = implode(',', $styleFilter);
            $sql = 'SELECT  id   FROM   ' . DB_PREFIX . 'store_goods  WHERE  store_id =' . $storeid . '   and   mark=1  and   style_id in(' . $styleids . ')';
            $dataS = $storeGoodMod->querySql($sql);
            $arrS = $this->getYiweiArr($dataS);
            $goodsId = array_intersect($goodsId, $arrS);
        }
        // 类型
        if (!empty($typeFilter)) {
            $typeids = implode(',', $typeFilter);
            $sql = 'SELECT  id   FROM   ' . DB_PREFIX . 'store_goods  WHERE  store_id =' . $storeid . '   and  mark=1  and   room_id in(' . $typeids . ')';
            $dataT = $storeGoodMod->querySql($sql);
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
                $dataP = $storeGoodMod->querySql($sql);
                $res = array_merge($res, $dataP);
            }
            $dataPr = $this->getYiweiArr($res);
            $goodsId = array_intersect($goodsId, $dataPr);
        }

        $gids = implode(',', $goodsId);
        $where = '  where   s.store_id =' . $storeid . '  and   s.id  in(' . $gids . ')   and   s.mark=1   and   s.is_on_sale =1  AND l.`lang_id` = ' . $this->langid;

        $sql = 'SELECT s.id,s.`goods_id`,s.`cat_id`,s.`store_id`,l.`goods_name`,l.`lang_id`,s.`shop_price`,s.`market_price`,s.`brand_id`,gl.`original_img`,s.is_free_shipping
                FROM  ' . DB_PREFIX . 'store_goods AS s LEFT JOIN  ' . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id` LEFT JOIN  '
                . DB_PREFIX . 'goods AS gl ON s.`goods_id` = gl.`goods_id`' . $where . $orderBy;
        $arr = $storeGoodMod->querySql($sql);

        $shorthand = $this->shorthand;
        foreach ($arr as &$item) {
            $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $this->storeid;
            $store_arr = $storeGoodMod->querySql($store_sql);
            $item['shop_price'] = number_format($item['shop_price'] * $store_arr[0]['store_discount'], 2);
            //是否包邮
            if ($shorthand == 'ZH') {
                switch ($item['is_free_shipping']) {
                    case 1:
                        $item['isfree'] = '包邮';  // Package mail
                        break;
                    case 2:
                        $item['isfree'] = '不包邮'; //Don't pack mail
                        break;
                    default:
                        $item['isfree'] = '不包邮';
                }
            } else {
                switch ($item['is_free_shipping']) {
                    case 1:
                        $item['isfree'] = 'Package mail';  // Package mail
                        break;
                    case 2:
                        $item['isfree'] = "No mail"; //Don't pack mail
                        break;
                    default:
                        $item['isfree'] = "No mail";
                }
            }
        }
        //组装数据
        $res = array();
        $res['data'] = $arr;
        $res['count'] = $total;

        return $res;
    }

    /**
     * 商品排序
     * @author wangh
     * @date 2017/09/13
     */
    public function getGoodsSort($langid, $filter_param, $baseUrl) {
        $sortFilter = !empty($filter_param['by']) ? $filter_param['by'] : 1;

        unset($filter_param['by']);
        $shorthand = $this->shorthand;
        if ($shorthand == 'ZH') {
            $sort = array(
                1 => array('by' => 1, 'val' => '价格从低到高'),
                2 => array('by' => 2, 'val' => '价格从高到低')
            );
        } else {
            $sort = array(
                1 => array('by' => 1, 'val' => 'Price Per Item: Low-High'),
                2 => array('by' => 2, 'val' => 'Price Per Item: High-Low')
            );
        }

        $uri = urldecode(http_build_query($filter_param));

        foreach ($sort as $key => $val) {
            $sort[$key]['href'] = $baseUrl . $uri . '&by=' . $val['by'];
            if ($val['by'] == $sortFilter) {
                $sort['selected'] = $val['val'];
            }
        }

        return $sort;
    }

    /**
     * @param $arr
     * @return array
     */
    public function getYiweiArr($arr) {
        $data = array();
        foreach ($arr as $key => $val) {
            $data[] = $val['id'];
        }
        return $data;
    }

    public function dd($info) {

        if (is_object($info) || is_array($info)) {
            $info_text = var_export($info, true);
        } elseif (is_bool($info)) {
            $info_text = $info ? 'true' : 'false';
        } else {
            $info_text = $info;
        }

        file_put_contents('./dd.txt', $info_text);
    }

}
