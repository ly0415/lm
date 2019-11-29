<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-08-05
 * Time: 13:37
 */

namespace app\api\controller;
use app\api\service\BarCode128;
use app\common\service\Message;
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
    //生成条码
    public function bcode(){
        $barcode = new BarCode128('8888888');

        $barcode->createBarCode();
    }
    //刷分类图片
    public function goods_category_image(){
        $data = Db::name('goods_category')->where(['id'=>['NOT IN',[1498,1503,1606]]])
            ->select();
        $i = 0;
        foreach ($data as $k => $item){
                        $i += 1;
            $image = str_replace('upload/images/cates/','120/Store.goodsCategory/',$item['image']);
            Db::name('goods_category')->where('id','=',$item['id'])
                ->update(['image'=>$image]);
//            $filename = explode('/',$item['image']);
//            $image = \think\Image::open(ROOT_PATH.'../'.$item['image']);
//            // 检测目录
//            $uplodBig = ROOT_PATH.'../web/uploads/big/120/Store.goodsCategory/'.$filename[3].'/';
//            $uplodSmall = ROOT_PATH.'../web/uploads/small/120/Store.goodsCategory/'.$filename[3].'/';
//            if (false === $this->checkPath($uplodSmall) || false === $this->checkPath($uplodBig)) {
//                return $this->renderError($k);
//            }
//            $image->save($uplodBig.$filename[4]);
//
//            $image->thumb(150,150,\think\Image::THUMB_CENTER)->save($uplodSmall.$filename[4]);
        }
        return $this->renderSuccess($i);
    }

    protected function checkPath($path)
    {


        if (is_dir($path) || mkdir($path, 0755, true)) {
            return true;
        }

        $this->error = ['directory {:path} creation failed', ['path' => $path]];

        return false;
    }

    public function sendMessage(){
        $Service = new Message();
        $order = [];
        // 发送消息通知
        $Service->delivery($order);
    }

    public function state1($order_sn = null){
        if(!$order_sn){
            return $this->renderError('缺少必填参数order_sn');
        }
        $orderModel = new \app\store\model\Order();
        //接单
        $order = $orderModel->getOrderDetail($order_sn);
        if($order['order_state'] == 20 && $orderModel->acceptOrder1($order,1)){
            return $this->renderSuccess('接单成功',url('order/detail',['order_sn'=>$order_sn]));
        }
        // 确认核销
        if ($order['order_state'] == 25 && $orderModel->extractOrder($order,2)) {
            return $this->renderSuccess('核销成功',url('order/detail',['order_sn'=>$order_sn]));
        }
        return $this->renderError($orderModel->getError() ? : '该订单未满足接单或核销条件');

    }


}