<?php

namespace app\store\model;

use Think\Db;
use app\common\model\OrderRefund    as OrderRefundModel;
use app\store\model\User            as UserModel;
use app\store\service\Payment       as PaymentService;

/**
 * 退款退货模型
 * @author  luffy
 * @date    2019-07-28
 */
class OrderRefund extends OrderRefundModel
{

    /**
     * 管理订单商品
     * @author  luffy
     * @date    2019-08-12
     */
    public function refundOrder(){
        return $this->hasMany('OrderGoods','order_id','order_sn');
    }

    /**
     * 获取退款商品信息
     * @author  luffy
     * @date    2019-08-12
     */
    public function getOrderRefundDetail($order_sn){
        return $this->with('refundOrder')->where(['order_sn'=>$order_sn])->select()->toArray();
    }

    /**
     * 审核审核
     * @author  luffy
     * @date    2019-08-13
     */
    public function refund($order_sn, $data){
        if ($data['status'] == 2) {
            $this->startTrans();
            try {
                //审核记录
                Db::name('order_relation_'.STORE_ID)->where('order_sn', $order_sn)->update(['refund_review_user'=>USER_ID, 'refund_review_time'=>time()]);
                // 更新退款单状态
                Db::name('order_'.STORE_ID)->where('order_sn', $order_sn)->update(['order_state'=>80]);
                // 事务提交
                $this->commit();
                return true;
            } catch (\Exception $e) {
                $this->error = $e->getMessage();
                $this->rollback();
                return false;
            }
        } else {
            //查询信息
            $info = Db::name('order_'.STORE_ID)->alias('a')
                ->field('a.id,a.store_id,a.order_state,a.buyer_id,b.id as relation_id,b.payment_type,b.refund_source,e.yinlian_order,c.refund_amount,d.amount,e.pay_sn')
                ->join('order_details_'.STORE_ID. ' e', 'e.order_sn = a.order_sn')
                ->join('order_relation_'.STORE_ID. ' b', 'a.order_sn = b.order_sn')
                ->join('order_refund c', 'b.order_sn = c.order_sn')
                ->join('user d', 'a.buyer_id = d.id')
                ->where(['a.order_sn'=>$order_sn])
                ->find();
            switch ($info['payment_type']) {
                case 1:     //支付宝
                    break;
                case 2:     //微信
                    PaymentService::wxRefund($order_sn, $info['store_id'], $info['refund_amount']);
                    return true;
                    break;
                case 3:     //余额
                    $newAmount = $info['amount'] + $info['refund_amount'];
                    //生成余额日志
                    $source = 0;
                    switch ($info['refund_source']) {
                        case 1:  //小程序
                            $source = 2;
                            break;
                        case 2:  //公众号
                            $source = 1;
                            break;
                        case 3:  //代客下单
                            $source = 4;
                            break;
                        case 4:  //pc前台下单
                            $source = 3;
                            break;
                    }
                    //生成余额日志
                    $amountData = array(
                        'order_sn'  => $order_sn,
                        'type'      => 6,
                        'status'    => 2,
                        'c_money'   => $info['refund_amount'],
                        'old_money' => $info['amount'],
                        'new_money' => $newAmount,
                        'source'    => $source,
                        'add_user'  => $info['buyer_id'],
                        'add_time'  => time(),
                    );
                    break;
                case 4:     //线下
                    break;
                case 5:     //免费兑换
                    break;
                case 11:    //银联支付
                    header('Content-Type: text/html; charset=utf-8');

                    //退款订单号
                    $refund_order_sn    = $order_sn.rand(1000, 9999);
                    $this->where('order_sn', $order_sn)->update(['yinlian_order'=>$refund_order_sn]);

                    //执行退款操作
                    include_once VENDOR_PATH."/shijicloud/OrderRefund.php";
                    $SearchOrder        = new \OrderRefund();
                    $result = $SearchOrder->orderRefund([$info['yinlian_order'], $refund_order_sn, $info['pay_sn'], $info['refund_amount'], $info['store_id']])->index();
                    if($result !== true){
                        $this->error = $result;
                        return false;
                    }
                    break;
            }
            $this->startTrans();
            try {
                if($info['payment_type'] == 3){
                    // 生成退款日志
                    Db::name('amount_log')->insert($amountData);
                    //增加用户余额
                    (new UserModel)->save(['amount'=>$newAmount], ['id' => $info['buyer_id']]);
                }
                //审核记录
                Db::name('order_relation_'.STORE_ID)->where('order_sn', $order_sn)->update(['refund_review_user'=>USER_ID, 'refund_review_time'=>time()]);
                // 更新退款单状态
                Db::name('order_'.STORE_ID)->where('order_sn', $order_sn)->update(['order_state'=>70]);
                // 事务提交
                $this->commit();
                return true;
            } catch (\Exception $e) {
                $this->error = $e->getMessage();
                $this->rollback();
                return false;
            }
        }
    }

    /**
     * 获取退款商品信息
     * @author  luffy
     * @date    2019-08-12
     */
    public function wxRefund($order_sn){

    }

}