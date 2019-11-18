<?php

/**
 * 扫码支付
 * @author wanyan
 * @date 2017/07/19
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class PointwxNotifyApp extends BaseFrontApp
{

    private  $storeMod;
    private $userMod;
    private $pointLogMod;
    private $pointOrderMod;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        include ROOT_PATH."/includes/libraries/WxPaysdk/WxPay.Config.php";
        require_once  ROOT_PATH."/includes/libraries/WxPaysdk/WxPay.Api.php";
        require_once  ROOT_PATH."/includes/libraries/WxPaysdk/notifyReply.php";
        $this->pointOrderMod=&m('pointOrder');
        $this->userMod = &m('user');
        $this->pointLogMod = &m("pointLog");
        $this->storeMod = &m('store');
        $wxConfig = new  WxPayConfig();
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
    public function getWxInfo() {
        $storeid=!empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : '';
        $Tstore = '';
        $wxPay = array();
        $rs = $this->storeMod->getOne(array('cond' => "`id` = '{$storeid}'", 'fields' => "store_type,store_cate_id"));
        if ($rs['store_type'] == 1) {
            $Tstore = $storeid;
        } else {
            $info = $this->storeMod->getOne(array('cond' => "`store_cate_id` = '{$rs['store_cate_id']}' and `store_type` = 1", 'fields' => "id"));
            $Tstore = $info['id'];
        }

        $sql = "select pd.mkey,pd.key_name from " . DB_PREFIX . "pay as p left join " . DB_PREFIX . "pay_detail as pd on p.id = pd.pay_id where p.store_id = '{$Tstore}' and p.`code` = 'weixin' and p.is_use =1";
        $payInfo = $this->storeMod->querySql($sql);
        foreach ($payInfo as $k => $v) {
            $wxPay[$v['mkey']] = $v['key_name'];
        }
        return $wxPay;
    }


    // xml转换为数组
    public function xmlToArray($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }

    // 微信回调
    public function notify(){
        require_once  ROOT_PATH."/includes/libraries/WxPaysdk/log.php";
        $logHandler= new CLogFileHandler(ROOT_PATH."/app/logs/".date('Y-m-d').'.log');
        $log = Log::Init($logHandler, 15);
        $notify = new PayNotifyCallBack();
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        Log::DEBUG("wx_response_data:".$xml);
        $wx_response_data = $this->xmlToArray($xml);
        if($notify -> Queryorder($wx_response_data['transaction_id'])){
            if($wx_response_data['result_code'] == "SUCCESS" && $wx_response_data['return_code'] == "SUCCESS"){
                $rs  = $this->pointOrderMod->isExist($wx_response_data['out_trade_no']);
                $data =array(
                    'pay_sn' => $wx_response_data['transaction_id'],
                    'payment_code' => 'wxpay',
                    'pay_time' => strtotime($wx_response_data['time_end']),
                    'status' => 1
                );
                $res =$this->pointOrderMod->doEdit($rs['id'], $data);
                if($res){
                    //生成睿积分日志
                    $sql='SELECT point,username,phone FROM '.DB_PREFIX.'user WHERE is_kefu = 0 AND id = '.$rs['buyer_id'];
                    $info = $this->userMod->querySql($sql);
                    //添加首次充值积分判断 modify by lee
                    $this->achievePoint($rs['buyer_id'],$rs['amount'],$info[0]);
                    $note='充值'.$rs['point'].'睿积分';
                    $expend='-';
                    $this->addPointLog($info[0]['phone'],$note,$rs['buyer_id'],$rs['point'],$expend);
                }
                //$out_trade_no = '201803151611313930';
                //  更新用户睿积分
                $point=$info[0]['point']+$rs['point'];
                $userData= array(
                    "table" => "user",
                    'cond' => 'id = ' . $rs['buyer_id'],
                    'set' => "point ='".$point."'",
                );
                $detailRes=$this->userMod->doUpdate($userData);
                if($detailRes){
                    $notify->Handle(true);
                }

                }

            }
        }


    //生成睿积分充值日志
    public  function addPointLog($username,$note,$userid,$deposit,$expend = "-"){

        if(empty($this->accountName)){
            $accountName='--';
        }
        $logData = array(
            'operator' => $accountName,
            'username' => $username,
            'add_time' => time(),
            'deposit'=>$deposit,
            'expend'=>$expend,
            'note'=>$note,
            'userid'=>$userid
        );

        $this->pointLogMod->doInsert($logData);
    }

    /*
     * 判断充值送
     * @author lee
     * @date 2018-6-21 09:48:29
     */
    public function doGive($userid,$info){
        $pointMod = &m('point');
        $site = $pointMod->getOne(array("cond"=>"1=1"));
        if(empty($info)){
            $info = $this->userMod->getOne(array("cond"=>"id=".$userid));
        }
        if($site){
            //判断是否为第一次充值
           $res = $this->pointLogMod->getCount(array("cond"=>"userid=".$userid));
            if($res >= 1){
                $arr = array(
                    "msg" =>"充值送".$site['recharge'],
                    "point"=> $site['recharge']
                );
            }else{
                $arr = array(
                    "msg" =>"充值送".$site['first_recharge'],
                    "point"=> $site['first_recharge']
                );
            }
            $point = $info['point']+$arr['point'];
            $user_point = array(
                "point"=>$point
            );
            $this->addPointLog($info['username'],$arr['msg'],$userid,$arr['point']);
            $res = $this->userMod->doEdit($userid,$user_point);
        }
    }

    /**
     * 新版充值送
     * @param $userid
     * @param $info
     * @author tangp
     * @date 2018-10-09
     */
    public function achievePoint($userid,$amount,$info)
    {
        $pointMod = &m('point');
        $site = $pointMod->getOne(array("cond"=>"1=1"));
        if(empty($info)){
            $info = $this->userMod->getOne(array("cond"=>"id=".$userid));
        }
        if($site){
            $res = $this->pointLogMod->getCount(array("cond"=>"userid=".$userid));
            if($res >= 1){
                $rechargeMod = &m('recharge');
                $po = $rechargeMod->getPoint($amount);
                $point = $po+$info['point'];
            }else{
                if ($amount >= $site['charge']){
                    $po = $site['first_recharge'];
                }else{
                    $po = 0;
                }
//                $arr = array(
//                    "msg" =>"充值送".$site['first_recharge'],
//                    "point"=> $site['first_recharge']
//                );
                $point = $info['point']+$po;
            }
            $user_point = array(
                "point"=>$point
            );
//            $this->addPointLog($info['username'],$arr['msg'],$userid,$arr['point']);
            $res = $this->userMod->doEdit($userid,$user_point);
        }
    }




}