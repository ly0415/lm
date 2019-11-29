<?php

namespace app\task\model;

use app\common\model\Order as OrderModel;

use app\task\model\User as UserModel;
use app\task\model\StoreGoods as StoreGoodsModel;
use app\task\model\dealer\Apply as DealerApplyModel;
use app\task\model\user\BalanceLog as BalanceLogModel;
use app\store\model\dealer\Order  as DealerOrderModel;
use app\common\exception\BaseException;
use app\common\enum\order\PayType as PayTypeEnum;
use app\common\enum\OrderType as OrderTypeEnum;
use app\common\enum\user\balanceLog\Scene as SceneEnum;
use think\Db;

/**
 * 订单模型
 * Class Order
 * @package app\common\model
 */
class Order extends OrderModel
{
    /**
     * 获取订单列表
     * @param array $filter
     * @param array $with
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList($filter = [], $with = [])
    {
        return $this->with($with)
            ->where($filter)
            ->where('is_delete', '=', 0)
            ->select();
    }

    /**
     * 待支付订单详情
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-03
     * Time: 18:38
     */
    public function payDetail($order_no)
    {
        if (!$order = self::get(['order_sn' => $order_no])) {
            throw new BaseException(['msg' => '订单不存在']);
        }
       $detail = Db::name('order_'.$order['store_id'])
           ->field('a.*,u.id as user_id,u.amount,b.fx_user_id,b.coupon_discount,l.user_coupon_id,l.coupon_id,cp.type as coupon_type')
           ->alias('a')
           ->join('order_details_'.$order['store_id'].' b','a.order_sn = b.order_sn','LEFT')
           ->join('order_relation_'.$order['store_id'].' c','b.order_sn = c.order_sn','LEFT')
           ->join('coupon_log l','a.order_sn = l.order_sn','LEFT')
           ->join('coupon cp','l.coupon_id = cp.id','LEFT')
           ->join('user u','a.buyer_id = u.id','LEFT')
           ->where('a.order_sn','=',$order_no)
           ->where('a.store_id','=',$order['store_id'])
           ->where('a.mark','=',1)
           ->find();
       if($detail){
           $detail['order_id'] = $order['order_id'];
           $detail['order_goods'] = Db::name('order_goods')
               ->where('order_id','=',$detail['order_sn'])
               ->select()->toArray();
       }
       return $detail;

    }

    /**
     * 订单支付成功业务处理
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-29
     * Time: 20:07
     */
    public function paySuccess($payType, $payData = [],$order = [])
    {
        // 更新付款状态
        $status = $this->updatePayStatus($payType, $payData,$order);
        return $status;
    }

    /**
     * 更新付款状态
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-03
     * Time: 20:30
     */
    private function updatePayStatus($payType, $payData = [],$order = [])
    {
        // 获取用户信息
        $user = UserModel::detail($order['user_id']);
//        dump($payType);
//        dump($order);dump($payData);die;
        // 验证余额支付时用户余额是否满足
//        if ($payType == PayTypeEnum::BALANCE) {
//            if ($user['amount'] < $order['order_amount']) {
//                $this->error = '用户余额不足，无法使用余额支付';
//                return false;
//            }
//        }
        $this->transaction(function () use ($user, $payType, $payData,$order) {
            // 更新商品库存、销量
            (new StoreGoodsModel)->updateStockSales($order['order_goods']);
            // 更新订单状态
            $orderRela = ['payment_type' => $payType,'payment_time' => time()];
            $_order = [ 'order_state' => 20];
            $orderDetails['number_order'] = $this->orderNumberOrder($order['store_id']);
            if ($payType == PayTypeEnum::WECHAT) {

                $orderDetails['pay_sn'] = $payData['transaction_id'];
            }
            if ($payType == PayTypeEnum::YINLIAN) {
                $orderDetails['yinlian_order'] = $payData['OrgTxnNo'];
                $orderDetails['pay_sn'] = $payData['ChannelTxnNo'] . '_'.$payData['BranchChannelID'];
            }
            if ($payType == PayTypeEnum::ALIPAY) {

                $orderDetails['pay_sn'] = $payData['trade_no'];
            }
            Db::name('order_details_'.$order['store_id'])
                ->where('order_sn','=',$order['order_sn'])
                ->update($orderDetails);
            Db::name('order')
                ->where('order_sn','=',$order['order_sn'])
                ->update($_order);
            Db::name('order_'.$order['store_id'])
                ->where('order_sn','=',$order['order_sn'])
                ->update($_order);
            Db::name('order_relation_'.$order['store_id'])
                ->where('order_sn','=',$order['order_sn'])
                ->update($orderRela);
            // 记录分销商订单
            DealerOrderModel::createOrder($order);
            // 余额支付
            if ($payType == PayTypeEnum::BALANCE) {
                // 更新用户余额
                BalanceLogModel::add(SceneEnum::CONSUME, [
                    'add_user' => $user['id'],
                    'status' => 2,
                    'source' => BalanceLogModel::$source[$order['source']],
                    'c_money' => $order['order_amount'],
                    'old_money' => $user['amount'],
                    'new_money' => bcsub($user['amount'],$order['order_amount'],2),
                    'order_sn' => $order['order_sn'],
                ]);
                $user->setDec('amount', $order['order_amount']);
            }
        });
        return true;
    }

    /**
     * 购买指定商品成为分销商
     * @param $user_id
     * @param $goodsList
     * @param $wxapp_id
     * @return bool
     * @throws \think\exception\DbException
     */
    private function becomeDealerUser($user_id, $goodsList, $wxapp_id)
    {
        // 整理商品id集
        $goodsIds = [];
        foreach ($goodsList as $item) {
            $goodsIds[] = $item['goods_id'];
        }
        $model = new DealerApplyModel;
        return $model->becomeDealerUser($user_id, $goodsIds, $wxapp_id);
    }

}
