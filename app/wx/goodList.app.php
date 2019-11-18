<?php

class goodListApp extends BaseWxApp {

    public function __construct() {
        parent::__construct();
    }

    //2级业务
    public function index() {
        $this->load($this->shorthand, 'WeChat/goods');
        $this->assign('langdata', $this->langData);
        $storeGoodsMod = &m('areaGood');
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;
        $this->assign('storeid',$storeid);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;
        $this->assign('lang',$lang);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? $_REQUEST['auxiliary'] : 0;
        $this->assign('auxiliary',$auxiliary);
        $rtid = !empty($_REQUEST['rtid']) ? $_REQUEST['rtid'] : 0;
        $latlon = !empty($_REQUEST['latlon']) ? $_REQUEST['latlon'] : 0;
        $this->assign('latlon',$latlon);
        $order=!empty($_REQUEST['order']) ? $_REQUEST['order'] : 0;
        $this->assign('order',$order);
        $tp = !empty($_REQUEST['tp']) ? $_REQUEST['tp'] : 0;  //区别授权的
        if( $tp ){
            //判断是否登录
            header('Location: wx.php?app=user&act=loginOuth&back_app=' . APP . '&back_act=' . ACT . '&storeid=' . $_REQUEST['store_id'] . '&lang=' . $_REQUEST['lang_id']. '&latlon=' . $_REQUEST['latlon']. '&auxiliary=' . $_REQUEST['auxiliary']. '&source=' . $_REQUEST['source']. '&rtid=' . $_REQUEST['rtid']. '&goods_tp=1');
            exit;
        }
        $store_sql = 'select store_discount,background_img,logo from  ' . DB_PREFIX . 'store where id=' . $this->storeid;
        $store_arr = $storeGoodsMod->querySql($store_sql);
        if($rtid==0){
            $buss_sql='select buss_id from  ' . DB_PREFIX . 'store_business where store_id=' . $this->storeid;
            $buss_arr = $storeGoodsMod->querySql($buss_sql);
            $rtid=$buss_arr[0]['buss_id'];
        }

        //去除没有商品的业务分类---临时解决方案
        $roomTypeMod = &m('roomType');
        $roomtypearr = $roomTypeMod->getBusinessType($this->langid, $storeid, $rtid, 1);

        foreach($roomtypearr as $kk=>$vv){
            $roomId[]=$vv['id'];
        }
        $roomIds=implode(',',$roomId);
        $hotGoods=$this->getHotGoods($roomIds,$storeid);
        $this->assign('hotGoods',$hotGoods);

        $goodsCommentMod = &m('goodsComment');
        //热销商品和优惠商品名称
        $sql = 'select * from bs_config  where  inc_type = "shop_info" ';
        $data = $goodsCommentMod->querySql($sql);
        $res = array();
        foreach ($data as $key => $val) {
            $res[$val['name']] = $data[$key];
        }
        $storeMod = &m('store');
        $storeName=$storeMod->getNameById($storeid,$lang);
        $sqlNotice='select store_notice from bs_store where id ='.$storeid;
        $Noticedata = $goodsCommentMod->querySql($sqlNotice);
        $this->assign('Noticedata',$Noticedata[0]);
        //获取秒杀商品
        $spikeActivityMod = &m('spikeActivity');
        $spikeActiviesGoodsMod =& m('spikeActiviesGoods');
        $time        = time();
        //一周每天时间段以及对应秒杀商品
        $time_arr  = $goods_arr = array();
        $init_time = strtotime(date('Y-m-d', $time));
        for($i=1;$i<8;$i ++){
            $miao       = 24*60*60;                 //1天跨度
            $start_time = $init_time + ($i-1)*$miao;
            $end_time   = $init_time + ($i*$miao-1);
            $time_arr[$i][] = $start_time;          //开始时间
            $time_arr[$i][] = $end_time;            //结束时间
            //获取当天商品
            $day = date('Y-m-d', $start_time);
            if($day == date('Y-m-d', $time)){
                $day = '今日';
            } elseif($day == date('Y-m-d', ($time + $miao))){
                $day = '明日';
            }
            $skill_goods = $spikeActivityMod->getData(array(
                'fields'    => '*',
                'cond'      => ' mark = 1 and store_id = '.$storeid.' AND (start_time BETWEEN '. $start_time .' AND '.$end_time .' OR end_time BETWEEN '. $start_time .' AND '.$end_time .' OR ( start_time < '. $start_time .' AND  end_time > '.$end_time .'))',
                'order_by'  => 'end_time desc'
            ));
            foreach($skill_goods as $k => $v){
                $skill_goods[$k]['format_start_time']   =   date('Y-m-d H:i', $v['start_time']);
                $skill_goods[$k]['format_end_time']     =   date('Y-m-d H:i', $v['end_time']);
                $skill_goods[$k]['source']=1;
                $skill_goods[$k]['goods']               =  $spikeActiviesGoodsMod->getSpikeGoods($v['id'],$skill_goods[$k]);;
            }
            $skill_goods && $goods_arr[$day] = $skill_goods;
        }
        //获取当天商品
        foreach($goods_arr as $k=>$v){
            foreach($v as $k1=>$v1){
                if($k == '今日'){
                    if($v1['end_time']<time()){
                        $goods_arr[$k][$k1]['status']='活动结束';//已经结束
                    }
                    if($v1['start_time']<=time()&&$v1['end_time']>=time() ){
                        $goods_arr[$k][$k1]['status']='马上抢购';//开始
                    }
                    if($v1['start_time']>time()){
                        $goods_arr[$k][$k1]['status']='即将开始';//未开始
                    }
                } else {
                    $goods_arr[$k][$k1]['status']='活动预告';
                }
            }
        }
        $keys = array_keys($goods_arr);

        foreach($goods_arr as $k=>$v){
            if(empty($v)){
                $goods_arr[$k]['display']=0;
            }else{
                foreach($v as $k1=>$v1){
                    if(empty($v1)){
                        $goods_arr[$k][$k1]['display']=0;
                    }else{
                        $goods_arr[$k][$k1]['display']=1;
                    }
                }
            }


        }


        $promotionSaleMod = &m('promotionSale');
        $promotionGoodsMod = &m('promotionGoods');
        $times        = time();
        //一周每天时间段以及对应促销商品
        $times_arr  = $promotionGoods_arr = array();
        $init_time = strtotime(date('Y-m-d', $times));
        for($i=1;$i<8;$i++){
            $miao       = 24*60*60;                 //1天跨度
            $start_time = $init_time + ($i-1)*$miao;
            $end_time   = $init_time + ($i*$miao-1);
            $times_arr[$i][] = $start_time;          //开始时间
            $times_arr[$i][] = $end_time;            //结束时间
            //获取当天商品
            $day = date('Y-m-d', $start_time);
            if($day == date('Y-m-d', $times)){
                $day = '今日';
            } elseif($day == date('Y-m-d', ($times + $miao))){
                $day = '明日';
            }
            $promotion_goods = $promotionSaleMod->getData(array(
                'fields'    => '*',
                'cond'      => ' mark = 1 and store_id = '.$storeid.' AND (start_time BETWEEN '. $start_time .' AND '.$end_time .' OR end_time BETWEEN '. $start_time .' AND '.$end_time .' OR ( start_time < '. $start_time .' AND  end_time > '.$end_time .'))',
                'order_by'  => 'end_time desc'
            ));
            foreach($promotion_goods as $k => $v){
                $promotion_goods[$k]['format_start_time']   =   date('Y-m-d H:i', $v['start_time']);
                $promotion_goods[$k]['format_end_time']     =   date('Y-m-d H:i', $v['end_time']);
                $promotion_goods[$k]['source']=1;
                $promotion_goods[$k]['goods']               =  $promotionGoodsMod->gePromotionGoods($v['id'],$promotion_goods[$k]);;
            }
            $promotion_goods && $promotionGoods_arr[$day] = $promotion_goods;
        }
        //获取当天商品
        foreach($promotionGoods_arr as $k=>$v){
            foreach($v as $k1=>$v1){
                if($k == '今日'){
                    if($v1['end_time']<time()){
                        $promotionGoods_arr[$k][$k1]['status']='活动结束';//已经结束
                    }
                    if($v1['start_time']<=time()&&$v1['end_time']>=time() ){
                        $promotionGoods_arr[$k][$k1]['status']='马上抢购';//开始
                    }
                    if($v1['start_time']>time()){
                        $promotionGoods_arr[$k][$k1]['status']='即将开始';//未开始
                    }
                } else {
                    $promotionGoods_arr[$k][$k1]['status']='活动预告';
                }
            }
        }
        $prokeys = array_keys($promotionGoods_arr);

        foreach($promotionGoods_arr as $k=>$v){
            if(empty($v)){
                $promotionGoods_arr[$k]['display']=0;
            }else{
                foreach($v as $k1=>$v1){
                    if(empty($v1)){
                        $promotionGoods_arr[$k][$k1]['display']=0;
                    }else{
                        $promotionGoods_arr[$k][$k1]['display']=1;
                    }
                }
            }
        }
        //没有秒杀商品
        if(empty($goods_arr)&&!empty($promotionGoods_arr)){
            $this->assign('spikeDisplay',1);
        }
        //没有促销商品
        if(empty($promotionGoods_arr)&&!empty($goods_arr)){
            $this->assign('promotionDisplay',1);
        }
        //秒杀和促销都没有
        if(empty($promotionGoods_arr)&&empty($goods_arr)){
            $this->assign('display',1);
        }
        /*     echo '<pre>';
             var_dump($goods_arr);exit;*/
//        echo '<pre>';print_r($promotionGoods_arr);die;
        //默认展示当前数据
        $this->assign('prokeys',$prokeys);
        $this->assign('first_day',$goods_arr[$keys[0]]);
        $this->assign('keys',$keys);
        $this->assign('activityData',$goods_arr);

        $this->assign('promotionData',$promotionGoods_arr);

        $this->assign('storeName',$storeName);
        $this->assign('background_img',$store_arr[0]['background_img']);
        $this->assign('logo',$store_arr[0]['logo']);
        $this->assign('store_arr',$store_arr[0]['store_discount']);
        $this->assign('mrstoreid',$storeid );
        $this->assign('res', $res);
        $this->assign('countryId',$this->countryId);
        $this->assign('rtid',$_REQUEST['rtid']);
        $this->assign('roomids',$roomIds);
        $this->assign('symbol', $this->symbol);
        $this->assign('roomtypearr', $roomtypearr);
        $this->display('goodsList/index.html');
    }


