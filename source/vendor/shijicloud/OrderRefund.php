<?php
include_once 'Config.php';
include_once 'Utility.php';

/**
  * 中行退款
  * @author: luffy
  * @date  : 2019-10-31
  */
class OrderRefund{

    private $Sign = '';             //签名域
    private $Pairs = array();       //参数集合

    public function __construct(){
        $this->Pairs['CurrencyCode']        = Config::$CNY;    //货币代码默认是人民币，如果是其他币种，请调用setCurrencyCode方法修改
    }

    /**
     * 中行退款
     * @author: luffy
     * @date  : 2019-10-31
     */
    public function orderRefund($order_sn){
        $this->Pairs['OrgTxnNo']            = $order_sn[0];
        $this->Pairs['RefundTxnNo']         = $order_sn[1];
        $this->Pairs['PlatformTxnNo']       = $order_sn[2];
        $this->Pairs['RefundAmt']           = $order_sn[3];
        $this->Pairs['MerchantID']          = getMerchantID($order_sn[4]);
        $this->Pairs['TxnReqTime']          = date('Y/m/d H:i:s');  //请求时间，格式： YYYY/MM/DD HH24:MM:DD
        return $this;
    }

    /**
     * 查询订单信息
     * @author: luffy
     * @date  : 2019-10-31
     */
    public function index(){
        Utility::noEmpty($this->Pairs);//去除空值
        $this->Pairs['PartnerID']   = Config::$PartnerID;
        $this->Pairs['TxnType']     = Config::$Tuikuan_Code;
        $this->Pairs['ChannelID']   = 80;
        $this->Sign = Utility::getSign($this->Pairs);//获取签名域
        $this->Pairs['Sign'] = $this->Sign;
        $res_json = Utility::doPost(Config::$ApiUrlUnderline, json_encode($this->Pairs));//请求接口
        $res_arr = json_decode($res_json, true);
        if (isset($res_arr['RespCode']) && $res_arr['RespCode'] == Config::$RespCode_Success) {
            return true;
        } else {
            return false;
        }
    }
}