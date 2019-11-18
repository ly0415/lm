<?php

/**
 * 订单确认模块
 * @author wanyan
 * @date 2017-10-19
 */
class ComfirmOrderApp extends BaseWxApp {

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
    private $rechargeAmountMod;
    private $userMod;

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
        $this->rechargeAmountMod = &m('rechargeAmount');
        $this->userMod=&m('user');
    }

    /**
     * 订单确认页面
     * @author wanyan
     * @date 2017-10-19
     */
    public function index() {
        //语言包
        $this->load($this->shorthand, 'WeChat/goods');
        $a = $this->langData;
       /* $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->mrstoreid;  //所选的站点id*/
        //睿积分兑换
        $this->assign('langdata', $this->langData);
        $referer = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"]);
        $cart_ids = !empty($_REQUEST['item_id']) ? $_REQUEST['item_id'] : '';
        $userGoods = $this->cartMod->getGoodByCartId($cart_ids);
        $storeid=$userGoods[0]['store_id'];
        $total = 0;
        $goods_num = 0;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : $this->latlon;
        $address = !empty($_REQUEST['address']) ? htmlspecialchars(trim($_REQUEST['address'])) : '';
        //睿积分兑换比例
        $pointSiteMod = &m('point');
        $point_price_site = $pointSiteMod->getOne(array("cond" => "1=1"));
        $rate = $point_price_site['point_rate'];
        $this->assign('rate', $rate);
        $this->assign('address', $address);
        $this->assign('latlon', $latlon);
        $this->assign('auxiliary', $auxiliary);
        $roomTypeMod=&m('roomType');
        foreach ($userGoods as $k => $v) {
            $userGoods[$k]['store_name'] = $this->storeMod->getNameById($v['store_id'], $this->langid);
            $userGoods[$k]['origin_img'] = $this->getGoodImg($v['goods_id'], $v['store_id']);
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
            $goods_num += $v['goods_num'];
            $userGoods[$k]['shipping_store_name'] = $this->storeMod->getNameById($v['shipping_store_id'], $this->langid);
            $userGoods[$k]['goods_name'] = $this->cartMod->getGoodNameById($v['goods_id'], $this->langid);
            $userGoods[$k]['room_type_id']=$roomTypeMod->getRoomTypeId($v['goods_id']);
            $userGoods[$k]['room_parent_id']=$roomTypeMod->getRoomParentId($v['goods_id']);
            //兑换劵参数
            $voucherParameter[$k]['money']=$v['goods_price'];
            $voucherParameter[$k]['room_type_id']=$roomTypeMod->getRoomTypeId($v['goods_id']);
        }
        $addr_id = !empty($_REQUEST['addr_id']) ? htmlspecialchars(trim($_REQUEST['addr_id'])) : '';
        if ($addr_id=='') {
            $where = ' and default_addr =1';

        } else {
            $where = ' and id=' . $addr_id;
        }
        //获取收货地址
        $addrSql = "select * from " . DB_PREFIX . 'user_address where distinguish=1 and user_id=' . $this->userId . $where;
        $userAddress = $this->userAddressMod->querySql($addrSql); // 获取用户的地址
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
            /* $this->assign('detailAddress', $this->getAddress($userAddress['store_address'])); */
        }
        //抵扣劵
        $userCouponMod=&m('userCoupon');
        $couponData=$userCouponMod->getValidCoupons($this->userId,$this->langid,1,$storeid,$total);
            $voucherData = $userCouponMod->getValidCoupons($this->userId, $this->langid, 2, 0, 0,$voucherParameter);


     /*   foreach($arr as $k=>$v){
           foreach($v as $k1=>$v1){
               $voucherData[]=$v1;
           }
        }
       $voucherData =$this->assoc_unique($voucherData, 'user_coupon_id');*/
        //用户信息
        $userMod = &m('user');
        $user_info = $userMod->getOne(array("cond" => "id = " . $this->userId));
        $this->assign('user_info', $user_info);
        $goodNum = count(explode(',', $cart_ids));
        //睿积分抵扣
        $pointSiteMod = &m('point');
        $storePointMod = &m('storePoint');
        $storeMod = &m('store');
        $curMod = &m('currency');
        //获取订单总金额
        //获取最大积分支付比例
        $point_price_site = $pointSiteMod->getOne(array("cond" => "1=1"));
        $store_point_site = $storePointMod->getOne(array("cond" => "store_id=" . $storeid));
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
        $store_info = $storeMod->getOne(array("cond" => "id=" . $storeid));
        //获取当前币种和RMB的比例
        $rate = $curMod->getCurrencyRate($store_info['currency_id']);
        //积分和RMB的比例
        if ($rate) {
            // $price_rmb = ceil(($point_price*$rate)/$rmb_point);
            //最大比例使用积分

            $price_rmb_point = $point_price * $rate * $rmb_point;
            if($price_rmb_point<1){
                $price_rmb_point=0;
                $point_price=0;
            }else{
                $price_rmb_point=ceil($price_rmb_point);
            }
            if (ceil($point_price * $rate * $rmb_point) > $user_info['point']) {
                $point_price = $user_info['point'] * $rmb_point / 100;
                $price_rmb_point = $point_price * $rate * $rmb_point;
                if($price_rmb_point<1){
                    $price_rmb_point=0;
                    $point_price=0;
                }else{
                    $price_rmb_point=ceil($price_rmb_point);
                }
            }


        }

        //会员默认分销码
        $fxCodeSql="SELECT fx_code FROM  ".DB_PREFIX."fx_user_account as fa LEFT JOIN " .DB_PREFIX."fx_user as fu ON fa.fx_user_id = fu.id WHERE fa.user_id =".$this->userId;
        $fxCodeData=$this->cartMod->querySql($fxCodeSql);
         $discount=0;
        if(!empty($fxCodeData)){
            $fxuserMod      = &m('fxuser');
            $fxruleMod      = &m('fxrule');
            $fxuserInfo     = $fxuserMod->getOne(array('cond' => "fx_code = '{$fxCodeData[0]['fx_code']}' AND mark = 1"));
            if( $fxuserInfo['level'] != 3 ){
                $this->setData('', $status = 1, $message = '');
            }
            $discount_rate  = $fxuserInfo['discount'];
            $discount       = ($total * $discount_rate * 0.01);
            $this->assign('discount',number_format($discount,2));
            $this->assign('fxCode',$fxCodeData[0]['fx_code']);
        }
        $this->assign('voucherData',$voucherData);
        $this->assign('res',$couponData);
        $this->assign('money', number_format($total - $point_price, 2));
        $this->assign('discountMoney',number_format($total - $point_price-$discount, 2));
        $this->assign('maxAccount', number_format($point_price, 2));
        $this->assign('maxPoint', $price_rmb_point);
        $this->assign('total', number_format($total, 2));
        $this->assign('store_id', $storeid);
        $this->assign('lang', $this->langid);
        $this->assign('storeName', $this->storeName($storeid, $auxiliary, $this->langid));
        $this->assign('userGoods', $userGoods);
        $this->assign('shipping_price', $this->getShippingPrice($cart_ids));
        $totalMoney = $total;
        $this->assign('totalMoney', number_format($totalMoney, 2));
        $this->assign('total_num', $goods_num);
        $rs = $this->getGift($storeid, $goodNum, $totalMoney);
        $this->assign('gift', $rs[0]);
        $curinfo = $this->getCurCountry($storeid);
        $this->assign('storeCate_1', $curinfo['cid']);
        $this->assign('referer', $referer);
        $this->assign('zonge', $totalMoney);
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang_id);
        $this->assign('cart_ids', $cart_ids);
        $this->assign('addr_id', $addr_id);
        $this->display('cart/sureorder.html');
    }

