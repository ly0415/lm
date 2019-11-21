<?php
/**
 * 商品订单接口控制器
 * @author:tangp
 * @date:2018-09-03
 */
class GoodsOrderApp extends BasePhApp
{
    private $orderMod;
    private $orderGoodsMod;
    private $userAddressMod;
    public $storeMod;
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
    private $rechargeAmountMod;
    private $amountLogMod;
    private $areaGoodMod;
    private  $goodsSpecPriceMod;


    public function __construct()
    {
        parent::__construct();
        $this->orderMod = &m('order');
        $this->orderGoodsMod = &m('orderGoods');
        $this->userAddressMod = &m('userAddress');
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
        $this->rechargeAmountMod = &m('rechargeAmount');
        $this->amountLogMod = &m('amountLog');
        $this->areaGoodMod = &m('areaGood');
        $this->goodsSpecPriceMod=&m('goodsSpecPrice');
    }

    public function __destruct()
    {

    }
    /**
     * 检测Cartid
     * @author tangp
     * @date 2018-12-12
     */
     public function getCartId()
     {
        $cart_id = !empty($_REQUEST['cart_ids']) ? htmlspecialchars($_REQUEST['cart_ids']) : '';
        $sql = "select `id` from " . DB_PREFIX . "cart where `id` = '{$cart_id}'";
        $info = $this->cartMod->querySql($sql);
        if ($info[0]['id']) {
            $this->setData(array(),1,'');
        }else{
            $this->setData(array(),0,'订单已被提交，请勿重复提交！');
        }
     }
    /**
     * 商品购买详情
     * @author:tangp
     * @date:2018-09-03
     */
    public function payDetails()
    {
        $lang              = !empty($_REQUEST['lang_id'])  ? intval($_REQUEST['lang_id']) : $this->lang_id;
        $store_id          = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : $this->store_id;
        $store_goods_id    = !empty($_REQUEST['store_goods_id']) ? intval($_REQUEST['store_goods_id']) : '278';
        $good_keys         = !empty($_REQUEST['item_id']) ? $_REQUEST['item_id'] : 190;
        $goods_num         = !empty($_REQUEST['goods_num']) ? intval($_REQUEST['goods_num']) : '1';
        $goods_price       = !empty($_REQUEST['goods_price']) ? $_REQUEST['goods_price'] : '20.00';
        $shipping_price    = !empty($_REQUEST['shipping_price']) ? $_REQUEST['shipping_price'] : '0.00';
        $shipping_store_id = !empty($_REQUEST['shipping_store_id']) ? intval($_REQUEST['shipping_store_id']) : '47';
        $order_from        = !empty($_REQUEST['order_from']) ? intval($_REQUEST['order_from']) : '';
        $cid               = !empty($_REQUEST['cid']) ? intval($_REQUEST['cid']) : '26';
        $source            = !empty($_REQUEST['source']) ? intval($_REQUEST['source']) : '3';
        $auxiliary         = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon            = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : 0;
        $data = array(
            'goods_id' => $store_goods_id,
            'goods_keys' => $good_keys,
            'goods_num' => $goods_num,
            'goods_price' => $goods_price,
            'shipping_price' => $shipping_price,
            'shipping_store_id' => $shipping_store_id,
            'order_from' => $order_from,
            'source' => $source,
            'cid' => $cid
        );

//        var_dump($info);die;
        //睿积分兑换比例
        $pointSiteMod = &m('point');
        $point_price_site = $pointSiteMod->getOne(array("cond" => "1=1"));
        $rate = $point_price_site['point_rate'];
        $addr_id = !empty($_REQUEST['addr_id']) ? htmlspecialchars(trim($_REQUEST['addr_id'])) : '0';
        if ($addr_id) {
            $where = ' and id=' . $addr_id;
        } else {
            $where = ' and default_addr =1';
        }
        $addrSql = "select * from " . DB_PREFIX . 'user_address where user_id=' . $this->userId . $where;

        $userAddress = $this->userAddressMod->querySql($addrSql); // 获取用户的地址

        if ($addr_id == '0') {
            $addr_id = $userAddress[0]['id'];
        }
        $addresss = explode('_', $userAddress[0]['address']);
        $count = strpos($userAddress[0]['address'], "_");
        if($count==false){
            $str=$userAddress[0]['address'];
        }else{
            $str = substr_replace($userAddress[0]['address'], "", $count, 1);
        }


        foreach ($userAddress as $k => $v) {
            $userAddress[$k]['addressDetail'] = $str;
        }

        if ($data['goods_keys']) {
            $goods_keys_name = $this->getSpec1($data['goods_keys'],$lang);
        }
        $totalMoney = ($data['goods_price'] * $data['goods_num']);
        $total = $totalMoney;
        $data['goods_keys'] = implode('_', $data['goods_keys']);
        $user_info = $this->userMod->getOne(array("cond"=>"id=" . $this->userId));
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
        foreach ($cData as $key => $val) {
            $cData[$key]['expire'] = 1;
            $cData[$key]['total'] = $ctData[0]['ctotal'];
        }

        //未过期优惠券
        $wSql = "select * from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and end_time > ' . time() . ' and start_time < ' . time();
        $wtSql = "select count(*) as wtotal from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and end_time > ' . time() . ' and start_time < ' . time();
        $wData = $this->cartMod->querySql($wSql);
        $wtData = $this->cartMod->querySql($wtSql);
        foreach ($wData as $key => $val) {
            $wData[$key]['expire'] = 0;
            $wData[$key]['total']  = $wtData[0]['wtotal'];
        }
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

        $rechargeData=$this->userMod->getOne(array('cond'=>"`id` = '{$this->userId}' and mark=1",'fields'=>'recharge_id'));


        $percentData=$this->rechargeAmountMod->getOne(array('cond'=>"`id` = '{$rechargeData['recharge_id']}'",'fields'=>'percent'));
        $percentData['percent']=empty($percentData['percent']) ? 0: $percentData['percent'];
        $point_price_site['point_price']=$point_price_site['point_price']+$percentData['percent'];
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
//            $price_rmb_point = ceil($point_price * $rate * $rmb_point);
            $ruiPrice=$point_price * $rate * $rmb_point;
            if($ruiPrice < 1){
                $ruiPrice=0;
            }

            $price_rmb_point = ceil($ruiPrice);
            if ($price_rmb_point > $user_info['point']) {
                $point_price = $user_info['point'] * $rmb_point / 100;
                $price_rmb_point = ceil($point_price * $rate * $rmb_point);
            }
        }

        $maxAccount = number_format($point_price, 2, '.', '');
        $maxPoint   = $price_rmb_point;
        $ruiData=array('maxAccount'=>$maxAccount,'maxPoint'=>$maxPoint);
        $store_name = $this->storeName($store_id, $auxiliary, $lang);

        $shipping_store_name = $this->storeMod->getNameById($data['shipping_store_id'],$lang);
        $sku = $this->storeGoodMod->getSku($data['goods_id']);
        $goods_name = $this->storeGoodMod->getGoodsName($data['goods_id'],$lang);
//        var_dump($goods_name);die;
        $original_img = $this->storeGoodMod->getStoreGoodImg($data['goods_id'],$lang);

        $total = number_format($totalMoney,2);
        $langData = array(
            $this->langData->project->add_address,
            $this->langData->project->wait_submit_order,
            $this->langData->project->distribution_style,
            $this->langData->project->by_message,
            $this->langData->project->discount_code,
            $this->langData->project->discount_money,
            $this->langData->project->available,
            $this->langData->project->integral_offset,
            $this->langData->project->total_start,
            $this->langData->project->total_end,
            $this->langData->public->total,
            $this->langData->project->include_freight,
            $this->langData->public->yuan,
            $this->langData->project->submit_order
        );
        $order_Data = array(
            'langData'   => $langData,
            'goodsData'  => array(
                'goods_name' => $goods_name,
                'store_name' => $store_name,
                'shipping_store_name' => $shipping_store_name,
                'sku' => $sku,
                'original_img' => $original_img,
                'good_num' => $data['goods_num'],
                'goods_price' => $data['goods_price'],
                'money' =>number_format($total - $point_price, 2),
                'goods_key_name' => $goods_keys_name
            ),
            'userAddress' => $userAddress,
            'couponData'  => $cData,
            'expireCouponData' => $wData,
            'ruiData' => $ruiData,
            'storeName' => $store_name
        );

        $this->setData($order_Data,1,'');
    }

    /**
     * 单条下订单
     * @author:tangp
     * @date:2018-09-19
     */
    public function confirmOrder()
    {
        $fxUserMod = &m('fxuser');
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        $prom_id = !empty($_REQUEST['cid']) ? intval($_REQUEST['cid']) : '';
        $goods_id = !empty($_REQUEST['goods_id']) ? $_REQUEST['goods_id'] : '';
        $goods_num = !empty($_REQUEST['goods_num']) ? $_REQUEST['goods_num'] : '';
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
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
        $storeCate = $this->getCountryLang($store_id);

        $latlon1 = explode(',', $latlon);
        //地址距离比较
        $userAddressMod = &m('userAddress');
        $sql = "select  distance,longitude,latitude from " . DB_PREFIX . "store where id =" . $store_id;
        $storeInfo = $userAddressMod->querySql($sql);
        $longitude = $storeInfo[0]['longitude'];
        $latitude = $storeInfo[0]['latitude'];
        $sqle = "select latlon from " . DB_PREFIX . "user_address where  user_id=" . $this->userId . ' and id =' . $addr_id;
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

            $this->setData($info = array(), $status = 0, '地址有误');
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
        $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $store_id;
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
//            'number_order' => $number_order, //生成小票编号
            'price' => $price
        );
        $rs = $this->genOrder($source, $genaral, $goodInfo, $lang_id);
        $orderNo1 = array(
            'orderNo' => $orderNo
        );
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
            if ($point) {
                $this->getPointPrice($orderNo, $price, $point);
            }
            if ($id) {
                $this->getCouponPrice($orderNo, $id);
            }
            $this->setData($orderNo1, 1, '下单成功');
        } else {
            $this->setData('', 0, '下单失败');
        }
    }
    //获取无规格价格
    public function getGoodsPrice($id){
        $sql="select * from bs_store_goods where id =".$id;
        $res = $this->orderMod->querySql($sql);
        return $res[0]['shop_price'];
    }
    /**
     * 多条下订单
     * @author:tangp
     * @date:2018-09-03
     */
    public function makeOrder()
    {
        $fxUserMod = &m('fxuser');
        $cart_ids = !empty($_REQUEST['cart_ids']) ? htmlspecialchars($_REQUEST['cart_ids']) : '10495,10502';
        $seller_msg = !empty($_REQUEST['seller_msg']) ? htmlspecialchars($_REQUEST['seller_msg']) : '';
        // $seller_msg = explode(',',$seller_msg);
        $user_address = !empty($_REQUEST['user_address']) ? htmlspecialchars($_REQUEST['user_address']) : '';
        //$gift_id= !empty($_REQUEST['gift_id']) ? intval($_REQUEST['gift_id']) : '';
        $fxPhone = !empty($_REQUEST['fxPhone']) ? $_REQUEST['fxPhone'] : '';
        $fx_user_id = !empty($_REQUEST['fx_user_id']) ? $_REQUEST['fx_user_id'] : '';
        $rule_id = !empty($_REQUEST['rule_id']) ? $_REQUEST['rule_id'] : '';
        $storeid = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : $this->store_id;
        $lang = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : $this->lang_id;
        $discount_rate = !empty($_REQUEST['discount_rate']) ? intval($_REQUEST['discount_rate']) : '';
        $price = !empty($_REQUEST['price']) ? $_REQUEST['price'] : '';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $sendout = !empty($_REQUEST['sendout']) ? htmlspecialchars($_REQUEST['sendout']) : '1';
        $address = !empty($_REQUEST['address']) ? htmlspecialchars($_REQUEST['address']) : '';
        $shippingfee = !empty($_REQUEST['shippingfee']) ? htmlspecialchars($_REQUEST['shippingfee']) : '';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $id = !empty($_REQUEST['id']) ? htmlspecialchars(trim($_REQUEST['id'])) : 0;
        $point = !empty($_REQUEST['point']) ? $_REQUEST['point'] : '';
        $addr_id = !empty($_REQUEST['addr_id']) ? htmlspecialchars(trim($_REQUEST['addr_id'])) : '0';
        $pei_time = !empty($_REQUEST['pei_time']) ? $_REQUEST['pei_time'] : 0;
        $pei_time=strtotime($pei_time);
        if (empty($user_address) && $sendout != 1) {
            $this->setData($info = array(), $status = 0, '地址没有');
        }
//        if ($fxPhone) {
//            if (!preg_match("/^1[34578]\d{9}$/", $fxPhone) || strlen($fxPhone) != 11) {
//                $this->setData($info = array(), $status = 0, '优惠码没有');
//            }
//        }

        $latlon1 = explode(',', $latlon);
        //地址距离比较
        $userAddressMod = &m('userAddress');
        $sql = "select  distance,longitude,latitude  from " . DB_PREFIX . "store where id =" . $storeid;
        $storeInfo = $userAddressMod->querySql($sql);
        $longitude = $storeInfo[0]['longitude'];
        $latitude = $storeInfo[0]['latitude'];
        $sqle = "select latlon from " . DB_PREFIX . "user_address where  user_id=" . $this->userId . ' and id =' . $addr_id;
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
                $this->setData(array(), 0, '目前地址不在配送范围');
            }
        }
        $rand = $this->buildNo(1);
        $orderNo = date('YmdHis') . $rand[0];
        $sql = "select c.user_id,c.store_id,c.shipping_store_id,SUM(c.goods_price*goods_num) as goods_amount,u.email,u.username from " . DB_PREFIX . "cart as c  LEFT JOIN " . DB_PREFIX . "user as u ON c.user_id = u.id  where c.`id` in  ({$cart_ids}) ";
