<?php
include_once 'Config.php';
include_once 'Utility.php';

/**
 * 聚合支付
 * User: jh
 * Date: 2019/3/1
 * Time: 16:18
 */
class Tuikuan
{
    private $TxnType = '';//交易类型码
    private $Sign = '';//签名域
    private $RefundTxnNo = '';//退款流水号，由收银系统生成，每笔退货唯一。 如果一笔消费交易被多次退款， OrgTxnNo 相同， RefundTxnNo 必须是不同的。
    private $OrgMerchantID = '';//原消费交易的MerchantID (如果需要支持跨店退款,本字段必填)
    private $OrgTxnNo = '';//原消费交易的MerchantTxnNo. (OrgTxnNo,OrgPlatformTxnNo两个必须选填其一)
    private $OrgPlatformTxnNo = '';//原消费交易平台交易流水号PlatformTxnNo (OrgTxnNo,OrgPlatformTxnNo两个必须选填其一)
    private $RefundAmt = 0.00;//退款金额，格式: ###############.## (元)
    private $CurrencyCode = '';//货币代码，例如人民币（CNY）:156
    private $TxnReqTime = '';//请求时间，格式： YYYY/MM/DD HH24:MM:DD
    private $CashierID = '';//收银员ID，由收银系统定义
    private $TerminalID = '';//终端ID，由平台分配或收银系统自定义
    private $TxnLongDesc = '';//交易明细信息
    private $TxnShortDesc = '';//交易简述，可显示在客户的手机上
    private $ItemDetail = '';//订单商品明细列表，json数组
    private $RefundReason = '';//退货原因
    private $Pairs = array();//参数集合

    public function __construct()
    {
        $this->Pairs['CurrencyCode'] = Config::$CNY;//货币代码默认是人民币，如果是其他币种，请调用setCurrencyCode方法修改
    }

    /**
     * @param string $RefundTxnNo
     */
    public function setRefundTxnNo($RefundTxnNo)
    {
        $this->RefundTxnNo = $RefundTxnNo;
        $this->Pairs['RefundTxnNo'] = $RefundTxnNo;
        return $this;
    }

    /**
     * @param string $OrgMerchantID
     */
    public function setOrgMerchantID($OrgMerchantID)
    {
        $this->OrgMerchantID = $OrgMerchantID;
        $this->Pairs['OrgMerchantID'] = $OrgMerchantID;
        return $this;
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
     * @param float $RefundAmt
     */
    public function setRefundAmt($RefundAmt)
    {
        $this->RefundAmt = $RefundAmt;
        $this->Pairs['RefundAmt'] = $RefundAmt;
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
     * @param string $RefundReason
     */
    public function setRefundReason($RefundReason)
    {
        $this->RefundReason = $RefundReason;
        $this->Pairs['RefundReason'] = $RefundReason;
        return $this;
    }

    public function index()
    {
        Utility::noEmpty($this->Pairs);//去除空值
        $this->Pairs['PartnerID'] = Config::$PartnerID;
        $this->Pairs['MerchantID'] = Config::$MerchantID;
        $this->Pairs['TxnType'] = Config::$Tuikuan_Code;
        $this->Sign = Utility::getSign($this->Pairs);//获取签名域
        $this->Pairs['Sign'] = $this->Sign;
        $res_json = Utility::doPost(Config::$ApiUrlUnderline, json_encode($this->Pairs));//请求接口
        $res_arr = json_decode($res_json, true);
        if (isset($res_arr['RespCode']) && $res_arr['RespCode'] == Config::$RespCode_Success) {
            return true;
        } else {
            writeLog('ERROR!--URL:' . Config::$ApiUrlUnderline . '--INFO:' . json_encode($this->Pairs), 'shijicloud');
            if (isset($res_arr['RespCode'])) {
                writeLog('ERROR!--URL:' . Config::$ApiUrlUnderline . '--RESPONSE:' . $res_json, 'shijicloud');
            }
            return false;
        }
//        writeLog($this->Pairs);
//        writeLog($res_json);
//        writeLog(json_decode($res_json, true));
    }
}