//二维数组去重
    function assoc_unique($arr, $key) {
        $tmp_arr = array();
        foreach ($arr as $k => $v) {
            if (in_array($v[$key], $tmp_arr)) {//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
                unset($arr[$k]);
            } else {
                $tmp_arr[] = $v[$key];
            }
        }
        sort($arr); //sort函数对数组进行排序
        return $arr;
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
     * 获取赠品信息
     * @author wanyan
     * @date 2017-11-8
     * @param  $goodNum 商品总数, $totalMoney 商品总价
     */
    public function getGift($store_id, $goodNum, $totalMoney) {
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
                    $goodInfo = $this->getGoods($info);
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
            $res[$v['goods_id']] = $v['amount'] . '_' . $v['gift_id'];
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
    public function getGoodImg($goods_id, $store_id) {
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

    //获取运费和配送费
    public function getMoney() {
        $type = !empty($_REQUEST['type']) ? htmlspecialchars(trim($_REQUEST['type'])) : 1;
        $total = !empty($_REQUEST['total']) ? $_REQUEST['total'] : 0;
        $discount = !empty($_REQUEST['discount']) ? $_REQUEST['discount'] : 0;
        $point = !empty($_REQUEST['price']) ? $_REQUEST['price'] : 0;
        $youhui = !empty($_REQUEST['youhui']) ? $_REQUEST['youhui'] : 0;
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

        $totalMoney = $total + $shipping_price - $discount - $point - $youhui;

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
        $point = !empty($_REQUEST['price']) ? $_REQUEST['price'] : 0;
        $youhui = !empty($_REQUEST['youhui']) ? $_REQUEST['youhui'] : 0;

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
        $totalMoney = $total + $shipping_price - $discount - $point - $youhui;
        if ($totalMoney < 0) {
            $totalMoney = 0.01;
        }
        $totalMoney = number_format($totalMoney, 2);
        $info = array('price' => $price, 'totalMoney' => $totalMoney);
        $this->setData($info, '1', '');
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
        $cartMod=&m('cart');
        $storeMod=&m('store');
        $userAddressMod=&m('userAddress');
        $couponMod=&m('coupon');
        $userCouponMod=&m('userCoupon');
        $userMod = &m('user');
        $orderMod=&m('order');
        $orderDetailMod=&m('orderDetail');
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
        $rule_id = !empty($_REQUEST['rule_id']) ? intval($_REQUEST['rule_id']) : ''; //粉线规则id
        $daifu=!empty($_REQUEST['daifu']) ?$_REQUEST['daifu']:''; //是否代付
        $couponId = !empty($_REQUEST['couponId']) ? $_REQUEST['couponId'] : 0;//优惠劵Id
        $userCouponId=!empty($_REQUEST['userCouponId']) ? $_REQUEST['userCouponId']:0;//用户优惠劵Id
        $discount_price=!empty($_REQUEST['discount_price']) ? $_REQUEST['discount_price'] : 0;//优惠劵优惠金额
        //订单号生成
        $rand = $this->buildNo(1);
        $orderNo = date('YmdHis') . $rand[0];
        //生成小票编号
        $number_order = $this->createNumberOrder($storeid);
        //订单信息
        $sql = "select c.user_id,c.store_id,c.shipping_store_id,SUM(c.goods_price*goods_num) as goods_amount,u.email,u.username from " .
                DB_PREFIX . "cart as c  LEFT JOIN " . DB_PREFIX . "user as u ON c.user_id = u.id  where c.`id` in  ({$cart_ids}) ";
        $orderInfo = $cartMod->querySql($sql);
        //店铺名称
        $orderInfo[0]['store_name'] = $storeMod->getNameById($orderInfo[0]['shipping_store_id'],$lang);
        //获取用户地址信息
        $user_address = $userAddressMod->getAddress($addressId);
        //获取购物车信息
        $goodsInfo = $cartMod->getGoodByCartId($cart_ids);
        //分销优惠金额计算
        if (!empty($fxPhone)) {
            $fxuserMod      = &m('fxuser');
            $fxuserInfo     = $fxuserMod->getOne(array('cond' => "fx_code = '{$fxPhone}' AND mark = 1"));
            $discount = (($orderInfo[0]['goods_amount'] - $price-$discount_price) * $fxuserInfo['discount'] * 0.01);
        } else {
            $discount = 0;
        }
        //是否使用了优惠劵
        if(!empty($couponId)){
            $couponData=$couponMod->getOne(array('cond'=>"`id` = '{$couponId}'",'fields'=>'money,discount'));//优惠劵信息
            $userCouponData=$userCouponMod->getOne(array('cond'=>"`c_id` = '{$couponId}' and user_id = '{$this->userId}' ",'fields'=>'id'));//用户优惠劵信息
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
        $userData=$userMod->getOne(array('cond'=>"`id` = '{$this->userId}' and mark=1",'fields'=>'phone'));
        if (empty($user_address)) {
            $user_address['phone'] = $userData['phone'];
            $user_address['name'] = $userData['phone'];
        }
        //配送方式数组处理
        $sendoutStr=implode(',',$sendout);
        // 主订单数据
        $insert_main_data = array(
            'order_sn' => $orderNo,
            'store_id' => $orderInfo[0]['shipping_store_id'],
            'sendout' => $sendoutStr, // 1自提2派送3邮寄托运
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
            'discount' => $discount,
            'fx_discount_rate' => $discount_rate,
            'fx_phone' => $fxPhone,
            'add_time' => time(),
            'number_order' => $number_order, //生成小票编号
            'seller_msg' => $seller_msg, //订单的留言
            'sub_user' => 2,
            'is_source'=>1,
            'fx_user_id'=>$fx_user_id,
        );
        //优惠劵
        if(!empty($couponId)){
            $insert_main_data['cid']=$couponId;
            $insert_main_data['cp_amount']=$discount_price;
        }
        try {
            //事务开始
            $orderMod->begin();
            //原来生成订单数据
            $main_rs = $orderMod->doInsert($insert_main_data);
            //生成新的订单表数据
            $insert_main_data['cp_amount']=$discount_price;
            $insert_main_data['pd_amount']=$price;
            $insert_main_data['fx_money']=$discount;
            $createOrderRes = $orderMod->createOrder($insert_main_data,1);
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
        if(!empty($couponId)){
            //用户使用优惠劵记录
            $couponLogMod=&m('couponLog');
            $couponLogData=array(
                'user_coupon_id'=>$userCouponId,
                'coupon_id'=>$couponId,
                'user_id'=>$this->userId,
                'order_id'=>$main_rs,
                'order_sn'=>$orderNo,  // by xt 2019.03.21
                'add_time'=>time()
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
                $insert_sub_data = array(
                    'order_id' => $orderNo,
                    'goods_id' => $v['goods_id'],
                    'goods_name' => addslashes(stripslashes($v['goods_name'])),
                    'goods_price' => $orderMod->getPrice($v['store_id'],$v['goods_id'],$v['spec_key']),
                    'goods_num' => $v['goods_num'],
                    'goods_image' => $this->getGoodImg($v['goods_id'], $v['store_id']),
                    'goods_pay_price'=>$orderMod->getGoodsPayPrice($v['store_id'],$v['goods_id'],$v['spec_key']),
                    'spec_key_name' => $v['spec_key_name'],
                    'spec_key' => $v['spec_key'],
                    'store_id' => $v['store_id'],
                    'buyer_id' => $v['user_id'],
                    'goods_type' => 0,
                    'order_state' => 10,
                    'fx_code' => $v['fx_code'],
                    'discount' => ($v['goods_price'] + $shippingfee) * ($fxuserInfo['discount']) * 0.01,
                    'discount_rate' => $fxuserInfo['discount'],
                    'shipping_price' => $v['shipping_price'],
                    'shipping_store_id' => $v['shipping_store_id'],
                    'add_time' => time(),
                    'good_id'=>$this->getGoodId($v['goods_id']),
                    'deduction'=>$this->getDeduction($v['goods_id'])
                );
                $rs[] = $orderDetailMod->doInsert($insert_sub_data);
            }
            $rs = array_filter($rs);
            $store_cate=$this->getStoreCate($goodsInfo[0]['store_id']);//站点国家
            $store_id = $goodsInfo[0]['store_id'];//选取的购物车商品的区域商品id
            if (count($rs)) {
                if ($this->delCart($cart_ids)) {
                    //添加积分优惠
                    if ($price && $price!='0.00') {
                        $this->getPointPrice($orderNo, $price, $point);
                    }
                    //代付
                    if($daifu ==1){
                        $info['url'] = "?app=fxPayment&act=index&storeid={$storeid}&store_cate={$store_cate}&store_id={$store_id}&fx_user_id={$fx_user_id}&rule_id={$rule_id}&orderNo={$orderNo}&orderid={$main_rs}&lang={$lang}&daifu=1";
                    }else{
                        $info['url'] = "?app=rechargeAmount&act=payment&order_id={$orderNo}&store_cate={$store_cate}&store_id={$store_id}&storeid={$storeid}&fx_user_id={$fx_user_id}&rule_id={$rule_id}&orderId={$main_rs}&lang={$lang}&auxiliary={$auxiliary}&latlon={$latlon}";
                    }
                    $this->setData($info, $status = 1, '提交订单成功,前往支付');
                } else {
                    $this->setData($info = array(), $status = 0, '提交订单失败');
                }
            }
        }
    }
    //获取站点国家
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
            return $goodInfo[0]['goods_id'];

    }

    //获取商品扣除方式
    function getDeduction($id){
        $sql="select deduction from ".DB_PREFIX.'store_goods where id='.$id;
        $goodInfo=$this->orderDetailMod->querySql($sql);
        return $goodInfo[0]['deduction'];
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
     * 生成小票编号
     * @author: luffy
     * @date: 2018-08-09
     */
    public function createNumberOrder($storeid) {
        //获取当天开始结束时间
        $startDay = strtotime(date('Y-m-d'));
        $endDay = strtotime(date('Y-m-d 23:59:59'));
        $sql = 'select order_sn,number_order from  '
                . DB_PREFIX . 'order where add_time BETWEEN ' . $startDay . ' AND ' . $endDay
                . ' AND mark = 1 and store_id = ' . $storeid . ' order by add_time DESC limit 1';
        $res = $this->orderMod->querySql($sql);
        //不管订单存在与否直接加
        $number_order = (int) $res[0]['number_order'] + 1;
        $number_order = str_pad($number_order, 4, 0, STR_PAD_LEFT);
        return $number_order;
    }

    //
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
     * @author luffy
     * @date 2018-10-15
     */
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
            $info['payMoney'] = $totalMoney + $shippingfee- $point - $youhui;
            if($info['payMoney']<=0){
                $info['payMoney']=0;
            }
            $this->setData($info, $status = 1, $message = '');
        }

        //获取分销人员信息
        $fxuserInfo     = $fxuserMod->getOne(array('cond' => "fx_code = '{$fxCode}' AND mark = 1"));
        if ($fxuserInfo['user_id'] == $this->userId){
            $info['discount'] = 0.00;
            $info['payMoney'] = $totalMoney + $shippingfee- $point - $youhui;
            if($info['payMoney']<=0){
                $info['payMoney']=0;
            }
            $this->setData($info,$status=1,$message='');
        }
        if( $fxuserInfo['level'] != 3 ){
            $info['discount'] = 0.00;
            $info['payMoney'] = $totalMoney + $shippingfee- $point - $youhui;
            if($info['payMoney']<=0){
                $info['payMoney']=0;
            }
            $this->setData($info, $status = 1, $message = '');
        }

        $discount_rate  = $fxuserInfo['discount'];
        $discount       = ($totalMoney * $discount_rate * 0.01);

        $info['discount']   = $discount;    //推荐用户优惠折扣
        $info['payMoney']   = $totalMoney + $shippingfee - $discount - $point - $youhui;
        if ($info['payMoney'] <= 0) {
            $info['payMoney']   = 0;
        };
        $info['discount_rate']  = $discount_rate;
        $info['fx_user_id']     = $fxuserInfo['id'];
        //获取分销规则
        $info['rule_id']    = $fxruleMod->getFxRule($fxuserInfo['id']);
        $this->setData($info, $status = 1, $message = '');
    }

    /**
     * 获取当前优惠订单总金额
     * @author wanyan
     * @date 2018-01-11
     */
    public function getOrderMoney($cart_ids) {
        $sql = "SELECT SUM(goods_price * goods_num) as orderAmount from " . DB_PREFIX . "cart where `id` IN ({$cart_ids})";
        $info = $this->cartMod->querySql($sql);
        return $info[0]['orderAmount'];
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
        $cart_id = !empty($_REQUEST['cart_ids']) ? htmlspecialchars($_REQUEST['cart_ids']) : '';
        $sql = "select `id` from " . DB_PREFIX . "cart where `id` = '{$cart_id}'";
        $info = $this->cartMod->querySql($sql);
        if ($info[0]['id']) {
            $this->setData($info = array(), $status = 1, $message = '');
        } else {
            $this->setData($info = array(), $status = 0, $a['order_Havebeen_submitted']);
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
            'order_sn' => "{$order_id}"
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
        //获取最大积分支付比例
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

}

?>