//        $sql = "select c.user_id,c.store_id,SUM(c.goods_price*goods_num) as goods_amount,u.email,u.username from " . DB_PREFIX . "cart as c LEFT JOIN " . DB_PREFIX . "store_lang as s ON c.store_id =s.store_id  and  s.lang_id = " . $this->langid . "  LEFT JOIN " . DB_PREFIX . "user as u ON c.user_id = u.id  where c.`id` in  ({$cart_ids}) ";
        $orderInfo = $this->cartMod->querySql($sql);
        $orderInfo[0]['store_name'] = $this->storeMod->getNameById($orderInfo[0]['shipping_store_id'], $this->lang_id);
        $shipping_fee = $this->getShippingPrice($cart_ids);
        $user_address = $this->userAddressMod->getAddress($user_address);
        $goodsInfo = $this->cartMod->getGoodByCartId($cart_ids);
        if (!empty($fxPhone)) {
            //$fxUserInfo = $fxUserMod->getOne(array('cond' => " telephone='{$fxPhone}' ", 'fields' => 'fx_discount'));
            $discount = (($orderInfo[0]['goods_amount']) * $discount_rate * 0.01);
        } else {
            $discount = 0;
        }
        //生成小票编号
        $number_order = $this->createNumberOrder($storeid);

        $order_amount = $orderInfo[0]['goods_amount'] + $shippingfee - $discount - $price;
        if ($order_amount <= 0) {
            $order_amount = 0.01;
        }
        //收货信息

        $count = strpos($user_address['address'], "_");
        if($count==false){
            $addressStr=$user_address['address'];
        }else{
            $addressStr = substr_replace($user_address['address'], "", $count, 1);
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

        /* desc  :记录参数集合
          auther:luffy
          date  :2018-09-11
       */
        $systemErrorLogMod =  &m('systemErrorLog');
        $systemErrorLogMod -> doInsert(array(
            'user_id'           => $this->userId,
            'request_params'    => serialize($_REQUEST),
            'deal_params'       => serialize($orderInfo),
            'important_params'  => $order_amount.'='.$orderInfo[0]['goods_amount'].'+'.$shippingfee.'-'.$discount.'-'.$price.'&&&'.$discount.'='.$orderInfo[0]['goods_amount'].'*'.$discount_rate.'*0.01',
            'add_time'          => time()
        ));
        /* if(empty($invalid)){
             $this->setData(array(),'0','商品库存不足');
         }*/
        // 先插入主订单
        $insert_main_data = array(
            'order_sn' => $orderNo,
            'store_id' => $orderInfo[0]['shipping_store_id'],
            'sendout' => $sendout, // 1自提2派送3邮寄托运
            'store_name' => $orderInfo[0]['store_name'],
            'buyer_id' => $orderInfo[0]['user_id'],
            'buyer_name' => addslashes($user_address['name']),
            'buyer_email' => $orderInfo[0]['email'],
            'goods_amount' => $orderInfo[0]['goods_amount'],
            'order_amount' => $order_amount,
            'shipping_fee' => $shippingfee,
            'order_state' => 10,
            'order_from' => 2,
            'buyer_address' => $addressStr,
            'buyer_phone' => $user_address['phone'],
            //'gift_id' =>$gift_id,
            'discount' => $discount,
            'fx_discount_rate' => $discount_rate,
            'fx_phone' => $fxPhone,
            'add_time' => time(),
//            'number_order' => $number_order, //生成小票编号
            'seller_msg' => $seller_msg, //订单的留言
            'sub_user' => 2,
            'pei_time'=>$pei_time,
            'is_source'=>1,
            'fx_user_id'=>$fx_user_id
        );

        $count = count($goodsInfo);
        $discount = round($discount / $count, 2);
        try {
            //事务开始
            $this->orderMod->begin();
            //生成新的订单表
            $createOrderRes = $this->orderMod->createOrder($insert_main_data,2);

            $main_rs = $this->orderMod->doInsert($insert_main_data);
            if (empty($main_rs) || empty($createOrderRes)) {
                //事务回滚
                $this->orderMod->rollback();
                $this->setData(array(), 0, '下单失败');
            } else {
                //事务提交
                $this->orderMod->commit();
            }
        } catch (Exception $e) {
            //事务回滚
            $this->orderMod->rollback();
            writeLog($e->getMessage());
            $this->setData(array(), 0, '下单失败');
        }
        //生成2维码
        $code = $this->goodsZcode($storeid, $lang, $main_rs);
        $cond['order_url'] = $code;
        $urldata = array(
            "table" => "order",
            'cond' => 'order_id = ' . $main_rs,
            'set' => "order_url='" . $code . "'",
        );
        $ress = $this->orderMod->doUpdate($urldata);
        $orderNo1 = array(
            'orderNo' => $orderNo
        );
        // 先插入子订单
        if ($main_rs) {
            foreach ($goodsInfo as $k => $v) {
                $insert_sub_data = array(
                    'order_id' => $orderNo,
                    'goods_id' => $v['goods_id'],
                    'goods_name' => addslashes(stripslashes($v['goods_name'])),
                    'goods_price' => $v['market_price'],
                    'goods_num' => $v['goods_num'],
                    'goods_image' => $this->getGoodImg($v['goods_id'], $v['store_id']),
                    'goods_pay_price' => $v['goods_price'],
                    'spec_key_name' => $v['spec_key_name'],
                    'spec_key' => $v['spec_key'],
                    'store_id' => $v['store_id'],
                    'buyer_id' => $v['user_id'],
                    'goods_type' => 0,
                    'order_state' => 10,
                    'fx_code' => $v['fx_code'],
                    'discount' => ($v['goods_price'] + $shippingfee) * ($fxUserInfo['fx_discount']) * 0.01,
                    'discount_rate' => $fxUserInfo['fx_discount'],
                    'shipping_price' => $v['shipping_price'],
                    'shipping_store_id' => $v['shipping_store_id'],
                    'add_time' => time(),
                    'good_id'=>$this->getGoodId($v['goods_id']),
                    'deduction'=>$this->getDeduction($v['goods_id'])
                );
                $rs[] = $this->orderDetailMod->doInsert($insert_sub_data);
            }
            $rs = array_filter($rs);
//            $store_cate = $this->getStoreCate($goodsInfo[0]['store_id']);
//            if (!empty($fx_user_id) && !empty($rule_id)) {
//                $userId = $this->userId;
//                $fx_info = $fxUserMod->getOne(array("cond" => "user_id='" . $userId . "'"));
//                if ($fx_info['fx_code'] !== $fxPhone){
//                    $fxOrderData = array(
//                        'order_id'   => $main_rs,
//                        'order_sn'   => $orderNo,
//                        'source'     => 4,
//                        'user_id'    => $this->userId,
//                        'fx_user_id' => $fx_user_id,
//                        'rule_id'    => $rule_id,
//                        'store_cate' => $store_cate,
//                        'store_id'   => $goodsInfo[0]['store_id'],
//                        'add_time'   => time(),
//                        'add_user'   => $this->userId,
//                        'pay_money'  => $order_amount
//                    );
//                    $fxOrderMod = &m('fxOrder');
//                    $fxUserAccountMod = &m('fxUserAccount');
//                    $fxOrderMod->doInsert($fxOrderData);
//                    $fxUserAccountMod->addFxUser($fx_user_id,$this->userId);
//                }
//            }
            if (count($rs)) {
                if ($this->delCart($cart_ids)) {
                    //添加积分优惠
                    if ($price) {
                        if ($point !=0){
                            $this->getPointPrice($orderNo, $price, $point);
                        }
                    }
                    if ($id) {
                        $this->getCouponPrice($orderNo, $id);
                    }
                    $this->setData($orderNo1, 1, '下单成功');
                } else {
                    $this->setData('', 0, '下单失败');
                }
//                 $info['url'] = "?app=jsapi&act=jsapi&order_id={$orderNo}&storeid={$storeid}&lang={$lang}";
//                 $this->setData($info,$status=1,$message="提交订单成功，即将进入支付页面！");
            }
        }

    }

    public function getStoreCate($storeId){
        $sql="select store_cate_id from ".DB_PREFIX.'store where id='.$storeId;
        $storeMod=&m('store');
        $storeInfo=$storeMod->querySql($sql);
        return $storeInfo[0]['store_cate_id'];
    }
    function  getGoodId($id){
        $sql="select goods_id from ".DB_PREFIX.'store_goods where id='.$id;
        $goodInfo=$this->orderDetailMod->querySql($sql);
        return $goodInfo[0]['goods_id'];

    }
    function getDeduction($id){
        $sql="select deduction from ".DB_PREFIX.'store_goods where id='.$id;
        $goodInfo=$this->orderDetailMod->querySql($sql);
        return $goodInfo[0]['deduction'];
    }
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
    public function goodsZcode($storeid, $lang, $order_id) {
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
        $valueUrl = 'http://' . $system_web . "/wx.php?app=order&act=order_details&orderid={$order_id}";
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
    public function createNumberOrder($store_id)
    {
        //获取当天开始结束时间
        $startDay = strtotime(date('Y-m-d'));
        $endDay = strtotime(date('Y-m-d 23:59:59'));
        $sql = 'select order_sn,number_order from  '
            . DB_PREFIX . 'order where add_time BETWEEN ' . $startDay . ' AND ' . $endDay
            . ' AND mark = 1 and number_order is not null and store_id = ' . $store_id . ' order by add_time DESC limit 1';
        $res = $this->orderMod->querySql($sql);
        //不管订单存在与否直接加
        $number_order = (int)$res[0]['number_order'] + 1;
        $number_order = str_pad($number_order, 4, 0, STR_PAD_LEFT);
        return $number_order;
    }

    public function buildNo($limit)
    {
        $begin = pow(10, 3);
        $end = (pow(10, 4) - 1);
        $rand_array = range($begin, $end);
        shuffle($rand_array); //调用现成的数组随机排列函数
        return array_slice($rand_array, 0, $limit); //截取前$limit个
    }

    public function getSecKill($prom_id, $goods_id, $store_id)
    {
        $query = array(
            'cond' => "`id` ='{$prom_id}' and `store_goods_id` = '{$goods_id}' and `store_id` = '{$store_id}'",
            'fields' => "*"
        );
        $rs = $this->spikeActivityMod->getOne($query);
        return $rs;
    }

    public function getGroupBuyGoods($prom_id)
    {
        $sql = "select * from " . DB_PREFIX . "goods_group_buy where `id` = '{$prom_id}'";
        $rs = $this->storeMod->querySql($sql);
        return $rs[0];
    }

    public function getGoodInfo($prom_id, $goods_id, $goods_key, $store_id)
    {
        $sql = " select ps.*,pg.goods_id,pg.goods_key,pg.goods_key_name,pg.goods_name,pg.goods_img,pg.goods_price,pg.discount_price,pg.discount_rate,pg.reduce from " . DB_PREFIX . "promotion_sale as ps
      left join " . DB_PREFIX . "promotion_goods as pg on  ps.id = pg.prom_id
      where ps.`store_id` = '{$store_id}'  and ps.`mark` =1
      and pg.goods_id = '{$goods_id}' and pg.goods_key ='{$goods_key}'
      and ps.id = '{$prom_id}'";
        $rs = $this->storeMod->querySql($sql);
        return $rs[0];
    }

    function getdistance($lng1, $lat1, $lng2, $lat2)
    {
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

    public function getCountryLang($store_id)
    {
        $storeCateMod = &m('storeCate');
        $sql = "select sc.lang_id from " . DB_PREFIX . "store as s left join " . DB_PREFIX . "store_cate as sc
      on s.store_cate_id = sc.id where s.id = '{$store_id}'  order by s.id";
        $rs = $storeCateMod->querySql($sql);
        return $rs[0]['lang_id'];
    }

    public function getGoodImg($goods_id,$store_id)
    {
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
    public function getShippingPrice($cart_id)
    {
        $sql = "select `shipping_price` as total from " . DB_PREFIX . "cart where `id` in ({$cart_id}) and `shipping_price` != '0.00' group by `goods_id`";
        $rs = $this->cartMod->querySql($sql);
        $total = 0;
        foreach ($rs as $k => $v) {
            $total += 0.00;
        };
        if (empty($total)) {
            $total = '0.00';
        }
        return number_format($total, 2);
    }

    public function storeName($store_id, $auxiliary, $lang_id)
    {
        $sql = 'select gl.store_name  from  '
            . DB_PREFIX . 'store as g  left join '
            . DB_PREFIX . 'store_lang as gl on g.id = gl.store_id and gl.distinguish= ' . $auxiliary . ' and gl.lang_id= ' . $lang_id . ' where g.id  = ' . $store_id;
        $rs = $this->storeMod->querySql($sql);
        return $rs[0]['store_name'];
    }

    public function getCurCountry($storeid)
    {
        $storeMod = &m('store');
        $sql = 'SELECT s.id,c.`id` AS cid,c.`cate_name`,l.cate_name as lcatename  FROM  ' . DB_PREFIX . 'store AS s
               LEFT JOIN  ' . DB_PREFIX . 'store_cate AS c ON s.`store_cate_id` = c.`id`  left join bs_store_cate_lang  as l on c.id=l.cate_id
                WHERE s.id =' . $storeid . '  and  l.lang_id =' . $this->langid;
        $data = $storeMod->querySql($sql);
        return $data[0];
    }

    public function getGift($store_id, $goodNum, $totalMoney)
    {
        $rs = $this->getActiveByGoodNum($store_id, $goodNum, $totalMoney);
        foreach ($rs as $k => $v) {
            foreach ($v as $k1 => $v1) {
                $info = explode('_', $v1);
                $sql = "select `id`,`goods_name`,`goods_key_name`,`gift_num` from " . DB_PREFIX . "gift_goods where `gift_id` ='{$info[1]}' and `goods_id` = '{$k1}' and `amount` = '{$info[0]}'";
            }
            $res[] = $this->giftGoodMod->querySql($sql);
        }
        return $res;
    }

    public function getSpec1($sp_key, $lang_id)
    {


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

    public function getSpec($sp_key, $lang_id)
    {


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

    public function genOrder($source, $genInfo, $goodInfo, $lang_id)
    {
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
            $rs = $this->orderDetailMod->doInsert($insert_sub_data);

            return $rs;
        } else {
            return 0;
        }
    }

    public function getPointPrice($order_id, $price, $point)
    {

//        $a = $this->langData;
        $userMod = &m('user');
        $pointSiteMod = &m('point');
        $storePointMod = &m('storePoint');
        $storeMod = &m('store');
        $curMod = &m('currency');
        $user_id = $this->userId;
        $user_info = $userMod->getOne(array("cond" => "id=" . $user_id));
        $order_info = $this->orderMod->getOne(array("cond" => "order_sn= '{$order_id}'"));


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
            $this->setData(array(), $status = 0, '失败');
        }
    }

    //优惠劵
    public function getCouponPrice($order_id, $id)
    {

//        $a = $this->langData;
        $userMod = &m('user');
        $pointSiteMod = &m('point');
        $storePointMod = &m('storePoint');
        $storeMod = &m('store');
        $curMod = &m('currency');
        $couponMod = &m('coupon');
        $userCounponMod = &m('userCoupon');
        $user_id = $this->userId;
        $user_info = $userMod->getOne(array("cond" => "id=" . $user_id));
        $order_info = $this->orderMod->getOne(array("cond" => "order_sn= '{$order_id}'"));
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
            $this->setData(array(), $status = 0, '失败');
        }
    }

    public function buyDetails()
    {
        $storeGoodsMod=&m('storeGoods');
        $cart_ids = !empty($_REQUEST['item_id']) ? $_REQUEST['item_id'] : '1110';
        $userGoods = $this->cartMod->getGoodByCartId($cart_ids);
        $total = 0;
        $goods_num = 0;
        $latlon = !empty($_REQUEST['latlon']) ? $_REQUEST['latlon'] : '118.77807441,32.0572355';
        $langData = array(
            $this->langData->project->add_address,
            $this->langData->project->wait_submit_order,
            $this->langData->project->distribution_style,
            $this->langData->project->by_message,
            $this->langData->project->discount_code,
            $this->langData->project->discount_money,
            $this->langData->project->available,
            $this->langData->project->integral_offset,
            $this->langData->project->total_start,
            $this->langData->project->total_end,
            $this->langData->public->total,
            $this->langData->project->include_freight,
            $this->langData->public->yuan,
            $this->langData->project->submit_order
        );
        //睿积分兑换比例
        $pointSiteMod = &m('point');
        $point_price_site = $pointSiteMod->getOne(array("cond" => "1=1"));
        $rate = $point_price_site['point_rate'];
        $roomTypeMod=&m('roomType');
        foreach ($userGoods as $k => $v) {
            $userGoods[$k]['store_name'] = $this->storeMod->getNameById($v['store_id'], $this->lang_id);
            $userGoods[$k]['origin_img'] = $this->getGoodImg($v['goods_id'], $v['store_id']);
            $userGoods[$k]['totalMoney'] = number_format(($v['goods_price'] * $v['goods_num']), 2);
            $total += ($v['goods_price'] * $v['goods_num']);
            if ($v['spec_key']) {
                $info = explode('_', $v['spec_key']);
                foreach ($info as $k1 => $v1) {
                    $sql = "select `item_name` from " . DB_PREFIX . "goods_spec_item_lang where `item_id` = '{$v1}' and  `lang_id` = $this->lang_id";
                    $spec_1 = $this->cartMod->querySql($sql);
                    $spec[] = $spec_1[0]['item_name'];
                }
                $spec_key = implode(':', $spec);
                $userGoods[$k]['spec_key_name'] = $spec_key;
                $spec = array();
            }
            $goods_num += $v['goods_num'];
            $userGoods[$k]['shipping_store_name'] = $this->storeMod->getNameById($v['shipping_store_id'], $this->lang_id);
            $userGoods[$k]['goods_name'] = $this->cartMod->getGoodNameById($v['goods_id'], $this->lang_id);
            $userGoods[$k]['room_type_id']=$roomTypeMod->getRoomTypeId($v['goods_id']);
            //配送方式
            $userGoods[$k]['sendout']=$storeGoodsMod->getGoodsSendoutArr($v['goods_id']);
            $userGoods[$k]['sendoutStr']=$storeGoodsMod->getGoodsSendout($v['goods_id']);
            $userGoods[$k]['sendoutIndex']=key($userGoods[$k]['sendout']);
            $userGoods[$k]['sendoutValue']=current($userGoods[$k]['sendout']);
            $userGoods[$k]['room_parent_id']=$roomTypeMod->getRoomParentId($v['goods_id']);
            $userGoods[$k]['isFreeShipping']=$storeGoodsMod->isFreeShipping($v['goods_id']);
        }

        $addr_id = !empty($_REQUEST['addr_id']) ? htmlspecialchars(trim($_REQUEST['addr_id'])) : '0';
        if ($addr_id) {
            $where = ' and id=' . $addr_id;
        } else {
            $where = ' and default_addr =1';
            //获取收货地址
            $addrSql = "select * from " . DB_PREFIX . 'user_address where user_id=' . $this->userId . $where;
            $userAddress = $this->userAddressMod->querySql($addrSql); // 获取用户的地址
            if ($addr_id == '0') {
                $addr_id = $userAddress[0]['id'];
            }

            $addresss = explode('_', $userAddress[0]['address']);

            $count = strpos($userAddress[0]['address'], "_");
            $str = substr_replace($userAddress[0]['address'], "", $count, 1);

            foreach ($userAddress as $k => $v) {
                $userAddress[$k]['addressDetail'] = $str;
            }
        }

        //优惠券
        $sql = "select c_id from " . DB_PREFIX . 'user_coupon where user_id=' . $this->userId . ' and store_id= ' . $this->store_id;
        $info = $this->cartMod->querySql($sql);
        foreach ($info as $key => $val) {
            $cIds[] = $val['c_id'];
        }
        $cIds = implode(',', $cIds);
        //过期优惠券
        $cSql = "select * from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and end_time < ' . time();
        $ctSql = "select count(*) as ctotal from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and end_time < ' . time();
        $cData = $this->cartMod->querySql($cSql);
        $ctData = $this->cartMod->querySql($ctSql);

        foreach ($cData as $key => $val) {
            $cData[$key]['expire'] = 1;
            $cData[$key]['total']=$ctData[0]['ctotal'];
        }
        //未过期优惠券
        $wSql = "select * from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and end_time > ' . time() . ' and start_time < ' . time();
        $wtSql = "select count(*) as wtotal from " . DB_PREFIX . 'coupon  where id in (' . $cIds . ') and end_time > ' . time() . ' and start_time < ' . time();
        $wData = $this->cartMod->querySql($wSql);
        $wtData = $this->cartMod->querySql($wtSql);
        foreach ($wData as $key => $val) {
            $wData[$key]['expire'] = 0;
            $wData[$key]['total']=$wtData[0]['wtotal'];
        }

        $userMod = &m('user');
        $user_info = $userMod->getOne(array("cond" => "id = " . $this->userId));

        $goodNum = count(explode(',', $cart_ids));
        //睿积分抵扣
        $pointSiteMod = &m('point');
        $storePointMod = &m('storePoint');
        $storeMod = &m('store');
        $curMod = &m('currency');
        //获取订单总金额
        //获取最大积分支付比例
        $point_price_site = $pointSiteMod->getOne(array("cond" => "1=1"));
        $store_point_site = $storePointMod->getOne(array("cond" => "store_id=" . $this->store_id));
        $point_price_site['point_price'] = empty($store_point_site) ? 0 : $store_point_site['point_price'];

        $rechargeData=$this->userMod->getOne(array('cond'=>"`id` = '{$this->userId}' and mark =1 ",'fields'=>'recharge_id'));

        $percentData=$this->rechargeAmountMod->getOne(array('cond'=>"`id` = '{$rechargeData['recharge_id']}'",'fields'=>'percent'));
        $percentData['percent']=empty($percentData['percent']) ? 0: $percentData['percent'];
        $point_price_site['point_price']=$point_price_site['point_price']+$percentData['percent'];
        if ($point_price_site) {
            $point_price = $point_price_site['point_price'] * $total / 100; //积分兑换最大金额
            $rmb_point = $point_price_site['point_rate']; //积分和RMB的比例
        } else {
            $point_price = 0;
            $rmb_point = 0;
        }
        //获取当前店铺币种以及兑换比例
        $store_info = $storeMod->getOne(array("cond" => "id=" . $this->store_id));
        //获取当前币种和RMB的比例
        $rate = $curMod->getCurrencyRate($store_info['currency_id']);
        //积分和RMB的比例
        if ($rate) {
            $ruiPrice=$point_price * $rate * $rmb_point;
            if($ruiPrice < 1){
                $ruiPrice=0;
            }

            $price_rmb_point = ceil($ruiPrice);
            //最大比例使用积分
            if ($price_rmb_point > $user_info['point']) {
                $point_price = $user_info['point'] * $rmb_point / 100;
                $price_rmb_point = ceil($point_price * $rate * $rmb_point);
            }
        }
        //会员默认分销码
        $fxCodeSql="SELECT fx_code FROM  ".DB_PREFIX."fx_user_account as fa LEFT JOIN " .DB_PREFIX."fx_user as fu ON fa.fx_user_id = fu.id WHERE fa.user_id =".$this->userId;

        $fxCodeData=$this->cartMod->querySql($fxCodeSql);
        if (!empty($fxCodeData)){
            $fxuserMod  = &m('fxuser');
            $fxruleMod  = &m('fxrule');
            $fxuserInfo     = $fxuserMod->getOne(array('cond' => "fx_code = '{$fxCodeData[0]['fx_code']}' AND mark = 1"));
            if ($fxuserInfo['level'] != 3){
                $this->setData('',$status=1,$message = '');
            }
            $discount_rate = $fxuserInfo['discount'];
            $discount      = ($total * $discount_rate * 0.01);
        }
        $maxAccount =number_format($point_price, 2, '.', '');
        $maxPoint=$price_rmb_point;
        $ruiData=array('maxAccount'=>$maxAccount,'maxPoint'=>$maxPoint);
        $storeName=$this->getstoreName($this->store_id, 0, $this->lang_id);
        $fxInfo = array(
            'discount' => number_format($discount, 2, '.', ''),
            'fxCode'   => $fxCodeData[0]['fx_code']
        );
        $orderData=array('fxInfo'=>$fxInfo,'langData'=>$langData,'goodsData'=>$userGoods,'userAddress'=>$userAddress,'couponData'=>$cData,'expireCouponData'=>$wData,'ruiData'=>$ruiData,'storeName'=>$storeName);

        if($orderData){

            $this->setData($orderData,'1','');
        }

    }
    /**
     * 获取当前站点名称
     * @author wangshuo
     * @date 2018-6-7
     */
    public function getstoreName($store_id, $auxiliary, $lang_id) {
        $sql = 'select gl.store_name  from  '
            . DB_PREFIX . 'store as g  left join '
            . DB_PREFIX . 'store_lang as gl on g.id = gl.store_id and gl.distinguish= ' . $auxiliary . ' and gl.lang_id= ' . $lang_id . ' where g.id  = ' . $store_id;
        $rs = $this->storeMod->querySql($sql);
        return $rs[0]['store_name'];
    }

    //生成日志
    public function addPointLog($username, $note, $userid, $deposit, $expend, $order_sn) {
        $logData = array(
            'operator' => '--',
            'username' => $username,
            'add_time' => time(),
            'deposit' => $deposit,
            'expend' => $expend,
            'note' => $note,
            'userid' => $userid,
            'order_sn' => $order_sn
        );
        $pointLogMod = &m("pointLog");
        $pointLogMod->doInsert($logData);
    }

    //余额支付接口
    public function rechargeIndex(){
        $langData = array(
            '充值中心',
            '记录',
            '余额',
            '积分抵扣',
            '累计充值',
            '选择充值金额',
            '充值',
            '送',
            '送',
            '获得',
            '积分抵扣比例',
            '积分'
        );
        $where=" where 1=1";
        $where.=' AND u.id='.$this->userId.' AND al.status=2 AND al.mark=1';
        $userData=$this->userMod->getOne(array('cond'=>"`id` = '{$this->userId}' and mark=1",'fields'=>'amount,recharge_id,username,headimgurl'));
        $percentData=$this->rechargeAmountMod->getOne(array('cond'=>"`id`= '{$userData['recharge_id']}'",'fields'=>'percent'));
        if(empty($percentData['percent'])){
            $percentData['percent']=0;
        }
        $userData['percent']=$percentData['percent'];
        $sql="SELECT sum(al.c_money) as accumulativeRecharge FROM ".DB_PREFIX.'amount_log AS al LEFT JOIN '.
            DB_PREFIX.'user AS u ON u.id=add_user'.$where;
        $accumulativeRecharge=$this->userMod->querySql($sql);
        if(empty($accumulativeRecharge[0]['accumulativeRecharge'])){
            $accumulativeRecharge[0]['accumulativeRecharge']=0;
        }
        $userData['accumulativeRecharge']=$accumulativeRecharge[0]['accumulativeRecharge'];
        //充值待付款
            $wxsql = 'SELECT count(*) as c FROM ' . DB_PREFIX . 'amount_log WHERE `type` = 1 AND `status` = 1 AND `mark` = 1 AND `add_user` = '. $this->userId;
            $yuesql = 'SELECT count(*) as c FROM ' . DB_PREFIX . 'amount_log WHERE `type` = 4 AND `status` = 1 AND `mark` = 1 AND `add_user` = '. $this->userId;
            $wx = $this->userMod->querySql($wxsql);
            $yue = $this->userMod->querySql($yuesql);
            $logData['count'] = (($wx[0]['c'] >= 1) ? 1 : 0) + (($yue[0]['c'] >= 1) ? 1 : 0);

        //充值规则
        $ruleSql="SELECT id,c_money,s_money,integral,percent FROM ".DB_PREFIX.'recharge_point WHERE mark=1';
        $ruleData=$this->rechargeAmountMod->querySql($ruleSql);
 	    $last_names = array_column($ruleData,'c_money');
        array_multisort($last_names,SORT_ASC,$ruleData);
        $data=array(
            'langData'=>$langData,
            'userData'=>$userData,
            'ruleData'=>$ruleData,
            'logData' => $logData
        );
        if($data){
            $this->setData($data,1,'');
        }
    }

    /**
     * 充值记录「新」 by xt 2019.03.11
     */
    public function showAmountLog()
    {
        $langData = array(
            '充值记录',
            '余额扣除',
            '充值规则',
            '送',
            '送',
            '获得',
            '积分抵扣比例',
            '积分',
            '微信充值'
        );

        $sql = <<<SQL
                    SELECT
                        l.id,
                        l.add_time,
                        l.c_money,
                        l.`type`,
                        l.point_rule_id,
                        l.`status`,
                        p.s_money,
                        p.integral,
                        p.percent
                    FROM
                        bs_amount_log l
                        LEFT JOIN bs_recharge_point p ON l.point_rule_id = p.id 
                        AND p.mark = 1 
                    WHERE
                        l.mark = 1 
                        AND l.add_user = {$this->userId} 
                    ORDER BY
                        l.add_time DESC
SQL;
        $amountLogData = $this->amountLogMod->querySql($sql);

        $data=array(
            'langData' => $langData,
            'amountLogData' => $amountLogData,
            'imgUrl'=>array(
                SITE_URL.'/assets/phone/images/recharge.png',
                SITE_URL.'/assets/phone/images/balancededuce.png',
                SITE_URL.'/assets/wx/rechargeAmount/payment/images/coupon.png',
            )
        );

        $this->setData($data, 1);
    }


    //充值记录
    public function   amountLog(){
        $langData = array(
            '充值记录',
            '余额扣除',
            '充值规则',
            '送',
            '送',
            '获得',
            '积分抵扣比例',
            '积分',
            '微信充值'
        );
        $where='where 1=1';
        $where .=' and add_user='.$this->userId.' and mark=1  ';
        $csql="SELECT add_time,c_money,s_money,point,`type`,point_rule_id,status,class FROM ".DB_PREFIX."amount_log ".
            $where .'  order by add_time desc ';

        $amountLogData=$this->amountLogMod->querySql($csql);
        foreach($amountLogData as $k=>$v){
            $amountLogData[$k]['percent']=$this->getPercent($v['point_rule_id']);
        }
        $data=array(
            'langData'=>$langData,
            'amountLogData'=>$amountLogData,
            'imgUrl'=>array(
                SITE_URL.'/assets/phone/images/recharge.png',
                SITE_URL.'/assets/phone/images/balancededuce.png',
            )

        );

        if($data){
            $this->setData($data,1,'');
        }
    }

    public function getPercent($rechargeId){
        $Sql="SELECT id,c_money,s_money,integral,percent FROM ".DB_PREFIX.'recharge_point WHERE mark=1 and id='.$rechargeId;
        $oldruleData=$this->rechargeAmountMod->querySql($Sql);
        if(empty($oldruleData[0]['percent'])){
            $oldruleData[0]['percent']=0;
        }
        return $oldruleData[0]['percent'];
    }

    //充值订单
    public function amountOrder(){
        $rule_id=!empty($_REQUEST['rule_id']) ? intval($_REQUEST['rule_id']) : 0;
        $userId=!empty($_REQUEST['userId']) ? intval($_REQUEST['userId']) : 0;
        $storeId=!empty($_REQUEST['storeId']) ? intval($_REQUEST['storeId']) : 0;
        if(empty($rule_id)){
            $this->setData(array(),'0','请选择充值规格');
        }
        $ruleData=$this->rechargeAmountMod->getOne(array('cond'=>"`id`= '{$rule_id}' and mark=1",'fields'=>'id,c_money,s_money,integral,percent'));
        if(empty($ruleData)){
            $this->setData(array(),'0','充值规则不存在');
        }
//        if($userId == 18918){
            $log = $this->amountLogMod->getCount(array('cond'=>"`add_user`= '{$userId}' AND mark = 1 AND status = 1 AND type = 1"));
            if($log >=1 ){
                $this->setData(array(),'0','您有未完成的微信充值订单，请前往支付');
            }
//        }

        $userData=$this->userMod->getOne(array('cond'=>"`id` = '{$userId}' and mark=1",'fields'=>'amount'));
        $rand = $this->buildNo(1);
        $ordersn = date('YmdHis') . $rand[0];
        $data=array(
            'c_money'=>$ruleData['c_money'],
            'old_money'=>$userData['amount'],
            'point_rule_id'=>$ruleData['id'],
            'new_money'=>$userData['amount']+$ruleData['c_money']+$ruleData['s_money'],
            'source'=>2,
            'add_user'=>$userId,
            'add_time'=>time(),
            'mark'=>1,
            's_money' => $ruleData['s_money'],
            'order_sn'=>$ordersn,
            'status'=>1,
            'point'=>$ruleData['integral'],
            'type'=>1
        );
        $res=$this->createAmountlog($data);
        if ($res) {
            /* $info['url'] = "?app=jsapi&act=amountJsapi&order_id={$ordersn}&storeid={$storeId}";*/
            $this->setData($ordersn, $status = 1, '提交订单成功，请前往支付');
        } else {
            $this->setData($info = array(), $status = 0, '订单提交失败');
        }
    }
    //生成充值记录
    public  function  createAmountlog($data){
        $amountLogId=$this->amountLogMod->doInsert($data);
        return $amountLogId;
    }



    public function payment()
    {
        $order_id = !empty($_REQUEST['order_id']) ? $_REQUEST['order_id'] : '201811262104116467';
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : 0;
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : 0;
        $auxiliary = !empty($_REQUEST['latlon']) ? $_REQUEST['latlon'] : 0;
        $orderInfo = $this->orderMod->getOne(array('cond' => "`order_sn` = '{$order_id}'", 'fields' => "order_amount,store_id"));
        $userData = $this->userMod->getOne(array('cond' => "`id` = '{$this->userId}' and mark=1", 'fields' => 'amount'));
        $langData=array(
            '请选择支付方式',
            '订单提交成功,请尽快付款',
            '应付金额',
            '请在24小时内完成支付,否则订单自动取消',
            '微信支付',
            '余额',
            '当前余额',
            '余额不足',
            '去支付',

        );
        $data=array(
            'orderInfo'=>$orderInfo,
            'userData'=>$userData,
            'storeid'=>$orderInfo['store_id'],
            'lang'=>$lang,
            'auxiliary'=>$auxiliary,
            'langData'=>$langData,
            'symbol'=>$this->symbol
        );
        if($data){
            $this->setData($data,1,'');
        }

    }

    //余额扣除
    public function deductAmount(){
        $order_id=!empty($_REQUEST['order_id']) ? $_REQUEST['order_id'] : '';
        $orderMod = &m('order');
        $childOrderData = $orderMod->getData(array("cond" => "`order_sn` like '{$order_id}%' and `mark` = 1"));
        $sql = "SELECT goods_num,goods_id,store_id,prom_id FROM bs_order_goods WHERE order_id = " .$order_id;
        $datas = $orderMod->querySql($sql);
        if ($datas[0]['prom_id'] !== 0){
            $sqll = "SELECT * FROM bs_spike_goods WHERE spike_id = {$datas[0]['prom_id']} AND store_goods_id = {$datas[0]['goods_id']}";
            $infos = $orderMod->querySql($sqll);
            $conds = array(
                "goods_num" => $infos[0]['goods_num'] - $datas[0]['goods_num']
            );
            $spikeActivitiesGoods = &m('spikeActiviesGoods');
//            $spikeActivitiesGoods->doEdit($infos[0]['id'],$conds);
        }
        foreach($childOrderData as $key =>$val){
            $store_id =$val['store_id'];
            if($val['buyer_id'] !=$this->userId){
                $this->setData(array(),0,'你不是购买者');
            }
            if($val['order_state'] >=20){
                $this->setData(array(),0,'订单已支付');
            }
            $userData=$this->userMod->getOne(array('cond'=>"`id` = '{$this->userId}' and mark=1",'fields'=>'amount'));
            $orderSn =$val['order_sn'];
            $fxOrderMod = &m('fxOrder');
            $fxOrderMod->addFxOrderByOrderSn($orderSn, 4);
            $amountLogData=array(
                'order_sn'=>$orderSn,
                'type'=>2,
                'status'=>0,
                'c_money'=>$val['order_amount'],
                'old_money'=>$userData['amount'],
                'new_money'=>$userData['amount']-$val['order_amount'],
                'source'=>1,
                'add_user'=>$this->userId,
                'add_time'=>time()
            );
            $userdata=array(
                'amount'=>$userData['amount']-$val['order_amount']
            );
            $data =array(
                'pay_sn' =>'余额支付' ,
                'payment_code' => '余额支付',
                'payment_time' => time(),
                'order_state' => 20,
                'number_order' => $this->createNumberOrder($store_id)
            );
            $cond =array(
                'order_sn' => "{$orderSn}"
            );
            $detail =array(
                'order_state' =>20
            );
            $this->userMod->doEdit($this->userId,$userdata);
            $amountLogId = $this->createAmountlog($amountLogData);
            $this->UpdateStock($orderSn);
            $this->orderDetailMod->doEditSpec(array('order_id' =>"{$orderSn}"),$detail);
            $res =$this->orderMod->doEditSpec($cond,$data);
            $this->orderMod->update_pay_time($store_id,$orderSn,'余额支付',3,20,0,$data['number_order']);
            $this->amountLogMod->doEdit($amountLogId, array('status' => 2));
        };
        if ($res) {
            $this->setData($info=array(), $status = 1, '支付成功');
        } else {
            $this->setData($info = array(), $status = 0, '支付失败');
        }
    }



    /**
     * 免费兑换
     * @author gao
     * @date  2019-02-25
     * @param int order_sn 订单号
     * @return mixed
     */
    public function voucherPay(){
        $order_id=!empty($_REQUEST['order_sn']) ? $_REQUEST['order_sn'] : '0';
        $userId=!empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;
        $couponLogMod=&m('couponLog');//优惠劵记录表
        $store_id = $_REQUEST['store_id'];
        $state=$this->orderMod->getOne(array('cond'=>"`order_sn`='{$order_id}'",'fields'=>'order_amount,cid,order_state,buyer_id'));
        $couponMod = &m('coupon'); //优惠劵模型
        $couponData = $couponMod->getOne(array('cond' => "`id` = '{$state['cid']}'", 'fields' => "type")); //1代表 抵扣劵 2是兑换券
        $couponType = $couponData['type']; //优惠劵类型  1代表 抵扣劵 2是兑换券
        $cmod = &m('userCoupon');
        $userCoupon = $cmod->getOne(array('cond' => "`user_id` = '{$state['buyer_id']}' AND `c_id` = '{$state['cid']}'"));
        if($couponType != 2 || empty($userCoupon)){
            $this->setData(array(),0,'该订单不满足兑换条件');
        }

        if($state['buyer_id'] !=$userId){
            $this->setData(array(),0,'你不是购买者');
        }
        if($state['order_state'] >=20){
            $this->setData(array(),0,'订单已支付');
        }

        $data =array(
            'pay_sn' =>'免费兑换' ,
            'payment_code' => '免费兑换',
            'payment_time' => time(),
            'order_state' => 20
        );
        $cond =array(
            'order_sn' =>$order_id
        );
        $detail =array(
            'order_state' =>20
        );
       $this->UpdateStock($order_id);
        $this->orderDetailMod->doEditSpec(array('order_id' =>$order_id ),$detail);
        $res =$this->orderMod->doEditSpec($cond,$data);
        $this->orderMod->update_pay_time($store_id,$order_id,'免费兑换',5);
        if ($res) {
            $info['url'] ="?app=order&act=orderHair";
            $this->setData($info, $status = 1, '支付成功');
        } else {
            $this->setData($info = array(), $status = 0, '支付失败');
        }
    }

    // 更新规格库存 和 无规格库存
    public function UpdateStock($out_trade_no){
        //  更新库存

        $sql = "SELECT sg.goods_id,sg.spec_key,sg.goods_num,sg.deduction,sg.good_id FROM ".
            DB_PREFIX."order as r LEFT JOIN ".
            DB_PREFIX."order_goods as sg ON  r.order_sn = sg.order_id WHERE sg.order_id = '{$out_trade_no}'";
        $orderRes = $this->areaGoodMod->querySql($sql);
        foreach ($orderRes as $k =>$v) {
            if (!empty($v['spec_key'])) {
                if($v['deduction']==1){
                    $query_id = "select `id` from " . DB_PREFIX . "store_goods_spec_price where `goods_id` = '{$v['good_id']}' and `key` = '{$v['spec_key']}' ";
                    $res_query = $this->storeGoodItemPriceMod->querySql($query_id);
                    $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods_spec_price WHERE `goods_id` = '{$v['good_id']}' and `key` = '{$v['spec_key']}'";
                    $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                    foreach($res_query as $key=>$val){
                        $condition = array(
                            'goods_storage' => $specInfo[0]['goods_storage'] - $v['goods_num']
                        );
                        $res = $this->storeGoodItemPriceMod->doEdit($val['id'], $condition);
                    }
                    if ($res) {
                        $infoSql = "select goods_storage,id from  " . DB_PREFIX . "store_goods WHERE `goods_id` = '{$v['good_id']}'";
                        $Info = $this->areaGoodMod->querySql($infoSql);
                        $cond = array(
                            'goods_storage' => $Info[0]['goods_storage'] - $v['goods_num']
                        );
                        foreach($Info as $key1=>$val1 ){
                            $this->areaGoodMod->doEdit($val1['id'], $cond);
                        }
                    }
                    $Sql = "select goods_storage from  " . DB_PREFIX . "goods_spec_price WHERE `goods_id` = '{$v['good_id']}' and `key` = '{$v['spec_key']}'";

                    $goodsSpec = $this->areaGoodMod->querySql($Sql);
                    $conditional=array(
                        'goods_storage'=>$goodsSpec[0]['goods_storage']-$v['goods_num']
                    );
                    $goodsSpecSql="update ".DB_PREFIX."goods_spec_price set goods_storage = ".$conditional['goods_storage']." where goods_id=".$v['good_id']." and `key` ='{$v['spec_key']}'" ;
                    $result=$this->goodsSpecPriceMod->doEditSql($goodsSpecSql);
                    if($result){
                        $goodSql="select goods_storage from  " . DB_PREFIX . "goods WHERE `goods_id` = '{$v['good_id']}'";

                        $goodInfo = $this->areaGoodMod->querySql($goodSql);
                        $goodCond = array(
                            'goods_storage' => $goodInfo[0]['goods_storage'] - $v['goods_num']
                        );
                        $this->goodsMod->doEdit($v['good_id'],$goodCond);
                    }
                }else{
                    $query_id = "select `id` from " . DB_PREFIX . "store_goods_spec_price where `store_goods_id` = '{$v['goods_id']}' and `key` = '{$v['spec_key']}' ";
                    $res_query = $this->storeGoodItemPriceMod->querySql($query_id);
                    $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods_spec_price WHERE `store_goods_id` = '{$v['goods_id']}' and `key` = '{$v['spec_key']}'";
                    $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                    $condition = array(
                        'goods_storage' => $specInfo[0]['goods_storage'] - $v['goods_num']
                    );
                    $res = $this->storeGoodItemPriceMod->doEdit($res_query[0]['id'], $condition);
                    if ($res) {
                        $infoSql = "select goods_storage from  " . DB_PREFIX . "store_goods WHERE `id` = '{$v['goods_id']}'";
                        $Info = $this->areaGoodMod->querySql($infoSql);
                        $cond = array(
                            'goods_storage' => $Info[0]['goods_storage'] - $v['goods_num']
                        );
                        $this->areaGoodMod->doEdit($v['goods_id'], $cond);
                    }
                }



            } else {
                if($v['deduction']==1){
                    $infoSql = "select goods_storage,id from  " . DB_PREFIX . "store_goods WHERE `goods_id` = '{$v['good_id']}'";
                    $Info = $this->areaGoodMod->querySql($infoSql);

                    $cond = array(
                        'goods_storage' => $Info[0]['goods_storage'] - $v['goods_num']
                    );
                    foreach($Info as $key1=>$val1 ){
                        $this->areaGoodMod->doEdit($val1['id'], $cond);
                    }
                    $goodSql="select goods_storage from  " . DB_PREFIX . "goods WHERE `goods_id` = '{$v['good_id']}'";

                    $goodInfo = $this->areaGoodMod->querySql($goodSql);
                    $goodCond = array(
                        'goods_storage' => $goodInfo[0]['goods_storage'] - $v['goods_num']
                    );
                    $this->goodsMod->doEdit($v['good_id'],$goodCond);
                }else{
                    $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods WHERE `id` = '{$v['goods_id']}'";
                    $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                    $condition = array(
                        'goods_storage' => $specInfo[0]['goods_storage'] - $v['goods_num']
                    );
                    $this->areaGoodMod->doEdit($v['goods_id'],$condition);
                }

            }
        }
    }


      /*
     * 我的订单分享付款价格
     * @author wangs
     * @2017-10-24 13:59:10
     */
  public function payfor() {
        $type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : 1;
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->lang_id;
        $orderid = $_REQUEST['orderid'];
        $where = "  order_id = '{$orderid}' " ;
        $storeMod = &m('store');
        $sqlStore = 'select store_type  from  ' . DB_PREFIX . 'store where id  = ' . $storeid;
        $dataStore = $storeMod->querySql($sqlStore);
        if ($dataStore[0]['store_type'] == 1) {
              //总代理
            //列表页数据
            $sql = 'select order_id,order_sn,store_id,store_name,buyer_id,add_time,order_amount,order_state from ' . DB_PREFIX . 'order'
                    . ' where' . $where . ' order by order_id desc';
        } else {
              //经销商
            //列表页数据
            $sql = 'select order_id,order_sn,store_id,store_name,buyer_id,add_time,order_amount,order_state from ' . DB_PREFIX . 'order'
                    . ' where' . $where . ' and store_id =' . $storeid
                    . ' order by order_id desc';
        }
        $data = $this->orderMod->querySql($sql);
       //获取用户的头像
        $sql_uname = 'select headimgurl from ' . DB_PREFIX . 'user'
                    . ' where  id =' . $data[0]['buyer_id'];
        $res_url = $this->orderGoodsMod->querySql($sql_uname);
         $data[0]['buyer_url']= $res_url[0]['headimgurl'];
        //获取订单所有商品
        $sql = "select l.goods_name,o.goods_image,o.goods_pay_price,o.goods_num from "
            . DB_PREFIX . "order_goods as o left join "
            . DB_PREFIX . "store_goods as s on o.goods_id = s.id left join "
            . DB_PREFIX . "goods_lang as l  on s.goods_id = l.goods_id "
            . " where o.order_id= '{$data[0]['order_sn'] }' and lang_id = " . $lang;
        $list = $this->orderGoodsMod->querySql($sql);
        $data[0]['goods_list'] = $list;
       $orderData=array(
            'info'=>$data,
            'type'=>$type
        );
        if($orderData){
            $this->setData($orderData,'1','');
        }
  }
    public function amountPay()
    {
        $order_id = !empty($_REQUEST['order_id']) ? htmlspecialchars(trim($_REQUEST['order_id'])) : '201811281720531299';
        $lang = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : $this->lang_id;
        $langData = array(
            $this->langData->public->yuan,
            $this->langData->public->immediately_payment,
            $this->langData->project->need_pay_money
        );
        $orderInfo = $this->amountLogMod->getOne(array('cond' => "`order_sn` = '{$order_id}'", 'fields' => "c_money,order_sn"));
        $order_amount = $orderInfo['c_money'];
        $data = array(
            'listData' => $order_amount,
            'order_sn' => $orderInfo['order_sn'],
            'langData' => $langData,
        );
        $this->setData($data,1,'');
    }

    public function comfirm() {
        //语言包
        $fxUserMod = &m('fxuser');
        $cart_ids = !empty($_REQUEST['cart_ids']) ? htmlspecialchars($_REQUEST['cart_ids']) : '';
        $seller_msg = !empty($_REQUEST['seller_msg']) ? htmlspecialchars($_REQUEST['seller_msg']) : '';
        $user_address = !empty($_REQUEST['user_address']) ? htmlspecialchars($_REQUEST['user_address']) : '';
        $fxPhone = !empty($_REQUEST['fxPhone']) ? $_REQUEST['fxPhone'] : '';
        $storeid = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0;
        $lang = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : 0;
        $discount_rate = !empty($_REQUEST['discount_rate']) ? intval($_REQUEST['discount_rate']) : '';
        $price = !empty($_REQUEST['price']) ? $_REQUEST['price'] : '';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $sendout = !empty($_REQUEST['sendout']) ? htmlspecialchars($_REQUEST['sendout']) : '1';
        $address = !empty($_REQUEST['address']) ? htmlspecialchars($_REQUEST['address']) : 0;
        $shippingfee = !empty($_REQUEST['shippingfee']) ? htmlspecialchars($_REQUEST['shippingfee']) : '';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $id = !empty($_REQUEST['id']) ? htmlspecialchars(trim($_REQUEST['id'])) : 0;
        $point = !empty($_REQUEST['point']) ? $_REQUEST['point'] : '';
        $addr_id = !empty($_REQUEST['addr_id']) ? htmlspecialchars(trim($_REQUEST['addr_id'])) : 0;
        $fx_user_id = !empty($_REQUEST['fx_user_id']) ? intval($_REQUEST['fx_user_id']) : '';
        $rule_id = !empty($_REQUEST['rule_id']) ? intval($_REQUEST['rule_id']) : '';
        $pei_time=!empty($_REQUEST['pei_time']) ?$_REQUEST['pei_time']:0;
        $userCouponId=!empty($_REQUEST['userCouponId']) ? $_REQUEST['userCouponId']:0;//用户优惠劵Id
        $discount_price=!empty($_REQUEST['discount_price']) ? $_REQUEST['discount_price'] : 0;//优惠劵优惠金额
        $pei_time=strtotime($pei_time);
        if (empty($user_address) && $sendout != 1) {
            $this->setData($info = array(), $status = 0, '地址有误');
        }
        $latlon1 = explode(',', $latlon);
        //地址距离比较
        $userAddressMod = &m('userAddress');
        $sql = "select  distance,longitude,latitude  from " . DB_PREFIX . "store where id =" . $storeid;
        $storeInfo = $userAddressMod->querySql($sql);
        $longitude = $storeInfo[0]['longitude'];
        $latitude = $storeInfo[0]['latitude'];
        $sqle = "select latlon from " . DB_PREFIX . "user_address where  user_id=" . $this->userId . ' and id =' . $addr_id .' and distinguish=1';
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
                $this->setData(array(), 0, '地址不在配送范围');
            }
        }
        $rand = $this->buildNo(1);
        $orderNo = date('YmdHis') . $rand[0];
        $sql = "select c.user_id,c.store_id,c.shipping_store_id,SUM(c.goods_price*goods_num) as goods_amount,u.email,u.username from " . DB_PREFIX . "cart as c  LEFT JOIN " . DB_PREFIX . "user as u ON c.user_id = u.id  where c.`id` in  ({$cart_ids}) ";
//        $sql = "select c.user_id,c.store_id,SUM(c.goods_price*goods_num) as goods_amount,u.email,u.username from " . DB_PREFIX . "cart as c LEFT JOIN " . DB_PREFIX . "store_lang as s ON c.store_id =s.store_id  and  s.lang_id = " . $this->langid . "  LEFT JOIN " . DB_PREFIX . "user as u ON c.user_id = u.id  where c.`id` in  ({$cart_ids}) ";
        $orderInfo = $this->cartMod->querySql($sql);
        $orderInfo[0]['store_name'] = $this->storeMod->getNameById($orderInfo[0]['shipping_store_id'], $this->lang_id);

        $shipping_fee = $this->getShippingPrice($cart_ids);
        $user_address = $this->userAddressMod->getAddress($user_address);
        $goodsInfo = $this->cartMod->getGoodByCartId($cart_ids);

        if (!empty($fxPhone)) {
            //$fxUserInfo = $fxUserMod->getOne(array('cond' => " telephone='{$fxPhone}' ", 'fields' => 'fx_discount'));
            $fxuserMod      = &m('fxuser');
            $fxuserInfo     = $fxuserMod->getOne(array('cond' => "fx_code = '{$fxPhone}' AND mark = 1"));
            $discount = (($orderInfo[0]['goods_amount']) * $fxuserInfo['discount'] * 0.01);
        } else {
            $discount = 0;
        }

        //生成小票编号
        $number_order = $this->createNumberOrder($storeid);

        //优惠劵
        $couponMod=&m('coupon');
        $userCouponMod=&m('userCoupon');
        if(!empty($id)){
            $couponData=$couponMod->getOne(array('cond'=>"`id` = '{$id}'",'fields'=>'money,discount'));//优惠劵信息
            $userCouponData=$userCouponMod->getOne(array('cond'=>"`c_id` = '{$id}' and user_id = '{$this->userId}' ",'fields'=>'id'));//用户优惠劵信息
            $order_amount = $orderInfo[0]['goods_amount'] + $shippingfee - $discount - $price-$discount_price;
        }else{
            $order_amount = $orderInfo[0]['goods_amount'] + $shippingfee - $discount - $price;
        }
        if ($order_amount <= 0) {
            $order_amount = 0;
        }

        //收货信息

        $count = strpos($user_address['address'], "_");
        if($count==false){
            $addressStr=$user_address['address'];
        }else{
            $addressStr = substr_replace($user_address['address'], "", $count, 1);
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

        /* desc  :记录参数集合
          auther:luffy
          date  :2018-09-11
       */
  /*      $systemErrorLogMod =  &m('systemErrorLog');
        $systemErrorLogMod -> doInsert(array(
            'user_id'           => $this->userId,
            'request_params'    => serialize($_REQUEST),
            'deal_params'       => serialize($orderInfo),
            'important_params'  => $order_amount.'='.$orderInfo[0]['goods_amount'].'+'.$shippingfee.'-'.$discount.'-'.$price.'&&&'.$discount.'='.$orderInfo[0]['goods_amount'].'*'.$discount_rate.'*0.01',
            'add_time'          => time()
        ));*/
        /* if(empty($invalid)){
             $this->setData(array(),'0','商品库存不足');
         }*/
        $insert_main_data = array(
            'order_sn' => $orderNo,
            'store_id' => $orderInfo[0]['shipping_store_id'],
            'sendout' => $sendout, // 1自提2派送3邮寄托运
            'store_name' => $orderInfo[0]['store_name'],
            'buyer_id' => $orderInfo[0]['user_id'],
            'buyer_name' => addslashes($user_address['name']),
            'buyer_email' => $orderInfo[0]['email'],
            'goods_amount' => $orderInfo[0]['goods_amount'],
            'order_amount' => $order_amount,
            'shipping_fee' => $shippingfee,
            'order_state' => 10,
            'order_from' => 2,
            'buyer_address' => $addressStr,
            'buyer_phone' => $user_address['phone'],
            //'gift_id' =>$gift_id,
            'discount' => $discount,
            'fx_discount_rate' => $discount_rate,
            'fx_phone' => $fxPhone,
            'add_time' => time(),
//            'number_order' => $number_order, //生成小票编号
            'seller_msg' => $seller_msg, //订单的留言
            'sub_user' => 2,
            'pei_time'=>$pei_time,
            'is_source'=>3,
            'fx_user_id'=>$fx_user_id
        );
        //优惠劵
        if(!empty($id)){
            $insert_main_data['cid']=$id;
            $insert_main_data['cp_amount']=$discount_price;
        }
        try {
            //事务开始
            $this->orderMod->begin();
            //原来生成订单数据
            $main_rs = $this->orderMod->doInsert($insert_main_data);
            //生成新的订单表数据
            $insert_main_data['cp_amount']=$discount_price;
            $insert_main_data['pd_amount']=$price;
            $insert_main_data['fx_money']=$discount;
            $createOrderRes = $this->orderMod->createOrder($insert_main_data,1);
            if (empty($main_rs) || empty($createOrderRes)) {
                //事务回滚
                $this->orderMod->rollback();
                $this->setData(array(), 0, '下单失败');
            } else {
                //事务提交
                $this->orderMod->commit();
            }
        } catch (Exception $e) {
            //事务回滚
            $this->orderMod->rollback();
            writeLog($e->getMessage());
            $this->setData(array(), 0, '下单失败');
        }
        $count = count($goodsInfo);
        $discount = round($discount / $count, 2);

        if(!empty($id)){
            //用户使用优惠劵记录
            $couponLogMod=&m('couponLog');
            $couponLogData=array(
                'user_coupon_id'=>$userCouponId,
                'coupon_id'=>$id,
                'user_id'=>$this->userId,
                'order_id'=>$main_rs,
                'order_sn'=>$orderNo,  // by xt 2019.03.21
                'add_time'=>time()
            );
            $res=$couponLogMod->doInsert($couponLogData);
        }

        // 先插入子订单
        if ($main_rs) {
            foreach ($goodsInfo as $k => $v) {
                $insert_sub_data = array(
                    'order_id' => $orderNo,
                    'goods_id' => $v['goods_id'],
                    'goods_name' => addslashes(stripslashes($v['goods_name'])),
                    'goods_price' => $this->orderMod->getPrice($v['store_id'],$v['goods_id'],$v['spec_key']),
                    'goods_num' => $v['goods_num'],
                    'goods_image' => $this->getGoodImg($v['goods_id'], $v['store_id']),
                    'goods_pay_price' => $this->orderMod->getGoodsPayPrice($v['store_id'],$v['goods_id'],$v['spec_key']),
                    'spec_key_name' => $v['spec_key_name'],
                    'spec_key' => $v['spec_key'],
                    'store_id' => $v['store_id'],
                    'buyer_id' => $v['user_id'],
                    'goods_type' => 0,
                    'order_state' => 10,
                    'fx_code' => $v['fx_code'],
                    'discount' => ($v['goods_price'] + $shippingfee) * ($fxUserInfo['fx_discount']) * 0.01,
                    'discount_rate' => $fxUserInfo['fx_discount'],
                    'shipping_price' => $v['shipping_price'],
                    'shipping_store_id' => $v['shipping_store_id'],
                    'add_time' => time(),
                    'good_id'=>$this->getGoodId($v['goods_id']),
                    'deduction'=>$this->getDeduction($v['goods_id'])
                );
                $rs[] = $this->orderDetailMod->doInsert($insert_sub_data);

            }

            $rs = array_filter($rs);


//            //获取站点国家id
//            $store_cate=$this->getStoreCate($goodsInfo[0]['store_id']);
//            if (!empty($fx_user_id) && !empty($rule_id)) {
//                $userId = $this->userId;
//                $fx_info = $fxUserMod->getOne(array("cond" => "user_id='" . $userId . "'"));
//                $fx     = $fxuserMod->getOne(array('cond' => "fx_code = '{$fxPhone}' AND mark = 1"));
//                $fxOrderMod =&m('fxOrder');
//                $fxMoney = $fxOrderMod->calFxMoney($order_amount, $fx_user_id);
//                if ($fx_info['fx_code'] !== $fxPhone){
//                    $fxOrderData = array(
//                        'order_id' => $main_rs,
//                        'order_sn' => $orderNo,
//                        'source' => 2,
//                        'user_id' => $this->userId,
//                        'fx_user_id' => $fx_user_id,
//                        'rule_id' => $rule_id,
//                        'store_cate' => $store_cate,
//                        'store_id' => $goodsInfo[0]['store_id'],
//                        'add_time' => time(),
//                        'add_user' => $this->userId,
//                        'pay_money' => $order_amount,
//                        'fx_money'=> $fxMoney
//                    );
//                    $fxUserAccountMod = &m('fxUserAccount');
//                    $fxOrderMod->doInsert($fxOrderData);
//                    $fxUserAccountMod->addFxUser($fx_user_id, $this->userId);
//                }
//            }
            if (count($rs)) {

                if ($this->delCart($cart_ids)) {
                    //添加积分优惠
                    if ($price) {
                        if ($point != 0){
                            $this->getPointPrice($orderNo, $price, $point);
                        }

                    }
                 /*   if ($id) {
                        $this->getCouponPrice($orderNo, $id);
                    }*/
                    $this->setData($orderNo, $status = 1, '下单成功');
                } else {
                    $this->setData($info = array(), $status = 0,'下单失败');
                }
            }
        }
    }
    //线下支付接口
    public function cashPayment(){
        $rule_id=!empty($_REQUEST['rule_id']) ? intval($_REQUEST['rule_id']) : '';
        $userId=!empty($_REQUEST['userId']) ? intval($_REQUEST['userId']) : 540;
        $storeId=!empty($_REQUEST['storeId']) ? intval($_REQUEST['storeId']) : 0;
        if(empty($rule_id)){
            $this->setData(array(),'0','请选择充值规格');
        }
        $ruleData=$this->rechargeAmountMod->getOne(array('cond'=>"`id`= '{$rule_id}' and mark=1",'fields'=>'id,c_money,s_money,integral,percent'));
        if(empty($ruleData)){
            $this->setData(array(),'0','充值规则不存在');
        }
//        if($userId == 18918){
            $log = $this->amountLogMod->getCount(array('cond'=>"`add_user`= '{$userId}' AND mark = 1 AND status = 1 AND type = 4"));
            if($log >=1 ){
                $this->setData(array(),'0','您有未完成的线下充值订单，请等待审核');
            }
//        }


        $userData=$this->userMod->getOne(array('cond'=>"`id` = '{$userId}' and mark=1",'fields'=>'amount'));
        $rand = $this->buildNo(1);
        $ordersn = date('YmdHis') . $rand[0];
        $data=array(
            'c_money'=>$ruleData['c_money'],
            'old_money'=>$userData['amount'],
            'point_rule_id'=>$ruleData['id'],
            'new_money'=>$userData['amount']+$ruleData['c_money']+$ruleData['s_money'],
            'source'=>1,
            'add_user'=>$userId,
            'add_time'=>time(),
            'mark'=>1,
            'order_sn'=>$ordersn,
            'status'=>1,
            's_money'=>$ruleData['s_money'],
            'point'=>$ruleData['integral'],
            'type'=>4,
            'class'=>2
        );
        $res=$this->createAmountlog($data);
        if ($res) {
            $info['url'] = "?app=rechargeAmount&act=amountLog&storeid={$storeId}";
            $this->setData($info, $status = 1, '提交订单成功，等待审核');
        } else {
            $this->setData($info = array(), $status = 0, '提交订单失败');
        }

    }

    /**
     * 充值
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-12
     * Time: 10:32
     */
    public function reChange(){
        $type=!empty($_REQUEST['type']) ? intval($_REQUEST['type']) : 4;
        $userId=!empty($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
        if(empty($userId)){
            $this->setData(array(),'0','缺少必要参数user_id');
        }

        $log = $this->amountLogMod->getCount(array('cond'=>"`add_user`= '{$userId}' AND mark = 1 AND status = 1 AND type = '{$type}'"));
        $title = $type == 1 ? '您有未完成的微信充值订单，请前往支付' : '您有未完成的线下充值订单，请等待审核';
        if($log >=1 ){
            $this->setData(array(),'0',$title);exit();
        }
        $this->setData(array(),'1','SUCCESS');
    }


    /**
     * 订单提交
     * @author gao
     * @date 2019/02/18
     */
    public function orderList(){
        $userAddresssMod=&m('userAddress');
        $cartMod=&m('cart');
        $userCouponMod=&m('userCoupon');
        //接收参数
        $cart_ids = !empty($_REQUEST['item_id']) ? $_REQUEST['item_id'] : '8457'; //购物车id
        $lang=!empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : 29; //语言id
        $referer = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"]);
        $userId=!empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '6039'; //购物车id
        /*$userAddress=$userAddresssMod->getUserAddress($userId);//用户默认收货地址*/
        $addr_id = !empty($_REQUEST['addr_id']) ? htmlspecialchars(trim($_REQUEST['addr_id'])) : '';
        if ($addr_id=='') {
            $where = ' and default_addr =1';
        } else {
            $where = ' and id=' . $addr_id;
        }
        $referer = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"]);
        $address = !empty($_REQUEST['address']) ? htmlspecialchars(trim($_REQUEST['address'])) : '';
        $addrSql = "select * from " . DB_PREFIX . 'user_address where distinguish=1 and user_id=' . $userId . $where;
        $userAddress = $userAddresssMod->querySql($addrSql); // 获取用户的地址
        if($addr_id==''){
            $addr_id=$userAddress[0]['id'];
        }
        $this->assign('nowlatlon', $userAddress[0]['latlon']);
        if (empty($userAddress[0])) { // 添加*/
            $flag=1;
        } else {
            $flag=2;
            $addresss = explode('_', $userAddress[0]['address']);
            $count = strpos($userAddress[0]['address'], "_");
            $str = substr_replace($userAddress[0]['address'], "", $count, 1);
        }
        $goodsInfo = $cartMod->getCartGoods($cart_ids,$lang);//获取购物车商品信息
        $userGoods=$goodsInfo['userGoods']; //用户购物车购买商品信息
        $goodsNum=$goodsInfo['goodsNum'];//总商品数量
        $totalMoney=$goodsInfo['totalMoney'];//总金额
        $storeId=$userGoods[0]['store_id'];//店铺Id
        $sendoutDisplay=$goodsInfo['sendoutDisplay']; //是否显示商品的配送方式  有值代表不显示 没值代表显示
        $voucherParameter=$goodsInfo['voucherParameter'];//兑换劵限制条件数组
        $pointData=$cartMod->getPointMoney($storeId,$userId,$totalMoney);//睿积分抵扣
        $fxData=$cartMod->getFxCode($totalMoney,$userId);//分销抵扣
        $discountMoney=number_format($totalMoney,2,".","")-$pointData['maxAccount']-$fxData['fxDiscount'];//抵扣后的金额
        $couponData=$userCouponMod->getValidCoupons($userId,$lang,1,$storeId,$totalMoney,'',$cart_ids);//抵扣劵信息
        $voucherData=$userCouponMod->getValidCoupons($userId,$lang, 2, 0, 0,$voucherParameter);//兑换劵信息
        $data=array(
            'couponData'=>$couponData,
            'voucherData'=>$voucherData,
            'fxData'=>$fxData,
            'pointData'=>$pointData,
            'totalMoney'=>number_format($totalMoney,2,".",""),
            'discountMoney'=>$discountMoney,
            'goodsNum'=>$goodsNum,
            'userGoods'=>$userGoods,
            'storeName'=>$userGoods[0]['store_name'],
            'referer'=>$referer,
            'address'=>$str,
            'flag'=>$flag,
            'address'=>$str,
            'userAddress'=>$userAddress[0],
            'sendoutDisplay'=>$sendoutDisplay
        );
        $this->setData($data,1,'');
    }


    /**
     * 待付款订单详情页面接口
     * @author gao
     * @date 2019-02-14
     */
    public function  pendingPayment(){
        $orderMod=&m('order');
        $orderGoodsMod=&m('orderGoods');
        $areaGoodMod = &m('areaGood');
        $cartMod=&m('cart');
        $storeMod=&m('store');
        $couponMod =&m('coupon');
        $expectTime=$cartMod->expectTime();//模型里面定义的过期时间
        $orderSn=!empty($_REQUEST['orderSn']) ? $_REQUEST['orderSn'] : ''; //订单编号
        $orderData=$orderMod->getOne(array('cond' => "order_sn = '{$orderSn}'")); //订单信息
        $orderGoodsData=$orderGoodsMod->getData(array('cond' => "order_id = '{$orderSn}'")); //订单商品信息
        foreach ($orderGoodsData as $key => $val){
            $sql = "SELECT * FROM bs_store_goods WHERE id = ".$val['goods_id'];
            $data = $areaGoodMod->querySql($sql);
            $orderGoodsData[$key]['attributes'] = $data[0]['attributes'];
            $orderGoodsData[$key]['is_free_shipping'] = $data[0]['is_free_shipping'];
        }
        $expireTime=$orderData['add_time']+$expectTime-time(); //订单过期时间
        $storeId=$orderData['store_id'];
        $storeImage = $storeMod->getOne(array('cond' => "id = '{$storeId}'"));
        if($expireTime<0){
            $expireTime=0;
        }
        $voucherData = $couponMod ->getOne(array("cond"=>" `id` = {$orderData['cid']}"));
        if(!empty($voucherData)){
            switch($voucherData['type']){
                case 2 :
                    $voucherStr = "封顶{$voucherData['money']}元兑换";
                    $orderData['voucherStr'] = $voucherStr;//折扣劵
                    break;
                case 1 :
                    $voucherStr = "满{$voucherData['money']}元抵扣{$voucherData['discount']}元";
                    $orderData['voucherStr'] = $voucherStr;//折扣劵
                    break;
            }
        }else{
            $orderData['voucherStr'] = "暂无";//折扣劵
        }
        switch ($orderData['sendout'])
        {
            case 1:
                $orderData['sendout']='到店自提';
                break;
            case 2:
                $orderData['sendout']='配送上门';
                break;
            case 3:
                $orderData['sendout']='邮寄托运';
                break;
            case 4:
                $orderData['sendout']='海外代购';
                break;
        }
        $data=array(
            'orderData'=>$orderData,
            'orderGoodsData'=>$orderGoodsData,
            'expireTime'=>$expireTime,
            'storeImage'=>$storeImage['logo']
        );
       $this->setData($data,1,'');
    }



    /**
     * 取消订单
     * @author gao
     * @date 2019-02-20
     */
    public function cancleOrder()
    {
        $orderMod=&m('order');
        $orderSn = !empty($_REQUEST['orderSn']) ? htmlspecialchars(trim($_REQUEST['orderSn'])) : '';//订单编号
        $res=$orderMod->xcxCancleOrder($orderSn);
        if($res==0){
            $this->setData('',0,'订单已失效');
        }else{
            $this->setData('',1,'订单已取消');
        }
    }



    /**
     * 取消订单
     * @author gao
     * @date 2019-02-20
     */
    public function sureOrder()
    {
        $orderMod =& m('order');
        $orderSn = !empty($_REQUEST['orderSn']) ? htmlspecialchars(trim($_REQUEST['orderSn'])) : '';//订单编号
        $orderInfo = $orderMod->getOne(array('cond' => "`order_sn`='{$orderSn}'", 'fields' => 'order_state'));
        if(!$orderInfo){
            $this->setData('',0,'该订单不存在');
        }
        $orderMods = &m('order_'.$orderInfo['store_id']);
        $info = $orderMods->getOne(array('cond'=>'order_sn = '.$orderInfo['order_sn'],'fields' => 'order_state'));
        if(!$info){
            $this->setData(array(),0,'该订单不存在');
        }
        if($info['order_state'] == 0){
            $this->setData(array(),0,'订单已失效！');
        }else{
            $this->setData(array(),1,'');
        }
    }


    /**
     * 秒杀和促销订单生成
     * @author gao
     * @date 2019-02-20
     */
    public function activityOrder()
    {
        $orderMod =& m('order');
        $orderSn = !empty($_REQUEST['orderSn']) ? htmlspecialchars(trim($_REQUEST['orderSn'])) : '';//订单编号
        $orderInfo = $orderMod->getOne(array('cond' => "`order_sn`='{$orderSn}'", 'fields' => 'order_state'));
        if($orderInfo['order_state'] ==0 ){
            $this->setData('',0,'订单已失效');
        }else{
            $this->setData('',1,'');
        }
    }

    /**
     * 获取商品的配送方式
     * @author gao
     * @date 2019-03-05
     */
    public function getSendout()
    {
        $storeGoodsMod=&m('storeGoods');
        $storeGoodsId=!empty($_REQUEST['storeGoodsId']) ? $_REQUEST['storeGoodsId'] : 14499; //店铺商品id
        $sendoutId = !empty($_REQUEST['sendoutId']) ? $_REQUEST['sendoutId'] : 2; //页面默认的选中配送方式
        $sendout=$storeGoodsMod->getGoodsSendoutArr($storeGoodsId);
        $data=array(
            'sendoutId'=>$sendoutId,
            'sendout'=>$sendout
        );
        $this->setData($data,1,'');
    }

    public function orderDetails()
    {
        $orderSn =!empty($_REQUEST['orderSn']) ? $_REQUEST['orderSn'] : '201901022020271356'; //订单编号
        $lang    = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : 29;
        if (empty($orderSn)){
            $this->setData(array(),0,'请传递订单号！');
        }
        if (empty($lang)){
            $this->setData(array(),0,'请传递语言id！');
        }
        $orderMod = &m('order');
        $storeMod = &m('store');
        $cartMod = &m('cart');
        $orderGoodsMod=&m('orderGoods');
        $data = $orderMod->getWxOrderDetails($orderSn,$lang);
        $orderData=$orderMod->getOne(array('cond' => "order_sn = '{$orderSn}'")); //订单信息
        $orderGoodsData=$orderGoodsMod->getData(array('cond' => "order_id = '{$orderSn}'")); //订单商品信息
        foreach ($orderGoodsData as $k => $v){
            $sqls = "SELECT is_free_shipping,attributes FROM bs_store_goods WHERE id = " . $v['goods_id'];
            $rrr = $storeMod->querySql($sqls);
            $orderGoodsData[$k]['is_free_shipping']= $rrr[0]['is_free_shipping'];
            $orderGoodsData[$k]['attributes'] = $rrr[0]['attributes'];
            $orderGoodsData[$k]['attributess'] = explode(',',$orderGoodsData[$k]['attributes']);
            if (in_array(3,$orderGoodsData[$k]['attributess'])){
                $orderGoodsData[$k]['is_exists'] = 1;//匹配到
            }else{
                $orderGoodsData[$k]['is_exists'] = 0;//匹配不到
            }
        }
        $couponMod=&m('coupon');
        $voucherData = $couponMod ->getOne(array("cond"=>" `id` = {$orderData['cid']}"));
        if(!empty($voucherData)){
            switch($voucherData['type']){
                case 2 :
                    $voucherStr = "封顶{$voucherData['money']}元兑换";
                    $orderData['voucherStr'] = $voucherStr;//折扣劵
                    break;
                case 1 :
                    $voucherStr = "满{$voucherData['money']}元抵扣{$voucherData['discount']}元";
                    $orderData['voucherStr'] = $voucherStr;//折扣劵
                    break;
            }
        }else{
            $orderData['voucherStr'] = "暂无";//折扣劵
        }
        $expectTime=$cartMod->expectTime();//模型里面定义的过期时间
        $expireTime=$data[0]['add_time']+$expectTime-time(); //订单过期时间
        if($expireTime<0){
            $expireTime=0;
        }
        $fxCode = $orderMod->getFxCode($orderSn);
        //获取配送地址等数据
        $address = $orderMod->getOrderAddress($orderSn, $data[0]['store_id']);
        $storeImage = $storeMod->getOne(array('cond' => "id = '{$data[0]['store_id']}'"));

        $data = array(
            'address' => $address,
            'orderSn' => $orderSn,
            'fxCode'  => $fxCode,
            'data'    => $data,
            'storeImage' => $storeImage['log'],
            'orderGoodsData' => $orderGoodsData,
            'orderData' => $orderData,
            'expireTime' => $expireTime
        );

        $this->setData($data,1,'');
    }
}

?>
