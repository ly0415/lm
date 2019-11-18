<?php

/**
 * 活动确认页面
 * @author wanyan
 * @date 2017-11-3
 */
class ActiveComfirmApp extends BaseFrontApp {

    private $storeGoodsMod;
    private $cartMod;
    private $storeMod;
    private $userAddressMod;
    private $orderMod;
    private $orderDetailMod;
    private $combinedSaleMod;
    private $combinedGoodsMod;
    private $spikeActivityMod;
    private $cityMod;
    private $countryMod;
    private $zoneMod;

    public function __construct() {
        parent::__construct();
        $this->storeGoodsMod = &m('areaGood'); //store_goods
        $this->cartMod = &m('cart');
        $this->storeMod = &m('store');
        $this->userAddressMod = &m('userAddress');
        $this->orderMod = &m('order');
        $this->orderDetailMod = &m('orderDetail');
        $this->combinedSaleMod = &m('combinedSale');
        $this->combinedGoodsMod = &m('combinedGoods');
        $this->spikeActivityMod = &m('spikeActivity');
        $this->cityMod = &m('city');
        $this->countryMod = &m('country');
        $this->zoneMod = &m('zone');
    }

    /**
     * 活动商品详情页面
     * @author wanyan
     * @date 2017-11-3
     */
    public function ajaxComfirm() {
        //加载语言包
        $this->load($this->shorthand, 'comfirmOrder/index');
        $a = $this->langData;
//        var_dump($_REQUEST);die;
        if (empty($this->userId)) {
            $referer = $_SERVER['HTTP_REFERER']; // 并不能真实获取上一级的页面。
            //$request_uri = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/bspm711/index.php?app=goods&act=goodInfo&storeid={$_REQUEST['store_id']}&lang={$_REQUEST['langId']}&gid={$_REQUEST['store_goods_id']}";
            // var_dump($url);die;
            $info['url'] = "index.php?app=user&act=login&pageUrl=" . urlencode($referer);
//            $info['url'] = "index.php?app=user&act=login";
            $this->setData($info, $status = 1, $a['Active_goods']);
        }
        $info = array();
        $store_id = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : "0";
        $lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : "0";
        $flag = !empty($_REQUEST['flag']) ? intval($_REQUEST['flag']) : "0";
        $goods_id = !empty($_REQUEST['store_goods_id']) ? intval($_REQUEST['store_goods_id']) : "0";
        $item_id = !empty($_REQUEST['item_id']) ? htmlspecialchars($_REQUEST['item_id']) : "";
        $goods_num = !empty($_REQUEST['goods_num']) ? intval($_REQUEST['goods_num']) : "0";
        $prom_id = !empty($_REQUEST['prom_id']) ? intval($_REQUEST['prom_id']) : "0";
        $goods_price = !empty($_REQUEST['goods_price']) ? $_REQUEST['goods_price'] : "0.00";
        $shipping_price = !empty($_REQUEST['shipping_price']) ? $_REQUEST['shipping_price'] : "0.00";
        $source = !empty($_REQUEST['source']) ? $_REQUEST['source'] : "0";
        $goods_name = !empty($_REQUEST['goods_name']) ? $_REQUEST['goods_name'] : "";
        $goods_img = !empty($_REQUEST['goods_img']) ? $_REQUEST['goods_img'] : "";
        $goods_key_name = !empty($_REQUEST['goods_key_name']) ? $_REQUEST['goods_key_name'] : "";
        $discount_rate = !empty($_REQUEST['discount_rate']) ? $_REQUEST['discount_rate'] : "";
        $reduce = !empty($_REQUEST['reduce']) ? $_REQUEST['reduce'] : "";
        $origin_goods_price = !empty($_REQUEST['origin_goods_price']) ? $_REQUEST['origin_goods_price'] : "";
        $fx_code = !empty($_REQUEST['fx_code']) ? $_REQUEST['fx_code'] : "";
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        if ($fx_code) {
            // 判断当前商品国家和fx人员的所属国家是否相同
            $fxStoreCate = $this->fxUserMod->getOne(array('cond' => "`fx_code` = '{$fx_code}'", 'fields' => "store_cate"));
            $storeCate = $this->storeMod->getOne(array('cond' => "`id` ='{$store_id}'", 'fields' => "store_cate_id"));
            if ($fxStoreCate['store_cate'] != $storeCate['store_cate_id']) {
                $this->setData($info = array(), $status = '0', $a['Active_distribution']);
            }
        }
        if (empty($goods_key_name)) {
            $goods_key_name = $this->getSpec($item_id, $lang_id);
        }
        $sp = array(
            'store_id' => $store_id,
            'goods_id' => $goods_id,
            'item_id' => $item_id,
            'goods_num' => $goods_num,
            'prom_id' => $prom_id,
            'goods_price' => $goods_price,
            'shipping_price' => $shipping_price,
            'source' => $source,
            'goods_name' => $goods_name,
            'goods_img' => $goods_img,
            'goods_key_name' => $goods_key_name,
            'reduce' => $reduce,
            'discount_rate' => $discount_rate,
            'origin_goods_price' => $origin_goods_price,
            'flag' => $flag,
            'fx_code' => $fx_code
        );
        $sp = base64_encode(json_encode($sp));
        $info['url'] = "?app=activeComfirm&act=index&storeid={$store_id}&lang={$lang_id}&auxiliary={$auxiliary}&sp={$sp}";
        $this->setData($info, $status = 1, $a['Active_confirm']);
    }

