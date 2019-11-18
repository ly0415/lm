<?php

/**
 * 积分日志模块控制器

 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class PointLogApp extends BackendApp {

    private $pointLogMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->pointLogMod = &m('pointLog');
    }

    public function index() {
        $username = !empty($_REQUEST['username']) ? htmlspecialchars(trim($_REQUEST['username'])) : '';
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars(trim($_REQUEST['phone'])) : '';
        $startTime = !empty($_REQUEST['start_time']) ? htmlspecialchars(trim($_REQUEST['start_time'])) : '';
        $endTime = !empty($_REQUEST['end_time']) ? htmlspecialchars(trim($_REQUEST['end_time'])) : '';
        if (empty($startTime) && empty($endTime)) {
            $startTime = date('Y-m-d', strtotime('-7 days'));
            $endTime = date('Y-m-d');
        }
        if ($startTime && $endTime && ($startTime > $endTime)) {
            $temp = $startTime;
            $startTime = $endTime;
            $endTime = $temp;
        }
        //一周之前
        $time1 = strtotime(date('Y-m-d', strtotime('-7  days')));
        //一个月之前
        $time2 = strtotime(date('Y-m-d', strtotime('-30  days')));
        //二个月之前
        $time3 = strtotime(date('Y-m-d', strtotime('-60  days')));
        //三个月之前
        $time4 = strtotime(date('Y-m-d', strtotime('-90  days')));
        //六个月之前
        $time5 = strtotime(date('Y-m-d', strtotime('-180  days')));
        $this->assign('time1', $time1);
        $this->assign('time2', $time2);
        $this->assign('time3', $time3);
        $this->assign('time4', $time4);
        $this->assign('time5', $time5);
        //where条件
        $where = ' where 1=1 ';
        if (!empty($username)) {
            $where .= ' and  b.username like "%' . $username . '%"';
            $this->assign('username', $username);
        }
        if (!empty($phone)) {
            $where .= ' and  b.phone = "' . $phone . '"';
            $this->assign('phone',$phone);
        }
        if (!empty($startTime)) {
            $where .= ' and  a.add_time >= ' . strtotime($startTime);
            $this->assign('startTime', $startTime);
        }
        if (!empty($endTime)) {
            $where .= ' and  a.add_time < ' . (strtotime($endTime) + 3600 * 24);
            $this->assign('endTime', $endTime);
        }
        //列表页数据
        $sql = ' select a.*, b.username as name, b.phone from  ' . DB_PREFIX . 'point_log as a 
                left join ' . DB_PREFIX . 'user as b on a.userid = b.id ' . $where . ' order by a.id desc';
        $data = $this->pointLogMod->querySqlPageData($sql);
        $list = $data['list'];
        $this->assign('list', $list);
        $this->assign('page_html', $data['ph']);
        //映射页面
        $this->display('userPoint/pointLog.html');
    }
    /**
     * 删除日志
     * @author jh
     * @date 2017/07/04
     */
    public function dele() {
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';

        if (empty($id)) {
            $this->setData(array(), '0', $this->langDataBank->public->system_error);
        }
        // 删除数据

        $res = $this->pointLogMod->doDrop($id);
        if ($res) {//删除成功
            $this->addLog('删除睿积分日志');
            $this->setData(array(), '1', $this->langDataBank->public->drop_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->drop_fail);
        }
    }

    /**
     * 删除日志
     * @author jh
     * @date 2017/07/25
     */
    public function delLog() {
        $time = $_REQUEST['time'];
        if ($time == 'all') {
            $cond = array('1=1');
        } else {
            $cond = array('add_time <' . $time);
        }
        $res = $this->pointLogMod->doDelete($cond);
        if ($res) {
            $this->addLog('删除睿积分日志');
            $this->setData(array(), '1', $this->langDataBank->public->drop_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->drop_fail);
        }
    }

}
