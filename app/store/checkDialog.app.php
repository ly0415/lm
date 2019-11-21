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

class CheckDialogApp extends BaseStoreApp {

    public $storeGoodsMod;
    private $pagesize = 10;
    private $lang_id;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->storeGoodsMod = &m('areaGood');
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
    public function spikeDialog() {
        //获取第一页数据
        $storeid = $this->storeId;
//        var_dump($storeid);die;
        $where = '  where  sg.is_on_sale =1 and sg.mark=1 and sg.store_id = ' . $storeid;
        $sql = 'select  COUNT(*)  as total  from  ' . DB_PREFIX . 'store_goods  where  is_on_sale =1 and mark=1 and store_id = ' . $storeid;
//        echo $sql;die;
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
        $limit = '  limit  ' . $start . ',' . $end;
        $sql = 'select  sg.id,sg.goods_name,sg.market_price,sg.shop_price,gl.original_img,sg.goods_id  from  '
            . DB_PREFIX . 'store_goods  AS sg LEFT JOIN '
            . DB_PREFIX . 'goods AS gl ON sg.goods_id = gl.goods_id  ' . $where.' AND sg.goods_storage > 0 ' . $limit;
        $data = $this->storeGoodsMod->querySql($sql);
        foreach ($data as $key => $val) {
         $data[$key]['specarr'] = $this->get_spec($val['goods_id'],$val['id'],2);
          $data[$key]['spec']=$this->getSpec($val['goods_id'],$storeid);
        }
//        var_dump($data);die;
        $this->assign('data', $data);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('symbol', $this->symbol);
        $this->display('checkDialog/spikeDialog.html');

    }

    /**
     * 商品的单选弹窗
     */
    public function spikeDialogs() {
        //获取第一页数据

        $storeid = $_REQUEST['store_ids'] ? : 0;
        if($storeid){
            $where = '  where  sg.is_on_sale =1 and sg.mark=1 and sg.store_id in (' . $storeid.')';
            $sql = 'select  COUNT(*)  as total  from  ' . DB_PREFIX . 'store_goods  where  is_on_sale =1 and mark=1 and store_id in ( ' . $storeid .')';
        }else{
            $where = '  where  sg.is_on_sale =1 and sg.mark=1 and sg.store_id = 58';
            $sql = 'select  COUNT(*)  as total  from  ' . DB_PREFIX . 'store_goods  where  is_on_sale =1 and mark=1 and store_id = 58 ';
        }
//        echo $sql;die;
//        var_dump($storeid);die;
//        $where = '  where  sg.is_on_sale =1 and sg.mark=1 and sg.store_id in (' . $storeid.')';
//        $sql = 'select  COUNT(*)  as total  from  ' . DB_PREFIX . 'store_goods  where  is_on_sale =1 and mark=1 and store_id in ( ' . $storeid .')';
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
        $limit = '  limit  ' . $start . ',' . $end;
        $sql = 'select  sg.id,sg.store_id,sg.goods_name,sg.market_price,sg.shop_price,gl.original_img,sg.goods_id  from  '
            . DB_PREFIX . 'store_goods  AS sg LEFT JOIN '
            . DB_PREFIX . 'goods AS gl ON sg.goods_id = gl.goods_id  ' . $where.' AND sg.goods_storage > 0 ' . $limit;
        $data = $this->storeGoodsMod->querySql($sql);
        foreach ($data as $key => $val) {
            $data[$key]['specarr'] = $this->get_spec($val['goods_id'],$val['id'],2);
            $data[$key]['spec']=$this->getSpec($val['goods_id'],$val['store_id']);
        }
//        var_dump($data);die;
        $this->assign('data', $data);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('symbol', $this->symbol);
        $this->display('checkDialog/spikeDialog.html');

    }

    /**
     * 商品的单选弹窗
     */
    public function promDialog() {
        //获取第一页数据
        $storeid = $this->storeId;
        $where = '  where  sg.is_on_sale =1 and sg.mark=1 and sg.store_id = ' . $storeid;
        $sql = 'select  COUNT(*)  as total  from  ' . DB_PREFIX . 'store_goods  where  is_on_sale =1 and mark=1 and store_id = ' . $storeid;
        $res = $this->storeGoodsMod->querySql($sql);
        $total = $res[0]['total'];
        /*  var_dump($total);exit; */
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
        $limit = '  limit  ' . $start . ',' . $end;
        $sql = 'select  sg.id,sg.goods_name,sg.market_price,sg.shop_price,gl.original_img,sg.goods_id  from  '
            . DB_PREFIX . 'store_goods  AS sg LEFT JOIN '
            . DB_PREFIX . 'goods AS gl ON sg.goods_id = gl.goods_id  ' . $where.' AND sg.goods_storage > 0 ' . $limit;
        $data = $this->storeGoodsMod->querySql($sql);
        foreach ($data as $key => $val) {
            $data[$key]['specarr'] = $this->get_spec($val['goods_id'],$val['id'],2);
            $data[$key]['spec']=$this->getSpec($val['goods_id'],$storeid);
        }
        $this->assign('data', $data);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('symbol', $this->symbol);
        $this->display('checkDialog/promDialog.html');
    }

