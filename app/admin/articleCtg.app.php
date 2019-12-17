<?php

/**
 * 文章列表
 * @author wh
 * @date 2017-8-9
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class articleCtgApp extends BackendApp {

    private $articleCtgMod;
    private $lang;
    private $aCLangMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->articleCtgMod = &m('articleCate');
        $this->aCLangMod = &m('articleCateLang');
        $this->lang = &m('language');
    }

    /**
     * 文章分类列表
     * @author wangshuo
     * @date 2017-9-19
     */
    public function ctgList() {
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        if ($this->shorthand == 'EN') {
            $where .= '   `english_name`  like  "%' . $name . '%"';
        } else if ($this->shorthand == 'ZH'){
            $where .= '   `name`  like  "%' . $name . '%"';
        }
        $where = ' al.lang_id = ' . $this->lang_id . ' and al.article_cate_name like "%' . $name . '%" ';


        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "article_category " . $where;
        $totalCount = $this->articleCtgMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        /* $sql = 'select  *  from  ' . DB_PREFIX . 'article_category  where  store_id = ' . $storeId . $where; */
        $sql = 'select al.article_cate_name,al.article_cate_id,a.room_id from ' . DB_PREFIX . 'article_category_lang AS al LEFT JOIN ' . DB_PREFIX . 'article_category AS a ON a.id=al.article_cate_id  where ' . $where;
        $res = $this->articleCtgMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1)); /*
          echo '<pre>';
          var_dump($res);
          echo '</pre>';
          exit; */
        foreach ($res['list'] as $k => $v) {
            if ($v['add_time']) {
                $res['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            } else {
                $res['list'][$k]['add_time'] = '';
            }
            $res['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
            $sql = 'SELECT id FROM ' . DB_PREFIX . 'room_type WHERE id=' . $v['room_id'];
            $roomRes = $this->articleCtgMod->querySql($sql);

            if ($roomRes) {
                $infosql = 'SELECT type_name FROM ' . DB_PREFIX . 'room_type_lang WHERE type_id=' . $v['room_id'] . ' AND lang_id= ' . $this->lang_id;
                $info = $this->articleCtgMod->querySql($infosql);

                $res['list'][$k]['type_name'] = $info[0]['type_name'];
            } else {

                $res['list'][$k]['type_name'] = '不限';
            }
        }
        $this->assign('p', $p);
        $this->assign('tree', $res['list']);
        $this->assign('name', $name);
        $this->display('articleCtg/ctgList.html');
    }

    /**
     * @author wangh
     * @date 2017-06-22
     * 获取分类tree
     * @param $parid
     * @param $channels
     * @param $dep
     * @return array
     */
    public function getTree($parid, $channels, $dep = 1) {
        static $html;
        for ($i = 0; $i < count($channels); $i++) {
            if ($channels[$i]['parent_id'] == $parid) {
                $html[] = array('id' => $channels[$i]['id'], 'name' => str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $dep) . '|—-' . $channels[$i]['name'], 'dep' => $dep,);
                $this->getTree($channels[$i]['id'], $channels, $dep + 1);
            }
        }
        return $html;
    }

    /**
     * 文章分类添加
     * @author wh
     * @date 2017-8-9
     */
    public function add() {
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $this->assign('p', $p);
        $this->assign('lang_id', $this->lang_id);
        $languageData = $this->lang->getData('fields=>name,id');
        $this->assign('languageData', $languageData);
        //业务类型.

        $roomsql = 'SELECT r.id,rl.type_name  FROM ' . DB_PREFIX . 'room_type AS r LEFT JOIN ' . DB_PREFIX . 'room_type_lang AS rl ON r.id=rl.type_id WHERE rl.lang_id =  ' . $this->lang_id . ' AND r.superior_id = 0 ORDER BY r.sort';
        $roomList = $this->aCLangMod->querySql($roomsql);
        $roomList[] = array('id' => -1, 'type_name' => '不限');
        $this->assign('roomList', $roomList);
         $this->display('articleCtg/ctgAdd.html');
    }

    public function doAdd() {
        $name = !empty($_REQUEST['name']) ? $_REQUEST['name'] : array();
        $room_id = $_REQUEST['room_id'];
        foreach ($name as $key => $val) {
            $name[$key] = addslashes(trim($val));
        }

        foreach ($name as $val) {
            if (empty($val)) {
                    $this->setData(array(), '0', $this->langDataBank->project->category_required);  
                break;
            }
            if ($this->aCLangMod->isExist('article_cate_name', $val)) {
                    $this->setData(array(), '0', $this->langDataBank->project->category_exist);  
                break;
            };
        }
        if (empty($room_id)) {
                $this->setData('array()', '0', $this->langDataBank->project->type_required);
        }
        $data = array(
            'lelvel' => 1,
            'parent_id' => 0,
            'add_time' => time(),
            'room_id' => $room_id
        );
        $res = $this->articleCtgMod->doInsert($data);
        if ($res) {
            $this->doLangData($name, $res);
            $this->setData(array(), '1', $this->langDataBank->public->add_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->add_error);
        }
    }

    /**
     * 文章分类编辑
     * @author wh
     * @date 2017-8-9
     */
    public function edit() {
        $languageData = $this->lang->getData('fields=>name,id');
        $id = $_REQUEST['id'];
        $this->assign('languageData', $languageData);
        $roomsql = 'SELECT r.id,rl.type_name  FROM ' . DB_PREFIX . 'room_type AS r LEFT JOIN ' . DB_PREFIX . 'room_type_lang AS rl ON r.id=rl.type_id WHERE rl.lang_id =  ' . $this->lang_id . ' AND r.superior_id = 0 ORDER BY r.sort';
        $roomList = $this->aCLangMod->querySql($roomsql);
        $roomIdSql = 'SELECT room_id FROM ' . DB_PREFIX . 'article_category where id=' . $id;
        $roomid = $this->aCLangMod->querySql($roomIdSql);
        $sql = 'select a.article_cate_name,l.name,l.id,a.lang_id from ' . DB_PREFIX . 'article_category_lang as a left join ' . DB_PREFIX . 'language as l on a.lang_id = l.id where article_cate_id = ' . $id . ' order by a.lang_id ';
        $data = $this->aCLangMod->querySql($sql);
        $roomList[] = array('id' => -1, 'type_name' => '不限');
        $this->assign('roomList', $roomList);
        $this->assign('roomid', $roomid[0]['room_id']);
        $this->assign('data', $data);
        $this->assign('id', $id);
        $this->display('articleCtg/ctgEdit.html');
    }

    public function doEdit() {
        $storeId = $this->storeId;
        $id = $_REQUEST['id'];
        $room_id = !empty($_REQUEST['room_id']) ? $_REQUEST['room_id'] : 0;
        $name = !empty($_REQUEST['name']) ? $_REQUEST['name'] : array();
        foreach ($name as $key => $val) {
            $name[$key] = htmlspecialchars(trim($val));
        }
        foreach ($name as $val) {
            if (empty($val)) {
                    $this->setData(array(), '0', $this->langDataBank->project->category_required);
                break;
            }
            if ($this->aCLangMod->isExist('article_cate_name', $val, $id)) {
                    $this->setData(array(), '0',$this->langDataBank->project->category_exist);
                break;
            }
        }
        if (empty($room_id)) {
                $this->setData('array()', '0', $this->langDataBank->project->type_required);
        }
        $data = array(
            'lelvel' => 1,
            'parent_id' => 0,
            'add_time' => time(),
            'room_id' => $room_id
        );
        $res = $this->articleCtgMod->doEdit($id, $data);

        if ($res) {
            /*  $this->doEditCateLang($id,$name); */
            $where = '  article_cate_id =' . $id;
            $this->aCLangMod->doDrops($where);

            $this->doLangData($name, $id);
            $this->setData(array(), '1', $this->langDataBank->public->edit_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->edit_fail);
        }
    }

    public function getChildInfo($id) {
        $sql = 'select id from  ' . DB_PREFIX . 'article_category where  parent_id = ' . $id;
        $data = $this->articleCtgMod->querySql($sql);
        if (!empty($data)) {
            return $data[0]['id'];
        } else {
            return null;
        }
    }

    /**
     * 文章分类删除
     * @author wh
     * @date 2017-8-9
     */
    public function dele() {
        $id = $_REQUEST['id'];
        if (empty($id)) {
            $this->setData(array(), '0', $this->langDataBank->public->system_error);
        }
        // 删除表数据
        $where = 'id  in(' . $id . ')';
        $res = $this->articleCtgMod->doDrops($where);
        $cateWhere = 'article_cate_id in(' . $id . ')';
        $cateRes = $this->aCLangMod->doDrops($cateWhere);
        if ($res && $cateRes) {   //删除成功
            $this->setData(array(), '1', $this->langDataBank->public->drop_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->drop_fail);
        }
    }

    //多语言
    public function doLangData($name, $res) {

        $data = array();
        foreach ($name as $key => $val) {
            $data[] = array(
                'lang_id' => $key,
                'article_cate_name' => addslashes($val),
                'article_cate_id' => $res,
                'add_time' => time()
            );
        }
        // 循环插入数据
        foreach ($data as $v) {
            $res = $this->aCLangMod->doInsert($v);
            if ($res) {
                continue;
            } else {
                return false;
                break;
            }
        }
        return true;
    }

}
