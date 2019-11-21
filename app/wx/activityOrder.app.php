<?php

/**
 * 微信订单订单中心
 * @author wanyan
 *
 */
class ActivityOrderApp extends BaseWxApp {

    public function __construct() {
        parent::__construct();
    }
    //活动商品页面
    public function index(){
        //模型
        $spikeActiviesMod=&m('spikeActivies');
        $spikeActiviesGoodsMod=&m('spikeActiviesGoods');
        $userMod=&m('user');
        $storeMod=&m('store');
        $storeGoodsMod=&m('areaGood');
        $promotionGoodsMod=&m('goodPromDetail');
        $promotionMod=&m('goodProm');
        $info=!empty($_REQUEST['info']) ? $_REQUEST['info'] : '';
        if(!empty($info)){
            $info=json_decode(base64_decode($info), true);
            $langId=$info['langId'];
            $storeId=$info['storeId'];
            $activityId=$info['activityId'];
            $activityGoodsId=$info['activityGoodsId'];
            $source=$info['source'];
            $goodsNum=$info['goodsNum'];
            $goodsKey=$info['goodsKey'];
            $goodsKeyName=$info['goodsKeyName'];
            $discountPrice=$info['discountPrice'];
        }else{
            $langId = !empty($_REQUEST['langId']) ? intval($_REQUEST['langId']) : 29/*$this->langid*/;
            $storeId = !empty($_REQUEST['storeId']) ? intval($_REQUEST['storeId']) :90/* $this->storeid*/;
            $activityId=!empty($_REQUEST['activityId']) ? intval($_REQUEST['activityId']) : 34; //活动Id
            $activityGoodsId=!empty($_REQUEST['activityGoodsId']) ? intval($_REQUEST['activityGoodsId']) : 496;//活动商品Id
            $source=!empty($_REQUEST['source']) ? intval($_REQUEST['source']) : 2 ; //活动来源 1 ：秒杀 2：促销
            $goodsNum=!empty($_REQUEST['goodsNum']) ? intval($_REQUEST['goodsNum']) : 2 ; //商品数量
            $goodsKey=!empty($_REQUEST['goodsKey']) ? $_REQUEST['goodsKey'] : '1064_1068_1074';  //促销活动商品规格
            $goodsKeyName=!empty($_REQUEST['goodsKeyName']) ? $_REQUEST['goodsKeyName'] : ':L（5-600ML) :热 :少甜';//促销活动商品规格名称
            $discountPrice=!empty($_REQUEST['discountPrice']) ? $_REQUEST['discountPrice'] : 5;//促销活动商品规格价格
        }
        //获取收货地址
        $addr_id = !empty($_REQUEST['addr_id']) ? htmlspecialchars(trim($_REQUEST['addr_id'])) : '';
        if ($addr_id=='') {
            $where = ' and default_addr =1';
        } else {
            $where = ' and id=' . $addr_id;
        }
        $addrSql = "select * from " . DB_PREFIX . 'user_address where distinguish=1 and  user_id=' . $this->userId . $where;
        $userAddress = $userMod->querySql($addrSql); // 获取用户的地址
        if ($addr_id == '0') {
            $addr_id = $userAddress[0]['id'];
        }
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
        //秒杀活动
        if($source==1){
            $activityGoodsData=$spikeActiviesGoodsMod->getOne(array('cond'=>"`id`= '{$activityGoodsId}' AND mark = 1 ",'fields'=>'store_goods_id,discount_price,goods_img,goods_name,goods_key_name,goods_key'));
            $activityData=$spikeActiviesMod->getOne(array('cond'=>"`id`= '{$activityId}' AND mark = 1 ",'fields'=>'store_id'));
            $storeGoodsId=$activityGoodsData['store_goods_id'];
        }
        //促销活动
        if($source==2){
            $activityGoodsData=$promotionGoodsMod->getOne(array('cond'=>"`id`= '{$activityGoodsId}' ",'fields'=>'goods_id,goods_img,goods_name'));
            $activityData=$promotionMod->getOne(array('cond'=>"`id`= '{$activityId}' AND mark = 1 ",'fields'=>'store_id'));
            $storeGoodsId=$activityGoodsData['goods_id'];
            $activityGoodsData['goods_key']=$goodsKey;
            $activityGoodsData['goods_key_name']=$goodsKeyName;
            $activityGoodsData['discount_price']=$discountPrice;
        }
        $shippingPrice=$storeGoodsMod->getOne(array('cond'=>"`id`='{$storeGoodsId}'",'fields'=>'shipping_price'));
        $storeName=$storeMod->getNameById($activityData['store_id'],$langId);
        $totalMoney = ( $activityGoodsData['discount_price']* $goodsNum);
        $this->load($this->shorthand, 'WeChat/goods');
        $this->assign('source',$source);
        $this->assign('activityId',$activityId);
        $this->assign('activityGoodsId',$activityGoodsId);
        $this->assign('storeGoodsId',$storeGoodsId);
        $this->assign('storeId',$activityData['store_id']);
        $this->assign('activityGoodsData',$activityGoodsData);
        $this->assign('shippingPrice',$shippingPrice['shipping_price']);
        $this->assign('goodsNum',$goodsNum);
        $this->assign('langdata', $this->langData);
        $this->assign('langId', $langId);
        $this->assign('symbol', $this->symbol);
        $this->assign('storeName',$storeName);
        $this->assign('totalMoney', number_format($totalMoney, 2));
        $this->assign('addr_id', $addr_id);
        $this->assign('nowlatlon', $userAddress[0]['latlon']);
        $this->display('activityOrder/index.html');
    }
    //订单生成
    public function comfirm() {
        $spikeActiviesGoodsMod=&m('spikeActiviesGoods');
        $storeMod=&m('store');
        $spikeActiviesMod=&m('spikeActivies');
        $storeGoodsMod=&m('storeGoods');
        $promotionGoodsMod=&m('goodPromDetail');
        $promotionMod=&m('goodProm');
        $storeGoodsItemMod=&m('storeGoodItemPrice');
        $orderGoodsMod=&m('orderGoods');
        //语言包
        $this->load($this->shorthand, 'WeChat/goods');
        $a = $this->langData;
        $langId = !empty($_REQUEST['langId']) ? $_REQUEST['langId'] : '0';
        $storeId = !empty($_REQUEST['storeId']) ? $_REQUEST['storeId'] : '';
        $seller_msg = !empty($_REQUEST['seller_msg']) ? $_REQUEST['seller_msg'] : '';
        $user_address_id = !empty($_REQUEST['user_address']) ? $_REQUEST['user_address'] : '';
        $storeGoodsId = !empty($_REQUEST['storeGoodsId']) ? $_REQUEST['storeGoodsId'] : '';
        $goodsKey = !empty($_REQUEST['goodsKey']) ? $_REQUEST['goodsKey'] : '';
        $goodsNum = !empty($_REQUEST['goodsNum']) ? $_REQUEST['goodsNum'] : '';
        $shippingPrice = !empty($_REQUEST['shippingPrice']) ? (int) $_REQUEST['shippingPrice'] : '';
        $activityId = !empty($_REQUEST['activityId']) ? intval($_REQUEST['activityId']) : '';
        $activityGoodsId = !empty($_REQUEST['activityGoodsId']) ? intval($_REQUEST['activityGoodsId']) : '';
        $source = !empty($_REQUEST['source']) ? intval($_REQUEST['source']) : '';
        $sendout = !empty($_REQUEST['sendout']) ? htmlspecialchars($_REQUEST['sendout']) : '1';
        $addr_id = !empty($_REQUEST['addr_id']) ? htmlspecialchars(trim($_REQUEST['addr_id'])) : '0';
        $goodsKeyName=!empty($_REQUEST['goodsKeyName']) ? $_REQUEST['goodsKeyName'] : '';//促销活动商品规格名称
        $discountPrice=!empty($_REQUEST['discountPrice']) ? $_REQUEST['discountPrice'] : 0;//促销活动商品规格价格
        /*$daifu = !empty($_REQUEST['daifu']) ? htmlspecialchars(trim($_REQUEST['daifu'])) : '';*/
        //地址距离比较
        $userAddressMod = &m('userAddress');
        $sql = "select  distance,longitude,latitude from " . DB_PREFIX . "store where id =" . $storeId;
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
        $user_address = $userAddressMod->getAddress($user_address_id);
        $storeGoodInfo = $storeGoodsMod->getOne(array('cond' => "`id` = '{$storeGoodsId}'", 'fields' => "goods_id,deduction"));
        if($source==1){ //秒杀数据信息
            $activityGoodsData=$spikeActiviesGoodsMod->getOne(array('cond'=>"`id`= '{$activityGoodsId}' AND mark = 1 ",'fields'=>'store_goods_id,discount_price,goods_img,goods_name,goods_key_name,goods_key,goods_price,reduce'));
            $activityData=$spikeActiviesMod->getOne(array('cond'=>"`id`= '{$activityId}' AND mark = 1 ",'fields'=>'store_id'));
            $activityGoodsData['discount_rate']=$activityGoodsData['discount'];
        }
        if($source==2){  //促销数据消息
            $activityGoodsData=$promotionGoodsMod->getOne(array('cond'=>"`id`= '{$activityGoodsId}'  ",'fields'=>'goods_id,goods_img,goods_name,goods_price,discount_rate,reduce'));
            $activityData=$promotionMod->getOne(array('cond'=>"`id`= '{$activityId}' AND mark = 1 ",'fields'=>'store_id'));
            $activityGoodsData['goodsKey']=$goodsKey;
            $activityGoodsData['goods_key_name']=$goodsKeyName;
            $activityGoodsData['discount_price']=$discountPrice;
            //判断条件
            $prommotionGoodsData=$promotionGoodsMod->getOne(array('cond'=>"`goods_id`= '{$storeGoodsId}'  AND `prom_id`='{$activityId}' ",'fields'=>'limit_amount,id'));//促销活动商品信息
            $limitNum=$prommotionGoodsData['limit_amount'];//限购数量
            $buyerId=$this->userId;
            $userNum=$orderGoodsMod->getActivityOrderNum(2,$activityId,$storeGoodsId,$buyerId);//用户购买数量
            if(!empty($goodsKey)){
                $singleStoreGoodsItemData=$storeGoodsItemMod->getOne(array('cond'=>"`store_goods_id`= '{$storeGoodsId}'  AND `key`='{$goodsKey}' ",'fields'=>'goods_storage,price,key_name'));//库存信息
                $goodsStorage=$singleStoreGoodsItemData['goods_storage'];//有规格库存
            }else{
                $storeGoodsData=$storeGoodsMod->getOne(array('cond'=>"`id`= '{$storeGoodsId}'",'fields'=>'goods_id,shop_price,goods_storage'));//店铺商品信息
                $goodsStorage=$storeGoodsData['goods_storage'];//无规格库存
            }
            if($goodsNum>($limitNum-$userNum)){ //限购判断
                $this->setData($limitNum,'0','限购'.$limitNum.'件商品');
            }
            if($goodsNum>$goodsStorage){ //库存判断
                $this->setData($goodsStorage,'0','库存不足,库存为'.$goodsStorage);
            }

        }
        $rand = $this->buildNo(1);
        $orderNo = date('YmdHis') . $rand[0];
        //生成小票编号
        $number_order = $this->createNumberOrder($storeId);
        //订单数据
        $count = strpos($user_address['address'], "_");
        if($count==false){
            $addressStr=$user_address['address'];
        }else{
            $addressStr = substr_replace($user_address['address'], "", $count, 1);
        }
        $order_amount = $activityGoodsData['discount_price']* $goodsNum;
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

        $storeCate = $this->getCountryLang($storeId);
        $genaral = array(
            'orderNo' => $orderNo,
            'store_id' => $storeId,
            'sendout' => $sendout, // 1派送 2自提
            'store_name' =>$storeMod->getNameById($activityData['store_id'],$langId),
            'buyer_id' => $this->userId,
            'buyer_name' => $user_address['name'],
            'buyer_email' => $userInfo['email'],
            'shipping_fee' => $shippingPrice,
            'buyer_address' => $addressStr,
            'prom_id' => $activityId,
            'prom_type' => $source,
            'goods_num' => $goodsNum,
            'storeCate' => $storeCate,
            'buyer_phone' => $user_address['phone'],
            'discount_rate' => $activityGoodsData['discount_rate'],
            'goods_id' => $storeGoodsId,
            'discount' => $activityGoodsData['reduce'],
            'goods_name' => $activityGoodsData['goods_name'],
            'goods_price' =>$activityGoodsData['goods_price'],
            'goods_image' => $activityGoodsData['goods_img'],
            'goods_pay_price' => $activityGoodsData['discount_price'],
            'spec_key_name' => $activityGoodsData['goods_key_name'],
            'spec_key' => $goodsKey,
            'goods_type' => 0,
            'seller_msg' => $seller_msg,
            'fx_code' => '',
            'shipping_price' => $shippingPrice,
            'shipping_store_id' =>$activityData['store_id'] ,
            'order_amount' => $order_amount,
            'goods_amount' => $order_amount,
            'number_order' => $number_order, //生成小票编号
            'price' => 0,
             'fx_user_id' =>0,
            'rule_id' => 0,
            'good_id'=>$storeGoodInfo['goods_id'],
            'deduction'=>$storeGoodInfo['deduction'],

        );
        $rs = $this->genOrder($source, $genaral, $activityGoodsData, $langId);
        if ($rs) {
            $info['url'] = "?app=rechargeAmount&act=payment&order_id={$orderNo}&storeid={$storeId}&store_id={$storeId}&lang={$langId}";
            $this->setData($info, $status = 1, $a['order_Order_success']);
        } else {
            $this->setData($info = array(), $status = 0, $a['order_Order_failure']);
        }
    }
    /**
     * 不同活动数据生成订单
     */
    public function genOrder($source, $genInfo, $goodInfo, $lang_id) {
        $fxUserMod = &m('fxuser');
        $orderMod=&m('order');
        $orderDetailMod=&m('orderDetail');
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
            'fx_user_id'=>0,
            'is_source'=>1
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
        if ($source == 1) {
            if (!empty($genInfo['fxPhone'])) {
                $discount = (($goodInfo['price'] * $genInfo['goods_num'])) * $genInfo['discount_rate'] * 0.01;
            }
            // 先插入主订单数据
            $insert_main_data['goods_amount'] = ($goodInfo['discount_price'] * $genInfo['goods_num']);
            $insert_main_data['order_amount'] = ($goodInfo['discount_price'] * $genInfo['goods_num']) + $genInfo['shipping_fee'];
            $insert_main_data['discount'] = $discount;
            $insert_main_data['discount_rate'] = $genInfo['discount_rate'];
            // 插入子表信息
            $insert_sub_data['prom_type'] = 1;
            $insert_sub_data['goods_id'] = $goodInfo['store_goods_id'];
            $insert_sub_data['goods_name'] = addslashes(stripslashes($goodInfo['goods_name']));
            $insert_sub_data['goods_price'] = $goodInfo['goods_price'];
            $insert_sub_data['goods_image'] = $goodInfo['goods_img'];
            $insert_sub_data['spec_key_name'] = $genInfo['spec_key_name'];
            $insert_sub_data['spec_key'] = $genInfo['spec_key'];
        }else{
            if (!empty($genInfo['fxPhone'])) {
                $discount = (($goodInfo['price'] * $genInfo['goods_num'])) * $genInfo['discount_rate'] * 0.01;
            }
            // 先插入主订单数据
            $insert_main_data['goods_amount'] = ($goodInfo['discount_price'] * $genInfo['goods_num']);
            $insert_main_data['order_amount'] = ($goodInfo['discount_price'] * $genInfo['goods_num']) + $genInfo['shipping_fee'];
            $insert_main_data['discount'] = $discount;
            $insert_main_data['discount_rate']= $genInfo['discount_rate'];
            // 插入子表信息
            $insert_sub_data['prom_type'] = 2;
            $insert_sub_data['goods_id'] = $goodInfo['goods_id'];
            $insert_sub_data['goods_name'] = addslashes(stripslashes($goodInfo['goods_name']));
            $insert_sub_data['goods_price'] = $goodInfo['goods_price'];
            $insert_sub_data['goods_image'] = $goodInfo['goods_img'];
            $insert_sub_data['spec_key_name'] = $genInfo['spec_key_name'];
            $insert_sub_data['spec_key'] = $genInfo['spec_key'];
        }
        if ($insert_main_data['order_amount'] <= 0) {
            $insert_main_data['order_amount'] = 0.01;
        }
        try {
            //事务开始
            $orderMod->begin();
            //旧订单表插入
            $main_rs = $orderMod->doInsert($insert_main_data);
            //生成新的订单表数据
            $createOrderRes = $orderMod->createOrder($insert_main_data,2);
            if (empty($main_rs) || empty($createOrderRes)) {
                //事务回滚
                $orderMod->rollback();
                return 0;
            } else {
                //事务提交
                $orderMod->commit();
            }
        } catch (Exception $e) {
            //事务回滚
            $orderMod->rollback();
            writeLog($e->getMessage());
            return 0;
        }

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
            $rs = $orderDetailMod->doInsert($insert_sub_data);
            return $main_rs;
        } else {
            return 0;
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
    /**
     * 生成小票编号
     * @author: luffy
     * @date: 2018-08-09
     */
    public function createNumberOrder($store_id) {
        //获取当天开始结束时间
        $orderMod=&m('order');
        $startDay = strtotime(date('Y-m-d'));
        $endDay = strtotime(date('Y-m-d 23:59:59'));
        $sql = 'select order_sn,number_order from  '
                . DB_PREFIX . 'order where add_time BETWEEN ' . $startDay . ' AND ' . $endDay
                . ' AND mark = 1 and store_id = ' . $store_id . ' order by add_time DESC limit 1';
        $res =$orderMod ->querySql($sql);
        //不管订单存在与否直接加
        $number_order = (int) $res[0]['number_order'] + 1;
        $number_order = str_pad($number_order, 4, 0, STR_PAD_LEFT);
        return $number_order;
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
