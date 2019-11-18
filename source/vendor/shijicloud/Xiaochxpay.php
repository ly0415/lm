<?php
include_once 'Config.php';
include_once 'Utility.php';

/**
 * 微信小程序支付
 * User: jh
 * Date: 2019/3/1
 * Time: 16:18
 */
class Xiaochxpay
{
    private $TxnType = '';//交易类型码
    private $Sign = '';//签名域
    private $ChannelID = '';//支付渠道ID
    private $MerchantTxnNo = '';//商户交易流水号，由商户收银系统生成，每笔交易必须唯一。 如果交易与之前交易的流水号相同，系统将拒绝。
    private $MerchantOrderNo = '';//商户订单号，商户系统产生的订单号 (例如:购物订单号、保险单号)
    private $TxnAmt = 0.00;//交易金额，格式： ###############.## 单位：元
    private $CurrencyCode = '';//货币代码，例如人民币（CNY）:156
    private $TxnReqTime = '';//请求时间，格式： YYYY/MM/DD HH24:MM:DD
    private $PermitDisctAmt = '';//允许参与折扣的金额，格式同TxnAmt。
    private $PayerRealID = '';//用户标识 小程序中用户openid
    private $CashierID = '';//收银员ID，由收银系统定义。
    private $TerminalID = '';//终端ID，由平台分配或收银系统自定义。
    private $TxnLongDesc = '';//交易明细信息。
    private $TxnShortDesc = '';//交易简述，可显示在客户的手机上。
    private $ItemDetail = '';//订单商品明细列表
    private $NotifyUrl = '';//异步通知地址URL URL的端口必须是 80或者443
    private $Pairs = array();//参数集合

    public function __construct()
    {
        $this->Pairs['CurrencyCode'] = Config::$CNY;//货币代码默认是人民币，如果是其他币种，请调用setCurrencyCode方法修改
    }

    /**
     * @param string $MerchantTxnNo
     */
    public function setMerchantTxnNo($MerchantTxnNo)
    {
        $this->MerchantTxnNo = $MerchantTxnNo;
        $this->Pairs['MerchantTxnNo'] = $MerchantTxnNo;
        return $this;
    }

    /**
     * @param string $MerchantOrderNo
     */
    public function setMerchantOrderNo($MerchantOrderNo)
    {
        $this->MerchantOrderNo = $MerchantOrderNo;
        $this->Pairs['MerchantOrderNo'] = $MerchantOrderNo;
        return $this;
    }

    /**
     * @param float $TxnAmt
     */
    public function setTxnAmt($TxnAmt)
    {
        $this->TxnAmt = $TxnAmt;
        $this->Pairs['TxnAmt'] = $TxnAmt;
        return $this;
    }

    /**
     * @param string $CurrencyCode
     */
    public function setCurrencyCode($CurrencyCode)
    {
        $this->CurrencyCode = $CurrencyCode;
        $this->Pairs['CurrencyCode'] = $CurrencyCode;
        return $this;
    }

    /**
     * @param string $TxnReqTime
     */
    public function setTxnReqTime($TxnReqTime)
    {
        $this->TxnReqTime = $TxnReqTime;
        $this->Pairs['TxnReqTime'] = $TxnReqTime;
        return $this;
    }

    /**
     * @param string $PermitDisctAmt
     */
    public function setPermitDisctAmt($PermitDisctAmt)
    {
        $this->PermitDisctAmt = $PermitDisctAmt;
        $this->Pairs['PermitDisctAmt'] = $PermitDisctAmt;
        return $this;
    }

    /**
     * @param string $PayerRealID
     */
    public function setPayerRealID($PayerRealID)
    {
        $this->PayerRealID = $PayerRealID;
        $this->Pairs['PayerRealID'] = $PayerRealID;
        return $this;
    }

    /**
     * @param string $CashierID
     */
    public function setCashierID($CashierID)
    {
        $this->CashierID = $CashierID;
        $this->Pairs['CashierID'] = $CashierID;
        return $this;
    }

    /**
     * @param string $TerminalID
     */
    public function setTerminalID($TerminalID)
    {
        $this->TerminalID = $TerminalID;
        $this->Pairs['TerminalID'] = $TerminalID;
        return $this;
    }

    /**
     * @param string $TxnLongDesc
     */
    public function setTxnLongDesc($TxnLongDesc)
    {
        $this->TxnLongDesc = $TxnLongDesc;
        $this->Pairs['TxnLongDesc'] = $TxnLongDesc;
        return $this;
    }

    /**
     * @param string $TxnShortDesc
     */
    public function setTxnShortDesc($TxnShortDesc)
    {
        $this->TxnShortDesc = $TxnShortDesc;
        $this->Pairs['TxnShortDesc'] = $TxnShortDesc;
        return $this;
    }

    /**
     * @param string $NotifyUrl
     */
    public function setNotifyUrl($NotifyUrl)
    {
        $this->NotifyUrl = $NotifyUrl;
        $this->Pairs['NotifyUrl'] = $NotifyUrl;
        return $this;
    }

    public function index()
    {
        Utility::noEmpty($this->Pairs);//去除空值
        $this->Pairs['PartnerID'] = Config::$PartnerID;
        $this->Pairs['MerchantID'] = Config::$MerchantID;
        $this->Pairs['ChannelID'] = Config::$ChannelID_WX;
        $this->Pairs['TxnType'] = Config::$Xiaochx_Code;
        $this->Sign = Utility::getSign($this->Pairs);//获取签名域
        $this->Pairs['Sign'] = $this->Sign;
        $res_json = Utility::doPost(Config::$ApiUrlUnderline, json_encode($this->Pairs));//请求接口
        $res_arr = json_decode($res_json, true);
        if (isset($res_arr['RespCode']) && $res_arr['RespCode'] == Config::$RespCode_Success) {
            return $res_arr;
        } else {
            writeLog('ERROR!--URL:' . Config::$ApiUrlUnderline . '--INFO:' . json_encode($this->Pairs), 'shijicloud');
            if (isset($res_arr['RespCode'])) {
                writeLog('ERROR!--URL:' . Config::$ApiUrlUnderline . '--RESPONSE:' . $res_json, 'shijicloud');
            }
            return false;
        }
    }
}