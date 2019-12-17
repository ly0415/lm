<?php
/**
 * 余额充值券
 * User: xt
 * Date: 2019/3/7
 * Time: 14:26
 */

class BalanceRechargeCouponApp extends BackendApp
{
    protected $balanceRechargeCouponMod;

    /**
     * BalanceRechargeCouponApp constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->balanceRechargeCouponMod = &m('balanceRechargeCoupon');
    }

    public function index()
    {
        $sn = !isset($_REQUEST['sn']) ? '' : htmlspecialchars(trim($_REQUEST['sn']));
        $isUse = empty($_REQUEST['is_use']) ? 0 : htmlspecialchars(trim($_REQUEST['is_use']));
        $startTime = empty($_REQUEST['start_time']) ? '' : htmlspecialchars(trim($_REQUEST['start_time']));
        $endTime = empty($_REQUEST['end_time']) ? '' : htmlspecialchars(trim($_REQUEST['end_time']));
        $export = empty($_REQUEST['export']) ? '' : htmlspecialchars(trim($_REQUEST['export']));

        // 搜索类型
        $this->assign('service_area_id',$_REQUEST['service_area_id']);
        $this->assign('service_store_id',$_REQUEST['service_store_id']);
        $this->assign('service_user_id',$_REQUEST['service_user_id']);


        // 区域列表
        $area_data = &m('storeCate')->getAreaArr(1,$this->lang_id);

        $service_area_data = array_map(function ($i, $m) {
            return array('id' => $i, 'name' => $m);
        }, array_keys($area_data), $area_data);

        $this->assign('service_area_data', $service_area_data);

        // 店铺列表
        $service_store_data = &m('store')->getStoreArr($_REQUEST['service_area_id'], 1);
        $service_store_data = &m('api')->convertArrForm($service_store_data);

        $this->assign('service_store_data', $service_store_data);

        // 店员列表
        $service_user_data = &m('storeUser')->storeUsers($_REQUEST['service_store_id']);

        $this->assign('service_user_data', $service_user_data);

        $where = '1';

        if ($isUse != 3) {
            $where .= ' and c.mark = 1';
        }

        if (!empty($sn) || $sn === '0') {
            $where .= ' and c.sn like "%' . $sn . '%"';
        }

        if (!empty($isUse)) {
            if ($isUse == 3) {
                $where .= ' and c.mark = 0';
            } else {
                $where .= ' and c.is_use = ' . $isUse;
            }
        }

        if (!empty($startTime)) {
            $where .= ' and c.add_time >= ' . strtotime($startTime);
        }

        if (!empty($endTime)) {
            $where .= ' and c.add_time < ' . strtotime($endTime);
        }

        if (!empty($_REQUEST['service_user_id'])) {
            $where .= " and c.store_user = {$_REQUEST['service_user_id']}";
        }

        $sql = <<<SQL
                SELECT
                    c.*,
                    u.username, 
                    u.phone, 
                    su.real_name as store_user_name,
                    a.account_name 
                FROM
                    bs_balance_recharge_coupon c
                    LEFT JOIN bs_account a ON c.add_user = a.id 
                    LEFT JOIN bs_user u ON c.use_user = u.id 
                    LEFT JOIN bs_store_user su ON c.store_user = su.id 
                WHERE {$where} 
                ORDER BY c.id desc 
SQL;

        if (!empty($export)) {
            $this->export($sql);
            exit;
        }

        $data = $this->balanceRechargeCouponMod->querySqlPageData($sql);

        $this->assign('list', $data['list']);
        $this->assign('page_html', $data['ph']);
        $this->assign('sn', $sn);
        $this->assign('isUse', $isUse);
        $this->assign('startTime', $startTime);
        $this->assign('endTime', $endTime);
        $this->display('balanceRechargeCoupon/index.html');
    }

    public function add()
    {
        $this->display('balanceRechargeCoupon/add.html');
    }

    public function doAdd()
    {
        $money = empty($_REQUEST['money']) ? '' : htmlspecialchars(trim($_REQUEST['money']));
        $times = empty($_REQUEST['times']) ? 0 : htmlspecialchars(trim($_REQUEST['times']));

        if (!is_numeric($money)) {
            $this->setData(array(), 0, '金额必须大于0');
        }

        if (empty($money) || $money < 0) {
            $this->setData(array(), 0, '金额必须大于0');
        }

        if ($times <= 0 || $times > 99) {
            $this->setData(array(), 0, '每次可添加1～99条');
        }

        // 循环入库
        while ($times) {
            $data = array(
                'sn' => $this->balanceRechargeCouponMod->findAvailableSn(),
                'money' => $money,
                'add_time' => time(),
                'add_user' => $this->accountId,
            );
            $this->balanceRechargeCouponMod->doInsert($data);
            $times--;
        }

        $this->setData(array(), 1, '添加成功');
    }

    public function drop()
    {
        $id = empty($_REQUEST['id']) ? '' : htmlspecialchars(trim($_REQUEST['id']));

        if (empty($id)) {
            $this->setData(array(), 0, 'id不能为空');
        }

        $res = $this->balanceRechargeCouponMod->doMark($id);

        if ($res) {
            $this->setData(array(), 1, '删除成功');
        }
        $this->setData(array(), 0, '删除失败');
    }

    public function export($sql)
    {
        $title = array('券码', '金额', '状态', '使用来源', '用户名', '手机号', '指派人员', '操作人员', '操作时间', '充值时间');
        $limit = 10000; // limit数

        $data = $this->balanceRechargeCouponMod->querySql($sql);

        foreach ($data as $index => &$datum) {
            $tmpTime = $datum['add_time'];
            $useTime = $datum['use_time'];
            unset($data[$index]['id']);
            unset($data[$index]['add_user']);
            unset($data[$index]['use_user']);
            unset($data[$index]['mark']);
            unset($data[$index]['store_user']);
            unset($data[$index]['add_time']);
            unset($data[$index]['use_time']);
            $datum['is_use'] = $datum['is_use'] == 1 ? '未使用' : '已使用';
            $datum['use_source'] = empty($datum['use_source']) ? '' : ($datum['use_source'] == 1 ? '公众号' : '小程序');
            $datum['add_time'] = date('Y-m-d H:i', $tmpTime);
            $datum['use_time'] = $useTime ? date('Y-m-d H:i', $useTime) : '';
        }

        include_once ROOT_PATH . '/includes/libraries/csvExport.lib.php';
        $csvExport = new csvExport();
        $csvExport->export($title, count($data), $limit, $data);
    }

    /**
     * 统计
     */
    public function count()
    {
        $opDay = $_REQUEST['opDay'] ? htmlspecialchars(trim($_REQUEST['opDay'])) : '';

        if ($opDay) {
            $start_time = $_REQUEST['start_time'] ? htmlspecialchars(trim($_REQUEST['start_time'])) : 0;
            $end_time = $_REQUEST['end_time'] ? htmlspecialchars(trim($_REQUEST['end_time'])) : 0;

            $timeSetArr = array(
                'start_time' => $start_time,
                'end_time' => $end_time
            );
            $topInfo = $this->balanceRechargeCouponMod->couponCount($opDay, 0, $timeSetArr);

            // 金额统计
            switch ($opDay) {
                case 'month':
                    $totalAmount = $this->balanceRechargeCouponMod->totalAmount(strtotime(date('Y-m-01')), strtotime(date('Y-m-t')));
                    break;
                case 'year':
                    $totalAmount = $this->balanceRechargeCouponMod->totalAmount(strtotime(date('Y-01-01')), strtotime(date('Y-12-t')));
                    break;
                case 'setting':
                    $totalAmount = $this->balanceRechargeCouponMod->totalAmount(strtotime($start_time), strtotime($end_time));
                    break;
            }

            $this->setData(array('topInfo' => $topInfo, 'totalAmount' => $totalAmount['total_amount']));
        }

        //新增统计
        $addCoupon = $this->balanceRechargeCouponMod->couponCount('month', 1);

        // 本月
        $monthTotalAmount = $this->balanceRechargeCouponMod->totalAmount(strtotime(date('Y-m-01')), strtotime(date('Y-m-t')));

        $this->assign('xAxis', json_encode($addCoupon['xAxis']));
        $this->assign('num', json_encode($addCoupon['num']));
        $this->assign('monthTotalAmount', $monthTotalAmount);
        $this->display('balanceRechargeCoupon/count.html');
    }

