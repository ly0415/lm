<?php

/**
 * 购物车
 * @author wanyan
 * @date 2017-09-19
 */

class CartApp extends BaseWXApp {

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
        //授权后的登录检验
        $this->ischeckLogin();

        //判断是否登录
        if (!isset($_SESSION['userId'])) {
            header('Location: wx.php?app=user&act=loginOuth&back_app=' . APP . '&back_act=' . ACT . '&storeid=' . $this->storeid . '&lang=' . $this->langid);
        }

        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        //获取语言包
//        $this->langData = languageFun($this->shorthand);

        //删除所有失效商品（此处不友好）
        $sql = "select * from " . DB_PREFIX . "cart where `user_id` = '{$this->userId}'";

        $rsSql = $this->cartMod->querySql($sql);
        foreach ($rsSql as $k1 => $v1) {
            $status = $this->checkOnSale($v1['goods_id']);
            if ($status == 2) {
                $this->cartMod->doDelete(array('cond' => "`goods_id`='{$v1['goods_id']}'"));
            }
            $mark = $this->checkDelete($v1['goods_id']);
            if ($mark == 0) {
                $this->cartMod->doDelete(array('cond' => "`goods_id` in ({$v1['goods_id']})"));
            }
        }

        $sql = "select * from " . DB_PREFIX . "cart where `user_id` = '{$this->userId}'";
        $rs = $this->cartMod->querySql($sql);


        // 统计购车商品数量
        // 如果有规格，就去当前商品的规格图片，没有去主商品图片，是组合商品 组合商品的图片
        $result = array();