    //获取规格价格
    public function getSpec($store_goods_id, $store_id){
        $storeMod=&m('storeGoods');
        $where=' and  mark=1   and   is_on_sale =1';
        $sql="SELECT id FROM ".DB_PREFIX.'store_goods WHERE store_id='.$store_id.' AND goods_id='.$store_goods_id.$where;
        $data=$storeMod->querySql($sql);
        $id=$data[0]['id'];
        $storeGoodMod = &m("storeGoodItemPrice");
        $spec_data = $storeGoodMod->getData(array('cond' => "store_goods_id=" . $id));
        foreach ($spec_data as $k => $v) {
            $spec_arr[$v['key']] = $v;
        }
        $spec_arr=json_encode($spec_arr);
        return $spec_arr;
    }

    public function getSpecitem($goodsid) {
        $gSpProceMod = &m('storeGoodItemPrice');
        $sql = 'select `id`,`key`,`key_name`,`price`  from  ' . DB_PREFIX . 'store_goods_spec_price  where  store_goods_id =' . $goodsid;
        $res = $gSpProceMod->querySql($sql);
        return $res;
    }
    //获取规格项
    public function get_spec($goods_id, $store_goods_id, $type = 1) {
        $storeGoodMod = &m("storeGoodItemPrice");
        //商品规格 价钱 库存表 找出 所有 规格项id
        //$keys = M('SpecGoodsPrice')->where("goods_id", $goods_id)->getField("GROUP_CONCAT(`key` SEPARATOR '_') ");
        if ($type == 1) {
            $sql = "select `key` as item_key from  " . DB_PREFIX . "goods_spec_price where goods_id=" . $goods_id;
        } else {
            $sql = "select `key` as item_key from  " . DB_PREFIX . "store_goods_spec_price where store_goods_id=" . $store_goods_id;
        }
        $keys = $storeGoodMod->querySql($sql);
        $filter_spec = array();
        if ($keys) {
            $key_str = "";
            foreach ($keys as $k => $v) {
                $key_str .= $v['item_key'] . "_";
            }
            $res_item = substr($key_str, 0, strlen($key_str) - 1);
            $keys = str_replace('_', ',', $res_item);
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
                     WHERE b.id IN($keys) and al.lang_id=" . $this->defaulLang . " and bl.lang_id=" . $this->defaulLang . " ORDER BY b.id";
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
     * 获取商品列表
     */
    public function getSpikeGoodsList() {
        $storeid = $this->storeId;
        $p = $_REQUEST['p'];
        $gname = $_REQUEST['gname'];
        //分页定义
        $currentPage = !empty($p) ? $p : 1;
        $pagesize = $this->pagesize; //每页显示的条数
        $start = ($currentPage - 1) * $pagesize;
        $end = $pagesize;
        $limit = '  limit  ' . $start . ',' . $end;
        $where = '  where  sg.is_on_sale =1 and sg.mark=1 and sg.store_id = ' . $storeid;
        if (!empty($gname)) {
            $where .= '  and  sg.goods_name  like "%' . $gname . '%"';
        }
        $sql = 'select  sg.id,sg.goods_name,sg.market_price,sg.shop_price,gl.original_img,sg.goods_id  from  '
            . DB_PREFIX . 'store_goods  AS sg LEFT JOIN '
            . DB_PREFIX . 'goods AS gl ON sg.goods_id = gl.goods_id  ' . $where. $limit;

        $data = $this->storeGoodsMod->querySql($sql);
        foreach ($data as $key => $val) {
            $data[$key]['specarr'] = $this->get_spec($val['goods_id'],$val['id'],2);
            $data[$key]['spec']=$this->getSpec($val['goods_id'],$storeid);
        }
        $this->assign('data', $data);
        $this->assign('symbol', $this->symbol);
        $this->display('checkDialog/spikeGoodsList.html');
    }


    /**
     * 获取商品列表
     */
    public function getPromGoodsList() {
        $storeid = $this->storeId;
        $p = $_REQUEST['p'];
        $gname = $_REQUEST['gname'];
        //分页定义
        $currentPage = !empty($p) ? $p : 1;
        $pagesize = $this->pagesize; //每页显示的条数
        $start = ($currentPage - 1) * $pagesize;
        $end = $pagesize;
        $limit = '  limit  ' . $start . ',' . $end;
        $where = '  where  sg.is_on_sale =1 and sg.mark=1 and sg.store_id = ' . $storeid;
        if (!empty($gname)) {
            $where .= '  and  sg.goods_name  like "%' . $gname . '%"';
        }
        $sql = 'select  sg.id,sg.goods_name,sg.market_price,sg.shop_price,gl.original_img,sg.goods_id  from  '
            . DB_PREFIX . 'store_goods  AS sg LEFT JOIN '
            . DB_PREFIX . 'goods AS gl ON sg.goods_id = gl.goods_id  ' . $where. $limit;
        $data = $this->storeGoodsMod->querySql($sql);
        foreach ($data as $key => $val) {
            $data[$key]['specarr'] = $this->get_spec($val['goods_id'],$val['id'],2);
            $data[$key]['spec']=$this->getSpec($val['goods_id'],$storeid);
        }
        $this->assign('data', $data);
        $this->assign('symbol', $this->symbol);
        $this->display('checkDialog/promGoodsList.html');
    }

    /**
     * 搜索物品，统计条数
     * @author wangh
     * @date 2017-06-26
     */
    public function totalPage() {
        $storeid = $this->storeId;
        $gname = $_REQUEST['gname'];
        $where = '  where  is_on_sale =1 and mark=1 and store_id = ' . $storeid;
        if (!empty($gname)) {
            $where .= '  and  goods_name like  "%' . $gname . '%"';
        }
        $sql = 'select  COUNT(*)  as total  from  ' . DB_PREFIX . 'store_goods ' . $where;
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

}
