<?php

/**
 * 附近门店
 * @author wangshuo
 * @date 2018-04-18
 */
class mapApp extends BaseFrontApp {

    /**
     * 附近门店首页
     * @author wangshuo
     * @date 2018-04-18
     */
    public function index() {
        $this->load($this->shorthand, 'public/daohang');
        $this->assign('langdata', $this->langData);
        $this->display('public/map.html');
    }

    /**
     * 获取附近门店
     * @author wanyan
     * @date 2017-08-31
     */
    public function getStore() {
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
            $where = ' and s.store_type<4 ';
        }
        $storeMod = &m('store');
        $sql = 'SELECT s.id,l.store_name,s.lang_id,s.addr_detail,s.longitude,s.latitude,s.store_type,s.store_mobile  FROM  ' . DB_PREFIX . 'store AS s
                 LEFT JOIN  ' . DB_PREFIX . 'store_lang AS l ON s.`id` = l.`store_id` 
                  WHERE s.is_open = 1 and l.distinguish = 0 and  l.lang_id =' . $this->langid . $where . '  ORDER BY s.id';
        $data = $storeMod->querySql($sql);
        echo json_encode($data);
        die;
    }

}
