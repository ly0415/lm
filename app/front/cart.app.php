<?php

/**
 * 购物车
 * @author wanyan
 * @date 2017-09-19
 */
class CartApp extends BaseFrontApp {

    private $storeGoodsMod;
    private $cartMod;
    private $storeMod;
    private $fxUserMod;

    public function __construct() {
        parent::__construct();
        $this->storeGoodsMod = &m('areaGood');
        $this->cartMod = &m('cart');
        $this->storeMod = &m('store');
        $this->fxUserMod = &m('fxuser');
        $this->assign('langdata', $this->langData);
    }

    /**
     * 购物车首页
     * @author wanyan
     * @date 2017-09-19
     */
    public function index() {
        $this->load($this->shorthand, 'cart/cart');

        if (empty($this->userId)) {
            $referer = $_SERVER['HTTP_REFERER']; // 并不能真实获取上一级的页面。
            //$request_uri = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/bspm711/index.php?app=goods&act=goodInfo&storeid={$_REQUEST['store_id']}&lang={$_REQUEST['langId']}&gid={$_REQUEST['store_goods_id']}";
            // var_dump($url);die;
            $url = "index.php?app=user&act=login&pageUrl=" . urlencode($referer);
            header("Location:$url");
        }
        $sql = "select * from " . DB_PREFIX . "cart where `user_id` = '{$this->userId}' and `store_id` = $this->storeid";
        $rs = $this->cartMod->querySql($sql);
        // 统计购车商品数量
        // 如果有规格，就去当前商品的规格图片，没有去主商品图片，是组合商品 组合商品的图片
        foreach ($rs as $k => $v) {
            $invalid=$this->cartMod->isInvalid($v['goods_id'],$v['spec_key']);
            $rs[$k]['invalid']=0;
            if(empty($invalid)){
                $rs[$k]['invalid']=1;
            }else{
                if($invalid<$v['goods_num']){
                    $rs[$k]['invalid']=1;
                }
            }
            $rs[$k]['original_img'] = $this->getGoodImg($v['goods_id']);
            $rs[$k]['store_name'] = $this->getStoreName($v['store_id']);
            $rs[$k]['short'] = $this->short;
            $rs[$k]['totalMoney'] = number_format(round(($v['goods_price'] * $v['goods_num']), 2), 2);
            if ($v['spec_key']) {
                $info = explode('_', $v['spec_key']);
                foreach ($info as $k1 => $v1) {
                    $sql = "select `item_name` from " . DB_PREFIX . "goods_spec_item_lang where `item_id` = '{$v1}' and  `lang_id` = $this->langid";
                    $spec_1 = $this->cartMod->querySql($sql);
                    $spec[] = $spec_1[0]['item_name'];
                }
                $spec_key = implode(':', $spec);
                $rs[$k]['spec_key_name'] = $spec_key;
                $spec = array();
            }
            $rs[$k]['shipping_store'] = $this->getStoreName($v['shipping_store_id']);
            $rs[$k]['goods_name'] = $this->cartMod->getGoodNameById($v['goods_id'], $this->langid);
        }
//        echo '<pre>';
//        var_dump($rs);die;
        $this->assign('cartInfo', $rs);
        $this->assign('store_id', $this->storeid);
        $this->assign('lang', $this->langid);
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang_id);
        //  $this->load($this->shorthand, 'cart/cart');
        $this->assign('langdata', $this->langData);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $this->display('cart/mycart.html');
    }

    /**
     * 获取当前商品图片
     * @author wanyan
     * @date 2017-09-21
     */
    public function getGoodImg($goods_id) {
        $sql = 'select gl.original_img  from  '
                . DB_PREFIX . 'store_goods as g  left join '
                . DB_PREFIX . 'goods as gl on g.goods_id = gl.goods_id where g.id  = ' . $goods_id;
        $rs = $this->storeMod->querySql($sql);
        return $rs[0]['original_img'];
    }

    /**
     * 获取店铺名称
     * @author wanyan
     * @date 2017-09-21
     */
    public function getStoreName($store_id) {
//        $query = array(
//            'cond' => "`id` = '{$store_id}'",
//            'fields' => "`store_name`"
//        );
//        $rs = $this->storeMod->getOne($query);
        $sql = 'SELECT  l.store_name  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE  l.distinguish=0 and l.lang_id =' . $this->langid . '  and  c.id=' . $store_id;
        $res = $this->storeMod->querySql($sql);
        return $res[0]['store_name'];
    }

    /**
     * 报错页面
     * @author wanyan
     * @date 2017-11-27
     */
    public function error() {
        $this->display('cart/error.html');
    }

    /**
     * 加入购物车
     * @author wanyan
     * @date 2017-09-19
     */
    public function doAddCart() {
        $this->load($this->shorthand, 'cart/cart');
        $a = $this->langData;
        $referer_1 = $_SERVER['HTTP_REFERER'];
        $returnUrl=!empty($_REQUEST['returnUrl']) ? urlencode($_REQUEST['returnUrl']) : '';

        if (empty($this->userId)) {
            $referer = $_SERVER['HTTP_REFERER']; // 并不能真实获取上一级的页面。
            //$request_uri = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/bspm711/index.php?app=goods&act=goodInfo&storeid={$_REQUEST['store_id']}&lang={$_REQUEST['langId']}&gid={$_REQUEST['store_goods_id']}";
            // var_dump($url);die;
            $info['url'] = "index.php?app=user&act=login&pageUrl=" . urlencode($referer)."&returnUrl={$returnUrl}";
            $this->setData($info, $status = 1, $a['pleaselogin']);
        }
        $store_goods_id = !empty($_REQUEST['store_goods_id']) ? htmlspecialchars($_REQUEST['store_goods_id']) : '0';
        $item_id = !empty($_REQUEST['item_id']) ? htmlspecialchars($_REQUEST['item_id']) : '';
        $goods_num = !empty($_REQUEST['goods_num']) ? htmlspecialchars($_REQUEST['goods_num']) : '0';
        $prom_id = !empty($_REQUEST['prom_id']) ? htmlspecialchars($_REQUEST['prom_id']) : '0';
        $goods_price = !empty($_REQUEST['goods_price']) ? htmlspecialchars($_REQUEST['goods_price']) : '0';
        $shipping_price = !empty($_REQUEST['shipping_price']) ? htmlspecialchars($_REQUEST['shipping_price']) : '0';
        $order_from = !empty($_REQUEST['order_from']) ? htmlspecialchars($_REQUEST['order_from']) : '0';
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars($_REQUEST['store_id']) : '0';
        $langId = !empty($_REQUEST['langId']) ? htmlspecialchars($_REQUEST['langId']) : '0';
        $fxCode = !empty($_REQUEST['fxCode']) ? htmlspecialchars($_REQUEST['fxCode']) : '';
        $shipping_store_id = !empty($_REQUEST['shipping_store_id']) ? (int) ($_REQUEST['shipping_store_id']) : '';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        if ($fxCode) {
            // 判断当前商品国家和fx人员的所属国家是否相同
            $fxStoreCate = $this->fxUserMod->getOne(array('cond' => "`fx_code` = '{$fxCode}'", 'fields' => "store_cate"));
            $storeCate = $this->storeMod->getOne(array('cond' => "`id` ='{$store_id}'", 'fields' => "store_cate_id"));
            if ($fxStoreCate['store_cate'] != $storeCate['store_cate_id']) {
                $this->setData($info = array(), $status = '0', $a['not_same']);
            }
        }
        $cart_data = array(
            'store_goods_id' => $store_goods_id,
            'item_id' => $item_id,
            'user_id' => $this->userId,
            'prom_id' => $prom_id,
            'store_id' => $store_id,
            'goods_price' => $goods_price,
            'shipping_price' => $shipping_price,
            'order_from' => $order_from,
            'fx_code' => $fxCode,
            'shipping_store_id' => $shipping_store_id,
        );
        $rs = $this->cartMod->addCart($cart_data, $goods_num);
        if ($rs) {
            $info['url'] = "?app=cart&act=index&storeid=$store_id&lang=$langId&auxiliary=$auxiliary";
            $this->setData($info, $status = 1, $a['Shopping_Success']);
        } else {
            $this->setData($info = array(), $status = 0, $a['Shopping_fail']);
        }
    }

    /**
     * 获取该商品所在的店铺id
     * @author wanyan
     * @date 2017-09-19
     */
    public function getStoreId($goods_id) {
        $query = array(
            'cond' => "`id` = '{$goods_id}' and mark ='1'",
            'fields' => "`store_id`"
        );
        $rs = $this->storeGoodsMod->getOne($query);
        return $rs['store_id'];
    }

    /**
     * 改变购物车中指定商品的数量
     * @author wanyan
     * @date 2017-10-17
     */
    public function doChangeNum() {

        $cart_id = !empty($_REQUEST['cart_id']) ? intval($_REQUEST['cart_id']) : '';
        $goods_num = !empty($_REQUEST['goods_num']) ? intval($_REQUEST['goods_num']) : '0';
        $prom_id = !empty($_REQUEST['prom_id']) ? intval($_REQUEST['prom_id']) : '0';
        $store_id = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : '0';
        $a = array(
            'goods_num' => $goods_num
        );
        $rs = $this->cartMod->doEdit($cart_id, $a);
        $query = array(
            'cond' => "`id` = '{$cart_id}'",
            'fields' => 'selected'
        );
        $selected = $this->cartMod->getOne($query);
        if ($rs && ($selected['selected'] == 0)) {
            $data['status'] = 1;
            $sql = "select sum((goods_num * goods_price)) as total from " . DB_PREFIX . "cart where user_id = $this->userId and store_id = $store_id and `selected` =1";
            $res = $this->cartMod->querySql($sql);
            if ($res[0]['total']) {
                $data['total'] = $res[0]['total'];
            } else {
                $data['total'] = '0.00';
            }
            echo json_encode($data);
            exit();
        }
        if ($rs && ($selected['selected'] == 1)) {
            $data['status'] = 1;
            $sql = "select sum((goods_num * goods_price)) as total from " . DB_PREFIX . "cart where user_id = $this->userId and store_id = $store_id and `selected` =1";
            $res = $this->cartMod->querySql($sql);
            $data['total'] = $res[0]['total'];
            echo json_encode($data);
            exit();
        }
    }

    /**
     * 获取某个商品购物车cart_id
     * @author wanyan
     * @date 2017-10-17
     */
    public function getGoodCart($data) {
        $query = array(
            'cond' => "`goods_id`='{$data['goods_id']}' and `user_id` =$this->userId and `store_id` = '{$data['store_id']}' and `spec_key` = '{$data['item_id']}'",
            'field' => "`id`"
        );
        $rs = $this->cartMod->getOne($query);
        return $rs['id'];
    }

    /**
     * 获取该用户购物车id 更新所有的选中状态
     * @author wanyan
     * @date 2017-10-17
     */
    public function changeSelectStatus($selected, $store_id) {
        $query = array(
            'cond' => "`user_id` = $this->userId and `store_id` = $store_id",
            'fields' => "`id`"
        );
        $cart_id = $this->cartMod->getData($query);
        foreach ($cart_id as $k2 => $v2) {
            $cart_ids[] = $v2['id'];
        }
        $cart_ids = implode(',', $cart_ids);
        $this->cartMod->doEdits($cart_ids, array('selected' => $selected));
    }

    /**
     * 获取该用户购物车总金额
     * @author wanyan
     * @date 2017-10-17
     */
    public function getTotalMoney() {
        $flag = !empty($_REQUEST['flag']) ? intval($_REQUEST['flag']) : 0;
        $store_id = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
        if ($flag == 0) {
            $this->changeSelectStatus(0, $store_id);
            $this->setData(array('total' => '0.00'), $status = 0, $message = '');
        }
        $sql = "select sum((goods_num * goods_price)) as total from " . DB_PREFIX . "cart where user_id = $this->userId and store_id = $store_id and `selected` =1";
        $rs = $this->cartMod->querySql($sql);
        $this->changeSelectStatus(1, $store_id);
        $this->setData(array('total' => number_format($rs[0]['total'], 2)), $status = 0, $message = '');
        //return $rs[0]['total'];
    }

    /**
     * 获取该用户选中购物车总金额
     * @author wanyan
     * @date 2017-10-17
     */
    public function getMoney() {
        $selected = array();
        $cart_ids = !empty($_REQUEST['cart_ids']) ? $_REQUEST['cart_ids'] : '';
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->storeid;
        $total = 0;
        if (empty($cart_ids)) {
            $cart_ids = implode(',', $cart_ids);
            $this->cartMod->doEdits($cart_ids, array('selected' => 0));
            $this->setData(array('total' => '0.00'), $status = 0, $message = '');
        }
        foreach ($cart_ids as $k => $v) {
            $sql = "select sum((goods_num * goods_price)) as total from " . DB_PREFIX . "cart where `id` = '{$v}'";
            $rs = $this->cartMod->querySql($sql); // 获取选中的商品总价格
            $total += $rs[0]['total'];
            //$cart_id = $this->getGoodCart(array('goods_id' => $info[0], 'item_id' => $info[1], 'store_id' => $store_id));
            //if($info[2]==0){
            $this->cartMod->doEdit($v, array('selected' => 1));
            //}
            $selected[] = $v;
        }
        $cart_ids = implode(',', $selected);
        $sql_1 = "select id from " . DB_PREFIX . "cart where `id` not in ($cart_ids) ";
        $r = $this->cartMod->querySql($sql_1); // 查到未被选中的值
        if (!empty($r)) {
            foreach ($r as $k2 => $v2) {
                $ids[] = $v2['id'];
            }
            $ids_1 = implode(',', $ids);
        }
        $query = array(
            'selected' => 0
        );
        $this->cartMod->doEdits($ids_1, $query);
        $this->setData(array('total' => number_format($total, 2)), $status = 1, $message = '');
    }

    /**
     * 删除购物车商品方法
     * @author wanyan
     * @date 2017-10-18
     */
    public function dele() {
        $this->load($this->shorthand, 'cart/cart');
        $a = $this->langData;
        $cart_id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '';
        $query = array(
            'cond' => "`id` in ({$cart_id})"
        );
        $rs = $this->cartMod->doDelete($query);
        if ($rs) {
            $this->setData(array(), $status = 1, $a['delete_success']);
        } else {
            $this->setData(array(), $status = 0, $a['delete_fail']);
        }
    }

}
