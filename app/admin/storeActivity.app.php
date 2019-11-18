<?php
/**
 * 店铺活动控制器
 * @author zhangkx
 * @date 2019-03-20
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class StoreActivityApp extends BackendApp {

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = &m('storeActivity');
        $this->storeMod = &m('store');
        $this->applyMod = &m('storeActivityApply');
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {

    }

    /**
     * 活动列表
     * @author zhangkx
     * @date 2019/3/20
     */
    public function index()
    {
        $langId = $_REQUEST['lang_id'] ? $_REQUEST['lang_id'] : $this->lang_id;
        $name = $_REQUEST['name'] ? $_REQUEST['name'] : '';
        $isUse = $_REQUEST['is_use'] ? $_REQUEST['is_use'] : '';
        $storeCate = $_REQUEST['store_cate'] ? $_REQUEST['store_cate'] : '';
        $storeId = $_REQUEST['store_id'] ? $_REQUEST['store_id'] : '';
        $where = ' where a.mark = 1 and c.lang_id = '.$langId;
        if (!empty($name)) {
            $where .= ' and a.name like "%'.$name.'%"';
            $this->assign('name', $name);
        }
        if (!empty($isUse)) {
            $where .= ' and a.is_use ='.$isUse;
            $this->assign('isUse', $isUse);
        }
        if (!empty($storeCate)) {
            $where .= ' and b.store_cate_id = '.$storeCate;
            $this->assign('storeCate', $storeCate);
            if (!empty($storeId)) {
                $where .= ' and a.store_id = '.$storeId;
                $storeList = $this->storeMod->getStoreArr($storeCate, 1);
                $this->assign('storeList', $storeList);
                $this->assign('storeId', $storeId);
            }
        }
        $sql = 'select a.*, c.store_name from '.DB_PREFIX.'store_activity as a 
                left join '.DB_PREFIX.'store as b on a.store_id = b.id
                left join '.DB_PREFIX.'store_lang as c on b.id = c.store_id'.$where;
//        echo '<pre>';print_r($sql);die;
        $data = $this->model->querySqlPageData($sql);
        foreach ($data['list'] as $key => &$value) {
            if (time() > $value['end_time']) {
                $value['edit'] = 0;
            } else {
                $value['edit'] = 1;
            }
            $value['begin_time'] = date('Y-m-d', $value['begin_time']);
            $value['end_time'] = date('Y-m-d', $value['end_time']);
            $value['add_time'] = date('Y-m-d H:i', $value['add_time']);
        }
        //国家列表
        $storeCateList = $this->allCountry;
        $this->assign('storeCateList', $storeCateList);
        $this->assign('data', $data['list']);
        $this->assign('page_html', $data['ph']);
        $this->display('storeActivity/index.html');
    }

    /**
     * 添加活动
     * @author zhangkx
     * @date 2019/3/20
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
                $data = $this->model->buildData($data);
            }
            //插入数据
            $result = $this->model->doInsert($data);
            if (!$result) {
                $this->setData(array(), '0', $this->langDataBank->public->add_error);
            }
            $info['url'] = "admin.php?app=storeActivity&act=index";
            $this->setData($info, '1', $this->langDataBank->public->add_success);
        }
        //国家列表
        $storeCateList = $this->allCountry;
        $this->assign('storeCate', $storeCateList);
        $this->display('storeActivity/add.html');
    }

    /**
     * 编辑活动
     * @author zhangkx
     * @date 2019/3/20
     */
    public function edit()
    {
        $id = $_REQUEST['id'] ? $_REQUEST['id'] : 0;
        if (IS_POST) {
            $data = $_POST;
            $id = $data['id'];
            //校验数据
            if (method_exists($this->model,  'checkData')) {
                $this->model->checkData($data, $id);
            }
            //组装数据
            if (method_exists($this->model,  'buildData')) {
                $data = $this->model->buildData($data, $id);
            }
            //插入数据
            $result = $this->model->doEdit($id, $data);
            if (!$result) {
                $this->setData(array(), '0', $this->langDataBank->public->cz_error);
            }
            $info['url'] = "admin.php?app=storeActivity&act=index";
            $this->setData($info, '1', $this->langDataBank->public->cz_success);
        }
        $info = $this->model->getRow($id);
        $info['begin_time'] = date('Y-m-d', $info['begin_time']);
        $info['end_time'] = date('Y-m-d', $info['end_time']);
        $cate = $this->storeMod->getRow($info['store_id']);
        $info['store_cate_id'] = $cate['store_cate_id'];
        $storeList = $this->storeMod->getStoreArr($cate['store_cate_id'], 1);
        //国家列表
        $storeCateList = $this->allCountry;
        //推广规则
        $fissionMod = &m('fission');
        $fissionList = $fissionMod->getData(array('cond'=>'mark = 1 and store_id = '.$info['store_id']));
        $this->assign('fissionList', $fissionList);
        $this->assign('storeCate', $storeCateList);
        $this->assign('storeList', $storeList);
        $this->assign('info', $info);
        $this->display('storeActivity/edit.html');
    }

    /**
     * 查看活动
     * @author zhangkx
     * @date 2019/3/20
     */
    public function info()
    {
        $id = $_REQUEST['id'] ? $_REQUEST['id'] : 0;
        $langId = $_REQUEST['lang_id'] ? $_REQUEST['lang_id'] : $this->lang_id;
        $info = $this->model->getRow($id);
        $info['begin_time'] = date('Y-m-d', $info['begin_time']);
        $info['end_time'] = date('Y-m-d', $info['end_time']);
        $info['add_time'] = date('Y-m-d h:i', $info['add_time']);
        $info['is_use'] = $info['is_use'] == 1 ? '开启' : '关闭';
        $storeName = $this->storeMod->getNameById($info['store_id'], $langId);
        $info['store_name'] = $storeName;
        $this->assign('data', $info);
        $this->display('storeActivity/info.html');
    }

    /**
     * 删除活动
     * @author zhangkx
     * @date 2019/3/20
     */
    public function drop()
    {
        $id = $_REQUEST['id'] ? $_REQUEST['id'] : 0;
        $info = $this->applyMod->getData(array('cond'=>'status = 1 and activity_id = '.$id));
        if ($info) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->activity_drop);
        }
        $result = $this->model->doMark($id);
        if (!$result) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->drop_fail);
        }
        $this->setData($info = array(), $status = '1', $this->langDataBank->public->drop_success);
    }

    /**
     * 是否启用禁用活动
     * @author zhangkx
     * @date 2019/3/20
     */
    public function isUse(){
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        $Enable = $_REQUEST['Enable'] ? htmlspecialchars(trim($_REQUEST['Enable'])) : '';
        if (empty($id)) {
            $this->jsonError('系统错误！');
        }
        $this->model->doEdit($id, array(
            'is_use' => $Enable
        ));
    }

    /**
     * 活动报名人员
     * @author zhangkx
     * @date 2019/3/26
     */
    public function member()
    {
        $id = $_REQUEST['id'] ? $_REQUEST['id'] : 0;
        $sql = 'select a.*, b.username, b.phone from '.DB_PREFIX.'store_activity_apply as a 
                left join '.DB_PREFIX.'user as b on a.user_id = b.id where a.activity_id = '.$id;
        $data = $this->applyMod->querySql($sql);
        $activity = $this->model->getRow($id);
        foreach ($data as $key => &$value) {
            if ($value['source'] == 1) {
                $value['source'] = $this->langDataBank->project->wechat;
            } else {
                $value['source'] = $this->langDataBank->project->applets;
            }
            $value['add_time'] = date('Y-m-d h:i', $value['add_time']);
        }
        $this->assign('data', $data);
        $this->assign('activityName', $activity['name']);
        $this->display('storeActivity/member.html');
    }

}
