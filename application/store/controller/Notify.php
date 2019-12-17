<?php

namespace app\store\controller;

use think\Db;
use think\Config;
use think\Controller;
use app\task\model\Order;
use app\common\enum\order\PayType as PayTypeEnum;

/**
 * 支付成功异步通知接口
 * Class Notify
 * @package app\task\controller
 */
class Notify extends Controller
{

    /**
     * 支付成功异步通知
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-06
     * Time: 09:50
     */
    public function notifyUrl()
    {


        require VENDOR_PATH.'/alipay/pagepay/service/AlipayTradeService.php';
//        $data =  array (
//            'gmt_create' => '2019-09-06 18:27:14',
//            'charset' => 'UTF-8',
//            'gmt_payment' => '2019-09-06 18:27:24',
//            'notify_time' => '2019-09-06 18:27:25',
//            'subject' => '艾美睿零售',
//            'sign' => 'jmqtCXzwCoXXTOtEksHvLmnkMgXhy9jE22Z3jn2+7Ot5U9f4qPGwHx6xggFvVAuo3S1gdIwfbeAipxg2uG8Up8KIYJMUb5HwaLpgPOlP5HjVwwLejtQAaYi7m7r+e1xlV7tZpb9IONzmKsXtfAkhM7/cuiczozrEi6NPafWsP/xsNGRjrZm3l8yxpGkm+3hzrOxS5CZzKWFYOe4ANks3jlVPhvxkU6Cox64I5oZPq9FxD01Fp5eJkg8/E07mD97sDHYpc80GcsZRPWhc3wP6V+4emtoLUOE1tesBESc6HOLYvRpNMtUylDWxXhCAIp/X5zFFtRAHuIcgyAWncotpCg==',
//            'buyer_id' => '2088012631449408',
//            'body' => '订单支付',
//            'invoice_amount' => '0.01',
//            'version' => '1.0',
//            'notify_id' => '2019090600222182724049401055612958',
//            'fund_bill_list' => '[{"amount":"0.01","fundChannel":"ALIPAYACCOUNT"}]',
//            'notify_type' => 'trade_status_sync',
//            'out_trade_no' => '201907311003088121',
//            'total_amount' => '0.01',
//            'trade_status' => 'TRADE_SUCCESS',
//            'trade_no' => '2019090622001449401000681960',
//            'auth_app_id' => '2017011905254523',
//            'receipt_amount' => '0.01',
//            'point_amount' => '0.00',
//            'buyer_pay_amount' => '0.01',
//            'app_id' => '2017011905254523',
//            'sign_type' => 'RSA2',
//            'seller_id' => '2088521547016083',
//        );

        $data = $_POST;
        $alipaySevice = new \AlipayTradeService(Config::get('alipay'));
        $alipaySevice->writeLog(var_export($_POST,true));
        $result = $alipaySevice->check($data);

        if($result){
            //商户订单号

            $out_trade_no = $data['out_trade_no'];

            //支付宝交易号

            $trade_no = $data['trade_no'];

            //交易状态
            $trade_status = $data['trade_status'];


            if($data['trade_status'] == 'TRADE_FINISHED') {

                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
                //如果有做过处理，不执行商户的业务程序

                //注意：
                //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
            }
            else if ($data['trade_status'] == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
                //如果有做过处理，不执行商户的业务程序
                //注意：
                //付款完成后，支付宝系统发送该交易状态通知
                $OrderModel = new Order();
                // 订单信息
                $order = $OrderModel->payDetail($data['out_trade_no']);

                if (!$order) {
                    echo "订单不存在";exit;
                }
                if ($order['order_state'] == 20) {
                    echo "订单已支付"; exit;
                }
                // 订单支付成功业务处理
                $OrderModel->paySuccess(PayTypeEnum::ALIPAY, $data,$order);
            }
            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
            echo "success";	//请不要修改或删除
        }else{
            echo "fail";
        }
    }

    /**
     * 支付成功同步通知
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-06
     * Time: 09:50
     */
    public function returnUrl()
    {

        require VENDOR_PATH.'/alipay/pagepay/service/AlipayTradeService.php';
        $alipaySevice = new \AlipayTradeService(Config::get('alipay'));
        $data = $this->request->get();
        $alipaySevice->writeLog(var_export($data, true));
        $result = $alipaySevice->check($data);
        if($result){
            $this->redirect('order/index');
        }else{
            echo "支付失败！";
            $this->error('支付失败','order/goodsList');
        }
    }

