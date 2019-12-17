<?php

/**
 * 用户中心
 * @author lvji
 *
 */
//include_once 'sms.app.php';
//include_once 'mail.app.php';
class StoreNameApp extends BaseFrontApp {

    private $storeMod;

    public function __construct() {
        $this->storeMod = &m('store');
    }

    public function index() {
        $lang = !empty($_REQUEST['lang']) ? intval($_REQUEST['lang']) : $this->langid;
        $store_name = !empty($_REQUEST['store_name']) ? trim($_REQUEST['store_name']) : '';
        $lang = !empty($_REQUEST['lang']) ? intval($_REQUEST['lang']) : $this->langid;
        $store_name = !empty($_REQUEST['store_name']) ? trim($_REQUEST['store_name']) : '';
        if ($_SESSION['userId']) {
            $user_id = $_SESSION['userId'];
            $userMod = &m('user');
            $sql = 'SELECT *  FROM  ' . DB_PREFIX . 'user WHERE  id  =' . $user_id; //odm_members
            $datas = $userMod->querySql($sql);
            if ($datas[0]['odm_members'] == 0) {
                $where = ' and s.store_type <4 ';
            } else {
                $where = '';
            }
        } else {
            $where = ' and s.store_type <4 ';
        }
        //获取表内容
        if (!empty($store_name)) {
            $where .= ' and l.store_name like "%' . addslashes(addslashes($store_name)) . '%"';
        }
        $sql = 'SELECT l.*  FROM  ' . DB_PREFIX . 'store AS s
                 LEFT JOIN  ' . DB_PREFIX . 'store_lang AS l ON s.id = l.store_id  WHERE  s.is_open = 1 and  l.lang_id =' . $lang . $where . ' order by l.id';
        $rs = $this->storeMod->querySql($sql);
        echo json_encode($rs);
        die;
    }

}
