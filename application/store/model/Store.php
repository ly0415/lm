<?php

namespace app\store\model;

use app\common\model\Store as StoreModel;

/**
 * 商城模型
 * Class Store
 * @package app\store\model
 */
class Store extends StoreModel
{

    /**
     * 构造方法
     */
    public function initialize()
    {
        parent::initialize();
    }

    /**
     * 后台首页数据
     * @author  luffy
     * @date    2019-07-09
     */
    public function getHomeData($storeId)
    {
        $model = new Order;
        // 最近七天日期
        $getLately7daysTime = $this->getLately7daysTime();
        //获取首页数据
        $data = [
            'widget-card'   => [
                // 商品总量
                'goods_total'   => $this->getGoodsTotal($storeId),
                // 已付款订单总量
                'order_total'   => $this->getOrderTotal($storeId),
                // 余额充值总额
                'recharge_total'=> $this->getRechargeTotal($storeId),
                // 总营业额
                'income_total'  => $this->getIncomeTotal($storeId)
            ],
            'widget-outline'    => [
                // 营业额(元)
                'income'    => [
                    'cday'      => $this->getIncomeTotal($storeId, $this->getAppointDays(1)),
                    'yday'      => $this->getIncomeTotal($storeId, $this->getAppointDays(2))
                ],
                // 支付订单数
                'order_total'   => [
                    'cday'      => number_format($model->getOrderTotal($storeId, 2, $this->getAppointDays(1))),
                    'yday'      => number_format($model->getOrderTotal($storeId, 2, $this->getAppointDays(2)))
                ],
                // 下单用户数
                'new_user_total'=> [
                    'cday'      => $this->getOrderUserTotal($storeId, $this->getAppointDays(1)),
                    'yday'      => $this->getOrderUserTotal($storeId, $this->getAppointDays(2))
                ],
                // 退款用户数
                'refund_user_num'=> [
                    'cday'      => $this->getRefundUserTotal($storeId, $this->getAppointDays(1)),
                    'yday'      => $this->getRefundUserTotal($storeId, $this->getAppointDays(2))
                ],
                // 退款总额
                'refund_order_money'=> [
                    'cday'      => $this->getReundMoneyTotal($storeId, $this->getAppointDays(1)),
                    'yday'      => $this->getReundMoneyTotal($storeId, $this->getAppointDays(2))
                ],
                // 退款订单数
                'refund_order_num'=> [
                    'cday'      => number_format($model->getOrderTotal($storeId, 3, $this->getAppointDays(1))),
                    'yday'      => number_format($model->getOrderTotal($storeId, 3, $this->getAppointDays(2)))
                ],
            ],
            'widget-echarts'    => [
                // 最近七天日期
                'date'              => json_encode($this->getLately7days()),        //具体时间-当天、昨天
                'order_total'       => json_encode($this->getOrderTotalByDate(STORE_ID, $getLately7daysTime)),
                'order_total_price' => json_encode($this->getOrderTotalPriceByDate(STORE_ID, $getLately7daysTime))
            ]
        ];
        return $data;
    }

    /**
     * 获取商品总量
     * @author  luffy
     * @date    2019-07-09
     */
    private function getGoodsTotal($storeId)
    {
        $model = new StoreGoods;
        return [
            number_format($model->getGoodsTotal($storeId)),                //所有商品
            number_format($model->getGoodsTotal($storeId, 1)),      //上架在售
            number_format($model->getGoodsTotal($storeId, 2))       //下架停售
        ];
    }

    /**
     * 获取订单总量
     * @author  luffy
     * @date    2019-07-09
     */
    private function getOrderTotal($storeId)
    {
        $model = new Order;
        return [
            number_format($model->getOrderTotal($storeId, 0)),      //所有订单
            number_format($model->getOrderTotal($storeId, 1)),      //已付款不包含退款
            number_format($model->getOrderTotal($storeId, 3)),      //仅仅是退款的（包含退款各种状态）
            number_format($model->getOrderTotal($storeId, 4)),      //已取消
            number_format($model->getOrderTotal($storeId, 5))       //仅仅是已删除
        ];
    }

