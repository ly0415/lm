<?php

/**
 * 扫码支付
 * @author wanyan
 * @date 2017/07/19
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class wxNotifyApp extends BaseWxApp {

    private $orderMod;
    private $orderDetailMod;
    private $storeMod;
    private $areaGoodMod;
    private $storeGoodItemPriceMod;
    private  $goodsSpecPriceMod;
    private $goodsMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        include ROOT_PATH . "/includes/libraries/WxPaysdk/WxPay.Config.php";
        require_once ROOT_PATH . "/includes/libraries/WxPaysdk/WxPay.Api.php";
        require_once ROOT_PATH . "/includes/libraries/WxPaysdk/notifyReply.php";
        $this->orderMod = &m('order');
        $this->orderDetailMod = &m('orderDetail');
        $this->storeMod = &m('store');
        $this->storeGoodItemPriceMod = &m('storeGoodItemPrice');
        $this->areaGoodMod = &m('areaGood');
        $this->goodsSpecPriceMod=&m('goodsSpecPrice');
        $this->goodsMod=&m('goods');
        $wxConfig = new WxPayConfig();
        $wxPayInfo = $this->getWxInfo();

//        $wxConfig::$APPID = "wx80d07d72079c04db";
//        $wxConfig::$MCHID = "1415524702";
//        $wxConfig::$KEY = "83iJNv8SsrqLTPpWKV4Z8JZElkN9aknb";
//        $wxConfig::$APPSECRET = "cb06dfe09354ada01688cea7173b3c45";
        $wxConfig::$APPID = trim($wxPayInfo['weixin_APPID']);
        $wxConfig::$MCHID = trim($wxPayInfo['weixin_account']);
        $wxConfig::$KEY = trim($wxPayInfo['weixin_KEY']);
        $wxConfig::$APPSECRET = trim($wxPayInfo['weixin_APPSECRET']);
	 
    }

    /**
     * @comment 获取总站点的微信支付配置信息
     * @wanyan
     * @date 2018/1/8
     */
    public function getWxInfo($store_id) {
        $storeId = $store_id;
        $Tstore = '';
        $wxPay = array();
        $rs = $this->storeMod->getOne(array('cond' => "`id` = '{$storeId}'", 'fields' => "store_type,store_cate_id"));
        if ($rs['store_type'] == 1) {
            $Tstore = $storeId;
        } else {
            $info = $this->storeMod->getOne(array('cond' => "`store_cate_id` = '{$rs['store_cate_id']}' and `store_type` = 1", 'fields' => "id"));
            $Tstore = $info['id'];
        }

        $sql = "select pd.mkey,pd.key_name from " . DB_PREFIX . "pay as p left join " . DB_PREFIX . "pay_detail as pd on p.id = pd.pay_id where p.store_id = '{$Tstore}' and p.`code` = 'weixin' and p.is_use =1";
        $payInfo = $this->storeMod->querySql($sql);
        foreach ($payInfo as $k => $v) {
            $wxPay[$v['mkey']] = $v['key_name'];
        }
        echo $this->storeId;
        return $wxPay;
    }

    // xml转换为数组
    public function xmlToArray($xml) {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }

    // 更新规格库存 和 无规格库存
    public function UpdateStock($out_trade_no){
        //  更新库存
        $sql = "SELECT sg.goods_id,sg.spec_key,sg.goods_num,sg.deduction,sg.good_id,sg.prom_id,sg.prom_type FROM ".
            DB_PREFIX."order as r LEFT JOIN ".
            DB_PREFIX."order_goods as sg ON  r.order_sn = sg.order_id WHERE sg.order_id = '{$out_trade_no}'";
        $orderRes = $this->areaGoodMod->querySql($sql);
        foreach ($orderRes as $k =>$v) {
            if($v['prom_type']==1){
                $spikeActiviesMod=&m('spikeActivies');
                $spikeActiviesGoodsMod=&m('spikeActiviesGoods');
                $activitySql="select id,goods_num from ".DB_PREFIX.'spike_goods where store_goods_id='.$v['goods_id'].' and spike_id='.$v['prom_id'];
                $spikeActiviesGoodsData=$spikeActiviesGoodsMod->querySql($activitySql);
                $activityNum=$spikeActiviesGoodsData[0]['goods_num']-$v['goods_num'];
                if($activityNum<=0){
                    $activityNum=0;
                }
                $activityArr=array(
                    'goods_num'=>$activityNum
                );
                $spikeActiviesGoodsMod->doEdit($spikeActiviesGoodsData[0]['id'], $activityArr);
            }
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


	 // 微信回调
    public function wxnotify() {
        require_once ROOT_PATH . "/includes/libraries/WxPaysdk/log.php";
        $logHandler = new CLogFileHandler(ROOT_PATH . "/app/logs/" . date('Y-m-d') . 'ws.log');
        $log = Log::Init($logHandler, 15);
        $notify = new PayNotifyCallBack();
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        Log::DEBUG("wx_response_data:" . $xml);
        $wx_response_data = $this->xmlToArray($xml);
	//获取区域支付信息
        $attach =$wx_response_data['attach'];
        if (strpos($attach, 'daifu')) {
            $array = explode(',', $attach);
            $daifu = 1;
            $store_id = $array[0];
        } else {
            $daifu = 0;
            $store_id = $attach;
        }
        $wxPayInfo = $this->getWxInfo($store_id);
        $wxConfig = new WxPayConfig();
        $wxConfig::$APPID = trim($wxPayInfo['weixin_APPID']);
        $wxConfig::$MCHID = trim($wxPayInfo['weixin_account']);
        $wxConfig::$KEY = trim($wxPayInfo['weixin_KEY']);
        $wxConfig::$APPSECRET = trim($wxPayInfo['weixin_APPSECRET']);
        $wx_response_data['out_trade_no'] = substr($wx_response_data['out_trade_no'], 0, -5);
	    if($notify -> Queryorder($wx_response_data['transaction_id'])){
            if($wx_response_data['result_code'] == "SUCCESS" && $wx_response_data['return_code'] == "SUCCESS"){
                $orderMod = &m('order');
                $childOrderData = $orderMod->getData(array("cond" => "`order_sn` like '{$wx_response_data['out_trade_no']}%' and `mark` = 1"));
                foreach($childOrderData as $key =>$val) {
                    $orderSn = $val['order_sn'];
                    $store_id = $val['store_id'];
                    $data = array(
                        'pay_sn' => $wx_response_data['transaction_id'],
                        'payment_code' => 'wxpay',
                        'payment_time' => strtotime($wx_response_data['time_end']),
                        'order_state' => 20, //已付款状态
                        'Appoint' => 1, //1未被指定 2被指定
                        'Appoint_store_id' => $store_id, //被指定的站点
                        'install_time' => strtotime($wx_response_data['time_end'])
                    ); //区域配送安装完成时间
                    $cond = array(
                        'order_sn' => $orderSn
                    );
                    $fxOrderMod = &m('fxOrder');
                    $fxOrderMod->addFxOrderByOrderSn($orderSn,2);
                    $res = $orderMod->doEditSpec($cond, $data);
                    $orderMod->update_pay_time($store_id, $orderSn, $wx_response_data['transaction_id'], 2, 20, $daifu);
                    $this->updateStock($orderSn);
                }

                    $notify->Handle(true);


            }
      }
  }
}
