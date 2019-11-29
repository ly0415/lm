<?php

namespace app\store\model\store;

use app\common\model\Store as StoreModel;

/**
 * 商家门店模型
 * Class Shop
 * @package app\store\model\store
 */
class Shop extends StoreModel
{
    /**
     * 获取门店列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-16
     * Time: 12:08
     */
    public function getList($status = null,$where=[])
    {
        !is_null($status) && $this->where('status', '=', (int)$status);
        return $this->where($where)
            ->where('store_cate_id','=',STORE_CATE)
            ->order(['is_open'=>'ASC', 'sort' => 'asc', 'add_time' => 'desc'])
            ->paginate(15, false, ['query' => \request()->request()])
            ->each(function ($value){
                return $this->toSwitch($value);
            });
    }

    /**
     * 数据转换
     * @author  luffy
     * @date    2019-08-27
     */
    public function toSwitch($value){
        $value['format_store_address']   = explode('_', $value['store_address']);
        $value['format_is_open']         = $this->is_open[$value['is_open']];
        $value['format_background_img']  = unserialize($value['background_img']);
        return $value;
    }

    /**
     * 获取门店详细信息
     * @author  luffy
     * @date    2019-09-05
     */
    public function detail($store_id){
        $info   = self::getCacheAll()[$store_id];
        return $this->toSwitch($info);
    }

