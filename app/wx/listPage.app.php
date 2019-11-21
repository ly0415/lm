<?php

/**
 * 商品列表页面
 * @author wangh
 * @date 2017/08/22
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class ListPageApp extends BaseWxApp {

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
        //语言包
        $this->load($this->shorthand, 'WeChat/goods');
        $this->assign('langdata', $this->langData);
        $lang_id = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : 29;
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
        $this->assign('latlon',$latlon);
        $this->assign('auxiliary', $auxiliary);
        $this->assign('sprice', $start_price);
        $this->assign('eprice', $end_price);

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

        //
        $baseUrl = '?app=listPage&act=index&storeid=' . $this->storeid . '&lang=' . $this->langid . '&auxiliary=' . $auxiliary . '&cid=' . $cid . '&latlon='.$latlon.'&';
        $clearAll = '?app=listPage&act=index&storeid=' . $this->storeid . '&lang=' . $this->langid . '&auxiliary=' . $auxiliary . '&cid=' . $cid.'&latlon='.$latlon;

        //加入筛选条件
        if (!empty($brand)) {
            $filter_param['b'] = $brand;
        }
        if (!empty($price)) {
            $filter_param['pr'] = $price;
        }
        if (!empty($sort)) {
            $filter_param['by'] = $sort;
        }

        //品牌
        $brand = $this->getGoodsBrand($this->langid, $filter_param, $baseUrl);
        //该分类下的商品
        $goodsList = $this->getGoodsList($this->langid, $this->storeid, $filter_param, $cid, $page);
        //商品排序
        $goodsSort = $this->getGoodsSort($this->langid, $filter_param, $baseUrl);

        $this->assign('lang', $lang_id);
        $this->assign('by', $sort);
        $this->assign('goodsSort', $goodsSort);
        $this->assign('brand', $brand);
        $this->assign('clearAll', $clearAll);
        $this->assign('curUrl', $curUrl);
//        $this->assign('state', $state);
        $this->assign('goodsList', $goodsList);
        $this->assign('cid', $cid);
        $this->display('listPage/listPage.html');
    }

    public function getCtgDetail($cid, $langid) {
        $ctgMod = &m('goodsClass');
        if (!empty($langid)) {
            $where = '    where  l.`lang_id`  = ' . $langid;
        } else {
            $where = '    where  l.`lang_id`  = ' . $this->mrlangid;
        }
        $sql = 'SELECT  c.`id`,l.`category_name` as cname,c.parent_id_path,c.parent_id  FROM  ' . DB_PREFIX . 'goods_category AS c
                LEFT JOIN  ' . DB_PREFIX . 'goods_category_lang AS l ON c.`id` = l.`category_id`  ' . $where . '  AND  c.`id` = ' . $cid;
        $data = $ctgMod->querySql($sql);
        return $data[0];
    }

    /**
     * 面包屑导航
     * @author wangh
     * @date 2017/09/13
     */
    public function getCateLink($arr, $storeid, $lang) {
        $catelik = '';
        $parent_id = $arr['parent_id'];
        if ($parent_id == 0) {
            $catelik .= ' <a href="javascript:;">' . $arr['name'] . '</a>';
        } else {
            $path = $arr['parent_id_path'];
            $pathArr = explode('_', $path);
            array_shift($pathArr);
            // 导航
            $pidpid = $this->getCtgDetail($pathArr[0], $lang);
            $catelik .= '<a href="?app=ctgPage&act=index&storeid=' . $storeid . '&lang=' . $lang . '&cid=' . $pidpid['id'] . '">' . $pidpid['cname'] . '</a>';
            $pid = $this->getCtgDetail($pathArr[1], $lang);
            $catelik .= '<i>/</i>';
            $catelik .= '<a href="?app=ctgPage&act=index&storeid=' . $storeid . '&lang=' . $lang . '&cid=' . $pid['id'] . '">' . $pid['cname'] . '</a>';
            $catelik .= ' <i>/</i>';
            $catelik .= '<span class="or">' . $arr['cname'] . '</span>';
        }
        return $catelik;
    }

    /**
     * 商品排序
     * @author wangh
     * @date 2017/09/13
     */
    public function getGoodsSort($langid, $filter_param, $baseUrl) {
        //加载语言包
        $this->load($this->shorthand, 'listpage/listpage');
        $a = $this->langData;
        $sortFilter = !empty($filter_param['by']) ? $filter_param['by'] : 1;
        unset($filter_param['by']);
        $sort = array(
            1 => array('by' => 1, 'val' => $a['From']),
            2 => array('by' => 2, 'val' => $a['high']),
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

        // echo '<pre>';print_r($data);

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
     * 价格区间2
     * @author wangh
     * @date 2017/09/13
     */
    public function getPriceQujian2($cid, $storeid) {
        $storegoodsMod = &m('areaGood');
        $sql = 'SELECT  MAX(shop_price) AS maxprice , MIN(shop_price) AS minprice  FROM  ' . DB_PREFIX . 'store_goods   where cat_id=' . $cid . '  and  store_id =' . $storeid . '  and  is_on_sale =1 ';
        $data = $storegoodsMod->querySql($sql);
        //该分类下的商品的最大和最小价格
        $minprice = intval($data[0]['minprice']);
        $maxprice = intval($data[0]['maxprice']);
        //价格区间
        $priceQujian = 4;
        $res = array(); //价格区间数组
        $priceTidu = ceil(($maxprice - $minprice) / $priceQujian);
        $firstprice = $minprice;
        //无价格差异
        if ($minprice == $maxprice) {
            $res[] = array(
                'val' => floor(($firstprice / 10) * 10) . '_' . floor((($firstprice) / 10) * 10 - 1),
                'price' => 'price ￥' . floor(($firstprice / 10) * 10) . ' to ￥' . floor((($firstprice) / 10) * 10)
            );
        } else {
            //有价格差异
            for ($i = 1; $i <= $priceQujian; $i++) {
                if ($i != $priceQujian) {
                    $res[] = array(
                        'val' => floor(($firstprice / 10) * 10) . '_' . floor((($firstprice + $priceTidu) / 10) * 10 - 1),
                        'price' => 'price ￥' . floor(($firstprice / 10) * 10) . ' to ￥' . floor((($firstprice + $priceTidu) / 10) * 10 - 1)
                    );
                } else {
                    $res[] = array(
                        'val' => floor(($firstprice / 10) * 10) . '_' . ceil($maxprice / 10) * 10,
                        'price' => 'price ￥' . floor(($firstprice / 10) * 10) . ' to ￥' . ceil($maxprice / 10) * 10
                    );
                }

                $firstprice += $priceTidu; //起始价格自增一个价格梯度
            }
        }

        return $res;
    }

    /**
     * 价格区间
     * @author wangh
     * @date 2017/09/13
     */

    /**
     * 价格区间
     * @author wangh
     * @date 2017/09/13
     */
    public function getPriceQujian($langid, $filter_param, $baseUrl, $start_price, $end_price) {
        //加载语言包
        $this->load($this->shorthand, 'listpage/listpage');
        $a = $this->langData;
        //
        $data = array();
        if ($this->syshort == 'RMB') {
            $data[1] = array('priceQj' => $a['Price_Price'] . $this->symbol . '500' . $a['Following'], 'val' => '0_500');
            $data[] = array('priceQj' => $a['Price_Price'] . $this->symbol . '500' . $a['reach'] . $this->symbol . '1,000', 'val' => '500_1000');
            $data[] = array('priceQj' => $a['Price_Price'] . $this->symbol . '1,000' . $a['reach'] . $this->symbol . '2,000', 'val' => '1000_2000');
            $data[] = array('priceQj' => $a['Price_Price'] . $this->symbol . '3,000' . $a['reach'] . $this->symbol . '4,000', 'val' => '3000_4000');
            $data[] = array('priceQj' => $a['Price_Price'] . $this->symbol . '4,000' . $a['reach'] . $this->symbol . '5,000', 'val' => '4000_5000');
            $data[] = array('priceQj' => $a['Price_Price'] . $this->symbol . '5000' . $a['Above'], 'val' => '5000_*');
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
     * 商品规格
     * @author wangh
     * @date 2017/09/13
     */
    public function goodSpec($cid, $langid, $filter_param, $baseUrl) {
        $goodsTypeMod = &m('goodsType');
        if (!empty($langid)) {
            $where = '    where  l.`lang_id`  = ' . $langid;
        } else {
            $where = '    where  l.`lang_id`  = ' . $this->mrlangid;
        }
        if (!empty($langid)) {
            $and = '    and  l.`lang_id`  = ' . $langid;
        } else {
            $and = '    and  l.`lang_id`  = ' . $this->mrlangid;
        }
        if (!empty($langid)) {
            $and2 = '    and  lang_id  = ' . $langid;
        } else {
            $and2 = '    and  lang_id  = ' . $this->mrlangid;
        }
        //
        $specFilter = !empty($filter_param['sp']) ? $filter_param['sp'] : array();
        unset($filter_param['sp']);
        $uri = urldecode(http_build_query($filter_param));
        $filterArr = explode('_', $specFilter);

        //商品模型
        $sql = 'SELECT   t.`id`,l.`type_name`,t.`category_id`  FROM   ' . DB_PREFIX . 'goods_type AS t
                LEFT JOIN  ' . DB_PREFIX . 'goods_type_lang AS l  ON t.`id`=l.`type_id`  ' . $where . '  AND  t.`category_id` = ' . $cid;
        $res = $goodsTypeMod->querySql($sql);
        //规格
        $goodsSpecMod = &m('goodsSpec');
        $goodsSpecItemMod = &m('goodsSpecItem');
        //规格值
        $sql2 = 'SELECT   s.`id`,l.`spec_name`,s.`type_id`,GROUP_CONCAT(i.`id`  ORDER BY i.`id`  ASC  )  AS items  FROM  ' . DB_PREFIX . 'goods_spec AS s
                  LEFT JOIN  ' . DB_PREFIX . 'goods_spec_lang AS l ON s.`id` = l.`spec_id`
                  LEFT JOIN  ' . DB_PREFIX . 'goods_spec_item  AS i ON i.`spec_id` = s.`id`
                  WHERE  s.`type_id` = ' . $res[0]['id'] . $and . '  GROUP  BY s.`id`';
        $data = $goodsSpecMod->querySql($sql2);
        foreach ($data as $i => $item) {
            $sql3 = 'SELECT  id,item_id,item_name  FROM  ' . DB_PREFIX . 'goods_spec_item_lang   WHERE  item_id IN(' . $item['items'] . ')' . $and2;
            $data[$i]['specitems'] = $goodsSpecItemMod->querySql($sql3);
        }
        //
        foreach ($data as $key => $value) {
            foreach ($value['specitems'] as $k => $val) {

                if (empty($specFilter)) {  //当没有 类型 筛选的 时候
                    $data[$key]['specitems'][$k]['href'] = $baseUrl . $uri . '&sp=' . $val['item_id'];
                } else {
                    if (in_array($val['item_id'], $filterArr)) {
                        $data[$key]['specitems'][$k]['ison'] = 1;
                        $a = array();
                        $a[] = $val['item_id'];
                        $dif = array_diff($filterArr, $a);
                        $itemd = implode('_', $dif);
                        if (!empty($itemd)) {
                            $data[$key]['specitems'][$k]['href'] = $baseUrl . $uri . '&sp=' . $itemd;
                        } else {
                            $data[$key]['specitems'][$k]['href'] = $baseUrl . $uri;
                        }
                        //加入筛选菜单
                        $menu = array(' <a href=' . $data[$key]['specitems'][$k]['href'] . ' class="tag">
                                  ' . $value['spec_name'] . ':' . $val['item_name'] . '<span class="del or">X</span>
                                </a>');
                        $this->filterMenu = array_merge($this->filterMenu, $menu);
                    } else {
                        $data[$key]['specitems'][$k]['ison'] = 0;
                        $b = array();
                        $b[] = $val['item_id'];
                        $meg = array_merge($filterArr, $b); //取合集
                        $itemm = implode('_', $meg);
                        $data[$key]['specitems'][$k]['href'] = $baseUrl . $uri . '&sp=' . $itemm;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * 商品列表
     * @author wangh
     * @date 2017/09/18
     */
    public function getGoodsList($langid, $storeid, $filter_param, $cid, $page) {

        // $cid = 1068;

        $userId = $this->userId;
        $by = !empty($filter_param['by']) ? $filter_param['by'] : 1;
        $brandFilter = !empty($filter_param['b']) ? explode('_', $filter_param['b']) : array();
        $styleFilter = !empty($filter_param['s']) ? explode('_', $filter_param['s']) : array();
        $typeFilter = !empty($filter_param['t']) ? explode('_', $filter_param['t']) : array();
        $specFilter = !empty($filter_param['sp']) ? explode('_', $filter_param['sp']) : array();
        $priceFilter = !empty($filter_param['pr']) ? $filter_param['pr'] : array();
        $storeGoodsMod = &m('areaGood');

        if ($by == 1) {
            $orderBy = '  order by  s.shop_price  desc ';
        } else if ($by == 2) {
            $orderBy = '  order by  s.shop_price  asc ';
        }
        // else if ($by == 3) {
        //     $orderBy = '  order by  s.add_time  desc ';
        // }


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
        $url = '?app=listPage&act=index&storeid=' . $this->storeid . '&lang=' . $this->langid . '&cid=' . $cid . '&' . $uri; //
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
            $this->load($this->shorthand, 'listpage/listpage');
            $a = $this->langData;
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

                $child_info = $storeGoodsMod->getLangInfo($item['id'], $this->langid);

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
            $where2 = '  where  b.store_id = ' . $this->storeid . ' and  b.mark =1 and g.mark=1 and g.is_on_sale=1';
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

            $where3 = 'WHERE  l.`lang_id` = ' . $this->langid . '  and  b.store_id =' . $this->storeid . '  AND b.is_end =1 AND b.mark = 1 and g.mark=1 and g.is_on_sale=1 ';
            $sql3 = 'SELECT  b.id as cid,b.goods_id as id,b.store_id,b.end_time,b.group_goods_price as price,b.virtual_num,gsl.original_img,b.goods_price as o_price,l.`goods_name`,b.goods_spec_key as item_key  FROM  '
                . DB_PREFIX . 'goods_group_buy   AS b  LEFT JOIN  '
                . DB_PREFIX . 'store_goods AS g ON b.`goods_id` = g.id LEFT JOIN  '
                . DB_PREFIX . 'goods_lang AS l ON g.`goods_id` = l.`goods_id`  LEFT JOIN  '
                . DB_PREFIX . 'goods AS gsl ON g.`goods_id` = gsl.`goods_id` ' . $where3;
            $groupByGoodArr = $goodsByMod->querySql($sql3);
            foreach ($groupByGoodArr as &$item) {
                $item['goods_name']= $cartMod->getGoodNameById($item['id'], $this->langid);
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
                $item['goods_name']= $cartMod->getGoodNameById($item['id'], $this->langid);
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
            $where = '  where   s.store_id =' . $storeid . '  and   s.id  in(' . $gids . ')   and   s.mark=1   and   s.is_on_sale =1  AND l.`lang_id` = ' . $this->langid;

            // by xt 2019.01.18
            if ($by == 3) {
                $where .= ' and s.is_free_shipping = 1';
            }

            $sql = 'SELECT s.id,s.`goods_id`,s.`cat_id`,s.`store_id`,l.`goods_name`,l.`lang_id`,s.`shop_price`,s.`market_price`,s.`brand_id`,gl.`original_img`,s.is_free_shipping
                FROM  '
                    . DB_PREFIX . 'store_goods AS s LEFT JOIN  '
                    . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id` LEFT JOIN '
                    . DB_PREFIX . 'goods AS gl ON s.`goods_id` = gl.`goods_id` ' . $where . $orderBy . $limit;
            $arr = $storeGoodsMod->querySql($sql);
            //加载语言包
            $this->load($this->shorthand, 'listpage/listpage');
            $a = $this->langData;

            foreach ($arr as &$item) {
            //店铺商品打折
            $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $this->storeid;
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

            // echo '<pre>';print_r($arr);

            //组装数据
            $res = array();
            $res['data'] = $arr;
            $res['pagelink'] = $pagelink;
            $res['count'] = $total;

            return $res;
        }
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

}
