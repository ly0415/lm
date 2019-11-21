<?php

/**
 * 微信订单订单中心
 * @author wanyan
 *
 */
class OrderSureApp extends BaseWxApp {
    private $orderMod;
    private $orderGoodsMod;
    private $userAddressMod;
    private $storeMod;
    private $cityMod;
    private $countryMod;
    private $zoneMod;
    private $storeGoodMod;
    private $storeGoodItemPriceMod;
    private $userMod;
    private $goodsMod;
    private $storeGoodsMod;
    private $cartMod;
    private $orderDetailMod;
    private $combinedSaleMod;
    private $combinedGoodsMod;
    private $spikeActivityMod;

    public function __construct() {
        parent::__construct();
        $this->orderMod = &m('order');
        $this->orderGoodsMod = &m('orderGoods');
        $this->userAddressMod = &m('userAddress');
        $this->storeMod = &m('store');
        $this->cityMod = &m('city');
        $this->countryMod = &m('country');
        $this->zoneMod = &m('zone');
        $this->storeGoodMod = &m('storeGoods');
        $this->storeGoodItemPriceMod = &m('storeGoodItemPrice');
        $this->userMod = &m('user');
        $this->goodsMod = &m('goods');
        $this->storeGoodsMod = &m('areaGood'); //store_goods
        $this->cartMod = &m('cart');
        $this->orderDetailMod = &m('orderDetail');
        $this->combinedSaleMod = &m('combinedSale');
        $this->combinedGoodsMod = &m('combinedGoods');
        $this->spikeActivityMod = &m('spikeActivity');
    }

    /**
     *  确认订单页面
     */
    public function doBuy() {
//        echo '<pre>';print_r($_REQUEST);die;
        //语言包
        $this->load($this->shorthand, 'WeChat/goods');
        $a = $this->langData;
        if (empty($this->userId)) {
            $referer = $_SERVER['HTTP_REFERER']; // 并不能真实获取上一级的页面。
            $returnUrl = "wx.php?app=user&act=quickLogin&storeid=" . $this->storeid . "&lang=" . $this->langid . "&returnUrl=" . urlencode($referer);
            $info['url'] = $returnUrl;
            $this->setData($info, $status = '0', $a['order_login']);
        }
        $langid = !empty($_REQUEST['langId']) ? intval($_REQUEST['langId']) : '';
        $store_id = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : '';
        $store_goods_id = !empty($_REQUEST['store_goods_id']) ? intval($_REQUEST['store_goods_id']) : '';
        $goods_keys = !empty($_REQUEST['item_id']) ? $_REQUEST['item_id'] : array();
        $goods_num = !empty($_REQUEST['goods_num']) ? intval($_REQUEST['goods_num']) : '';
        $goods_price = !empty($_REQUEST['goods_price']) ? $_REQUEST['goods_price'] : '0.00';
        $shipping_price = !empty($_REQUEST['shipping_price']) ? $_REQUEST['shipping_price'] : '0.00';
        $shipping_store_id = !empty($_REQUEST['shipping_store_id']) ? intval($_REQUEST['shipping_store_id']) : '';
        $order_from = !empty($_REQUEST['order_from']) ? intval($_REQUEST['order_from']) : '';
        $cid = !empty($_REQUEST['cid']) ? intval($_REQUEST['cid']) : '';
        $source = !empty($_REQUEST['source']) ? intval($_REQUEST['source']) : '';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : 0;
        $data = array(
            'goods_id' => $store_goods_id,
            'goods_keys' => $goods_keys,
            'goods_num' => $goods_num,
            'goods_price' => $goods_price,
            'shipping_price' => $shipping_price,
            'shipping_store_id' => $shipping_store_id,
            'order_from' => $order_from,
            'source' => $source,
            'cid' => $cid
        );
        $sp = base64_encode(json_encode($data));
        $info['url'] = "wx.php?app=orderSure&act=sureOrder&lang={$langid}&storeid={$store_id}&auxiliary={$auxiliary}&latlon={$latlon}&sp={$sp}";
        $this->setData($info, $status = 1, $message = '');
    }

