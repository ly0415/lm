<?php
//活动管理
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class ActivityApp extends BackendApp {

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
     * 添加活动
     * @author zhangkx
     * @date 2019/5/14
     */
    public function add()
    {
        if (IS_POST) {
            $data = $_POST;
            //校验数据
            if (method_exists($this->model,  'checkData')) {
                $this->model->checkData($data);
            }
            //组装数据
            if (method_exists($this->model,  'buildData')) {
                $data = $this->model->buildData($data, $this->accountId);
            }
            //插入数据
            $result = $this->model->doInsert($data);
            if (!$result) {
                $this->setData(array(), '0', $this->langDataBank->public->add_error);
            }
            $info['url'] = "admin.php?app=activity&act=index";
            $this->setData($info, '1', $this->langDataBank->public->add_success);
        }
        $this->display('activity/add.html');
    }

    /**
     * 编辑活动
     * @author zhangkx
     * @date 2019/5/14
     */
    public function edit()
    {
        $id = $_REQUEST['id'] ? $_REQUEST['id'] : 0;
        if (IS_POST) {
            $data = $_POST;
            $id = $data['id'];
            //校验数据
            if (method_exists($this->model,  'checkData')) {
                $this->model->checkData($data);
            }
            //组装数据
            if (method_exists($this->model,  'buildData')) {
                $data = $this->model->buildData($data, $this->accountId, $id);
            }
            //编辑数据
            $result = $this->model->doEdit($id, $data);
            if (!$result) {
                $this->setData(array(), '0', $this->langDataBank->public->cz_error);
            }
            $info['url'] = "admin.php?app=activity&act=index";
            $this->setData($info, '1', $this->langDataBank->public->cz_success);
        }
        $info = $this->model->getRow($id);
        $info['begin_time'] = date('Y-m-d', $info['begin_time']);
        $info['end_time'] = date('Y-m-d', $info['end_time']);
        $this->assign('info', $info);
        $this->display('activity/edit.html');
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

    /**
     * 删除活动
     * @author zhangkx
     * @date 2019/5/14
     */
    public function drop()
    {
        $id = $_REQUEST['id'] ? $_REQUEST['id'] : 0;
        $result = $this->model->doMark($id);
        if (!$result) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->drop_fail);
        }
        $this->setData($info = array(), $status = '1', $this->langDataBank->public->drop_success);
    }

}
