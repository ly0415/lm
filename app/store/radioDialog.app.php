<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/26
 * Time: 15:44
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class RadioDialogApp extends BaseStoreApp {

    public $storeGoodsMod;
    private $pagesize = 10;
    private $langid;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->storeGoodsMod = &m('areaGood');
        $this->langid = $this->storeInfo['lang_id'];
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 商品的单选弹窗
     */
    public function goodsDialog() {
        //获取第一页数据
        $lang_id = $_REQUEST['lang_id'];
        $storeid = $this->storeId;
        $where = '  where  l.`lang_id` = ' . $this->langid . '  and  g.is_on_sale =1 and g.mark=1 and g.store_id = ' . $storeid;
        $sql = 'select  COUNT(*)  as total  from  ' . DB_PREFIX . 'store_goods  as g
                LEFT JOIN  bs_goods_lang AS l ON g.`goods_id` = l.`goods_id`  ' . $where;
        $res = $this->storeGoodsMod->querySql($sql);
        $total = $res[0]['total'];
        $pagesize = $this->pagesize;
        $totalpage = ceil($total / $pagesize);
        if (empty($totalpage)) {
            $totalpage = 1;
        }
        $this->assign('totalpage', $totalpage);
        //分页定义
        $currentPage = 1;
        $start = ($currentPage - 1) * $pagesize;
        $end = $pagesize;
        $limit = '    limit  ' . $start . ',' . $end;
        $sql = 'select  g.id,l.goods_name,g.market_price,g.shop_price,gl.original_img,g.goods_id  from  ' . DB_PREFIX . 'store_goods  as g
                LEFT JOIN  bs_goods_lang AS l ON g.`goods_id` = l.`goods_id`  LEFT JOIN  bs_goods AS gl ON g.`goods_id` = gl.`goods_id` ' . $where . $limit;
        $data = $this->storeGoodsMod->querySql($sql);
        foreach ($data as $key => $val) {
            //规格数据
            $data[$key]['specarr'] = $this->getSpecitem($val['id']);
        }
        $this->assign('symbol', $this->symbol);
        $this->assign('data', $data);
        if ($lang_id == 1) {
            $this->display('radioDialog/goodsdialog_1.html');
        } else {
            $this->display('radioDialog/goodsdialog.html');
        }
    }

    public function getSpecitem($goodsid) {
        $gSpProceMod = &m('storeGoodItemPrice');
        $sql = 'select `id`,`key`,`key_name`,`price`,goods_storage  from  ' . DB_PREFIX . 'store_goods_spec_price  where  store_goods_id =' . $goodsid;
        $res = $gSpProceMod->querySql($sql);

        foreach ($res as $key => $val) {
            $res[$key]['key'] = $val['key'];
            $res[$key]['key_name'] = $this->getkeyName(implode(',', explode('_', $val['key'])));
        }

        return $res;
    }

    public function getkeyName($key) {
        $specItemMod = &m('goodsSpecItem');
        $sql = 'SELECT  i.id,l.`item_name`,l.`lang_id`   FROM   bs_goods_spec_item AS i LEFT JOIN   bs_goods_spec_item_lang AS l ON i.id = l.`item_id`
                 WHERE  i.id IN(' . $key . ')  AND  l.`lang_id` = ' . $this->langid;
        $data = $specItemMod->querySql($sql);
        $res = array();
        foreach ($data as $key => $val) {
            $data[$key]['item_name'] = ':' . $val['item_name'];
            $res[] = $data[$key]['item_name'];
        }
        return implode(' ', $res);
    }

    /**
     * 获取商品列表
     */
    public function getGoodsList() {
        $storeid = $this->storeId;
        $p = $_REQUEST['p'];
        $gname = $_REQUEST['gname'];
        //分页定义
        $currentPage = !empty($p) ? $p : 1;
        $pagesize = $this->pagesize; //每页显示的条数
        $start = ($currentPage - 1) * $pagesize;
        $end = $pagesize;
        $limit = '  limit  ' . $start . ',' . $end;
        $where = '  where   l.`lang_id` = ' . $this->langid . ' and  g.is_on_sale =1 and g.mark=1 and g.store_id = ' . $storeid;
        if (!empty($gname)) {
            $where .= '  and  l.goods_name  like "%' . $gname . '%"';
        }
        $sql = 'select  g.id,l.goods_name,g.market_price,g.shop_price,gl.original_img,g.goods_id  from   ' . DB_PREFIX . 'store_goods  as g
                 LEFT JOIN  bs_goods_lang AS l ON g.`goods_id` = l.`goods_id`  LEFT JOIN  bs_goods AS gl ON g.`goods_id` = gl.`goods_id` ' . $where.$limit ;
        $data = $this->storeGoodsMod->querySql($sql);

        foreach ($data as $key => $val) {
            //规格数据
            $data[$key]['specarr'] = $this->getSpecitem($val['id']);
        }
        $this->assign('data', $data);
        $this->assign('symbol', $this->symbol);
        $this->display('radioDialog/goodslist.html');
    }

    /**
     * 搜索物品，统计条数
     * @author wangh
     * @date 2017-06-26
     */
    public function totalPage() {
        $storeid = $this->storeId;
        $gname = $_REQUEST['gname'];
        $where = '  where  g.is_on_sale =1 and g.mark=1 and g.store_id = ' . $storeid .' AND l.lang_id = '.$this->defaulLang;
        if (!empty($gname)) {
            $where .= '  and  g.goods_name like  "%' . $gname . '%"';
        }
        $sql = 'select  COUNT(*)  as total  from  ' . DB_PREFIX . 'store_goods  as g
                LEFT JOIN  bs_goods_lang AS l ON g.`goods_id` = l.`goods_id` ' . $where;
        $res = $this->storeGoodsMod->querySql($sql);
        $total = $res[0]['total'];
        $pagesize = $this->pagesize;
        $totalpage = ceil($total / $pagesize);

        if (!empty($totalpage)) {
            echo json_encode(array('total' => $totalpage));
            exit;
        } else {
            echo json_encode(array('total' => 1));
            exit;
        }
    }

    /**
     * 获取商品的多语言信息
     * @param $goodsId
     * @param $langid
     * @return mixed
     */
//    public function getStoreGoodsLang($goodsId, $langid) {
//        $storeGLMod = &m('storeGoodsLang');
//        $sql = 'SELECT  goods_name,goods_content  FROM  ' . DB_PREFIX . 'store_goods_lang   WHERE  store_good_id = ' . $goodsId . ' AND   lang_id =' . $langid;
//        $res = $storeGLMod->querySql($sql);
//        return $res[0];
//    }
}
