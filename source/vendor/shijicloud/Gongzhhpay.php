<?php
include_once 'Config.php';
include_once 'Utility.php';

/**
 * 公众号/口碑支付
 * User: jh
 * Date: 2019/3/1
 * Time: 16:18
 */
class Gongzhhpay
{
    private $TxnType = '';//交易类型码
    private $Sign = '';//签名域
    private $MerchantTxnNo = '';//商户交易流水号，由商户收银系统生成，每笔交易必须唯一。如果交易与之前交易的流水号相同，系统将拒绝。
    private $MerchantOrderNo = '';//商户订单号，商户系统产生的订单号 (例如:购物订单号、保险单号)
    private $TxnAmt = 0.00;//交易金额，格式： ###############.## 单位：元
    private $CurrencyCode = '';//货币代码，例如人民币（CNY）:156，默认：156 人民币
    private $PermitDisctAmt = 0.00;//允许参与折扣的金额
    private $TxnLongDesc = '';//交易明细信息
    private $TxnShortDesc = '';//交易简述
    private $ItemDetail = '';//订单商品明细列表，json数组
    private $NotifyUrl = '';//商户接收异步通知URL 只支持端口为80或443的URL
    private $RedirectUrl = '';//支付结果页面返回通知URL
    private $Pairs = array();//参数集合

    public function __construct()
    {
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
     * @param float $PermitDisctAmt
     */
    public function setPermitDisctAmt($PermitDisctAmt)
    {
        $this->PermitDisctAmt = $PermitDisctAmt;
        $this->Pairs['PermitDisctAmt'] = $PermitDisctAmt;
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

    /**
     * @param string $RedirectUrl
     */
    public function setRedirectUrl($RedirectUrl)
    {
        $this->RedirectUrl = $RedirectUrl;
        $this->Pairs['RedirectUrl'] = $RedirectUrl;
        return $this;
    }

    public function index()
    {
        Utility::noEmpty($this->Pairs);//去除空值
        $this->Pairs['PartnerID'] = Config::$PartnerID;
        $this->Pairs['MerchantID'] = Config::$MerchantID;
        $this->Pairs['TxnType'] = Config::$Gongzhh_Code;
        $this->Sign = Utility::getSign($this->Pairs);//获取签名域
        $this->Pairs['Sign'] = $this->Sign;
        $res = Utility::doPost(Config::$ApiUrlOnline, $this->Pairs);//请求接口
        writeLog($this->Pairs);
        writeLog($res);
        return $res;
    }
}