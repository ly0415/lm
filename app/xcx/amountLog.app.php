<?php
/**
 * 余额历史记录
 * User: xt
 * Date: 2019/3/8
 * Time: 15:13
 */

class AmountLogApp extends BasePhApp
{
    protected $amountLogMod;
    /**
     * AmountLogApp constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->amountLogMod = &m('amountLog');
    }

    /**
     * 余额充值券充值
     * by xt 2019.03.08
     */
    public function rechargeCouponPay()
    {
        $sn = empty($_REQUEST['sn']) ? '' : htmlspecialchars(trim($_REQUEST['sn']));

        if (empty($sn)) {
            $this->setData(array(), 0, '券码不能为空');
        }

        $balanceRechargeCouponMod = &m('balanceRechargeCoupon');
        $balanceRechargeCouponData = $balanceRechargeCouponMod->getOne(array(
            'cond' => "mark = 1 and is_use = 1 and sn = '{$sn}'",
            'fields' => 'id,money',
        ));

        if (empty($balanceRechargeCouponData)) {
            $this->setData(array(), 0, '券码无效');
        }

        if($balanceRechargeCouponData['money'] == (float) 199){
                $times = $balanceRechargeCouponMod->getCount(
                array(
                'cond' => "mark = 1 and is_use = 2 and money = {$balanceRechargeCouponData['money']} and use_user = {$this->userId}"
                )
            );
            if($times >= 2){
                $this->setData(array(), 0, '已达充值上限！');
            }
        }

         if(in_array($balanceRechargeCouponData['money'], array(28.8,88.8,199.9))){
                $times2 = $balanceRechargeCouponMod->getCount(
                array(
                'cond' => "mark = 1 and is_use = 2 and money in (28.8,88.8,199.9) and use_user = {$this->userId}"
                )
            );
            if($times2 >= 1){
                $this->setData(array(), 0, '当前充值券不符合充值条件！');
            }
        }       

        $userMod = &m('user');
        $user = $userMod->getOne(array(
            'cond' => "id = {$this->userId}",
            'fields' => 'amount',
        ));

        // bs_amount_log 表，插入记录
        $res = $this->amountLogMod->addAmountLog($this->userId, 5, 2, 2, '', $balanceRechargeCouponData['money'], $user['amount'], $user['amount'] + $balanceRechargeCouponData['money'], '');

        if ($res) {
            // 更新 bs_balance_recharge_coupon 表
            $balanceRechargeCouponMod->doEdit($balanceRechargeCouponData['id'], array(
                'is_use' => 2,
                'use_source' => 2,
                'use_user' => $this->userId,
                'use_time' => time(),
            ));

            // 更新 bs_user 表
            $userMod->doEdit($this->userId, array(
                'amount' => $user['amount'] + $balanceRechargeCouponData['money'],
            ));

            $this->setData(array(), 1, '充值成功');
        }

        $this->setData(array(), 0, '充值失败');
    }
}