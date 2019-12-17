<?php

/**
 * 设计师列表模块
 * @author wangshuo
 * @date 2018-10-31
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class DesignerListApp extends BackendApp {

    private $lang_id;
    private $DesignerMod;
    private $cityMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->DesignerMod = &m('designer');
        $this->cityMod = &m('city');
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
    }

    /**
     * 设计师列表模块
     * @author wangshuo
     * @date 2018-10-31
     */
    public function index() {
        $this->assign('lang_id', $this->lang_id);
        $sql = 'select * from ' . DB_PREFIX . 'designer_require where mark =1 order by  id ';
        $rs = $this->DesignerMod->querySql($sql);
        $this->assign('res', $rs);
        $this->display('designerList/index.html');
    }

    /**
     * 设计师添加页面
     * @author wangshuo
     * @date 2018-10-31
     */
    public function add() {
        $this->assign('lang_id', $this->lang_id);
        $this->assign('pros', $this->cityMod->getParentNodes());
        $this->display('designerList/add.html');
    }

    /**
     * 设计师添加
     * @author wangshuo
     * @date 2018-10-31
     */
    public function doAdd() {
        $this->load($this->lang_id, 'admin/admin');
        $a = $this->langData;
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars(trim($_REQUEST['phone'])) : '';
        $province = !empty($_REQUEST['pro_id']) ? htmlspecialchars(trim($_REQUEST['pro_id'])) : '';
        $city = !empty($_REQUEST['city_id']) ? htmlspecialchars(trim($_REQUEST['city_id'])) : '';
        $lang_id = !empty($_REQUEST['lang_id']) ? htmlspecialchars(trim($_REQUEST['lang_id'])) : '';
        $ch_sheng = $this->cityMod->getAreaName($province);
        $ch_city = $this->cityMod->getAreaName($city);
        $store_address = $ch_sheng . '/' . $ch_city;
        if (empty($name)) {
            $this->setData($info, $status = '0', '名称不能为空');
        }
        if (empty($phone)) {
            $this->setData($info, $status = '0', '手机不能为空');
        }
        if (empty($province)) {
            $this->setData($info, $status = '0', '地址不能为空');
        }
        if (empty($city)) {
            $this->setData($info, $status = '0', '地址不能为空');
        }
        $data = array(
            'name' => $name,
            'phone' => $phone,
            'province_id' => $province,
            'city_id' => $city,
            'addr' => $store_address,
            'add_time' => time(),
        );
        $rs = $this->DesignerMod->doInsert($data);
        if ($rs) {
            $info['url'] = "?app=designerList&act=index&lang_id=" . $lang_id;
            $this->setData($info, $status = '1', '保存成功');
        } else {
            $this->setData($info = array(), $status = '0', '保存失败');
        }
    }

    /**
     * 设计师编辑模块
     * @author wangshuo
     * @date 2018-10-31
     */
    public function edit() {
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '';
        $lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0';
        $rs = $this->DesignerMod->getOne(array('cond' => "`id` = '{$id}'"));
        $ch_city = $this->getCity($rs['province']);
        $this->assign('ch_city', $ch_city);
        $this->assign('list', $rs);
        $this->assign('lang_id', $lang_id);
        $this->assign('pros', $this->cityMod->getParentNodes());
        $this->display('designerList/edit.html');
    }

    /**
     * 查找地址模块
     * @author wangshuo
     * @date 2018-10-31
     */
    public function getCity($id) {
        $sql = "select `id`,`name` from " . DB_PREFIX . "city where `parent_id`='{$id}'";
        $rs = $this->DesignerMod->querySql($sql);
        return $rs;
    }

    /**
     * 客户编辑模块
     * @author wanyan
     * @date 2018-1-2
     */
    public function doEdit() {
        $this->load($this->lang_id, 'admin/admin');
        $a = $this->langData;
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars(trim($_REQUEST['phone'])) : '';
        $province = !empty($_REQUEST['pro_id']) ? htmlspecialchars(trim($_REQUEST['pro_id'])) : '';
        $city = !empty($_REQUEST['city_id']) ? htmlspecialchars(trim($_REQUEST['city_id'])) : '';
        $lang_id = !empty($_REQUEST['lang_id']) ? htmlspecialchars(trim($_REQUEST['lang_id'])) : '';
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '';
        $ch_sheng = $this->cityMod->getAreaName($province);
        $ch_city = $this->cityMod->getAreaName($city);
        $store_address = $ch_sheng . '/' . $ch_city;
        if (empty($name)) {
            $this->setData($info, $status = '0', '名称不能为空');
        }
        if (empty($phone)) {
            $this->setData($info, $status = '0', '手机不能为空');
        }
        if (empty($province)) {
            $this->setData($info, $status = '0', '地址不能为空');
        }
        if (empty($city)) {
            $this->setData($info, $status = '0', '地址不能为空');
        }
        $data = array(
            'name' => $name,
            'phone' => $phone,
            'province_id' => $province,
            'city_id' => $city,
            'addr' => $store_address,
            'add_time' => time(),
        );
        $rs = $this->DesignerMod->doEdit($id, $data);
        if ($rs) {
            $info['url'] = "?app=designerList&act=index&lang_id=" . $lang_id;
            $this->setData($info, $status = '1', '编辑成功');
        } else {
            $this->setData($info = array(), $status = '0', '编辑失败');
        }
    }

    /**
     * 客服删除模块
     * @author wanyan
     * @date 2018-1-2
     */
    public function dele() {
        $this->load($this->lang_id, 'admin/admin');
        $a = $this->langData;
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : ''; 
        $id = explode(',', $id);
        $data = array(
            'mark' => 0,
        );
           foreach ($id as $k => $v) {
            $rs = $this->DesignerMod->doEdits($v, $data);
        }
        if ($rs) {
            $this->setData($info = array(), $status = '1', $a['delete_Success']);
        } else {
            $this->setData($info = array(), $status = '0', $a['delete_fail']);
        }
    }

}
