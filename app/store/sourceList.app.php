<?php

/**
 * 订单列表
 * @author wangshuo
 * @date 2017-10-20
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class sourceListApp extends BaseStoreApp {

    private $sourceListMod;
    private $lang_id;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
        $this->sourceListMod = &m('sourceList');
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 来源列表添加展示页面
     * @author wangshuo
     * @date 2017/10/24
     */
    public function index() {
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim(addslashes($_REQUEST['name']))) : '';
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "store_source ";
        $totalCount = $this->sourceListMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        //列表页数据
        if (!empty($name)) {
            $where .= " and  name like '%" . $name . "%'";
        }
        $sql = 'select * from ' . DB_PREFIX . 'store_source where store_id= ' . $this->storeId . $where . ' order by sort';

//        print_r($sql);
//        exit;
        $res = $this->sourceListMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($res['list'] as $k => $v) {
            if ($v['add_time']) {
                $res['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            } else {
                $res['list'][$k]['add_time'] = '';
            }
            $res['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        $this->assign('name', $name);
        $this->assign('info', $res['list']);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('page_html', $res['ph']);
        $this->display('sourceList/index.html');
    }

    /**
     * 来源列表添加
     * @author wangshuo
     * @date 2018-5-20
     */
    public function add() {
        $this->display('sourceList/add.html');
    }

    /**
     * 列表添加处理
     * @author wangshuo
     * @date 2018-5-20
     */
    public function doAdd() {
        $lang_id = $_REQUEST['lang_id'];
        $this->load($lang_id, 'store/store');
        $a = $this->langData;
        $name = !empty($_REQUEST['name']) ? $_REQUEST['name'] : '';
        $img = !empty($_REQUEST['image_id']) ? $_REQUEST['image_id'] : '';
        $sort = !empty($_REQUEST['sort']) ? $_REQUEST['sort'] : '5';
        if (empty($name)) {
            $this->setData(array(), '0', $a['source_name']);
        }
        if (empty($img)) {
            $this->setData(array(), '0', $a['source_img']);
        }
        if (!empty($sort)) {
            if (!preg_match("/^[1-9][0-9]{0,2}$/", $sort)) {
                $this->setData(array(), '0', $a['source_sort']);
            }
        }
        $data = array(
            'name' => $name,
            'img' => $img,
            'store_id' => $this->storeId,
            'sort' => $sort,
            'add_time' => time()
        );
        $res = $this->sourceListMod->doInsert($data);
        if ($res) {
            $this->setData(array(), $status = 1, $a['add_Success']);
        } else {
            $this->setData(array(), '0', $a['add_fail']);
        }
    }

    /**
     * 来源列表编辑
     * @author wangshuo
     * @date 2018-5-20
     */
    public function edit() {
        $this->load($this->lang_id, 'admin/admin');
        $a = $this->langData;
        $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
        $lang_id = $_REQUEST['lang_id'];
        if (empty($id)) {
            $this->setData(array(), '0', $a['System_error']);
        }
        //角色信息
        $sql = 'SELECT  *  FROM  ' . DB_PREFIX . 'store_source  where id = ' . $id . ' and store_id= ' . $this->storeId;
        $rs = $this->sourceListMod->querySql($sql);
        $this->assign('data', $rs[0]);
        $this->display('sourceList/edit.html');
    }

    /**
     * 来源列表处理
     * @author wangshuo
     * @date 2018-5-20
     */
    public function doEdit() {
        $lang_id = $_REQUEST['lang_id'];
        $this->load($lang_id, 'store/store');
        $a = $this->langData;
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : 0;
        $name = !empty($_REQUEST['name']) ? $_REQUEST['name'] : '';
        $img = !empty($_REQUEST['image_id']) ? $_REQUEST['image_id'] : '';
        $sort = !empty($_REQUEST['sort']) ? $_REQUEST['sort'] : '5';
        if (empty($name)) {
            $this->setData(array(), '0', $a['source_name']);
        }
        if (empty($img)) {
            $this->setData(array(), '0', $a['source_img']);
        }
        if (!empty($sort)) {
            if (!preg_match("/^[1-9][0-9]{0,2}$/", $sort)) {
                $this->setData(array(), '0', $a['source_sort']);
            }
        }
        $data = array(
            'name' => $name,
            'img' => $img,
            'store_id' => $this->storeId,
            'sort' => $sort,
            'add_time' => time()
        );
        $res = $this->sourceListMod->doEdit($id, $data);
        if ($res) {
            $this->setData(array(), $status = 1, $a['edit_sussess']);
        } else {
            $this->setData(array(), '0', $a['edit_fail']);
        }
    }

    /**
     * 图片上传
     * @author wangshuo 
     * @date 2018-5-21
     */
    public function upload() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        if (IS_POST) {
            $fileName = $_FILES['fileName']['name'];
            $type = strtolower(substr(strrchr($fileName, '.'), 1)); //获取文件类型
            $info = array();
            if (!in_array($type, array('jpg', 'png', 'jpeg', 'gif', 'JPG', 'PNG', 'JPEG', 'GIF'))) {
                $this->setData($info, $status = 'error', $a['please_upload']);
            }
            $savePath = "upload/images/sourceList/" . date("Ymd");
            // 判断文件夹是否存在否则创建
            if (!file_exists($savePath)) {
                @mkdir($savePath, 0777, true);
                @chmod($savePath, 0777);
                @exec("chmod 777 {$savePath}");
            }
            $filePath = $_FILES['fileName']['tmp_name']; //文件路径
            $url = $savePath . '/' . time() . '.' . $type;
            if (!is_uploaded_file($filePath)) {
                exit("临时文件错误");
            }
            //上传文件
            if (!move_uploaded_file($filePath, $url)) {
                $this->setData(array(), '0', $a['add_fail']);
            }
            $data = array(
                "name" => $fileName,
                "status" => 1,
                "url" => $url,
                "add_time" => time()
            );
            echo json_encode($data);
            exit;
        } else {
            $this->setData($info = array(), 2, $a['System_error']);
        }
    }

    /**
     * 广告删除
     * @author  wangshuo
     * @date 2017-9-13
     */
    public function dele() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        if (empty($id)) {
            $this->setData(array(), '0', $a['System_error']);
        }
        // 删除表数据
        $where = 'id  in(' . $id . ')';
        $res = $this->sourceListMod->doDrops($where);
        if ($res) {   //删除成功
            $this->setData(array(), '1', $a['delete_Success']);
        } else {
            $this->setData(array(), '0', $a['delete_fail']);
        }
    }

}
