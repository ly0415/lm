<?php

/**
 * 文章列表
 * @author wh
 * @date 2017-8-9
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class ArticleCtgApp extends BaseStoreApp {

    private $articleCtgMod;
    private $lang_id;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->articleCtgMod = &m('articleCate');
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
    }

    /**
     * 文章分类列表
     * @author wangshuo
     * @date 2017-9-19
     */
    public function ctgList() {
        $lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
        $this->assign('lang_id', $this->lang_id);
        $storeId = $this->storeId;
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        if ($lang_id == 1) {
            $where .= '   and `english_name`  like  "%' . $name . '%"';
        } else {
            $where .= '   and `name`  like  "%' . $name . '%"';
        }
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "article_category " . $where;
        $totalCount = $this->articleCtgMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;

        $sql = 'select  *  from  ' . DB_PREFIX . 'article_category  where  store_id = ' . $storeId . $where;
        $res = $this->articleCtgMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($res['list'] as $k => $v) {
            if ($v['add_time']) {
                $res['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            } else {
                $res['list'][$k]['add_time'] = '';
            }
            $res['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        $this->assign('tree', $res['list']);
        $this->assign('name', $name);
        if ($this->lang_id == 1) {
            $this->display('articleCtg/ctgList_1.html');
        } else {
            $this->display('articleCtg/ctgList.html');
        }
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
        $this->assign('lang_id', $this->lang_id);
        if ($this->lang_id == 1) {
            $this->display('articleCtg/ctgAdd_1.html');
        } else {
            $this->display('articleCtg/ctgAdd.html');
        }
    }

    public function doAdd() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $storeId = $this->storeId;
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        $english_name = !empty($_REQUEST['english_name']) ? htmlspecialchars(trim($_REQUEST['english_name'])) : '';
        if (empty($name)) {
            $this->setData(array(), '0', $a['name']);
        }
        if (empty($english_name)) {
            $this->setData(array(), '0', $a['english_name']);
        }
        //验证唯一性
        if ($this->articleCtgMod->isExist('name', $name, $storeId)) {
            $this->setData(array(), '0', $a['class_name']);
        }
        if ($this->articleCtgMod->isExist('english_name', $english_name, $storeId, $id)) {
            $this->setData(array(), '0', $a['class_english']);
        }
        //统计有多少分类数量
        $sqltotal = 'select  COUNT(*)  as total  from  ' . DB_PREFIX . 'article_category  where   store_id = ' . $storeId;
        $data = $this->articleCtgMod->querySql($sqltotal);
        $total = $data[0]['total'];
        if ($total > 9) {
            $this->setData(array(), '0', $a['total']);
        }
        $data = array(
            'name' => $name,
            'english_name' => $english_name,
            'lelvel' => 1,
            'parent_id' => 0,
            'store_id' => $storeId,
            'add_time' => time()
        );
        $res = $this->articleCtgMod->doInsert($data);
        if ($res) {
            $this->setData(array(), '1', $a['add_Success']);
        } else {
            $this->setData(array(), '0', $a['add_fail']);
        }
    }

    /**
     * 文章分类编辑
     * @author wh
     * @date 2017-8-9
     */
    public function edit() {
        //中英切换
        $this->assign('lang_id', $this->lang_id);
        $storeId = $this->storeId;
        $id = $_REQUEST['id'];
        $sql = 'select  *  from  ' . DB_PREFIX . 'article_category  where  id =' . $id . '  and  store_id =' . $storeId;
        $data = $this->articleCtgMod->querySql($sql);
        $this->assign('data', $data[0]);
        if ($this->lang_id == 1) {
            $this->display('articleCtg/ctgEdit_1.html');
        } else {
            $this->display('articleCtg/ctgEdit.html');
        }
    }

    public function doEdit() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $storeId = $this->storeId;
        $id = $_REQUEST['id'];
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        $english_name = !empty($_REQUEST['english_name']) ? htmlspecialchars(trim($_REQUEST['english_name'])) : '';
        if (empty($name)) {
            $this->setData(array(), '0', $a['name']);
        }
        if (empty($english_name)) {
            $this->setData(array(), '0', $a['english_name']);
        }

        if ($this->articleCtgMod->isExist('name', $name, $storeId, $id)) {
            $this->setData(array(), '0', $a['class_name']);
        }
        if ($this->articleCtgMod->isExist('english_name', $english_name, $storeId, $id)) {
            $this->setData(array(), '0', $a['class_english']);
        }
        $data = array(
            'name' => $name,
            'english_name' => $english_name,
            'lelvel' => 1,
            'parent_id' => 0,
            'store_id' => $storeId,
            'add_time' => time()
        );
        $res = $this->articleCtgMod->doEdit($id, $data);
        if ($res) {
            $this->setData(array(), '1', $a['edit_Success']);
        } else {
            $this->setData(array(), '0', $a['edit_fail']);
        }
    }

    public function getChildInfo($id) {
        $sql = 'select id from  ' . DB_PREFIX . 'article_category where  parent_id = ' . $id . '   and  store_id' . $this->storeId;
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
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $id = $_REQUEST['id'];
        if (empty($id)) {
            $this->setData(array(), '0', $a['System_error']);
        }
        // 删除表数据
        $where = 'id  in(' . $id . ')';
        $res = $this->articleCtgMod->doDrops($where);
        if ($res) {   //删除成功
            $this->setData(array(), '1', $a['delete_Success']);
        } else {
            $this->setData(array(), '0', $a['delete_fail']);
        }
    }

}