    /**
     * 生成小票编号
     * @author: luffy
     * @date: 2018-08-09
     */
    public function createNumberOrder($store_id) {
        //获取当天开始结束时间
        $startDay = strtotime(date('Y-m-d'));
        $endDay = strtotime(date('Y-m-d 23:59:59'));
        $sql = 'select order_sn,number_order from  '
                . DB_PREFIX . 'order where add_time BETWEEN ' . $startDay . ' AND ' . $endDay
                . ' AND mark = 1 and store_id = ' . $store_id . ' order by add_time DESC limit 1';
        $res = $this->orderMod->querySql($sql);
        //不管订单存在与否直接加
        $number_order = (int) $res[0]['number_order'] + 1;
        $number_order = str_pad($number_order, 4, 0, STR_PAD_LEFT);
        return $number_order;
    }

    /**
     * 订单确认页面
     * @author wanyan
     * @date 2017-11-3
     */
    public function index() {
        if (empty($this->userId)) {
            $referer = $_SERVER['HTTP_REFERER']; // 并不能真实获取上一级的页面。
            //$request_uri = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/bspm711/index.php?app=goods&act=goodInfo&storeid={$_REQUEST['store_id']}&lang={$_REQUEST['langId']}&gid={$_REQUEST['store_goods_id']}";
            // var_dump($url);die;
            $returnUrl = "index.php?app=user&act=login&pageUrl=" . urlencode($referer);
            header('Location: ' . $returnUrl);
        }
        $referer = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"]);
        $sp = !empty($_REQUEST['sp']) ? htmlspecialchars($_REQUEST['sp']) : '';
        $lang_id = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : '0';
        if (empty($sp)) {
            return false;
        }
        $info = json_decode(base64_decode($sp), true);
        /*   echo '<pre>';
          var_dump($info);
          echo '</pre>';exit; */
         $addr_id = !empty($_REQUEST['addr_id']) ? $_REQUEST['addr_id'] : '';
//        $userAddress = $this->userAddressMod->getAddressById($this->userId); // 获取用户的地址
         if(empty($addr_id)){
               $mr_sql = "select * from " . DB_PREFIX . "user_address where `user_id`= $this->userId and default_addr=1 and distinguish=2";
               $userAddress =$this->userAddressMod->querySql($mr_sql);
         }else{
               $mr_sql = "select * from " . DB_PREFIX . "user_address where `user_id`= $this->userId and id={$addr_id} and distinguish=2";
               $userAddress =$this->userAddressMod->querySql($mr_sql);  
         }
        if (empty($userAddress)) { // 添加
            $this->assign('flag', 1);
        } else {
            $this->assign('flag', 2);
            $this->assign('useraddress', $userAddress[0]);
        }
        if ($info['item_id']) {
            $this->assign('goods_key_name', $this->getSpec($info['item_id'], $lang_id));
        }
        $totalMoney = ($info['goods_price'] * $info['goods_num']);
        $info['goods_name'] = $this->cartMod->getGoodNameById($info['goods_id'], $lang_id);
        $this->assign('info', $info);
        $store_name = $this->storeMod->getNameById($this->storeid, $lang_id);
        $this->assign('store_name', $store_name);
        $this->load($this->shorthand, 'comfirmOrder/index');
        $this->assign('langdata', $this->langData);

        $this->assign('store_id', $this->storeid);
        $this->assign('lang_id', $lang_id);
        $this->assign('symbol', $this->symbol);
        // 团购省的费用
        $saveMoney = $info['origin_goods_price'] - $info['goods_price'];
        if (($info['source'] == 2) || ($info['source'] == 4) || ($info['source'] == 1)) {
            $this->assign('saveMoney', number_format($saveMoney, 2));
        }
        // 获取对应语言的goods_name
        $goods_name = $this->cartMod->getGoodNameById($info['goods_id'], $this->langid);
        // 商品总价格
        $goodsTotal = $totalMoney;
        $this->assign('goodsTotal', number_format($goodsTotal, 2));
        $this->assign('totalMoney', number_format($totalMoney, 2));
        $this->assign('source', $info['source']);
        $this->assign('fx_code', $info['fx_code']);
        $this->assign('pageUrl', $referer);
        $langguages = $this->shorthand;
        $this->assign('langguages', $langguages);
        $this->assign('storeCate', $this->countryId);
        $this->assign('totalGoodTotal', $totalMoney); // 商品总价格
        $this->assign('shipping_fee', $info['shipping_price']); // 购买商品的邮箱
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $this->display('activeComfirm/surebuy.html');
    }

