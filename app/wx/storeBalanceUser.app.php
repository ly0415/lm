<?php
/**
 * 推荐余额活动
 * @author zhangkx
 * @date 2019/3/28
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class StoreBalanceUserApp extends BaseWxApp
{

    public function __construct() {
        parent::__construct();
        $this->model = &m('storeBalanceUser');
    }

    /**
     * 我的奖励
     * @author zhangkx
     * @date 2019/3/28
     */
    public function myRewards()
    {
        $page = $_REQUEST['page'] ? $_REQUEST['page'] : 0;
        $ajax = $_REQUEST['ajax'] ? $_REQUEST['ajax'] : 0;
        $page = $page * 5;
        $sql = 'select a.*, c.phone, d.money, d.num from '.DB_PREFIX.'store_balance_user as a 
                left join '.DB_PREFIX.'user as b on a.recomend_user_id = b.id 
                left join '.DB_PREFIX.'user as c on a.login_user_id = c.id 
                left join '.DB_PREFIX.'fission_rules as d on a.fission_rules_id = d.id where a.recomend_user_id = '.$this->userId.'  limit '.$page.',5';
        $data = $this->model->querySql($sql);
        $moneySql = 'select sum(b.money) as total_money, sum(b.num) as total_num, count(*) as total from '.DB_PREFIX.'store_balance_user as a 
                left join '.DB_PREFIX.'fission_rules as b on a.fission_rules_id = b.id where a.recomend_user_id = '.$this->userId;
        $money = $this->model->querySql($moneySql);
        foreach ($data as $key => &$value) {
            $value['money'] = $value['money'] + $value['num'];
            $value['add_time'] = date('Y-m-d', $value['add_time']);
        }
        if ($ajax) {
            $this->setData($data, '1');
        }
        $totalMoney = $money[0]['total_money'] + $money[0]['total_num'];
        $this->load($this->shorthand, 'WeChat/goods');
        $this->assign('langdata', $this->langData);
        $this->assign('data', $data);
        $this->assign('count', $money[0]['total']);
        $this->assign('money', $totalMoney);
        $this->display('storeBalanceUser/myRewards.html');
    }

}
