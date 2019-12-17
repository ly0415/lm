<?php

//

/**
 * 微信操作类.
 * @author lvj
 * @date 2016-11-21
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class StoreNameApp extends BaseWxApp {

    private $storeMod;

    public function __construct() {
        parent::__construct();
        $this->storeMod = &m('store');
    }

    public function index() {
        //语言包
        $this->load($this->shorthand, 'WeChat/goods');
        $this->assign('langdata', $this->langData);
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang_id);
        $latlon=$_REQUEST['latlon'];
        $this->assign('latlon',$latlon);

        $this->display('userCenter/storename.html');
    }

    public function store_name() {
        $lang = !empty($_REQUEST['lang']) ? intval($_REQUEST['lang']) : $this->langid;
        $store_name = !empty($_REQUEST['store_name']) ? trim($_REQUEST['store_name']) : '';
        $latlon=$_REQUEST['latlon'];
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
