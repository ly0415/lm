<?php

/**
 * 网站配置
 * @author  wh
 * @date 2017-8-18
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class WebconfigApp extends BackendApp {

    private $configMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->configMod = &m('config');
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 网站基本信息
     * @author  wh
     * @date 2017-8-18
     */
    public function baseInfo() {
        $sql = 'select * from bs_config  where  inc_type = "shop_info" ';
        $data = $this->configMod->querySql($sql);

        $res = array();
        foreach ($data as $key => $val) {
            $res[$val['name']] = $data[$key];
        }
        $this->assign('res', $res);
        $this->display('config/webinfo.html');
    }

    /**
     * 网站基本信息保存
     * @author  wh
     * @date 2017-8-18
     */
    public function baseinfoSave() {
        $record_no = !empty($_REQUEST['record_no']) ? htmlspecialchars(trim($_REQUEST['record_no'])) : '';
        $record_no_id = !empty($_REQUEST['record_no_id']) ? $_REQUEST['record_no_id'] : '';

        $store_name = !empty($_REQUEST['store_name']) ? htmlspecialchars(trim($_REQUEST['store_name'])) : '';
        $store_name_id = !empty($_REQUEST['store_name_id']) ? $_REQUEST['store_name_id'] : '';

        $store_logo = !empty($_REQUEST['store_logo']) ? htmlspecialchars(trim($_REQUEST['store_logo'])) : '';
        $store_logo_id = !empty($_REQUEST['store_logo_id']) ? $_REQUEST['store_logo_id'] : '';

//        $store_ico = !empty($_REQUEST['store_ico']) ? htmlspecialchars(trim($_REQUEST['store_ico'])) : '';
//        $store_ico_id = !empty($_REQUEST['store_ico_id']) ? $_REQUEST['store_ico_id'] : '';

        $store_title = !empty($_REQUEST['store_title']) ? htmlspecialchars(trim($_REQUEST['store_title'])) : '';
        $store_title_id = !empty($_REQUEST['store_title_id']) ? $_REQUEST['store_title_id'] : '';

        $store_desc = !empty($_REQUEST['store_desc']) ? htmlspecialchars(trim($_REQUEST['store_desc'])) : '';
        $store_desc_id = !empty($_REQUEST['store_desc_id']) ? $_REQUEST['store_desc_id'] : '';

        $store_keyword = !empty($_REQUEST['store_keyword']) ? htmlspecialchars(trim($_REQUEST['store_keyword'])) : '';
        $store_keyword_id = !empty($_REQUEST['store_keyword_id']) ? $_REQUEST['store_keyword_id'] : '';

        $link_name = !empty($_REQUEST['link_name']) ? htmlspecialchars(trim($_REQUEST['link_name'])) : '';
        $link_name_id = !empty($_REQUEST['link_name_id']) ? $_REQUEST['link_name_id'] : '';

        $link_phone = !empty($_REQUEST['link_phone']) ? htmlspecialchars(trim($_REQUEST['link_phone'])) : '';
        $link_phone_id = !empty($_REQUEST['link_phone_id']) ? $_REQUEST['link_phone_id'] : '';

        $kefu_phone = !empty($_REQUEST['kefu_phone']) ? htmlspecialchars(trim($_REQUEST['kefu_phone'])) : '';
        $kefu_phone_id = !empty($_REQUEST['kefu_phone_id']) ? $_REQUEST['kefu_phone_id'] : '';

        $active_name = !empty($_REQUEST['active_name']) ? htmlspecialchars(trim($_REQUEST['active_name'])) : '';
        $active_goods_id = !empty($_REQUEST['active_goods_id']) ? $_REQUEST['active_goods_id'] : '';

        $hot_name = !empty($_REQUEST['hot_name']) ? htmlspecialchars(trim($_REQUEST['hot_name'])) : '';
        $hot_goods_id = !empty($_REQUEST['hot_goods_id']) ? $_REQUEST['hot_goods_id'] : '';

        if(empty($active_name)){
            $active_name=$this->langDataBank->project->preferential_goods;
        }
        if(empty($hot_name)){
            $hot_name=$this->langDataBank->project->hot_selling_goods;
        }
        $data = array(
            0 =>
            array(
                'id' => $record_no_id,
                'name' => 'record_no',
                'value' => $record_no,
                'inc_type' => 'shop_info',
            ),
            1 =>
            array(
                'id' => $store_name_id,
                'name' => 'store_name',
                'value' => $store_name,
                'inc_type' => 'shop_info',
            ),
            2 =>
            array(
                'id' => $store_logo_id,
                'name' => 'store_logo',
                'value' => $store_logo,
                'inc_type' => 'shop_info',
            ),
//            3 =>
//            array(
//                'id' => $store_ico_id,
//                'name' => 'store_ico',
//                'value' => $store_ico,
//                'inc_type' => 'shop_info',
//            ),
            4 =>
            array(
                'id' => $store_title_id,
                'name' => 'store_title',
                'value' => $store_title,
                'inc_type' => 'shop_info',
            ),
            5 =>
            array(
                'id' => $store_desc_id,
                'name' => 'store_desc',
                'value' => $store_desc,
                'inc_type' => 'shop_info',
            ),
            6 =>
            array(
                'id' => $store_keyword_id,
                'name' => 'store_keyword',
                'value' => $store_keyword,
                'inc_type' => 'shop_info',
            ),
            7 =>
            array(
                'id' => $link_name_id,
                'name' => 'link_name',
                'value' => $link_name,
                'inc_type' => 'shop_info',
            ),
            8 =>
            array(
                'id' => $link_phone_id,
                'name' => 'link_phone',
                'value' => $link_phone,
                'inc_type' => 'shop_info',
            ),
            9 =>
            array(
                'id' => $kefu_phone_id,
                'name' => 'kefu_phone',
                'value' => $kefu_phone,
                'inc_type' => 'shop_info',
            ),
            10 =>
                array(
                    'id' => $active_goods_id,
                    'name' => 'active_name',
                    'value' => $active_name,
                    'inc_type' => 'shop_info',
                ),
            11 =>
                array(
                    'id' => $hot_goods_id,
                    'name' => 'hot_name',
                    'value' => $hot_name,
                    'inc_type' => 'shop_info',
                ),
        );
        //循环插入和编辑数据
        foreach ($data as $key => $val) {
            if (empty($val['id'])) {
                //插入数据
                $this->configMod->doInsert(array('inc_type' => 'shop_info', 'name' => $val['name'], 'value' => $val['value']));
            } else {
                //编辑数据
                $this->configMod->doEdit($val['id'], array('inc_type' => 'shop_info', 'name' => $val['name'], 'value' => $val['value']));
            }
        }
        $this->setData(array(), $status = 0,$this->langDataBank->public->success_save);
    }

    /**
     * logo上传
     * @author  wh
     * @date 2017-8-18
     */
    public function upload() {
        if (IS_POST) {
            $fileName = $_FILES['fileName']['name'];
            $type = strtolower(substr(strrchr($fileName, '.'), 1)); //获取文件类型
            $info = array();
            if (!in_array($type, array('png'))) {
                $this->setData($info, $status = 'error', $this->langDataBank->project->upload_png);
            }
            $savePath = "upload/images/webconfig/" . date("Ymd");
            // 判断文件夹是否存在否则创建
            if (!file_exists($savePath)) {
                @mkdir($savePath, 0777, true);
                @chmod($savePath, 0777);
                @exec("chmod 777 {$savePath}");
            }
            $filePath = $_FILES['fileName']['tmp_name']; //文件路径
            $url = $savePath . '/' . time() . '.' . $type;
            if (!is_uploaded_file($filePath)) {
                exit($this->langDataBank->project->temp_file_error);
            }
            //上传文件
            if (!move_uploaded_file($filePath, $url)) {
                $this->setData(array(), '0', $this->langDataBank->public->add_success);
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
            $this->setData($info = array(), 2, $this->langDataBank->public->system_error);
        }
    }

    /**
     * 上传
     * @author  wh
     * @date 2017-8-18
     */
    public function upload2() {
        if (IS_POST) {
            $fileName = $_FILES['fileName2']['name'];
            $type = strtolower(substr(strrchr($fileName, '.'), 1)); //获取文件类型
            $info = array();
            if (!in_array($type, array('ico'))) {
                $this->setData($info, $status = 'error', $this->langDataBank->project->upload_ico);
            }
            $savePath = "upload/images/webconfig/" . date("Ymd");
            // 判断文件夹是否存在否则创建
            if (!file_exists($savePath)) {
                @mkdir($savePath, 0777, true);
                @chmod($savePath, 0777);
                @exec("chmod 777 {$savePath}");
            }
            $filePath = $_FILES['fileName2']['tmp_name']; //文件路径
            $url = $savePath . '/' . time() . '.' . $type;
            if (!is_uploaded_file($filePath)) {
                exit($this->langDataBank->project->temp_file_error);
            }
            //上传文件
            if (!move_uploaded_file($filePath, $url)) {
                $this->setData(array(), '0', $this->langDataBank->public->add_success);
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
            $this->setData($info = array(), 2, $this->langDataBank->public->system_error);
        }
    }

}
