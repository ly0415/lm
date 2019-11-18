<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-08-05
 * Time: 13:37
 */

namespace app\api\controller;
use think\Config;
use app\common\library\wechat\WxPay;
use think\Db;

class Test extends Controller
{
    private $config = [1=>'xcx',2=>'weixin'];

    /**
     * 查询微信支付订单
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-16
     * Time: 10:08
     */
    public function searchWxPayInfo($order_sn = null,$type = 1){
        if(!$order_sn){
            return $this->renderError('传递订单号');
        }

        // 统一下单API
        $wxConfig = Config::get($this->config[$type]);
        $WxPay = new WxPay($wxConfig);
        $payment = $WxPay->queryOrderInfo($order_sn);
        return $this->renderSuccess($payment);

    }
}