<?php

/**
 * 商家后台
 * @author lee
 * @date 2017/07/19
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class goodApp extends BaseStoreApp {

    private $areaGoodMod;
    private $goodsSpecPriceMod;
    private $goodClassMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->areaGoodMod = &m('areaGood');
        $this->goodsSpecPriceMod = &m('goodsSpecPrice');
        $this->goodClassMod = &m('goodsClass');
    }

    /*
     * 商品列表
     * @author lee
     * @date 2017-8-1 15:08:15
     */

    public function goodList() {
        $is_on_sale = !empty($_REQUEST['is_sale']) ? htmlspecialchars($_REQUEST['is_sale']) : '0';
        $goods_name = !empty($_REQUEST['goods_name']) ? htmlspecialchars(addslashes($_REQUEST['goods_name'])) : '';
        $where = " where `store_id` ='{$this->storeId}' and `mark` =1";
        if (!empty($is_on_sale)) {
            $where .="  and `is_on_sale` = '{$is_on_sale}'";
        }
        if (!empty($goods_name)) {
            $where .=" and `goods_name` like '%" . $goods_name . "%'";
        }
        $where .= "  order by id desc ";
        $sql = "select * from " . DB_PREFIX . "store_goods " . $where;
        $data = $this->areaGoodMod->querySqlPageData($sql);
        foreach ($data['list'] as $k => $v) {
            $res = $this->goodClassMod->getOne(array('cond' => 'id=' . $v['cat_id']));
            $data['list'][$k]['cat_name'] = $res['name'];
        }
        $this->assign('is_on_sale', $is_on_sale);
        $this->assign('goods_name', $goods_name);
        $this->assign('list', $data['list']);
        $this->assign('ph', $data['ph']);
        $this->display("goods/index.html");
    }

    /*
     * 是否推荐
     * @author wanyan
     * @date 2017-09-14
     */

    public function changeRecommend() {
        $id = $_REQUEST['id'];
        $is_recommend = $_REQUEST['is_recommend'];
        $data = array(
            'is_recommend' => $is_recommend
        );
        $rs = $this->areaGoodMod->doEdit($id, $data);
        if ($rs) {
            $this->setData($info = array(), $status = 1, $message = '');
        } else {
            $this->setData($info = array(), $status = 0, $message = '推荐失败！');
        }
    }

    /*
     * 是否推荐
     * @author wanyan
     * @date 2017-09-14
     */

    public function changeSales() {
        $id = $_REQUEST['id'];
        $is_on_sale = $_REQUEST['is_on_sale'];
        $data = array(
            'is_on_sale' => $is_on_sale
        );
        $rs = $this->areaGoodMod->doEdit($id, $data);
        if ($rs) {
            $this->setData($info = array(), $status = 1, $message = '');
        } else {
            $this->setData($info = array(), $status = 0, $message = '推荐失败！');
        }
    }

    /*
     * 是否推荐
     * @author wanyan
     * @date 2017-09-14
     */

    public function changeFree() {
        $id = $_REQUEST['id'];
        $is_free_shipping = $_REQUEST['is_free_shipping'];
        $data = array(
            'is_free_shipping' => $is_free_shipping
        );
        $rs = $this->areaGoodMod->doEdit($id, $data);
        if ($rs) {
            $this->setData($info = array(), $status = 1, $message = '');
        } else {
            $this->setData($info = array(), $status = 0, $message = '推荐失败！');
        }
    }

    /*
     * 是否推荐
     * @author wanyan
     * @date 2017-09-14
     */

    public function editGood() {
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '';
        $storeSpecMod=&m('storeGoodItemPrice');
        $rs = $this->areaGoodMod->getOne(array('cond' => "`id`='{$id}'"));
        $list=$storeSpecMod->getData(array("cond"=>"store_goods_id=".$id));
        if($list){
            $this->assign('hasList', 1);
            $this->assign('specList', $list);
        }
        $this->assign('goodInfo', $rs);
        $this->display('goods/edit.html');
    }

    /*
     * 是否推荐
     * @author wanyan
     * @date 2017-09-14
     */

    public function doSPrice() {
        $id = $_REQUEST['id'];
        $shop_price = $_REQUEST['shop_price'];
        $goods_storage = $_REQUEST['goods_storage'];
        $data = array(
            'shop_price' => $shop_price,
            'goods_storage' => $goods_storage
        );
        $rs = $this->areaGoodMod->doEdit($id, $data);
        if ($rs) {
            $this->setData($info = array(), $status = '1', $message = '修改成功！');
        } else {
            $this->setData($info = array(), $status = '0', $message = '修改失败！');
        }
    }
    /*
     * 删除商品
     * @author wanyan
     * @date 2017-09-14
     */
    public function dele(){
        $id = !empty($_REQUEST['id']) ? htmlspecialchars($_REQUEST['id']) : '';
        $query=array(
            'cond' =>" `id` ='{$id}'"
        );
        $rs = $this->areaGoodMod->doDelete($query);
        if($rs){
            //删除对应规格区域商品规格
            //modify by lee @2017-9-19 10:01:18
            $sql="delete from ".DB_PREFIX."store_goods_spec_price where store_goods_id=".$id;
            $this->areaGoodMod->sql_b_spec($sql);
            //end
            $this->setData($info = array(), $status = '1', $message = '删除成功！');
        }else{
            $this->setData($info = array(), $status = '0', $message = '删除失败！');
        }
    }

}

?>