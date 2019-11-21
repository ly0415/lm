<?php

/**
 * 物流模块
 * @author  lee
 * @date 2017-10-18 10:16:54
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class logisticsApp extends BackendApp {

    private $corplistMod;
    private $deliveryMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->corplistMod = &m('corplist');
        $this->deliveryMod = &m('freight');
        $this->freightMod = &m('freight');
    }

    /**
     * 运费模板
     * @author  lee
     * @date 2017-10-18 09:57:12
     */
    public function deliveryTypeList() {
        $name = ($_REQUEST['keyname']) ? trim($_REQUEST['keyname']) : '';
        $list = $this->deliveryMod->pageData(array("cond" => "1=1"));
        $this->assign('list', $list['list']);
        $this->assign('page', $list['ph']);
        $this->display('logistics/deliveryList.html');
    }

    /*
     * 添加运费模板
     */

    public function deliveryAdd() {
        $list = $this->corplistMod->getData(array("cond" => "is_use=1", "order by" => "sort desc"));
        $this->assign("corp_list", $list);
        $this->display('logistics/deliveryAdd.html');
    }

    /*
     * 添加运费模板
     */

    public function deliveryDoAdd() {
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(($_REQUEST['name'])) : '';
        $code = !empty($_REQUEST['code']) ? htmlspecialchars(trim($_REQUEST['code'])) : '';
        $web = !empty($_REQUEST['web']) ? htmlspecialchars(trim($_REQUEST['web'])) : '';
        $web_api = !empty($_REQUEST['web_api']) ? htmlspecialchars(trim($_REQUEST['web_api'])) : '';
        $sort = !empty($_REQUEST['sort']) ? htmlspecialchars(trim($_REQUEST['sort'])) : 1;
    }

    /*
     * 添加运费模板
     */

    public function deliveryEdit() {
        $list = $this->corplistMod->getData(array("cond" => "is_use=1", "order by" => "sort desc"));
        $this->display('logistics/deliveryEdit.html');
    }

    /*
     * 添加运费模板
     */

    public function deliveryDoEdit() {
        
    }

    /*
     * 添加物流公司
     * @author lee
     * @date 2017-10-18 10:16:33
     */

    public function corpAdd() {
        $this->display('logistics/add.html');
    }

    public function corpEdit() {
        $id = !empty($_REQUEST['id']) ? htmlspecialchars(($_REQUEST['id'])) : '';
        $info = $this->corplistMod->getOne(array("cond" => "id=" . $id));
        $this->assign("info", $info);
        $this->display('logistics/edit.html');
    }

    /*
     * 编辑物流公司
     * @author lee
     * @date 2017-10-19 10:34:18
     */

    public function doCorpEdit() {
        $id = !empty($_REQUEST['id']) ? htmlspecialchars(($_REQUEST['id'])) : '';
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        $code = !empty($_REQUEST['code']) ? htmlspecialchars(trim($_REQUEST['code'])) : '';
        $web = !empty($_REQUEST['web']) ? htmlspecialchars(trim($_REQUEST['web'])) : '';
        $web_api = !empty($_REQUEST['web_api']) ? htmlspecialchars(trim($_REQUEST['web_api'])) : '';
//        $sort = !empty($_REQUEST['sort']) ? htmlspecialchars(trim($_REQUEST['sort'])) : 1;
        if (empty($name)) {
            $this->setData(array(), '0', $this->langDataBank->project->company_name_required);
        }
        if (empty($code)) {
            $this->setData(array(), '0', $this->langDataBank->project->company_code_required);
        }
        $has = $this->corplistMod->getOne(array("cond" => "(`name`='" . $name . "' or `code`='" . $code . "') and id !=" . $id));
        // print_r($has);exit;
        if ($has) {
            $this->setData(array(), '0', $this->langDataBank->project->company_exist);
        }
        $arr = array(
            'name' => $name,
            'code' => $code,
            'web' => $web,
            'web_api' => $web_api,
//            'sort' => $sort,
        );
        $res = $this->corplistMod->doEdit($id, $arr);
        if ($res) {
            $this->addLog('编辑物流公司');
            $info['url'] = "admin.php?app=logistics&act=corpList";
            $this->setData($info, '1', $this->langDataBank->public->edit_success);
        } else {
            $this->setData(array(), '0',$this->langDataBank->public->edit_fail);
        }
    }

    public function corpDoAdd() {
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        $code = !empty($_REQUEST['code']) ? htmlspecialchars(trim($_REQUEST['code'])) : '';
        $web = !empty($_REQUEST['web']) ? htmlspecialchars(trim($_REQUEST['web'])) : '';
        $web_api = !empty($_REQUEST['web_api']) ? htmlspecialchars(trim($_REQUEST['web_api'])) : '';
//        $sort = !empty($_REQUEST['sort']) ? htmlspecialchars(trim($_REQUEST['sort'])) : 1;
        $is_use = !empty($_REQUEST['is_use']) ? $_REQUEST['is_use'] : 1;
        if (empty($name)) {
            $this->setData(array(), '0', $this->langDataBank->project->company_name_required);
        }
        if (empty($code)) {
            $this->setData(array(), '0', $this->langDataBank->project->company_code_required);
        }
        $has = $this->corplistMod->getOne(array("cond" => "`name`='" . $name . "' or `code`='" . $code . "'"));
        if ($has) {
            $this->setData(array(), '0', $this->langDataBank->project->company_exist);
        }
        $arr = array(
            'name' => $name,
            'code' => $code,
            'web' => $web,
            'web_api' => $web_api,
            'is_use' => $is_use,
//            'sort' => $sort,
            'add_time' => time()
        );
        $res = $this->corplistMod->doInsert($arr);
        if ($res) {
            $this->addLog('添加物流公司');
            $info['url'] = "admin.php?app=logistics&act=corpList";
            $this->setData($info, '1', $this->langDataBank->public->add_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->add_error);
        }
    }

    /*
     * 是否使用
     * @author lee
     * @date 2017-10-19 10:01:52
     */

    public function changeSales() {
        $id = $_REQUEST['id'];
        $is_use = $_REQUEST['is_use'];
        $data = array(
            'is_use' => $is_use
        );
        $rs = $this->corplistMod->doEdit($id, $data);
        if ($rs) {
            $this->setData($info = array(), $status = 1, $message = '');
        } else {
            $this->setData($info = array(), $status = 0, $this->langDataBank->project->recommend_fail);
        }
    }

    /*
     * 删除商品
     * @author wanyan
     * @date 2017-09-14
     */

    public function corpDele() {
        $id = !empty($_REQUEST['id']) ? htmlspecialchars($_REQUEST['id']) : '';
        $query = array(
            'cond' => " `id` ='{$id}'"
        );
        $rs = $rs = $this->corplistMod->doDelete($query);
        if ($rs) {
            $this->addLog('删除物流公司');
            $this->setData($info = array(), $status = '1', $this->langDataBank->public->drop_success);
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->drop_fail);
        }
    }

    /**
     * 物流公司
     * @author lee
     * @date 2017-10-18 09:57:26
     */
    public function corpList() {
        $name = $_REQUEST['name'] ? trim($_REQUEST['name']) : '';
        $where = "1 = 1";
        if ($name) {
            $where .= " and name like '%" . $name . "%'";
        }
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "corplist where " . $where;
        $totalCount = $this->corplistMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $sql = 'select  * from  ' . DB_PREFIX . 'corplist where  ' . $where . ' order by sort desc';
        $list = $this->corplistMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($list['list'] as $k => $v) {
            if ($v['add_time']) {
                $list['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            } else {
                $list['list'][$k]['add_time'] = '';
            }
            $list['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        $this->assign('name', $name);
        $this->assign('list', $list['list']);
        $this->assign('page', $list['ph']);
        $this->display('logistics/list.html');
    }

}
