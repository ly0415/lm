<?php

namespace app\common\model;

use think\Hook;
use think\Config;
use think\Db;
use app\common\model\store\shop\Order as ShopOrder;
use app\common\service\Order as OrderService;
use app\common\library\helper;
use app\common\enum\DeliveryType as DeliveryTypeEnum;

/**
 * 订单模型
 * Class Order
 * @package app\common\model
 */
class Order extends BaseModel
{
    protected $name = 'order';

    protected $updateTime = false;
    //订单状态
    public $order_state = [
        '0'     => '已取消',
        '10'    => '待付款',
        '20'    => '已付款',
        '25'    => '已接单',
        '30'    => '已发货',
        '40'    => '区域配送',
        '50'    => '已收货',
        '60'    => '退款中',
        '70'    => '已退款',
        '80'    => '拒退款'
    ];
    //支付方式
    public $payment_type = [
        '0'     => '未支付',
        '1'     => '支付宝支付',
        '2'     => '微信支付',
        '3'     => '余额支付',
        '4'     => '线下支付',
        '5'     => '免费兑换',
        '11'    => '聚合支付'
    ];

    //聚合支付
    public $payment = [
        'WEIX' => '微信',
        'ZFBA' => '支付宝'
    ];
    //配送属性
    public $delivery_type = [
        '1'     => '到店自取',
        '2'     => '门店直配',
        '3'     => '总仓直邮',
        '4'     => '海外保税购'
    ];



    /**
     * 追加字段
     * @var array
     */
//    protected $append = [
//        'state_text',   // 售后单状态文字描述
//    ];

    /**
     * 订单模型初始化
     */
    public static function init()
    {
//        parent::init();
//        // 监听订单处理事件
//        $static = new static;
//        Hook::listen('order', $static);
    }

    /**
     * 订单商品列表
     * @return \think\model\relation\HasMany
     */
    public function goods()
    {
        return $this->hasMany('OrderGoods','order_sn','order_id');
    }

    /**
     * 关联订单收货地址表
     * @return \think\model\relation\HasOne
     */
    public function address()
    {
        return $this->hasOne('OrderAddress');
    }

    /**
     * 关联订单收货地址表
     * @return \think\model\relation\HasOne
     */
    public function extract()
    {
        return $this->hasOne('OrderExtract');
    }

    /**
     * 关联自提门店表
     * @return \think\model\relation\BelongsTo
     */
    public function extractShop()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\store\\Shop", 'extract_shop_id');
    }

    /**
     * 关联门店店员表
     * @return \think\model\relation\BelongsTo
     */
    public function extractClerk()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\store\\shop\\Clerk", 'extract_clerk_id');
    }

    /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('User');
    }

    /**
     * 关联物流公司表
     * @return \think\model\relation\BelongsTo
     */
    public function express()
    {
        return $this->belongsTo('Express');
    }

    /**
     * 改价金额（差价）
     * @param $value
     * @return array
     */
//    public function getUpdatePriceAttr($value)
//    {
//        return [
//            'symbol' => $value < 0 ? '-' : '+',
//            'value' => sprintf('%.2f', abs($value))
//        ];
//    }

    /**
     * 付款状态
     * @param $value
     * @return array
     */
//    public function getPayTypeAttr($value)
//    {
//        return ['text' => PayTypeEnum::data()[$value]['name'], 'value' => $value];
//    }

    /**
     * 付款状态
     * @param $value
     * @return array
     */
//    public function getPayStatusAttr($value)
//    {
//        return ['text' => PayStatusEnum::data()[$value]['name'], 'value' => $value];
//    }

    /**
     * 发货状态
     * @param $value
     * @return array
     */
//    public function getDeliveryStatusAttr($value)
//    {
//        $status = [10 => '待发货', 20 => '已发货'];
//        return ['text' => $status[$value], 'value' => $value];
//    }

    /**
     * 收货状态
     * @param $value
     * @return array
     */
//    public function getReceiptStatusAttr($value)
//    {
//        $status = [10 => '待收货', 20 => '已收货'];
//        return ['text' => $status[$value], 'value' => $value];
//    }

    /**
     * 配送方式
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-13
     * Time: 10:25
     */
    public function getSendoutAttr($value){
        return ['text'=>$this->delivery_type[$value],'value'=>$value];
    }

    /**
     * 收货状态
     * @param $value
     * @return array
     */
//    public function getOrderStatusAttr($value)
//    {
//        $status = [10 => '进行中', 20 => '已取消', 21 => '待取消', 30 => '已完成'];
//        return ['text' => $status[$value], 'value' => $value];
//    }

    /**
     * 配送方式
     * @param $value
     * @return array
     */
