<?php
/**
 * 城市控制器
 * @author zhangkx
 * @date 2019-03-22
 */
if (!defined('IN_ECM')) {die('Forbidden');}
class SystemCityApp extends BackendApp{

    private $model;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = &m('systemCity');
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
    }

    /**
     * 城市列表
     * @author zhangkx
     * @date 2019-03-22
     */
    public function index()
    {
        $cityData = $this->model->getData(array(
            'cond' =>' parent_id = 1',
            'fields' => '*'
        ));
        $this->assign('cityData', $cityData);
        //映射页面
        $this->display('systemCity/index.html');
    }

    /*
     * 添加/编辑城市
     * @author zhangkx
     * @date 2019-03-22
	 */
    public function edit()
    {
        $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        $pid = isset($_REQUEST['pid']) ? intval($_REQUEST['pid']) : 0;
        if ($_POST) {
            $name = $_REQUEST['name'] ? htmlspecialchars(trim($_REQUEST['name'])) : '';
            $parent_id = $_REQUEST['parent_id'] ? htmlspecialchars(trim($_REQUEST['parent_id'])) : 1;
            $level = isset($_REQUEST['level']) ? intval($_REQUEST['level']) + 1 : 0;
            $sort = $_REQUEST['sort'] ? htmlspecialchars(trim($_REQUEST['sort'])) : 5;
            if (empty($name)) {
                $this->setData($info = array(), $status = '0', '城市名称必填');
            }
            if (!empty($sort)) {
                if (!preg_match("/^[1-9][0-9]{0,2}$/", $sort)) {
                    $this->setData($info = array(), $status = '0', '排序必须为正整数且最多3位数');
                }
            }
            $data = array(
                'name'      => $name,
                'parent_id' => $parent_id,
                'level'     => $level,
                'sort'      => $sort,
            );
            if ($id) {
                $tips = '编辑';
                $data['upd_user']       = $this->accountId;
                $data['upd_time']       = time();
                $res = $this->model->doEdit($id, $data);
            } else {
                $tips = '添加';
                $data['add_user']       = $this->accountId;
                $data['add_time']       = time();
                $res = $this->model->doInsert($data);
            }
            if ($res) {
                $this->setData($info = array(), $status = '0', $tips.'成功！');
            } else {
                $this->setData($info = array(), $status = '0', $tips.'失败！');
            }
        }
        //角色信息
        if ($id) {
            $data = $this->model->getRow($id);
            $this->assign('data', $data);
        }
        if ($pid) {
            $parent_info = $this->model->getRow($pid);
            $this->assign('parent_info', $parent_info);
        }
        $this->display("systemCity/edit.html");
    }

    /**
     * 删除城市
     * @author zhangkx
     * @date 2019-03-22
     */
    public function drop()
    {
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        if (empty($id)) {
            $this->setData($info = array(), $status = '0', '系统错误！');
        }
        //判断子分类
        $sql = 'select a.id,b.name from  ' . DB_PREFIX . 'system_city as a left join ' . DB_PREFIX . 'system_city as b on a.parent_id = b.id where a.parent_id in (' . $id . ') and a.id not in (' . $id . ')';
        $data = $this->model->querySql($sql);
        if (!empty($data)) {
            $this->setData($info = array(), $status = '0', '【' . $data[0]['name'] . '】下有未删除的子权限，不能删除！');
        }
        // 删除数据
        $res = $this->model->doDrop($id);
        if ($res) {//删除成功
            $this->setData($info = array(), $status = '1', $this->langDataBank->public->drop_success);
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->drop_fail);
        }
    }
}