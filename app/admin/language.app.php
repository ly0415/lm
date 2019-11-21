<?php

/**
 * 语言模块
 * @author lee
 * @date 2017-09-11
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class LanguageApp extends BackendApp {


    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /*
     * 语言列表
     * @author　ｌｅｅ
     * @date 2017-9-11 13:54:31
     */

    public function index() {
        $langMod = &m('language');
        $name = ($_REQUEST['name']) ? trim($_REQUEST['name']) : '';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $where = " where 1=1 ";
            if ($name) {
                $where .= " and name like '%" . $name . "%'";
                $this->assign('name', $name);
            }
        $sql = "select * from " . DB_PREFIX . "language" . $where;
        $list = $langMod->querySqlPageData($sql);
        $this->assign('p', $p);
        $this->assign('list', $list['list']);
        $this->assign('page_html', $list['ph']);
        $this->display('language/index.html');
    }

    /*
     * 添加
     * @author lee
     * @date 2017-9-11 13:55:16
     */

    public function languageAdd() {
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $this->assign('p', $p);
        $this->display('language/add.html');
    }

    /*
     * 编辑
     */

    public function languageEdit() {
        $id = ($_REQUEST['id']) ? $_REQUEST['id'] : 0;
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $langMod = &m('language');
        $info = $langMod->getOne(array("cond" => "id=" . $id));
        $this->assign("data", $info);
        $this->assign("p", $p);
        $this->display('language/edit.html');
    }

    /*
     * 添加/编辑处理
     * @author lee
     * @date 2017-9-12 11:06:14
     */

    public function doEdit() {
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $name = ($_REQUEST['name']) ? trim($_REQUEST['name']) : '';
        $name_en = ($_REQUEST['name_en']) ? trim($_REQUEST['name_en']) : '';
        $shorthand = ($_REQUEST['shorthand']) ? trim($_REQUEST['shorthand']) : '';
        $image_id = ($_REQUEST['image_id']) ? trim($_REQUEST['image_id']) : '';
        $is_default = ($_REQUEST['is_default']) ? trim($_REQUEST['is_default']) : '';
        $Enable = ($_REQUEST['Enable']) ? trim($_REQUEST['Enable']) : 2;
        $id = ($_REQUEST['id']) ? trim($_REQUEST['id']) : '';
        if (empty($name)) {
            $this->setData(array(), '0', $this->langDataBank->project->name_required);
        }
        if (empty($name_en)) {
            $this->setData(array(), '0', $this->langDataBank->project->english_name_required);
        }
        if (empty($shorthand)) {
            $this->setData(array(), '0', $this->langDataBank->project->short_name_required);
        }
        if (empty($image_id)) {
            $this->setData(array(), '0', $this->langDataBank->project->logo_required);
        }
        $langMod = &m('language');
        if ($id) {
            $has_name = $langMod->getOne(array("cond" => "`name`=" . $name . " or `name_en`='" . $name_en . "' and id!=" . $id));
            if ($has_name) {
                $this->setData(array(), '0',$this->langDataBank->project->name_exist);
            }
            $has_short = $langMod->getOne(array("cond" => "shorthand='" . $shorthand . "' and id!=" . $id));
            if ($has_short) {
                $this->setData(array(), '0', $this->langDataBank->project->shorthand_exist);
            }
        } else {
            $has_name = $langMod->getOne(array("cond" => "`name`='" . $name . "' or name_en='" . $name_en . "'"));
            if ($has_name) {
                $this->setData(array(), '0', $this->langDataBank->project->name_exist);
            }
            $has_short = $langMod->getOne(array("cond" => "shorthand='" . $shorthand . "'"));
            if ($has_short) {
                $this->setData(array(), '0', $this->langDataBank->project->shorthand_exist);
            }
        }
        $data = array(
            'name' => $name,
            'logo' => $image_id,
            'add_time' => time(),
            'name_en' => $name_en,
            'shorthand' => $shorthand,
//            'enable' => $Enable,
        );
        if ($id) {
            $data['enable'] = $Enable;
        }
        $res = $langMod->doEdit($id, $data);
        $info = $langMod->getOne(array("cond" => "id=" . $id));
        if ($info[is_default] == 2) {
            if ($Enable == 2) {
                $this->setData($info = array(), $status = 0, $this->langDataBank->project->language_close);
            } else {
                $data = array(
                    'enable' => $Enable
                );
                $rs = $langMod->doEdit($id, $data);
            }
        }
        if ($id) {
            unset($data['add_time']);
            if ($is_default == 2) {
                $data['is_default'] = 2;
                $data['enable'] = 1;
            }
            $res = $langMod->doEdit($id, $data);
            if ($is_default == 2) {
                $r = $langMod->doUpdate(array("cond" => "id !=" . $id, "set" => array("is_default" => 1)));
                $this->changeDefaultLang($id);
            } else {
                $info = $langMod->getOne(array("cond" => "is_default = 2 and id !=" . $id));
                if (empty($info)) {
                    $this->setData(array(), 0, $this->langDataBank->project->language_required);
                }
            }
        } else {
            if ($is_default == 2) {
                $data['is_default'] = 2;
            }
            $res = $langMod->doInsert($data);
            if ($is_default == 2) {
                $r = $langMod->doUpdate(array("cond" => "id !=" . $res, "set" => array("is_default" => 1)));
                $this->changeDefaultLang($res);
            }
        }
        if ($res) {
            $info['url'] = "admin.php?app=language&act=index&p={$p}";
            $this->addLog('添加or编辑语言');
            $this->setData($info, $status = 1, $this->langDataBank->public->success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->fail);
        }
    }

    /*
     * 是否使用
     * @author lee
     * @date 2017-10-19 10:01:52
     */

    public function changeSales() {
        $langMod = &m('language');
        $id = $_REQUEST['id'];
        $Enable = ($_REQUEST['Enable']) ? trim($_REQUEST['Enable']) : 2;
//        $is_default = $_REQUEST['is_default'] ? intval($_REQUEST['is_default']) : ''
        $info = $langMod->getOne(array("cond" => "id=" . $id));
        if ($info[is_default] == 2) {
            if ($info[enable] == 1) {
                $this->setData($info = array(), $status = 0, $this->langDataBank->project->language_close);
            } else {
                $data = array(
                    'enable' => $Enable
                );
                $rs = $langMod->doEdit($id, $data);
                if ($rs) {
                    $this->setData($info = array(), $status = 1, $message = '');
                } else {
                    $this->setData($info = array(), $status = 0, $this->langDataBank->project->recommend_fail);
                }
            }
        } else {
            $data = array(
                'enable' => $Enable
            );
            $rs = $langMod->doEdit($id, $data);
            if ($rs) {
                $this->setData($info = array(), $status = 1, $message = '');
            } else {
                $this->setData($info = array(), $status = 0, $this->langDataBank->project->recommend_fail);
            }
        }
    }

    /*
     * 删除处理
     * @author  lee
     * @date 2017-9-12 14:20:58
     */

    public function delete() {
        $langMod = &m('language');
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        if (empty($id)) {
            $this->setData(array(), '0', $this->langDataBank->public->system_error); 
        }
        // 删除表数据
        $where = 'id  in(' . $id . ')';
        $res = $langMod->getOne(array("cond" => $where));
        if ($res['is_default'] == 2) {
            $this->setData(array(), '0', $this->langDataBank->public->drop_fail);
        }
        $res = $langMod->doDrops($where);
        if ($res) {   //删除成功
            $this->addLog('删除语言');
            $this->setData(array(), '1', $this->langDataBank->public->drop_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->drop_fail);
        }
    }

    /*
     * 列表页改变默认语言
     * @author lee
     * @date 2018-1-17 14:20:08
     */

    public function changeLanguage() {
        $is_default = $_REQUEST['is_default'] ? intval($_REQUEST['is_default']) : '';
        $lang_id = $_REQUEST['lang_id'] ? intval($_REQUEST['lang_id']) : '';
        $langMod = &m('language');
        if ($is_default == 1) {
            $info = $langMod->getOne(array("cond" => "is_default = 2 and id !=" . $lang_id));
            if (empty($info)) {
                $this->setData(array(), 0, $this->langDataBank->project->language_required);
            }
        }
        $res = $langMod->doEdit($lang_id, array("is_default" => $is_default, 'enable' => 1));
        if ($is_default == 2) {
            if ($res) {
                $r = $langMod->doUpdate(array("cond" => "id !=" . $lang_id, "set" => array("is_default" => 1)));
                if ($r) {
                    $this->changeDefaultLang($lang_id);
                    $this->addLog('修改默认语言');
                    $this->setData($info, $status = 1,$this->langDataBank->public->success);
                } else {
                    $this->setData(array(), '0', $this->langDataBank->public->fail);
                }
            } else {
                $this->setData(array(), '0', $this->langDataBank->public->fail);
            }
        }
    }

    //改变默认语言
    public function changeDefaultLang($lang_id) {
        $_SESSION['admin']['defal_lang'] = $lang_id;
    }

}
