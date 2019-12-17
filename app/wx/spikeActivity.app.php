<?php
/**
 * 秒杀
 * @author tangp
 * @date 2018-12-18
 */
class SpikeActivityApp extends BaseWxApp
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 秒杀商品展示
     * @author tangp
     * @date 2018-12-18
     */
    public function getSkillGoods(){
        $spikeActivityMod = &m('spikeActivity');
        $spikeActiviesGoodsMod =& m('spikeActiviesGoods');
        $time        = time();
        //一周每天时间段以及对应秒杀商品
        $time_arr  = $goods_arr = array();
        $init_time = strtotime(date('Y-m-d', $time));
        for($i=1;$i<8;$i++){
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
                'cond'      => ' mark = 1 AND (start_time BETWEEN '. $start_time .' AND '.$end_time .' OR end_time BETWEEN '. $start_time .' AND '.$end_time .' OR ( start_time < '. $start_time .' AND  end_time > '.$end_time .'))'
            ));
            foreach($skill_goods as $k=>$v){
                $skill_goods[$k]['goods']=$spikeActiviesGoodsMod->getSpikeGoods($v['id']);
            }
            $skill_goods && $goods_arr[$day] = $skill_goods;
        }
        foreach($goods_arr as $k=>$v){
            foreach($v as $k1=>$v1){
                if($v1['end_time']<time()){
                    $goods_arr[$k][$k1]['status']=0;//已经结束
                }
                if($v1['start_time']<time()&&$v1['end_time']>time()){
                    $goods_arr[$k][$k1]['status']=1;//开始
                }
                if($v1['start_time']>time()){
                    $goods_arr[$k][$k1]['status']=2;//未开始
                }
            }
        }

        $keys = array_keys($goods_arr);