    //2级业务
    public function indexCeshi() {
        $this->load($this->shorthand, 'WeChat/goods');
        $this->assign('langdata', $this->langData);
        $storeGoodsMod = &m('areaGood');
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;
        $this->assign('storeid',$storeid);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;
        $this->assign('lang',$lang);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? $_REQUEST['auxiliary'] : 0;
        $this->assign('auxiliary',$auxiliary);
        $rtid = !empty($_REQUEST['rtid']) ? $_REQUEST['rtid'] : 0;
        $latlon = !empty($_REQUEST['latlon']) ? $_REQUEST['latlon'] : 0;
        $this->assign('latlon',$latlon);
        $order=!empty($_REQUEST['order']) ? $_REQUEST['order'] : 0;
        $this->assign('order',$order);
          $tp = !empty($_REQUEST['tp']) ? $_REQUEST['tp'] : 0;  //区别授权的
          if( $tp ){
              //判断是否登录
              header('Location: wx.php?app=user&act=loginOuth&back_app=' . APP . '&back_act=' . ACT .
  '&storeid=' . $_REQUEST['store_id'] . '&lang=' . $_REQUEST['lang_id']. '&latlon=' . $_REQUEST
  ['latlon']. '&auxiliary=' . $_REQUEST['auxiliary']. '&source=' . $_REQUEST['source']. '&rtid=' .
  $_REQUEST['rtid']. '&goods_tp=1');
              exit;
          }
        $store_sql = 'select store_discount,background_img,logo from  ' . DB_PREFIX . 'store where 
id=' . $this->storeid;
        $store_arr = $storeGoodsMod->querySql($store_sql);
        if($rtid==0){
            $buss_sql='select buss_id from  ' . DB_PREFIX . 'store_business where store_id=' .
                $this->storeid;
            $buss_arr = $storeGoodsMod->querySql($buss_sql);
            $rtid=$buss_arr[0]['buss_id'];
        }

        //去除没有商品的业务分类---临时解决方案
        /*      $roomTypeMod = &m('roomType');
              $roomtypearr = $roomTypeMod->getBusinessType($this->langid, $storeid, $rtid, 1);

              foreach($roomtypearr as $kk=>$vv){
                  $roomId[]=$vv['id'];
              }
              $roomIds=implode(',',$roomId);*/
        /* $hotGoods=$this->getHotGoods($roomIds,$storeid);
         $this->assign('hotGoods',$hotGoods);*/
        $storeGoodsMod=&m('storeGoods');
        $data=$storeGoodsMod->getRedisStoreGoods($storeid,$rtid,$lang,$this->userId);

        //error_log(print_r($data, 1), 3, 'redisGoods.log');
        $goods=$data['goods']; //业务类型商品
        $room=$data['room'];  //业务类型
        // $hotGoods=$data['hotGoods']; //热销商品
        $hot_ids = array_column($storeGoodsMod->getData(array(
            'fields' => 'id,goods_id,store_id',
            'cond'   => 'store_id = ' . $storeid . ' and is_hot = 1 and  is_on_sale = 1 and mark = 1',
            'order_by' => 'id DESC'
        )),NULL,'id');
        foreach ($goods as $v_1) {
            foreach ($v_1 as $v_2) {
                foreach ($v_2 as $v_3) {
                    if(array_key_exists($v_3['id'],$hot_ids)){
                        $hotGoods[] = $v_3;
                    }
                }
            }
        }
        $goodsCommentMod = &m('goodsComment');
        //热销商品和优惠商品名称
        $sql = 'select * from bs_config  where  inc_type = "shop_info" ';
        $data = $goodsCommentMod->querySql($sql);
        $res = array();
        foreach ($data as $key => $val) {
            $res[$val['name']] = $data[$key];
        }
        $storeMod = &m('store');
        $storeName=$storeMod->getNameById($storeid,$lang);
        $sqlNotice='select store_notice from bs_store where id ='.$storeid;
        $Noticedata = $goodsCommentMod->querySql($sqlNotice);
        $this->assign('Noticedata',$Noticedata[0]);
        //获取秒杀商品
        $spikeActivityMod = &m('spikeActivity');
        $spikeActiviesGoodsMod =& m('spikeActiviesGoods');
        $time        = time();
        //一周每天时间段以及对应秒杀商品
        $time_arr  = $goods_arr = array();
        $init_time = strtotime(date('Y-m-d', $time));
        for($i=1;$i<8;$i ++){
            $miao       = 24*60*60;                 //1天跨度
            $start_time = $init_time + ($i-1)*$miao;
            $end_time   = $init_time + ($i*$miao-1);
            $time_arr[$i][] = $start_time;          //开始时间
            $time_arr[$i][] = $end_time;            //结束时间
            //获取当天商品
            $day = date('Y-m-d', $start_time);
            if($day == date('Y-m-d', $time)){
                $day = '今日';
            } elseif($day == date('Y-m-d', ($time + $miao))){
                $day = '明日';
            }
            $skill_goods = $spikeActivityMod->getData(array(
                'fields'    => '*',
                'cond'      => ' mark = 1 and store_id = '.$storeid.' AND (start_time BETWEEN '.
                    $start_time .' AND '.$end_time .' OR end_time BETWEEN '. $start_time .' AND '.$end_time .' OR ( 
start_time < '. $start_time .' AND  end_time > '.$end_time .'))',
                'order_by'  => 'end_time desc'
            ));
            foreach($skill_goods as $k => $v){
                $skill_goods[$k]['format_start_time']   =   date('Y-m-d H:i', $v['start_time']);
                $skill_goods[$k]['format_end_time']     =   date('Y-m-d H:i', $v['end_time']);
                $skill_goods[$k]['source']=1;
                $skill_goods[$k]['goods']               =  $spikeActiviesGoodsMod->getSpikeGoods($v
                ['id'],$skill_goods[$k]);;
            }
            $skill_goods && $goods_arr[$day] = $skill_goods;
        }
        //获取当天商品
        foreach($goods_arr as $k=>$v){
            foreach($v as $k1=>$v1){
                if($k == '今日'){
                    if($v1['end_time']<time()){
                        $goods_arr[$k][$k1]['status']='活动结束';//已经结束
                    }
                    if($v1['start_time']<=time()&&$v1['end_time']>=time() ){
                        $goods_arr[$k][$k1]['status']='马上抢购';//开始
                    }
                    if($v1['start_time']>time()){
                        $goods_arr[$k][$k1]['status']='即将开始';//未开始
                    }
                } else {
                    $goods_arr[$k][$k1]['status']='活动预告';
                }
            }
        }
        $keys = array_keys($goods_arr);

        foreach($goods_arr as $k=>$v){
            if(empty($v)){
                $goods_arr[$k]['display']=0;
            }else{
                foreach($v as $k1=>$v1){
                    if(empty($v1)){
                        $goods_arr[$k][$k1]['display']=0;
                    }else{
                        $goods_arr[$k][$k1]['display']=1;
                    }
                }
            }


        }


        $promotionSaleMod = &m('promotionSale');
        $promotionGoodsMod = &m('promotionGoods');
        $times        = time();
        //一周每天时间段以及对应促销商品
        $times_arr  = $promotionGoods_arr = array();
        $init_time = strtotime(date('Y-m-d', $times));
        for($i=1;$i<8;$i++){
            $miao       = 24*60*60;                 //1天跨度
            $start_time = $init_time + ($i-1)*$miao;
            $end_time   = $init_time + ($i*$miao-1);
            $times_arr[$i][] = $start_time;          //开始时间
            $times_arr[$i][] = $end_time;            //结束时间
            //获取当天商品
            $day = date('Y-m-d', $start_time);
            if($day == date('Y-m-d', $times)){
                $day = '今日';
            } elseif($day == date('Y-m-d', ($times + $miao))){
                $day = '明日';
            }
            $promotion_goods = $promotionSaleMod->getData(array(
                'fields'    => '*',
                'cond'      => ' mark = 1 and store_id = '.$storeid.' AND (start_time BETWEEN '.
                    $start_time .' AND '.$end_time .' OR end_time BETWEEN '. $start_time .' AND '.$end_time .' OR ( 
start_time < '. $start_time .' AND  end_time > '.$end_time .'))',
                'order_by'  => 'end_time desc'
            ));
            foreach($promotion_goods as $k => $v){
                $promotion_goods[$k]['format_start_time']   =   date('Y-m-d H:i', $v['start_time']);
                $promotion_goods[$k]['format_end_time']     =   date('Y-m-d H:i', $v['end_time']);
                $promotion_goods[$k]['source']=1;
                $promotion_goods[$k]['goods']               =  $promotionGoodsMod->gePromotionGoods
                ($v['id'],$promotion_goods[$k]);;
            }
            $promotion_goods && $promotionGoods_arr[$day] = $promotion_goods;
        }
        //获取当天商品
        foreach($promotionGoods_arr as $k=>$v){
            foreach($v as $k1=>$v1){
                if($k == '今日'){
                    if($v1['end_time']<time()){
                        $promotionGoods_arr[$k][$k1]['status']='活动结束';//已经结束
                    }
                    if($v1['start_time']<=time()&&$v1['end_time']>=time() ){
                        $promotionGoods_arr[$k][$k1]['status']='马上抢购';//开始
                    }
                    if($v1['start_time']>time()){
                        $promotionGoods_arr[$k][$k1]['status']='即将开始';//未开始
                    }
                } else {
                    $promotionGoods_arr[$k][$k1]['status']='活动预告';
                }
            }
        }
        $prokeys = array_keys($promotionGoods_arr);

        foreach($promotionGoods_arr as $k=>$v){
            if(empty($v)){
                $promotionGoods_arr[$k]['display']=0;
            }else{
                foreach($v as $k1=>$v1){
                    if(empty($v1)){
                        $promotionGoods_arr[$k][$k1]['display']=0;
                    }else{
                        $promotionGoods_arr[$k][$k1]['display']=1;
                    }
                }
            }
        }
        //没有秒杀商品
        if(empty($goods_arr)&&!empty($promotionGoods_arr)){
            $this->assign('spikeDisplay',1);
        }
        //没有促销商品
        if(empty($promotionGoods_arr)&&!empty($goods_arr)){
            $this->assign('promotionDisplay',1);
        }
        //秒杀和促销都没有
        if(empty($promotionGoods_arr)&&empty($goods_arr)){
            $this->assign('display',1);
        }
