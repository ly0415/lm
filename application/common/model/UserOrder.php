<?php

namespace app\common\model;

use think\Hook;
use think\Config;
use think\Db;
use app\common\model\store\shop\Order as ShopOrder;
use app\common\service\Order as OrderService;
use app\common\library\helper;

/**
 * 用户订单模型
 * Class Order
 * @package app\common\model
 */
class UserOrder extends BaseModel
{
    protected $name = 'user_order';


}