    /**
     * 获取英文的规格
     * @author wanyan
     * @date 2017-11-3
     */
    public function getSpec($sp_key, $lang_id) {
        if ($sp_key) {
            $info = explode('_', $sp_key);
            foreach ($info as $k1 => $v1) {
                $sql = "select `item_name` from " . DB_PREFIX . "goods_spec_item_lang where `item_id` = '{$v1}' and  `lang_id` ='{$lang_id}'";
                $spec_1 = $this->cartMod->querySql($sql);
                $spec[] = $spec_1[0]['item_name'];
            }
            $spec_key = implode(':', $spec);
            return $spec_key;
        }
    }

    /**
     * 确认下单按钮操作
     * @author wanyan
     * @date 2017-10-23
     */
    public function comfirm() {
        //加载语言包
        $this->load($_REQUEST['langguages'], 'comfirmOrder/index');
        $a = $this->langData;
//        var_dump($_REQUEST);die;
        $prom_id = !empty($_REQUEST['active_id']) ? $_REQUEST['active_id'] : '0';
        $fx_code = !empty($_REQUEST['fx_code']) ? $_REQUEST['fx_code'] : '0';
        $fxPhone = !empty($_REQUEST['fxPhone']) ? trim($_REQUEST['fxPhone']) : '';
        $storeCate = !empty($_REQUEST['storeCate']) ? $_REQUEST['storeCate'] : '0';
        $goods_id = !empty($_REQUEST['goods_id']) ? $_REQUEST['goods_id'] : '0';
        $goods_num = !empty($_REQUEST['goods_num']) ? $_REQUEST['goods_num'] : '0';
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : '0';
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : '0';
        $flag = !empty($_REQUEST['flag']) ? $_REQUEST['flag'] : '0';
        // $prom_type= !empty($_REQUEST['prom_type']) ? $_REQUEST['prom_type'] : '0';
        $source = !empty($_REQUEST['source']) ? $_REQUEST['source'] : '0';
        $goods_key = !empty($_REQUEST['goods_key']) ? htmlspecialchars($_REQUEST['goods_key']) : '';
        $seller_msg = !empty($_REQUEST['seller_msg']) ? htmlspecialchars($_REQUEST['seller_msg']) : '';
        $user_address = !empty($_REQUEST['user_address']) ? htmlspecialchars($_REQUEST['user_address']) : '';
        $discount_rate = !empty($_REQUEST['discount_rate']) ? intval($_REQUEST['discount_rate']) : '';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $sendout = !empty($_REQUEST['sendout']) ? htmlspecialchars(trim($_REQUEST['sendout'])) : 1;
        $shippingfee = !empty($_REQUEST['shippingfee']) ? htmlspecialchars(trim($_REQUEST['shippingfee'])) : 0;

        /*  if (empty($user_address)) {
          $this->setData(array(), $status = 0, $a['Active_fillin']);
          } */
        if ($fxPhone) {
            if (!preg_match("/^1[34578]\d{9}$/", $fxPhone) || strlen($fxPhone) != 11) {
                $this->setData($info = array(), $status = 0, $a['fxPhone_error']);
            }
        }
        $rand = $this->buildNo(1);
        $orderNo = date('YmdHis') . $rand[0];
        $store_name = $this->storeMod->getNameById($store_id, $lang_id);
        if ($source == 3) { //商品促销
            $goodInfo = $this->getGoodInfo($prom_id, $goods_id, $goods_key, $store_id);

        } elseif ($source == 2) { // 团购商品
            $goodInfo = $this->getGroupBuyGoods($prom_id);

        } elseif ($source == 4) {
            if ($flag == 1) { // 组合销售主商品
                $goodInfo = $this->getZuHeZGood($flag, $prom_id, $goods_id, $goods_key, $store_id, $lang_id);

            } elseif ($flag == 2) {// 组合销售子商品
                $goodInfo = $this->getZuHeZGood($flag, $prom_id, $goods_id, $goods_key, $store_id, $lang_id);
            }
        } elseif ($source == 1) {
            $goodInfo = $this->getSecKill($prom_id, $goods_id, $store_id);
            if ($goods_key) {
                $key_name = $this->getSpec($goods_key, $lang_id);
            }
        }
        $shipping_fee = $this->getShippingPrice($goods_id);
        $user_address = $this->userAddressMod->getAddress($user_address);
        $userMod = &m('user');
        $uSql = "SELECT * FROM " . DB_PREFIX . 'user WHERE id=' . $this->userId;

        $uData = $userMod->querySql($uSql);
        if (!empty($user_address)) {
            $count = strpos($user_address['address'], "_");
            $addressStr = substr_replace($user_address['address'], "", $count, 1);
        } else {
            $user_address['name'] =$uData[0]['phone'];
            $user_address['phone'] = $uData[0]['phone'];
        }
        if ($sendout == 1) {
            $addressStr = '自提';
        }

        //生成小票编号
        $number_order = $this->createNumberOrder($store_id);


        //$goodsInfo = $this->cartMod->getGoodByCartId($cart_ids);
        $genaral = array(
            'orderNo' => $orderNo,
            'store_id' => $store_id,
            'store_name' => $store_name,
            'buyer_id' => $this->userId,
            'buyer_name' => $user_address['name'],
            'buyer_email' => $this->userName,
            'shipping_fee' => $shippingfee,
            'buyer_address' => /* $this->getAddress($user_address['store_address']) . ' ' . $user_address['address'] */ $addressStr,
            'seller_msg' => $seller_msg,
            'prom_id' => $prom_id,
            'prom_type' => $source,
            'goods_num' => $goods_num,
            'fx_code' => $fx_code,
            'fxPhone' => $fxPhone,
            'storeCate' => $storeCate,
            'buyer_phone' => $user_address['phone'],
            'discount_rate' => $discount_rate,
            'sendout' => $sendout,
            'number_order' => $number_order, //生成小票编号
           'good_id'=>$this->getGoodId($goods_id),
        'deduction'=>$this->getDeduction($goods_id)
        );
        $rs = $this->genOrder($source, $genaral, $goodInfo, $lang_id, $goods_key, $key_name);
        if ($rs) {
            if ($source == 1) {
                $sql = "update " . DB_PREFIX . "spike_activity SET goods_num = goods_num -1 where `id` ='{$prom_id}'";
                $rid = $this->spikeActivityMod->sql_b_spec($sql);
            } elseif ($source == 2) {
                $sql = "update " . DB_PREFIX . "goods_group_buy SET buy_num = buy_num +'{$goods_num}' where `id` ='{$prom_id}'";
                $rid = $this->spikeActivityMod->sql_b_spec($sql);
                $sql = "update " . DB_PREFIX . "goods_group_buy SET order_num = order_num + 1 where `id` ='{$prom_id}'";
                $cid = $this->spikeActivityMod->sql_b_spec($sql);
            }
            $info['url'] = "?app=orderPay&act=index&order_id={$orderNo}&storeid={$store_id}&lang={$lang_id}&auxiliary={$auxiliary}";
            $this->setData($info, $status = 1, $a['Submission_success']);
        } else {
            $this->setData($info, $status = 0, $a['Submission_fail']);
        }
    }
    //获取商品id

