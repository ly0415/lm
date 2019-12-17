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

class MsgApp extends BackendApp {

    // private  $kefuMod;
    private $msgMod;
    private $userMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        // $this->kefuMod = &m('imKf');
        $this->msgMod = &m('imMsg');
        $this->userMod = &m('user');
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
        $where = " where  u.`mark` =1  and u.`is_kefu` = 0  and s.distinguish = 0";
        //搜索
        if (!empty($username)) {
                $where .= "  and  u.username like '%" . $username . "%'";
        }

        $sql = "select u.`id`,u.`username`,u.`phone`,u.`email`,u.`add_time`,u.`point`,u.`is_use`,s.store_name
                 from " . DB_PREFIX . "user as u left join " . DB_PREFIX . "store_lang as s on u.store_id=s.store_id and s.lang_id = " . $this->lang_id . $where;
        $sql .= '  order by  u.id desc';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $data = $this->userMod->querySqlPageData($sql);
        $this->assign('p', $p);
        $this->assign('username', $username);
        $this->assign('list', $data['list']);
        $this->assign('page', $data['ph']);
        $this->display('msg/index.html');
    }

    /**
     * 聊天记录
     */
    public function msgList() {
        $uid = !empty($_REQUEST['uid']) ? $_REQUEST['uid'] : '';
        $kfid = !empty($_REQUEST['kefu_id']) ? $_REQUEST['kefu_id'] : 0;
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $sql = 'SELECT  id,username,kf_name  FROM  bs_user  WHERE  is_kefu = 1  ';
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
        $this->assign('uid', $uid);
        $this->assign('act', 'index');
        $this->display('msg/msglist.html');
    }

}
