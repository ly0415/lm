<?php

/**
 * 商品列表页面
 * @author wangh
 * @date 2017/08/22
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class listPageApp extends BaseFrontApp
{

    private $filterMenu = array();  //筛选菜单

    /**
     * 构造函数
     */

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {

    }

    /**
     * 三级分类页面
     * @author wangh
     * @date 2017/08/22
     */
    public function index()
    {
        //接受数据
        $cid = $_REQUEST['cid'];
        $page = !empty($_REQUEST['p']) ? $_REQUEST['p'] : 1;  //当前第几页
        $filter_param = array(); //筛选数组
        $style = !empty($_REQUEST['s']) ? $_REQUEST['s'] : '';  //风格
        $type = !empty($_REQUEST['t']) ? $_REQUEST['t'] : '';  //类型
        $brand = !empty($_REQUEST['b']) ? $_REQUEST['b'] : '';  //品牌
        $price = !empty($_REQUEST['pr']) ? $_REQUEST['pr'] : '';  // 价格
        $sort = !empty($_REQUEST['by']) ? $_REQUEST['by'] : 1; // 排序
        $spec = !empty($_REQUEST['sp']) ? $_REQUEST['sp'] : '';  // 规格
        $start_price = !empty($_REQUEST['start_price']) ? $_REQUEST['start_price'] : '';
        $end_price = !empty($_REQUEST['end_price']) ? $_REQUEST['end_price'] : '';
        //当前站点信息
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        if (!empty($start_price) && !empty($end_price)) {
            if ($end_price < $start_price) {
                $price = $end_price . '_' . $start_price;
            } else {
                $price = $start_price . '_' . $end_price;
            }
        }
        $baseUrl = '?app=listPage&act=index&storeid=' . $this->storeid . '&lang=' . $this->langid . '&auxiliary=' . $auxiliary . '&cid=' . $cid . '&';
        $clearAll = '?app=listPage&act=index&storeid=' . $this->storeid . '&lang=' . $this->langid . '&auxiliary=' . $auxiliary . '&cid=' . $cid;

        //加入筛选条件
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
        if (!empty($spec)) {
            $filter_param['sp'] = $spec;
        }
        if (!empty($sort)) {
            $filter_param['by'] = $sort;
        }


        //分类详情
        $ctgDetail = $this->getCtgDetail($cid, $this->langid);
        //面包屑导航
        $cateLink = $this->getCateLink($ctgDetail, $this->storeid, $this->langid);
        //所属模型的 规格项
        $goodspec = $this->goodSpec($cid, $this->langid, $filter_param, $baseUrl);
        //风格
        $goodstyle = $this->getGoodStyle($this->langid, $filter_param, $baseUrl);
        //业务类型
        $roomtype = $this->getgoodRoomType($this->langid, $filter_param, $baseUrl);
        //品牌
        $brand = $this->getGoodsBrand($this->langid, $filter_param, $baseUrl);
        //价格区间
        $piceQujian = $this->getPriceQujian($this->langid, $filter_param, $baseUrl, $start_price, $end_price);
        //该分类下的商品
        $goodsList = $this->getGoodsList($this->langid, $this->storeid, $filter_param, $cid, $page);
        // 商品评价星级
        $goodsCommentMod = &m('goodsComment');
        foreach ($goodsList['data'] as $k => $v) {
            $good_id = $v['id'];
            $sql = 'select  sum(goods_rank) as total_rank,count(*) as num from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $good_id;
            $trance = $goodsCommentMod->querySql($sql);
            $goodsList['data'][$k]['rate'] = $trance[0]['total_rank'] / $trance[0]['num'];
            $goodsList['data'][$k]['num'] = $trance[0]['num'];
        }
        //商品排序
        $goodsSort = $this->getGoodsSort($this->langid, $filter_param, $baseUrl);

        //加载语言包
        $this->load($this->shorthand, 'listpage/listpage');
        $this->assign('filterMenu', $this->filterMenu);
        $this->assign('ctgDetail', $ctgDetail);
        $this->assign('goodsSort', $goodsSort);
        $this->assign('cateLink', $cateLink);
        $this->assign('goodstyle', $goodstyle);
        $this->assign('roomtype', $roomtype);
        $this->assign('brand', $brand);
        $this->assign("user_id", $this->userId);
        $this->assign('clearAll', $clearAll);
        $this->assign('sprice', $start_price);
        $this->assign('eprice', $end_price);
        $this->assign('goodspec', $goodspec);
        $this->assign('piceQujian', $piceQujian);
        $this->assign('goodsList', $goodsList);
        $this->assign('langdata', $this->langData);
        $this->display('listpage/listpage.html');
    }

    public function getCtgDetail($cid, $langid)
    {
        $ctgMod = &m('goodsClass');
        if (!empty($langid)) {
            $where = '    where  l.`lang_id`  = ' . $langid;
        } else {
            $where = '    where  l.`lang_id`  = ' . $this->mrlangid;
        }
        $sql = 'SELECT  c.`id`,l.`category_name` as cname,c.parent_id_path,c.parent_id  FROM  ' .
            DB_PREFIX . 'goods_category AS c LEFT JOIN  ' .
            DB_PREFIX . 'goods_category_lang AS l ON c.`id` = l.`category_id`  ' .
            $where . '  AND  c.`id` = ' . $cid;
        $data = $ctgMod->querySql($sql);
        return $data[0];
    }

    /**
     * 面包屑导航
     * @author wangh
     * @date 2017/09/13
     */
    public function getCateLink($arr, $storeid, $lang)
    {
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
    public function getGoodsSort($langid, $filter_param, $baseUrl)
    {
        //加载语言包
        $this->load($this->shorthand, 'listpage/listpage');
        $a = $this->langData;
        $sortFilter = !empty($filter_param['by']) ? $filter_param['by'] : 1;
        unset($filter_param['by']);
        $sort = array(
            1 => array('by' => 1, 'val' => $a['From']),
            2 => array('by' => 2, 'val' => $a['high'])
        );
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
    public function getGoodsBrand($langid, $filter_param, $baseUrl)
    {
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
    public function getGoodStyle($langid, $filter_param, $baseUrl)
    {
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
    public function getgoodRoomType($langid, $filter_param, $baseUrl)
    {
        $roomTypeMod = &m('roomType');
        $typeFilter = !empty($filter_param['t']) ? explode('_', $filter_param['t']) : array();
        unset($filter_param['t']);
        $uri = urldecode(http_build_query($filter_param));
        $sql = 'SELECT  id as t_id  FROM  ' . DB_PREFIX . 'room_type  where  superior_id = 0  ORDER BY  sort ';
        $data = $roomTypeMod->querySql($sql);
        foreach ($data as $k => $v) {
            if (!empty($langid)) {
                $where = '    where  l.`lang_id`  = ' . $langid . ' and t.superior_id = ' . $v['t_id'];
            } else {
                $where = '    where  l.`lang_id`  = ' . $this->mrlangid . ' and t.superior_id != ' . $v['t_id'];
            }
            $sql = 'SELECT  t.`id`,l.`type_name`  FROM  ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN  ' . DB_PREFIX . 'room_type_lang  AS  l  ON  t.`id` = l.`type_id`  ' . $where;
            $res = $roomTypeMod->querySql($sql);
            $data[$k]['type'] = $res;
        }
        foreach ($data as $key => $val) {
            foreach ($val['type'] as $k => $v) {
                if (empty($typeFilter)) {  //当没有 类型 筛选的 时候
                    $data[$key]['type'][$k]['href'] = $baseUrl . $uri . '&t=' . $v['id'];
                } else {  //当有 类型 筛选的 时候
                    if (in_array($v['id'], $typeFilter)) {
                        $data[$key]['type'][$k]['ison'] = 1;
                        $a = array();
                        $a[] = $v['id'];
                        $dif = array_diff($typeFilter, $a);
                        $itemd = implode('_', $dif);
                        if (!empty($itemd)) {
                            $data[$key]['type'][$k]['href'] = $baseUrl . $uri . '&t=' . $itemd;
                        } else {
                            $data[$key]['type'][$k]['href'] = $baseUrl . $uri;
                        }
                        //加入筛选菜单
                        $menu = array(' <a href=' . $data[$key]['type'][$k]['href'] . ' class="tag">
                                  ' . $v['type_name'] . '<span class="del or">X</span>
                                </a>');
                        $this->filterMenu = array_merge($this->filterMenu, $menu);
                    } else {
                        $data[$key]['type'][$k]['ison'] = 0;
                        $b = array();
                        $b[] = $v['id'];
                        $meg = array_merge($typeFilter, $b); //取合集
                        $itemm = implode('_', $meg);
                        $data[$key]['type'][$k]['href'] = $baseUrl . $uri . '&t=' . $itemm;
                    }
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
    public function getPriceQujian2($cid, $storeid)
    {
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
    public function getPriceQujian($langid, $filter_param, $baseUrl, $start_price, $end_price)
    {
        $data = array();
        //加载语言包
        $this->load($this->shorthand, 'listpage/listpage');
        $ab = $this->langData;
        if ($this->syshort == 'RMB') {
            $data[1] = array('priceQj' => $ab['Price_Price'] . $this->symbol . '500' . $ab['Following'], 'val' => '0_500');
            $data[] = array('priceQj' => $ab['Price_Price'] . $this->symbol . '500' . $ab['reach'] . $this->symbol . '1,000', 'val' => '500_1000');
            $data[] = array('priceQj' => $ab['Price_Price'] . $this->symbol . '1,000' . $ab['reach'] . $this->symbol . '2,000', 'val' => '1000_2000');
            $data[] = array('priceQj' => $ab['Price_Price'] . $this->symbol . '2,000' . $ab['reach'] . $this->symbol . '3,000', 'val' => '2000_3000');
            $data[] = array('priceQj' => $ab['Price_Price'] . $this->symbol . '3,000' . $ab['reach'] . $this->symbol . '4,000', 'val' => '3000_4000');
            $data[] = array('priceQj' => $ab['Price_Price'] . $this->symbol . '4,000' . $ab['reach'] . $this->symbol . '5,000', 'val' => '4000_5000');
            $data[] = array('priceQj' => $ab['Price_Price'] . $this->symbol . '5000' . $ab['Above'], 'val' => '5000_*');
        } else {
            $data[1] = array('priceQj' => $ab['Price_Price'] . $this->symbol . '100' . $ab['Following'], 'val' => '0_100');
            $data[] = array('priceQj' => $ab['Price_Price'] . $this->symbol . '100' . $ab['reach'] . $this->symbol . '250', 'val' => '100_250');
            $data[] = array('priceQj' => $ab['Price_Price'] . $this->symbol . '250' . $ab['reach'] . $this->symbol . '500', 'val' => '250_500');
            $data[] = array('priceQj' => $ab['Price_Price'] . $this->symbol . '500' . $ab['reach'] . $this->symbol . '750', 'val' => '500_750');
            $data[] = array('priceQj' => $ab['Price_Price'] . $this->symbol . '750' . $ab['reach'] . $this->symbol . '1000', 'val' => '750_1000');
            $data[] = array('priceQj' => $ab['Price_Price'] . $this->symbol . '1000 ' . $ab['Above'], 'val' => '1000_*');
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
    public function goodSpec($cid, $langid, $filter_param, $baseUrl)
    {
        $goodsTypeMod = &m('goodsType');
        if (!empty($langid)) {
            $where = ' where  l.`lang_id`  = ' . $langid;
        } else {
            $where = ' where  l.`lang_id`  = ' . $this->mrlangid;
        }
        if (!empty($langid)) {
            $and = ' and  l.`lang_id`  = ' . $langid;
        } else {
            $and = ' and  l.`lang_id`  = ' . $this->mrlangid;
        }
        if (!empty($langid)) {
            $and2 = ' and  lang_id  = ' . $langid;
        } else {
            $and2 = ' and  lang_id  = ' . $this->mrlangid;
        }
        //
        $specFilter = !empty($filter_param['sp']) ? $filter_param['sp'] : array();
        unset($filter_param['sp']);
        $uri = urldecode(http_build_query($filter_param));
        $filterArr = explode('_', $specFilter);

        //商品模型
        $sql = 'SELECT   t.`id`,l.`type_name`,t.`category_id`  FROM   ' .
            DB_PREFIX . 'goods_type AS t LEFT JOIN  ' .
            DB_PREFIX . 'goods_type_lang AS l  ON t.`id`=l.`type_id`  ' .
            $where . '  AND  t.`category_id` = ' . $cid;
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
    public function getGoodsList($langid, $storeid, $filter_param, $cid, $page)
    {
        $userId = $this->userId;
        $by = !empty($filter_param['by']) ? $filter_param['by'] : 1;
        $brandFilter = !empty($filter_param['b']) ? explode('_', $filter_param['b']) : array();
        $styleFilter = !empty($filter_param['s']) ? explode('_', $filter_param['s']) : array();
        $typeFilter = !empty($filter_param['t']) ? explode('_', $filter_param['t']) : array();
        $specFilter = !empty($filter_param['sp']) ? explode('_', $filter_param['sp']) : array();
        $priceFilter = !empty($filter_param['pr']) ? $filter_param['pr'] : array();

        $storeGoodsMod = &m('areaGood');

        if ($by == 1) {
            $orderBy = '  order by  s.shop_price  asc ';
        } else {
            $orderBy = '  order by  s.shop_price  desc ';
        }

        $goodsId = array();
        $where = " where mark=1 and store_id={$storeid} and is_on_sale=1 ";
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
            if (empty($ids)) {
                $where .= " and cat_id={$cid} ";
            } else {
                $where .= " and (goods_id in ({$ids}) or cat_id={$cid}) ";
            }
        }

        //品牌
        if (!empty($brandFilter)) {
            $brandids = implode(', ', $brandFilter);
            $where .= " and brand_id in ({$brandids})";
        }
        //风格
        if (!empty($styleFilter)) {
            $styleids = implode(', ', $styleFilter);
            $where .= " and style_id in ({$styleids})";
        }
        // 类型
        if (!empty($typeFilter)) {
            $typeids = implode(', ', $typeFilter);
            $where .= " and room_id in ({$typeids})";
        }
        $sql = 'SELECT id FROM ' . DB_PREFIX . 'store_goods ' . $where;
        $dataD = $storeGoodsMod->querySql($sql);
        $goodsId = $this->getYiweiArr($dataD);
        // 价格
        if (!empty($priceFilter)) {
            $arr2 = array();
            $arr1 = explode('-', $priceFilter);
            foreach ($arr1 as $key => $val) {
                $arr1[$key] = explode('_', $val);
            }
            foreach ($arr1 as $val) {
                if (in_array('*', $val)) {
                    $arr2[] = ' and shop_price >= ' . $val[0];
                } else {
                    $arr2[] = ' and shop_price >= ' . $val[0] . ' and shop_price <= ' . $val[1];
                }
            }
            $res = array();
            foreach ($arr2 as $v) {
                $sql = 'SELECT id FROM ' . DB_PREFIX . 'store_goods WHERE mark = 1 and store_id = ' . $storeid . $v;
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
                $like[] = ' `key` like "%' . $val . '%" ';
            }
            $or = '(' . implode(' or', $like) . ')';

            $storeGoodsSpecPrice = &m('storeGoodItemPrice');
            $sql = 'select store_goods_id AS id from ' . DB_PREFIX . 'store_goods_spec_price where ' . $or;
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

        $limit = ' limit ' . ($curpage - 1) * $pagesize . ', ' . $pagesize;
        //实例化分页类
        $pageClass = new page2($total, $pagesize, $curpage, $url, 2);
        // 输出分页html
        $pagelink = $pageClass->myde_write();

        //
        $gids = implode(', ', $goodsId);
        $where = ' where s.store_id = ' . $storeid . ' and s.id in(' . $gids . ') and s.mark = 1 and s.is_on_sale = 1 AND l.`lang_id` = ' . $this->langid;

        $sql = 'SELECT s.id, s.`goods_id`, s.`cat_id`, s.`store_id`, l.`goods_name`, l.`lang_id`, s.`shop_price`, s.`market_price`, s.`brand_id`, gl.`original_img`, s.is_free_shipping
                    FROM ' . DB_PREFIX . 'store_goods AS s LEFT JOIN ' . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id`  LEFT JOIN  ' . DB_PREFIX . 'goods AS gl ON s.`goods_id` = gl.`goods_id` ' . $where . $orderBy . $limit;

        $arr = $storeGoodsMod->querySql($sql);


        $shorthand = $this->shorthand;
        foreach ($arr as &$item) {
            //店铺商品打折
            $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $this->storeid;
            $store_arr = $storeGoodsMod->querySql($store_sql);
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

//            收藏商品
            $sql_collection = 'select * from ' . DB_PREFIX . 'user_collection where user_id=' . $userId . ' and store_id=' . $storeid;
            $data_collection = $storeGoodsMod->querySql($sql_collection);
            foreach ($data_collection as &$collertion) {
                if ($collertion['store_good_id'] == $item['id']) {
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

    /**
     * @param $arr
     * @return array
     */
    public function getYiweiArr($arr)
    {
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
    public function getStoreGoodsLang($goodsId, $langid)
    {
        $storeGLMod = &m('storeGoodsLang');
        $sql = 'SELECT  goods_name  FROM  ' . DB_PREFIX . 'store_goods_lang   WHERE  store_good_id = ' . $goodsId . ' AND   lang_id =' . $langid;
        $res = $storeGLMod->querySql($sql);
        return $res[0]['goods_name'];
    }

}