    /**
     * 获取余额充值总额
     * @author  luffy
     * @date    2019-07-10
     */
    private function getRechargeTotal($store_id)
    {
        $model = new BalanceRechargeCoupon;
        return number_format($model->getStoreRechargeTotal($store_id));
    }

    /**
     * 获取营业额
     * @author  luffy
     * @date    2019-07-11
     */
    private function getIncomeTotal($store_id, $day = '')
    {
        $model = new Order;
        return sprintf("%.2f", $model->getIncomeTotal($store_id, $day));
    }

    /**
     * 获取退款总额
     * @author  luffy
     * @date    2019-10-12
     */
    private function getReundMoneyTotal($store_id, $day = '')
    {
        $model = new Order;
        return sprintf("%.2f", $model->getReundMoneyTotal($store_id, $day));
    }

    /**
     * 获取下单用户数
     * @author  luffy
     * @date    2019-07-11
     */
    private function getOrderUserTotal($store_id, $day = '')
    {
        $model = new Order;
        return $model->getOrderUserTotal($store_id, $day);
    }

    /**
     * 获取退款用户数
     * @author  luffy
     * @date    2019-10-12
     */
    private function getRefundUserTotal($store_id, $day = '')
    {
        $model = new Order;
        return $model->getRefundUserTotal($store_id, $day);
    }

    /**
     * 获取指定店铺已支付订单总量(
     * @author  luffy
     * @date    2019-07-09
     */
    private function getOrderTotalByDate($store_id, $days)
    {
        $model = new Order;
        $data = [];
        foreach ($days as $day) {
            $data[] = number_format($model->getOrderTotal($store_id, 2, $day));
        }
        return $data;
    }

    /**
     * 获取已支付订单总额
     * @author  luffy
     * @date    2019-07-09
     */
    private function getOrderTotalPriceByDate($store_id, $days)
    {
        $data = [];
        foreach ($days as $day) {
            $data[] = $this->getIncomeTotal($store_id, $day);
        }
        return $data;
    }

    /**
     * 最近七天日期
     * @author  luffy
     * @date    2019-07-09
     */
    private function getLately7days()
    {
        // 获取当前周几
        $date = [];
        for ($i = 0; $i < 7; $i++) {
            $date[] = date('Y-m-d', strtotime('-' . $i . ' days'));
        }
        return array_reverse($date);
    }

    /**
     * 最近七天开始结束时间戳
     * @author  luffy
     * @date    2019-07-09
     */
    private function getLately7daysTime()
    {
        $date = [];
        for ($i = 0; $i < 7; $i++) {
            $date[$i][] = strtotime(date('Y-m-d', strtotime('-' . $i . ' days')));
            $date[$i][] = strtotime(date('Y-m-d', strtotime('-' . ($i-1) . ' days'))) - 1;
        }
        return array_reverse($date);
    }

    /**
     * 具体时间-当天、昨天
     * @author  luffy
     * @date    2019-07-09
     */
    private function getAppointDays($type = 0)
    {
        $res = [];
        //当天初始时间
        $_time = strtotime(date('Y-m-d', time()));
        if( $type == 1 ){            //当天
            $res[]  = $_time;            //初始
            $res[]  = $_time + 86399;    //结束
        } elseif( $type == 2 ){     //昨天
            $res[]  = $_time - 86400;    //初始
            $res[]  = $_time - 1;        //结束
        }
        return $res;
    }
    /**
     *  获取店铺信息
     * author fup
     * date 2019-07-11
     */
    public function getStoreInfo($query=[]){
        return $this->field('id,store_name')
            ->where($query)
            ->where('is_open','=',1)
            ->select();
    }

    /**
     * 获取总站id
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-06
     * Time: 14:04
     */
    public static function getAdminStoreId($store_cate_id = 17){
        return self::where('store_cate_id','=',$store_cate_id)
            ->where('store_type','=',1)
            ->value('id');
    }


    /**
     * 获取店铺的业务类型id
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-24
     * Time: 11:13
     */
    public static function getStoreBusinessId($storeId = STORE_ID){
        return self::where('id','=',$storeId)
            ->value('business_id');
    }

}