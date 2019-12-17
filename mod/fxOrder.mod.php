<?php
/**
 * 分销订单模型
 * @author: wanyan
 * @date: 2017/11/20
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class FxOrderMod extends BaseMod
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct("fx_order");
    }

    /**
     * 订单来源
     *
     * @var array
     */
    public $source = array(
        '1' => '代客下单',
        '2' => '微信下单',
        '3' => '前台下单',
        '4' => '小程序下单',
    );

    public $order_state = array(
        '10' => '未入账',
        '20' => '未入账',
        '30' => '未入账',
        '40' => '未入账',
        '50' => '已入账',
        '0' => '订单已取消',
    );

    /**
     * 收益状态
     * @var array
     */
    public $state = array(
        '0' => '未入账',
        '1' => '已入账'
    );
    /**
     * 根据会员ID获取默认分销人员信息(分销号)
     * @params  $user_id      会员ID
     * @author: luffy
     * @date  : 2018-10-17
     */
    public function getFxCode($user_id)
    {
        //获取最新订单分销人员
        $order_info = $this->getOne(array(
            'cond' => ' user_id = ' . $user_id,
            'order_by' => ' id DESC '
        ));

        //分销人员存在则返回
        $fxuserMod = &m('fxuser');
        $fxUserInfo = $fxuserMod->getRow($order_info['fx_user_id']);

        if ($fxUserInfo['mark'] == 0 || $fxUserInfo['is_check'] != 2) {
            return false;
        }
        return $fxUserInfo;
    }

    /**
     * 插入分销订单表
     * @param $main_rs  订单id
     * @param $orderNo  订单号
     * @param $source 订单来源1web2微信
     * @param $fx_user_id  分销人员的序号
     * @param $rule_id   权限规则的序号
     * @param $store_cate  区域店铺类别序号
     * @param $store_id   区域店铺id
     * @param $userId   操作人的用户id
     * @param $paymoney 实际支付金额
     * @author tangp
     * @date 2018-10-17
     */
    public function insertFxOrder($main_rs, $orderNo, $source, $fx_money, $userId, $fx_user_id, $rule_id, $store_cate, $store_id, $userId, $paymoney)
    {
        $fxOrderData = array(
            'order_id' => $main_rs,
            'order_sn' => $orderNo,
            'source' => $source,
            'fx_money' => $fx_money,
            'user_id' => $userId,
            'fx_user_id' => $fx_user_id,
            'rule_id' => $rule_id,
            'store_cate' => $store_cate,
            'store_id' => $store_id,
            'add_time' => time(),
            'add_user' => $userId,
            'pay_money' => $paymoney
        );
        $data = $this->getOne(array('cond' => "order_id={$main_rs}"));
        if (empty($data)) {
            $res = $this->doInsert($fxOrderData);
        } else {
            $res = $this->doEdit($data['id'], $fxOrderData);
        }
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 根据订单编号，插入分销订单
     * $order_sn:订单编号
     * $source:订单来源
     */
    public function addFxOrderByOrderSn($order_sn, $source)
    {
        //获取订单信息
        $orderMod = &m('order');

        $orderInfo = $orderMod->getOne(array('cond' => "order_sn='{$order_sn}'"));
        if ($orderInfo['fx_user_id'] > 0) {
            //购买单个商品使用兑换券，不生成分销订单
            if (($orderInfo['order_amount'] <= 0) && ($orderInfo['cid'] > 0)) {
                //获取订单商品信息
                $sql = "select order_id from bs_order_goods where order_id = '{$order_sn}'";
                $orderGoodsInfo = $this->querySql($sql);
                //获取劵信息
                $couponMod = &m('coupon');
                $couponInfo = $couponMod->getOne(array('cond' => "id={$orderInfo['cid']}"));
                if (count($orderGoodsInfo) <= 1 && ($couponInfo['type'] == 2)) {
                    return true;
                }
            }
            //获取分销比例
            $fxuserMod = &m('fxuser');
            $fxuserInfo = $fxuserMod->getOne(array('cond' => "id={$orderInfo['fx_user_id']}"));
            //获取一级分销信息
            $fxPInfo = $fxuserMod->getLevel1Info($fxuserInfo['id']);
            $fxRuleMod = &m('fxrule');
            $fxRuleInfo = $fxRuleMod->getOne(array('cond' => "id={$fxPInfo['rule_id']}"));
            $fxCommissionPercent = $fxRuleInfo['lev3_prop'] - $fxuserInfo['discount'];
            //获取店铺信息
            $storeMod = &m('store');
            $storeInfo = $storeMod->getOne(array('cond' => "id={$orderInfo['store_id']}"));
            $fxOrderData = array(
                'order_id' => $orderInfo['order_id'],
                'order_sn' => $order_sn,
                'pay_money' => $orderInfo['order_amount'],
                'fx_money' => number_format(($orderInfo['goods_amount']-$orderInfo['cp_amount']-$orderInfo['pd_amount']) * $fxuserInfo['discount'] * 0.01, 2, '.', ''),
                'source' => $source,
                'user_id' => $orderInfo['buyer_id'],
                'fx_user_id' => $orderInfo['fx_user_id'],
                'rule_id' => $fxPInfo['rule_id'],
                'store_cate' => $storeInfo['store_cate_id'],
                'store_id' => $orderInfo['store_id'],
                'add_time' => time(),
                'add_user' => $orderInfo['buyer_id'],
                'fx_discount'=>$fxuserInfo['discount'],
                'fx_commission_percent'=>$fxCommissionPercent,
                'fx_commission' => number_format($orderInfo['order_amount'] * $fxCommissionPercent * 0.01, 2, '.', ''),
                'fx_commission_1' => number_format($orderInfo['order_amount'] * $fxRuleInfo['lev1_prop'] * 0.01, 2, '.', ''),
                'fx_commission_2' => number_format($orderInfo['order_amount'] * $fxRuleInfo['lev2_prop'] * 0.01, 2, '.', '')
            );
            //获取分销订单信息，用来判断是否已经存在
            $fxOrderInfo = $this->getOne(array('cond' => "order_sn='{$order_sn}'"));
            if (empty($fxOrderInfo)) {
                $this->doInsert($fxOrderData);
            } else {
                $this->doEdit($fxOrderInfo['id'], $fxOrderData);
            }
	   $type = 9;
            //查询当前会员是否绑定分销人员
            $fxUserAccountMod = &m('fxUserAccount');
            $fxAccountInfo = $fxUserAccountMod->getOne(array(
                'cond'   => ' user_id = '.$orderInfo['buyer_id']
            ));
            if($source == 1){
                if(empty($fxAccountInfo)){
                    $type = 5;
                }elseif($fxAccountInfo && ($fxAccountInfo['fx_user_id'] != $orderInfo['fx_user_id'])){
                    $type = 6;
                }
            }elseif($source == 2){
                if(empty($fxAccountInfo)){
                    $type = 7;
                }elseif($fxAccountInfo && ($fxAccountInfo['fx_user_id'] != $orderInfo['fx_user_id'])){
                    $type = 8;
                }
            }
            //插入分销人员对应会员表
            $fxUserAccountMod = &m('fxUserAccount');
            $fxUserAccountMod->addFxUser($orderInfo['fx_user_id'], $orderInfo['buyer_id'], $type);  
        }
        return true;
    }

    /**
     * 计算分销优惠金额
     * @author zhangkx
     * @date 2019/1/18
     * @param $payAmount
     * @param $fxUserId
     * @return float|int
     */
    public function calFxMoney($payAmount, $fxUserId)
    {
        //获取分销人员比例
        $fxUserMod = &m('fxuser');
        $fxRuleMod = &m('fxrule');
        $userData = $fxUserMod->getRow($fxUserId);
        $ruleData = $fxRuleMod->getRow($userData['rule_id']);

        if ($userData['discount'] > 0) {
            $discount = $ruleData['lev3_prop'] - $userData['discount'];
        } else {
            $discount = $userData['discount'];
        }
        $money = number_format(($payAmount * ($discount / 100)), 2);
        $result = array(
            'money' => $money,
            'discount' => $discount
        );
        return $result;
    }
    //获取类型
    public function getRoomType($order_sn)
    {
        $sql = "SELECT goods_id FROM bs_order_goods WHERE order_id=".$order_sn;
        $orderGoodsMod = &m('orderGoods');
        $storeGoodsMod = &m('storeGoods');
        $roomTypeLangMod = &m('roomTypeLang');
        $res = $orderGoodsMod->querySql($sql);
        foreach ($res as $key => $val){
            $arr[] = $val['goods_id'];
        }
        $arrs = implode(',',$arr);
        $sqls = "SELECT room_id FROM bs_store_goods WHERE id in (".$arrs.")";
        $result = $storeGoodsMod->querySql($sqls);
        foreach ($result as $k => $v){
            $array[] = $v['room_id'];
        }
        $arrays = implode(',',$array);
        $sqlss = "SELECT type_name FROM bs_room_type_lang WHERE type_id in (".$arrays.") AND lang_id=29";
        $rss = $roomTypeLangMod->querySql($sqlss);
        foreach ($rss as $kk => $vv){
            $rsss[] = $vv['type_name'];
        }
        $r = implode(';',$rsss);

        return $r;
    }
}