        foreach ($rs as $k => $v) {
            $store_info_1   = $this->getStoreName($v['store_id']);
            $buss_info=$this->getBussInfo($v['store_id']);
            $result[$v['store_id']]['store_name'] = $store_info_1['store_name'];
            $result[$v['store_id']]['logo']       = $store_info_1['logo'];
            $result[$v['store_id']]['buss_id']=$buss_info;
            $result[$v['store_id']]['store_id']=$v['store_id'];
            $result[$v['store_id']]['child'][$k]  = $v;
            $invalid=$this->cartMod->isInvalid($v['goods_id'],$v['spec_key']);
            $result[$v['store_id']]['child'][$k]['invalid'] = 0;
            if(empty($invalid)){
                $result[$v['store_id']]['child'][$k]['invalid']=1;
            }else{
                if($invalid<$v['goods_num']){
                    $result[$v['store_id']]['child'][$k]['invalid']=1;
                }
            }
            $result[$v['store_id']]['child'][$k]['original_img'] = $this->getGoodImg($v['goods_id']);
            $result[$v['store_id']]['child'][$k]['totalMoney']   = round(($v['goods_price'] * $v['goods_num']), 2);
            if ($v['spec_key']) {
                $info = explode('_', $v['spec_key']);
                foreach ($info as $k1 => $v1) {
                    $sql = "select `item_name` from " . DB_PREFIX . "goods_spec_item_lang where `item_id` = '{$v1}' and  `lang_id` = $this->langid";
                    $spec_1 = $this->cartMod->querySql($sql);
                    $spec[] = $spec_1[0]['item_name'];
                }
                $spec_key = implode(':', $spec);
                $result[$v['store_id']]['child'][$k]['spec_key_name'] = $spec_key;
                $spec = array();
            }
            $store_info_2 = $this->getStoreName($v['shipping_store_id']);
            $result[$v['store_id']]['child'][$k]['shipping_store'] = $store_info_2['store_name'];
            $result[$v['store_id']]['child'][$k]['goods_name']     = $this->cartMod->getGoodNameById($v['goods_id'], $this->langid);
        }
        $this->assign('cartInfo', $result);
        $this->display('cart/mycart.html');
    }

       public function getBussInfo($store_id){
           $busSql = "SELECT buss_id FROM " . DB_PREFIX . 'store_business WHERE  store_id=' .$store_id;
           $busData = $this->storeMod->querySql($busSql);
           return  $busData[0]['buss_id'];
       }


    /**
     * 获取当前商品是否下架
     * @author wanyan
     * @date 2017-11-09
     */
    public function checkOnSale($goods_id) {
        $query = array(
            'cond' => "`id`='{$goods_id}'",
            'fields' => "is_on_sale"
        );
        $rs = $this->storeGoodsMod->getOne($query);
        return $rs['is_on_sale'];
    }

    /**
     * 获取当前商品是否删除
     * @author wanyan
     * @date 2017-11-09
     */
    public function checkDelete($goods_id) {
        $query = array(
            'cond' => "`id`='{$goods_id}'",
            'fields' => "mark"
        );
        $rs = $this->storeGoodsMod->getOne($query);
        return $rs['mark'];
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
        $sql = 'SELECT  c.logo,l.store_name  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE l.distinguish=0 and  l.lang_id =' . $this->langid . '  and  c.id=' . $store_id;
        $res = $this->storeMod->querySql($sql);
        return $res[0];
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
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        if (empty($this->userId)) {
            $referer = $_SERVER['HTTP_REFERER']; // 并不能真实获取上一级的页面。
            //$request_uri = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/bspm711/index.php?app=goods&act=goodInfo&storeid={$_REQUEST['store_id']}&lang={$_REQUEST['langId']}&gid={$_REQUEST['store_goods_id']}";
            // var_dump($url);die;
            $info['url'] = "wx.php?app=user&act=login&storeid=" . $this->storeid . "&lang=" . $this->langid . "&pageUrl=" . urlencode($referer).'&latlon='.$latlon;
            $this->setData($info, $status = 0, $a['pleaselogin']);
            }
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $store_goods_id = !empty($_REQUEST['store_goods_id']) ? htmlspecialchars($_REQUEST['store_goods_id']) : '0';
        $item_id = !empty($_REQUEST['item_id']) ? htmlspecialchars($_REQUEST['item_id']) : '';
        $goods_num = !empty($_REQUEST['goods_num']) ? htmlspecialchars($_REQUEST['goods_num']) : '0';
        $prom_id = !empty($_REQUEST['prom_id']) ? htmlspecialchars($_REQUEST['prom_id']) : '0';
        $goods_price = !empty($_REQUEST['goods_price']) ? $_REQUEST['goods_price'] : '0';
        $shipping_price = !empty($_REQUEST['shipping_price']) ? htmlspecialchars($_REQUEST['shipping_price']) : '0';
        $order_from = !empty($_REQUEST['order_from']) ? htmlspecialchars($_REQUEST['order_from']) : '0';
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars($_REQUEST['store_id']) : '0';
        $langId = !empty($_REQUEST['langId']) ? htmlspecialchars($_REQUEST['langId']) : '0';
        $fxCode = !empty($_REQUEST['fxCode']) ? htmlspecialchars($_REQUEST['fxCode']) : '';
        $shipping_store_id = !empty($_REQUEST['shipping_store_id']) ? (int) ($_REQUEST['shipping_store_id']) : '';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : 0 ; //1 代表直接购买

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


        $rs = $this->cartMod->addCart($cart_data, $goods_num,$type);

        if ($rs) {
            if($type==1){
                $info['url'] = "?app=orderList&act=index&store_id={$store_id}&lang={$langId}&auxiliary=0&item_id={$rs}&latlon=0";
                $this->setData($info, $status = 1, '购买成功');
            }else{
                $info['url'] = "?app=cart&act=index&storeid=$shipping_store_id&lang=$langId&auxiliary=$auxiliary&latlon=$latlon";
                $this->setData($info, $status = 1, $a['Shopping_Success']);
            }
        } else {
            $this->setData($info = array(), $status = 0, $a['Shopping_fail']);
        }
    }



    public function doCart() {
        $this->load($this->shorthand, 'cart/cart');
        $a = $this->langData;
        $referer_1 = $_SERVER['HTTP_REFERER'];
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        if (empty($this->userId)) {
            $referer = $_SERVER['HTTP_REFERER']; // 并不能真实获取上一级的页面。
            //$request_uri = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/bspm711/index.php?app=goods&act=goodInfo&storeid={$_REQUEST['store_id']}&lang={$_REQUEST['langId']}&gid={$_REQUEST['store_goods_id']}";
            // var_dump($url);die;
            $info['url'] = "wx.php?app=user&act=quickLogin&storeid=" . $this->storeid . "&lang=" . $this->langid . "&returnUrl=" . urlencode($referer).'&latlon='.$latlon;
            $this->setData($info, $status = 0, $a['pleaselogin']);
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
            $info['url'] = "?app=cart&act=index&storeid=$shipping_store_id&lang=$langId&auxiliary=$auxiliary&latlon=$latlon";
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
       /* $store_id = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : '0';*/
        $cartMod = &m('cart');
        $goodsInfo = $cartMod->getGoodByCartId($cart_id);
        //商品库存判断
        foreach($goodsInfo as $k=>$v){
            $invalid=$cartMod->isInvalid($v['goods_id'],$v['spec_key']);
            if($invalid<$goods_num){
                    $this->setData(array(),'0',$v['goods_name'].'商品库存不足');
            }
        }
        $a = array(
            'goods_num' => $goods_num
        );
        $rs = $this->cartMod->doEdit($cart_id, $a);
        $query = array(
            'cond' => "`id` = '{$cart_id}'",
            'fields' => 'selected,store_id'
        );
        $selected = $this->cartMod->getOne($query);
        if ($rs && ($selected['selected'] == 0)) {
            $data['status'] = 1;
            $sql = "select sum((goods_num * goods_price)) as total from " . DB_PREFIX . "cart where user_id = $this->userId and store_id = {$selected['store_id']} and `selected` =1";
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
            $sql = "select sum((goods_num * goods_price)) as total from " . DB_PREFIX . "cart where user_id = $this->userId and store_id = {$selected['store_id']} and `selected` =1";
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

    /**
     * 编辑购物车商品方法
     * @author wanyan
     * @date 2018-01-11
     */
    public function editMycart() {
        $this->load($this->shorthand, 'cart/cart');
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : '';
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : '';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $sql = "select * from " . DB_PREFIX . "cart where `user_id` = '{$this->userId}' and `shipping_store_id` = $storeid";
        $rs = $this->cartMod->querySql($sql);
        // 统计购车商品数量
        // 如果有规格，就去当前商品的规格图片，没有去主商品图片，是组合商品 组合商品的图片
        foreach ($rs as $k => $v) {
            $rs[$k]['original_img'] = $this->getGoodImg($v['goods_id'], $v['store_id']);
            $rs[$k]['store_name'] = $this->getStoreName($v['store_id']);
            $rs[$k]['short'] = $this->short;
            $rs[$k]['totalMoney'] = round(($v['goods_price'] * $v['goods_num']), 2);
            if ($v['spec_key']) {
                $info = explode('_', $v['spec_key']);
                foreach ($info as $k1 => $v1) {
                    $sql = "select `item_name` from " . DB_PREFIX . "goods_spec_item_lang where `item_id` = '{$v1}' and  `lang_id` = $lang";
                    $spec_1 = $this->cartMod->querySql($sql);
                    $spec[] = $spec_1[0]['item_name'];
                }
                $spec_key = implode(':', $spec);
                $rs[$k]['spec_key_name'] = $spec_key;
                $spec = array();
            }
            $rs[$k]['shipping_store'] = $this->getStoreName($v['shipping_store_id']);
            $rs[$k]['goods_name'] = $this->cartMod->getGoodNameById($v['goods_id'], $lang);
        }
        $this->assign('cartInfo', $rs);
        $this->assign('storeid', $storeid);
        $this->assign('lang', $lang);
        $language = $this->shorthand;
        $this->assign('language', $language);
        $this->assign('langdata', $this->langData);
        $this->display('cart/editMyCart.html');
    }


    /**
     * 校验购物车商品信息
     * @author gao
     * @date 2017-10-18
     */
    public function checkCartData() {
        $cart_ids = !empty($_REQUEST['cart_ids']) ? $_REQUEST['cart_ids'] : '';
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : 0;
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : 0;
        $cartMod = &m('cart');
        $goodsInfo = $cartMod->getGoodByCartId($cart_ids);
        foreach($goodsInfo as $k=>$v){
            $invalid=$this->checkGoodsData($v['goods_id'],$v['spec_key'],$v['goods_price'],$v['store_id']);
            if($invalid){
            }else{
                $this->setData(array(), $status = 0, $v['goods_name']."商品已失效，请重新购买");
            }
        }
        $info['url'] = "?app=orderList&act=index&store_id=" . $store_id.'&lang=' . $lang_id . '&auxiliary=0&item_id=' .$cart_ids .'&latlon=0';
        $this->setData($info, $status = 1, '');
    }
    /**
     * 校验购物车商品信息
     * @author wanyan
     * @date 2017-10-18
     */
    public function checkGoodsData($storeGoodsId,$specKey,$goodsPrice,$storeId) {
        $storeGoodItemPriceMod = &m('storeGoodItemPrice');
        $orderMod = &m('order');
        $info=$storeGoodItemPriceMod->getOne(array("cond"=>"store_goods_id = {$storeGoodsId}"));
        if(!empty($info)){
            $data=$storeGoodItemPriceMod->getOne(array("cond"=>"`store_goods_id` = {$storeGoodsId}  and  `key` = '{$specKey}' "));
            if(!empty($data)&& $data['price'] == $goodsPrice){
                return true;
            }else{
                return false;
            }
        }else{
            $price = $orderMod->getGoodsPayPrice($storeId,$storeGoodsId,$specKey);
            if($price != $goodsPrice){
                return false;
            }else{
                return true;
            }
        }
    }

}
