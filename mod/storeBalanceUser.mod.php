<?php
/**
 * 推荐余额活动人员记录模型
 * @author zhangkx
 * @date 2019/3/27
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class storeBalanceUserMod extends BaseMod {
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct("store_balance_user");
    }

    /**
     * 推荐余额活动人员记录
     * @author zhangkx
     * @date 2019/3/29
     * @param $recommendId 推荐人id
     * @param $userId 被推荐人id
     * @param $activityId 活动id
     * @param $source 来源
     * @return int
     */
    public function recommend($recommendId, $userId, $activityId, $source)
    {
        $activityMod = &m('storeActivity');
        $balanceMod = &m('storeBalanceUser');
        $userMod = &m('user');
        $fxUserMod = &m('fxuser');
        $amountLogMod = &m('amountLog');
        //根据获取活动推荐奖励规则
        $sql = 'select c.* from '.DB_PREFIX.'store_activity as a 
                left join '.DB_PREFIX.'fission as b on a.fission_id = b.id 
                left join '.DB_PREFIX.'fission_rules as c on b.id = c.fission_id and a.store_id = c.store_id where a.id='.$activityId;
        $data = $activityMod->querySql($sql);
        //统计推荐人当前已推荐的人数
        $userNumber = $balanceMod->getOne(array('cond'=>'recomend_user_id='.$recommendId,'fields'=>'count(*) as total'));
        $userNumber = $userNumber['total'] + 1;
        $ruleId = 0;
        $money = 0;
        //判断被推荐人应属于哪种规则
        foreach ($data as $key => $value) {
            if ($value['max_persons'] == -1) {
                $value['max_persons'] = 99999999;
            }
            $min = 0;
            $max = 0;
            if ($value['symbol_one'] == 1 && $value['symbol_two'] == 1) {
                $min = $value['min_persons'] + 1;
                $max = $value['max_persons'] - 1;
            }
            if ($value['symbol_one'] == 1 && $value['symbol_two'] == 2) {
                $min = $value['min_persons'] + 1;
                $max = $value['max_persons'];
            }
            if ($value['symbol_one'] == 2 && $value['symbol_two'] == 1) {
                $min = $value['min_persons'];
                $max = $value['max_persons'] - 1;
            }
            if ($value['symbol_one'] == 2 && $value['symbol_two'] == 2) {
                $min = $value['min_persons'];
                $max = $value['max_persons'];
            }
            $array = array();
            for ($i=$min;$i<=$max;$i++) {
                $array[] = $i;
            }
            if (in_array($userNumber, $array)) {
                $ruleId = $value['id'];
                $money = $value['num'] + $value['money'];
            }
        }
        //推荐余额活动人员记录
        $user = array(
            'activity_id' => $activityId,
            'fission_rules_id' => $ruleId,
            'recomend_user_id' => $recommendId,
            'login_user_id' => $userId,
            'source' => $source,
            'add_time' => time(),
        );
        $result = $balanceMod->doInsert($user);
        if (!$result) {
            return false;
        }
        $userData = $userMod->getRow($recommendId);
        $userMoney = $money + $userData['amount'];
        //余额日志记录
        $amountResult = $amountLogMod->addAmountLog($recommendId, 8, 1, $source, 0, $money, $userData['amount'], $userMoney, '');
        if (!$amountResult) {
            return false;
        }
        //推荐人推荐奖励入账
        $moneyResult = $userMod->doEdit($recommendId, array('amount' => $userMoney));
        if (!$moneyResult) {
            return false;
        }
        $fxUserData = $fxUserMod->getOne(array('cond'=>'user_id = '.$recommendId));
        if ($fxUserData) {
            $fxUserMoney = $money + $fxUserData['monery'];
            $moneyResult = $fxUserMod->doEdit($fxUserData['id'], array('monery' => $fxUserMoney));
            if (!$moneyResult) {
                return false;
            }
        }
        //更改余额充值状态
        $amountLogMod->doEdit($amountResult, array('status'=>2));
        return true;
    }
}