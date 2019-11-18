<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/27
 * Time: 15:03
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class MsgApp extends BaseStoreApp {

    // private  $kefuMod;
    private $msgMod;
    private $userMod;
    private $lang_id;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        // $this->kefuMod = &m('imKf');
        $this->msgMod = &m('imMsg');
        $this->userMod = &m('user');
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 客服列表
     */
    public function index() {
        $username = !empty($_REQUEST['username']) ? htmlspecialchars(trim($_REQUEST['username'])) : '';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $where = " where  u.`mark` =1  and u.`is_kefu` = 0";
        //搜索
        if (!empty($username)) {
            if ($this->lang_id == 1) {
                $where .= "  and  u.username like '%" . $username . "%'";
            } else {
                $where .= "  and  u.username like '%" . $username . "%'";
            }
        }

        $sql = "select u.`id`,u.`username`,u.`phone`,u.`email`,u.`add_time`,u.`point`,u.`is_use`,sl.store_name
                 from " . DB_PREFIX . "user as u left join " . DB_PREFIX . "store as s on u.store_id=s.id  left join "
                . DB_PREFIX . "store_lang as sl on sl.store_id=s.id  and sl.distinguish = 0  and sl.lang_id = " . $this->defaulLang . $where;
        $sql .= '  order by  u.id desc';
        $data = $this->userMod->querySqlPageData($sql);
        $this->assign('p', $p);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('username', $username);
        $this->assign('list', $data['list']);
        $this->assign('page', $data['ph']);

        if ($this->lang_id) {
            $this->display('msg/index_1.html');
        } else {
            $this->display('msg/index.html');
        }
    }

    /**
     * 聊天记录
     */
    public function msgList() {
        $uid = !empty($_REQUEST['uid']) ? $_REQUEST['uid'] : '';
        $kfid = !empty($_REQUEST['kefu_id']) ? $_REQUEST['kefu_id'] : 0;
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $sql = 'SELECT  id,username,kf_name  FROM  bs_user  WHERE  is_kefu = 1  AND  store_id =' . $this->storeId;
        $kfList = $this->userMod->querySql($sql);

        $this->assign('kfList', $kfList);
        $this->assign('kfid', $kfid);

        if (!empty($kfid)) {
            //需要选择客服，才可以把聊天信息调出来
            $sql = 'SELECT  m.*,u.`username` AS tu_name,u.`kf_name` AS tkf_name,u.`is_kefu` AS tis_kefu ,s.`username`  AS fu_name,s.`kf_name` AS fkf_name,s.`is_kefu`  AS fis_kefu
                    FROM  bs_im_msg AS m LEFT JOIN  bs_user AS u  ON  m.`tid` = u.id
                    LEFT JOIN  bs_user AS s ON m.`fid` = s.`id`
                    WHERE  ( m.tid = ' . $uid . ' AND  m.fid =' . $kfid . ' ) OR ( m.tid = ' . $kfid . ' AND  m.fid =' . $uid . ' )
                    ORDER BY  m.add_time ASC,m.id  ASC';

            $list = $this->msgMod->querySql($sql);
            $this->assign('list', $list);
        }
        $this->assign('p', $p);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('uid', $uid);
        $this->assign('act', 'index');
        if ($this->lang_id) {
            $this->display('msg/msglist_1.html');
        } else {
            $this->display('msg/msglist.html');
        }
    }

}