//        echo '<pre>';print_r(unserialize($store_arr[0]['background_img']));die;
        $backgroundImg = unserialize($store_arr[0]['background_img']);
        foreach ($backgroundImg as $key => &$value) {
            if ($value['activity_id']) {
                $activityMod = &m('storeActivity');
                $activity = $activityMod->getRow($value['activity_id']);
                if ($activity['is_use'] == 2 || $value['begin_time'] > time() || $value['end_time'] < time()) {
                    $value['activity_id'] = 0;
                }

            }
        }
        $this->assign('webtitle',"店铺商品");
        //默认展示当前数据
        $this->assign('app',APP);
        $this->assign('goods',$goods);
        $this->assign('room',$room);
        $this->assign('hotGoods',$hotGoods);
        $this->assign('prokeys',$prokeys);
        $this->assign('first_day',$goods_arr[$keys[0]]);
        $this->assign('keys',$keys);
        $this->assign('activityData',$goods_arr);
        $this->assign('promotionData',$promotionGoods_arr);
        $this->assign('storeName',$storeName);
        $this->assign('background_img', $backgroundImg);
        $this->assign('logo',$store_arr[0]['logo']);
        $this->assign('store_arr',$store_arr[0]['store_discount']);
        $this->assign('mrstoreid',$storeid );
        $this->assign('res', $res);
        $this->assign('countryId',$this->countryId);
        $this->assign('rtid',$_REQUEST['rtid']);
        /*   $this->assign('roomids',$roomIds);*/
        $this->assign('symbol', $this->symbol);
        /* $this->assign('roomtypearr', $roomtypearr);*/
        $this->display('goodsList/indexCeshi.html');
    }





    public function  getUserNum($source,$prom_id,$store_goods_id){
        $storeGoodsMod = &m('areaGood');
        $sql="select sum(og.goods_num) as total from ".DB_PREFIX.'order  as o  left join '.DB_PREFIX.'order_goods as og ON og.order_id = o.order_sn
        where o.buyer_id='.$this->userId.' and  og.prom_type='.$source.' and og.prom_id='.$prom_id.' and og.goods_id='.$store_goods_id.' and o.mark=1 and o.order_state >=20';
        $sum=$storeGoodsMod->querySql($sql);
        if(empty($sum[0]['total'])){
            $sum[0]['total']=0;
        }

        return $sum[0]['total'];
    }

    //睿积分抵扣金额
    public function  getPointAccount($total,$storeid){
        $pointSiteMod = &m('point');
        $storePointMod = &m('storePoint');
        $storeMod = &m('store');
        $curMod = &m('currency');
        //获取订单总金额
        //获取最大积分支付比例
        $point_price_site = $pointSiteMod->getOne(array("cond" => "1=1"));
        $store_point_site = $storePointMod->getOne(array("cond" => "store_id=" . $storeid));
        if($this->userId){
            $userSql = "select rc.percent from " . DB_PREFIX . "user as u  LEFT JOIN  "
                . DB_PREFIX . "recharge_point AS rc on u.recharge_id=rc.id where u.id = ".$this->userId;
            $user_id = $storeMod->querySql($userSql);
            if(!empty($user_id[0]['percent'])){
                $point_price_site['point_price'] = $user_id[0]['percent'] + $store_point_site['point_price'];
            }else{
                $point_price_site['point_price'] = empty($store_point_site) ? 0 : $store_point_site['point_price'];
            }
        }else{
            $point_price_site['point_price'] = empty($store_point_site) ? 0 : $store_point_site['point_price'];
        }
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
        return $point_price;
    }

    //活动商品
    public function getACtivity($roomIds,$storeid){
        //秒杀商品
        $seckMod = &m('spikeActivity');
        $goodsByMod = &m('groupbuy');
        $goodPromMod = &m('goodProm');
        $storeGoodsMod = &m('areaGood');
        $curtime = time();
        $today = strtotime(date('Y-m-d', time()));
        $now = $curtime - $today;


        $where1 =  ' where s.store_id =' . $storeid . '  and   (s.start_time+s.start_our) <= ' . $curtime . ' and   (s.end_time+s.end_our)  >= ' . $curtime .' and rc.room_type_id in ('.$roomIds.') and g.is_on_sale =1 and g.mark=1 and l.lang_id ='.$this->langid ;
        $sql1 = 'SELECT  s.id as cid,s.`name`,s.start_time,s.end_time,s.start_our,s.end_our,s.store_id,s.store_goods_id as id,gl.original_img,s.content,s.item_name,s.item_key,s.discount,s.o_price,s.price,s.goods_num,g.is_free_shipping,l.goods_remark,gl.goods_id as good_id,g.id as gid,rc.room_type_id,g.is_on_sale,g.mark,g.goods_storage,s.limit_num,s.store_id FROM  '
            . DB_PREFIX . 'spike_activity as s left join '
            . DB_PREFIX . 'store_goods as g on  s.store_goods_id = g.id  LEFT JOIN  '
            . DB_PREFIX . 'room_category AS rc on g.cat_id=rc.category_id LEFT JOIN '
            . DB_PREFIX . 'goods AS gl ON g.`goods_id` = gl.`goods_id` LEFT JOIN  bs_goods_lang AS l ON g.`goods_id` = l.`goods_id`  '. $where1;
        $spikeArr = $seckMod->querySql($sql1);
        foreach($spikeArr as $key=>$val){
            $spikeArr[$key]['source']=1;
            $child_info = $storeGoodsMod->getLangInfo($val['id'], $this->langid);
            if ($child_info) {
                $k_name = $child_info['goods_name'];
                $spikeArr[$key]['goods_name'] = $k_name;
            }
            $member_price=$val['price']-($this->getPointAccount($val['price'],$storeid));
            $spikeArr[$key]['member_price']=number_format($member_price,2);

        }

        //团购商品
        $where3 = 'WHERE  l.`lang_id` = ' . $this->langid . '  and  b.store_id =' . $this->storeid . '  AND b.is_end =1 AND b.mark = 1  and rc.room_type_id in ('.$roomIds.') and g.is_on_sale=1 and g.mark=1 ';
        $sql3 = 'SELECT b.title as name, b.id as cid,b.goods_id as id,b.store_id,b.end_time,b.group_goods_price as price,b.virtual_num,gl.original_img,b.goods_price as o_price,l.goods_name,b.goods_spec_key as item_key,gl.goods_id as good_id,g.id as gid,g.goods_storage
                FROM  bs_goods_group_buy  AS b  LEFT JOIN   bs_store_goods AS g ON b.`goods_id` = g.id
                LEFT JOIN  bs_goods_lang AS l ON g.`goods_id` = l.`goods_id`   LEFT JOIN  '
            . DB_PREFIX . 'room_category AS rc on g.cat_id=rc.category_id LEFT JOIN '
            . DB_PREFIX . 'goods AS gl ON g.`goods_id` = gl.`goods_id`'. $where3;

        $groupByGoodArr = $goodsByMod->querySql($sql3);
        foreach($groupByGoodArr as $key=>$val){
            $groupByGoodArr[$key]['source']=2;
            $member_price=$val['price']-($this->getPointAccount($val['price'],$storeid));
            $groupByGoodArr[$key]['member_price']=number_format($member_price,2);

        }
        $this->checkOver();
        // 获取正在进行或者未开始的促销活动
        $sql4 = " select  ps.prom_name as name, ps.id as cid,ps.*,pg.goods_id as id,pg.goods_key as item_key,pg.goods_key_name,l.goods_name,gl.original_img,pg.goods_price as o_price,pg.discount_price as price,s.is_free_shipping,gl.goods_id as good_id,s.id as gid,pg.limit_amount,l.goods_remark,s.goods_storage  from "
            . DB_PREFIX . "promotion_sale as ps left join "
            . DB_PREFIX . "promotion_goods as pg on  ps.id = pg.prom_id left join "
            . DB_PREFIX . "store_goods as s on pg.goods_id = s.id  LEFT JOIN "
            . DB_PREFIX . "goods AS gl ON s.`goods_id` = gl.`goods_id` LEFT JOIN  bs_goods_lang AS l ON s.`goods_id` = l.`goods_id` LEFT JOIN "
            . DB_PREFIX . "room_category AS rc on s.cat_id=rc.category_id
             where ps.`store_id` = $this->storeid and ps.`status` in (1,2) and ps.`mark` =1  and rc.room_type_id in ($roomIds) and s.is_on_sale = 1 and s.mark = 1  and l.lang_id = ".$this->langid."  order by ps.status desc,ps.id desc";
        $promotionGoodsArr = $goodPromMod->querySql($sql4);
        foreach($promotionGoodsArr as $key=>$val){
            $promotionGoodsArr[$key]['source']=3;
            $member_price=$val['price']-($this->getPointAccount($val['price'],$storeid));
            $promotionGoodsArr[$key]['member_price']=number_format($member_price,2);

        }
        $res = array();
        $res['data'] = array_merge($spikeArr, $groupByGoodArr, $promotionGoodsArr);
        return $res;
    }

    //热销商品
    public function getHotGoods($roomIds,$storeid){
        $storeGoodsMod = &m('areaGood');
        $where = '  where     s.store_id =' . $storeid . '   and rc.room_type_id in ('.$roomIds.')  and   s.mark=1    and   s.is_on_sale =1  AND l.`lang_id` = ' . $this->langid ;
        //所以子类的商品
        $hsql = 'SELECT s.id,s.`goods_id`,l.`goods_name`,l.`lang_id`,l.`goods_remark`,s.`shop_price`,s.`market_price`,gl.`original_img`,s.goods_storage,l.goods_remark
                FROM  ' . DB_PREFIX . 'store_goods AS s LEFT JOIN  '
            . DB_PREFIX . 'room_category AS rc on s.cat_id=rc.category_id LEFT JOIN '
            . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id`  left join '
            . DB_PREFIX . 'goods as gl on s.goods_id = gl.goods_id' . $where;
        $hotData=$storeGoodsMod->querySql($hsql);
        foreach($hotData as $k=>$v){
            $oSql="SELECT rec_id,goods_num  FROM ".DB_PREFIX.'order_goods WHERE goods_id='.$v['id']." and order_state in (20,30,40,50)";
            $oData=$storeGoodsMod->querySql($oSql);
            if(!empty($oData)){
                $sum=0;
                foreach($oData as $k1=>$v1){
                    $sum +=$v1['goods_num'];
                }
                $hotData[$k]['order_num']=$sum;
            }else{
                $hotData[$k]['order_num']=0;
            }
            $goodsCommentMod = &m('goodsComment');
            $sql = 'select  sum(goods_rank) / count(goods_rank) as res,count(*) as num from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $v['gid'];
            $trance = $goodsCommentMod->querySql($sql);
            $hotData[$k]['rate']=(int)$trance[0]['res'];;
            $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $storeid;
            $store_arr = $storeGoodsMod->querySql($store_sql);
            $member_price=$v['shop_price'] * $store_arr[0]['store_discount']-($this->getPointAccount($v['shop_price'] * $store_arr[0]['store_discount'],$storeid));
            $hotData[$k]['member_price']=number_format($member_price,2);
            $hotData[$k]['shop_price'] = number_format($v['shop_price'] * $store_arr[0]['store_discount'],2);
            $hotData[$k]['sale_price']=number_format($v['shop_price'],2);
        }
        $sort = array(
            'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
            'field' => 'order_num', //排序字段
        );
        $arrSort = array();
        foreach ($hotData AS $uniqid => $row) {
            foreach ($row AS $key => $value) {
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if ($sort['direction']) {
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $hotData);
        }
        $hotData=array_slice($hotData,0,4);
        return $hotData;
    }

    //检查促销商品是否过期
    public function checkOver() {
        $goodPromMod = &m('goodProm');
        $sql = "select * from " . DB_PREFIX . "promotion_sale where mark =1";
        $rs = $goodPromMod->querySql($sql);
        foreach ($rs as $k => $v) {
            if ($v['start_time'] > time()) {
                $vstatus = 1;
            } elseif ($v['start_time'] <= time() && $v['end_time'] >= time()) {
                $vstatus = 2;
            } elseif ($v['end_time'] < time()) {
                $vstatus = 3;
            }
            $goodPromMod->doEdit($v['id'], array('status' => $vstatus));
        }
    }

    //获取商品属性规格
    public function getType(){
        /*        $goodMod = &m('goods');
                $storeGoodMod = &m("storeGoodItemPrice");*/
        $storeGoods = &m('areaGood');
        $id=$_REQUEST['id'];
        $goods_id=$_REQUEST['goods_id'];
        /*  $storeid=$_REQUEST['storeid'];*/
        $lang=$_REQUEST['lang'];
        $source=$_REQUEST['source'];
        $cid=$_REQUEST['cid'];
        $latlon=$_REQUEST['latlon'];
        $goodinfo = $storeGoods->getLangInfo1($id, $lang);
        if ($source == 3) {
            $sql = " select ps.*,pg.goods_key as item_id,pg.goods_price,pg.discount_price,pg.limit_amount,ps.status  from " . DB_PREFIX . "promotion_sale as ps left join " . DB_PREFIX . "promotion_goods as pg on
                ps.id = pg.prom_id  where ps.`store_id` = $this->storeid and ps.`status` in (1,2) and ps.`mark` =1 and ps.id =$cid  and pg.goods_id=".$id;
            $arr = $storeGoods->querySql($sql);
            foreach ($arr as $k => $v) {
                $arr1 = explode('_', $v['item_id']);

            }
        }
        // 团购
        if ($source == 2) {
            $where2 = '  where  store_id = ' . $this->storeid . ' and  mark =1 and id=' . $cid;
            $sql2 = 'SELECT  goods_spec_key as item_id,group_goods_price,goods_price,group_goods_num,virtual_num,end_time,title as name,start_time
                FROM  ' . DB_PREFIX . 'goods_group_buy ' . $where2;
            $arr = $storeGoods->querySql($sql2);
            foreach ($arr as $v) {
                $arr1 = explode('_', $v['item_id']);
            }}
        $spec_img1 = $this->get_spec($goodinfo['goods_id'], $id, 2);

        if (!empty($arr1)) {
            foreach ($spec_img1 as $key => $value) {
                foreach ($value as $k => $v) {
                    if (!in_array($v['item_id'], $arr1))
                        unset($spec_img1[$key][$k]);
                }
            }
        }

        foreach($spec_img1 as $key1=>$value1){
            foreach($value1 as $k1=>$v1){
                $spec_img[$key1][]=$v1;
            }
        }

        $storeList = $this->getCountryStore($this->countryId,$goods_id,$latlon);
        $info=array('spec_img'=>$spec_img,'info'=>$goodinfo,'lang'=>$lang,'storeList'=>$storeList);
        $this->setData($info,1,'');

    }

    /**
     * 获取商品规格
     * @param $goods_id|商品id  $type=1 读取商品原有规格属性  2 读取区域编辑后的规格属性
     * @return array
     */
    public function get_spec($goods_id, $store_goods_id, $type = 1) {

        $storeGoodMod = &m("storeGoodItemPrice");
        //商品规格 价钱 库存表 找出 所有 规格项id
        //$keys = M('SpecGoodsPrice')->where("goods_id", $goods_id)->getField("GROUP_CONCAT(`key` SEPARATOR '_') ");
        if ($type == 1) {
            $sql = "select `key` as item_key from  " . DB_PREFIX . "goods_spec_price where goods_id=" . $goods_id;
        } else {
            $sql = "select `key` as item_key from  " . DB_PREFIX . "store_goods_spec_price where store_goods_id=" . $store_goods_id;
        }
        $keys = $storeGoodMod->querySql($sql);
        $filter_spec = array();
        if ($keys) {
            $key_str = "";
            foreach ($keys as $k => $v) {
                $key_str .= $v['item_key'] . "_";
            }
            $res_item = substr($key_str, 0, strlen($key_str) - 1);
            $keys = str_replace('_', ',', $res_item);
            $specImage = array();
            $sql3 = "select spec_image_id,src from " . DB_PREFIX . "goods_spec_image where goods_id=" . $goods_id; // 规格对应的 图片表， 例如颜色
            $img_list = $storeGoodMod->querySql($sql3);
            foreach ($img_list as $k => $v) {
                $specImage[$v['spec_image_id']] = $v['src'];
            }
            $sql4 = "SELECT a.`order`,b.*,al.spec_name,bl.`item_name`  FROM " . DB_PREFIX . "goods_spec AS a
                     INNER JOIN " . DB_PREFIX . "goods_spec_item AS b ON a.id = b.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_lang AS al ON a.id=al.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_item_lang as bl ON b.id=bl.item_id
                     WHERE b.id IN($keys) and al.lang_id=" . $this->langid . " and bl.lang_id=" . $this->langid . " ORDER BY a.sort,b.id";
            $filter_spec2 = $storeGoodMod->querySql($sql4);
            foreach ($filter_spec2 as $key => $val) {
                $filter_spec[$val['spec_name']][] = array(
                    'item_id' => $val['id'],
                    'item' => $val['item_name'],
                    'src' => $specImage[$val['id']],
                );
            }
        }
        return $filter_spec;
    }


    //获取配送店铺
    public function getCountryStore($country_id,$goods_id,$latlon) {
        if ($_SESSION['userId']) {
            $user_id = $_SESSION['userId'];
            $userMod = &m('user');
            $sql = 'SELECT *  FROM  ' . DB_PREFIX . 'user WHERE  id  =' . $user_id; //odm_members
            $datas = $userMod->querySql($sql);
            if ($datas[0]['odm_members'] == 0) {
                $where = ' and c.store_type < 4  ';
            } else {
                $where = '';
            }
        } else {
            $where = ' and c.store_type < 4  ';
        }
        $mod = &m('store');
        $sql = 'SELECT  c.id,l.store_name,c.distance,c.longitude,c.latitude  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE c.is_open = 1  and  l.lang_id =' . $this->langid . ' and l.distinguish=0  and c.store_cate_id=' . $country_id . $where;
        $data = $mod->querySql($sql);
        $sql1='SELECT store_id FROM '.DB_PREFIX.'store_goods  WHERE goods_id='.$goods_id . ' and mark =1  and is_on_sale =1 ';
        $gData=$mod->querySql($sql1);
        foreach($gData as $key=>$val){
            $val=join(',',$val);
            $temp[]=$val;
        }
        $temp=array_unique($temp);
        foreach($data as $k=>$v){
            foreach($temp as $k1=>$v1){
                if($v['id']==$v1){
                    $arr[$k1]['id']=$v['id'];
                    $arr[$k1]['store_name']=$v['store_name'];
                    $arr[$k1]['distance']=$v['distance'];
                    $arr[$k1]['latitude']=$v['latitude'];
                    $arr[$k1]['longitude']=$v['longitude'];
                }
            }
        }

        $latlon = explode(',', $latlon);
        $lng = $latlon[0]; //经度
        $lat = $latlon[1]; //纬度
        foreach ($arr as $key => $val) {
            $s = $this->getdistance($lng, $lat, $val['longitude'], $val['latitude']);
            $distance = number_format(($s / 1000), 2, '.', '');
            $arr[$key]['dis'] = $distance;
            $busSql = "SELECT buss_id FROM " . DB_PREFIX . 'store_business WHERE  store_id=' .$val['id'];
            $busData = $mod->querySql($busSql);
            $arr[$key]['b_id']=$busData[0]['buss_id'];
            if($val['distance'] < $distance){
                unset($arr[$key]);
            }
        }
        $sort = array(
            'direction' => 'SORT_ASC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
            'field' => 'dis', //排序字段
        );
        $arrSort = array();
        foreach ($arr AS $uniqid => $row) {
            foreach ($row AS $key => $value) {
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if ($sort['direction']) {
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $arr);
        }
        return $arr;
    }

    //获取规格价格库存
    public function getSpec(){
        $store_goods_id=$_REQUEST['store_goods_id'];
        $store_id=$_REQUEST['store_id'];
        $storeMod=&m('storeGoods');
        $where=' and  mark=1   and   is_on_sale =1';
        $sql="SELECT id FROM ".DB_PREFIX.'store_goods WHERE store_id='.$store_id.' AND goods_id='.$store_goods_id.$where;
        $data=$storeMod->querySql($sql);
        $id=$data[0]['id'];
        $storeGoodMod = &m("storeGoodItemPrice");
        $spec_data = $storeGoodMod->getData(array('cond' => "store_goods_id=" . $id));
        foreach ($spec_data as $k => $v) {
            $spec_arr[$v['key']] = $v;
        }
        $spec_arr=json_encode($spec_arr);
        $info=array('id'=>$id,'spec_arr'=>$spec_arr);
        $this->setData($info,1,'');
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



    public function getLangInfo1($id,$lang_id){
        $storeGoods = &m('areaGood');
        $info=$storeGoods->getRow($id,'id,goods_id');
        $sqlLang="select  goods_name  from ".DB_PREFIX."goods_lang where goods_id=".$info['goods_id']." and lang_id=".$lang_id;
        $langInfo=$this->querySql($sqlLang);
        if($langInfo){
            $info['goods_name']=$langInfo[0]['goods_name'];
        }
        return $info;
    }
    public function  getRoomGoods(){
        $roomId=!empty($_REQUEST['roomId']) ? $_REQUEST['roomId'] : 0;
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;
        $roomIds=!empty($_REQUEST['roomids']) ? $_REQUEST['roomids']: 0;
        $type=!empty($_REQUEST['type'])? $_REQUEST['type']: 0; //type=1 促销商品   2：热卖商品 3;分类商品
        $storeGoodsMod = &m('areaGood');
        if($type==1){
            $activiData=$this->getACtivity($roomIds,$storeid);
            $goodsCommentMod = &m('goodsComment');
            foreach($activiData['data'] as $key=>$val){
                $oSql="SELECT rec_id,goods_num FROM ".DB_PREFIX.'order_goods WHERE goods_id='.$val['gid']." and order_state in (20,30,40,50)";
                $oData=$storeGoodsMod->querySql($oSql);
                if(!empty($oData)){
                    $num=0;
                    foreach($oData as $k=>$v){
                        $num+=$v['goods_num'];
                    }
                    $activiData['data'][$key]['order_num']=$num;
                }else{
                    $activiData['data'][$key]['order_num']=0;
                }

                $sql = 'select  sum(goods_rank) / count(goods_rank) as res,count(*) as num from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $val['gid'];
                $trance = $goodsCommentMod->querySql($sql);
                $activiData['data'][$key]['rate']=(int)$trance[0]['res'];
                $activiData['data'][$key]['user_num']=$this->getUserNum($val['source'],$val['cid'],$val['gid']);
            }
            $gooodInfo=array('data'=>$activiData,'type'=>1);
            if($activiData){
                $this->setData($gooodInfo,1,'');
            }
        }
        if($type==2){
            $hotGoods=$this->getHotGoods($roomIds,$storeid);
            foreach($hotGoods as $k=>$v){
                if($v['id']==9713){
                    unset($hotGoods[$k]);
                }
            }
            $gooodInfo=array('data'=>$hotGoods,'type'=>2);

            if($hotGoods){
                $this->setData($gooodInfo,1,'');
            }
        }
        if($type==3){
            $where = '  where   s.store_id =' . $storeid . '   and rc.room_type_id = ' . $roomId . '  and   s.mark=1   and   s.is_on_sale =1  AND l.`lang_id` = ' . $this->langid;
            //所以子类的商品
            $rsql = 'SELECT s.id,rc.sort,s.`goods_id`,s.`cat_id`,s.`store_id`,l.`goods_name`,l.`lang_id`,l.`goods_remark`,s.`shop_price`,s.`market_price`,s.`brand_id`,gl.`original_img`,s.is_free_shipping,s.add_time,l.goods_remark,s.goods_storage
                FROM  ' . DB_PREFIX . 'store_goods AS s LEFT JOIN  '
                . DB_PREFIX . 'room_category AS rc on s.cat_id=rc.category_id LEFT JOIN '
                . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id`  left join '
                . DB_PREFIX . 'goods as gl on s.goods_id = gl.goods_id ' . $where;
            $goods =$storeGoodsMod->querySql($rsql);
            foreach($goods as $k1=>$v1){
                //店铺商品打折
                $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $this->storeid;
                $store_arr = $storeGoodsMod->querySql($store_sql);
                $goods[$k1]['shop_price'] =number_format($v1['shop_price'] * $store_arr[0]['store_discount'],2);
                $member_price=$v1['shop_price'] * $store_arr[0]['store_discount']-($this->getPointAccount($v1['shop_price'] * $store_arr[0]['store_discount'],$storeid));
                $goods[$k1]['member_price'] =number_format($member_price,2);
                $goods[$k1]['sale_price'] =number_format($v1['shop_price'],2);
                $oSql="SELECT rec_id,goods_num  FROM ".DB_PREFIX.'order_goods WHERE goods_id='.$v1['id']." and order_state in (20,30,40,50)";
                $oData=$storeGoodsMod->querySql($oSql);
                if(!empty($oData)){
                    $sum=0;
                    foreach($oData as $k2=>$v2){
                        $sum +=$v2['goods_num'];
                    }
                    $goods[$k1]['order_num']=$sum;
                }else{
                    $goods[$k1]['order_num']=0;
                }
                $goodsCommentMod = &m('goodsComment');
                $sql = 'select  sum(goods_rank) / count(goods_rank) as res,count(*) as num from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $v1['id'];
                $trance = $goodsCommentMod->querySql($sql);
                $goods[$k1]['rate'] = (int)$trance[0]['res'];
                $goods[$k1]['num'] = $trance[0]['num'];
                if($v1['id']==9713){
                    unset($goods[$k1]);
                }
            }
            $gooodInfo=array('data'=>$goods,'type'=>3);
            if($goods){
                $this->setData($gooodInfo,1,'');
            }
        }


    }


    /**
     * 获得配送属性对应的商品
     * @author tangp
     * @date 2019-03-07
     */
    public function getAttributeGoods()
    {
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : '';
        $rtid     = !empty($_REQUEST['rtid']) ? $_REQUEST['rtid'] : '';
        $lang_id  = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : '';
        $type     = !empty($_REQUEST['type']) ? $_REQUEST['type'] : '';
        $ress = &m('storeGoods')->getGoods($store_id,$rtid,$lang_id,$this->userId,$type);
        $goodsCommentMod = &m('goodsComment');
        //热销商品和优惠商品名称
        $sql = 'select * from bs_config  where  inc_type = "shop_info" ';
        $data = $goodsCommentMod->querySql($sql);
        $res = array();
        foreach ($data as $key => $val) {
            $res[$val['name']] = $data[$key];
        }
//        echo '<pre>';print_r($res);die;
        $goods=$ress['goods']; //业务类型商品
        $room=$ress['room'];  //业务类型
        $hotGoods=$ress['hotGoods']; //热销商品
//        echo '<pre>';print_r($hotGoods);die;
        $this->assign('goods',$goods);
        $this->assign('room',$room);
        $this->assign('res',$res);
        $this->assign('hotGoods',$hotGoods);
        $this->load($this->shorthand, 'WeChat/goods');
        $this->assign('langdata', $this->langData);
        $html = self::$smarty->fetch('goodsList/self.html');

        $this->setData($html,1,'');
    }

    public function getAllGoods()
    {
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : '';
        $rtid     = !empty($_REQUEST['rtid']) ? $_REQUEST['rtid'] : '';
        $lang_id  = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : '';
        $storeGoodsMod=&m('storeGoods');
        $data=$storeGoodsMod->getRedisStoreGoods($store_id,$rtid,$lang_id,$this->userId);
        //error_log(print_r($data, 1), 3, 'redisGoods.log');
        $goods=$data['goods']; //业务类型商品
        $room=$data['room'];  //业务类型
        $hotGoods=$data['hotGoods']; //热销商品

        $goodsCommentMod = &m('goodsComment');
        //热销商品和优惠商品名称
        $sql = 'select * from bs_config  where  inc_type = "shop_info" ';
        $data = $goodsCommentMod->querySql($sql);
        $res = array();
        foreach ($data as $key => $val) {
            $res[$val['name']] = $data[$key];
        }
        $this->assign('goods',$goods);
        $this->assign('room',$room);
        $this->assign('res',$res);
        $this->assign('hotGoods',$hotGoods);
        $this->load($this->shorthand, 'WeChat/goods');
        $this->assign('langdata', $this->langData);
        $html = self::$smarty->fetch('goodsList/self.html');

        $this->setData($html,1,'');
    }

    /**
     * 店铺活动
     * @author zhangkx
     * @date 2019/3/22
     */
    public function activity()
    {
        //授权后的登录检验
        $this->ischeckLogin();
        //判断是否登录
        if (!isset($_SESSION['userId'])) {
            header('Location: wx.php?app=user&act=loginOuth&back_app=' . APP . '&back_act=' . ACT .  '&id='.$_REQUEST['activity_id']. '&storeid=' . $this->storeid . '&lang=' . $this->langid);
        }
        $id = $_REQUEST['id'] ? $_REQUEST['id'] : 0;
        $activityMod = &m('storeActivity');
        $applyMod = &m('storeActivityApply');
        $data = $activityMod->getRow($id);
        if ($this->userId) {
            $apply = $applyMod->getOne(array('cond' => 'activity_id = '.$id.' and user_id = '.$this->userId));
            $data['status'] = $apply['status'] == 1 ? 1 : 2;
            $this->assign('userId', $this->userId);
        }
        $this->load($this->shorthand, 'WeChat/goods');
        $this->assign('langdata', $this->langData);
        $this->assign('data', $data);
        $this->display('goodsList/activity.html');
    }

    /**
     * 报名店铺活动
     * @author zhangkx
     * @date 2019/3/22
     */
    public function apply()
    {
        if (IS_POST) {
            $data = $_POST;
            $data['source'] = 1;
            $data['status'] = 1;
            $applyMod = &m('storeActivityApply');
            if (method_exists($applyMod,  'checkData')) {
                $applyMod->checkData($data);
            }
            //组装数据
            if (method_exists($applyMod,  'buildData')) {
                $data = $applyMod->buildData($data);
            }
            //插入数据
            $result = $applyMod->doInsert($data);
            if (!$result) {
                $this->setData(array(), '0', '报名失败');
            }
            $this->setData(array(),'1','报名成功');
        }
    }

    public function newIndex()
    {
        $this->load($this->shorthand, 'WeChat/goods');
        $this->assign('langdata', $this->langData);
        $storeGoodsMod = &m('areaGood');
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;
        $this->assign('storeid',$storeid);
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;
        $this->assign('lang',$lang);
        $auxiliary = !empty($_REQUEST['auxiliary']) ? $_REQUEST['auxiliary'] : 0;
        $this->assign('auxiliary',$auxiliary);
        $rtid = !empty($_REQUEST['rtid']) ? $_REQUEST['rtid'] : 0;
        $latlon = !empty($_REQUEST['latlon']) ? $_REQUEST['latlon'] : 0;
        $this->assign('latlon',$latlon);
        $order=!empty($_REQUEST['order']) ? $_REQUEST['order'] : 0;
        $this->assign('order',$order);
        $tp = !empty($_REQUEST['tp']) ? $_REQUEST['tp'] : 0;  //区别授权的
        if ($tp) {
            //判断是否登录
            header('Location: wx.php?app=user&act=loginOuth&back_app=' . APP . '&back_act=' . ACT .
                '&storeid=' . $_REQUEST['store_id'] . '&lang=' . $_REQUEST['lang_id']. '&latlon=' . $_REQUEST
                ['latlon']. '&auxiliary=' . $_REQUEST['auxiliary']. '&source=' . $_REQUEST['source']. '&rtid=' .
                $_REQUEST['rtid']. '&goods_tp=1');
            exit;
        }
        if($rtid==0){
            $buss_sql='select buss_id from  ' . DB_PREFIX . 'store_business where store_id=' .
                $this->storeid;
            $buss_arr = $storeGoodsMod->querySql($buss_sql);
            $rtid=$buss_arr[0]['buss_id'];
        }
        //获取店铺信息
        $storeMod = &m('store');
        $storeData = $storeMod->getRow($this->storeid);
        $storeName = $storeMod->getNameById($storeid,$lang);
        $this->assign('storeName', $storeName);
        $this->assign('storeData', $storeData);
        //获取店铺商品
        $storeGoodsMod = &m('storeGoods');
        $data = $storeGoodsMod->getRedisStoreGoods($storeid,$rtid,$lang,$this->userId);
        $goods = $data['goods']; //业务类型商品
        $room = $data['room'];  //业务类型
        $hotGoods = $data['hotGoods']; //热销商品
        $this->assign('room', $room);
        $this->assign('hotGoods', $hotGoods);
        $this->assign('goods', $goods);
        $goodsCommentMod = &m('goodsComment');
        //热销商品和优惠商品名称
        $sql = 'select * from bs_config  where  inc_type = "shop_info" ';
        $data = $goodsCommentMod->querySql($sql);
        $res = array();
        foreach ($data as $key => $val) {
            $res[$val['name']] = $data[$key];
        }
        //获取秒杀商品
        $spikeActivityMod = &m('spikeActivity');
        $spikeActiviesGoodsMod =& m('spikeActiviesGoods');
        $time        = time();
        //一周每天时间段以及对应秒杀商品
        $time_arr  = $goods_arr = array();
        $init_time = strtotime(date('Y-m-d', $time));
        for ($i=1;$i<8;$i ++) {
            $miao       = 24*60*60;                 //1天跨度
            $start_time = $init_time + ($i-1)*$miao;
            $end_time   = $init_time + ($i*$miao-1);
            $time_arr[$i][] = $start_time;          //开始时间
            $time_arr[$i][] = $end_time;            //结束时间
            //获取当天商品
            $day = date('Y-m-d', $start_time);
            if($day == date('Y-m-d', $time)){
                $day = '今日';
            } elseif($day == date('Y-m-d', ($time + $miao))){
                $day = '明日';
            }
            $skill_goods = $spikeActivityMod->getData(array(
                'fields'    => '*',
                'cond'      => ' mark = 1 and store_id = '.$storeid.' AND (start_time BETWEEN '.
                    $start_time .' AND '.$end_time .' OR end_time BETWEEN '. $start_time .' AND '.$end_time .' OR ( 
start_time < '. $start_time .' AND  end_time > '.$end_time .'))',
                'order_by'  => 'end_time desc'
            ));
            foreach($skill_goods as $k => $v){
                $skill_goods[$k]['format_start_time']   =   date('Y-m-d H:i', $v['start_time']);
                $skill_goods[$k]['format_end_time']     =   date('Y-m-d H:i', $v['end_time']);
                $skill_goods[$k]['source']=1;
                $skill_goods[$k]['goods']               =  $spikeActiviesGoodsMod->getSpikeGoods($v
                ['id'],$skill_goods[$k]);;
            }
            $skill_goods && $goods_arr[$day] = $skill_goods;
        }
        //获取当天商品
        foreach($goods_arr as $k=>$v){
            foreach($v as $k1=>$v1){
                if($k == '今日'){
                    if($v1['end_time']<time()){
                        $goods_arr[$k][$k1]['status']='活动结束';//已经结束
                    }
                    if($v1['start_time']<=time()&&$v1['end_time']>=time() ){
                        $goods_arr[$k][$k1]['status']='马上抢购';//开始
                    }
                    if($v1['start_time']>time()){
                        $goods_arr[$k][$k1]['status']='即将开始';//未开始
                    }
                } else {
                    $goods_arr[$k][$k1]['status']='活动预告';
                }
            }
        }
        $keys = array_keys($goods_arr);
        foreach($goods_arr as $k=>$v){
            if(empty($v)){
                $goods_arr[$k]['display']=0;
            }else{
                foreach($v as $k1=>$v1){
                    if(empty($v1)){
                        $goods_arr[$k][$k1]['display']=0;
                    }else{
                        $goods_arr[$k][$k1]['display']=1;
                    }
                }
            }
        }
        $promotionSaleMod = &m('promotionSale');
        $promotionGoodsMod = &m('promotionGoods');
        $times        = time();
        //一周每天时间段以及对应促销商品
        $times_arr  = $promotionGoods_arr = array();
        $init_time = strtotime(date('Y-m-d', $times));
        for($i=1;$i<8;$i++){
            $miao       = 24*60*60;                 //1天跨度
            $start_time = $init_time + ($i-1)*$miao;
            $end_time   = $init_time + ($i*$miao-1);
            $times_arr[$i][] = $start_time;          //开始时间
            $times_arr[$i][] = $end_time;            //结束时间
            //获取当天商品
            $day = date('Y-m-d', $start_time);
            if($day == date('Y-m-d', $times)){
                $day = '今日';
            } elseif($day == date('Y-m-d', ($times + $miao))){
                $day = '明日';
            }
            $promotion_goods = $promotionSaleMod->getData(array(
                'fields'    => '*',
                'cond'      => ' mark = 1 and store_id = '.$storeid.' AND (start_time BETWEEN '.
                    $start_time .' AND '.$end_time .' OR end_time BETWEEN '. $start_time .' AND '.$end_time .' OR ( 
start_time < '. $start_time .' AND  end_time > '.$end_time .'))',
                'order_by'  => 'end_time desc'
            ));
            foreach($promotion_goods as $k => $v){
                $promotion_goods[$k]['format_start_time']   =   date('Y-m-d H:i', $v['start_time']);
                $promotion_goods[$k]['format_end_time']     =   date('Y-m-d H:i', $v['end_time']);
                $promotion_goods[$k]['source']=1;
                $promotion_goods[$k]['goods']               =  $promotionGoodsMod->gePromotionGoods
                ($v['id'],$promotion_goods[$k]);;
            }
            $promotion_goods && $promotionGoods_arr[$day] = $promotion_goods;
        }
        //获取当天商品
        foreach($promotionGoods_arr as $k=>$v){
            foreach($v as $k1=>$v1){
                if($k == '今日'){
                    if($v1['end_time']<time()){
                        $promotionGoods_arr[$k][$k1]['status']='活动结束';//已经结束
                    }
                    if($v1['start_time']<=time()&&$v1['end_time']>=time() ){
                        $promotionGoods_arr[$k][$k1]['status']='马上抢购';//开始
                    }
                    if($v1['start_time']>time()){
                        $promotionGoods_arr[$k][$k1]['status']='即将开始';//未开始
                    }
                } else {
                    $promotionGoods_arr[$k][$k1]['status']='活动预告';
                }
            }
        }
        $prokeys = array_keys($promotionGoods_arr);

        foreach($promotionGoods_arr as $k=>$v){
            if(empty($v)){
                $promotionGoods_arr[$k]['display']=0;
            }else{
                foreach($v as $k1=>$v1){
                    if(empty($v1)){
                        $promotionGoods_arr[$k][$k1]['display']=0;
                    }else{
                        $promotionGoods_arr[$k][$k1]['display']=1;
                    }
                }
            }
        }