    public function sureOrder() {
        //语言包
        $address = !empty($_REQUEST['address']) ? $_REQUEST['address'] : '';
        $this->assign('address', $address);
        $this->load($this->shorthand, 'WeChat/goods');
        $this->assign('langdata', $this->langData);
        $referer = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"]);
        $lang = !empty($_REQUEST['lang']) ? intval($_REQUEST['lang']) : $this->langid;
        $store_id = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : $this->storeid;
        $sp = !empty($_REQUEST['sp']) ? htmlspecialchars($_REQUEST['sp']) : '';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars($_REQUEST['latlon']) : '';
        $this->assign('latlon', $latlon);
        $info = json_decode(base64_decode($sp), true);
        //获取收货地址
        $addr_id = !empty($_REQUEST['addr_id']) ? htmlspecialchars(trim($_REQUEST['addr_id'])) : '';
        if ($addr_id=='') {
            $where = ' and default_addr =1';

        } else {
            $where = ' and id=' . $addr_id;
        }
        $addrSql = "select * from " . DB_PREFIX . 'user_address where distinguish=1 and  user_id=' . $this->userId . $where;
        $userAddress = $this->userAddressMod->querySql($addrSql); // 获取用户的地址
        if ($addr_id == '0') {
            $addr_id = $userAddress[0]['id'];
        }
        $this->assign('nowlatlon', $userAddress[0]['latlon']);
        if (empty($userAddress[0])) { // 添加
            $this->assign('flag', 1);
        } else {
            $this->assign('flag', 2);
            $addresss = explode('_', $userAddress[0]['address']);
            $this->assign('city', $addresss[0]);

            $count = strpos($userAddress[0]['address'], "_");
            $str = substr_replace($userAddress[0]['address'], "", $count, 1);
            $this->assign('address1', $str);
            $this->assign('userAddress', $userAddress[0]);
        }
        if ($info['goods_keys']) {
            $this->assign('goods_key_name', $this->getSpec1($info['goods_keys'], $lang));
        }
        $user_info = $this->userMod->getOne(array("cond" => "id=" . $this->userId));
        $totalMoney = ($info['goods_price'] * $info['goods_num']);
        $total = $totalMoney;
        $info['goods_keys'] = implode('_', $info['goods_keys']);

        //优惠券
        $sql = "select c_id from " . DB_PREFIX . 'user_coupon where user_id=' . $this->userId . ' and store_id= ' . $store_id;
        $info1 = $this->cartMod->querySql($sql);
        foreach ($info1 as $key => $val) {
            $cIds[] = $val['c_id'];
        }
        $cIds = implode(',', $cIds);
        //过期优惠券
        $cSql = "select * from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and end_time < ' . time();
        $ctSql = "select count(*) as ctotal from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and end_time < ' . time();

        $cData = $this->cartMod->querySql($cSql);
        $ctData = $this->cartMod->querySql($ctSql);
        $this->assign('ctotal', $ctData[0]['ctotal']);
        foreach ($cData as $key => $val) {
            $cData[$key]['expire'] = 1;
        }
        //未过期优惠券
        $wSql = "select * from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and end_time > ' . time() . ' and start_time < ' . time();
        $wtSql = "select count(*) as wtotal from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and end_time > ' . time() . ' and start_time < ' . time();
        $wData = $this->cartMod->querySql($wSql);
        $wtData = $this->cartMod->querySql($wtSql);
        foreach ($wData as $key => $val) {
            $wData[$key]['expire'] = 0;
        }
        $this->assign('wtotal', $wtData[0]['wtotal']);
        $this->assign('wData', $wData);
        $this->assign('cData', $cData);
        //睿积分兑换比例
        $pointSiteMod = &m('point');
        $point_price_site = $pointSiteMod->getOne(array("cond" => "1=1"));
        $rate = $point_price_site['point_rate'];
        $this->assign('rate', $rate);
        $this->assign('info', $info);
        $this->assign('user_info', $user_info);
        //睿积分抵扣
        $pointSiteMod = &m('point');
        $storePointMod = &m('storePoint');
        $storeMod = &m('store');
        $curMod = &m('currency');
        //获取订单总金额
        //获取最大积分支付比例
        $point_price_site = $pointSiteMod->getOne(array("cond" => "1=1"));
        $store_point_site = $storePointMod->getOne(array("cond" => "store_id=" . $store_id));
        $point_price_site['point_price'] = empty($store_point_site) ? 0 : $store_point_site['point_price'];
        if ($point_price_site) {
            $point_price = $point_price_site['point_price'] * $total / 100; //积分兑换最大金额
            $rmb_point = $point_price_site['point_rate']; //积分和RMB的比例
        } else {
            $point_price = 0;
            $rmb_point = 0;
        }
        //获取当前店铺币种以及兑换比例
        $store_info = $storeMod->getOne(array("cond" => "id=" . $store_id));
        //获取当前币种和RMB的比例
        $rate = $curMod->getCurrencyRate($store_info['currency_id']);
        //积分和RMB的比例
        if ($rate) {
            // $price_rmb = ceil(($point_price*$rate)/$rmb_point);
            //最大比例使用积分
            $price_rmb_point = ceil($point_price * $rate * $rmb_point);
            if ($price_rmb_point > $user_info['point']) {
                $point_price = $user_info['point'] * $rmb_point / 100;
                $price_rmb_point = ceil($point_price * $rate * $rmb_point);
            }
        }
        $this->assign('money', number_format($total - $point_price, 2));
        $this->assign('maxAccount', number_format($point_price, 2));
        $this->assign('maxPoint', $price_rmb_point);
        $store_name = $this->storeMod->getNameById($store_id);
        $this->assign('store_name', $store_name);
        $shipping_store_name = $this->storeMod->getNameById($info['shipping_store_id'], $lang);
        $this->assign('shipping_store_name', $shipping_store_name);
        $this->load($this->shorthand, 'comfirmOrder/index');
        $this->assign('langdata', $this->langData);
        $this->assign('sku', $this->storeGoodMod->getSku($info['goods_id']));
        $this->assign('store_id', $store_id);
        $this->assign('lang', $lang);
        $this->assign('symbol', $this->symbol);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $this->assign('goods_name', $this->storeGoodMod->getGoodsName($info['goods_id'], $lang));
        $this->assign('original_img', $this->storeGoodMod->getStoreGoodImg($info['goods_id']));
        $this->assign('storeName', $this->storeName($this->storeid, $auxiliary, $this->langid));
        $this->assign('total', number_format($totalMoney, 2));
        $this->assign('referer', $referer);
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang_id);
        $this->assign('addr_id', $addr_id);
        $this->display('directPay/sureorder.html');
    }
    public function getFxDiscount1()
    {
        $fxUserMod = &m('fxuser');
        $fxUserTreeMod = &m('fxuserTree');
        $fxuserMod      = &m('fxuser');
        $fxruleMod      = &m('fxrule');
        $fxPhone = !empty($_REQUEST['fxPhone']) ? $_REQUEST['fxPhone'] : '';
        $goods_id = !empty($_REQUEST['goods_id']) ? $_REQUEST['goods_id'] : '';
        $goods_key = !empty($_REQUEST['goods_key']) ? $_REQUEST['goods_key'] : '';
        $goods_num = !empty($_REQUEST['goods_num']) ? $_REQUEST['goods_num'] : '';
        $shippingfee = !empty($_REQUEST['shippingfee']) ? $_REQUEST['shippingfee'] : '0.00';
        $point = !empty($_REQUEST['point']) ? $_REQUEST['point'] : 0;
        $youhui = !empty($_REQUEST['youhui']) ? $_REQUEST['youhui'] : 0;
        $totalMoney = !empty($_REQUEST['totalMoney']) ? $_REQUEST['totalMoney'] : 0;
//        var_dump($totalMoney);die;
        if (empty($fxPhone)){
            $info['discount'] =0.00;
            $info['payMoney'] = $totalMoney + $shippingfee  - $point - $youhui;
            $this->setData($info,$status='1',$message='');
        }
//获取分销人员信息
        $fxuserInfo     = $fxuserMod->getOne(array('cond' => "fx_code = '{$fxPhone}' AND mark = 1"));
        if($fxuserInfo['user_id'] == $this->userId){
            $info['discount'] = 0.00;
            $info['payMoney'] = $totalMoney + $shippingfee- $point - $youhui;
            $this->setData($info,$status=1,$message='');
        }
        if( $fxuserInfo['level'] != 3 ){
            $this->setData(array(), $status = 1, $message = '');
        }


        $discount_rate = $fxuserInfo['discount'];;
        $discount       = ($totalMoney * $discount_rate * 0.01);
        $info['discount']   = $discount;
        $info['payMoney'] = $totalMoney + $shippingfee - $discount - $point - $youhui;
        if ($info['payMoney'] <= 0) {
            $info['payMoney'] = 0.01;
        }
        $info['discount_rate'] = $discount_rate;
        $info['fx_user_id']     = $fxuserInfo['id'];
        //获取分销规则
        $info['rule_id']    = $fxruleMod->getFxRule($fxuserInfo['id']);
        $this->setData($info, $status = 1, $message = '');
    }
    public function getFxDiscount() {
        $fxuserMod      = &m('fxuser');
        $fxruleMod      = &m('fxrule');

        $fxCode         = !empty($_REQUEST['fxPhone'])   ? htmlspecialchars(trim($_REQUEST['fxPhone'])) : '';
        $cart_ids       = !empty($_REQUEST['cart_ids']) ? $_REQUEST['cart_ids'] : '';
        $shippingfee    = !empty($_REQUEST['shippingfee']) ? $_REQUEST['shippingfee'] : 0;
        $point          = !empty($_REQUEST['point'])    ? $_REQUEST['point'] : 0;
        $youhui         = !empty($_REQUEST['youhui'])   ? $_REQUEST['youhui'] : 0;
        $totalMoney     = $this->getOrderMoney($cart_ids);

        if( empty($fxCode) ){
            $info['discount'] = 0.00;
            $info['payMoney'] = $totalMoney + $shippingfee  - $point - $youhui;
            $this->setData($info, $status = 1, $message = '');
        }

        //获取分销人员信息
        $fxuserInfo     = $fxuserMod->getOne(array('cond' => "fx_code = '{$fxCode}' AND mark = 1"));
//        var_dump($fxuserInfo);die;
        if( $fxuserInfo['level'] != 3 ){
            $this->setData(array(), $status = 1, $message = '');
        }

        $discount_rate  = $fxuserInfo['discount'];
        $discount       = ($totalMoney * $discount_rate * 0.01);

        $info['discount']   = $discount;    //推荐用户优惠折扣
        $info['payMoney']   = $totalMoney + $shippingfee - $discount - $point - $youhui;
        if ($info['payMoney'] <= 0) {
            $info['payMoney']   = 0.01;
        };
        $info['discount_rate']  = $discount_rate;
        $info['fx_user_id']     = $fxuserInfo['id'];

        //获取分销规则
        $info['rule_id']    = $fxruleMod->getFxRule($fxuserInfo['id']);
        $this->setData($info, $status = 1, $message = '');
    }
    /**
     * 获取当前站点名称
     * @author wangshuo
     * @date 2018-6-7
     */
    public function storeName($store_id, $auxiliary, $lang_id) {
        $sql = 'select gl.store_name  from  '
                . DB_PREFIX . 'store as g  left join '
                . DB_PREFIX . 'store_lang as gl on g.id = gl.store_id and gl.distinguish= ' . $auxiliary . ' and gl.lang_id= ' . $lang_id . ' where g.id  = ' . $store_id;
        $rs = $this->storeMod->querySql($sql);
        return $rs[0]['store_name'];
    }

    /**
     * 获取英文的规格
     * @author wanyan
     * @date 2017-11-3
     */
    public function getSpec1($sp_key, $lang_id) {


        if ($sp_key) {


            foreach ($sp_key as $k1 => $v1) {
                $sql = "select `item_name` from " . DB_PREFIX . "goods_spec_item_lang where `item_id` = '{$v1}' and  `lang_id` ='{$lang_id}'";
                $spec_1 = $this->storeGoodMod->querySql($sql);
                $spec[] = $spec_1[0]['item_name'];
            }
            $spec_key = implode(':', $spec);

            return $spec_key;
        }
    }

    public function getSpec($sp_key, $lang_id) {


        if ($sp_key) {

            $sp_key = explode('_', $sp_key);
            foreach ($sp_key as $k1 => $v1) {
                $sql = "select `item_name` from " . DB_PREFIX . "goods_spec_item_lang where `item_id` = '{$v1}' and  `lang_id` ='{$lang_id}'";
                $spec_1 = $this->storeGoodMod->querySql($sql);
                $spec[] = $spec_1[0]['item_name'];
            }
            $spec_key = implode(':', $spec);

            return $spec_key;
        }
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
    
    /**
     * 确认订单
     * @author wanyan
     * @date 2017-11-28
     */
    public function comfirm() {
        //语言包

        $this->load($this->shorthand, 'WeChat/goods');
        $a = $this->langData;
        $fxUserMod = &m('fxuser');
        $lang_id = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : '0';
        $prom_id = !empty($_REQUEST['cid']) ? intval($_REQUEST['cid']) : '';
        $goods_id = !empty($_REQUEST['goods_id']) ? $_REQUEST['goods_id'] : '';
        $goods_num = !empty($_REQUEST['goods_num']) ? $_REQUEST['goods_num'] : '';
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : '';
        $goods_key = !empty($_REQUEST['goods_key']) ? $_REQUEST['goods_key'] : '';
        $seller_msg = !empty($_REQUEST['seller_msg']) ? $_REQUEST['seller_msg'] : '';
        $shipping_price = !empty($_REQUEST['shipping_price']) ? (int) $_REQUEST['shipping_price'] : '';
        $user_address_id = !empty($_REQUEST['user_address']) ? $_REQUEST['user_address'] : '';
        $shipping_store_id = !empty($_REQUEST['shipping_store_id']) ? $_REQUEST['shipping_store_id'] : '';
        $discount_rate = !empty($_REQUEST['discount_rate']) ? $_REQUEST['discount_rate'] : '0.00';
        $source = !empty($_REQUEST['source']) ? intval($_REQUEST['source']) : '';
        $fxPhone = !empty($_REQUEST['fxPhone']) ? trim($_REQUEST['fxPhone']) : '';
        $point = !empty($_REQUEST['point']) ? trim($_REQUEST['point']) : '';
        $sendout = !empty($_REQUEST['sendout']) ? htmlspecialchars($_REQUEST['sendout']) : '1';
        $shippingfee = !empty($_REQUEST['shippingfee']) ? htmlspecialchars($_REQUEST['shippingfee']) : 0;
        $id = !empty($_REQUEST['id']) ? htmlspecialchars(trim($_REQUEST['id'])) : 0;
        $price = !empty($_REQUEST['price']) ? $_REQUEST['price'] : '';
        $latlon = !empty($_REQUEST['latlon']) ? $_REQUEST['latlon'] : '';
        $addr_id = !empty($_REQUEST['addr_id']) ? htmlspecialchars(trim($_REQUEST['addr_id'])) : '0';
        $fx_user_id = !empty($_REQUEST['fx_user_id']) ? htmlspecialchars(trim($_REQUEST['fx_user_id'])) :'';
        $rule_id = !empty($_REQUEST['rule_id']) ? htmlspecialchars(trim($_REQUEST['rule_id'])) : '';
        $daifu = !empty($_REQUEST['daifu']) ? htmlspecialchars(trim($_REQUEST['daifu'])) : '';
        $storeCate = $this->getCountryLang($store_id);
        $latlon1 = explode(',', $latlon);
        //地址距离比较
        $userAddressMod = &m('userAddress');
        $sql = "select  distance,longitude,latitude from " . DB_PREFIX . "store where id =" . $store_id;
        $storeInfo = $userAddressMod->querySql($sql);
        $longitude = $storeInfo[0]['longitude'];
        $latitude = $storeInfo[0]['latitude'];
        $sqle = "select latlon from " . DB_PREFIX . "user_address where distinguish=1 and  user_id=" . $this->userId . ' and id =' . $addr_id;
        $userInfo = $userAddressMod->querySql($sqle);

        //地址经纬度转化
        $nowlatlon = $userInfo[0]['latlon'];
        $latlon = explode(',', $nowlatlon );
        $lng = $latlon[1]; //经度
        $lat = $latlon[0]; //纬度
        $latlon=$this->coordinate_switchf($lat,$lng);
        $lng=$latlon['Longitude'];
        $lat=$latlon['Latitude'];
        $distance = $this->getdistance($longitude, $latitude,$lng, $lat);
        $distance = $distance / 1000;

        if ($sendout == 2) {
            if ($distance > $storeInfo[0]['distance']) {
                $this->setData($info = array(), $status = 0, '目前地址不在配送范围');
            }
        }
        if (empty($user_address_id) && $sendout != 1) {

            $this->setData($info = array(), $status = 0, $a['order_address2']);
        }
        $user_address = $this->userAddressMod->getAddress($user_address_id);
        /*  if ($fxPhone) {
          if (!preg_match("/^1[34578]\d{9}$/", $fxPhone) || strlen($fxPhone) != 11) {
          $this->setData($info = array(), $status = 0, $message = '分销手机号格式有误!');
          }
          } */
        if ($source == 3) { //商品促销
            $goodInfo = $this->getGoodInfo($prom_id, $goods_id, $goods_key, $store_id);

        } elseif ($source == 2) { // 团购商品
            $goodInfo = $this->getGroupBuyGoods($prom_id);

        } elseif ($source == 1) {
            $goodInfo = $this->getSecKill($prom_id, $goods_id, $store_id);

        }

        if(!empty($goods_key)){
            $goodsprice = $this->storeGoodItemPriceMod->getSpecPrice($goods_id, $goods_key);
        }else{
            $goodsprice=$this->getGoodsPrice($goods_id);//无规格价格
        }
        //店铺商品打折
        $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $this->storeid;
        $store_arr = $this->storeGoodItemPriceMod->querySql($store_sql);
        $goods_price = $goodsprice * $store_arr[0]['store_discount'];
        $userInfo = $this->userMod->getInfo(array('id' => $this->userId));
        $storeGoodInfo = $this->storeGoodMod->getOne(array('cond' => "`id` = '{$goods_id}'", 'fields' => "*"));
        $rand = $this->buildNo(1);
        $orderNo = date('YmdHis') . $rand[0];
        if (!empty($fxPhone)) {
            //$fxUserInfo = $fxUserMod->getOne(array('cond' => " telephone='{$fxPhone}' ", 'fields' => 'fx_discount'));
            $discount = (($goods_price) * $discount_rate * 0.01);
        } else {
            $discount = 0;
        }
        //生成小票编号
        $number_order = $this->createNumberOrder($store_id);
        //订单数据
        $count = strpos($user_address['address'], "_");
        if($count==false){
            $addressStr=$user_address['address'];
        }else{
            $addressStr = substr_replace($user_address['address'], "", $count, 1);
        }
        $order_amount = (($goods_price * $goods_num) + $shippingfee - $discount - $price);
        if ($order_amount <= 0) {
            $order_amount = 0.01;
        }
        $userMod = &m('user');
        $uSql = "SELECT * FROM " . DB_PREFIX . 'user WHERE id=' . $this->userId;
        $uData = $userMod->querySql($uSql);
        if (empty($user_address)) {
            $user_address['phone'] = $uData[0]['phone'];
            $user_address['name'] = $uData[0]['phone'];
        }
        if ($sendout == 1) {
            $addressStr = '自提';
        }
        $genaral = array(
            'orderNo' => $orderNo,
            'store_id' => $store_id,
            'sendout' => $sendout, // 1派送 2自提
            'store_name' => $this->storeMod->getNameById($store_id, $lang_id),
            'buyer_id' => $this->userId,
            'buyer_name' => $user_address['name'],
            'buyer_email' => $userInfo['email'],
            'shipping_fee' => $shippingfee,
            'buyer_address' => $addressStr,
            'prom_id' => $prom_id,
            'prom_type' => $source,
            'goods_num' => $goods_num,
            'storeCate' => $storeCate,
            'buyer_phone' => $user_address['phone'],
            'discount_rate' => $discount_rate,
            'fxPhone' => $fxPhone,
            'goods_id' => $goods_id,
            'discount' => $discount,
            'goods_name' => addslashes($this->storeGoodMod->getGoodsName($goods_id, $lang_id)),
            'goods_price' => $storeGoodInfo['market_price'],
            'goods_image' => $this->getGoodImg($goods_id, $store_id),
            'goods_pay_price' => $goods_price,
            'spec_key_name' => $this->getSpec($goods_key, $lang_id),
            'spec_key' => $goods_key,
            'goods_type' => 0,
            'seller_msg' => $seller_msg,
            'fx_code' => '',
            'shipping_price' => $shipping_price,
            'shipping_store_id' => $shipping_store_id,
            'order_amount' => $order_amount,
            'goods_amount' => $goods_price * $goods_num,
            'number_order' => $number_order, //生成小票编号
            'price' => $price,
           'fx_user_id' =>$fx_user_id,
           'rule_id' => $rule_id,
            'good_id'=>$storeGoodInfo['goods_id'],
            'deduction'=>$storeGoodInfo['deduction']

        );
        $rs = $this->genOrder($source, $genaral, $goodInfo, $lang_id);
        if($this->userId==5640){
            var_dump($genaral);eixt;
        }
        if ($rs) {
            if ($source == 1) {
                /* $sql = "update " . DB_PREFIX . "spike_activity SET goods_num = goods_num -1 where `id` ='{$prom_id}'";
                  $rid = $this->spikeActivityMod->sql_b_spec($sql); */
            } elseif ($source == 2) {
                $sql = "update " . DB_PREFIX . "goods_group_buy SET buy_num = buy_num +'{$goods_num}' where `id` ='{$prom_id}'";
                $rid = $this->spikeActivityMod->sql_b_spec($sql);
                $sql = "update " . DB_PREFIX . "goods_group_buy SET order_num = order_num + 1 where `id` ='{$prom_id}'";
                $cid = $this->spikeActivityMod->sql_b_spec($sql);
            }
          /*  if ($point) {
                $this->getPointPrice($orderNo, $price, $point);
            }*/
            if ($id) {
                $this->getCouponPrice($orderNo, $id);
            }
            if($daifu ==1){
                  $info['url'] = "?app=fxPayment&act=index&storeid={$store_id}&orderid={$rs}&lang={$lang_id}"; 
               }else{
                  $info['url'] = "?app=jsapi&act=jsapi&order_id={$orderNo}&storeid={$store_id}&lang={$lang_id}";
               }
            $this->setData($info, $status = 1, $a['order_Order_success']);
        } else {
            $this->setData($info = array(), $status = 0, $a['order_Order_failure']);
        }
    }
    public function getStoreCate($storeId){
        $sql="select store_cate_id from ".DB_PREFIX.'store where id='.$storeId;
        $storeMod=&m('store');
        $storeInfo=$storeMod->querySql($sql);
        return $storeInfo[0]['store_cate_id'];
    }
    //获取商品id

    function  getGoodId($id){
        $sql="select goods_id from ".DB_PREFIX.'store_goods where id='.$id;
        $goodInfo=$this->orderDetailMod->querySql($sql);
        return $sql;

    }


    //腾讯转百度坐标转换
    function coordinate_switchf($a, $b){
        $x = (double)$b ;
        $y = (double)$a;
        $x_pi = 3.14159265358979324;
        $z = sqrt($x * $x+$y * $y) + 0.00002 * sin($y * $x_pi);
        $theta = atan2($y,$x) + 0.000003 * cos($x*$x_pi);
        $gb = number_format($z * cos($theta) + 0.0065,6);
        $ga = number_format($z * sin($theta) + 0.006,6);

        return array(
            'Latitude'=>$ga,
            'Longitude'=>$gb
        );
    }

    //转化距离
    function getdistance($lng1, $lat1, $lng2, $lat2) {
        // 将角度转为狐度
        $radLat1 = deg2rad($lat1); //deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;
        return $s;
    }

    //获取无规格价格
    public function getGoodsPrice($id){
        $sql="select * from bs_store_goods where id =".$id;
        $res = $this->orderMod->querySql($sql);
        return $res[0]['shop_price'];
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

    //优惠劵
    public function getCouponPrice($order_id, $id) {
        $this->load($this->shorthand, 'userCenter/userCenter');
        $a = $this->langData;
        $userMod = &m('user');
        $pointSiteMod = &m('point');
        $storePointMod = &m('storePoint');
        $storeMod = &m('store');
        $curMod = &m('currency');
        $couponMod = &m('coupon');
        $userCounponMod = &m('userCoupon');
        $user_id = $this->userId;
        $user_info = $userMod->getOne(array("cond" => "id=" . $user_id));
        $order_info = $this->orderMod->getOne(array("cond" => "order_sn=" . $order_id));
        $store_id = $order_info['store_id'];
        //获取订单总金额
        $totalMoney = $order_info['order_amount']; //原订单价格
        $sql = "select * from " . DB_PREFIX . "coupon where id=" . $id;
        $cData = $couponMod->querySql($sql);
        $order_price = $totalMoney - $cData[0]['discount'];
        $order_arr = array(
            'pd_amount' => 0.00,
            'order_amount' => $order_price,
            'cp_amount' => $cData[0]['discount']
        );
        $order_cond = array(
            'order_sn' => $order_id
        );
        $order_res = $this->orderMod->doEditSpec($order_cond, $order_arr);
        if ($order_res) {
            $where = " c_id=" . $id . " and user_id=" . $this->userId;
            $res = $userCounponMod->doDrops($where);
            return $order_res;
        } else {
            $this->setData(array(), $status = 0, $a['saveAdd_fail']);
        }
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
     * 不同活动数据生成订单
     */
    public function genOrder($source, $genInfo, $goodInfo, $lang_id) {
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
            'order_from' => 2,
            'buyer_address' => $genInfo['buyer_address'],
            'order_amount' => $genInfo['order_amount'],
            'discount' => $genInfo['discount'],
            'fx_phone' => $genInfo['fxPhone'],
            'buyer_phone' => $genInfo['buyer_phone'],
            'add_time' => time(),
            'seller_msg' => $genInfo['seller_msg'],
            'sendout' => $genInfo['sendout'],
            'goods_amount' => $genInfo['goods_amount'],
            'number_order' => $genInfo['number_order'], //生成小票编号
            'sub_user' => 2,
        );
        $insert_sub_data = array(
            'order_id' => $genInfo['orderNo'],
            'store_id' => $genInfo['store_id'],
            'buyer_id' => $genInfo['buyer_id'],
            'prom_id' => $genInfo['prom_id'],
            'prom_type' => $genInfo['prom_type'],
            'order_state' => 10,
            'add_time' => time(),
            'goods_type' => 1,
            'goods_id' => $genInfo['goods_id'],
            'goods_name' => $genInfo['goods_name'],
            'goods_price' => $genInfo['goods_price'],
            'goods_num' => $genInfo['goods_num'],
            'goods_image' => $genInfo['goods_image'],
            'goods_pay_price' => $genInfo['goods_pay_price'],
            'spec_key_name' => $genInfo['spec_key_name'],
            'spec_key' => $genInfo['spec_key'],
            'shipping_store_id' => $genInfo['shipping_store_id'],
            'shipping_price' => $genInfo['shipping_fee'],
            'good_id'=>$genInfo['good_id'],
            'deduction'=>$genInfo['deduction']
        );

        if ($source == 3) { // 商品促销
            if (!empty($genInfo['fxPhone'])) {
                //$fxUserInfo = $fxUserMod->getOne(array('cond' => " telephone='{$genInfo['fxPhone']}' and store_cate ='{$genInfo['storeCate']}'", 'fields' => 'fx_discount'));
                $discount = (($goodInfo['discount_price'] * $genInfo['goods_num'])) * $genInfo['discount_rate'] * 0.01;
            }
            // 先插入主订单数据
            $insert_main_data['goods_amount'] = ($goodInfo['discount_price'] * $genInfo['goods_num']);
            $insert_main_data['order_amount'] = ($goodInfo['discount_price'] * $genInfo['goods_num']) + $genInfo['shipping_fee'] - $discount - $genInfo['price'];
            $insert_main_data['discount'] = $discount;
            $insert_main_data['fx_discount_rate'] = $genInfo['discount_rate'];
            // 插入子表信息
            $insert_sub_data['prom_type'] = 3;
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
            $insert_main_data['order_amount'] = ($goodInfo['group_goods_price'] * $genInfo['goods_num']) + $genInfo['shipping_fee'] - $discount - $genInfo['price'];
            $insert_main_data['discount'] = $discount;
            $insert_main_data['fx_discount_rate'] = $genInfo['discount_rate'];
            // 插入子表信息
            $insert_sub_data['prom_type'] = 2;
            $insert_sub_data['goods_id'] = $goodInfo['goods_id'];
            $insert_sub_data['goods_name'] = addslashes(stripslashes($goodInfo['goods_name']));
            $insert_sub_data['goods_price'] = $goodInfo['goods_price'];
            $insert_sub_data['goods_image'] = $goodInfo['original_img'];
            $insert_sub_data['goods_pay_price'] = $goodInfo['group_goods_price'];
            $insert_sub_data['spec_key_name'] = $this->getSpec($goodInfo['goods_spec_key'], $lang_id);
            $insert_sub_data['spec_key'] = $goodInfo['goods_spec_key'];
        } elseif ($source == 1) {
            if (!empty($genInfo['fxPhone'])) {
                //$fxUserInfo = $fxUserMod->getOne(array('cond' => " telephone='{$genInfo['fxPhone']}' and store_cate ='{$genInfo['storeCate']}'", 'fields' => 'fx_discount'));
                $discount = (($goodInfo['price'] * $genInfo['goods_num'])) * $genInfo['discount_rate'] * 0.01;
            }
            // 先插入主订单数据
            $insert_main_data['goods_amount'] = ($goodInfo['price'] * $genInfo['goods_num']);
            $insert_main_data['order_amount'] = ($goodInfo['price'] * $genInfo['goods_num']) + $genInfo['shipping_fee'] - $discount - $genInfo['price'];
            $insert_main_data['discount'] = $discount;
            $insert_main_data['fx_discount_rate'] = $genInfo['discount_rate'];

            // 插入子表信息
            $insert_sub_data['prom_type'] = 1;
            $insert_sub_data['goods_id'] = $goodInfo['store_goods_id'];
            $insert_sub_data['goods_name'] = addslashes(stripslashes($goodInfo['goods_name']));
            $insert_sub_data['goods_price'] = $goodInfo['o_price'];
            $insert_sub_data['goods_image'] = $goodInfo['goods_img'];
            $insert_sub_data['goods_pay_price'] = $goodInfo['price'];
            $insert_sub_data['spec_key_name'] = $genInfo['spec_key_name'];
            $insert_sub_data['spec_key'] = $genInfo['spec_key'];
        }
        if ($insert_main_data['order_amount'] <= 0) {
            $insert_main_data['order_amount'] = 0.01;
        }
        // var_dump($goodInfo['discount_price']);die;

        $main_rs = $this->orderMod->doInsert($insert_main_data);
        // 先插入子订单
        if ($main_rs) {
            if (!empty($genInfo['fx_user_id']) && !empty($genInfo['rule_id'])){
                $userId = $this->userId;
                $fxUserMod = &m('fxuser');
                $fx_info = $fxUserMod->getOne(array("cond" => "user_id='" . $userId . "'"));
                if ($fx_info['fx_code'] !== $genInfo['fxPhone']){
                    $fxOrderData = array(
                        'order_id' => $main_rs,
                        'order_sn' => $genInfo['orderNo'],
                        'source' => 2,
                        'user_id' => $this->userId,
                        'fx_user_id' => $genInfo['fx_user_id'],
                        'rule_id' => $genInfo['rule_id'],
                        'store_cate' => $genInfo['storeCate'],
                        'store_id' => $genInfo['store_id'],
                        'add_time' => time(),
                        'add_user' => $this->userId,
                        'pay_money' => $genInfo['order_amount']
                    );
                    $fxOrderMod =& m('fxOrder');
                    $fxUserAccountMod = &m('fxUserAccount');
                    $fxOrderMod->doInsert($fxOrderData);
                    $fxUserAccountMod->addFxUser($genInfo['fx_user_id'], $this->userId);
                }
            }
            $rs = $this->orderDetailMod->doInsert($insert_sub_data);

            return $main_rs;
        } else {
            return 0;
        }
    }

    /**
     * 获取促销商品的信息
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
     */
    public function getGroupBuyGoods($prom_id) {
        $sql = "select * from " . DB_PREFIX . "goods_group_buy where `id` = '{$prom_id}'";
        $rs = $this->storeMod->querySql($sql);
        return $rs[0];
    }

    /**
     * 获取商品秒杀的商品信息
     */
    public function getSecKill($prom_id, $goods_id, $store_id) {
        $query = array(
            'cond' => "`id` ='{$prom_id}' and `store_goods_id` = '{$goods_id}' and `store_id` = '{$store_id}'",
            'fields' => "*"
        );
        $rs = $this->spikeActivityMod->getOne($query);
        return $rs;
    }

    /*
     * 积分兑换优惠处理
     * @auhtor lee
     * @date 2018-5-7 15:35:33
     */

    public function getPointPrice($order_id, $price, $point) {
        $this->load($this->shorthand, 'userCenter/userCenter');
        $a = $this->langData;
        $userMod = &m('user');
        $pointSiteMod = &m('point');
        $storePointMod = &m('storePoint');
        $storeMod = &m('store');
        $curMod = &m('currency');
        $user_id = $this->userId;
        $user_info = $userMod->getOne(array("cond" => "id=" . $user_id));
        $order_info = $this->orderMod->getOne(array("cond" => "order_sn=" . $order_id));


        $store_id = $order_info['store_id'];
        //获取订单总金额
        $totalMoney = $order_info['order_amount']; //原订单价格
        //获取最大积分支付比例
        $point_price_site = $pointSiteMod->getOne(array("cond" => "1=1"));
        $store_point_site = $storePointMod->getOne(array("cond" => "store_id=" . $order_info['store_id']));
        $point_price_site['point_price'] = empty($store_point_site) ? 0 : $store_point_site['point_price'];
        if ($point_price_site) {
            $point_price = $point_price_site['point_price'] * $totalMoney / 100; //积分兑换最大金额
            $rmb_point = $point_price_site['point_rate']; //积分和RMB的比例
        } else {
            $point_price = 0;
            $rmb_point = 0;
        }
        //获取当前店铺币种以及兑换比例
        $store_info = $storeMod->getOne(array("cond" => "id=" . $store_id));
        //获取当前币种和RMB的比例
        $rate = $curMod->getCurrencyRate($store_info['currency_id']);
        //积分和RMB的比例
        if ($rate) {
            // $price_rmb = ceil(($point_price*$rate)/$rmb_point);
            //最大比例使用积分
            $price_rmb_point = ceil($point_price * $rate * $rmb_point);
        }

        $last_price = ($point / $point_price_site['point_rate']) / $rate;
        $order_price = $totalMoney - $last_price;

        $order_arr = array(
            'pd_amount' => $price,
        );

        $order_cond = array(
            'order_sn' => $order_id
        );

        $order_res = $this->orderMod->doEditSpec($order_cond, $order_arr);

        if ($order_res) {
            //扣除用户积分
            $user_point = $user_info['point'] - $point;
            $userMod->doEdit($user_id, array("point" => $user_point));
            //积分日志
            $logMessage = "订单：" . $order_id . " 使用：" . $point . "睿积分";
            $this->addPointLog($user_info['phone'], $logMessage, $user_id, 0, $point, $order_id);
            return $order_res;
        } else {
            $this->setData(array(), $status = 0, $a['saveAdd_fail']);
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
            'userid' => $userid
        );
        if ($order_sn) {
            $logData['order_sn'] = $order_sn;
        }
        $pointLogMod = &m("pointLog");
        $pointLogMod->doInsert($logData);
    }

}
