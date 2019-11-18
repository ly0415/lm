<?php

/**
 * 公共参数配置文件
 * User: jh
 * Date: 2019/3/1
 * Time: 16:38
 */
class Config
{
    //商户自定义配置参数
    public static $PartnerID = '1048';//由平台分配给每个系统接入合作方（系统供应商及系统）
    public static $MerchantID = '100110000044036';//商户ID平台分配
    public static $ChannelID = '80';//支付渠道ID
    public static $Key = 'm8vuja11bnsb7z7lmyr7';//签名秘钥


    //平台配置参数（用户无需更改）
    public static $ApiUrlUnderline = 'https://ss-platform01.shijicloud.com/api-v16';
    public static $ApiUrlOnline = 'https://ss-platform01.shijicloud.com/webpay/api';

    //交易类型配置参数（用户无需更改）
    public static $Saoma_Code = '1';//扫码支付（客户扫商家生成的二维码）
    public static $Shuaka_Code = '2';//刷卡支付（商家扫客户付款码）
    public static $Chexiao_Code = '3';//撤销
    public static $Tuikuan_Code = '4';//退款
    public static $Paycheck_Code = '5';//消费结果查询
    public static $Gongzhh_Code = '9';//公众号/口碑支付（公众号/口碑等应用内支付）
    public static $Shoujiweb_Code = '10';//手机网页支付（移动端手机浏览器）
    public static $Xiaochx_Code = '24';//微信小程序支付
    public static $Diannaoweb_Code = '60';//电脑网页支付(二维码扫码)
    public static $Juhepay_Code = '61';//线上聚合支付

    //货币代码配置参数（用户无需更改）
    public static $CNY = '156';//人民币

    //返回码配置参数（用户无需更改）
    public static $RespCode_Success = '00';//成功
    public static $RespCode_UnknowStatus = 'X0';//状态未知，请调用查询交易，查询订单状态
    public static $RespCode_Error = 'X1';//异常，使用相同参数发起重试
    public static $RespCode_Fail = 'XX';//失败

    //支付渠道配置参数（用户无需更改）
    public static $ChannelID_WX = '2';//微信
    public static $ChannelID_ZFB = '6';//支付宝
}