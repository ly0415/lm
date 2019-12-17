<?php

namespace app\common\service;

use app\common\enum\OrderType as OrderTypeEnum;
use think\Db;

/**
 * 订单服务类
 * Class Order
 * @package app\common\service
 */
class Order
{
    /**
     * 订单模型类
     * @var array
     */
    private static $orderModelClass = [
        OrderTypeEnum::MASTER => 'app\common\model\Order',
        OrderTypeEnum::SHARING => 'app\common\model\sharing\Order'
    ];

    /**
     * 生成订单号
     * @return string
     */
    public static function createOrderNo($limit = 1)
    {
        $begin = pow(10, 3);
        $end = (pow(10, 4) - 1);
        $rand_array = range($begin, $end);
        shuffle($rand_array); //调用现成的数组随机排列函数
        return date('YmdHis') .array_slice($rand_array, 0, $limit)[0] ;
    }

    /**
     * 生成小票编号
     * @author: luffy
     * @date: 2018-08-09
     */
    public static function createNumberOrder($storeid)
    {
        //获取当天开始结束时间
        $startDay = strtotime(date('Y-m-d'));
        $endDay = strtotime(date('Y-m-d 23:59:59'));
        $number_order = Db::name('order_' . $storeid)
            ->alias('a')
            ->join('order_details_'.$storeid.' b','a.order_sn = b.order_sn','LEFT')
            ->where('a.add_time', 'BETWEEN', [$startDay, $endDay])
            ->where('a.order_state', '>', 10)
            ->where('a.mark', '=', 1)
            ->order('a.add_time DESC')
            ->value('b.number_order');
        //不管订单存在与否直接加
        $number_order = (int)$number_order + 1;
        return str_pad($number_order, 4, 0, STR_PAD_LEFT);
    }
       

    /**
     * 整理订单列表 (根据order_type获取不同类型的订单记录)
     * @param \think\Collection|\think\Paginator $data 数据源
     * @param string $orderIndex 订单记录的索引
     * @param array $with 关联查询
     * @return mixed
     */
    public static function getOrderList(&$data, $orderIndex = 'order', $with = [])
    {
        // 整理订单id
        $orderIds = [];
        foreach ($data as &$item) {
            $orderIds[$item['order_type']['value']][] = $item['order_id'];
        }
        // 获取订单列表
        $orderList = [];
        foreach ($orderIds as $orderType => $values) {
            $model = self::model($orderType);
            $orderList[$orderType] = $model->getListByIds($values, $with);
        }
        // 格式化到数据源
        foreach ($data as &$item) {
            $item[$orderIndex] = $orderList[$item['order_type']['value']][$item['order_id']];
        }
        return $data;
    }

    /**
     * 获取订单详情 (根据order_type获取不同类型的订单详情)
     * @param $orderId
     * @param int $orderType
     * @return mixed
     */
    public static function getOrderDetail($orderId, $orderType = OrderTypeEnum::MASTER)
    {
        $model = self::model($orderType);
        return $model::detail($orderId);
    }

    /**
     * 根据订单类型获取对应的订单模型类
     * @param int $orderType
     * @return mixed
     */
    public static function model($orderType = OrderTypeEnum::MASTER)
    {
        static $models = [];
        if (!isset($models[$orderType])) {
            $models[$orderType] = new self::$orderModelClass[$orderType];
        }
        return $models[$orderType];
    }

}