//        echo '<pre>';print_r($goods_arr);die;
        $spikeDisplay = 0;
        $promotionDisplay = 0;
        $display = 0;
        //没有秒杀商品
        if (empty($goods_arr) && !empty($promotionGoods_arr)) {
            $spikeDisplay = 1;
        }
        //没有促销商品
        if (empty($promotionGoods_arr) && !empty($goods_arr)) {
            $promotionDisplay = 1;
        }
        //秒杀和促销都没有
        if (empty($promotionGoods_arr) && empty($goods_arr)) {
            $display = 1;
        }
        //秒杀和促销都没有
        if (!empty($promotionGoods_arr) && empty($goods_arr)) {
            $display = 1;
        }
        $this->assign('spikeDisplay',$spikeDisplay);
        $this->assign('promotionDisplay',$promotionDisplay);
        $this->assign('display',$display);
        //背景图片
        $backgroundImg = unserialize($storeData['background_img']);
        foreach ($backgroundImg as $key => &$value) {
            if ($value['activity_id']) {
                $activityMod = &m('storeActivity');
                $activity = $activityMod->getRow($value['activity_id']);
                if ($activity['is_use'] == 2 || $value['begin_time'] > time() || $value['end_time'] < time()) {
                    $value['activity_id'] = 0;
                }

            }
        }
