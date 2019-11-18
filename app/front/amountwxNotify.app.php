<?php

/**
 * 扫码支付
 * @author wanyan
 * @date 2017/07/19
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class AmountwxNotifyApp extends BaseFrontApp
{

    private  $storeMod;
    private $userMod;
    private $pointLogMod;
    private $amountLogMod;
    private $rechargeAmountMod;


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
        $this->amountLogMod=&m('amountLog');
        $this->rechargeAmountMod = &m('rechargeAmount');
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
                $sql = "SELECT al.*,rp.integral as point,rp.s_money from bs_amount_log as al 
                        left join bs_recharge_point as rp on al.point_rule_id = rp.id where al.order_sn = '" . $wx_response_data['out_trade_no'] . "'";
                $rs  = $this->amountLogMod->querySql($sql);
                $rs = $rs[0];
                $amount=$rs['c_money']+$rs['s_money'];
                $result=$this->updateAmount($amount,$rs['point_rule_id'],$rs['add_user'],$rs['point'],$wx_response_data['out_trade_no']);
                //修改记录状态
                $data =array(
                    'pay_time' => strtotime($wx_response_data['time_end']),
                    'status' => 2
                );
                $res =$this->amountLogMod->doEdit($rs['id'], $data);
                if($res){
                    //发劵给用户
                    $userCouponMod=&m('userCoupon');
                    $userCouponMod->addCouponByRecharge($rs['add_user'],$rs['point_rule_id'],$rs['id'],$wx_response_data['out_trade_no']);
                    //生成积分日志
                    $sql='SELECT point,username,phone FROM '.DB_PREFIX.'user WHERE is_kefu = 0 AND id = '.$rs['add_user'];
                    $info = $this->userMod->querySql($sql);
                    $note='余额充值赠送'.$rs['point'].'睿积分';
                    $expend='-';
                    $this->addPointLog($info[0]['phone'],$note,$rs['add_user'],$rs['point'],$expend);
                    if($result){
                        $notify->Handle(true);
                    }
                }
            }

        }
    }



    //更新用户的余额和睿积分抵扣规则
    public function  updateAmount($amount,$rechargeId,$userId,$point,$orderSn){
        $rs  = $this->amountLogMod->isExist($orderSn);
        if($rs['status']==1) {
            $userData = $this->userMod->getOne(array('cond' => "`id` = '{$userId} and mark=1'", 'fields' => 'amount,point,recharge_id'));
            $ruleSql = "SELECT id,c_money,s_money,integral,percent FROM " . DB_PREFIX . 'recharge_point WHERE mark=1 and id=' . $rechargeId;
            $newruleData = $this->rechargeAmountMod->querySql($ruleSql);
            $Sql = "SELECT id,c_money,s_money,integral,percent FROM " . DB_PREFIX . 'recharge_point WHERE mark=1 and id=' . $userData['recharge_id'];
            $oldruleData = $this->rechargeAmountMod->querySql($Sql);
            if ($oldruleData[0]['percent'] > $newruleData[0]['percent']) {
                $rechargeId = $oldruleData[0]['id'];
            }
            $data = array(
                'recharge_id' => $rechargeId,
                'amount' => $userData['amount'] + $amount,
                'point' => $userData['point'] + $point
            );
            $res = $this->userMod->doEdit($userId, $data);
            return $res;
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





}