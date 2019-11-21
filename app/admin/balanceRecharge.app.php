<?php

/**
 * 订单列表
 * @author wangshuo
 * @date 2017-10-20
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class balanceRechargeApp extends BackendApp {

    private $userMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->userMod = &m('user');
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 订单展示
     * @author wangs
     * @date 2017/10/24
     */
    public function index() {
        $username = !empty($_REQUEST['username']) ? htmlspecialchars(trim($_REQUEST['username'])) : '';
        $this->assign('username', $username);
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars(trim($_REQUEST['phone'])) : '';
        $this->assign('phone', $phone);
        $type = !empty($_REQUEST['type']) ? htmlspecialchars(trim($_REQUEST['type'])) : '';
        $this->assign('type', $type);
        $status = !empty($_REQUEST['status']) ? htmlspecialchars(trim($_REQUEST['status'])) : '';
        $this->assign('status', $status);
        $source = !empty($_REQUEST['source']) ? htmlspecialchars(trim($_REQUEST['source'])) : '';
        $this->assign('source', $source);
        $startTime = !empty($_REQUEST['start_time']) ? htmlspecialchars(trim($_REQUEST['start_time'])) : '';
        $endTime = !empty($_REQUEST['end_time']) ? htmlspecialchars(trim($_REQUEST['end_time'])) : '';
        $export = empty($_REQUEST['export']) ? '' : htmlspecialchars(trim($_REQUEST['export']));
        $this->assign('startTime', $startTime);
        $this->assign('endTime', $endTime);
        $where = ' g.mark = 1 and g.type in (1,3,4,5,6,7,8)';
        if (!empty($username)) {
            $where .= ' and  f.username like "%' . $username . '%"';
        }
        if (!empty($phone)) {
            $where .= ' and  f.phone like "%' . $phone . '%"';
        }
        if (!empty($type)) {
            $where .= ' and  g.type like "%' . $type . '%"';
        }
        if (!empty($status)) {
            $where .= ' and  g.status like "%' . $status . '%"';
        }
        if (!empty($source)) {
            $where .= ' and  g.source like "%' . $source . '%"';
        }
        if (!empty($startTime)) {
            $where .= ' and  g.add_time >= ' . strtotime($startTime);
        }
        if (!empty($endTime)) {
            $where .= ' and  g.add_time < ' . (strtotime($endTime) + 3600 * 24);
        }
          // 获取总数
        $totalSql = "select count(*) as totalCount from "
                . DB_PREFIX . 'amount_log as g left join '
                . DB_PREFIX . 'user as f on f.id = g.add_user where '
                . $where . ' order by g.add_time desc';
        $totalCount = $this->userMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        //列表页数据
        $sql = 'select f.username,f.phone,g.* from '
                . DB_PREFIX . 'amount_log as g left join '
                . DB_PREFIX . 'user as f on f.id = g.add_user where '
                . $where . ' order by g.add_time desc';

        if (!empty($export)) {
            $this->export($sql);
            exit;
        }

        //余额明细
        $result = $this->userMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $amountLogMod = &m('amountLog');
        foreach ($result['list'] as $k => &$v) {
            $data = $amountLogMod->getAmountData($v['id']);
            $v['source'] = $data['source_name'];
            $v['recharge_amount'] = $data['recharge_amount'];
            $v['amount_before'] = $data['amount_before'];
            $v['amount_after'] = $data['amount_after'];
            $v['recharge_rule'] = $data['recharge_rule'];
            $v['recharge_type'] = $data['recharge_type'];
            $v['recharge_status'] = $data['recharge_status'];
            $v['add_time'] = date("Y-m-d H:i", $v['add_time']);
            $v['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        $this->assign('page_html', $result['ph']);
        $this->assign('info', $result['list']);
        $this->display('balanceRecharge/pointLog.html');
    }

    public function export($sql)
    {
        // echo $sql;die;
        $title = array('用户名称', '联系方式', '充值金额', '变更前余额', '账户余额', '充值描述', '充值类型', '充值状态', '来源', '操作时间');
        $limit = 10000; // limit数

        $logs = $this->userMod->querySql($sql);

        $amountLogMod = &m('amountLog');
        foreach ($logs as $index => &$datum) {
            $data = $amountLogMod->getAmountData($datum);

            $tmpTime = $datum['add_time'];

            unset($logs[$index]['add_time']);
            unset($logs[$index]['id']);
            unset($logs[$index]['order_sn']);
            unset($logs[$index]['c_money']);
            unset($logs[$index]['old_money']);
            unset($logs[$index]['new_money']);
            unset($logs[$index]['point_rule_id']);
            unset($logs[$index]['type']);
            unset($logs[$index]['status']);
            unset($logs[$index]['add_user']);
            unset($logs[$index]['check_user']);
            unset($logs[$index]['pay_time']);
            unset($logs[$index]['mark']);
            unset($logs[$index]['source']);

            $datum['recharge_amount'] = $data['recharge_amount'];
            $datum['amount_before'] = $data['amount_before'];
            $datum['amount_after'] = $data['amount_after'];
            $datum['recharge_rule'] = $data['recharge_rule'];
            $datum['recharge_type'] = $data['recharge_type'];
            $datum['recharge_status'] = $data['recharge_status'];
            $datum['source'] = $data['source_name'];
            $datum['add_time'] = date("Y-m-d H:i", $tmpTime);
        }
        // echo '<pre>';print_r($logs);die;
        include_once ROOT_PATH . '/includes/libraries/csvExport.lib.php';
        $csvExport = new csvExport();
        $csvExport->export($title, count($logs), $limit, $logs);
    }

}
