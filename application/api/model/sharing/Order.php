<?php

namespace app\api\model\sharing;

use app\common\model\sharing\Order as OrderModel;

use app\api\model\Setting as SettingModel;
use app\api\model\store\Shop as ShopModel;
use app\api\model\UserCoupon as UserCouponModel;
use app\api\model\dealer\Order as DealerOrderModel;
use app\api\model\GoodsSku as GoodsSkuModel;
use app\api\model\sharing\Goods as GoodsModel;
use app\api\model\sharing\Setting as SharingSettingModel;
use app\api\model\sharing\OrderGoods as OrderGoodsModel;

use app\api\service\Payment as PaymentService;

use app\common\enum\order\PayStatus as PayStatusEnum;
use app\common\enum\OrderType as OrderTypeEnum;
use app\common\enum\DeliveryType as DeliveryTypeEnum;
use app\common\enum\order\PayType as PayTypeEnum;
use app\common\service\delivery\Express as ExpressService;

use app\common\exception\BaseException;


/**
 * 拼团订单模型
 * Class Order
 * @package app\api\model
 */
class Order extends OrderModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
        'update_time'
    ];

    /**
     * 订单确认
     * @param \app\api\model\User $user
     * @param int $order_type 订单类型 (10单独购买 20拼团)
     * @param int $goods_id 商品id
     * @param int $goods_num
     * @param int $goods_sku_id
     * @param int $delivery 配送方式
     * @param int $pay_type 支付方式
     * @param int $shop_id 自提门店id
     * @return array
     * @throws BaseException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function getBuyNow(
        $user,
        $order_type,
        $goods_id,
        $goods_num,
        $goods_sku_id,
        $delivery = null,
        $pay_type = PayTypeEnum::WECHAT,
        $shop_id = 0
    )
    {
        // 商品信息
        /* @var GoodsModel $goods */
        $goods = GoodsModel::detail($goods_id);
        // 判断商品是否下架
        if (!$goods || $goods['is_delete'] || $goods['goods_status']['value'] != 10) {
            throw new BaseException(['msg' => '很抱歉，商品信息不存在或已下架']);
        }
        // 商品sku信息
        $goods['goods_sku'] = $goods->getGoodsSku($goods_sku_id);
        // 判断商品库存
        if ($goods_num > $goods['goods_sku']['stock_num']) {
            $this->setError('很抱歉，商品库存不足');
        }
        // 返回的数据
        $returnData = [];
        // 商品单价 (根据order_type判断单买还是拼单)
        // order_type：下单类型 10=>单独购买 20=>拼团
        $goods['goods_price'] = $order_type == 10 ? $goods['goods_sku']['goods_price']
            : $goods['goods_sku']['sharing_price'];
        // 商品总价
        $goods['total_num'] = $goods_num;
        $goods['total_price'] = $goodsTotalPrice = bcmul($goods['goods_price'], $goods_num, 2);
        // 商品详情
        $goodsList = [$goods->toArray()];
        // 默认配送方式
        !$delivery && $delivery = current(SettingModel::getItem('store')['delivery_type']);
        // 处理配送方式
        if ($delivery == DeliveryTypeEnum::EXPRESS) {
            $this->orderExpress($returnData, $user, $goodsList, $goodsTotalPrice);
        } elseif ($delivery == DeliveryTypeEnum::EXTRACT) {
            $shop_id > 0 && $returnData['extract_shop'] = ShopModel::detail($shop_id);
        }
        // 可用优惠券列表
        if (SharingSettingModel::getItem('basic')['is_coupon']) {
            $returnData['coupon_list'] = UserCouponModel::getUserCouponList($user['user_id'], $goodsTotalPrice);
        }
        return array_merge([
            'order_type' => $order_type,                // 订单类型
            'goods_list' => array_values($goodsList),   // 商品详情
            'order_total_num' => $goods_num,            // 商品总数量
            'order_total_price' => $goodsTotalPrice,    // 商品总金额 (不含运费)
            'order_pay_price' => $goodsTotalPrice,      // 订单总金额 (含运费)
            'delivery' => $delivery,                    // 配送类型
            'coupon_list' => [],                        // 优惠券列表
            'address' => $user['address_default'],      // 默认地址
            'exist_address' => !$user['address']->isEmpty(),     // 是否存在收货地址
            'express_price' => '0.00',      // 配送费用
            'intra_region' => true,         // 当前用户收货城市是否存在配送规则中
            'extract_shop' => [],           // 自提门店信息
            'pay_type' => $pay_type,        // 支付方式
            'has_error' => $this->hasError(),
            'error_msg' => $this->getError(),
        ], $returnData);
    }

    /**
     * 订单配送-快递配送
     * @param $returnData
     * @param $user
     * @param $goodsList
     * @param $goodsTotalPrice
     */
    private function orderExpress(&$returnData, $user, $goodsList, $goodsTotalPrice)
    {
        // 当前用户收货城市id
        $cityId = $user['address_default'] ? $user['address_default']['city_id'] : null;
        // 初始化配送服务类
        $ExpressService = new ExpressService(
            static::$wxapp_id,
            $cityId,
            $goodsList,
            OrderTypeEnum::SHARING
        );
        // 获取不支持当前城市配送的商品
        $notInRuleGoods = $ExpressService->getNotInRuleGoods();
        // 验证商品是否在配送范围
        $intraRegion = $returnData['intra_region'] = $notInRuleGoods === false;
        if ($intraRegion == false) {
            $notInRuleGoodsName = $notInRuleGoods['goods_name'];
            $this->setError("很抱歉，您的收货地址不在商品 [{$notInRuleGoodsName}] 的配送范围内");
        } else {
            // 计算配送金额
            $ExpressService->setExpressPrice();
        }
        // 订单总运费金额
        $expressPrice = $returnData['express_price'] = $ExpressService->getTotalFreight();
        // 订单总金额 (含运费)
        $returnData['order_pay_price'] = bcadd($goodsTotalPrice, $expressPrice, 2);
    }

    /**
     * 创建新订单
     * @param \app\api\model\User $user
     * @param array $order 订单信息
     * @param string $linkman 联系人姓名
     * @param string $phone 联系电话
     * @param int $active_id 拼单id
     * @param int $coupon_id 用户优惠券id
     * @param string $remark 买家留言
     * @return bool
     * @throws \think\exception\DbException
     * @throws \Exception
     */
    public function createOrder(
        $user,
        $order,
        $linkman,
        $phone,
        $active_id = null,
        $coupon_id = null,
        $remark = ''
    )
    {
        // 如果是参与拼单，则记录拼单id
        $order['active_id'] = $active_id > 0 ? $active_id : 0;
        // 表单验证
        if (!$this->validateOrderForm($user, $order, $linkman, $phone)) {
            return false;
        }
        // 创建新的订单
        $status = $this->transaction(function () use ($order, $user, $linkman, $phone, $coupon_id, $remark) {
            // 设置订单优惠券信息
            $this->setCouponPrice($order, $coupon_id);
            // 记录订单信息
            $status = $this->add($user['user_id'], $order, $remark);
            // 记录收货地址
            $order['delivery'] == DeliveryTypeEnum::EXPRESS && $this->saveOrderAddress($user['user_id'], $order['address']);
            // 记录上门自提联系方式
            $order['delivery'] == DeliveryTypeEnum::EXTRACT && $this->saveOrderExtract($linkman, $phone);
            // 保存订单商品信息
            $this->saveOrderGoods($user['user_id'], $order);
            // 更新商品库存 (针对下单减库存的商品)
            $this->updateGoodsStockNum($order['goods_list']);
            // 获取订单详情
            $detail = self::getUserOrderDetail($this['order_id'], $user['user_id']);
            // 记录分销商订单
            if (SharingSettingModel::getItem('basic')['is_dealer']) {
                DealerOrderModel::createOrder($detail, OrderTypeEnum::SHARING);
            }
            return $status;
        });
        // 余额支付标记订单已支付
        if ($status && $order['pay_type'] == PayTypeEnum::BALANCE) {
            $this->paymentByBalance($this['order_no']);
        }
        return $status;
    }

    /**
     * 构建微信支付请求
     * @param \app\api\model\User $user
     * @return array
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    public function paymentByWechat($user)
    {
        return PaymentService::wechat(
            $user,
            $this['order_id'],
            $this['order_no'],
            $this['pay_price'],
            OrderTypeEnum::SHARING
        );
    }

    /**
     * 余额支付标记订单已支付
     * @param string $orderNo 订单号
     * @return bool
     * @throws BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function paymentByBalance($orderNo)
    {
        // 获取订单详情
        $model = new \app\task\model\sharing\Order;
        $order = $model->payDetail($orderNo);
        // 发起余额支付
        $status = $order->paySuccess(PayTypeEnum::BALANCE);
        if (!$status) {
            $this->error = $order->error;
        }
        return $status;
    }

    /**
     * 表单验证 (订单提交)
     * @param \app\api\model\User $user 用户信息
     * @param array $order 订单信息
     * @return bool
     * @throws \think\exception\DbException
     */
    private function validateOrderForm($user, &$order, $linkman, $phone)
    {
        if ($order['delivery'] == DeliveryTypeEnum::EXPRESS) {
            if (empty($order['address'])) {
                $this->error = '请先选择收货地址';
                return false;
            }
        }
        if ($order['delivery'] == DeliveryTypeEnum::EXTRACT) {
            if (empty($order['extract_shop'])) {
                $this->error = '请先选择自提门店';
                return false;
            }
            if (empty($linkman) || empty($phone)) {
                $this->error = '请填写联系人和电话';
                return false;
            }
        }
        // 余额支付时判断用户余额是否足够
        if ($order['pay_type'] == PayTypeEnum::BALANCE) {
            if ($user['balance'] < $order['order_pay_price']) {
                $this->error = '用户余额不足，无法使用余额支付';
                return false;
            }
        }
        // 验证拼单id是否合法
        if ($order['active_id'] > 0) {
            // 拼单详情
            $detail = Active::detail($order['active_id']);
            if (empty($detail)) {
                $this->error = '很抱歉，拼单不存在';
                return false;
            }
            // 验证当前拼单是否允许加入新成员
            if (!$detail->checkAllowJoin()) {
                $this->error = $detail->getError();
                return false;
            }
        }
        return true;
    }

    /**
     * 验证拼单是否允许加入
     * @param $active_id
     * @return bool
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    public function checkActiveIsAllowJoin($active_id)
    {
        // 拼单详情
        $detail = Active::detail($active_id);
        if (!$detail) {
            throw new BaseException('很抱歉，拼单不存在');
        }
        // 验证当前拼单是否允许加入新成员
        return $detail->checkAllowJoin();
    }

    /**
     * 设置订单优惠券信息
     * @param $order
     * @param $coupon_id
     * @return bool
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    private function setCouponPrice(&$order, $coupon_id)
    {
        if ($coupon_id > 0 && !empty($order['coupon_list'])) {
            // 获取优惠券信息
            $couponInfo = [];
            foreach ($order['coupon_list'] as $coupon)
                $coupon['user_coupon_id'] == $coupon_id && $couponInfo = $coupon;
            if (empty($couponInfo)) throw new BaseException(['msg' => '未找到优惠券信息']);
            // 计算订单金额 (抵扣后)
            $orderTotalPrice = bcsub($order['order_total_price'], $couponInfo['reduced_price'], 2);
            $orderTotalPrice <= 0 && $orderTotalPrice = '0.01';
            // 记录订单信息
            $order['coupon_id'] = $coupon_id;
            $order['coupon_price'] = $couponInfo['reduced_price'];
            $order['order_pay_price'] = bcadd($orderTotalPrice, $order['express_price'], 2);
            // 设置优惠券使用状态
            $model = UserCouponModel::detail($coupon_id);
            $model->setIsUse();
            return true;
        }
        $order['coupon_id'] = 0;
        $order['coupon_price'] = 0.00;
        return true;
    }

    /**
     * 新增订单记录
     * @param $user_id
     * @param $order
     * @param string $remark
     * @return false|int
     */
    private function add($user_id, &$order, $remark = '')
    {
        $data = [
            'user_id' => $user_id,
            'order_type' => $order['order_type'],
            'active_id' => $order['active_id'],
            'order_no' => $this->orderNo(),
            'total_price' => $order['order_total_price'],
            'coupon_id' => $order['coupon_id'],
            'coupon_price' => $order['coupon_price'],
            'pay_price' => $order['order_pay_price'],
            'delivery_type' => $order['delivery'],
            'pay_type' => $order['pay_type'],
            'buyer_remark' => trim($remark),
            'wxapp_id' => self::$wxapp_id,
        ];
        if ($order['delivery'] == DeliveryTypeEnum::EXPRESS) {
            $data['express_price'] = $order['express_price'];
        } elseif ($order['delivery'] == DeliveryTypeEnum::EXTRACT) {
            $data['extract_shop_id'] = $order['extract_shop']['shop_id'];
        }
        return $this->save($data);
    }

    /**
     * 保存订单商品信息
     * @param $user_id
     * @param $order
     * @return int
     */
    private function saveOrderGoods($user_id, &$order)
    {
        // 订单商品列表
        $goodsList = [];
        // 订单商品实付款金额 (不包含运费)
        $realTotalPrice = bcsub($order['order_pay_price'], $order['express_price'], 2);
        foreach ($order['goods_list'] as $goods) {
            /* @var Goods $goods */
            // 计算商品实际付款价
            $total_pay_price = $realTotalPrice * $goods['total_price'] / $order['order_total_price'];
            $goodsList[] = [
                'user_id' => $user_id,
                'wxapp_id' => self::$wxapp_id,
                'goods_id' => $goods['goods_id'],
                'goods_name' => $goods['goods_name'],
                'image_id' => $goods['image'][0]['image_id'],
                'selling_point' => $goods['selling_point'],
                'people' => $goods['people'],
                'group_time' => $goods['group_time'],
                'is_alone' => $goods['is_alone'],
                'deduct_stock_type' => $goods['deduct_stock_type'],
                'spec_type' => $goods['spec_type'],
                'spec_sku_id' => $goods['goods_sku']['spec_sku_id'],
                'goods_sku_id' => $goods['goods_sku']['goods_sku_id'],
                'goods_attr' => $goods['goods_sku']['goods_attr'],
                'content' => $goods['content'],
                'goods_no' => $goods['goods_sku']['goods_no'],
                'goods_price' => $goods['goods_sku']['goods_price'],
                'line_price' => $goods['goods_sku']['line_price'],
                'goods_weight' => $goods['goods_sku']['goods_weight'],
                'total_num' => $goods['total_num'],
                'total_price' => $goods['total_price'],
                'total_pay_price' => sprintf('%.2f', $total_pay_price),
                'is_ind_dealer' => $goods['is_ind_dealer'],
                'dealer_money_type' => $goods['dealer_money_type'],
                'first_money' => $goods['first_money'],
                'second_money' => $goods['second_money'],
                'third_money' => $goods['third_money'],
            ];
        }
        return $this->goods()->saveAll($goodsList);
    }

    /**
     * 更新商品库存 (针对下单减库存的商品)
     * @param $goods_list
     * @throws \Exception
     */
    private function updateGoodsStockNum($goods_list)
    {
        $deductStockData = [];
        foreach ($goods_list as $goods) {
            // 下单减库存
            $goods['deduct_stock_type'] == 10 && $deductStockData[] = [
                'goods_sku_id' => $goods['goods_sku']['goods_sku_id'],
                'stock_num' => ['dec', $goods['total_num']]
            ];
        }
        !empty($deductStockData) && (new GoodsSkuModel)->isUpdate()->saveAll($deductStockData);
    }

    /**
     * 记录收货地址
     * @param $user_id
     * @param $address
     * @return false|\think\Model
     */
    private function saveOrderAddress($user_id, $address)
    {
        if ($address['region_id'] == 0 && !empty($address['district'])) {
            $address['detail'] = $address['district'] . ' ' . $address['detail'];
        }
        return $this->address()->save([
            'user_id' => $user_id,
            'wxapp_id' => self::$wxapp_id,
            'name' => $address['name'],
            'phone' => $address['phone'],
            'province_id' => $address['province_id'],
            'city_id' => $address['city_id'],
            'region_id' => $address['region_id'],
            'detail' => $address['detail'],
        ]);
    }

    /**
     * 保存上门自提联系人
     * @param $linkman
     * @param $phone
     * @return false|\think\Model
     */
    public function saveOrderExtract($linkman, $phone)
    {
        return $this->extract()->save([
            'linkman' => trim($linkman),
            'phone' => trim($phone),
            'user_id' => $this['user_id'],
            'wxapp_id' => self::$wxapp_id,
        ]);
    }

    /**
     * 用户拼团订单列表
     * @param $user_id
     * @param string $type
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($user_id, $type = 'all')
    {
        // 筛选条件
        $filter = [];
        // 订单数据类型
        switch ($type) {
            case 'all':
                // 全部
                break;
            case 'payment';
                // 待支付
                $filter['pay_status'] = PayStatusEnum::PENDING;
                break;
            case 'sharing';
                // 拼团中
                $filter['active.status'] = 10;
                break;
            case 'delivery';
                // 待发货
                $this->where('IF ( (`order`.`order_type` = 20), (`active`.`status` = 20), TRUE)');
                $filter['pay_status'] = 20;
                $filter['delivery_status'] = 10;
                break;
            case 'received';
                // 待收货
                $filter['pay_status'] = 20;
                $filter['delivery_status'] = 20;
                $filter['receipt_status'] = 10;
                break;
            case 'comment';
                $filter['order_status'] = 30;
                $filter['is_comment'] = 0;
                break;
        }
        return $this->with(['goods.image', 'active'])
            ->alias('order')
            ->field('order.*, active.status as active_status')
            ->join('sharing_active active', 'order.active_id = active.active_id', 'LEFT')
            ->where('user_id', '=', $user_id)
            ->where($filter)
            ->where('order.is_delete', '=', 0)
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 取消订单
     * @return bool|false|int
     */
    public function cancel()
    {
        if ($this['delivery_status']['value'] == 20) {
            $this->error = '已发货订单不可取消';
            return false;
        }
        if ($this['order_type']['value'] == 20) {
            $this->error = '拼团订单不允许取消';
            return false;
        }
        $this->transaction(function () {
            // 回退商品库存
            (new OrderGoodsModel)->backGoodsStock($this['goods']);
            // 更新订单状态
            $this->save(['order_status' => $this['pay_status']['value'] == PayStatusEnum::SUCCESS ? 21 : 20]);
        });
        return true;
    }

    /**
     * 确认收货
     * @return bool|mixed
     */
    public function receipt()
    {
        // 验证订单是否合法
        // 条件1: 订单必须已发货
        // 条件2: 订单必须未收货
        if ($this['delivery_status']['value'] != 20 || $this['receipt_status']['value'] != 10) {
            $this->error = '该订单不合法';
            return false;
        }
        return $this->transaction(function () {
            // 更新订单状态
            $status = $this->save([
                'receipt_status' => 20,
                'receipt_time' => time(),
                'order_status' => 30
            ]);
            // 发放分销商佣金
            DealerOrderModel::grantMoney($this, OrderTypeEnum::SHARING);
            return $status;
        });
    }

    /**
     * 获取订单总数
     * @param $user_id
     * @param string $type
     * @return int|string
     * @throws \think\Exception
     */
    public function getCount($user_id, $type = 'all')
    {
        // 筛选条件
        $filter = [];
        // 订单数据类型
        switch ($type) {
            case 'all':
                break;
            case 'payment';
                $filter['pay_status'] = PayStatusEnum::PENDING;
                break;
            case 'received';
                $filter['pay_status'] = PayStatusEnum::SUCCESS;
                $filter['delivery_status'] = 20;
                $filter['receipt_status'] = 10;
                break;
            case 'comment';
                $filter['order_status'] = 30;
                $filter['is_comment'] = 0;
                break;
        }
        return $this->where('user_id', '=', $user_id)
            ->where('order_status', '<>', 20)
            ->where($filter)
            ->where('is_delete', '=', 0)
            ->count();
    }

    /**
     * 订单详情
     * @param $order_id
     * @param $user_id
     * @return array|false|\PDOStatement|string|\think\Model|static
     * @throws BaseException
     */
    public static function getUserOrderDetail($order_id, $user_id)
    {
        $order = (new static)->with(['goods' => ['image', 'refund'], 'address', 'express', 'extract_shop'])
            ->alias('order')
            ->field('order.*, active.status as active_status')
            ->join('sharing_active active', 'order.active_id = active.active_id', 'LEFT')
            ->where([
                'order_id' => $order_id,
                'user_id' => $user_id,
//                'order_status' => ['<>', 20]
            ])->find();
        if (!$order) {
            throw new BaseException(['msg' => '订单不存在']);
        }
        return $order;
    }

    /**
     * 判断商品库存不足 (未付款订单)
     * @param $goodsList
     * @return bool
     */
    public function checkGoodsStatusFromOrder(&$goodsList)
    {
        foreach ($goodsList as $goods) {
            // 判断商品是否下架
            if (!$goods['goods'] || $goods['goods']['goods_status']['value'] != 10) {
                $this->setError('很抱歉，商品 [' . $goods['goods_name'] . '] 已下架');
                return false;
            }
            // 付款减库存
            if ($goods['deduct_stock_type'] == 20 && $goods['sku']['stock_num'] < 1) {
                $this->setError('很抱歉，商品 [' . $goods['goods_name'] . '] 库存不足');
                return false;
            }
        }
        return true;
    }

    /**
     * 当前订单是否允许申请售后
     * @return bool
     */
    public function isAllowRefund()
    {
        // 允许申请售后期限
        $refund_days = SettingModel::getItem('trade')['order']['refund_days'];
        if ($refund_days == 0) {
            return false;
        }
        if (time() > $this['receipt_time'] + ((int)$refund_days * 86400)) {
            return false;
        }
        if ($this['receipt_status']['value'] != 20) {
            return false;
        }
        return true;
    }

    /**
     * 判断当前订单是否允许核销
     * @param static $order
     * @return bool
     */
    public function checkExtractOrder(&$order)
    {
        if (
            $order['pay_status']['value'] == PayStatusEnum::SUCCESS
            && $order['delivery_type']['value'] == DeliveryTypeEnum::EXTRACT
            && $order['delivery_status']['value'] == 10
            // 拼团订单验证拼单状态
            && ($order['order_type']['value'] == 20 ? $order['active']['status']['value'] == 20 : true)
        ) {
            return true;
        }
        $this->setError('该订单不能被核销');
        return false;
    }

    /**
     * 设置错误信息
     * @param $error
     */
    private function setError($error)
    {
        empty($this->error) && $this->error = $error;
    }

    /**
     * 是否存在错误
     * @return bool
     */
    public function hasError()
    {
        return !empty($this->error);
    }

}
