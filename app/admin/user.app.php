<?php

/**
 * 会员模块控制器
 * @author jh
 * @date 2017-06-22
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class UserApp extends BackendApp {

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
     * 会员首页
     * @author wanyan
     * @date 2017-09-15
     */
    public function index() {
        $username = !empty($_REQUEST['username']) ? htmlspecialchars(trim($_REQUEST['username'])) : '';
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars(trim($_REQUEST['phone'])) : '';
        $email = !empty($_REQUEST['email']) ? htmlspecialchars(trim($_REQUEST['email'])) : '';
        $where = " where `mark` =1   and  is_kefu = 0 and s.lang_id = " . $this->lang_id;
        //搜索
            if (!empty($username)) {
                $where .= "  and u.username like '%" . $username . "%'";
            }
            if (!empty($phone)) {
                $where .= "  and u.phone like '%" . $phone . "%'";
            }
            if (!empty($email)) {
                $where .= "  and u.email like '%" . $email . "%'";
            }
        $country_id = $this->roleCountry;
//        if ($country_id) {
//            $storeMod = &m('store');
//            $store_ids = $storeMod->getData(array("cond" => "store_cate_id=" . $country_id, "fields" => "id"));
//            $ids = implode(',', $this->arrayColumn($store_ids, "id"));
//            $where .= " and u.store_id in (" . $ids . ") and and s.distinguish = 0 ";
//        }
        $where .= " order by `id` desc";
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "user as u" . $where;
        $totalCount = $this->userMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;

        $sql = "select u.`id`,u.`username`,u.`phone`,u.`email`,u.`add_time`,u.`point`,u.`is_use`,s.store_name,u.point,u.amount from " . DB_PREFIX . "user as u
            left join " . DB_PREFIX . "store_lang as s on u.store_id=s.store_id  and s.distinguish = 0" . $where;
        $rs = $this->userMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($rs['list'] as $k => $v) {
            $rs['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            if ($v['add_time']) {
                $rs['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            } else {
                $rs['list'][$k]['add_time'] = '';
            }
            $rs['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        $this->assign('p', $p);
        $this->assign('phone', $phone);
        $this->assign('email', $email);
        $this->assign('username', $username);
        $this->assign('list', $rs['list']);
        $this->assign('page', $rs['ph']);
        $this->display('user/index.html');
    }

    /**
     * 启用禁用会员
     * @author wanyan
     * @date 2017-09-15
     */
    public function getStatus() {
        $user_id = !empty($_REQUEST['user_id']) ? htmlspecialchars($_REQUEST['user_id']) : '';
        $is_use = !empty($_REQUEST['is_use']) ? htmlspecialchars($_REQUEST['is_use']) : '';
        $data = array(
            'is_use' => $is_use
        );
        $rs = $this->userMod->doEdit($user_id, $data);
        if ($rs) {
            $this->addLog('会员状态改变操作');
            $this->setData($info = array(), $status = 1, $message = '');
        } else {
            $this->setData($info = array(), $status = 0, $message = '');
        }
    }

    /**
     * 删除会员
     * @author wanyan
     * @date 2017-09-15
     */
    public function dele() {
        $id = !empty($_REQUEST['id']) ? htmlspecialchars($_REQUEST['id']) : '';
        if (empty($id)) {
            return false;
        }
        $rs = $this->userMod->doMark($id);
        if ($rs) {
            $this->addLog('会员删除操作');
            $this->setData($info = array(), $status = 1, $this->langDataBank->project->drop_success);
        } else {
            $this->setData($info = array(), $status = 0, $this->langDataBank->project->drop_fail);
        }
    }

    /*
     * 编辑跳转
     * @author lee
     * @date 2017-9-20 18:41:53
     */

    public function edit() {
        $id = !empty($_REQUEST['id']) ? htmlspecialchars($_REQUEST['id']) : '';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $info = $this->userMod->getOne(array("cond" => "id=" . $id));
        $this->assign("info", $info);
        $this->assign("p", $p);
        $this->display('user/edit.html');
    }

    /*
     * 编辑用户信息
     */

    public function doEdit() {
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $username = !empty($_POST['username']) ? htmlspecialchars(trim($_POST['username'])) : '';
        $mobile = !empty($_POST['mobile']) ? htmlspecialchars(trim($_POST['mobile'])) : '';
        $email = !empty($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
        $password = !empty($_POST['password']) ? trim($_POST['password']) : '';
        /* $member_id= !empty($_POST['member_id']) ? trim($_POST['member_id']); */
        $id = !empty($_POST['id']) ? $_POST['id'] : '';
        if (empty($username)) {
            $this->setData($info = array(), $status = 0, $this->langDataBank->project->fill_name);
        } else {
            $rs = $this->userMod->getOne(array('cond' => "`username`= '{$username}' and mark =1 and `id` != '{$id}'", 'fields' => 'username'));
            if ($rs['username']) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->project->user_name_exist);
            }
        }
        if (!empty($mobile)) {
            if (!preg_match('/^1[34578]{1}\d{9}$/', $mobile)) {
                $this->setData($info = array(), $status = 0, $this->langDataBank->project->phone_format);
            }
            $rs = $this->userMod->getOne(array('cond' => "`phone`= '{$mobile}' and `id` != '{$id}' and mark =1", 'fields' => 'phone'));
            if ($rs['phone']) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->project->phone_exist);
            }
        }

        if (empty($password)) {
            $this->setData($info = array(), $status = 0, $this->langDataBank->project->please_password);//please_password
        }
        if (!empty($email)) {
            $rs = $this->userMod->getOne(array('cond' => "`email`= '{$email}' and `id` != '{$id}' and mark =1", 'fields' => 'email'));
            if ($rs['email']) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->project->email_exist);
            }
        }
        $data = array(
            'username' => $username,
            'phone' => $mobile,
            'email' => $email
        );
        if ($password) {
            if ($password == '******') {
                
            } else {
                $data['password'] = md5($password);
            }
        }
        $res = $this->userMod->doEdit($id, $data);
        if ($res) {
            $info['url'] = "admin.php?app=user&act=index&p={$p}";
            $this->addLog('会员编辑操作');
            $this->setData($info, $status = 1, $this->langDataBank->public->edit_success);
        } else {
            $this->setData($info = array(), $status = 0, $this->langDataBank->public->edit_fail);
        }
    }

    public function exportOrder() {

        $userMod = &m('user');
        $where = " order by `id` desc";
//        $sql = "select u.`id`,u.`username`,u.`phone`,u.`email`,u.`add_time`,u.`point`,u.`is_use`,s.store_name,u.point from " . DB_PREFIX . "user as u
//            left join " . DB_PREFIX . "store_lang as s on u.store_id=s.store_id  and s.distinguish = 0 where s.lang_id =" . $this->lang_id . $where;
//        $sql = "SELECT id,username,phone,email,add_time,point,is_use,store_id FROM bs_user order by id desc";
//        foreach ($userList as $k => $v){
//            $userList[$k]['store_name'] = $storeMod->getNameById($v['store_id'],$this->lang_id);
//        }
        $sql = "SELECT u.`id`,u.`username`,u.`phone`,u.`email`,u.`add_time`,u.`point`,u.`is_use`,s.store_name,u.point,u.amount FROM bs_user as u LEFT JOIN bs_store_lang as s ON u.store_id = s.store_id WHERE s.distinguish = 0 and s.lang_id =" . $this->lang_id . $where;
        $userList = $userMod->querySql($sql);
//        echo '<pre>';print_r($userList);die;
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=会员列表信息.xls");
        echo iconv('utf-8', 'gb2312', "用户名称") . "\t";
        echo iconv('utf-8', 'gb2312', "联系方式") . "\t";
        echo iconv('utf-8', 'gb2312', "邮箱") . "\t";
        echo iconv('utf-8', 'gb2312', "睿积分") . "\t";
        echo iconv('utf-8', 'gb2312', "余额") . "\t";
        echo iconv('utf-8', 'gb2312', "注册区域") . "\t";
        echo iconv('utf-8', 'gb2312', "添加时间") . "\t";
        echo "\n";

        foreach ($userList as $k => $v) {

            echo iconv('utf-8', 'gb2312', $v['username']) . "\t";
            echo iconv('utf-8', 'gb2312', $v['phone']) . "\t";
            echo iconv('utf-8', 'gb2312', $v['email']) . "\t";
            echo iconv('utf-8', 'gb2312', $v['point']) . "\t";
            echo iconv('utf-8', 'gb2312', $v['amount']) . "\t";
            echo iconv('utf-8', 'gb2312//ignore', $v['store_name']) . "\t";
            echo iconv('utf-8', 'gb2312', date('Y-m-d', $v['add_time'])) . "\t"; //日期
            echo "\n";
        }

    }

    /**
     * 账户余额
     * @author Run
     * @date 2018-10-9
     */
    public function balance() {
        $id = $_REQUEST['id'] ? htmlspecialchars(intval($_REQUEST['id'])) : '';
        $p = $_REQUEST['p'] ? htmlspecialchars(intval($_REQUEST['p'])) : '';
        //账户余额
        $sql_user = "select id,amount,username from bs_user  WHERE id = {$id}";
        $res_user = $this->userMod->querySql($sql_user);
        //余额明细
        $sql = "SELECT * from bs_amount_log where mark = 1 and add_user =" . $id . " order by add_time desc";
        $info = $this->userMod->querySqlPageData($sql);
        $countSql = "SELECT count(*) as totalCount from bs_amount_log where mark = 1 and add_user =" . $id . " order by add_time desc";
        $count = $this->userMod->querySql($countSql);
        $total = $count[0]['totalCount'];
        $orderMod = &m('order');
        $amountLogMod = &m('amountLog');
        foreach ($info['list'] as $k => &$v) {
            $data = $amountLogMod->getAmountData($v['id']);
            $v['source'] = $data['source_name'];
            $v['recharge_amount'] = $data['recharge_amount'];
            $v['amount_before'] = $data['amount_before'];
            $v['amount_after'] = $data['amount_after'];
            $v['recharge_rule'] = $data['recharge_rule'];
            $v['recharge_type'] = $data['recharge_type'];
            $v['recharge_status'] = $data['recharge_status'];

            if ($v['type'] == 2) {
                $order = $orderMod->getOne(array('cond'=>'order_sn="'.$v['order_sn'].'"'));
                $v['order_id'] = $order['order_id'];
                $v['order_sn'] = $v['order_sn'];
            }  elseif ($v['type'] == 6) {
                $order = $orderMod->getOne(array('cond'=>'order_sn="'.$v['order_sn'].'"'));
                $v['order_id'] = $order['order_id'];
                $v['order_sn'] = $v['order_sn'];
            } else {
                $v['order_sn'] = '';
            }
            $v['add_time'] = date("Y-m-d H:i", $v['add_time']);
            $v['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        $this->assign('p', $p);
        $this->assign('page_html', $info['ph']);
        $this->assign('info', $info['list']);
        $this->assign('balance', $res_user[0]);
        $this->assign('id', $id);
        $this->display("user/balance.html");
    }

    /**
     * 订单导出excel
     * @author wangshuo 
     * @date 2018-6-27
     */
    public function exportBalance() {
        $id = $_REQUEST['id'] ? htmlspecialchars(intval($_REQUEST['id'])) : '';
        //账户余额
        $sql_user = "select id,amount,username from bs_user  WHERE id = {$id}";
        $res_user = $this->userMod->querySql($sql_user);
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=订单统计报表.xls");
        echo iconv('utf-8', 'gb2312', "序号") . "\t";
        echo iconv('utf-8', 'gb2312', "订单编号") . "\t";
        echo iconv('utf-8', 'gb2312', "金额") . "\t";
        echo iconv('utf-8', 'gb2312', "变更前余额") . "\t";
        echo iconv('utf-8', 'gb2312', "变更后余额") . "\t";
        echo iconv('utf-8', 'gb2312', "充值规则") . "\t";
        echo iconv('utf-8', 'gb2312', "类型") . "\t";
        echo iconv('utf-8', 'gb2312', "充值状态") . "\t";
        echo iconv('utf-8', 'gb2312', "来源") . "\t";
        echo iconv('utf-8', 'gb2312', "操作时间") . "\t";
        echo "\n";
        //余额明细
        $sql = "SELECT * from bs_amount_log where add_user =" . $id . " order by add_time desc";
        $info = $this->userMod->querySql($sql);
        echo iconv('utf-8', 'gb2312', '账户余额') . "\t";
        echo iconv('utf-8', 'gb2312', $res_user[0]['amount'] . '元') . "\t";
        echo "\n";
        $orderMod = &m('order');
        $amountLogMod = &m('amountLog');
        foreach ($info as $k => &$v) {
            $data = $amountLogMod->getAmountData($v['id']);
            $v['source'] = $data['source_name'];
            $v['recharge_amount'] = $data['recharge_amount'];
            $v['amount_before'] = $data['amount_before'];
            $v['amount_after'] = $data['amount_after'];
            $v['recharge_rule'] = $data['recharge_rule'];
            $v['recharge_type'] = $data['recharge_type'];
            $v['recharge_status'] = $data['recharge_status'];
            if ($v['type'] == 2) {
                $order = $orderMod->getOne(array('cond'=>'order_sn="'.$v['order_sn'].'"'));
                $v['order_id'] = $order['order_id'];
            } else {
                $v['order_sn'] = '';
            }
            $v['add_time'] = date("Y-m-d H:i", $v['add_time']);
            echo iconv('utf-8', 'gb2312', $k+1) . "\t";
            echo iconv('utf-8', 'gb2312', "'" . $v['order_sn']) . "\t";
            echo iconv('utf-8', 'gb2312', $v['recharge_amount']) . "\t";
            echo iconv('utf-8', 'gb2312', $v['amount_before']) . "\t";
            echo iconv('utf-8', 'gb2312', $v['amount_after'] ) . "\t";
            echo iconv('utf-8', 'gb2312', $v['recharge_rule']) . "\t";
            echo iconv('utf-8', 'gb2312', $v['recharge_type']) . "\t";
            echo iconv('utf-8', 'gb2312', $v['recharge_status']) . "\t";
            echo iconv('utf-8', 'gb2312', $v['source']) . "\t";
            echo iconv('utf-8', 'gb2312', $v['add_time']) . "\t";
            echo "\n";
        }
    }

}
