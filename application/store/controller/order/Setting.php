<?php

namespace app\store\controller\order;

use app\store\controller\Controller;
use think\Config;
use app\common\library\sms\Driver   as SmsDriver;
use app\store\model\Sms;
use app\store\model\User as UserModel;
use app\store\model\Cart as CartModel;
use app\store\model\UserCoupon;
use app\store\model\StoreGoodsSpecPrice;
use app\store\model\GoodsSpec as GoodsSpecModel;
use app\store\model\Store as StoreModel;
use app\store\model\FxUser;
/**
 * 代客下单
 * Class Setting
 * @package app\store\controller\order
 */
class Setting extends Controller
{

    /**
     * 发送短信通知
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-12
     * Time: 10:45
     */
    public function sms_code($accept_phone,$msg_type = 'balance' ){
        $aliyun = Config::get('aliyun');

        $SmsDriver = new SmsDriver([
            'default' => 'aliyun',
            'engine' => [
                'aliyun' => [
                    'AccessKeyId' => $aliyun['AccessKeyId'],
                    'AccessKeySecret' => $aliyun['AccessKeySecret'],
                    'sign' => $aliyun['sign'],
                    $msg_type => [
                        'is_enable' => $aliyun['is_enable'],
                        'template_code' => $aliyun['template_code'],
                        'accept_phone' => $accept_phone
                    ],
                ],
            ],
        ]);
        $templateParams = ['phone'=>$accept_phone,'code' => getCode()];
        $sms = new Sms();
        $sms->add($templateParams);
        if ($SmsDriver->sendSms($msg_type, $templateParams)) {
            return $this->renderSuccess('发送成功');
        }
        return $this->renderError('发送失败 ' . $SmsDriver->getError());
    }

    /**
     * 获取优惠券
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-04
     * Time: 17:19
     */
    public function ajax_get_coupon($phone = 0,$amount = 0,$uniquecode = 0){
        $coupon = [];
        $user = UserModel::detail(['phone'=>$phone]);
        if($user){
            $Cart = new CartModel();
            $model = new UserCoupon();
            $order = $Cart->getList(['uniquecode'=>$uniquecode,'cart_ids'=>null],STORE_ID);
                $good_arr   = $model->getGoodsAll(implode(',',array_column($order['goods_list'],'cart_id')));
                $coupon       = $model->getCouponAll($user['id'], STORE_ID, $order['order_total_price'], $good_arr);
//                dump($list);die;

        }
        $this->view->engine->layout(false);
        return $this->fetch('order/ajaxGetCoupon', compact('coupon'));
    }

    /**
     * 获取商品规格
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-21
     * Time: 20:34
     */
    public function ajax_get_spec($store_goods_id = 0){
        //获取店铺商品对应规格
        $specKey = StoreGoodsSpecPrice::getSpecKey($store_goods_id);

        //获取规格对应的规格值
        $data = (new GoodsSpecModel)->getList($specKey);

        $list = $this->formatData($store_goods_id,$data);

        $this->view->engine->layout(false);
        return $this->fetch('order/ajaxGetSpec', compact('list'));
    }

    /**
     * 获取规格对应价格
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-22
     * Time: 16:14
     */
    public function ajax_goods_price_stock($store_goods_id = 0,$key = null,$return_array = false){
        $spec_arr = [];
        if ($key) {
            $key_arr = explode('_', $key);
            $key_pailie = arrangement($key_arr, count($key_arr));
            foreach ($key_pailie as $v) {
                $spec_arr[] = implode('_', $v);
            }
        }
        $store_discount = StoreModel::getStoreDiscount();
        if(!$goodsPriceStock = StoreGoodsSpecPrice::getSpecPriceStock($store_goods_id,$spec_arr)){
            $goodsPriceStock['stock'] = 0;
            $goodsPriceStock['price'] = 0;
        }
        $goodsPriceStock['price'] = number_format($goodsPriceStock['price'] * $store_discount,'2','.','');
        if($return_array){
            return $goodsPriceStock;
        }
        return $this->renderSuccess('SUCCESS','',$goodsPriceStock);
    }


    /**
     * 获取分销码
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-10
     * Time: 14:56
     */
    public function ajax_get_fx_code($phone = null ){
        $user = UserModel::detail(['phone'=>$phone]);
        $fxUserModel = new FxUser();
        $data = $fxUserModel->getCodeByUser($user);
        if($data){
            return $this->renderSuccess('SUCCESS','',$data);
        }
        return $this->renderError('FAIL');
    }

    /**
     * 通过手机号模糊搜索用户
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-02
     * Time: 13:50
     */
    public function search_user($phone = ''){
        $model = new UserModel();
        $user = $model->getList('','','',$phone);
        return $this->renderSuccess('SUCCESS','',$user);
    }

}