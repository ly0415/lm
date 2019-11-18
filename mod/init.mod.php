<?php
/**
 * INIT模型
 * @author  luffy
 * @date    2019-01-28
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}
class InitMod extends BaseMod{
    /**
     * init constructor.
     */
    public function __construct(){
        parent::__construct();
    }
    /**
     * 生成对应的店铺订单表
     * @param $store_id
     * @return string
     */
    public function createTable($store_id)
    {
        $res = $this->initTable($store_id);

        return $res;
    }
    /**
     * 初始化订单模型、订单详情模型、订单关联数据模型
     * @param   $store_id
     * @author  luffy
     * @date    12019-01-24
     * @return  string
     */
    public function initTable($store_id){
        $tbl_1 = DB_PREFIX.'order_'.$store_id;
        $tbl_2 = DB_PREFIX.'order_details_'.$store_id;
        $tbl_3 = DB_PREFIX.'order_relation_'.$store_id;
        if (!$this->func_table_exists($tbl_1)){
            $sql_1 = "CREATE TABLE `{$tbl_1}` (
                          `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '订单id',
                          `order_sn` varchar(20) NOT NULL DEFAULT '' COMMENT '订单编号',
                          `store_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '卖家店铺ID',
                          `buyer_id` int(11) NOT NULL DEFAULT '0' COMMENT '购买人',
                          `goods_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '商品总价格',
                          `order_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '订单总价格/实付金额',
                          `order_state` tinyint(4) NOT NULL DEFAULT '10' COMMENT '订单状态（0、已取消 10、未付款 20、已付款 30、已发货 40、区域配送 50、已收货 60、已退款 70、已退款）',
                          `sendout` tinyint(1) NOT NULL DEFAULT '1' COMMENT '配送方式（1、自提 2、配送  3、邮寄托运） ',
                          `evaluation_state` tinyint(1) NOT NULL DEFAULT '0' COMMENT '评价状态（0、未评价 1、已评价 2、已过期未评价）',
                          `source` tinyint(1) NOT NULL DEFAULT '0' COMMENT '下单来源（1、小程序 2、公众号 3、代课下单 4、PC前台下单）',
                          `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '订单生成时间',
                          `mark` tinyint(2) NOT NULL DEFAULT '1' COMMENT '软删除（1、未删除  2、已删除）',
                          PRIMARY KEY (`id`),
                          KEY `order_sn` (`order_sn`) USING BTREE
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单主表'";
            $this->exeSql($sql_1);
            $path_1 = ROOT_PATH."/mod/order{$store_id}.mod.php";
            $tbl_1=substr($tbl_1,strlen(DB_PREFIX));//去除表前缀
            if (!file_exists($path_1)){
                $myfile_1 = fopen($path_1,"w");
                $txt_1 = "
                <?php
                    /**
                     * 店铺订单模型
                     * @author: luffy
                     * @date  : 2019-01-28
                     */
                     if(!defined('IN_ECM')) {die('Forbidden');}
                     class order{$store_id}Mod extends BaseMod {
                         /**
                         * 构造函数
                         */
                        public function __construct() {
                            parent::__construct(\"{$tbl_1}\");
                        }
                     }
                ?>";
                fwrite($myfile_1,$txt_1);
                fclose($myfile_1);
            }
        }
        if(!$this->func_table_exists($tbl_2)){
            $sql_2 = "CREATE TABLE `{$tbl_2}` (
                          `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '订单详情id',
                          `order_id` int(11) NOT NULL,
                          `pay_sn` varchar(32) NOT NULL DEFAULT '' COMMENT '支付单号',
                          `discount_num` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠打折',
                          `discount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '优惠金额（可为负数）',
                          `fx_user_id` int(10) NOT NULL DEFAULT '0' COMMENT '三级分销人员ID',
                          `fx_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '分销给用户优惠的金额',
                          `point_discount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户使用积分优惠的金额',
                          `coupon_discount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '用户使用抵扣券优惠的金额',
                          `delivery_lal` varchar(20) NOT NULL DEFAULT '' COMMENT '经纬度',
                          `delivery` varchar(100) NOT NULL DEFAULT '' COMMENT '配送地址',
                          `shipping_fee` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '运费',
                          `seller_msg` varchar(255) NOT NULL DEFAULT '' COMMENT '订单留言',
                          `warning_tone` tinyint(1) NOT NULL DEFAULT '1' COMMENT '提示音 （ 1 、未提示  2 、已提示 ）',
                          `address_id` int(11) NOT NULL DEFAULT '0' COMMENT '收货地址ID',
                          `sendout_time` int(11) NOT NULL DEFAULT '0' COMMENT '自提时间',
                          `valet_order_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '代客下单人员ID',
                          `valet_order_time` int(11) NOT NULL DEFAULT '0' COMMENT '代客下单时间',
                          `number_order` varchar(255) NOT NULL COMMENT '取货码',
                          KEY `order_id` (`order_id`) USING BTREE,
                          PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单详情表'";
            $this->exeSql($sql_2);
            $path_2 = ROOT_PATH."/mod/orderDetails{$store_id}.mod.php";
            $tbl_2=substr($tbl_2,strlen(DB_PREFIX));//去除表前缀
            if(!file_exists($path_2)){
                $myfile_2 = fopen($path_2, "w");
                $txt_2 = "
                <?php
                    /**
                     * 店铺订单详情模型
                     * @author: luffy
                     * @date  : 2019-01-28
                     */
                    if (!defined('IN_ECM')) { die('Forbidden'); }
                    class orderDetails{$store_id}Mod extends BaseMod {
                        /**
                         * 构造函数
                         */
                        public function __construct() {
                            parent::__construct(\"{$tbl_2}\");
                        }
                    }
                ?>";
                fwrite($myfile_2, $txt_2);
                fclose($myfile_2);
            }
        }
        if(!$this->func_table_exists($tbl_3)){
            $sql_3 = "CREATE TABLE `{$tbl_3}` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单ID',
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
                          KEY `order_id` (`order_id`) USING BTREE
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单关联表'";
            $this->exeSql($sql_3);
            $path_3 = ROOT_PATH."/mod/orderRelation{$store_id}.mod.php";
            $tbl_3=substr($tbl_3,strlen(DB_PREFIX));//去除表前缀
            if(!file_exists($path_3)){
                $myfile_3 = fopen($path_3, "w");
                $txt_3 = "
                <?php
                    /**
                     * 店铺订单关联模型
                     * @author: luffy
                     * @date  : 2019-01-28
                     */
                    if (!defined('IN_ECM')) { die('Forbidden'); }
                    class orderRelation{$store_id}Mod extends BaseMod {
                        /**
                         * 构造函数
                         */
                        public function __construct() {
                            parent::__construct(\"{$tbl_3}\");
                        }
                    }
                ?>";
                fwrite($myfile_3, $txt_3);
                fclose($myfile_3);
            }
        }
        return $tbl_1;
    }
}