//        echo '<pre>';var_dump($goods_arr);
        $this->assign('keys',$keys);
        $this->display('spike/index.html');
    }

    /**
     * 秒杀商品详情页
     * @author tangp
     * @date 2018-12-19
     */
    public function goodsDetail()
    {
        $id = !empty($_REQUEST['store_goods_id']) ? $_REQUEST['store_goods_id'] : 7094;//store_goods_id
        $activityId = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 89;//活动id
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->storeid;//区域店铺id
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->langid;//语言id

        //判断是否登录
        if (!isset($_SESSION['userId'])) {
            header('Location: wx.php?app=user&act=loginOuth&back_app=' . APP . '&back_act=' . ACT . '&storeid=' . $this->storeid . '&lang=' . $this->langid);
        }

        $goodsImgMod = &m('goodsImg');
        $storeGoodsMod = &m('storeGoods');
        $storeGoods = &m('areaGood');
        $spikeActiviesGoodsMod = &m('spikeActiviesGoods');
        $spikeActivityMod = &m('spikeActivity');
        $goodAttrMod = &m('goodsAttriInfo');
        $sql = "SELECT goods_id FROM bs_store_goods WHERE id = {$id}";
        $res = $storeGoodsMod->querySql($sql);
        $img_arr = $goodsImgMod->getData(array('cond'=>"goods_id=".$res[0]['goods_id']));//拿轮播图
        $sql_test = 'select * from bs_spike_activity where id = '. $activityId;
        $data_test = $spikeActivityMod->querySql($sql_test);
        $end = date('Y-m-d H:i:s',$data_test[0]['end_time']);
        $end_time= $data_test[0]['end_time'];
        $start_time = $data_test[0]['start_time'];
        $time = time();
        $goods_name = $spikeActiviesGoodsMod->getData(array('cond'=>"store_goods_id={$id} and mark=1"));
        $goods_key_name = explode(':',$goods_name[0]['goods_key_name']);
        foreach ($goods_key_name as $key => $value){
            if (!$value){
                unset($goods_key_name[$key]);
            }
        }
        $spec_arrs = array_values($goods_key_name);
        $info = $storeGoods->getLangInfo($id,$this->langid);

        $attr_arr = $goodAttrMod->getLangData($info['goods_id'],$this->langid);
        $attr = $goods_name[0]['goods_key_name'];
        $rand = $this->buildNo(1);
        $orderNo = date('YmdHis') . $rand[0];
        $data=array(
            'orderNo'=>$orderNo,
            'buyerId'=>$this->userId
        );

        $this->assign('activityGoodsId',$goods_name[0]['id']);
        $this->assign('orderSn',$orderNo);
        $this->assign('end',$end);
        $this->assign('id',$id);
        $this->assign('end_time',$end_time);
        $this->assign('start_time',$start_time);
        $this->assign('time',$time);
        $this->assign('attr',$attr);
        $this->assign('store_id',$store_id);
        $this->assign('langid',$lang_id);
        $this->assign('source',1);
        $this->assign('spec_arrs',$spec_arrs);
        $this->assign('activityId',$activityId);
        $this->assign('symbol',$this->symbol);
        $this->assign('discount_price',$goods_name[0]['discount_price']);
        $this->assign('attr_arr',$attr_arr);
        $this->assign('goods_price',$goods_name[0]['goods_price']);
        $this->assign('goods_name',$goods_name[0]['goods_name']);
        $this->assign('goods_num',$goods_name[0]['goods_num']);
        $this->assign('limit_num',$goods_name[0]['limit_num']);
        $this->assign('original_img',$info['original_img']);
        $this->assign('img_arr',$img_arr);
        $this->assign('info',$info);
        $this->assign('user_id',$this->userId);
        $this->display('spike/spikeGoodsDetails.html');
    }
    /**
     * 检测购买数量有没有超过限购
     * @author
     * @date 2018-12-20
     */
    public function getBuyNums()
    {
        $activityGoodsId = $_REQUEST['activityGoodsId'];
        $num = $_REQUEST['num'];
        $id = $_REQUEST['id'];
        $activityId = $_REQUEST['activityId'];
        $userId = $this->userId;
        $source = $_REQUEST['source'];
        $spikeActiviesGoodsMod = &m('spikeActiviesGoods');
        $orderGoodsMod = &m('orderGoods');
        $storeGoodsMod = &m('storeGoods');
        $storeGoodsSpecPriceMod = &m('storeGoodsSpecPrice');
        $sql = "select * from bs_spike_goods where id=".$activityGoodsId;
        $res = $spikeActiviesGoodsMod->querySql($sql);
        //限购判断
        if ($num > $res[0]['limit_num']){
            $this->setData(array(),0,'你选择的数量大于该商品的限购数量！');
        }
//        $nums = $orderGoodsMod->getActivityOrderNum($source,$activityId,$id,$userId);
        if (!empty($userId)){
            $sql4 = "select sum(goods_num) as total from bs_order_goods where prom_type={$source} and prom_id={$activityId} and goods_id={$id} and buyer_id={$userId} ";
            $ac = $orderGoodsMod->querySql($sql4);
            $nums = $ac[0]['total'] ?: 0;
            //购买限购
            if ($res[0]['limit_num'] - $nums < $num){
                $this->setData(array(),0,"限购{$res[0]['limit_num']}件");
            }
        }
        //查库存足不足
        if (empty($res[0]['goods_key'])  && empty($res[0]['goods_key_name'])){
            $sql2 = "SELECT goods_storage FROM bs_store_goods WHERE id=".$id;//无规格的查库存
            $goodsInfo = $storeGoodsMod->querySql($sql2);
            if ($goodsInfo[0]['goods_storage'] == 0){
                $this->setData(array(),0,'该商品的库存不足！');
            }
            if ($num > $goodsInfo[0]['goods_storage']){
                $this->setData(array(),0,'该商品的库存不足！');
            }
        }else{
//            $sql3 = "SELECT goods_storage FROM bs_store_goods_spec_price WHERE store_goods_id={$id} AND `key`='{$res[0]['goods_key']}'";//有规格查库存
            $goods_key = $res[0]['goods_key'];
            $info = $storeGoodsSpecPriceMod->getOne(array('cond' =>"store_goods_id = '{$id}' and `key` ='{$goods_key}'"));

            if ($info['goods_storage'] == 0){
                $this->setData(array(),0,'该商品的库存不足！');
            }
            if ($num > $info['goods_storage']){
                $this->setData(array(),0,'该商品的库存不足！');
            }
        }


        $this->setData(array(),1,'');
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

}