    /**
     * 指派店员
     */
    public function assignStoreUser()
    {
        // 区域列表
        $area_data = &m('storeCate')->getAreaArr(1,$this->lang_id);

        $service_area_data = array_map(function ($i, $m) {
            return array('id' => $i, 'name' => $m);
        }, array_keys($area_data), $area_data);

        $this->assign('service_area_data', $service_area_data);
        $this->assign('coupon_id', $_REQUEST['coupon_id']);
        $this->display('balanceRechargeCoupon/assign.html');
    }

    public function doAssignStoreUser()
    {
        $coupon_id = empty($_REQUEST['coupon_id']) ? 0 : htmlspecialchars(trim($_REQUEST['coupon_id']));
        $service_user_id = empty($_REQUEST['service_user_id']) ? 0 : htmlspecialchars(trim($_REQUEST['service_user_id']));

        if (empty($coupon_id)) {
            $this->setData(array(), 0, '券码不能为空');
        }

        if (empty($service_user_id)) {
            $this->setData(array(), 0, '店员不能为空');
        }

        $sql = "select is_use from bs_balance_recharge_coupon where id in ({$coupon_id})";
        $data = $this->balanceRechargeCouponMod->querySql($sql);

        $isUses = array_map(function ($item) {
            return $item['is_use'];
        }, $data);
        // echo '<pre>';print_r($isUses);die;

        if (in_array(2, $isUses)) {
            $this->setData(array(), 0, '被指派的券码中存在已使用');
        }



        $sql = "update bs_balance_recharge_coupon set store_user = {$service_user_id} where id in ({$coupon_id})";

        $res = $this->balanceRechargeCouponMod->db->Execute($sql);

        if ($res) {
            $this->setData(array(), 1, '指派成功');
        }

        $this->setData(array(), 0, '指派失败');
    }
}