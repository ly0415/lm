<?php
/**
 * 余额充值记录
 * @author: gao
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class amountLogMod extends BaseMod
{
    private $langDataBank;
    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct("amount_log");
        //加载语言包
        $this->langDataBank = languageFun($this->shorthand);
    }

    public function isExist($order_sn){
        $rs = $this->getOne(array('cond'=>"`order_sn` = '{$order_sn}'",'fields'=>"`order_sn`,id,status,c_money,old_money,new_money,point_rule_id,add_user"));
        return $rs;
    }

    /**
     * 添加余额变更记录
     * @author zhangkx
     * @date 2019/3/8
     * @param $addUser 添加人
     * @param $type 类型：1.微信充值 2.消费扣除 3.注册赠送 4.线下审核支付 5.充值码 6.退款回滚 7.系统处理 8.推荐奖励
     * @param $status 充值状态：1.待支付 2.已支付 3.支付失败 4.已赠送
     * @param $source 充值/扣除来源：1.微信端 2.小程序 3.PC 4.代客下单
     * @param $pointRuleId 充值余额规则ID
     * @param $cMoney 充值/扣除 金额
     * @param $oldMoney 充值/扣除 前用户账户余额
     * @param $newMoney 充值/扣除 后当前用户剩余金额
     * @param $orderSn 充值/消费/退款单号
     * @return int 余额记录id
     */
    public function addAmountLog($addUser,$type, $status, $source,$pointRuleId, $cMoney, $oldMoney, $newMoney, $orderSn)
    {
        $data = array(
            'order_sn' => $orderSn,
            'type' => $type,
            'status' => $status,
            'c_money' => $cMoney,
            'old_money' => $oldMoney,
            'new_money' => $newMoney,
            'point_rule_id' => $pointRuleId,
            'source' => $source,
            'add_user' => $addUser,
            'add_time' => time(),
            'mark' => 1,
        );
        $result = $this->doInsert($data);
        return $result;
    }

    /**
     * 获取来源
     * @author zhangkx
     * @date 2019/4/3
     * @param $id
     * @return array
     */
    public function getAmountData($id)
    {
        // $data = $this->getRow($id);

        // by xt 2019.04.09
        if (is_numeric($id)) {
            $data = $this->getRow($id);
        } else {
            $data = $id;
        }

        $sourceName = '';
        //来源
        if ($data['source'] == 1) {
            $sourceName = $this->langDataBank->project->accounts;
        }
        if ($data['source'] == 2) {
            $sourceName = $this->langDataBank->project->applets;
        }
        if ($data['source'] == 3) {
            $sourceName = $this->langDataBank->project->pc;
        }
        if ($data['source'] == 4) {
            $sourceName = $this->langDataBank->public->dk_order;
        }
        //充值金额
        $rechargeAmount = $data['c_money'].$this->langDataBank->public->yuan;
        $amountBefore = $data['old_money'].$this->langDataBank->public->yuan;
        $rechargePointMod = &m('rechargeAmount');
        $rechargeRule = '';
        $rechargeType = '';
        $rechargeStatus = '';
        switch ($data['type']) {
            case 1:
                $rechargeRule = $rechargePointMod->getRule($data['point_rule_id']);
                $rechargeType = $this->langDataBank->project->wechat_recharge;
                if ($data['status'] == 1) {
                    $rechargeStatus = $this->langDataBank->public->to_paid;
                } elseif ($data['status'] == 2) {
                    $rechargeStatus = $this->langDataBank->public->paid;
                } else {
                    $rechargeStatus = $this->langDataBank->project->payment_fail;
                }
                break;
            case 2:
                $rechargeRule = $this->langDataBank->project->consumption.$rechargeAmount.$this->langDataBank->project->yuan;
                $rechargeType = $this->langDataBank->project->consumption_deduction;
                if ($data['status'] == 1) {
                    $rechargeStatus = $this->langDataBank->public->to_paid;
                } elseif ($data['status'] == 2) {
                    $rechargeStatus = $this->langDataBank->public->paid;
                } else {
                    $rechargeStatus = $this->langDataBank->project->payment_fail;
                }
                break;
            case 3:
                $amountBefore = '0.00'.$this->langDataBank->public->yuan;
                $rechargeRule = $this->langDataBank->project->register_gift.$data['c_money'].$this->langDataBank->public->yuan;
                $rechargeType = $this->langDataBank->project->register_gift;
                $rechargeStatus = $this->langDataBank->project->given;
                break;
            case 4:
                $rechargeRule = $rechargePointMod->getRule($data['point_rule_id']);
                $rechargeType = $this->langDataBank->project->offline_payment;
                if ($data['status'] == 1) {
                    $rechargeStatus = $this->langDataBank->public->pending_review;
                } elseif ($data['status'] == 2) {
                    $rechargeStatus = $this->langDataBank->public->success_review;
                } else {
                    $rechargeStatus = $this->langDataBank->public->audit_fail;
                }
                break;
            case 5:
                $rechargeRule = $this->langDataBank->project->recharge_code.$this->langDataBank->project->recharge.$data['c_money'].$this->langDataBank->public->yuan;
                $rechargeType = $this->langDataBank->project->recharge_code;
                $rechargeStatus = $this->langDataBank->project->recharge_success;
                break;
            case 6:
                $rechargeRule = $this->langDataBank->project->order_refund.$data['c_money'].$this->langDataBank->public->yuan;
                $rechargeType = $this->langDataBank->project->order_refund;
                $rechargeStatus = $this->langDataBank->project->refunded;
                break;
            case 7:
                $rechargeRule = $this->langDataBank->project->system_process.$data['c_money'].$this->langDataBank->public->yuan;
                $rechargeType = $this->langDataBank->project->system_process;
                $rechargeStatus = $this->langDataBank->project->already_send;
                break;
            case 8:
                $rechargeRule = $this->langDataBank->project->recommend_rewards.$data['c_money'].$this->langDataBank->public->yuan;
                $rechargeType = $this->langDataBank->project->recommend_rewards;
                $rechargeStatus = $this->langDataBank->project->already_send;
                break;
        }
        $amountAfter = $data['new_money'].$this->langDataBank->public->yuan;
        $result = array(
            'source_name' => $sourceName,
            'recharge_amount' => $rechargeAmount,
            'amount_before' => $amountBefore,
            'amount_after' => $amountAfter,
            'recharge_rule' => $rechargeRule,
            'recharge_type' => $rechargeType,
            'recharge_status' => $rechargeStatus,
        );
        return $result;
    }
    
}
?>