    function  getGoodId($id){
        $sql="select goods_id from ".DB_PREFIX.'store_goods where id='.$id;
        $goodInfo=$this->orderDetailMod->querySql($sql);
        return $goodInfo[0]['goods_id'];

    }

    //获取商品扣除方式
    function getDeduction($id){
        $sql="select deduction from ".DB_PREFIX.'store_goods where id='.$id;
        $goodInfo=$this->orderDetailMod->querySql($sql);
        return $goodInfo[0]['deduction'];
    }




    /**
     * 不同活动数据生成订单
     * @author wanyan
     * @date 2017-11-6
     */
    public function genOrder($source, $genInfo, $goodInfo, $lang_id, $goods_key = null, $key_name = null) {
        $fxUserMod = &m('fxuser');
        $insert_main_data = array(
            'order_sn' => $genInfo['orderNo'],
            'store_id' => $genInfo['store_id'],
            'store_name' => $genInfo['store_name'],
            'buyer_id' => $genInfo['buyer_id'],
            'buyer_name' => $genInfo['buyer_name'],
            'buyer_email' => $genInfo['buyer_email'],
            'shipping_fee' => $genInfo['shipping_fee'],
            'order_state' => 10,
            'order_from' => 1,
            'buyer_address' => $genInfo['buyer_address'],
//            'discount' => $discount,
            'fx_phone' => $genInfo['fxPhone'],
            'buyer_phone' => $genInfo['buyer_phone'],
            'add_time' => time(),
            'sendout' => $genInfo['sendout'],
            'number_order' => $genInfo['number_order'], //生成小票编号
        );

        $insert_sub_data = array(
            'order_id' => $genInfo['orderNo'],
            'store_id' => $genInfo['store_id'],
            'buyer_id' => $genInfo['buyer_id'],
            'seller_msg' => $genInfo['seller_msg'],
            'prom_id' => $genInfo['prom_id'],
            'prom_type' => $genInfo['prom_type'],
            'order_state' => 10,
            'add_time' => time(),
            'goods_num' => $genInfo['goods_num'],
            'fx_code' => $genInfo['fx_code'],
//            'discount' =>$discount,
//            'discount_rate' =>$fxUserInfo['fx_discount'],
            'shipping_price' => $genInfo['shipping_fee'],
            'good_id'=>$genInfo['goods_id'],
            'deduction'=>$genInfo['deduction']
        );
        if ($source == 3) { // 商品促销
            if (!empty($genInfo['fxPhone'])) {
                //$fxUserInfo = $fxUserMod->getOne(array('cond' => " telephone='{$genInfo['fxPhone']}' and store_cate ='{$genInfo['storeCate']}'", 'fields' => 'fx_discount'));
                $discount = (($goodInfo['discount_price'] * $genInfo['goods_num'])) * $genInfo['discount_rate'] * 0.01;
            }
            // 先插入主订单数据
            $insert_main_data['goods_amount'] = ($goodInfo['discount_price'] * $genInfo['goods_num']);
            $insert_main_data['order_amount'] = ($goodInfo['discount_price'] * $genInfo['goods_num']) + $genInfo['shipping_fee'] - $discount;
            $insert_main_data['discount'] = $discount;
            $insert_main_data['fx_discount_rate'] = $genInfo['discount_rate'];
            // 插入子表信息

            $insert_sub_data['goods_id'] = $goodInfo['goods_id'];
            $insert_sub_data['goods_name'] = addslashes(stripslashes($goodInfo['goods_name']));
            $insert_sub_data['goods_price'] = $goodInfo['goods_price'];
            $insert_sub_data['goods_image'] = $goodInfo['goods_img'];
            $insert_sub_data['goods_pay_price'] = $goodInfo['discount_price'];
            $insert_sub_data['spec_key_name'] = $goodInfo['goods_key_name'];
            $insert_sub_data['spec_key'] = $goodInfo['goods_key'];
        } elseif ($source == 2) {  //商品团购信息
            if (!empty($genInfo['fxPhone'])) {
//                $fxUserInfo = $fxUserMod->getOne(array('cond' => " telephone='{$genInfo['fxPhone']}' and store_cate ='{$genInfo['storeCate']}'", 'fields' => 'fx_discount'));
                $discount = (($goodInfo['group_goods_price'] * $genInfo['goods_num'])) * $genInfo['discount_rate'] * 0.01;
            }
            // 先插入主订单数据
            $insert_main_data['goods_amount'] = ($goodInfo['group_goods_price'] * $genInfo['goods_num']);
            $insert_main_data['order_amount'] = ($goodInfo['group_goods_price'] * $genInfo['goods_num']) + $genInfo['shipping_fee'] - $discount;
            $insert_main_data['discount'] = $discount;
            $insert_main_data['fx_discount_rate'] = $genInfo['discount_rate'];
            // 插入子表信息
            $insert_sub_data['goods_id'] = $goodInfo['goods_id'];
            $insert_sub_data['goods_name'] = addslashes(stripslashes($goodInfo['goods_name']));
            $insert_sub_data['goods_price'] = $goodInfo['goods_price'];
            $insert_sub_data['goods_image'] = $goodInfo['original_img'];
            $insert_sub_data['goods_pay_price'] = $goodInfo['group_goods_price'];
            $insert_sub_data['spec_key_name'] = $this->getSpec($goodInfo['goods_spec_key'], $lang_id);
            $insert_sub_data['spec_key'] = $goodInfo['goods_spec_key'];
        } elseif ($source == 4) {
            if (!empty($genInfo['fxPhone'])) {
                //$fxUserInfo = $fxUserMod->getOne(array('cond' => " telephone='{$genInfo['fxPhone']}' and store_cate ='{$genInfo['storeCate']}'", 'fields' => 'fx_discount'));
                $discount = (($goodInfo['goods_pay_price'] * $genInfo['goods_num'])) * $genInfo['discount_rate'] * 0.01;
            }
            // 先插入主订单数据
            $insert_main_data['goods_amount'] = ($goodInfo['goods_pay_price'] * $genInfo['goods_num']);
            $insert_main_data['order_amount'] = ($goodInfo['goods_pay_price'] * $genInfo['goods_num']) + $genInfo['shipping_fee'] - $discount;
            $insert_main_data['discount'] = $discount;
            $insert_main_data['fx_discount_rate'] = $genInfo['discount_rate'];
            // 插入子表信息
            $insert_sub_data['goods_id'] = $goodInfo['goods_id'];
            $insert_sub_data['goods_name'] = addslashes(stripslashes($goodInfo['goods_name']));
            $insert_sub_data['goods_price'] = $goodInfo['goods_price'];
            $insert_sub_data['goods_image'] = $goodInfo['goods_image'];
            $insert_sub_data['goods_pay_price'] = $goodInfo['goods_pay_price'];
            $insert_sub_data['spec_key_name'] = $goodInfo['spec_key_name'];
            $insert_sub_data['spec_key'] = $goodInfo['spec_key'];
        } elseif ($source == 1) {
            if (!empty($genInfo['fxPhone'])) {
                //$fxUserInfo = $fxUserMod->getOne(array('cond' => " telephone='{$genInfo['fxPhone']}' and store_cate ='{$genInfo['storeCate']}'", 'fields' => 'fx_discount'));
                $discount = (($goodInfo['group_goods_price'] * $genInfo['goods_num'])) * $genInfo['discount_rate'] * 0.01;
            }
            // 先插入主订单数据
            $insert_main_data['goods_amount'] = ($goodInfo['price'] * $genInfo['goods_num']);
            $insert_main_data['order_amount'] = ($goodInfo['price'] * $genInfo['goods_num']) + $genInfo['shipping_fee'] - $discount;
            $insert_main_data['discount'] = $discount;
            $insert_main_data['fx_discount_rate'] = $genInfo['discount_rate'];
            // 插入子表信息
            $insert_sub_data['goods_id'] = $goodInfo['store_goods_id'];
            $insert_sub_data['goods_name'] = addslashes(stripslashes($goodInfo['goods_name']));
            $insert_sub_data['goods_price'] = $goodInfo['o_price'];
            $insert_sub_data['goods_image'] = $goodInfo['goods_img'];
            $insert_sub_data['goods_pay_price'] = $goodInfo['price'];
            $insert_sub_data['spec_key_name'] = $key_name;
            $insert_sub_data['spec_key'] = $goods_key;
        }
        // var_dump($goodInfo['discount_price']);die;
        $main_rs = $this->orderMod->doInsert($insert_main_data);
        // 先插入子订单
        if ($main_rs) {
            $rs = $this->orderDetailMod->doInsert($insert_sub_data);
            return $rs;
        } else {
            return 0;
        }
    }

