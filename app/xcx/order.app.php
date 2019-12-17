<?php
/**
 * 文章控制器
 * @author zhangkx
 * @date: 2019/3/21
 */
class OrderApp extends BasePhApp
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
    }


    //确认订单页面
    public function  comfirmOrder(){
        //模型
        $userAddresssMod=&m('userAddress');
        $orderMod = &m('order');
        $orderGoodsMod = &m('orderGoods');
        $couponMod = &m('coupon');
        $storeGoodsMod =&m('storeGoods');
        //接收参数
        $orderSn = !empty($_REQUEST['order_id']) ?  $_REQUEST['order_id'] : '' ; //订单编号
        $addr_id = !empty($_REQUEST['addr_id']) ? $_REQUEST['addr_id'] : 0; //地址id
        $orderData = $orderMod->getOne(array("cond" => "`order_sn` ='{$orderSn}'"));
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
                    $voucherStr = "满{$voucherData['money']}元抵扣{$voucherData['discount']}元";
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
                        $goodsInfo['sendout'.$sendoutTemp[1]][] = $val;
                    }
                }
            }
        }else{ //不拆分单
            $goodsInfo['sendout'.$orderData['sendout']] = $orderGoodsData;
        }
        if ($addr_id) {
            $where = ' and id=' . $addr_id;
        } else {
            $where = ' and default_addr =1';
            //获取收货地址
            $addrSql = "select * from " . DB_PREFIX . 'user_address where user_id=' . $this->userId . $where;
            $userAddress = $userAddresssMod->querySql($addrSql); // 获取用户的地址
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
        $data=array(
            'goodsInfo'=>$goodsInfo,
            'userAddress'=>$userAddress,
            'orderData'=>$orderData
        );
        $this->setData($data,1,'');
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
        }else{
            $sendout = explode(',', $orderData['sendout']);
            foreach ($sendout as $k => $v) {
                $sendoutTemp = explode('-', $v);
                $sort[] = $sendoutTemp[1];
            }
            if(in_array(2,$sort)){
                if(empty($addr_id)){
                    $this->setData('',0,'请填写收货地址');
                }else{
                    $addressData = $userAddresssMod->getOne(array("cond"=>"`id` = {$addr_id}"));
                    if(empty($addressData)){
                        $this->setData('',0,'请填写收货地址');
                    }
                    $distance = $this->getDistance($addr_id,$orderData['store_id']);
                    if($distance){
                        $this->setData('',0,'不在配送范围内');
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
                            buyer_name='{$userInfo['name']}',buyer_address='自提',buyer_phone={$userInfo['phone']},pei_time={$peiTime} 
                            WHERE order_sn = '{$val['order_sn']}' ";
                $orderMod->doEditSql($sql);
            }
        }
        $info['url'] = "?app=rechargeAmount&act=payment&order_id={$orderSn}&store_id={$storeId}&storeid={$storeId}&lang={$this->langid}";
        $this->setData($info, $status = 1, '确认订单成功,前往支付');
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

    /**
     * 判断待付款订单是否失效
     * @author tangp
     * @date 2019-07-04
     */
    public function getPayOrderStatus()
    {
       $order_sn = $_REQUEST['order_sn'];
       if (empty($order_sn)){
           $this->setData(array(),0,'请传递订单号！');
       }
       $sql = "SELECT * FROM bs_order WHERE order_sn = {$order_sn}";
       $res = &m('order')->querySql($sql);
       if ($res[0]['order_state'] == 0){
           $this->setData(array(),0,'订单已失效！');
       }else{
           $this->setData(array(),1,'');
       }
    }





}