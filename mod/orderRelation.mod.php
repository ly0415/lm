<?php
/**
 * User: xt
 * Date: 2019/1/24
 * Time: 14:05
 */

class OrderRelationMod extends BaseMod
{
    /**
     * OrderRelationMod constructor.
     */
    public function __construct()
    {
        parent::__construct('order_relation');
    }

    /**
     * 生成数据记录
     * @param $order_id
     * @param $source
     */
    public function insertOrderRelation($order_id, $source)
    {
        $orderMod = &m('order');

        // 订单信息
        $order = $orderMod->getOne(
            array(
                'cond' => 'order_id = ' . $order_id,
                'fields' => 'payment_time, finished_time',
            )
        );

        // 组装数据
        $data = array(
            'order_id' => $order_id,
            'receipt_time' => $order['finished_time'],
            'receipt_time_difference' => $order['finished_time'] - $order['payment_time'],
            'receipt_source' => $source,
        );

        $this->doInsert($data);
    }

}