//        echo '<pre>';print_r($goods_arr);die;
        $this->assign('backgroundImg', $backgroundImg);
        $this->assign('app',APP);
        $this->assign('prokeys',$prokeys);
        $this->assign('first_day',$goods_arr[$keys[0]]);
        $this->assign('keys',$keys);
        $this->assign('activityData',$goods_arr);
        $this->assign('promotionData',$promotionGoods_arr);
        $this->assign('store_arr',$storeData['store_discount']);
        $this->assign('mrstoreid',$storeid );
        $this->assign('res', $res);
        $this->assign('countryId',$this->countryId);
        $this->assign('rtid',$_REQUEST['rtid']);
        $this->assign('symbol', $this->symbol);
        $this->display('goodsList/newIndex.html');
    }

    public function specDialog()
    {
        $storeGoods = &m('areaGood');
        $id = $_REQUEST['id'];
        $goods_id = $_REQUEST['goods_id'];
        /*  $storeid=$_REQUEST['storeid'];*/
        $lang = $_REQUEST['lang'];
        $source = $_REQUEST['source'];
        $cid = $_REQUEST['cid'];
        $number = $_REQUEST['number'];
        $cart = $_REQUEST['cart'];
        $order = $_REQUEST['order'];
        $latlon = $_REQUEST['latlon'];
        $number = $_REQUEST['number'];
        $cart = $_REQUEST['cart'];
        $order = $_REQUEST['order'];
        $storeArr = $_REQUEST['store_arr'];

        $this->assign('number', $number);
        $this->assign('cart', $cart);
        $this->assign('order', $order);
        $this->assign('store_arr', $storeArr);

        $goodinfo = $storeGoods->getLangInfo1($id, $lang);
        if ($source == 3) {
            $sql = " select ps.*,pg.goods_key as item_id,pg.goods_price,pg.discount_price,pg.limit_amount,ps.status  from " . DB_PREFIX . "promotion_sale as ps left join " . DB_PREFIX . "promotion_goods as pg on
                ps.id = pg.prom_id  where ps.`store_id` = $this->storeid and ps.`status` in (1,2) and ps.`mark` =1 and ps.id =$cid  and pg.goods_id=".$id;
            $arr = $storeGoods->querySql($sql);
            foreach ($arr as $k => $v) {
                $arr1 = explode('_', $v['item_id']);
            }
        }
        // 团购
        if ($source == 2) {
            $where2 = '  where  store_id = ' . $this->storeid . ' and  mark =1 and id=' . $cid;
            $sql2 = 'SELECT  goods_spec_key as item_id,group_goods_price,goods_price,group_goods_num,virtual_num,end_time,title as name,start_time
                FROM  ' . DB_PREFIX . 'goods_group_buy ' . $where2;
            $arr = $storeGoods->querySql($sql2);
            foreach ($arr as $v) {
                $arr1 = explode('_', $v['item_id']);
            }}
        $spec_img1 = $this->get_spec($goodinfo['goods_id'], $id, 2);

        if (!empty($arr1)) {
            foreach ($spec_img1 as $key => $value) {
                foreach ($value as $k => $v) {
                    if (!in_array($v['item_id'], $arr1))
                        unset($spec_img1[$key][$k]);
                }
            }
        }
        foreach($spec_img1 as $key1=>$value1){
            foreach($value1 as $k1=>$v1){
                $spec_img[$key1][]=$v1;
            }
        }
        $storeList = $this->getCountryStore($this->countryId,$goods_id,$latlon);
        $info=array('spec_img'=>$spec_img,'info'=>$goodinfo,'lang'=>$lang,'storeList'=>$storeList);
//echo '<pre>';print_r($spec_img['温度选择']);die;
        $this->assign('spec', $spec_img);
        $this->assign('goodInfo', $goodinfo);
        $this->assign('lang', $lang);
        $this->assign('storeList', $storeList);
        $this->display('goodsList/specDialog.html');
    }
}