    /**
     * 获取促销商品的信息
     * @author wanyan
     * @date 2017-11-2
     */
    public function getGoodInfo($prom_id, $goods_id, $goods_key, $store_id) {
        $sql = " select ps.*,pg.goods_id,pg.goods_key,pg.goods_key_name,pg.goods_name,pg.goods_img,pg.goods_price,pg.discount_price,pg.discount_rate,pg.reduce from " . DB_PREFIX . "promotion_sale as ps
        left join " . DB_PREFIX . "promotion_goods as pg on  ps.id = pg.prom_id 
        where ps.`store_id` = '{$store_id}'  and ps.`mark` =1 
        and pg.goods_id = '{$goods_id}' and pg.goods_key ='{$goods_key}' 
        and ps.id = '{$prom_id}'";
        $rs = $this->storeMod->querySql($sql);
        return $rs[0];
    }

    /**
     * 获取团购商品的信息
     * @author wanyan
     * @date 2017-11-6
     */
    public function getGroupBuyGoods($prom_id) {
        $sql = "select * from " . DB_PREFIX . "goods_group_buy where `id` = '{$prom_id}'";
        $rs = $this->storeMod->querySql($sql);
        return $rs[0];
    }

    /**
     * 获取该商品的运费
     * @author wanyan
     * @date 2017-11-3
     */
    public function getShippingPrice($goods_id) {
        $sql = "select `shipping_price` from " . DB_PREFIX . "store_goods where `id` = '{$goods_id}'";
        $rs = $this->orderMod->querySql($sql);
        return $rs[0]['shipping_price'];
    }

