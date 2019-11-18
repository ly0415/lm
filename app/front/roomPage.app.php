<?php

/**
 * 业务二级页面
 * @author wangh
 * @date 2017/08/22
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class RoomPageApp extends BaseFrontApp {

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
     * 业务二级页面
     * @author wangh
     * @date 2017/08/22
     */
    public function index() {
        //接受数据
        $rtid = $_REQUEST['rtid'];
        $page = !empty($_REQUEST['p']) ? $_REQUEST['p'] : 1;  //当前第几页
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        //业务详情
        $roomDetail = $this->getRoomDetail($rtid, $this->langid);
        //业务下的分类
        $roomCtg = $this->getRoomCtg($rtid, $this->langid);
        $cidArr = array();
        foreach ($roomCtg as $val) {
            $cidArr[] = $val['cid'];
        }
        $cids = implode(',', $cidArr);
        //分类下的商品
        $ctgGoods = $this->getCtgGoods($cids, $rtid, $this->storeid, $this->langid, $page);
        // 业务分类商品评价星级
        $this->goodsCommentMod = &m('goodsComment');
        foreach ($ctgGoods['data'] as $k => $v) {
            $good_id = $v['id'];
            $sql = 'select  sum(goods_rank) / count(goods_rank) as res,count(*) as num from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $good_id;
            $trance = $this->goodsCommentMod->querySql($sql);
            $ctgGoods['data'][$k]['rate'] = $trance[0]['res'];
            $ctgGoods['data'][$k]['num'] = $trance[0]['num'];
        }

        //加载语言包
        $this->load($this->shorthand, 'roompage/roompage');
        $this->assign('langdata', $this->langData);
        $this->assign('roomDetail', $roomDetail);
        $this->assign('roomCtg', $roomCtg);
        $this->assign('user_id', $this->userId);
        $this->assign('store_id', $this->storeid);
        $this->assign('ctgGoods', $ctgGoods);
        $this->display('roompage/roompage.html');
    }

    public function getRoomDetail($rtid, $langid) {
        $roomMod = &m('roomType');
        if (!empty($langid)) {
            $where = '    where  l.`lang_id`  = ' . $langid;
        } else {
            $where = '    where  l.`lang_id`  = ' . $this->mrlangid;
        }
        $sql = 'SELECT  t.`id`,l.`type_name` ,t.`room_img`,t.`room_adv_img` as advimg ,t.`adv_url` as advurl   FROM  ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN   ' . DB_PREFIX . 'room_type_lang AS l  ON t.`id` = l.`type_id`  ' . $where . '  AND  t.id= ' . $rtid;
        $data = $roomMod->querySql($sql);
        return $data[0];
    }

    public function getRoomCtg($rtid, $langid) {
        $roomCtgMod = &m('roomTypeCate');
        if (!empty($langid)) {
            $where = '    where  cl.`lang_id`  = ' . $langid;
        } else {
            $where = '    where  cl.`lang_id`  = ' . $this->mrlangid;
        }
        $sql = 'SELECT  rc.`room_type_id`  as rtid ,rc.`category_id`  as cid  ,cl.`category_name`  as cname,c.`image`   FROM   ' . DB_PREFIX . 'room_category  AS rc
              LEFT JOIN  ' . DB_PREFIX . 'room_type as t ON  rc.`room_type_id` =t.`id`
              LEFT  JOIN   ' . DB_PREFIX . 'goods_category_lang   AS cl   ON rc.`category_id`  = cl.`category_id`
              LEFT JOIN  ' . DB_PREFIX . 'goods_category  AS c ON c.`id` = rc.`category_id`  ' . $where . '  AND  rc.`room_type_id` = ' . $rtid . ' order by rc.sort';
        $data = $roomCtgMod->querySql($sql);
        return $data;
    }

    /**
     * 获取分类下的商品
     * @author wangh
     * @date 2017/09/13
     */
    public function getCtgGoods($cids, $rtid, $storeid, $lang, $page) {
        $shorthand = $this->shorthand;
        $storeGoodsMod = &m('areaGood');
        //添加分页类
        include(ROOT_PATH . '/data/page/pageClass.php');
        $url = 'index.php?app=roomPage&act=index&storeid=' . $storeid . '&lang=' . $lang . '&rtid=' . $rtid;  //
        $pagesize = $this->pagesize; //每页显示条数
        $curpage = $page;  //当前页数
        $limit = '  limit ' . ($curpage - 1 ) * $pagesize . ',' . $pagesize;
        //所以子类
        $where = '  where   s.store_id =' . $storeid . '  and   s.cat_id  in(' . $cids . ')   and   s.mark=1   and   s.is_on_sale =1';
        //统计条数
        $sqltotal = 'SELECT  COUNT(id)  as total   FROM  ' . DB_PREFIX . 'store_goods  ' . $where;
        $res = $storeGoodsMod->querySql($sqltotal);
        $total = $res[0]['total'];  //总条数
        //实例化分页类
        $pageClass = new page2($total, $pagesize, $curpage, $url, 2);
        // 输出分页html
        $pagelink = $pageClass->myde_write();
        //所以子类的商品
        $where .= '  AND l.`lang_id` = ' . $this->langid;
        $sql = 'SELECT s.id,s.`goods_id`,s.`cat_id`,s.`store_id`,l.`goods_name`,l.`lang_id`,s.`shop_price`,s.`market_price`,s.`brand_id`,gl.`original_img`,s.is_free_shipping
                FROM  ' . DB_PREFIX . 'store_goods AS s LEFT JOIN  ' . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id` LEFT JOIN  ' . DB_PREFIX . 'goods AS gl ON s.`goods_id` = gl.`goods_id` ' . $where;
        $sql .= '  order by  s.goods_salenum  desc  ' . $limit;
        $arr = $storeGoodsMod->querySql($sql);
        //
        foreach ($arr as &$item) {
            //店铺商品打折
            $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $this->storeid;
            $store_arr = $storeGoodsMod->querySql($store_sql);
            $item['shop_price'] = number_format($item['shop_price'] * $store_arr[0]['store_discount'],2);
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

            //收藏商品
            $sql_collection = 'select * from ' . DB_PREFIX . 'user_collection where user_id=' . $this->userId . ' and store_id=' . $this->storeid;
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
