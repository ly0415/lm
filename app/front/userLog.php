<?php

/**
 * 睿积分日志模块控制器

 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class UserLogApp extends BaseFrontApp {

    private $pointLogMod;
    private $lang_id;
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->pointLogMod = &m('pointLog');
        $this->lang = !empty($_REQUEST['lang']) ? intval($_REQUEST['lang']) :$this->langid; //获取切换语言
    }

    public function index() {
        //搜索参数
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;

        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
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
        $this->assign('startTime', $startTime);
        $this->assign('endTime', $endTime);
        $where = ' where 1=1 ';
        if ($this->lang_id == 1) {
            if (!empty($startTime)) {
                $where .= ' and  add_time >= ' . strtotime($startTime);
            }
            if (!empty($endTime)) {
                $where .= ' and  add_time < ' . (strtotime($endTime) + 3600 * 24);
            }
        } else {
            if (!empty($startTime)) {
                $where .= ' and  add_time >= ' . strtotime($startTime);
            }
            if (!empty($endTime)) {
                $where .= ' and  add_time < ' . (strtotime($endTime) + 3600 * 24);
            }
        }
        //列表页数据
        $sql = ' select  *,count(*) as total from  ' . DB_PREFIX . 'point_log   ' . $where . ' AND userid='.$this->userId.'  order by id desc ';
        $data = $this->pointLogMod->querySqlPageData($sql);
        $this->assign('storeid',$storeid);
        $list = $data['list'];
        $this->assign('list', $list);
        $this->assign('lang_id', $lang);
        $this->assign('page_html', $data['ph']);
        //映射页面
        $this->display('userPoint/pointLog.html');
    }


}