    /**
     * 获取组合销售主商品
     * @author wanyan
     * @date 2017-11-9
     */
    public function getZuHeZGood($flag, $prom_id, $goods_id, $goods_key, $store_id, $lang_id) {
        if ($flag == 1) {
            $query = array(
                'cond' => "`id`='{$prom_id}' and main_id ='{$goods_id}' and main_key ='{$goods_key}' and store_id = '{$store_id}'",
                'field' => "*"
            );
            $rs = $this->combinedSaleMod->getData($query);
            $insert_data = array(
                'goods_id' => $rs[0]['main_id'],
                'goods_name' => $rs[0]['main_name'],
                'goods_price' => $rs[0]['main_price'],
                'goods_image' => $rs[0]['main_img'],
                'goods_pay_price' => $rs[0]['main_price'],
                'spec_key' => $rs[0]['main_key'],
                'spec_key_name' => $rs[0]['main_key_name'],
            );
        } else {
            $sql = "SELECT cg.* FROM `bs_combined_sale` AS cs LEFT JOIN `bs_combined_goods` as cg ON cs.id = cg.com_id 
          WHERE cg.`com_id`='{$prom_id}' and cg.store_goods_id ='{$goods_id}' and cg.item_key ='{$goods_key}' and cs.store_id = '{$store_id}' ";
            $rs = $this->combinedGoodsMod->querySql($sql);
            $insert_data = array(
                'goods_id' => $rs[0]['store_goods_id'],
                'goods_name' => $this->getGoodsName($goods_id),
                'goods_price' => $rs[0]['price'],
                'goods_image' => $rs[0]['goods_img'],
                'goods_pay_price' => $rs[0]['c_price'],
                'spec_key_name' => $this->getSpec($rs[0]['item_key'], $lang_id),
                'spec_key' => $rs[0]['item_key'],
            );
        }
        return $insert_data;
    }

