<?php

/**
 * 订单确认模块
 * @author wanyan
 * @date 2017-10-19
 */
class ComfirmOrderApp extends BaseFrontApp {

    private $storeGoodsMod;
    private $cartMod;
    private $storeMod;
    private $userAddressMod;
    private $orderMod;
    private $orderDetailMod;
    private $giftActivityMod;
    private $giftGoodMod;
    private $cityMod;
    private $countryMod;
    private $zoneMod;

    public function __construct() {
        parent::__construct();
        $this->storeGoodsMod = &m('areaGood');
        $this->cartMod = &m('cart');
        $this->storeMod = &m('store');
        $this->userAddressMod = &m('userAddress');
        $this->orderMod = &m('order');
        $this->orderDetailMod = &m('orderDetail');
        $this->giftActivityMod = &m('giftActivity');
        $this->giftGoodMod = &m('giftGood');
        $this->cityMod = &m('city');
        $this->countryMod = &m('country');
        $this->zoneMod = &m('zone');
    }

    /**
     * 订单确认页面
     * @author wanyan
     * @date 2017-10-19
     */
    public function index() {
        $referer = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"]);
        $cart_ids = !empty($_REQUEST['item_id']) ? $_REQUEST['item_id'] : '';
         $addr_id = !empty($_REQUEST['addr_id']) ? $_REQUEST['addr_id'] : '';
//        $userAddress = $this->userAddressMod->getAddressById($this->userId); // 获取用户的地址
         if(empty($addr_id)){
               $mr_sql = "select * from " . DB_PREFIX . "user_address where `user_id`= $this->userId and default_addr=1 and distinguish=2";
        $userAddress =$this->userAddressMod->querySql($mr_sql);
         }else{
               $mr_sql = "select * from " . DB_PREFIX . "user_address where `user_id`= $this->userId and id={$addr_id} and distinguish=2";
        $userAddress =$this->userAddressMod->querySql($mr_sql);  
         }
      
        $userGoods = $this->cartMod->getGoodByCartId($cart_ids);
        $total = 0;
        foreach ($userGoods as $k => $v) {
            $userGoods[$k]['store_name'] = $this->storeMod->getNameById($v['store_id'], $this->langid);
            $userGoods[$k]['origin_img'] = $this->getGoodImg($v['goods_id']);
            $userGoods[$k]['totalMoney'] = number_format(($v['goods_price'] * $v['goods_num']), 2);
            $total += ($v['goods_price'] * $v['goods_num']);
            if ($v['spec_key']) {
                $info = explode('_', $v['spec_key']);
                foreach ($info as $k1 => $v1) {
                    $sql = "select `item_name` from " . DB_PREFIX . "goods_spec_item_lang where `item_id` = '{$v1}' and  `lang_id` = $this->langid";
                    $spec_1 = $this->cartMod->querySql($sql);
                    $spec[] = $spec_1[0]['item_name'];
                }
                $spec_key = implode(':', $spec);
                $userGoods[$k]['spec_key_name'] = $spec_key;
                $spec = array();
            }
            $userGoods[$k]['shipping_store_name'] = $this->storeMod->getNameById($v['shipping_store_id'], $this->langid);
            $userGoods[$k]['goods_name'] = $this->cartMod->getGoodNameById($v['goods_id'], $this->langid);
        }
//      echo '<pre>';
//      var_dump($userGoods);die;
        if (empty($userAddress)) { // 添加
            $this->assign('flag', 1);
        } else {
            $this->assign('flag', 2);
            $this->assign('useraddress', $userAddress[0]);
        }
        $goodNum = $this->getGoodsNum($cart_ids);
        $this->load($this->shorthand, 'comfirmOrder/index');
        $this->assign('total', number_format($total, 2));
        $this->assign('store_id', $this->storeid);
        $this->assign('lang', $this->langid);
        $this->assign('userGoods', $userGoods);
        $this->assign('langdata', $this->langData);
        $this->assign('shipping_price', 0.00);
        $totalMoney = $total;
        $this->assign('totalMoney', number_format($totalMoney, 2));
//      $totalMoney =4000;
        $rs = $this->getGift($this->storeid, $goodNum, $totalMoney);
        // 获取赠送的商品
        $giftInfo = array();
        foreach ($rs as $v) {
            foreach ($v as $v1) {
                $giftGoods = $this->giftGoodMod->getOne(array('cond' => "`id` = {$v1['id']}", 'fields' => 'goods_id,goods_key,gift_num'));
                $gift_good_name = $this->cartMod->getGoodNameById($giftGoods['goods_id'], $this->langid);
                $sql = "select item_name from " . DB_PREFIX . "goods_spec_item_lang where item_id = '{$giftGoods['goods_key']}' and lang_id = $this->langid";
                $goodsKey = $this->cartMod->querySql($sql);
                $giftInfo ['goods_name'] = $gift_good_name;
                $giftInfo ['goods_key_name'] = $goodsKey[0]['item_name'];
                $giftInfo ['gift_num'] = $giftGoods['gift_num'];
                $giftInfo ['gift_id'] = $v1['id'];
            }
        }
        $this->assign('gift', $giftInfo);
        $this->assign('storeCate_1', $this->countryId);
        $this->assign('pageUrl', $referer);
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang_id);
        $this->assign('item_ids', $cart_ids);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $this->display('cart/surebuy.html');
    }

    /**
     * 获取赠品信息
     * @author wanyan
     * @date 2017-11-8
     * @param  $goodNum 商品总数, $totalMoney 商品总价
     */
    public function getGift($store_id, $goodNum, $totalMoney) {
        $rs = $this->getActiveByGoodNum($store_id, $goodNum, $totalMoney);
        foreach ($rs as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $info = explode('_', $k1);
                $sql = "select `id`,`goods_name`,`goods_key_name`,`gift_num` from " . DB_PREFIX . "gift_goods where `gift_id` ='{$info[1]}' and `goods_id` = '{$info[0]}' and `amount` = '{$v1}'";
            }
            $res[] = $this->giftGoodMod->querySql($sql);
        }
        return $res;
    }

    /**
     * 获取商品总数量
     * @author wanyan
     * @date 2017-1-12
     */
    public function getGoodsNum($cart_ids) {
        $sql = "select sum(goods_num) as num  from " . DB_PREFIX . "cart where `id` in ({$cart_ids})";
        $rs = $this->cartMod->querySql($sql);
        return $rs[0]['num'];
    }

    /**
     * 获取商品总金额的在当前时间有没有活动
     * @author wanyan
     * @date 2017-11-08
     */
    public function getActiveByGoodNum($store_id, $goodNum, $totalMoney) {
        $goodInfo = array();
        $sql = "select `id`,`active_id` from " . DB_PREFIX . "gift_activity where `start_time` <= unix_timestamp(now())  AND `end_time` >= unix_timestamp(now()) and `store_id` ='{$store_id}' and mark =1";
        $rs = $this->giftActivityMod->querySql($sql);
        // 这段时间有这个没有满额和满件的活动
        if (empty($rs)) {
            return $goodInfo;
        }
        foreach ($rs as $k => $v) {
            if ($v['active_id'] == 1) { // 满额送
                $giftGoods = $this->getCond($v['id']);
                if ($totalMoney < min($giftGoods)) {
                    $piece_1 = $goodInfo;
                } elseif ($totalMoney >= max($giftGoods)) {
                    $goodInfo = $this->getGoods($giftGoods);
                    $piece_1 = $goodInfo;
                } else {
                    $info = $this->getBetween($giftGoods, $totalMoney);
                    $goodInfo = $this->getGoods($info);
                    $piece_1 = $goodInfo;
                }
            } elseif ($v['active_id'] == 2) {
                $giftGoods = $this->getCond($v['id']);
                if ($goodNum < min($giftGoods)) {
                    $piece_2 = $goodInfo;
                } elseif ($goodNum >= max($giftGoods)) {
                    $goodInfo = $this->getGoods($giftGoods);
                    $piece_2 = $goodInfo;
                } else {
                    $info = $this->getBetween($giftGoods, $goodNum);
                    $goodInfo = $this->getGoods($info); // 获取筛选后值
                    $piece_2 = $goodInfo;
                }
            }
        }
        if (!empty($piece_1) && empty($piece_2)) {
            $pieces[] = $piece_1;
        }
        if (empty($piece_1) && !empty($piece_2)) {
            $pieces[] = $piece_2;
        }
        if (!empty($piece_1) && !empty($piece_2)) {
            $pieces[] = $piece_1;
            $pieces[] = $piece_2;
        }
        return $pieces;
    }

    /**
     * 获取活动设置的赠送条件
     * @author wanyan
     * @date 2017-11-08
     */
    public function getCond($id) {
        $sql = "select `gift_id`,`goods_id`,`amount` from " . DB_PREFIX . "gift_goods where `gift_id` = '{$id}' ";
        $rs = $this->giftGoodMod->querySql($sql);
        foreach ($rs as $k => $v) {
            $res[$v['goods_id'] . '_' . $v['gift_id']] = $v['amount'];
        }
        return $res;
    }

    /**
     * 获取小于总金额的数据
     * @author wanyan
     * @date 2017-11-08
     */
    public function getBetween($giftGoods, $totalMoney) {
        foreach ($giftGoods as $k => $v) {
            if ($v <= $totalMoney) {
                $res[$k] = $v;
            }
        }
        return $res;
    }

    /**
     * 获取最大值的物品ID
     * @author wanyan
     * @date 2017-11-08
     */
    public function getGoods($giftGoods) {
        foreach ($giftGoods as $k => $v) {
            if ($v == max($giftGoods)) {
                $res[$k] = $v;
            }
        }
        return $res;
    }

    /**
     * 获取当前商品图片
     * @author wanyan
     * @date 2017-10-20
     */
    public function getGoodImg($goods_id) {
        $sql = 'select gl.original_img  from  '
            . DB_PREFIX . 'store_goods as g  left join '
            . DB_PREFIX . 'goods as gl on g.goods_id = gl.goods_id where g.id  = ' . $goods_id;
        $rs = $this->storeMod->querySql($sql);
        return $rs[0]['original_img'];
    }

    /**
     * 获取当前用户的运费费
     * @author wanyan
     * @date 2017-10-23
     */
    public function getShippingPrice($cart_id) {
        $sql = "select shipping_price as total from " . DB_PREFIX . "cart where `id` in ({$cart_id}) and `shipping_price` != '0.00' group by `goods_id` order by `add_time`";
        $rs = $this->cartMod->querySql($sql);
        $total = 0;
        foreach ($rs as $k => $v) {
            $total += $v['total'];
        };
        if (empty($total)) {
            $total = '0.00';
        }
        return number_format($total, 2);
    }

    /**
     * 生成不重复的四位随机数
     * @author wanyan
     * @date 2017-10-23
     */
    public function buildNo($limit) {
        $begin = pow(10, 3);
        $end = (pow(10, 4) - 1);
        $rand_array = range($begin, $end);
        shuffle($rand_array); //调用现成的数组随机排列函数
        return array_slice($rand_array, 0, $limit); //截取前$limit个
    }

    /**
     * 确认下单按钮操作
     * @author wanyan
     * @date 2017-10-23
     */
    public function comfirm() {
        //加载语言包
        $this->load($this->shorthand, 'comfirmOrder/index');
        $a = $this->langData;
        $fxUserMod = &m('fxuser');
        $cart_ids = !empty($_REQUEST['cart_ids']) ? htmlspecialchars($_REQUEST['cart_ids']) : '';
        $seller_msg = !empty($_REQUEST['seller_msg']) ? htmlspecialchars($_REQUEST['seller_msg']) : '';
        $user_address = !empty($_REQUEST['user_address']) ? htmlspecialchars($_REQUEST['user_address']) : '';
        $gift_id = !empty($_REQUEST['gift_id']) ? intval($_REQUEST['gift_id']) : '';
        $fxPhone = !empty($_REQUEST['fxPhone']) ? trim($_REQUEST['fxPhone']) : '';
        $storeCate = !empty($_REQUEST['storeCate']) ? intval($_REQUEST['storeCate']) : '';
        $storeid = !empty($_REQUEST['storeid']) ? intval($_REQUEST['storeid']) : '';
        $lang = !empty($_REQUEST['storeCate']) ? intval($_REQUEST['lang']) : '';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $discount_rate = !empty($_REQUEST['discount_rate']) ? intval($_REQUEST['discount_rate']) : '';
        $sendout = !empty($_REQUEST['sendout']) ? htmlspecialchars($_REQUEST['sendout']) : '1';
        $shippingfee = !empty($_REQUEST['shippingfee']) ? htmlspecialchars($_REQUEST['shippingfee']) : 0;
        if ($fxPhone) {
            if (!preg_match("/^1[34578]\d{9}$/", $fxPhone) || strlen($fxPhone) != 11) {
                $this->setData($info = array(), $status = 0, $a['fxPhone_error']);
            }
        }
        $rand = $this->buildNo(1);
        $orderNo = date('YmdHis') . $rand[0];
        $sql = "select c.user_id,c.store_id,SUM(c.goods_price*goods_num) as goods_amount,u.email,u.username from " . DB_PREFIX . "cart as c  LEFT JOIN " . DB_PREFIX . "user as u ON c.user_id = u.id  where c.`id` in  ({$cart_ids}) ";
        $orderInfo = $this->cartMod->querySql($sql);
        $orderInfo[0]['store_name'] = $this->storeMod->getNameById($orderInfo[0]['store_id'], $this->langid);
        /*  $shipping_fee = $this->getShippingPrice($cart_ids); */
        $user_address = $this->userAddressMod->getAddress($user_address);
        $goodsInfo = $this->cartMod->getGoodByCartId($cart_ids);
        if (!empty($fxPhone)) {
            //$fxUserInfo = $fxUserMod->getOne(array('cond' => " telephone='{$fxPhone}' and store_cate ='{$storeCate}'", 'fields' => 'fx_discount'));
            $discount = (($orderInfo[0]['goods_amount']) * $discount_rate * 0.01);
        }else {
            $discount = 0;
        }
        //生成小票编号
        $number_order = $this -> createNumberOrder($storeid);
        // 先插入主订单
        $userMod = &m('user');
        $uSql = "SELECT * FROM " . DB_PREFIX . 'user WHERE id=' . $this->userId;

        $uData = $userMod->querySql($uSql);
        if(!empty($user_address)){
            $count = strpos($user_address['address'], "_");
            $addressStr = substr_replace($user_address['address'], "", $count, 1);
        }else{
            $user_address['name']=$uData[0]['phone'];
            $user_address['phone']=$uData[0]['phone'];
        }
        if($sendout==1){
            $addressStr='自提';
        }
        foreach($goodsInfo as $k=>$v){
            $invalid=$this->cartMod->isInvalid($v['goods_id'],$v['spec_key']);
            if(empty($invalid)){
                $this->setData(array(),'0',$v['goods_name'].'商品库存不足');
            }else{
                if($invalid<$v['goods_num']){
                    $this->setData(array(),'0',$v['goods_name'].'商品库存不足');
                }
            }
        }
        $insert_main_data = array(
            'order_sn' => $orderNo,
            'store_id' => $orderInfo[0]['store_id'],
            'sendout' => $sendout, // 1派送 2自提 3邮寄托运
            'store_name' => $orderInfo[0]['store_name'],
            'buyer_id' => $orderInfo[0]['user_id'],
            'buyer_name' => addslashes($user_address['name']),
            'buyer_email' => $orderInfo[0]['email'],
            'goods_amount' => $orderInfo[0]['goods_amount'],
            'order_amount' => ($orderInfo[0]['goods_amount'] + $shippingfee - $discount),
            'shipping_fee' => $shippingfee,
            'order_state' => 10,
            'order_from' => 1,
            'buyer_address' => $addressStr . ' ' . $user_address['postal_code'],
            'buyer_phone' => $user_address['phone'],
            'gift_id' => $gift_id,
            'discount' => $discount,
            'fx_discount_rate' => $discount_rate,
            'fx_phone' => $fxPhone,
            'add_time' => time(),
            'number_order' => $number_order, //生成小票编号
            'seller_msg' => $seller_msg, //订单留言
            'sub_user' => 2,
            ''
        );

        $count = count($goodsInfo);
        $discount = round($discount / $count, 2);
        $main_rs = $this->orderMod->doInsert($insert_main_data);
        //生成2维码
        $code = $this->goodsZcode($main_rs);
        $cond['order_url'] = $code;
        $urldata = array(
            "table" => "order",
            'cond' => 'order_id = ' . $main_rs,
            'set' => "order_url='" . $code . "'",
        );
        $ress = $this->orderMod->doUpdate($urldata);
        // 先插入子订单
        if ($main_rs) {
            foreach ($goodsInfo as $k => $v) {
                $insert_sub_data = array(
                    'order_id' => $orderNo,
                    'goods_id' => $v['goods_id'],
                    'goods_name' => addslashes($v['goods_name']),
                    'goods_price' => $v['market_price'],
                    'goods_num' => $v['goods_num'],
                    'goods_image' => $this->getGoodImg($v['goods_id']),
                    'goods_pay_price' => $v['goods_price'],
                    'spec_key_name' => $v['spec_key_name'],
                    'spec_key' => $v['spec_key'],
                    'store_id' => $v['store_id'],
                    'buyer_id' => $v['user_id'],
                    'goods_type' => 0,
//                    'seller_msg' => $seller_msg[$k],
                    'order_state' => 10,
                    'fx_code' => $v['fx_code'],
//                     'discount' =>($v['goods_price']+$v['shipping_price'])*$fxUserInfo['fx_discount']*0.01,
//                     'discount_rate' =>$fxUserInfo['fx_discount'],
                    'shipping_price' => $v['shipping_price'],
                    'shipping_store_id' => $v['shipping_store_id'],
                    'add_time' => time(),
                   'good_id'=>$this->getGoodId($v['goods_id']),
                    'deduction'=>$this->getDeduction($v['goods_id'])
                );
                $rs[] = $this->orderDetailMod->doInsert($insert_sub_data);
            }
            $rs = array_filter($rs);
            if (count($rs)) {
                if ($this->delCart($cart_ids)) {
                    $info['url'] = "?app=orderPay&act=index&order_id={$orderNo}&storeid={$storeid}&lang={$lang}&auxiliary={$auxiliary}";
                    $this->setData($info, $status = 1, $a['Submission_success']);
                } else {
                    $this->setData($info = array(), $status = 0, $a['Submission_fail']);
                }
            }
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
     * 生成小票编号
     * @author: luffy
     * @date: 2018-08-09
     */
    public function createNumberOrder() {
        //获取当天开始结束时间
        $startDay   = strtotime(date('Y-m-d'));
        $endDay     = strtotime(date('Y-m-d 23:59:59'));
        $sql = 'select order_sn,number_order from  '
            . DB_PREFIX . 'order where add_time BETWEEN '.$startDay.' AND '.$endDay
            .' AND mark = 1 and store_id = ' . $this->storeid . ' order by add_time DESC limit 1';
        $res = $this->orderMod->querySql($sql);
        //不管订单存在与否直接加
        $number_order = (int)$res[0]['number_order'] + 1;
        $number_order = str_pad($number_order, 4, 0, STR_PAD_LEFT);
        return $number_order;
    }
    public function goodsZcode($order_id) {
        include ROOT_PATH . "/includes/classes/class.qrcode.php"; // 生成二维码库
        $mainPath = ROOT_PATH . '/upload/orderCode';
        $this->mkDir($mainPath);
        $timePath = date('Ymd');
        $savePath = $mainPath . '/' . $timePath;
        $this->mkDir($savePath);
        $newFileName = uniqid() . ".png";
        $filename = $savePath . '/' . $newFileName;
        $pathName = 'upload/orderCode/' . $timePath . '/' . $newFileName;
        $http_host = $_SERVER['HTTP_HOST'];
        $system_web = 'www.711home.net';
        $valueUrl = 'http://' . $system_web . "/index.php?app=print&act=index&orderid={$order_id}";
        QRcode::png($valueUrl, $filename);
        return $pathName;
    }

    public function mkDir($dir) {
        if (!is_dir($dir)) {
            @mkdir($dir);
            @chmod($dir, 0777);
            @exec('chmod -R 777 {$dir}');
        }
    }

    /**
     * 删除下单完成后删除购物车中数据
     * @author wanyan
     * @date 2017-11-9
     */
    public function delCart($cart_ids) {
        $query = array(
            'cond' => " `id` in ({$cart_ids})"
        );
        $rs = $this->cartMod->doDelete($query);
        if ($rs) {
            return true;
        } else {
            return false;
        }
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
        $fxPhone = !empty($_REQUEST['fxPhone']) ? trim($_REQUEST['fxPhone']) : '';
        $item_ids = !empty($_REQUEST['item_ids']) ? $_REQUEST['item_ids'] : '';
        $shippingfee = !empty($_REQUEST['shippingfee']) ? $_REQUEST['shippingfee'] : 0;
        $totalMoney = $this->getGoodTotal($item_ids);
        $fxUserInfo = $fxUserMod->getOne(array('cond' => " telephone='{$fxPhone}' ", 'fields' => '`user_id`,fx_discount'));
        $pidInfo = $fxUserTreeMod->getOne(array('cond' => " user_id='{$fxUserInfo['user_id']}' ", 'fields' => 'pidpid'));
        $firstUserInfo = $fxUserTreeMod->getOne(array('cond' => "`id` = {$pidInfo['pidpid']} ", 'fields' => "user_id"));
        $sql = "select `lev3_prop`  from " . DB_PREFIX . "fx_user_rule AS ur LEFT JOIN " . DB_PREFIX . "fx_rule as r ON ur.rule_id = r.id where ur.user_id = '{$firstUserInfo['user_id']}'";
        $rs = $fxUserMod->querySql($sql);
        if (empty($fxUserInfo['fx_discount']) || $fxUserInfo['fx_discount'] == '0.00') {
            $info['totalMoney'] = number_format(($totalMoney + $shippingfee), 2);
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
        $info['zMoney'] = number_format(($totalMoney + $shippingfee - $discount), 2);
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

    /*
     * 睿积分兑换优惠处理
     * @auhtor lee
     * @date 2018-5-7 15:35:33
     */

    public function getPointPrice() {
        //语言包
        $this->load($this->shorthand, 'userCenter/userCenter');
        $a = $this->langData;
        $userMod = &m('user');
        $pointSiteMod = &m('point');
        $storePointMod = &m('storePoint');
        $storeMod = &m('store');
        $curMod = &m('currency');
        $user_id = $this->userId;
        $point = $_REQUEST['point'] ? (int) $_REQUEST['point'] : 0;
        $user_info = $userMod->getOne(array("cond" => "id=" . $user_id));
        $order_id = !empty($_REQUEST['order_id']) ? $_REQUEST['order_id'] : '';
        $order_info = $this->orderMod->getOne(array("cond" => "order_sn=" . $order_id));

        $cp_amount = (float) $order_info['cp_amount'];
        if (!empty($cp_amount)) {
            $this->setData('', 0, '你已经使用了优惠券，不可使用积分');
        }
        if ($order_info['pd_amount'] > 0) {
            $this->setData(array(), $status = 0, $a['has_use']);
        }
        if (!preg_match("/^[1-9][0-9]*$/", $point)) {
            $this->setData(array(), $status = 0, $a['rui_z']);
        }
        if (empty($point) || $point == 0) {
            $this->setData(array(), $status = 0, $a['rui_num']);
        }
        if ($point > $user_info['point']) {
            //echo  '睿积分不足';exit;
            $this->setData(array(), $status = 0, $a['no_point']);
        }
        $store_id = $_REQUEST['store_id'] ? $_REQUEST['store_id'] : $this->storeid;
        //获取订单总金额
        $totalMoney = $order_info['goods_amount']; //原订单价格
        //获取最大睿积分支付比例
        $point_price_site = $pointSiteMod->getOne(array("cond" => "1=1"));
        $store_point_site = $storePointMod->getOne(array("cond" => "store_id=" . $order_info['store_id']));
        $point_price_site['point_price'] = empty($store_point_site) ? 0 : $store_point_site['point_price'];

        if ($point_price_site) {
            $point_price = $point_price_site['point_price'] * $totalMoney / 100; //睿积分兑换最大金额
            $rmb_point = $point_price_site['point_rate']; //睿积分和RMB的比例
        } else {
            $point_price = 0;
            $rmb_point = 0;
        }
        //获取当前店铺币种以及兑换比例
        $store_info = $storeMod->getOne(array("cond" => "id=" . $store_id));
        //获取当前币种和RMB的比例
        $rate = $curMod->getCurrencyRate($store_info['currency_id']);
        //睿积分和RMB的比例
        if ($rate) {
            // $price_rmb = ceil(($point_price*$rate)/$rmb_point);
            //最大比例使用睿积分
            $price_rmb_point = ceil($point_price * $rate * $rmb_point);
        } else {
            $this->setData(array(), $status = 0, $a['no_point_site']);
            // echo  '该币种汇率尚未添加，请联系管理员！';exit;
        }
        if ($point > $price_rmb_point) {
            $this->setData(array(), $status = 0, $message = $a['order_point_max'] . $price_rmb_point . $a['rui']);
            //echo "该订单最多只能使用".$price_rmb."睿积分";exit;
        }
        $last_price = ($point / $point_price_site['point_rate']) / $rate;


        $order_price = number_format(($totalMoney - $last_price), 2, '.', '');
        if ($order_price == '0.00') {
            $order_price = 0.01;
        }
        $order_arr = array(
            'pd_amount' => $last_price,
            'order_amount' => $order_price,
            'cp_amount' => 0.00
        );
        $order_cond = array(
            'order_sn' => $order_id
        );

        $order_res = $this->orderMod->doEditSpec($order_cond, $order_arr);

        if ($order_res) {
            //扣除用户睿积分
            $user_point = $user_info['point'] - $point;
            $userMod->doEdit($user_id, array("point" => $user_point));
            //睿积分日志
            $logMessage = "订单：" . $order_id . " 使用：" . $point . "睿积分";
            $this->addPointLog($user_info['phone'], $logMessage, $user_id, 0, $point, $order_id);
            $this->setData(array('money' => $order_price), $status = 1, $message = '扣除成功！');
        } else {
            $this->setData(array(), $status = 0, $message = '网络错误，扣除失败！');
        }
    }

    //生成日志
    public function addPointLog($username, $note, $userid, $deposit, $expend, $order_sn = null) {
        $logData = array(
            'operator' => '--',
            'username' => $username,
            'add_time' => time(),
            'deposit' => $deposit,
            'expend' => $expend,
            'note' => $note,
            'userid' => $userid,
        );
        if ($order_sn) {
            $logData['order_sn'] = $order_sn;
        }
        $pointLogMod = &m("pointLog");
        $pointLogMod->doInsert($logData);
    }

    //获取运费和配送费
    public function getMoney() {
        $type = !empty($_REQUEST['type']) ? htmlspecialchars(trim($_REQUEST['type'])) : 1;
        $total = !empty($_REQUEST['total']) ? $_REQUEST['total'] : 0;
        $discount = !empty($_REQUEST['discount']) ? $_REQUEST['discount'] : 0;
        if ($type == 1) {
            $shipping_price = 0;
        }
        if ($type == 2) {
            $cart_id = !empty($_REQUEST['cart_ids']) ? htmlspecialchars($_REQUEST['cart_ids']) : '';
            $sql = "select `shipping_store_id` from " . DB_PREFIX . "cart where `id` in (" . $cart_id . ")";
            $info = $this->cartMod->querySql($sql);
            foreach ($info as $key => $val) {
                $store_ids[] = $val['shipping_store_id'];
            }
            $store_ids = array_unique($store_ids);
            $store_ids = implode(',', $store_ids);
            $sql = "select `fee` from " . DB_PREFIX . "store where `id` in (" . $store_ids . ")";
            $data = $this->cartMod->querySql($sql);

            foreach ($data as $k => $v) {
                $shipping_price += $v['fee'];
            }
        }
        if ($type == 3) {
            $cart_id = !empty($_REQUEST['cart_ids']) ? htmlspecialchars($_REQUEST['cart_ids']) : '';
            $sql = "select shipping_price,id from " . DB_PREFIX . "cart  where `id` in (" . $cart_id . ") order by shipping_price desc";
            $info = $this->cartMod->querySql($sql);
            $shipping_price = $info[0]['shipping_price'];
        }
        $price = number_format($shipping_price, 2);
        $totalMoney = $total + $shipping_price - $discount;
        if ($totalMoney < 0) {
            $totalMoney = 0.01;
        }
        $totalMoney = number_format($totalMoney, 2);
        $info = array('price' => $price, 'totalMoney' => $totalMoney);
        $this->setData($info, '1', '');
    }

    public function getMoney1() {
        $type = !empty($_REQUEST['type']) ? htmlspecialchars(trim($_REQUEST['type'])) : 1;
        $total = !empty($_REQUEST['total']) ? $_REQUEST['total'] : 0;
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : 0;
        $shippingprice = !empty($_REQUEST['shippingprice']) ? $_REQUEST['shippingprice'] : 0;
        $discount = !empty($_REQUEST['discount']) ? $_REQUEST['discount'] : 0;

        if ($type == 1) {
            $shipping_price = 0;
        }
        if ($type == 2) {

            $sql = "select `fee` from " . DB_PREFIX . "store where `id` in (" . $storeid . ")";
            $data = $this->cartMod->querySql($sql);

            foreach ($data as $k => $v) {
                $shipping_price += $v['fee'];
            }
        }
        if ($type == 3) {
            $shipping_price = $shippingprice;
        }
        $price = number_format($shipping_price, 2);
        $totalMoney = $total + $price - $discount;
        if ($totalMoney < 0) {
            $totalMoney = 0.01;
        }
        $totalMoney = number_format($totalMoney, 2);
        $info = array('price' => $price, 'totalMoney' => $totalMoney);
        $this->setData($info, '1', '');
    }

    public function getCouponPrice() {
        $this->load($this->shorthand, 'userCenter/userCenter');
        $a = $this->langData;
        $userMod = &m('user');
        $pointSiteMod = &m('point');
        $storePointMod = &m('storePoint');
        $storeMod = &m('store');
        $curMod = &m('currency');
        $userCounponMod = &m('userCoupon');
        $couponMod = &m('coupon');
        $user_id = $this->userId;
        $storeid = $_REQUEST['storeid'] ? $_REQUEST['storeid'] : 0;
        $cid = $_REQUEST['cid'] ? (int) $_REQUEST['cid'] : 0;
        $user_info = $userMod->getOne(array("cond" => "id=" . $user_id));
        $order_id = !empty($_REQUEST['order_id']) ? $_REQUEST['order_id'] : '';
        $order_info = $this->orderMod->getOne(array("cond" => "order_sn=" . $order_id));
        $store_id = $_REQUEST['store_id'] ? $_REQUEST['store_id'] : $this->storeid;
        //获取订单总金额
        $totalMoney = $order_info['goods_amount']; //原订单价格
        //获取最大睿积分支付比例
        $point_price_site = $pointSiteMod->getOne(array("cond" => "1=1"));
        $store_point_site = $storePointMod->getOne(array("cond" => "store_id=" . $order_info['store_id']));
        $point_price_site['point_price'] = empty($store_point_site) ? 0 : $store_point_site['point_price'];
        //优惠券
        $sql = "select * from " . DB_PREFIX . "coupon where id=" . $cid;
        $cData = $couponMod->querySql($sql);


        if ($totalMoney < $cData[0]['money']) {
            $this->setData('', 0, $a['use_bukeyong']);
        }
        $pd_amount = (float) $order_info['pd_amount'];
        if (!empty($pd_amount)) {
            $this->setData('', 0, $a['use_yishiyong']);
        }

        if (!empty($order_info['cp_amount'])) {
            $order_price = number_format(($totalMoney - $cData[0]['discount'] + $order_info['cp_amount']), 2, '.', '');
        } else {
            $order_price = number_format(($totalMoney - $cData[0]['discount']), 2, '.', '');
        }
        if (!empty($order_info['cid'])) {
            $info = array('user_id' => $this->userId, 'c_id' => $order_info['cid'], 'store_id' => $storeid, 'remark' => '发送优惠券', 'type' => 1);
            $rs = $userCounponMod->doInsert($info);
        }

        //优惠金额
        if ($order_price == '0.00') {
            $order_price = 0.01;
        }
        $order_arr = array(
            'pd_amount' => 0.00,
            'order_amount' => $order_price,
            'cp_amount' => $cData[0]['discount'],
            'cid' => $cid
        );
        $order_cond = array(
            'order_sn' => $order_id
        );
        $order_res = $this->orderMod->doEditSpec($order_cond, $order_arr);
        if ($order_res) {
            $where = " c_id=" . $cid . " and user_id=" . $this->userId;
            $res = $userCounponMod->doDrops($where);
            $this->setData(array(), $status = 1, $a['use_chenggong']);
        } else {
            $this->setData(array(), $status = 0, $a['use_shibai']);
        }
    }

}

?>