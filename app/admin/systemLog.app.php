<?php

/**
 * 日志模块控制器
 * @author jh
 * @date 2017-06-22
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class SystemLogApp extends BackendApp {

    private $logMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->logMod = &m('systemLog');
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 日志列表
     * @author jh
     * @date 2017/06/30
     */
    public function index() {
        //搜索参数
        $username = !empty($_REQUEST['username']) ? htmlspecialchars(trim($_REQUEST['username'])) : '';
        //$title = !empty($_REQUEST['title']) ? htmlspecialchars(trim($_REQUEST['title'])) : '';
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
        $this->assign('username', $username);
        $this->assign('startTime', $startTime);
        $this->assign('endTime', $endTime);
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
                $where .= ' and  username like "%' . $username . '%"';
            }
            if (!empty($startTime)) {
                $where .= ' and  add_time >= ' . strtotime($startTime);
            }
            if (!empty($endTime)) {
                $where .= ' and  add_time < ' . (strtotime($endTime) + 3600 * 24);
            }
        //列表页数据
        $sql = ' select  * from  ' . DB_PREFIX . 'system_log   ' . $where . ' order by id desc';
        $data = $this->logMod->querySqlPageData($sql);
        $list = $data['list'];
        $this->assign('list', $list);
        $this->assign('page_html', $data['ph']);
        //映射页面
        $this->display('systemLog/index.html');
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
        $res = $this->logMod->doDrop($id);
        if ($res) {//删除成功
            $this->addLog('删除日志');
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
        $res = $this->logMod->doDelete($cond);
        if ($res) {
            $this->addLog('删除日志');
            $this->setData(array(), '1', $this->langDataBank->public->drop_success);
        } else {
            $this->setData(array(), '0',  $this->langDataBank->public->drop_fail);
        }
    }

}
