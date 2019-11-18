<?php
//活动管理
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class ActivityApp extends BaseStoreApp {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->model = &m('activity');
    }
    /**
     * 析构函数
     */
    public function __destruct() {

    }

    /**
     * 活动列表
     * @author zhangkx
     * @date 2019/5/14
     */
    public function index()
    {
        $name = $_REQUEST['name'] ? $_REQUEST['name'] : '';
        $where = ' where mark = 1';
        if (!empty($name)) {
            $where .= ' and name like "%'.$name.'%"';
            $this->assign('name', $name);
        }
        $sql = 'select * from '.DB_PREFIX.'activity'.$where;
        $data = $this->model->querySqlPageData($sql);
        foreach ($data['list'] as $key => &$value) {
            $value['begin_time'] = date('Y-m-d', $value['begin_time']);
            $value['end_time'] = date('Y-m-d', $value['end_time']);
            $value['add_time'] = date('Y-m-d H:i', $value['add_time']);
        }
        $this->assign('data', $data['list']);
        $this->assign('page_html', $data['ph']);
        $this->display('activity/index.html');
    }

    /**
     * 查看活动
     * @author zhangkx
     * @date 2019/5/14
     */
    public function info()
    {
        $id = $_REQUEST['id'] ? $_REQUEST['id'] : 0;
        $info = $this->model->getRow($id);
        $info['begin_time'] = date('Y-m-d', $info['begin_time']);
        $info['end_time'] = date('Y-m-d', $info['end_time']);
        $info['add_time'] = date('Y-m-d h:i', $info['add_time']);
        $this->assign('data', $info);
        $this->display('activity/info.html');
    }

}
