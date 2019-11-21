<?php

/**
 * 权限控制器
 * @author: jh
 * @date: 2017/6/22
 */
class SystemAuthApp extends BackendApp {

    private $systemAuthMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->systemAuthMod = &m('systemAuth');
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 权限列表
     * @author wangs
     * @date 2017/06/26
     */
    public function index() {
        $title = !empty($_REQUEST['title']) ? htmlspecialchars(trim($_REQUEST['title'])) : '';
        $this->assign('title', $title);
        $titles = !empty($_REQUEST['titles']) ? htmlspecialchars(trim($_REQUEST['titles'])) : '';
        $this->assign('titles', $titles);
        // 数据
        $where = ' where 1=1';
        //搜索
        if ($this->shorthand == 'EN') {
            if (!empty($titles)) {
                $where .= '   and  `english_title`  like  "%' . $titles . '%"';
            }
        } else if($this->shorthand == 'ZH'){
            if (!empty($title)) {
                $where .= ' and  `title`  like "%' . $title . '%"'; //搜索
            }
        }
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "system_auth " . $where;
        $totalCount = $this->systemAuthMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        //中英切换
        $this->assign('lang_id', $this->lang_id);
        $sql = 'select * from ' . DB_PREFIX . 'system_auth ' . $where . ' order by sort';
        $data = $this->systemAuthMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
         foreach ($data['list'] as $k => $v) {
            if ($v['add_time']) {
                $data['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            } else {
                $data['list'][$k]['add_time'] = '';
            }
            $data['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        $this->assign('list', $data['list']);
        $this->assign('page_html', $data['ph']);
        $this->display('systemAuth/index.html');
    }

    /*
     * 权限添加页面
     * @author jh
     * @date 2017/06/22
     */

    public function add() {
        $sql = 'select id,title,english_title from ' . DB_PREFIX . 'system_auth where parent_id = 0';
        $columnData = $this->systemAuthMod->querySql($sql);
        $this->assign('columnData', $columnData);
        $this->display('systemAuth/add.html');
    }

    /*
     * 权限添加入库
     * @author jh
     * @date 2017/06/22
     */

    public function doAdd() {
        $title = $_REQUEST['title'] ? htmlspecialchars(trim($_REQUEST['title'])) : '';
        $english_title = $_REQUEST['english_title'] ? htmlspecialchars(trim($_REQUEST['english_title'])) : '';
        $parentId = $_REQUEST['parent_id'] ? htmlspecialchars($_REQUEST['parent_id']) : 0;
        $app = $_REQUEST['app-form'] ? htmlspecialchars(trim($_REQUEST['app-form'])) : '';
        $act = $_REQUEST['act-form'] ? htmlspecialchars(trim($_REQUEST['act-form'])) : '';
        $parameters = $_REQUEST['param-form'] ? htmlspecialchars(trim($_REQUEST['param-form'])) : '';
        $sort = $_REQUEST['sort'] ? htmlspecialchars(trim($_REQUEST['sort'])) : 5;
        $isMenu = $_REQUEST['is_menu'] ? htmlspecialchars(trim($_REQUEST['is_menu'])) : 0;
        $isPublic = $_REQUEST['is_public'] ? htmlspecialchars(trim($_REQUEST['is_public'])) : 0;
        $level = $parentId == 0 ? 1 : 2;
        if (empty($title)) {
            $this->setData(array(), '0', $this->langDataBank->project->permission_cn_required);
        }
        if (mb_strlen($title) > 20) {
            $this->setData(array(), '0', $this->langDataBank->project->permission_cn_length);
        }
        if ($this->systemAuthMod->isExist('title', $title)) {
            $this->setData(array(), '0', $this->langDataBank->project->permission_cn_exist);
        }
        if (empty($english_title)) {
            $this->setData(array(), '0', $this->langDataBank->project->permission_en_required);
        }
        if (mb_strlen($english_title) > 50) {
            $this->setData(array(), '0', $this->langDataBank->project->permission_en_length);
        }
        if ($this->systemAuthMod->isExist('english_title', $english_title)) {
            $this->setData(array(), '0', $this->langDataBank->project->permission_en_exist);
        }
        // if (mb_strlen($app) > 20) {
        //     $this->setData(array(), '0', $this->langDataBank->project->module_length);
        // }
        if (mb_strlen($act) > 20) {
            $this->setData(array(), '0',$this->langDataBank->project->method_length);
        }
        if (mb_strlen($parameters) > 100) {
            $this->setData(array(), '0', $this->langDataBank->project->param_length);
        }
        if (!empty($sort)) {
            if (!preg_match("/^[1-9][0-9]{0,2}$/", $sort)) {
                $this->setData(array(), '0', $this->langDataBank->project->sort_rule);
            }
        }
        $data = array(
            'title' => $title,
            'english_title' => $english_title,
            'app' => $app,
            'act' => $act,
            'parameters' => $parameters,
            'parent_id' => $parentId,
            'level' => $level,
            'sort' => $sort,
            'is_menu' => $isMenu,
            'is_public' => $isPublic,
            'modify_time' => time(),
            'add_time' => time()
        );
        $res = $this->systemAuthMod->doInsert($data);
        if ($res) {
            $this->addLog('添加权限');
            $this->setData(array(), '1', $this->langDataBank->public->add_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->add_error);
        }
    }

    /*
     * 权限编辑入库
     * @author jh
     * @date 2017/06/22
     */

    public function edit() {
        $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        if (empty($id)) {
            $this->setData(array(), '0', $this->langDataBank->public->system_error);
        }
        $sql = 'select id,title,english_title from ' . DB_PREFIX . 'system_auth where parent_id = 0 and id != ' . $id;
        $columnData = $this->systemAuthMod->querySql($sql);
        $this->assign('columnData', $columnData);
        $res = $this->systemAuthMod->getInfo('id', $id);
        $this->assign('data', $res);
        $this->display("systemAuth/edit.html");
    }

    /*
     * 权限编辑入库
     * @author jh
     * @date 2017/06/22
     */

    public function doEdit() {
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : 0;
        $title = $_REQUEST['title'] ? htmlspecialchars(trim($_REQUEST['title'])) : '';
        $english_title = $_REQUEST['english_title'] ? htmlspecialchars(trim($_REQUEST['english_title'])) : '';
        $parentId = $_REQUEST['parent_id'] ? htmlspecialchars($_REQUEST['parent_id']) : 0;
        $app = $_REQUEST['app-form'] ? htmlspecialchars(trim($_REQUEST['app-form'])) : '';
        $act = $_REQUEST['act-form'] ? htmlspecialchars(trim($_REQUEST['act-form'])) : '';
        $parameters = $_REQUEST['param-form'] ? htmlspecialchars(trim($_REQUEST['param-form'])) : '';
        $sort = $_REQUEST['sort'] ? htmlspecialchars(trim($_REQUEST['sort'])) : 5;
        $isMenu = $_REQUEST['is_menu'] ? htmlspecialchars(trim($_REQUEST['is_menu'])) : 0;
        $isPublic = $_REQUEST['is_public'] ? htmlspecialchars(trim($_REQUEST['is_public'])) : 0;
        $level = $parentId == 0 ? 1 : 2;
        if (empty($id)) {
            $this->setData(array(), '0', $this->langDataBank->public->system_error);
        }
        if (empty($title)) {
            $this->setData(array(), '0', $this->langDataBank->project->permission_cn_required);
        }
        if (mb_strlen($title) > 20) {
            $this->setData(array(), '0', $this->langDataBank->project->permission_cn_length);
        }
        if ($this->systemAuthMod->isExist('title', $title, $id)) {
            $this->setData(array(), '0', $this->langDataBank->project->permission_cn_exist);
        }
        if (empty($english_title)) {
            $this->setData(array(), '0', $this->langDataBank->project->permission_en_required);
        }
        if (mb_strlen($english_title) > 50) {
            $this->setData(array(), '0', $this->langDataBank->project->permission_en_length);
        }
        if ($this->systemAuthMod->isExist('english_title', $english_title, $id)) {
            $this->setData(array(), '0', $this->langDataBank->project->permission_en_exist);
        }
        if (mb_strlen($app) > 20) {
            $this->setData(array(), '0', $this->langDataBank->project->module_length);
        }
        if (mb_strlen($act) > 20) {
            $this->setData(array(), '0', $this->langDataBank->project->method_length);
        }
        if (mb_strlen($parameters) > 100) {
            $this->setData(array(), '0', $this->langDataBank->project->param_length);
        }
        if (!empty($sort)) {
            if (!preg_match("/^[1-9][0-9]{0,2}$/", $sort)) {
                $this->setData(array(), '0', $this->langDataBank->project->sort_rule);
            }
        }
        $data = array(
            'title' => $title,
            'english_title' => $english_title,
            'app' => $app,
            'act' => $act,
            'parameters' => $parameters,
            'parent_id' => $parentId,
            'level' => $level,
            'sort' => $sort,
            'is_menu' => $isMenu,
            'is_public' => $isPublic,
            'modify_time' => time(),
        );
        $res = $this->systemAuthMod->doEdit($id, $data);
        if ($res) {
            $this->addLog('编辑权限');
            $this->setData(array(), '1', $this->langDataBank->public->edit_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->edit_fail);
        }
    }

    /**
     * 删除权限
     * @author jh
     * @date 2017/06/22
     */
    public function dele() {
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        if (empty($id)) {
            $this->setData(array(), '0', $a['System_error']);
        }
        //判断子分类
        $sql = 'select a.id,b.title from  ' . DB_PREFIX . 'system_auth as a left join ' . DB_PREFIX . 'system_auth as b on a.parent_id = b.id where a.parent_id in (' . $id . ') and a.id not in (' . $id . ')';
        $data = $this->systemAuthMod->querySql($sql);
        if (!empty($data)) {
            $this->setData(array(), '0', '【' . $data[0]['title'] . '】 ' . $this->langDataBank->project->cannot_delete);
        }
        // 删除数据
        $res = $this->systemAuthMod->doDrop($id);
        if ($res) {//删除成功
            $this->addLog('删除权限');
            $this->setData(array(), '1', $this->langDataBank->public->drop_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->drop_fail);
        }
    }

}