    /**
     * 银联支付
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-20
     * Time: 17:51
     *
     */
    public function yinLianUrl(){
//        $json = '{"PlatformTxnNo":"819092207831165","NotifyType":"1","PayerID":"*","TxnReqTime":"2019/09/22 02:52:51","MerchantRechargeAmt":"0.00","PartnerID":"1048","MerchantID":"100110000044037","ChannelID":"80","ChannelDisctAmt":"0.00","CurrencyCode":"156","ChannelTxnNo":"4200000390201909227234206779","MerchantName":"浙江衢州亓茶餐饮有限公司","TotalAmt":"0.01","BranchChannelID":"WEIX","OrgTxnNo":"2019092202522559059461","TerminalID":"*","IncomeAmt":"0.01","MerchantDisctAmt":"0.00","PointAmt":"0.00","Sign":"3a2c9e1e70bb0cce40ac06cb8a0de2ef50e6dc171ccf960d39c559458407bc80","InvoiceAmt":"0.01"}';
        if (!$json = file_get_contents('php://input')) {

            $this->returnCode(false, 'Not found DATA');

        }
        file_put_contents(dirname(__FILE__).'/aapp.php',$json);
        // 将服务器返回的JSON数据转化为数组
        $data = $this->fromJson($json);
        if (isset($data['OrgTxnNo']) && !empty($data['OrgTxnNo'])) {
            $OrderModel = new Order();

            $order_sn = substr($data['OrgTxnNo'], 0, -4);
            // 订单信息
            $order = $OrderModel->payDetail($order_sn);
            if (!$order) {
                echo "订单不存在";exit;
            }
            if ($order['order_state'] == 20) {
                echo "订单已支付"; exit;
            }
            // 订单支付成功业务处理
            $OrderModel->paySuccess(PayTypeEnum::YINLIAN, $data,$order);
            echo '{"RespCode":"00"}';
            exit();
        }else{
            $this->returnCode(false, '接口请求失败');
        }
    }

    /**
     * 返回状态给微信服务器
     * @param boolean $return_code
     * @param string $msg
     */
    private function returnCode($return_code = true, $msg = null)
    {
        // 返回状态
        $return = [
            'return_code' => $return_code ? 'SUCCESS' : 'FAIL',
            'return_msg' => $msg ?: 'OK',
        ];
        // 记录日志
        log_write([
            'describe' => '返回银联支付状态',
            'data' => $return
        ]);
        die();
    }

    /**
     * 输出xml字符
     * @param $values
     * @return bool|string
     */
    private function toXml($values)
    {
        if (!is_array($values)
            || count($values) <= 0
        ) {
            return false;
        }

        $xml = "<xml>";
        foreach ($values as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * 将json转为array
     * @param $xml
     * @return mixed
     */
    private function fromJson($json)
    {
        return json_decode($json, true);
    }

    /**
     * 写入日志记录
     * @param $values
     * @return bool|int
     */
    protected function doLogs($values)
    {
        return log_write($values);
    }

    /**
     * 退款成功回调
     * @author: luffy
     * @date  : 2019-11-07
     */
    public function refund_notify(){
        //获取返回的xml
        $testxml    = file_get_contents("php://input");
        //将xml转化为json格式
        $jsonxml    = json_encode(simplexml_load_string($testxml, 'SimpleXMLElement', LIBXML_NOCDATA));
        //转成数组
        $result     = json_decode($jsonxml, true);
        $refundDecryptData  = $this->refundDecrypt($result['req_info']);
        $refundDecryptData  = $this->xmlToArray($refundDecryptData);
        $order_info         = Db::name('order')->where('order_sn', $refundDecryptData['out_trade_no'])->find();
        if($result){
            //如果成功返回了
            if($result['return_code'] == 'SUCCESS'){
                //进行改变订单状态等操作。。。。
                // 更新退款单状态
                Db::name('order_'.$order_info['store_id'])->where('order_sn', $refundDecryptData['out_trade_no'])->update(['order_state'=>70]);
                echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            } else {
                Db::name('order_'.$order_info['store_id'])->where('order_sn', $refundDecryptData['out_trade_no'])->update(['order_state'=>60]);
            }
        }
    }

    /**
     * XML转数组
     * @author: luffy
     * @date  : 2019-11-07
     */
    public function refundDecrypt($str) {
        $decrypt = base64_decode($str,true);
        $key = md5('DB4EED2130E6D0CAF383E6B9B66D5528');
        return openssl_decrypt($decrypt , 'aes-256-ecb',$key, OPENSSL_RAW_DATA);
    }

    /**
     * 将xml转为array
     * @param string  $xml xml字符串或者xml文件名
     * @param bool   $isfile 传入的是否是xml文件名
     * @return array  转换得到的数组
     */
    public function xmlToArray($xml,$isfile=false){
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        if($isfile){
            if(!file_exists($xml)) return false;
            $xmlstr = file_get_contents($xml);
        }else{
            $xmlstr = $xml;
        }
        $result= json_decode(json_encode(simplexml_load_string($xmlstr, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $result;
    }
}