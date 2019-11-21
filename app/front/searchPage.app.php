<?php

/**
 * 商品列表页面
 * @author wangh
 * @date 2017/08/22
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class SearchPageApp extends BaseFrontApp {

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

    /**
     * 三级分类页面
     * @author wangh
     * @date 2017/08/22
     */
    public function index() {
        $page = !empty($_REQUEST['p']) ? $_REQUEST['p'] : 1;  //当前第几页
        $filter_param = array(); //筛选数组
        $gname = !empty($_REQUEST['gname']) ? $_REQUEST['gname'] : '';
        $style = !empty($_REQUEST['s']) ? $_REQUEST['s'] : '';  //风格
        $type = !empty($_REQUEST['t']) ? $_REQUEST['t'] : '';  //类型
        $brand = !empty($_REQUEST['b']) ? $_REQUEST['b'] : '';  //类型
        $price = !empty($_REQUEST['pr']) ? $_REQUEST['pr'] : '';  // 价格
        $sort = !empty($_REQUEST['by']) ? $_REQUEST['by'] : 1; // 排序
        $start_price = !empty($_REQUEST['start_price']) ? $_REQUEST['start_price'] : '';
        $end_price = !empty($_REQUEST['end_price']) ? $_REQUEST['end_price'] : '';
        if (!empty($start_price) && !empty($end_price)) {
            if ($end_price < $start_price) {
                $price = $end_price . '_' . $start_price;
            } else {
                $price = $start_price . '_' . $end_price;
            }
        }

        $baseUrl = '?app=searchPage&act=index&storeid=' . $this->storeid . '&lang=' . $this->langid . '&';
        $clearAll = '?app=searchPage&act=index&storeid=' . $this->storeid . '&lang=' . $this->langid . '&gname=' . $gname;
        //加入筛选条件
        if (!empty($gname)) {
            $filter_param['gname'] = $gname;
        }
        if (!empty($style)) {
            $filter_param['s'] = $style;
        }
        if (!empty($brand)) {
            $filter_param['b'] = $brand;
        }
        if (!empty($type)) {
            $filter_param['t'] = $type;
        }
        if (!empty($price)) {
            $filter_param['pr'] = $price;
        }
        if (!empty($sort)) {
            $filter_param['by'] = $sort;
        }


        //风格
        $goodstyle = $this->getGoodStyle($this->langid, $filter_param, $baseUrl);
        //业务类型
        $roomtype = $this->getgoodRoomType($this->langid, $filter_param, $baseUrl);
        //价格区间
        $piceQujian = $this->getPriceQujian($this->langid, $filter_param, $baseUrl, $start_price, $end_price);

        //品牌
        $brand = $this->getGoodsBrand($this->langid, $filter_param, $baseUrl);
        //商品列表
        $goodslist = $this->getGoodsList($this->langid, $this->storeid, $filter_param, $page);
        //商品排序
        $goodsSort = $this->getGoodsSort($this->langid, $filter_param, $baseUrl);

        //加载语言包
        $this->load($this->shorthand, 'searchpage/searchpage');

        $this->assign('filterMenu', $this->filterMenu);
        $this->assign('brand', $brand);
        $this->assign('goodslist', $goodslist);
        $this->assign('goodstyle', $goodstyle);
        $this->assign('roomtype', $roomtype);
        $this->assign('piceQujian', $piceQujian);
        $this->assign('clearAll', $clearAll);
        $this->assign('langdata', $this->langData);
        $this->assign('sprice', $start_price);
        $this->assign('eprice', $end_price);
        $this->assign('goodsSort', $goodsSort);
        $this->display('searchpage/searchpage.html');
    }

    /** 获取商品类别
     * @param $langid
     * @param $filer_param
     */
    public function getGoodsList($langid, $storeid, $filter_param, $page) {
        $by = !empty($filter_param['by']) ? $filter_param['by'] : 1;
        $gname = !empty($filter_param['gname']) ? htmlspecialchars(trim($filter_param['gname'])) : '';
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
            $sql = 'SELECT  s.id,l.goods_name   FROM   ' . DB_PREFIX . 'store_goods  as s  LEFT JOIN  ' . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id`
                    WHERE  s.store_id =' . $storeid . '   and  l.lang_id = ' . $this->langid . '  and  s.mark=1  '
                    . 'and   ( l.goods_name like "%' . $gname . '%" or l.keywords like "%' . $gname . '%" )';
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

        //添加分页类
        include(ROOT_PATH . '/data/page/pageClass.php');
        //商品列表
        $total = count($goodsId);  //总条数
        $uri = urldecode(http_build_query($filter_param));
        $url = '?app=searchPage&act=index&storeid=' . $this->storeid . '&lang=' . $this->langid . '&' . $uri; //
        $pagesize = $this->pagesize; //每页显示条数
        $curpage = $page;  //当前页数
        $limit = '  limit ' . ($curpage - 1 ) * $pagesize . ',' . $pagesize;
        //实例化分页类
        $pageClass = new page2($total, $pagesize, $curpage, $url, 2);
        // 输出分页html
        $pagelink = $pageClass->myde_write();
        //
        $gids = implode(',', $goodsId);


        $where = '  where   s.store_id =' . $storeid . '  and   s.id  in(' . $gids . ')   and   s.mark=1   and   s.is_on_sale =1  AND l.`lang_id` = ' . $this->langid;

        $sql = 'SELECT s.id,s.`goods_id`,s.`cat_id`,s.`store_id`,l.`goods_name`,l.`lang_id`,s.`shop_price`,s.`market_price`,s.`brand_id`,gl.`original_img`,s.is_free_shipping
                FROM  ' . DB_PREFIX . 'store_goods AS s LEFT JOIN  ' . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id` left join ' . DB_PREFIX . 'goods AS gl ON s.`goods_id` = gl.`goods_id` ' . $where . $orderBy . $limit;
        $arr = $storeGoodMod->querySql($sql);


        $shorthand = $this->shorthand;
        foreach ($arr as &$item) {
             $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $this->storeid;
            $store_arr = $storeGoodMod->querySql($store_sql);
            $item['shop_price'] =number_format($item['shop_price'] * $store_arr[0]['store_discount'],2);
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
        $res['pagelink'] = $pagelink;
        $res['count'] = $total;

        return $res;
    }

    /**
     * 获取商品的多语言信息
     * @param $goodsId
     * @param $langid
     * @return mixed
     */
    public function getStoreGoodsLang($goodsId, $langid) {
        $storeGLMod = &m('storeGoodsLang');
        $sql = 'SELECT  goods_name  FROM  ' . DB_PREFIX . 'store_goods_lang   WHERE  store_good_id = ' . $goodsId . ' AND   lang_id =' . $langid;
        $res = $storeGLMod->querySql($sql);
        return $res[0]['goods_name'];
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

    /**
     * 商品风格
     * @author wangh
     * @date 2017/09/13
     */
    public function getGoodStyle($langid, $filter_param, $baseUrl) {
        $goodstyleMod = &m('goodsStyle');
        $styleFilter = !empty($filter_param['s']) ? explode('_', $filter_param['s']) : array();

        unset($filter_param['s']);
        $uri = urldecode(http_build_query($filter_param));

        if (!empty($langid)) {
            $where = '    where  l.`lang_id`  = ' . $langid;
        } else {
            $where = '    where  l.`lang_id`  = ' . $this->mrlangid;
        }
        $sql = 'SELECT t.`id`,l.`style_name` FROM  ' . DB_PREFIX . 'goods_style AS t
                 LEFT JOIN  ' . DB_PREFIX . 'goods_style_lang AS l ON  t.`id` = l.`style_id`  ' . $where;
        $data = $goodstyleMod->querySql($sql);

        foreach ($data as $key => $val) {
            if (empty($styleFilter)) { //当没有风格 筛选的 时候
                $data[$key]['href'] = $baseUrl . $uri . '&s=' . $val['id'];
            } else {  //当有 风格 筛选的 时候
                if (in_array($val['id'], $styleFilter)) {  // 减去
                    $data[$key]['ison'] = 1;
                    $a = array();
                    $a[] = $val['id'];
                    $dif = array_diff($styleFilter, $a); //取差集
                    $itemd = implode('_', $dif);
                    if (!empty($itemd)) {
                        $data[$key]['href'] = $baseUrl . $uri . '&s=' . $itemd;
                    } else {
                        $data[$key]['href'] = $baseUrl . $uri;
                    }
                    //加入筛选菜单
                    $menu = array(' <a href=' . $data[$key]['href'] . ' class="tag">
                                  ' . $val['style_name'] . '<span class="del or">X</span>
                                </a>');
                    $this->filterMenu = array_merge($this->filterMenu, $menu);
                } else {  //加上
                    $data[$key]['ison'] = 0;
                    $b = array();
                    $b[] = $val['id'];
                    $meg = array_merge($styleFilter, $b); //取合集
                    $itemm = implode('_', $meg);
                    $data[$key]['href'] = $baseUrl . $uri . '&s=' . $itemm;
                }
            }
        }

        return $data;
    }

    /**
     * 业务类型
     * @author wangh
     * @date 2017/09/13
     */
    public function getgoodRoomType($langid, $filter_param, $baseUrl) {
        $roomTypeMod = &m('roomType');
        $typeFilter = !empty($filter_param['t']) ? explode('_', $filter_param['t']) : array();
        unset($filter_param['t']);
        $uri = urldecode(http_build_query($filter_param));
        if (!empty($langid)) {
            $where = '    where  l.`lang_id`  = ' . $langid;
        } else {
            $where = '    where  l.`lang_id`  = ' . $this->mrlangid;
        }
        $sql = 'SELECT  t.`id`,l.`type_name`  FROM  ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN  ' . DB_PREFIX . 'room_type_lang  AS  l  ON  t.`id` = l.`type_id`  ' . $where;
        $data = $roomTypeMod->querySql($sql);
        foreach ($data as $key => $val) {
            if (empty($typeFilter)) {  //当没有 类型 筛选的 时候
                $data[$key]['href'] = $baseUrl . $uri . '&t=' . $val['id'];
            } else {  //当有 类型 筛选的 时候
                if (in_array($val['id'], $typeFilter)) {
                    $data[$key]['ison'] = 1;
                    $a = array();
                    $a[] = $val['id'];
                    $dif = array_diff($typeFilter, $a);
                    $itemd = implode('_', $dif);
                    if (!empty($itemd)) {
                        $data[$key]['href'] = $baseUrl . $uri . '&t=' . $itemd;
                    } else {
                        $data[$key]['href'] = $baseUrl . $uri;
                    }
                    //加入筛选菜单
                    $menu = array(' <a href=' . $data[$key]['href'] . ' class="tag">
                                  ' . $val['type_name'] . '<span class="del or">X</span>
                                </a>');
                    $this->filterMenu = array_merge($this->filterMenu, $menu);
                } else {
                    $data[$key]['ison'] = 0;
                    $b = array();
                    $b[] = $val['id'];
                    $meg = array_merge($typeFilter, $b); //取合集
                    $itemm = implode('_', $meg);
                    $data[$key]['href'] = $baseUrl . $uri . '&t=' . $itemm;
                }
            }
        }

        return $data;
    }

    /**
     * 价格区间
     * @author wangh
     * @date 2017/09/13
     */
    public function getPriceQujian($langid, $filter_param, $baseUrl, $start_price, $end_price) {
        $data = array();

        if ($this->syshort == '人民币') {
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
     * 手动输入价格
     * url 跳转
     */
    public function ajaxUrl() {
        $cururl = $_REQUEST['cururl'];  //当前页面的url
        $start_price = !empty($_REQUEST['start_price']) ? $_REQUEST['start_price'] : '';
        $end_price = !empty($_REQUEST['end_price']) ? $_REQUEST['end_price'] : '';
        $arr = explode("&", $cururl);
        foreach ($arr as $key => $val) {
            if (strpos($val, 'start_price') !== false) {
                unset($arr[$key]);
            } elseif (strpos($val, 'end_price') !== false) {
                unset($arr[$key]);
            }
        }
        $arr[] = 'start_price=' . $start_price;
        $arr[] = 'end_price=' . $end_price;
        $reurl = implode('&', $arr);
        $urljson = json_encode(array('reurl' => $reurl));
        echo $urljson;
    }

}
