<?php

/**
 * 二级分类页面
 * @author wangh
 * @date 2017/08/22
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class CtgPageApp extends BaseFrontApp {

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
     * 二级分类页面
     * @author wangh
     * @date 2017/08/22
     */
    public function index() {
        //接受数据
        $cid = $_REQUEST['cid'];
        $page = !empty($_REQUEST['p']) ? $_REQUEST['p'] : 1;  //当前第几页
        //
        $ctgDetail = $this->getCtgDetail($cid, $this->langid);
        $childs = $this->getctgChilds($cid, $this->langid);
        //面包屑导航
        $cateLink = $this->getCateLink($ctgDetail, $this->storeid, $this->langid);

        //子分类
        if ($ctgDetail['parent_id'] == 0) {
            foreach ($childs as &$val) {
                $val['link'] = '?app=ctgPage&act=index&storeid=' . $this->storeid . '&lang=' . $this->langid . '&cid=' . $val['id'];
            }
        } else {
            foreach ($childs as &$val) {
                $val['link'] = '?app=listPage&act=index&storeid=' . $this->storeid . '&lang=' . $this->langid . '&cid=' . $val['id'];
            }
        }
        // 分类下的商品
        $allChilds = $this->getCtgGoods($cid, $this->storeid, $this->langid, $page);

        // 分类商品评价星级
        $this->goodsCommentMod = &m('goodsComment');
        foreach ($allChilds['data'] as $k => $v) {
            $good_id = $v['id'];
            $sql = 'select  sum(goods_rank) / count(goods_rank) as res,count(*) as num from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $good_id;
            $trance = $this->goodsCommentMod->querySql($sql);
            $allChilds['data'][$k]['rate'] = $trance[0]['res'];
            $allChilds['data'][$k]['num'] = $trance[0]['num'];
        }

        //加载语言包
        $this->load($this->shorthand, 'ctgpage/ctgpage');
        $this->assign('allChilds', $allChilds);
        $this->assign("user_id", $this->userId);
        $this->assign('cateLink', $cateLink);
        $this->assign('ctgDetail', $ctgDetail);
        $this->assign('childs', $childs);
        $this->assign('langdata', $this->langData);
        $this->display('ctgpage/ctgpage.html');
    }

    public function getCtgDetail($cid, $langid) {
        $ctgMod = &m('goodsClass');
        if (!empty($langid)) {
            $where = '    where  l.`lang_id`  = ' . $langid;
        } else {
            $where = '    where  l.`lang_id`  = ' . $this->mrlangid;
        }
        $sql = 'SELECT  c.`id`,l.`category_name` as cname,c.parent_id_path,c.parent_id,c.image,c.adv_img  FROM  ' . DB_PREFIX . 'goods_category AS c
                LEFT JOIN  ' . DB_PREFIX . 'goods_category_lang AS l ON c.`id` = l.`category_id`  ' . $where . '  AND  c.`id` = ' . $cid;
        $data = $ctgMod->querySql($sql);
        return $data[0];
    }

    public function getctgChilds($pid, $langid) {
        $ctgMod = &m('goodsClass');
        if (!empty($langid)) {
            $where = '    where  l.`lang_id`  = ' . $langid;
        } else {
            $where = '    where  l.`lang_id`  = ' . $this->mrlangid;
        }
        $sql = 'SELECT  c.`id`,l.`category_name` as cname,c.parent_id_path,c.parent_id,c.image  FROM  ' . DB_PREFIX . 'goods_category AS c
                LEFT JOIN  ' . DB_PREFIX . 'goods_category_lang AS l ON c.`id` = l.`category_id`  ' . $where . '  AND  c.`parent_id` = ' . $pid;
        $sql .= '  order by  c.id ';

        $data = $ctgMod->querySql($sql);
        return $data;
    }

    /**
     * 面包屑导航
     * @author wangh
     * @date 2017/09/13
     */
    public function getCateLink($arr, $storeid, $langid) {
        $catelik = '';
        $parent_id = $arr['parent_id'];
        if ($parent_id == 0) {
            $catelik .= ' <a href="javascript:;">' . $arr['cname'] . '</a>';
        } else {
            $path = $arr['parent_id_path'];
            $pathArr = explode('_', $path);
            array_shift($pathArr);
            // 导航
            $pidpid = $this->getCtgDetail($pathArr[0], $langid);
            $catelik .= '<a href="?app=ctgPage&act=index&storeid=' . $storeid . '&lang=' . $langid . '&cid=' . $pidpid['id'] . '">' . $pidpid['cname'] . '</a>';
            $pid = $this->getCtgDetail($pathArr[1], $langid);
            $catelik .= '<i>/</i>';
            $catelik .= '<span class="or">' . $pid['cname'] . '</span>';
        }
        return $catelik;
    }

    /**
     * 获取分类下的商品
     * @author wangh
     * @date 2017/09/13
     */
    public function getCtgGoods($pcid, $storeid, $lang, $page) {
        $storeGoodsMod = &m('areaGood');
        //添加分页类
        include(ROOT_PATH . '/data/page/pageClass.php');
        $url = 'index.php?app=ctgPage&act=index&storeid=' . $storeid . '&lang=' . $lang . '&cid=' . $pcid;  //
        $pagesize = $this->pagesize; //每页显示条数
        $curpage = $page;  //当前页数
        $limit = '  limit ' . ($curpage - 1 ) * $pagesize . ',' . $pagesize;
        //所以子类
        $data = array();
        $res = $this->getctgChilds($pcid, $lang);
        foreach ($res as $val) {
            $data[] = $val['id'];
            $childs = $this->getctgChilds($val['id'], $lang);
            if (!empty($childs)) {
                foreach ($childs as $v) {
                    $data[] = $v['id'];
                }
            }
        }
        //所以子类
        $ids = implode(',', $data);
        $where = '  where   s.store_id =' . $storeid . '  and   s.cat_id  in(' . $ids . ')   and   s.mark=1   and   s.is_on_sale =1';
        //统计条数
        $sqltotal = 'SELECT  COUNT(id)  as total   FROM  ' . DB_PREFIX . 'store_goods  ' . $where;
        $res = $storeGoodsMod->querySql($sqltotal);
        $total = $res[0]['total'];  //总条数
        //实例化分页类
        $pageClass = new page2($total, $pagesize, $curpage, $url, 2);
        // 输出分页html
        $pagelink = $pageClass->myde_write();
        //所以子类的商品
        $where .= '  AND  l.`lang_id` = ' . $this->langid;
        $sql = 'SELECT s.id,s.`goods_id`,s.`cat_id`,s.`store_id`,l.`goods_name`,l.`lang_id`,s.`shop_price`,s.`market_price`,s.`brand_id`,gl.`original_img`,s.is_free_shipping
                FROM  ' . DB_PREFIX . 'store_goods AS s LEFT JOIN  ' . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id` LEFT JOIN  ' . DB_PREFIX . 'goods AS gl ON s.`goods_id` = gl.`goods_id` ' . $where;
        $sql .= '  order by  s.goods_salenum  desc  ' . $limit;
        $arr = $storeGoodsMod->querySql($sql);

        $shorthand = $this->shorthand;
        foreach ($arr as &$item) {
//            print_r($item['shop_price']);exit;
            $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $this->storeid;
            $store_arr = $storeGoodsMod->querySql($store_sql);
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
            //            收藏商品
            $userId = $this->userId;
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

}