    /**
     * 新增记录
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function add($data)
    {
        if (!$this->validateForm($data)) {
            return false;
        }
        // 开启事务
        $this->startTrans();
        try {
            // 添加门店
            $this->allowField(true)->save($this->createData($data));
            $this->initTable($this->id);
            $this->commit();
            //重置缓存
            $this->resetCache();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 编辑记录
     * @param $data
     * @return false|int
     */
    public function edit($data)
    {
        if (!$this->validateForm($data)) {
            return false;
        }
        // 开启事务
        $this->startTrans();
        try {
            // 编辑门店
            $this->allowField(true)->save($this->createData($data),['id' => $data['id']]);
            $this->commit();
            //重置缓存
            $this->resetCache();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 电子围栏设置
     * @author  luffy
     * @date    2019-10-22
     */
    public function edit_ef($data){
        if(!isset($data['delivery_area']) || empty($data['delivery_area'])){
            $this->error = '请划定围栏区域';
            return false;
        }
        // 开启事务
        $this->startTrans();
        try {
            // 编辑门店
            $this->allowField(true)->save(['delivery_area'=>$data['delivery_area']],['id' => $data['store_id']]);
            $this->commit();
            //重置缓存
            $this->resetCache();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 商品上下架
     * @author  luffy
     * @date    2019-09-09
     */
    public function setOpen($state, $store_id){
        return $this->allowField(['is_open'])->save(['is_open' => $state ? 1 : 2], ['id' => $store_id]) !== false;
    }

    /**
     * 创建数据
     * @param array $data
     * @return array
     */
    private function createData($data)
    {
        $data['store_address'] = $data['province_id'].'_'.$data['city_id'].'_'.$data['region_id'];
        // 格式化坐标信息
        $coordinate = explode(',', $data['coordinate']);
        $data['latitude'] = $coordinate[0];
        $data['longitude'] = $coordinate[1];
        $data['is_site'] = 2;
        $data['store_cate_id'] = 17;
        $data['lang_id'] = 29;
        //添加链接地址  ly
        $data['background_img'] = serialize(array_map(function ($img,$url) {
            return [
                'background'  => $img,
                'activity_id' => 0,
                'url'         => $url

            ];
        }, $data['background_img'],$data['url']));
        unset($data['url']);
        return $data    ;
    }

    /**
     * 表单验证
     * @param $data
     * @return bool
     */
    private function validateForm($data)
    {
            if(!isset($data['store_type']) || empty($data['store_type'])){
                $this->error = '请选择站点参照类型';
                return false;
            }
            if(!isset($data['store_name']) || empty($data['store_name'])){
                $this->error = '请填写门店名称';
                return false;
            }
            if (!isset($data['logo']) || empty($data['logo'])) {
            $this->error = '请上传门店logo';
            return false;
            }
            if(!isset($data['background_img']) || empty($data['background_img'])){
                $this->error = '请上传门店轮播图';
                return false;
            }
            if(!isset($data['store_mobile']) || empty($data['store_mobile'])){
                $this->error = '请填写门店联系电话';
                return false;
            }
            if(!isset($data['store_start_time']) || empty($data['store_start_time'])){
                $this->error = '请填写营业开始时间';
                return false;
            }
            if(!isset($data['store_end_time']) || empty($data['store_start_time'])){
                $this->error = '请填写营业结束时间';
                return false;
            }
            if(!isset($data['business_id']) || empty($data['business_id'])){
                $this->error = '请选择店铺业务类型';
                return false;
            }
            if(!isset($data['province_id']) || empty($data['province_id']) || !isset($data['city_id']) || empty($data['city_id']) || !isset($data['region_id']) || empty($data['region_id'])){
                $this->error = '请选择门店地址';
                return false;
            }
            if(!isset($data['addr_detail']) || empty($data['addr_detail'])){
                $this->error = '请填写门店详细地址';
                return false;
            }
            if(!isset($data['coordinate']) || empty($data['coordinate'])){
                $this->error = '请设置门店坐标';
                return false;
            }
        return true;
    }

    /**
     * 生成对应的店铺订单表
     * @author  luffy
     * @date    2019-09-09
     */
    public function createTable($store_id){
        $res = $this->initTable($store_id);
        return $res;
    }
    /**
     * 初始化订单模型、订单详情模型、订单关联数据模型
     * @author  luffy
     * @date    2019-09-09
     */
    public function initTable($store_id){
        $prefix = config('database.prefix');
        $tbl_1 = $prefix.'order_'.$store_id;
        $tbl_2 = $prefix.'order_details_'.$store_id;
        $tbl_3 = $prefix.'order_relation_'.$store_id;
        if ($this->func_table_exists($tbl_1) === FALSE){
            $sql_1 = "CREATE TABLE `{$tbl_1}` (
                          `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '订单id',
                          `order_sn` varchar(20) NOT NULL DEFAULT '' COMMENT '订单编号',
                          `store_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '卖家店铺ID',
                          `buyer_id` int(11) NOT NULL DEFAULT '0' COMMENT '购买人',
                          `goods_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '商品总价格',
                          `order_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '订单总价格/实付金额',
                          `order_state` tinyint(4) NOT NULL DEFAULT '10' COMMENT '订单状态（0、已取消 10、未付款 20、已付款  25、已接单  30、已发货 40、区域配送 50、已收货 60、退款中 70、已退款）',
                          `sendout` varchar(100) NOT NULL DEFAULT '' COMMENT '配送方式（1、自提 2、配送  3、邮寄托运 4、海外代购） ',
                          `evaluation_state` tinyint(1) NOT NULL DEFAULT '0' COMMENT '评价状态（0、未评价 1、已评价 2、已过期未评价）',
                          `source` tinyint(1) NOT NULL DEFAULT '0' COMMENT '下单来源（1、小程序 2、公众号 3、代课下单 4、PC前台下单）',
                          `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '订单生成时间',
                          `mark` tinyint(2) NOT NULL DEFAULT '1' COMMENT '软删除（1、未删除  2、已删除）',
                          PRIMARY KEY (`id`),
                          KEY `order_sn` (`order_sn`) USING BTREE,
                          KEY `buyer_id` (`buyer_id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单主表'";
            $this->execute($sql_1);
        }
        if(!$this->func_table_exists($tbl_2)){
            $sql_2 = "CREATE TABLE `{$tbl_2}` (
                          `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '订单详情id',
                          `order_id` int(11) NOT NULL,
                          `order_sn` varchar(20) NOT NULL DEFAULT '' COMMENT '订单编号',
                          `pay_sn` varchar(32) NOT NULL DEFAULT '' COMMENT '支付单号',
                          `discount_num` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠打折',
                          `discount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠金额（可为负数）',
                          `fx_user_id` int(10) NOT NULL DEFAULT '0' COMMENT '三级分销人员ID',
                          `fx_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '分销给用户优惠的金额',
                          `point_discount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户使用积分优惠的金额',
                          `coupon_discount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户使用抵扣券优惠的金额',
                          `delivery` text COMMENT '配送信息',
                          `shipping_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '运费',
                          `seller_msg` varchar(255) NOT NULL DEFAULT '' COMMENT '订单留言',
                          `warning_tone` tinyint(1) NOT NULL DEFAULT '1' COMMENT '提示音 （ 1 、未提示  2 、已提示 ）',
                          `address_id` int(11) NOT NULL DEFAULT '0' COMMENT '收货地址ID',
                          `sendout_time` int(11) NOT NULL DEFAULT '0' COMMENT '自提时间',
                          `valet_order_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '代客下单人员ID',
                          `valet_order_time` int(11) NOT NULL DEFAULT '0' COMMENT '代客下单时间',
                          `number_order` varchar(255) NOT NULL DEFAULT '' COMMENT '取货码',
                          `clickandview` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否处理（1、未处理的订单  2、已经处理的订单）',
                          PRIMARY KEY (`id`),
                          KEY `order_id` (`order_id`),
                          KEY `address_id` (`address_id`),
                          KEY `order_sn` (`order_sn`) USING BTREE 
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单详情表'";
            $this->execute($sql_2);
        }
        if(!$this->func_table_exists($tbl_3)){
            $sql_3 = "CREATE TABLE `{$tbl_3}` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单ID',
                          `order_sn` varchar(20) NOT NULL DEFAULT '' COMMENT '订单编号',
                          `cancel_time` int(11) NOT NULL DEFAULT '0' COMMENT '取消时间',
                          `payment_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '支付方式（1、支付宝支付  2、微信支付  3、余额支付  4、线下支付  5、免费兑换）',
                          `payment_source` int(11) NOT NULL DEFAULT '0' COMMENT '线下付款来源ID',
                          `payment_time` int(11) NOT NULL DEFAULT '0' COMMENT '付款时间',
                          `receive_user` int(11) NOT NULL DEFAULT '0' COMMENT '接单店员ID',
                          `receive_time` int(11) NOT NULL DEFAULT '0' COMMENT '接单时间ID',
                          `ship_time` int(11) NOT NULL DEFAULT '0' COMMENT '发货时间',
                          `delivery_time` int(11) NOT NULL DEFAULT '0' COMMENT '配送时间',
                          `receipt_time` int(11) NOT NULL DEFAULT '0' COMMENT '收货时间',
                          `receipt_time_difference` int(11) NOT NULL DEFAULT '0' COMMENT '时差（收货时间-付款时间）',
                          `receipt_source` tinyint(1) NOT NULL DEFAULT '0' COMMENT '收货来源（1、自动脚本 2、小程序  3、公众号）',
                          `refund_time` int(11) NOT NULL DEFAULT '0' COMMENT '退款时间',
                          `refund_source` tinyint(1) NOT NULL DEFAULT '0' COMMENT '退款来源（1、小程序  2、公众号）',
                          `refund_review_time` int(11) NOT NULL DEFAULT '0' COMMENT '退款审核时间',
                          `refund_review_user` int(11) NOT NULL DEFAULT '0' COMMENT '退款审核人员ID',
                          `comment_time` int(11) NOT NULL DEFAULT '0' COMMENT '评论时间',
                          `comment_source` tinyint(1) NOT NULL DEFAULT '0' COMMENT '评论来源（1、小程序  2、公众号）',
                          `is_instead_pay` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否代付流程 （1、亲友代付  0、否）',
                          PRIMARY KEY (`id`),
                          KEY `order_sn` (`order_sn`) USING BTREE,
                          KEY `order_id` (`order_id`) USING BTREE
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单关联表'";
            $this->execute($sql_3);
        }
        return $tbl_1;
    }

    /**
     * 校验表是否存在
     * @author  luffy
     * @date    2019-09-09
     */
    public function func_table_exists($store_id){
        $tableName  = config('database.prefix').'order_details_'.$store_id;
        $isTable    = $this->query('SHOW TABLES LIKE '."'".$tableName."'");
        return (!empty($isTable) ? TRUE: False);
    }


}