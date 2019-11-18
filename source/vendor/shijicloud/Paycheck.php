<?php
include_once 'Config.php';
include_once 'Utility.php';

/**
 * 消费结果查询
 * User: jh
 * Date: 2019/3/1
 * Time: 16:18
 */
class Paycheck
{
    private $TxnType = '';//交易类型码
    private $Sign = '';//签名域
    private $OrgTxnNo = '';//原消费交易的MerchantTxnNo. (OrgTxnNo,OrgPlatformTxnNo两个必须选填其一)
    private $OrgPlatformTxnNo = '';//原消费交易平台交易流水号PlatformTxnNo (OrgTxnNo,OrgPlatformTxnNo两个必须选填其一)
    private $TxnAmt = '';//交易金额，格式： ###############.## 单位：元
    private $CurrencyCode = '';//货币代码，例如人民币（CNY）:156
    private $TxnReqTime = '';//请求时间，格式： YYYY/MM/DD HH24:MM:DD
    private $CashierID = '';//收银员ID，由收银系统定义
    private $TerminalID = '';//终端ID，由平台分配或收银系统自定义
    private $Pairs = array();//参数集合

    public function __construct()
    {
        $this->Pairs['CurrencyCode'] = Config::$CNY;//货币代码默认是人民币，如果是其他币种，请调用setCurrencyCode方法修改
    }

    /**
     * @param string $OrgTxnNo
     */
    public function setOrgTxnNo($OrgTxnNo)
    {
        $this->OrgTxnNo = $OrgTxnNo;
        $this->Pairs['OrgTxnNo'] = $OrgTxnNo;
        return $this;
    }

    /**
     * @param string $OrgPlatformTxnNo
     */
    public function setOrgPlatformTxnNo($OrgPlatformTxnNo)
    {
        $this->OrgPlatformTxnNo = $OrgPlatformTxnNo;
        $this->Pairs['OrgPlatformTxnNo'] = $OrgPlatformTxnNo;
        return $this;
    }

    /**
     * @param string $TxnAmt
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

    public function index()
    {
        Utility::noEmpty($this->Pairs);//去除空值
        $this->Pairs['PartnerID'] = Config::$PartnerID;
        $this->Pairs['MerchantID'] = Config::$MerchantID;
        $this->Pairs['TxnType'] = Config::$Paycheck_Code;
        $this->Sign = Utility::getSign($this->Pairs);//获取签名域
        $this->Pairs['Sign'] = $this->Sign;
        $res_json = Utility::doPost(Config::$ApiUrlUnderline, json_encode($this->Pairs));//请求接口
        writeLog($this->Pairs);
        writeLog(json_encode($this->Pairs));
        writeLog($res_json);
        $res_arr = json_decode($res_json, true);
        if (isset($res_arr['RespCode'])) {
            return $res_arr;
        } else {
            return false;
        }
//        writeLog($this->Pairs);
//        writeLog($res_json);
//        writeLog(json_decode($res_json, true));
    }
}