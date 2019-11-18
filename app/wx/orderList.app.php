<?php
/**
 * 订单页面
 * @author gao
 * @date 2019-01-24
 */
    class  OrderListApp extends BaseWxApp{
        public function __construct()
        {
            parent::__construct();
        }

        //订单详情页面
        public function  index(){
            //模型
            $userAddresssMod=&m('userAddress');
            $cartMod=&m('cart');
            $userCouponMod=&m('userCoupon');
            //接收参数
            $cart_ids = !empty($_REQUEST['item_id']) ? $_REQUEST['item_id'] : ''; //购物车id
            $lang=!empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid; //语言id
            $userId=$this->userId;//用户Id
            $addr_id = !empty($_REQUEST['addr_id']) ? htmlspecialchars(trim($_REQUEST['addr_id'])) : '';
            if ($addr_id=='') {
                $where = ' and default_addr =1';
            } else {
                $where = ' and id=' . $addr_id;
            }
            $referer = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"]);
            $addrSql = "select * from " . DB_PREFIX . 'user_address where distinguish=1 and user_id=' . $userId . $where;
            $userAddress = $userAddresssMod->querySql($addrSql); // 获取用户的地址
            if($addr_id==''){
                $addr_id=$userAddress[0]['id'];
            }
            $this->assign('nowlatlon', $userAddress[0]['latlon']);
            if (empty($userAddress[0])) { // 添加*/
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
            $goodsInfo = $cartMod->getCartGoods($cart_ids,$lang);//获取购物车商品信息
            $userGoods=$goodsInfo['userGoods']; //用户购物车购买商品信息
            $goodsNum=$goodsInfo['goodsNum'];//总商品数量
            $totalMoney=$goodsInfo['totalMoney'];//总金额
            $storeId=$userGoods[0]['store_id'];//店铺Id
            $sendoutDisplay=$goodsInfo['sendoutDisplay']; //是否显示商品的配送方式  有值代表不显示 没值代表显示
            $voucherParameter=$goodsInfo['voucherParameter'];//兑换劵限制条件数组
            $pointData=$cartMod->getPointMoney($storeId,$userId,$totalMoney);//睿积分抵扣
            $fxData=$cartMod->getFxCode($totalMoney,$userId,$pointData['maxAccount']);//分销抵扣
            $discountMoney=number_format($totalMoney,2,".","")-$pointData['maxAccount']-$fxData['fxDiscount'];//抵扣后的金额
            $couponData=$userCouponMod->getValidCoupons($userId,$lang,1,$storeId,$totalMoney);//抵扣劵信息
            $voucherData=$userCouponMod->getValidCoupons($userId,$lang, 2, 0, 0,$voucherParameter);//兑换劵信
            $this->assign('sendoutDisplay',$sendoutDisplay);
            $this->assign("cartIds",$cart_ids);
            $this->assign('couponData',$couponData);
            $this->assign('voucherData',$voucherData);
            $this->assign('fxData',$fxData);
            $this->assign('pointData',$pointData);
            $this->assign('totalMoney',number_format($totalMoney,2,".",""));
            $this->assign('discountMoney',$discountMoney);
            $this->assign('goodsNum',$goodsNum);
            $this->assign('userGoods',$userGoods);
            $this->assign('storeName',$userGoods[0]['store_name']);
            $this->assign("langId",$lang);
            $this->assign("storeId",$storeId);
            $this->assign('referer', $referer);
            $this->assign('latlon', $userAddress[0]['latlon']);
            $this->assign('addr_id',$addr_id);
            $this->display("orderList/index.html");
        }


        //确认订单页面
        public function  comfirmOrder(){
            //模型
            $userAddresssMod=&m('userAddress');
            $orderMod = &m('order');
            $orderGoodsMod = &m('orderGoods');
            $storeGoodsMod =&m('storeGoods');
            $couponMod = &m('coupon');
            //接收参数
            $orderSn = !empty($_REQUEST['order_id']) ?  $_REQUEST['order_id'] : '' ; //订单编号
            $lang=!empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid; //语言id
            $addr_id = !empty($_REQUEST['addr_id']) ? $_REQUEST['addr_id'] : 0; //地址id
            $orderData = $orderMod ->getOne(array("cond" => "`order_sn` ='{$orderSn}'"));
            $cond= array(
                "cond" => " `order_id` like '{$orderSn}%'"
            );
            $voucherData = $couponMod ->getOne(array("cond"=>" `id` = {$orderData['cid']}"));
            if(!empty($voucherData)){
                    switch($voucherData['type']){
                        case 2 :
                            $voucherStr = "封顶{$voucherData['money']}元兑换";
                            $orderData['voucherStr'] = $voucherStr;//折扣劵
                            break;
                        case 1 :
                            $voucherStr = "满{$voucherData['money']}抵扣{$voucherData['discount']}元";
                            $orderData['voucherStr'] = $voucherStr;//折扣劵
                            break;
                    }
                    }else{
                    $orderData['voucherStr'] = "暂无";//折扣劵
                    }
            $orderGoodsData = $orderGoodsMod->getData($cond);
            foreach($orderGoodsData as $key =>$val){
                $marketPrice = $storeGoodsMod->getOne(array("cond"=>"`id` = {$val['goods_id']}","fields" => "market_price"));
                $orderGoodsData[$key]['market_price'] = $marketPrice['market_price'] ;
            }
            if(strlen($orderData['sendout']) != 1) {  //拆分单
                //配送属性
                $sendout = explode(',', $orderData['sendout']);
                $sort = array();
                foreach ($sendout as $k => $v) {
                    $sendoutTemp = explode('-', $v);
                    $sort[] = $sendoutTemp[1];
                }
                array_multisort($sort, SORT_ASC, $sendout); //排序 按照配送方式排序
                //组装各个配送方式的商品
                $goodsInfo = array();
                foreach ($sendout as $k => $v) {
                    $sendoutTemp = explode('-', $v);
                    foreach ($orderGoodsData as $key => $val) {
                        if ($val['goods_id'] == $sendoutTemp[0] && $val['spec_key'] == $sendoutTemp[2]) {
                            $goodsInfo[$sendoutTemp[1]][] = $val;
                        }
                    }
                }
            }else{ //不拆分单
                $goodsInfo[$orderData['sendout']] = $orderGoodsData;
            }
            $userId=$this->userId;//用户Id
            if ($addr_id== 0 ) {
                $where = ' and default_addr =1';
            } else {
                $where = ' and id=' . $addr_id;
            }
            $referer = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"]);
            $addrSql = "select * from " . DB_PREFIX . 'user_address where distinguish=1 and user_id=' . $userId . $where;
            $userAddress = $userAddresssMod->querySql($addrSql); // 获取用户的地址
            if($addr_id==0){
                $addr_id=$userAddress[0]['id'];
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
            $this->assign('orderSn',$orderSn);
            $this->assign('orderData',$orderData);
            $this->assign("langId",$lang);
            $this->assign('referer', $referer);
            $this->assign('latlon', $userAddress[0]['latlon']);
            $this->assign('addr_id',$addr_id);
            $this->assign('goodsInfo',$goodsInfo);
            $this->display("orderList/comfirmOrder.html");
        }

        //修改订单的收货地址
        public function updateOrderAddress(){
            //模型
            $orderMod = &m('order');
            $userAddresssMod=&m('userAddress');
            $userMod =&m('user');
            //接收参数
            $orderSn = !empty($_REQUEST['orderSn']) ?  $_REQUEST['orderSn'] : '' ; //订单编号
            $addr_id = !empty($_REQUEST['addr_id']) ? $_REQUEST['addr_id'] : '';  //地址id
            $peiTime = !empty($_REQUEST['pei_time']) ? $_REQUEST['pei_time'] : 0; //配送时间
            $daifu=!empty($_REQUEST['daifu']) ?$_REQUEST['daifu']:''; //是否代付
            $peiTime =strtotime($peiTime);
            $orderData = $orderMod ->getOne(array("cond" => "`order_sn` ='{$orderSn}'"));
            if(strlen($orderData['sendout']) == 1){ //只有一种配送方式的情况
                if($orderData['sendout'] == 2){
                    if(empty($addr_id)){
                        $this->setData('',0,'请填写收货地址');
                    }else{
                        $addressData = $userAddresssMod->getOne(array("cond"=>"`id` = {$addr_id}"));
                        if(empty($addressData)){
                            $this->setData('',0,'请填写收货地址');
                        }
                        //获取店铺距离地址的距离
                        $distance = $this->getDistance($addr_id,$orderData['store_id']);
                        if($distance){
                            $this->setData('',0,'不在配送范围内');
                        }
                    }
                }
            }else {
                $sendout = explode(',', $orderData['sendout']);
                foreach ($sendout as $k => $v) {
                    $sendoutTemp = explode('-', $v);
                    $sort[] = $sendoutTemp[1];
                }
                if (in_array(2, $sort)) {
                    if (empty($addr_id)) {
                        $this->setData('', 0, '请填写收货地址');
                    } else {
                        $addressData = $userAddresssMod->getOne(array("cond" => "`id` = {$addr_id}"));
                        if (empty($addressData)) {
                            $this->setData('', 0, '请填写收货地址');
                        }
                        $distance = $this->getDistance($addr_id, $orderData['store_id']);
                        if ($distance) {
                            $this->setData('', 0, '不在配送范围内');
                        }
                    }
                }
            }
            //收货地址信息
            $addressInfo = $userAddresssMod->getOne(array("cond"=>"`id` = {$addr_id}"));
            $count = strpos($addressInfo['address'], "_");
            if($count==false){
                $address=$addressInfo['address'];
            }else{
                $address = substr_replace($addressInfo['address'], "", $count, 1);
            }
            //用户信息
            $userInfo = $userMod ->getOne(array("cond"=>"`id` = {$this->userId}"));
            //更改新表数据
            $storeId =$orderData['store_id'];
            $newOrderMod = &m('order'.$storeId);
            $newChildOrderData = $newOrderMod ->getData(array("cond" => "`order_sn` like '{$orderSn}%' and `mark` = 1"));
            foreach($newChildOrderData as $key=>$val){
                if($val['sendout'] == 2){
                    $sql = "UPDATE bs_order_details_{$storeId} SET 
                           address_id = {$addr_id}
                            WHERE order_id = {$val['id']} ";
                    $orderMod->doEditSql($sql);
                }
                if($val['sendout'] == 1){
                    $sql = "UPDATE bs_order_details_{$storeId} SET 
                            sendout_time ={$peiTime}
                            WHERE order_id = {$val['id']} ";
                    $orderMod->doEditSql($sql);
                }
            }
            //更改老表数据
            $childOrderData = $orderMod->getData(array("cond" => "`order_sn` like '{$orderSn}%' and `mark` = 1"));
            foreach($childOrderData as $key=>$val){
                 if($val['sendout'] == 2){
                     $sql = "UPDATE bs_order SET 
                            buyer_name='{$addressInfo['name']}',buyer_address='{$address}',buyer_phone={$addressInfo['phone']} 
                            WHERE order_sn = '{$val['order_sn']}' ";
                    $orderMod->doEditSql($sql);
                 }
                 if($val['sendout'] == 1){
                     $sql = "UPDATE bs_order SET 
                            buyer_name='{$userInfo['username']}',buyer_address='自提',buyer_phone={$userInfo['phone']},pei_time={$peiTime} 
                            WHERE order_sn = '{$val['order_sn']}' ";
                    $orderMod->doEditSql($sql);
                 }
            }
            if($daifu ==1){
                $info['url'] = "?app=fxPayment&act=index&storeid={$storeId}&store_id={$storeId}&orderNo={$orderData['order_sn']}&orderid={$orderData['order_id']}&lang={$this->langid}&daifu=1";
            }else{
                $info['url'] = "?app=rechargeAmount&act=payment&order_id={$orderSn}&store_id={$storeId}&storeid={$storeId}&lang={$this->langid}";
            }
            $this->setData($info, $status = 1, '确认订单成功,前往支付');
        }

        //活动商品页面
        public function activityIndex(){
            //模型
            $spikeActiviesMod=&m('spikeActivies');
            $spikeActiviesGoodsMod=&m('spikeActiviesGoods');
            $userMod=&m('user');
            $storeMod=&m('store');
            $storeGoodsMod=&m('storeGoods');
            $promotionGoodsMod=&m('goodPromDetail');
            $promotionMod=&m('goodProm');
            $info=!empty($_REQUEST['info']) ? $_REQUEST['info'] : '';
            $referer = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"]);
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
                $langId = !empty($_REQUEST['langId']) ? intval($_REQUEST['langId']) : 0/*$this->langid*/;
                $storeId = !empty($_REQUEST['storeId']) ? intval($_REQUEST['storeId']) :0/* $this->storeid*/;
                $activityId=!empty($_REQUEST['activityId']) ? intval($_REQUEST['activityId']) : 0; //活动Id
                $activityGoodsId=!empty($_REQUEST['activityGoodsId']) ? intval($_REQUEST['activityGoodsId']) : 0;//活动商品Id
                $source=!empty($_REQUEST['source']) ? intval($_REQUEST['source']) : 1 ; //活动来源 1 ：秒杀 2：促销
                $goodsNum=!empty($_REQUEST['goodsNum']) ? intval($_REQUEST['goodsNum']) : 0; //商品数量
                $discountPrice=!empty($_REQUEST['discountPrice']) ? $_REQUEST['discountPrice'] : 0;//促销活动商品规格价格
                $orderSn = !empty($_REQUEST['orderSn']) ? $_REQUEST['orderSn'] : '';//订单编号
                $info['langId'] =   $langId;
                $info['storeId'] = $storeId;
                $info['activityId'] = $activityId;
                $info['activityGoodsId'] = $activityGoodsId;
                $info['source'] = $source;
                $info['goodsNum'] = $goodsNum ;
                $info['discountPrice'] = $discountPrice;
                $info['orderSn'] = $orderSn;
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
                $activityGoodsData=$spikeActiviesGoodsMod->getOne(array('cond'=>"`id`= '{$activityGoodsId}' AND mark = 1 ",'fields'=>'store_goods_id,discount_price,goods_img,goods_name,goods_key_name,goods_key,goods_price'));
                $activityData=$spikeActiviesMod->getOne(array('cond'=>"`id`= '{$activityId}' AND mark = 1 ",'fields'=>'store_id'));
                $storeGoodsId=$activityGoodsData['store_goods_id'];
                $activityGoodsData['isFreeShipping']=$storeGoodsMod->isFreeShipping($storeGoodsId);
                $activityGoodsData['sendout']=$storeGoodsMod->getGoodsSendoutArr($storeGoodsId);
                $activityGoodsData['sendoutStr']=$storeGoodsMod->getGoodsSendout($storeGoodsId);
                $activityGoodsData['sendoutIndex']=key($activityGoodsData['sendout']);
                $activityGoodsData['sendoutValue']=current($activityGoodsData['sendout']);
                $info['goodsKey'] = $activityGoodsData['goods_key'];
                $info['goodsKeyName'] = $activityGoodsData['goods_key_name'];
                $info['discountPrice'] = $activityGoodsData['discount_price'];
                $info['goodsPrice'] = $activityGoodsData['goods_price'];
            }
            //促销活动
            if($source==2){
                $activityGoodsData=$promotionGoodsMod->getOne(array('cond'=>"`id`= '{$activityGoodsId}' ",'fields'=>'goods_id,goods_img,goods_name,goods_price'));
                $activityData=$promotionMod->getOne(array('cond'=>"`id`= '{$activityId}' AND mark = 1 ",'fields'=>'store_id'));
                $storeGoodsId=$activityGoodsData['goods_id'];
                $activityGoodsData['isFreeShipping']=$storeGoodsMod->isFreeShipping($storeGoodsId);
                $activityGoodsData['sendout']=$storeGoodsMod->getGoodsSendoutArr($storeGoodsId);
                $activityGoodsData['sendoutStr']=$storeGoodsMod->getGoodsSendout($storeGoodsId);
                $activityGoodsData['sendoutIndex']=key($activityGoodsData['sendout']);
                $activityGoodsData['sendoutValue']=current($activityGoodsData['sendout']);
                $activityGoodsData['goods_key']=$goodsKey;
                $activityGoodsData['goods_key_name']=$goodsKeyName;
                $activityGoodsData['discount_price']=$discountPrice;
                $info['goodsPrice'] = $activityGoodsData['goods_price'];
            }
            $shippingPrice=$storeGoodsMod->getOne(array('cond'=>"`id`='{$storeGoodsId}'",'fields'=>'shipping_price'));
            $storeName=$storeMod->getNameById($activityData['store_id'],$langId);
            $totalMoney = ( $activityGoodsData['discount_price']* $goodsNum);
            $this->load($this->shorthand, 'WeChat/goods');
            $info['storeGoodsId']=$storeGoodsId;
            $this->assign('activityInfo',base64_encode(json_encode($info)));
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
            $this->assign('referer', $referer);
            $this->display('orderList/activityIndex.html');
        }


        /**
         * 确认下单按钮操作
         * @author wanyan
         * @date 2017-10-23
         */
        public function comfirm()
        {
            $cartMod =& m('cart');
            $storeMod =& m('store');
            $userAddressMod =& m('userAddress');
            $couponMod =& m('coupon');
            $userCouponMod =& m('userCoupon');
            $userMod = &m('user');
            $orderMod =& m('order');
            $orderDetailMod =& m('orderDetail');
            $cart_ids = !empty($_REQUEST['cart_ids']) ? htmlspecialchars($_REQUEST['cart_ids']) : ''; //购物车id
            $seller_msg = !empty($_REQUEST['seller_msg']) ? htmlspecialchars($_REQUEST['seller_msg']) : ''; //留言
            $addressId = !empty($_REQUEST['addressId']) ? htmlspecialchars($_REQUEST['addressId']) : ''; //地址id
            $fxPhone = !empty($_REQUEST['fxPhone']) ? $_REQUEST['fxPhone'] : ''; //分销code
            $storeid = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : ''; //店铺
            $lang = !empty($_REQUEST['lang']) ? intval($_REQUEST['lang']) : $this->langid; //语言
            $discount_rate = !empty($_REQUEST['discount_rate']) ? intval($_REQUEST['discount_rate']) : '';//分销抵扣比例
            $point = !empty($_REQUEST['point']) ? $_REQUEST['point'] : ''; //睿积分数值
            $price = !empty($_REQUEST['price']) ? $_REQUEST['price'] : '';//睿积分抵扣金额
            $sendout = !empty($_REQUEST['sendout']) ? $_REQUEST['sendout'] : ''; //配送方式 数组形式 商品id-配送方式
            $shippingfee = !empty($_REQUEST['shippingfee']) ? htmlspecialchars($_REQUEST['shippingfee']) : 0; //邮费
            $fx_user_id = !empty($_REQUEST['fx_user_id']) ? intval($_REQUEST['fx_user_id']) : ''; //分销用户id
            $rule_id = !empty($_REQUEST['rule_id']) ? intval($_REQUEST['rule_id']) : ''; //分销规则id
            $daifu = !empty($_REQUEST['daifu']) ? $_REQUEST['daifu'] : ''; //是否代付
            $couponId = !empty($_REQUEST['couponId']) ? $_REQUEST['couponId'] : 0;//优惠劵Id
            $userCouponId = !empty($_REQUEST['userCouponId']) ? $_REQUEST['userCouponId'] : 0;//用户优惠劵Id
            $discount_price = !empty($_REQUEST['discount_price']) ? $_REQUEST['discount_price'] : 0;//优惠劵优惠金额`
            //配送方式数组处理
            foreach ($sendout as $key => $val) {
                $shippingMethodArr = explode('-', $val);
                $shippingMethod[] = $shippingMethodArr[1];
            }
            $uniqueShippingMethod = array_unique($shippingMethod);

            if (count($uniqueShippingMethod) == 1) { //判断是否是同一配送方式
                $sendoutStr = $uniqueShippingMethod[0];
            } else {
                $sendoutStr = implode(',', $sendout);
            }

            //生成小票编号
            $number_order = $this->createNumberOrder($storeid);
            if (!empty($cart_ids)) { //购物车商品信息
                //订单信息
                //订单号生成
                $rand = $this->buildNo(1);
                $orderNo = date('YmdHis') . $rand[0];
                $sql = "select c.user_id,c.store_id,c.shipping_store_id,SUM(c.goods_price*goods_num) as goods_amount,u.email,u.username from " .
                    DB_PREFIX . "cart as c  LEFT JOIN " . DB_PREFIX . "user as u ON c.user_id = u.id  where c.`id` in  ({$cart_ids}) ";
                $orderInfo = $cartMod->querySql($sql);
                //获取购物车信息
                $goodsInfo = $cartMod->getGoodByCartId($cart_ids);
                //店铺名称
                $orderInfo[0]['store_name'] = $storeMod->getNameById($orderInfo[0]['shipping_store_id'], $lang);
                $storeName = $orderInfo[0]['store_name']; //店铺名称
                $goodsAmount = $orderInfo[0]['goods_amount']; //订单商品金额
                $storeId = $orderInfo[0]['shipping_store_id'];  //店铺id
                $buyerId = $orderInfo[0]['user_id']; //购买者
            } else {  //秒杀商品信息
                //模型
                $spikeActiviesGoodsMod =& m('spikeActiviesGoods');
                $promotionGoodsMod =& m('goodPromDetail');
                $activityInfo = !empty($_REQUEST['activityInfo']) ? $_REQUEST['activityInfo'] : ''; //活动信息 促销或者秒杀
                $activityInfo = json_decode(base64_decode($activityInfo), true);
                $storeId = $activityInfo['storeId'];  //店铺id
                $langId = $activityInfo['langId']; //语言id
                $storeName = $storeMod->getNameById($storeId, $langId); //店铺名称
                $goodsNum = $activityInfo['goodsNum']; //商品数量
                $goodsAmount = $activityInfo['discountPrice'] * $goodsNum; //订单商品金额
                $buyerId = $this->userId; //购买者
                $source = $activityInfo['source']; //活动来源 1秒杀 2促销
                $activityGoodsId = $activityInfo['activityGoodsId']; //活动商品表id
                $activityId = $activityInfo['activityId']; //活动id
                $orderNo = $activityInfo['orderSn'];
                $orderInfo = $orderMod->getOne(array('cond' => "`order_sn` = '{$orderNo}'"));
                $data = array(
                    'order_sn' => $orderNo,
                    'buyer_id' => $this->userId
                );

                if (!empty($orderInfo)) {
                    $info['url'] = "?app=orderList&act=comfirmOrder&order_id={$orderNo}&store_id={$storeId}&storeid={$storeId}orderId={$orderInfo['order_id']}&lang={$lang}";
                    $this->setData($info, 1, '订单已生成,快去付款吧');
                }
                if ($source == 1) {
                    $activityGoodsData = $spikeActiviesGoodsMod->getOne(array('cond' => "`id`= '{$activityGoodsId}' AND mark = 1 ", 'fields' => 'store_goods_id,discount_price,goods_img,goods_name,goods_key_name,goods_key,goods_num,limit_num'));
                }
                //促销活动
                if ($source == 2) {
                    $activityGoodsData = $promotionGoodsMod->getOne(array('cond' => "`id`= '{$activityGoodsId}' ", 'fields' => 'goods_id,goods_img,goods_name,limit_amount'));
                }
                $goodsInfo = array(
                    array(
                        'goods_id' => $activityInfo['storeGoodsId'],
                        'goods_name' => $activityGoodsData['goods_name'],
                        'store_id' => $storeId,
                        'spec_key' => $activityInfo['goodsKey'],
                        'goods_num' => $activityInfo['goodsNum'],
                        'spec_key_name' => $activityInfo['goodsKeyName'],
                        'user_id' => $buyerId,
                        'fx_code' => '',
                        'discount_price' => $activityInfo['discountPrice'],
                        'shipping_price' => 0,
                        'prom_id' => $activityId,
                        'prom_type' => $source,
                        'goods_price' => $activityInfo['goodsPrice']
                    )
                );
            }
            //商品库存判断
            foreach ($goodsInfo as $k => $v) {
                $invalid = $cartMod->isInvalid($v['goods_id'], $v['spec_key']);
                if ($invalid < $v['goods_num']) {
                    $this->setData(array(), '0', $v['goods_name'] . '商品库存不足');
                }
            }
            //获取用户地址信息
            $user_address = $userAddressMod->getAddress($addressId);
            //分销优惠金额计算
            if (!empty($fxPhone)) {
                $fxuserMod = &m('fxuser');
                $fxuserInfo = $fxuserMod->getOne(array('cond' => "fx_code = '{$fxPhone}' AND mark = 1"));
                $discount = (($goodsAmount - $price - $discount_price) * $fxuserInfo['discount'] * 0.01);
            } else {
                $discount = 0;
            }
            //是否使用了优惠劵
            if (!empty($couponId)) {
                $order_amount = $goodsAmount + $shippingfee - $discount - $price - $discount_price;
            } else {
                $order_amount = $goodsAmount + $shippingfee - $discount - $price;
            }
            if ($order_amount <= 0) {
                $order_amount = 0;
            }
            //收货信息
            $count = strpos($user_address['address'], "_");
            if ($count == false) {
                $addressStr = $user_address['address'];
            } else {
                $addressStr = substr_replace($user_address['address'], "", $count, 1);
            }
            $userData = $userMod->getOne(array('cond' => "`id` = '{$this->userId}' and mark=1", 'fields' => 'phone'));
            if (empty($user_address)) {
                $user_address['phone'] = $userData['phone'];
                $user_address['name'] = $userData['phone'];
            }
            // 主订单数据
            $insert_main_data = array(
                'order_sn' => $orderNo,
                'store_id' => $storeId,
                'sendout' => $sendoutStr,
                'store_name' => $storeName,
                'buyer_id' => $buyerId,
                'buyer_name' => addslashes($user_address['name']),
                'buyer_email' => '',
                'goods_amount' => $goodsAmount,
                'order_amount' => $order_amount,
                'shipping_fee' => $shippingfee,
                'order_state' => 10,
                'order_from' => 2,
                'buyer_address' => $addressStr,
                'buyer_phone' => $user_address['phone'],
                'discount' => $discount,
                'fx_discount_rate' => $discount_rate,
                'fx_phone' => $fxPhone,
                'add_time' => time(),
                'number_order' => $number_order, //生成小票编号
                'seller_msg' => $seller_msg, //订单的留言
                'sub_user' => 2,
                'is_source' => 1,
                'fx_user_id' => $fx_user_id,
            );
            //优惠劵
            if (!empty($couponId)) {
                $insert_main_data['cid'] = $couponId;
                $insert_main_data['cp_amount'] = $discount_price;
            }
            try {
                //事务开始
                $orderMod->begin();
                //原来生成订单数据
                $main_rs = $orderMod->doInsert($insert_main_data);
                //生成新的订单表数据
                $insert_main_data['cp_amount'] = $discount_price;
                $insert_main_data['pd_amount'] = $price;
                $insert_main_data['fx_money'] = $discount;
                $createOrderRes = $orderMod->createOrder($insert_main_data, 2); //新单订单数据生成
                if (empty($main_rs) || empty($createOrderRes)) {
                    //事务回滚
                    $orderMod->rollback();
                    $this->setData(array(), 0, '提交订单失败');
                } else {
                    //事务提交
                    $orderMod->commit();
                }
            } catch (Exception $e) {
                //事务回滚
                $orderMod->rollback();
                writeLog($e->getMessage());
                $this->setData(array(), 0, '提交订单失败');
            }
            if (!empty($couponId)) {
                //用户使用优惠劵记录
                $couponLogMod =& m('couponLog');
                $couponLogData = array(
                    'user_coupon_id' => $userCouponId,
                    'coupon_id' => $couponId,
                    'user_id' => $this->userId,
                    'order_id' => $main_rs,
                    'order_sn' => $orderNo,  // by xt 2019.03.21
                    'add_time' => time()
                );
                $couponLogMod->doInsert($couponLogData);
            }
            //生成2维码
            $code = $this->goodsZcode($storeid, $lang, $main_rs);
            $cond['order_url'] = $code;
            $urldata = array(
                "table" => "order",
                'cond' => 'order_id = ' . $main_rs,
                'set' => "order_url='" . $code . "'",
            );
            $orderMod->doUpdate($urldata);
            // 先插入子订单
            if ($main_rs) {
                foreach ($goodsInfo as $k => $v) {
                    if (!empty($activityInfo)) {
                        $goodsPayPrice = $v['discount_price'];
                        $goodsPrice = $v['goods_price'];
                        $insert_sub_data['prom_id'] = $v['prom_id'];
                        $insert_sub_data['prom_type'] = $v['prom_type'];
                    } else {
                        $goodsPayPrice = $orderMod->getGoodsPayPrice($v['store_id'], $v['goods_id'], $v['spec_key']);
                        $goodsPrice = $orderMod->getPrice($v['store_id'], $v['goods_id'], $v['spec_key']);
                    }
                    $insert_sub_data = array(
                        'order_id' => $orderNo,
                        'goods_id' => $v['goods_id'],
                        'goods_name' => addslashes(stripslashes($v['goods_name'])),
                        'goods_price' => $goodsPrice,
                        'goods_num' => $v['goods_num'],
                        'goods_image' => $this->getGoodImg($v['goods_id'], $v['store_id']),
                        'goods_pay_price' => $goodsPayPrice,
                        'spec_key_name' => $v['spec_key_name'],
                        'spec_key' => $v['spec_key'],
                        'store_id' => $v['store_id'],
                        'buyer_id' => $v['user_id'],
                        'goods_type' => 0,
                        'order_state' => 10,
                        'fx_code' => $v['fx_code'],
                        'discount' => ($v['goods_price']) * ($fxuserInfo['discount']) * 0.01,
                        'discount_rate' => $fxuserInfo['discount'],
                        'shipping_price' => $v['shipping_price'],
                        'shipping_store_id' => $v['shipping_store_id'],
                        'add_time' => time(),
                        'good_id' => $this->getGoodId($v['goods_id']),
                        'deduction' => $this->getDeduction($v['goods_id'])
                    );
                    if (!empty($activityInfo)) {
                        $insert_sub_data['prom_id'] = $v['prom_id'];
                        $insert_sub_data['prom_type'] = $v['prom_type'];
                    }
                    $rs[] = $orderDetailMod->doInsert($insert_sub_data);
                }
                $rs = array_filter($rs);
                $store_cate = $this->getStoreCate($goodsInfo[0]['store_id']);//站点国家
                $store_id = $goodsInfo[0]['store_id'];//选取的购物车商品的区域商品id
                if (count($rs)) {
                    if ($this->delCart($cart_ids)) {
                        //添加积分优惠
                        if ($price && $price != '0.00') {
                            $this->getPointPrice($orderNo, $price, $point);
                        }
                        //分单
                        $orderMod =& m('order');
                        $res = $orderMod->separateOrder($orderNo, 2, 1);

                        //代付
                        if ($daifu == 1) {
                            $info['url'] = "?app=fxPayment&act=index&storeid={$storeid}&store_cate={$store_cate}&store_id={$store_id}&fx_user_id={$fx_user_id}&rule_id={$rule_id}&orderNo={$orderNo}&orderid={$main_rs}&lang={$lang}&daifu=1";
                        } else {
                            $info['url'] = "?app=orderList&act=comfirmOrder&order_id={$orderNo}&store_cate={$store_cate}&store_id={$store_id}&storeid={$storeid}&fx_user_id={$fx_user_id}&rule_id={$rule_id}&orderId={$main_rs}&lang={$lang}";
                        }
                        $this->setData($info, $status = 1, '提交订单成功,前往支付');
                    } else {
                        $this->setData($info = array(), $status = 0, '提交订单失败');
                    }
                }
            }
        }

        /**
         * 检测Cartid 是否存在
         * @author wanyan
         * @date 2018-01-11
         */
        public function getCartId() {
            //语言包
            $this->load($this->shorthand, 'WeChat/goods');
            $a = $this->langData;
            $cartMod = &m('cart');
            $cart_id = !empty($_REQUEST['cart_ids']) ? htmlspecialchars($_REQUEST['cart_ids']) : '';
            $sql = "select `id` from " . DB_PREFIX . "cart where `id` = '{$cart_id}'";
            $info = $cartMod->querySql($sql);
            if ($info[0]['id']) {
                $this->setData($info = array(), $status = 1, $message = '');
            } else {
                $this->setData($info = array(), $status = 0, $a['order_Havebeen_submitted']);
            }
        }
        //生成二维码
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
        //获取站点国家
        public function getStoreCate($storeId){
            $sql="select store_cate_id from ".DB_PREFIX.'store where id='.$storeId;
            $storeMod=&m('store');
            $storeInfo=$storeMod->querySql($sql);
            return $storeInfo[0]['store_cate_id'];
        }

        //获取原始商品id
        function  getGoodId($id){
            $orderDetailMod=&m('orderDetail');
            $sql="select goods_id from ".DB_PREFIX.'store_goods where id='.$id;
            $goodInfo=$orderDetailMod->querySql($sql);
            return $goodInfo[0]['goods_id'];

        }
        //获取商品扣除方式
        function getDeduction($id){
            $orderDetailMod=&m('orderDetail');
            $sql="select deduction from ".DB_PREFIX.'store_goods where id='.$id;
            $goodInfo=$orderDetailMod->querySql($sql);
            return $goodInfo[0]['deduction'];
        }
        /**
         * 删除下单完成后删除购物车中数据
         * @author wanyan
         * @date 2017-11-9
         */
        public function delCart($cart_ids) {
            $cartMod=&m('cart');
            $query = array(
                'cond' => " `id` in ({$cart_ids})"
            );
            $rs = $cartMod->doDelete($query);
            if ($rs) {
                return true;
            } else {
                return false;
            }
        }
        /**
         * 获取当前商品图片
         * @author wanyan
         * @date 2017-10-20
         */
        public function getGoodImg($goods_id, $store_id) {
            $storeMod=&m('store');
            $sql = 'select gl.original_img  from  '
                . DB_PREFIX . 'store_goods as g  left join '
                . DB_PREFIX . 'goods as gl on g.goods_id = gl.goods_id where g.id  = ' . $goods_id;
            $rs = $storeMod->querySql($sql);
            return $rs[0]['original_img'];
        }
        /**
         * 生成小票编号
         * @author: luffy
         * @date: 2018-08-09
         */
        public function createNumberOrder($storeid) {
            //获取当天开始结束时间
            $orderMod=&m('order');
            $startDay = strtotime(date('Y-m-d'));
            $endDay = strtotime(date('Y-m-d 23:59:59'));
            $sql = 'select order_sn,number_order from  '
                . DB_PREFIX . 'order where add_time BETWEEN ' . $startDay . ' AND ' . $endDay
                . ' AND mark = 1 and store_id = ' . $storeid . ' order by add_time DESC limit 1';
            $res =$orderMod->querySql($sql);
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



        /*
        * 积分兑换优惠处理
        * @auhtor lee
        * @date 2018-5-7 15:35:33
        */
        public function getPointPrice($order_id, $price, $point) {
            $this->load($this->shorthand, 'userCenter/userCenter');
            $a = $this->langData;
            $orderMod = &m('order');
            $userMod = &m('user');
            $pointSiteMod = &m('point');
            $storePointMod = &m('storePoint');
            $storeMod = &m('store');
            $curMod = &m('currency');
            $user_id = $this->userId;
            $user_info = $userMod->getOne(array("cond" => "id=" . $user_id));
            $order_info = $orderMod->getOne(array("cond" => "order_sn = '{$order_id}'"));
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
                'order_sn' => "{$order_id}"
            );
            $order_res = $orderMod->doEditSpec($order_cond, $order_arr);
            if ($order_res) {
                //扣除用户积分
                $user_point = $user_info['point'] - $point;
                $userMod->doEdit($user_id, array("point" => $user_point));
                //积分日志
                $logMessage = "订单：" . $order_id . " 使用：" . $point . "睿积分";
                $this->addPointLog($user_info['phone'], $logMessage, $user_id, 0, $point, $order_id);
                return $order_res;
            } else {
                $this->setData(array(), $status = 0, '使用睿积分失败');
            }
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


        /**
         * 更改订单金额接口
         * @author gao
         * @date 2019-02-14
         */
        public function changeDiscount() {
            $fxuserMod      = &m('fxuser');
            $fxruleMod      = &m('fxrule');
            $storeFareRuleMod = &m('storeFareRule');
            $storeGoodsMod = &m('storeGoods');
            $fxCode         = !empty($_REQUEST['fxCode'])   ? htmlspecialchars(trim($_REQUEST['fxCode'])) : ''; //分销码
            $totalMoney     = !empty($_REQUEST['totalMoney'])    ? $_REQUEST['totalMoney'] : 0; //订单总金额
            $ruiDiscount    = !empty($_REQUEST['ruiDiscount'])   ? $_REQUEST['ruiDiscount'] : 0; //睿积分抵扣金额
            $voucherDiscunt = !empty($_REQUEST['voucherDiscunt'])  ? $_REQUEST['voucherDiscunt'] : 0; //优惠劵抵扣金额
            $goodsSendout   = !empty($_REQUEST['goodsSendout']) ? $_REQUEST['goodsSendout'] : 0; //运费
            $storeId        = !empty($_REQUEST['storeId']) ? $_REQUEST['storeId'] : 0; //店铺id.
            foreach($goodsSendout as $key=>$val){
                $temp=explode('-',$val);
                if($temp[1] == 2){
                    $shippingFee[] = array(
                        "goods_id"=>$temp[0],
                        'number'=>$temp[2]
                    );
                }
            }

            foreach($shippingFee as $key=>$val){
                $goodsId = $storeGoodsMod->getOne(array("cond"=>"`id` = {$val['goods_id']}"));
                $shippingFee[$key]['goods_id'] = $goodsId['goods_id'];
            }

            if(!empty($shippingFee)){
                $shippingPrice= $storeFareRuleMod->getFare($shippingFee,$storeId);
            }else{
                $shippingPrice = 0;
            }
            $shippingPrice = number_format($shippingPrice,2,".","");
            $info['shippingPrice'] = $shippingPrice;
            if(empty($fxCode)){
                $info['fxDiscount'] = '0.00';
                $info['payMoney'] = $totalMoney - $ruiDiscount - $voucherDiscunt + $shippingPrice;
                $info["payMoney"]=number_format($info["payMoney"],2,".","");
                if($info['payMoney']<=0){
                    $info['payMoney']=0;
                }
                $this->setData($info, $status = 1, $message = '');
            }
            //获取分销人员信息
            $fxuserInfo  = $fxuserMod->getOne(array('cond' => "fx_code = '{$fxCode}' AND mark = 1"));
            if( $fxuserInfo['level'] != 3 ){
                $info['fxDiscount'] = 0.00;
                $info['payMoney'] = $totalMoney - $ruiDiscount - $voucherDiscunt + $shippingPrice ;
                $info["payMoney"]=number_format($info["payMoney"],2,".","");
                if($info['payMoney']<=0){
                    $info['payMoney']=0;
                }
                $this->setData($info, $status = 1, $message = '');
            }
            $discount_rate  = $fxuserInfo['discount'];
            $fxDiscount       = ($totalMoney-$ruiDiscount - $voucherDiscunt) * $discount_rate * 0.01;
            $info['fxDiscount']   = $fxDiscount;    //推荐用户优惠折扣
            $info['payMoney']   = $totalMoney  - $fxDiscount - $ruiDiscount - $voucherDiscunt + $shippingPrice ;
            if ($info['payMoney'] <= 0) {
                $info['payMoney']   = 0;
            };
            $info["payMoney"]=number_format($info["payMoney"],2,".","");
            $info["fxDiscount"]=number_format($info["fxDiscount"],2,".","");
            $info['discountRate']  = $discount_rate;
            $info['fxUserId']     = $fxuserInfo['id'];
            //获取分销规则
            $info['ruleId']    = $fxruleMod->getFxRule($fxuserInfo['id']);
            $this->setData($info, $status = 1, $message = '');
        }



        /**
         * 待付款订单详情页面
         * @author gao
         * @date 2019-02-14
         */
        public function  pendingPayment(){
            $orderMod=&m('order');
            $orderGoodsMod=&m('orderGoods');
            $cartMod=&m('cart');
            $storeMod=&m('store');
            $couponMod=&m('coupon');
            $expectTime=$cartMod->expectTime();//模型里面定义的过期时间
            $orderSn=!empty($_REQUEST['orderSn']) ? $_REQUEST['orderSn'] : '201902151421556492'; //订单编号
            $orderData=$orderMod->getOne(array('cond' => "order_sn = '{$orderSn}'")); //订单信息
            $orderGoodsData=$orderGoodsMod->getData(array('cond' => "order_id = '{$orderSn}'")); //订单商品信息
            $referer=!empty($_REQUEST['referer']) ? $_REQUEST['referer'] : ''; //订单编号
            $lang= !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : 0;
            $storeId=$orderData['store_id'];
            $expireTime=$orderData['add_time']+$expectTime-time(); //订单过期时间
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
            //获取配送地址等数据
            $address = $orderMod->getOrderAddress($orderSn, $storeId);
            $orderData['address'] = $address;
            $this->assign('lang',$lang);
            $this->assign('storeId',$storeId);
            $this->assign('expireTime',$expireTime);
            $this->assign('orderData',$orderData);
            $this->assign('orderGoodsData',$orderGoodsData);
            $this->assign('storeImage',$storeImage['logo']);
            $this->assign('referer',$referer);
            $this->display("orderList/pendingPayment.html");
        }

        public function orderDetails()
        {
            $orderSn =!empty($_REQUEST['orderSn']) ? $_REQUEST['orderSn'] : '201901022020271356'; //订单编号
            $lang    = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : 29;
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
            $this->assign('address',$address);
            $this->assign('orderSn',$orderSn);
            $this->assign('fxCode',$fxCode);
            $this->assign('data',$data);
            $this->assign('storeImage',$storeImage['logo']);
            $this->assign('orderGoodsData',$orderGoodsData);
            $this->assign('expireTime',$expireTime);
            $this->assign('orderData',$orderData);
            $this->assign('storeId',$data[0]['store_id']);
            $this->display('orderList/pendingPayment.html');
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
            $referer = !empty($_REQUEST['referer']) ? $_REQUEST['referer'] : '' ;//返回链接地址
            $res=$orderMod->wxCancleOrder($orderSn);
            $info['url']=$referer;
            if($res==0){
                $this->setData($info,0,'订单已失效');
            }else{
                $this->setData($info,1,'订单已取消');
            }
        }



        /**
         * 去付款
         * @author gao
         * @date 2019-02-20
         */
        public function sureOrder()
        {
            $orderMod =& m('order');
            $orderSn = !empty($_REQUEST['orderSn']) ? htmlspecialchars(trim($_REQUEST['orderSn'])) : '';//订单编号
            $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : 0;
            $daifu = !empty($_REQUEST['daifu']) ? $_REQUEST['daifu'] : 0;
            $store_id = !empty($_REQUEST['storeId']) ? intval($_REQUEST['storeId']) : '';
            $order_id = !empty($_REQUEST['orderId']) ? intval($_REQUEST['orderId']) : '';
            $orderInfo = $orderMod->getOne(array('cond' => "`order_sn`='{$orderSn}'", 'fields' => 'order_state'));
            if($orderInfo['order_state'] ==0 ){
                $this->setData('',0,'订单已失效');
            }else{
                if ($daifu == 1) {
                    $info['url'] = "?app=fxPayment&act=index&storeid={$store_id}&orderid={$order_id}&lang={$lang}&daifu=1&orderSn={$orderSn}";
                } else {
                    $info['url']="?app=orderList&act=comfirmOrder&order_id={$orderSn}&lang={$lang}&store_id={$store_id}";
                }
                $this->setData($info,1,'');
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
            $storeGoodsId=!empty($_REQUEST['storeGoodsId']) ? $_REQUEST['storeGoodsId'] : 14499;
            $dataId=!empty($_REQUEST['dataId']) ? $_REQUEST['dataId'] : 0; //配送方式
            $sendout=$storeGoodsMod->getGoodsSendoutArr($storeGoodsId);
            $this->assign('dataId',$dataId);
            $this->assign('sendout',$sendout);
            $html = self::$smarty->fetch("orderList/sendout.html");
            $this->setData($html,1,'');
        }


        //获取店铺距离位置
        function getDistance($addressId,$storeId){
            $userAddresssMod=&m('userAddress');
            $storeMod =&m('store');
            $storeInfo=$storeMod ->getOne(array("cond"=> "`id`= {$storeId}"));
            $storeLongitude = $storeInfo['longitude'];
            $storeLatitude = $storeInfo['latitude'];
            $addressInfo = $userAddresssMod->getOne(array("cond"=>"`id` = {$addressId}"));
            $address = $addressInfo['latlon'];
            $address = explode(',', $address );
            $addressLongitude = $address[1]; //经度
            $addressLatitude = $address[0]; //纬度
            $latlon=$this->coordinate_switchf($addressLatitude,$addressLongitude);
            $lng=$latlon['Longitude'];
            $lat=$latlon['Latitude'];
            $distance = $this->setDistance($storeLongitude, $storeLatitude,$lng, $lat);
            $distance = $distance / 1000;
            if($distance > $storeInfo['distance']){
                return true;
            }else{
                return false;
            }

        }


        //转化距离
        function setDistance($lng1, $lat1, $lng2, $lat2) {
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




    }