    /**
     * 获取商品的名称
     * @author wanyan
     * @date 2017-11-9
     */
    public function getGoodsName($store_goods_id) {
        $sql = "SELECT (CASE WHEN ISNULL(sgl.goods_name) THEN sg.goods_name ELSE sgl.goods_name END) AS goods_name FROM " . DB_PREFIX . "store_goods as sg
            LEFT JOIN " . DB_PREFIX . "store_goods_lang AS sgl ON sg.id = sgl.store_good_id 
            WHERE `mark` =1 AND sg.id='{$store_goods_id}'";
        $rs = $this->storeMod->querySql($sql);
        return $rs[0]['goods_name'];
    }

    /**
     * 获取商品秒杀的商品信息
     * @author wanyan
     * @date 2017-11-9
     */
    public function getSecKill($prom_id, $goods_id, $store_id) {
        $query = array(
            'cond' => "`id` ='{$prom_id}' and `store_goods_id` = '{$goods_id}'  and `store_id` = '{$store_id}'",
            'fields' => "*"
        );
        $rs = $this->spikeActivityMod->getOne($query);
        return $rs;
    }

    /**
     * 获取三级分销人员的优惠率
     * @author wanyan
     * @date 2017-11-28
     */
    public function getFxDiscount() {
//        var_dump($_REQUEST);die;
        $fxUserMod = &m('fxuser');
        $fxUserTreeMod = &m('fxuserTree');
        $fxPhone = !empty($_REQUEST['fxPhone']) ? $_REQUEST['fxPhone'] : '';
        $totalMoney = !empty($_REQUEST['totalMoney']) ? $_REQUEST['totalMoney'] : '0.00'; // 商品的总价格
        $shipping_fee = !empty($_REQUEST['shippingfee']) ? $_REQUEST['shippingfee'] : '0.00'; // 邮费
//        $fxUserInfo = $fxUserMod->getOne(array('cond' => " telephone='{$fxPhone}' ", 'fields' => 'fx_discount'));
        $fxUserInfo = $fxUserMod->getOne(array('cond' => " telephone='{$fxPhone}' ", 'fields' => '`user_id`,fx_discount'));
        $pidInfo = $fxUserTreeMod->getOne(array('cond' => " user_id='{$fxUserInfo['user_id']}' ", 'fields' => 'pidpid'));
        $firstUserInfo = $fxUserTreeMod->getOne(array('cond' => "`id` = {$pidInfo['pidpid']} ", 'fields' => "user_id"));
        $sql = "select `lev3_prop`  from " . DB_PREFIX . "fx_user_rule AS ur LEFT JOIN " . DB_PREFIX . "fx_rule as r ON ur.rule_id = r.id where ur.user_id = '{$firstUserInfo['user_id']}'";
        $rs = $fxUserMod->querySql($sql);
        if (empty($fxUserInfo['fx_discount']) || $fxUserInfo['fx_discount'] == '0.00') {
            $totalAllMoney = $totalMoney + $shipping_fee;
            $info['totalMoney'] = number_format($totalAllMoney, 2);
            $this->setData($info, $status = '0', $this->symbol . ' 0.00');
        }
        if ($rs[0]['lev3_prop'] < $fxUserInfo['fx_discount']) {
            $discount = ($totalMoney * $rs[0]['lev3_prop'] * 0.01);
            $discount_rate = $rs[0]['lev3_prop'];
        } else {
            $discount = ($totalMoney * $fxUserInfo['fx_discount'] * 0.01);
            $discount_rate = $fxUserInfo['fx_discount'];
        }
        $info['discount'] = $discount;
        $info['zMoney'] = number_format(($totalMoney + $shipping_fee - $discount), 2);
        if ($info['zMoney'] < 0) {
            $info['zMoney'] = 0.01;
        }
        $info['discount_rate'] = $discount_rate;
        $this->setData($info, $status = 1, $message = '');
    }

    /**
     * 获取省市区的地址
     * @author wanyan
     * @date 2017-1-17
     */
    public function getAddress($areaAddress) {
        $areaAddress = explode('_', $areaAddress);
        if (count($areaAddress) == 3) {
            $result = $this->cityMod->getAreaName($areaAddress[0]) . ' ' . $this->cityMod->getAreaName($areaAddress[1]) . ' ' . $this->cityMod->getAreaName($areaAddress[2]);
        } elseif (count($areaAddress) == 2) {
            $country = $this->countryMod->getCountryName($areaAddress[0]);
            $zone = $this->zoneMod->getZoneName($areaAddress[1]);
            $result = $country . ' ' . $zone;
        }
        return $result;
    }

}