//    public function getDeliveryTypeAttr($value)
//    {
//        return ['text' => DeliveryTypeEnum::data()[$value]['name'], 'value' => $value];
//    }

    /**
     * 生成订单号
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-03
     * Time: 16:42
     */
    protected function orderNo()
    {
        return OrderService::createOrderNo();
    }

    /**
     * 生成小票
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-03
     * Time: 16:42
     */
    protected function orderNumberOrder($storeId)
    {
        return OrderService::createNumberOrder($storeId);
    }

    /**
     * 订单详情
     * @param array|int $where
     * @param array $with
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($where, $with = [
        'user',
        'address',
        'goods' => ['image'],
        'extract',
        'express',
        'extract_shop.logo',
        'extract_clerk'
    ])
    {
        is_array($where) ? $filter = $where : $filter['order_id'] = (int)$where;
        return self::get($filter, $with);
    }

    /**
     * 批量获取订单列表
     * @param $orderIds
     * @param array $with 关联查询
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getListByIds($orderIds, $with = [])
    {
        $data = $this->getListByInArray('order_id', $orderIds, $with);
        return helper::arrayColumn2Key($data, 'order_id');
    }

    /**
     * 批量获取订单列表
     * @param string $field
     * @param array $data
     * @param array $with
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getListByInArray($field, $data, $with = [])
    {
        return $this->with($with)
            ->where($field, 'in', $data)
            ->where('is_delete', '=', 0)
            ->select();
    }

    /**
     * 根据订单号批量查询
     * @param $orderNos
     * @param array $with
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getListByOrderNos($orderNos, $with = [])
    {
        return $this->getListByInArray('order_no', $orderNos, $with);
    }

    /**
     * 订单商品
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-13
     * Time: 09:54
     */
    public function orderGoods(){
        return $this->hasMany('OrderGoods','order_id','order_sn');
    }

    /**
     * 关联电子券
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-13
     * Time: 10:21
     */
    public function coupon(){
        return $this->belongsTo('Coupon','cid','id');
    }

    /**
     * 更新商品库存
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-29
     * Time: 17:02
     */
    public function updateStockSales($goodsList){

        // 批量更新商品规格：库存
        foreach ($goodsList as $goods){
            $where = [];
            if($goods['deduction'] == 1){
                $where = [
                    'goods_id' => $goods['goods_id'],
                    'store_id' => \app\store\model\Store::getAdminStoreId(),
                    'mark' => 1
                ];
                $goodsSpecSave = [
                    'goods_storage' => ['dec', $goods['total_num']]
                ];
                (new StoreGoods)->where($where)->update($goodsSpecSave);
            }elseif($goods['deduction'] == 2){
                if(!empty($goods['key'])){
                    $where['key'] = ['IN',$goods['key']];
                    $where['store_goods_id'] = $goods['id'];
                    $goodsSpecSave = [
                        'stock' => ['dec', $goods['total_num']]
                    ];
                    (new StoreGoodsSpecPrice())->where($where)->update($goodsSpecSave);

                }else{
                    $where['id'] = $goods['id'];
                    $goodsSpecSave = [
                        'goods_storage' => ['dec', $goods['total_num']]
                    ];
                    (new StoreGoods)->where($where)->update($goodsSpecSave);
                }
            }

        }
    }


    /**
     * 确认核销（自提订单）咖啡机
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-05
     * Time: 16:12
     */
    public function verificationOrder(&$order)
    {

        if (
            $order['order_state'] != 20 || $order['sendout'] != DeliveryTypeEnum::EXTRACT
        ) {
            $this->error = '该订单不满足核销条件';
            return false;
        }
        return $this->transaction(function () use ($order) {
            // 更新订单状态：已发货、已收货
            Db::name('order')
                ->where('order_sn','=',$order['order_sn'])
                ->update(['order_state'=>50]);
            $status = Db::name('order_'.$order['store_id'])
                ->where('order_sn','=',$order['order_sn'])
                ->update(['order_state'=>50]);
            Db::name('order_relation_'.$order['store_id'])
                ->where('order_sn','=',$order['order_sn'])
                ->update([
                    'receipt_time' => time(),
                    'receipt_time_difference' => time() - $order['payment_time'],
                    'receipt_source' => 2
                ]);
            return $status;
        });
    }

    /**
     * 确认核销（自提订单）平台自用
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-05
     * Time: 16:13
     */
    public function verificationOrderSelf(&$order,$source)
    {

        if (
            $order['order_state'] != 25 || $order['sendout'] != DeliveryTypeEnum::EXTRACT
        ) {
            $this->error = '该订单不满足核销条件';
            return false;
        }
        $order['point'] = StorePointSite::detail(['store_id'=>$order['store_id']]);
        return $this->transaction(function () use ($order,$source) {
            $this->addPoint($order);
            $this->addFxMoney($order['order_sn']);
            // 更新订单状态：已发货、已收货
            return $this->edit($order,$source);
        });
    }

    /**
     *
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-05
     * Time: 11:40
     */
    private function edit(&$order,$source){
        $data = [
            'order_state'=>50,
            'finished_time'=>time(),
            'delivery_status'=>1
        ];
        Db::name('order')
            ->where('order_sn','=',$order['order_sn'])
            ->update($data);
        $status = Db::name('order_'.$order['store_id'])
            ->where('order_sn','=',$order['order_sn'])
            ->update(['order_state'=>50]);
        Db::name('order_relation_'.$order['store_id'])
            ->where('order_sn','=',$order['order_sn'])
            ->update([
                'receipt_time' => time(),
                'receipt_time_difference' => time() - $order['payment_time'],
                'receipt_source' => $source
            ]);
//        Db::name('order_goods')
//            ->where('order_sn','=',$order['order_sn'])
//            ->update($data);
        return $status;
    }

    /**
     * 赠送积分
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-05
     * Time: 15:38
     */
    private function addPoint(&$order){
        $user = User::detail($order['buyer_id']);
        $point = !empty($order['point']) ? ceil($order['point']['order_point'] * $order['order_amount'] * 0.01) : 0;
        // 添加积分日志
        $point > 0 &&  PointLog::add([
            'username' => $user['phone'],
            'operator' => '--',
            'deposit' => (int)$point,
            'expend' => '-',
            'note' => "订单号【".$order['order_sn']."】确认收货"."获取" . (int)$point ."睿积分",
            'userid' => $user['id'],
            'order_sn' => $order['order_sn']

        ]);
        //更新用户积分
        return $user->setInc('point', (int)$point);
    }

    /**
     * 分销佣金
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-15
     * Time: 10:07
     */
    private function addFxMoney($order_sn){
        $model = new FxOrder();
        return $model->getFxMoney($order_sn);
    }
}
