<?php
    /**
     * 购物车模块模型
     * @author: wanyan
     * @date: 2017/9/19
     */
    if (!defined('IN_ECM')) { die('Forbidden'); }
    class CartMod extends BaseMod{
        const EXPECT_TIME = 86400;  // 购物车存在时间  默认12h  by xt 2019.02.11


    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct("cart");
    }
    /**
     * 判断购物车商品是否存在   // 当前用户同一店铺规格商品，配送区域不一样。显示两条记录
     * @author: wanyan
     * @date: 2017/9/19
     */
    public function goodExist($store_goods_id,$item_id,$shipping_price,$user_id,$store_id,$goods_price,$fx_code,$shipping_store_id,$deliveryType){
        $query =array(
            'cond' =>"`goods_id` = '{$store_goods_id}' and `store_id` = '{$store_id}' and `spec_key` = '{$item_id}' and `user_id` = '{$user_id}'
             and `shipping_price` ='{$shipping_price}' and `goods_price` = '{$goods_price}' and `fx_code` = '{$fx_code}' and `shipping_store_id` = '{$shipping_store_id}' and `delivery_type` = '{$deliveryType}'",
            'fields' =>'`id`'
        );

        $rs = $this->getOne($query);
        return $rs['id'];
    }
     /**
      * 获取购物车已有商品数量
      * @author: wanyan
      * @date: 2017/9/19
      */
     public function getCartGoodsNum($store_goods_id,$item_id,$shipping_price,$user_id,$store_id,$goods_price,$shipping_store_id,$deliveryType){
         $query =array(
             'cond' =>"`goods_id` = '{$store_goods_id}' and `store_id` = '{$store_id}' and `spec_key` = '{$item_id}' and `user_id` = '{$user_id}' and `shipping_price` ='{$shipping_price}' and `goods_price` = '{$goods_price}' and `shipping_store_id` = '{$shipping_store_id}' and `delivery_type` = '{$deliveryType}' ",
             'fields' =>'`goods_num`,`id`'
         );
         $rs = $this->getOne($query);
         return $rs;
     }
    /**
     * 加入购物车商品
     * @author: wanyan
     * @date: 2017/9/19
     */
    public function addCart($goodInfo,$good_num,$type){
        $orderMod = &m('order');
       if(!$this->goodExist($goodInfo['store_goods_id'],$goodInfo['item_id'],$goodInfo['shipping_price'],$goodInfo['user_id'],$goodInfo['store_id'],$goodInfo['goods_price'],$goodInfo['fx_code'],$goodInfo['shipping_store_id'],$goodInfo['delivery_type'])){
          $goods = $this->getGoodInfo($goodInfo['store_goods_id'],$goodInfo['shipping_store_id']);
          $specName = $this->getSpecName($goodInfo['item_id'],$goodInfo['store_goods_id']);
//          $goodSpecPrice = $this->getSpecPrice($goodInfo['store_goods_id'],$goodInfo['item_id']);
//          $goodPrice = !empty($goodSpecPrice) ? $goodSpecPrice : $goods[0]['shop_price'];
           $insert_data =array(
             'user_id' => $goodInfo['user_id'],
             'store_id' => $goodInfo['store_id'],
             'goods_id' =>$goodInfo['store_goods_id'],
             'goods_sn' =>$goods[0]['goods_sn'],
             'goods_name' =>addslashes($goods[0]['goods_name']),
             'market_price' =>$goods[0]['market_price'],
//             'goods_price' =>$goodInfo['goods_price'],
             'goods_price'=>$orderMod->getGoodsPayPrice($goodInfo['store_id'],$goodInfo['store_goods_id'],$goodInfo['item_id']),
             'goods_num'   =>$good_num,
             'spec_key'    => $goodInfo['item_id'],
             'spec_key_name'  => $specName,
             'prom_id'    => $goodInfo['prom_id'],
             'prom_type'    => 0,
             'sku'          =>$goods[0]['sku'],
             'shipping_price' => $goodInfo['shipping_price'],
             'order_from'     =>$goodInfo['order_from'],
             'fx_code'     =>$goodInfo['fx_code'],
             'delivery_type' => $goodInfo['delivery_type'],
             'shipping_store_id'     =>$goodInfo['shipping_store_id'],
             'add_time'     =>time(),
           );
           $rs = $this->doInsert($insert_data);

       }else{
           $num = $this->getCartGoodsNum($goodInfo['store_goods_id'],$goodInfo['item_id'],$goodInfo['shipping_price'],$goodInfo['user_id'],$goodInfo['store_id'],$goodInfo['goods_price'],$goodInfo['shipping_store_id'],$goodInfo['delivery_type']);
           $edit_data = array(
               'goods_num' =>$num['goods_num'] + $good_num
           );
           if($type==1){
               $edit_data = array(
                   'goods_num' =>$good_num
               );
           }
           $rs = $this->doEdit($num['id'],$edit_data);
           $rs=$num['id'];
       }
       return $rs;
    }

    /*
    * 获取当前商品的规格价格
    * @author: wanyan
    * @date: 2017/9/20
    */
    public function getSpecPrice($store_goods_id,$item_id){
       $sql = "select `price` from ".DB_PREFIX."store_goods_spec_price where `store_goods_id` ='{$store_goods_id}' and `key` = '{$item_id}'";
       $rs = $this->querySql($sql);
       if($rs[0]['price']){
           return $rs[0]['price'];
       }
    }
    /*
     * 获取商品信息
     * @author: wanyan
     * @date: 2017/9/19
     */
    public function  getGoodInfo($good_id,$store_id){
        $sql = "select `goods_sn`,`goods_name`,`market_price`,`shop_price`,`sku` from ".DB_PREFIX."store_goods where `id` = '{$good_id}' and `store_id` = '{$store_id}'";
        $rs = $this->querySql($sql);
        return $rs;
    }


    /*
     * 获取规格名称
     * @author: wanyan
     * @date: 2017/9/19
     */
    public function getSpecName($item_id,$goods_id){
       $spec_name = array();
            $ccc = explode('_', $item_id);
            foreach ($ccc as $value){
                $sql = 'select item_name from bs_goods_spec_item_lang WHERE item_id = '.$value.' AND lang_id = 29';
                $res = $this->querySql($sql);
                $spec_name[] = $res[0]['item_name'];
            }
            if($spec_name) $spec_name = implode(':',$spec_name);
            return $spec_name;
    }

        public function getSpecName1($item_id,$goods_id){
            $sql = "select `key_name`  from ".DB_PREFIX."store_goods_spec_price where `key`='{$item_id}' and `id` = '{$goods_id}'";
            $rs = $this->querySql($sql);
            return $rs[0]['key_name'];
        }

     /*
      * 获取规格名称
      * @author: wanyan
      * @date: 2017/9/19
      */
     public function getGoodById($store_id,$user_id){
          $query=array(
              'cond' =>"`user_id` = '{$user_id}' and `selected` =1 and `store_id` = '{$store_id}'",
              'fields' =>"*"
          );
          $rs = $this->getData($query);
          return $rs;
        }
        /*
        * 获取cart_id 获取用户选择商品信息
        * @author: wanyan
        * @date: 2017/9/19
        */
        public function getGoodByCartId($cart_id){
            $query=array(
                'cond' =>"`id` in  ({$cart_id}) ",
                'fields' =>"*"
            );
            $rs = $this->getData($query);
            return $rs;
        }

        /*
       * 根据store_goods_id 去查原商品不同语言下的商品名称
       * @author: wanyan
       * @date: 2018/1/17
       */
        public function getGoodNameById($store_goods_id,$lang_id){
            $sql = "SELECT gl.goods_name from ".DB_PREFIX."goods AS g  LEFT JOIN ".DB_PREFIX."store_goods as sg ON g.goods_id = sg.goods_id  
              LEFT JOIN ".DB_PREFIX."goods_lang as gl ON g.goods_id= gl.goods_id 
               WHERE gl.lang_id = {$lang_id} AND sg.id = {$store_goods_id}";
            $rs = $this->querySql($sql);
            return $rs[0]['goods_name'];
        }

        /*
    * 获取商品库存
    * @author: gao
    * @date: 2018/9/3
    */
        public function  isInvalid($store_good_id,$item_id){
            $storeGoodsMod = &m('storeGoods');
            $storeGoodItemPriceMod = &m('storeGoodItemPrice');
            $storeGoodsData = $storeGoodsMod->getOne(array('cond'=>"`id` ={$store_good_id}"));
            $storeGoodItemPriceData = $storeGoodItemPriceMod->getOne(array('cond'=>"`store_goods_id` ={$store_good_id} and `key` = '{$item_id}' "));
            if(empty($item_id)){
                return $storeGoodsData['goods_storage'];
            }else{
                return $storeGoodItemPriceData['goods_storage'];
            }
        }

        /*
         * 获取cart_id 获取用户选择商品信息
         * @author: gao
         * @date: 2019/02/12
        */
   /*     public function getCartGoods($cart_id,$lang){
            $storeMod=&m('store');
            $query=array(
                'cond' =>"`id` in  ({$cart_id}) ",
                'fields' =>"*"
            );
            $rs = $this->getData($query);
            foreach($rs as &$v){
                $res[$v['store_id']]['goods'][]=$v;
            }
            foreach($res as $k=>$v){
                $goodsNum=0; //每个店铺的商品数量
                $totalMoney=0;//每个店铺的总金额
                foreach($v['goods'] as $key=>$val){
                    $goodsNum +=$val['goods_num'];
                    $totalMoney += $val['goods_num'] * $val['goods_price'];
                    $res[$k]['goods'][$key]['goods_name']=$this->getGoodNameById($val['goods_id'],$lang);
                    $res[$k]['goods'][$key]['origin_img']=$this->getGoodImg($val['goods_id'], $val['store_id']);
                    if ($v['spec_key']) {
                        $info = explode('_', $v['spec_key']);
                        foreach ($info as $k1 => $v1) {
                            $sql = "select `item_name` from " . DB_PREFIX . "goods_spec_item_lang where `item_id` = '{$v1}' and  `lang_id` = ".$lang;
                            $spec_1 = $this->querySql($sql);
                            $spec[] = $spec_1[0]['item_name'];
                        }
                        $spec_key = implode(':', $spec);
                        $res[$k]['goods'][$key]['spec_key_name'] = $spec_key; //规格名称
                        $spec = array();
                    }
                }
                $res[$k]['goods_num']=$goodsNum;
                $res[$k]['store_name']=$storeMod->getNameById($k,$lang); //店铺名称
                $res[$k]['total_money']=$totalMoney;
            }
            return $res;
        }*/

        /*
         * 获取订单总金额
         * @author: gao
         * @date: 2019/02/13
        */
        public function getTotalMoney($userGoods){
            foreach($userGoods as $k=>$v){
                $totalMoney +=$v['total_money']; //订单总金额
                $totalNum += $v['goods_num']; //总共的商品数量
            }
            return  $totalMoney;
        }


        /*
         * 获取最大积分支付比例金额
         * @author: gao
         * @date: 2019/02/13
        */
     /*   public function getPointMoney($userGoods,$userId){
            //模型
            $pointSiteMod = &m('point');
            $storePointMod = &m('storePoint');
            $storeMod = &m('store');
            $userMod=&m('user');
            $curMod = &m('currency');
            $rechargeAmountMod=&m('rechargeAmount');
            $user_info = $userMod->getOne(array("cond" => "id = " . $userId));
            foreach($userGoods as $k=>$v){ //购物车商品信息
                $point_price_site = $pointSiteMod->getOne(array("cond" => "1=1"));
                $store_point_site = $storePointMod->getOne(array("cond" => "store_id=" . $k));
                $point_price_site['point_price'] = empty($store_point_site) ? 0 : $store_point_site['point_price'];
                $rechargeData=$userMod->getOne(array('cond'=>"`id` = '{$userId}' and mark=1",'fields'=>'recharge_id'));
                $percentData= $rechargeAmountMod->getOne(array('cond'=>"`id` = '{$rechargeData['recharge_id']}'",'fields'=>'percent'));
                $percentData['percent']=empty($percentData['percent']) ? 0: $percentData['percent'];
                $point_price_site['point_price']=$point_price_site['point_price']+$percentData['percent'];
                if ($point_price_site) {
                    $point_price = $point_price_site['point_price'] * $v['total_money'] / 100; //积分兑换最大金额
                    $rmb_point = $point_price_site['point_rate']; //积分和RMB的比例
                } else {
                    $point_price = 0;
                    $rmb_point = 0;
                }
                //获取当前店铺币种以及兑换比例
                $store_info = $storeMod->getOne(array("cond" => "id=" . $k));
                //获取当前币种和RMB的比例
                $rate = $curMod->getCurrencyRate($store_info['currency_id']);
                //积分和RMB的比例
                if ($rate) {
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
                $userGoods[$k]['maxAccount']=$point_price;
                $userGoods[$k]['maxPoint']=$price_rmb_point;
            }
            return $userGoods;
        }*/

        /*
        * 获取订单总睿积分抵扣
        * @author: gao
        * @date: 2019/02/13
        */
        public function getPoint($userGoods,$userId){
            $userGoods= $this->getPointMoney($userGoods,$userId);
            foreach($userGoods as $k=>$v){
                $maxAccount +=$v['maxAccount']; //订单总金额
                $maxPoint += $v['maxPoint']; //总共的商品数量
            }
            $res=array(
                'maxAccount'=>$maxAccount,
                'maxPoint'=>$maxPoint
            );
            return  $res;
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
            $rs = $this->querySql($sql);
            return $rs[0]['original_img'];
        }



        /*
     * 获取cart_id 获取用户选择商品信息
     * @author: gao
     * @date: 2019/02/12
    */
        public function getCartGoods($cart_id,$lang){
            $storeMod=&m('store');
            $roomTypeMod=&m('roomType');
            $storeGoodsMod=&m('storeGoods');
            $query=array(
                'cond' =>"`id` in  ({$cart_id}) ",
                'fields' =>"*"
            );
            $userGoods = $this->getData($query);

            $totalMoney=0;
            $goodsNum=0;
            foreach ($userGoods as $k => $v) {
                $userGoods[$k]['store_name'] = $storeMod->getNameById($v['store_id'], $lang);
                $userGoods[$k]['origin_img'] = $this->getGoodImg($v['goods_id'], $v['store_id']);
                $userGoods[$k]['singleMoney'] = $v['goods_price'] * $v['goods_num'];
                $totalMoney += ($v['goods_price'] * $v['goods_num']);
                $goodsNum += $v['goods_num'];
                if ($v['spec_key']) {
                    $info = explode('_', $v['spec_key']);
                    foreach ($info as $k1 => $v1) {
                        $sql = "select `item_name` from " . DB_PREFIX . "goods_spec_item_lang where `item_id` = '{$v1}' and  `lang_id` = ".$lang;
                        $spec_1 = $this->querySql($sql);
                        $spec[] = $spec_1[0]['item_name'];
                    }
                    $spec_key = implode(':', $spec);
                    $userGoods[$k]['spec_key_name'] = $spec_key;
                    $spec = array();
                }
                $userGoods[$k]['shipping_store_name'] = $storeMod->getNameById($v['shipping_store_id'], $lang);
                $userGoods[$k]['goods_name'] = $this->getGoodNameById($v['goods_id'], $lang);
                $userGoods[$k]['room_type_id']=$roomTypeMod->getRoomTypeId($v['goods_id']);
                $userGoods[$k]['room_parent_id']=$roomTypeMod->getRoomParentId($v['goods_id']);
                //配送方式
                $userGoods[$k]['isFreeShipping']=$storeGoodsMod->isFreeShipping($v['goods_id']);
                $userGoods[$k]['sendout']=$storeGoodsMod->getGoodsSendoutArr($v['goods_id']);
                $userGoods[$k]['sendoutStr']=$storeGoodsMod->getGoodsSendout($v['goods_id']);
                $userGoods[$k]['sendoutIndex']=key($userGoods[$k]['sendout']);
                $userGoods[$k]['sendoutValue']=current($userGoods[$k]['sendout']);
                $userGoods[$k]['auxiliary_type']=$roomTypeMod->getRoomType($v['goods_id']);
                //兑换劵参数
                $voucherParameter[$k]['money']=$v['goods_price'];
                $voucherParameter[$k]['room_type_id']=$roomTypeMod->getRoomType($v['goods_id']);
            }
            foreach($userGoods as $key=>$val) {
                $sendout[] = $val['sendout'];
                $sendoutStr[]=$val['sendoutStr'];
            }
            $uniqueSendoutStr=array_unique($sendoutStr);

            if( count($uniqueSendoutStr)==1 && strlen($uniqueSendoutStr[0]) == 1){
                $sendoutDisplay=1; //只显示最下面的配送方式
            }

            $data=array(
                "totalMoney"=>$totalMoney,
                "goodsNum"=>$goodsNum,
                'userGoods'=>$userGoods,
                'voucherParameter'=>$voucherParameter,
                'sendoutDisplay'=>$sendoutDisplay
            );
            return $data;
        }



        /*
         * 获取最大积分支付比例金额
         * @author: gao
         * @date: 2019/02/13
        */
        public function getPointMoney($storeId,$userId,$totalMoney){
            //模型
            $pointSiteMod = &m('point');
            $storePointMod = &m('storePoint');
            $storeMod = &m('store');
            $userMod=&m('user');
            $curMod = &m('currency');
            $rechargeAmountMod=&m('rechargeAmount');
            $user_info = $userMod->getOne(array("cond" => "id = " . $userId));
            $point_price_site = $pointSiteMod->getOne(array("cond" => "1=1"));
            $store_point_site = $storePointMod->getOne(array("cond" => "store_id=" . $storeId));
            $point_price_site['point_price'] = empty($store_point_site) ? 0 : $store_point_site['point_price'];
            $rechargeData=$userMod->getOne(array('cond'=>"`id` = '{$userId}' and mark=1",'fields'=>'recharge_id'));
            $percentData= $rechargeAmountMod->getOne(array('cond'=>"`id` = '{$rechargeData['recharge_id']}'",'fields'=>'percent'));
            $percentData['percent']=empty($percentData['percent']) ? 0: $percentData['percent'];
            $point_price_site['point_price']=$point_price_site['point_price']+$percentData['percent'];
            if($point_price_site) {
                $point_price = $point_price_site['point_price'] * $totalMoney / 100; //积分兑换最大金额
                $rmb_point = $point_price_site['point_rate']; //积分和RMB的比例
            }else{
                $point_price = 0;
                $rmb_point = 0;
            }
            //获取当前店铺币种以及兑换比例
            $store_info = $storeMod->getOne(array("cond" => "id=" . $storeId));
            //获取当前币种和RMB的比例
            $rate = $curMod->getCurrencyRate($store_info['currency_id']);
            //积分和RMB的比例
            if ($rate) {
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
            $data=array(
                'maxPoint'=>$price_rmb_point,
                'maxAccount'=>number_format($point_price,2,".","")
            );
            return $data;
        }

        /*
         * 会员默认分销码
         * @author: gao
         * @date: 2019/02/13
        */
        public function getFxCode($totalMoney,$userId,$pointMoney){
            $fxruleMod      = &m('fxrule');
            $fxCodeSql="SELECT fx_code,fu.id,fu.rule_id,fu.discount FROM  ".DB_PREFIX."fx_user_account as fa LEFT JOIN " .DB_PREFIX."fx_user as fu ON fa.fx_user_id = fu.id WHERE fa.user_id =".$userId;
            $fxCodeData=$this->querySql($fxCodeSql);
            $discount=0;
            if(!empty($fxCodeData)){
                $fxuserMod      = &m('fxuser');
                $fxruleMod      = &m('fxrule');
                $fxuserInfo     = $fxuserMod->getOne(array('cond' => "fx_code = '{$fxCodeData[0]['fx_code']}' AND mark = 1"));
                if( $fxuserInfo['level'] != 3 ){
                    $this->setData('', $status = 1, $message = '');
                }
                $discount_rate  = $fxuserInfo['discount'];
                $discount       = (($totalMoney-$pointMoney) * $discount_rate * 0.01);
                if($discount<0.01){
                    $discount=0;
                }
                $data=array(
                    'fxDiscount'=>number_format($discount,2,".",""),
                    'fxCode'=>$fxCodeData[0]['fx_code'],
                    'fxUserId'=>$fxCodeData[0]['id'],
                    'ruleId'=>$fxruleMod->getFxRule($fxuserInfo['id']),
                    'discountRate'=>$fxCodeData[0]['discount']
                );
                return $data;
            }
        }


        /*
        * 获取过时时间
        * @author: gao
        * @date: 2019/02/13
        */
        public function expectTime(){
            return self::EXPECT_TIME;
        }




}