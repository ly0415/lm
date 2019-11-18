<?php

/**
 * 二级分类页面
 * @author wangh
 * @date 2017/08/22
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class CtgPageApp extends BaseWxApp {

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
     * 总站分类页面
     * @author gsb
     * @date   2018-12-17
     */
    public function index() {
        //语言包
        $this->load($this->shorthand, 'WeChat/goods');
        $this->assign('langdata', $this->langData);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $_SESSION['latlon'];
        $this->assign('latlon',$latlon);

        //加载语言包
        $this->load($this->shorthand, 'ctgpage/ctgpage');

        //获取一级分类
        $oneCategory = &m('goodsCategory');
        $ctglev1 = $oneCategory->getOneCategory();

        //获取第一级分类数据
        $threeChilds = $oneCategory->getRelationDatas();

        //获取一级分类第一个ID值
        $c_val = current($threeChilds);
        $c_val = $c_val['id'];
        $this->assign('c_val', $c_val);

        //店铺图片
        $sqls = 'SELECT image_url FROM  ' . DB_PREFIX . 'store WHERE id = '.$this->storeid;
        $datas = $oneCategory->querySql($sqls);
        $this->assign('image', $datas[0]['image_url']);

        $this->assign('storeid',$this->storeid);
        $this->assign('threeChilds', $threeChilds[$c_val]['childs']);
        $this->assign('langdata', $this->langData);
        $this->assign('ctglev1', $ctglev1);
        $this->assign('lang', $lang);
        $this->display('ctgpage/ctgpage.html');
    }

    /**
     * ajax获取二级和三级分类
     * @author gsb
     * @date   2018-12-17
     */
    public function getCateChilds(){
        $cate_id=!empty($_REQUEST['cate_id']) ? intval($_REQUEST['cate_id'] ): 0;
        $lang=!empty($_REQUEST['lang']) ? intval($_REQUEST['lang']) : $this->mrlangid;
        $store_id=!empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
        $auxiliary=!empty($_REQUEST['auxiliary']) ? intval($_REQUEST['auxiliary']) : 0;
        $goodsCategoryMod=&m('goodsCategory');
        $data=$goodsCategoryMod->getRelationDatas();
        $data=$data[$cate_id]['childs'];
        foreach($data as $k=>$v){
            if(empty($data[$k]['childs'])){
                unset($data[$k]);
            }
        }
        $this->assign('storeid',$store_id);
        $this->assign('auxiliary',$auxiliary);
        $this->assign('lang',$lang);
        $this->assign('data',$data);
        $str = self::$smarty->fetch("ctgpage/cateChilds.html");
        $this->setData($str,1,'');
    }

    /**
     * 获取分类下的商品
     * @author wangh
     * @date 2017/09/13
     */
    public function getCtgGoods($pcid, $storeid, $lang, $page) {
        $language = $this->shorthand;
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
                FROM  ' . DB_PREFIX . 'store_goods AS s LEFT JOIN  ' 
                . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id` LEFT JOIN  ' 
                . DB_PREFIX . 'goods AS gl ON s.`goods_id` = gl.`goods_id`' . $where;
        $sql .= '  order by  s.goods_salenum  desc  ' . $limit;

        $arr = $storeGoodsMod->querySql($sql);

        foreach ($arr as &$item) {
            //是否包邮
            if ($language == 'en') {
                switch ($item['is_free_shipping']) {
                    case 1:
                        $item['isfree'] = 'Package mail';  // Package mail
                        break;
                    case 2:
                        $item['isfree'] = "Don't pack mail"; //Don't pack mail
                        break;
                    default:
                        $item['isfree'] = "Don't pack mail";
                }
            } else {
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
            }
            //收藏商品
            $userId = $this->userId;
            $sql_collection = 'select * from ' . DB_PREFIX . 'user_collection where user_id=' . $userId . ' and store_id=' . $storeid;
            $data_collection = $storeGoodsMod->querySql($sql_collection);
            foreach ($data_collection as &$collertion) {
                if ($collertion['good_id'] == $item['id']) {
                    $item['type'] = 1;
                }
            }
            //多语言版本信息
            $gname = $this->getStoreGoodsLang($item['id'], $this->langid);
            if ($gname) {
                $item['goods_name'] = $gname;
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
