<?php
//活动点赞管理
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class LikeManagementApp extends BackendApp {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->model = &m('likeManagement');
    }
    /**
     * 析构函数
     */
    public function __destruct() {

    }

    /**
     * 点赞列表
     * @author zhangkx
     * @date 2019/5/14
     */
    public function index()
    {
        $langId = $_REQUEST['lang_id'] ? $_REQUEST['lang_id'] : $this->lang_id;
        $activityId = $_REQUEST['activity_id'] ? $_REQUEST['activity_id'] : 0;
        $wxCode = $_REQUEST['wx_code'] ? $_REQUEST['wx_code'] : '';
        $storeCate = $_REQUEST['store_cate'] ? $_REQUEST['store_cate'] : '';
        $storeId = $_REQUEST['store_id'] ? $_REQUEST['store_id'] : '';
        $where = ' where a.mark = 1 and c.lang_id = '.$langId;
        if (!empty($wxCode)) {
            $where .= ' and a.wx_code like "%'.$wxCode.'%"';
            $this->assign('wxCode', $wxCode);
        }
        if (!empty($storeCate)) {
            $where .= ' and b.store_cate_id = '.$storeCate;
            $this->assign('storeCate', $storeCate);
            if (!empty($storeId)) {
                $where .= ' and a.store_id = '.$storeId;
                $storeMod = &m('store');
                $storeList = $storeMod->getStoreArr($storeCate, 1);
                $this->assign('storeList', $storeList);
                $this->assign('storeId', $storeId);
            }
        }
        $sql = 'select a.*, c.store_name from '.DB_PREFIX.'like_management as a 
                left join '.DB_PREFIX.'store as b on a.store_id = b.id
                left join '.DB_PREFIX.'store_lang as c on b.id = c.store_id'.$where;
        $data = $this->model->querySqlPageData($sql);
        foreach ($data['list'] as $key => &$value) {
            $value['add_time'] = date('Y-m-d H:i', $value['add_time']);
        }
        //国家列表
        $storeCateList = $this->allCountry;
        $this->assign('storeCateList', $storeCateList);
        $this->assign('activityId', $activityId);
        $this->assign('data', $data['list']);
        $this->assign('page_html', $data['ph']);
        $this->display('likeManagement/index.html');
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
            $info['url'] = "admin.php?app=likeManagement&act=index";
            $this->setData($info, '1', $this->langDataBank->public->add_success);
        }
        $activityId = $_REQUEST['activity_id'] ? $_REQUEST['activity_id'] : 0;
        //国家列表
        $storeCateList = $this->allCountry;
        $this->assign('storeCate', $storeCateList);
        $this->assign('activityId', $activityId);
        $this->display('likeManagement/add.html');
    }

    /**
     * 删除活动
     * @author zhangkx
     * @date 2019/5/14
     */
    public function drop()
    {
        $id = $_REQUEST['id'] ? $_REQUEST['id'] : '';
        $result = $this->model->doMark($id);
        if (!$result) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->drop_fail);
        }
        $this->setData($info = array(), $status = '1', $this->langDataBank->public->drop_success);
    }

}
