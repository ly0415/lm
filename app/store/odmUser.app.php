<?php

/**
 * 会员模块控制器
 * @author jh
 * @date 2017-06-22
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class odmUserApp extends BaseStoreApp {

    private $userMod;
    private $lang_id;
    private $storeMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->userMod = &m('user');
        $this->storeMod = &m('store');
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 会员首页
     * @author wanyan
     * @date 2017-09-15
     */
    public function index() {
        $this->assign('lang_id', $this->lang_id);
        $username = !empty($_REQUEST['username']) ? htmlspecialchars(trim($_REQUEST['username'])) : '';
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars(trim($_REQUEST['phone'])) : '';
        $email = !empty($_REQUEST['email']) ? htmlspecialchars(trim($_REQUEST['email'])) : '';
        $where = " where `mark` =1   and  is_kefu = 0  and odm_members =1";
        //搜索
        if ($this->lang_id == 1) {
            if (!empty($username)) {
                $where .= "  and u.username like '%" . $username . "%'";
            }
            if (!empty($phone)) {
                $where .= "  and u.phone like '%" . $phone . "%'";
            }
            if (!empty($email)) {
                $where .= "  and u.email like '%" . $email . "%'";
            }
        } else {
            if (!empty($username)) {
                $where .= "  and u.username like '%" . $username . "%'";
            }
            if (!empty($phone)) {
                $where .= "  and u.phone like '%" . $phone . "%'";
            }
            if (!empty($email)) {
                $where .= "  and u.email like '%" . $email . "%'";
            }
        }
        $where .= " order by `id` desc";
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;

        $sql = "select u.`id`,u.`username`,u.`phone`,u.`email`,u.`add_time`,u.`point`,u.`is_use`,s.store_name from " . DB_PREFIX . "user as u
            left join " . DB_PREFIX . "store_lang as s on u.store_id=s.store_id  and s.distinguish = 0 and s.lang_id = " . $this->defaulLang . $where;
        //echo $sql;exit;
        $rs = $this->userMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($rs['list'] as $k => $v) {
            $rs['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            if ($v['add_time']) {
                $rs['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            } else {
                $rs['list'][$k]['add_time'] = '';
            }
            $rs['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        $this->assign('p', $p);
        $this->assign('phone', $phone);
        $this->assign('email', $email);
        $this->assign('username', $username);
        $this->assign('list', $rs['list']);
        $this->assign('page', $rs['ph']);
        $this->display('odmUser/index.html');
    }

    /**
     * 启用禁用会员
     * @author wanyan
     * @date 2017-09-15
     */
    public function getStatus() {
        $user_id = !empty($_REQUEST['user_id']) ? htmlspecialchars($_REQUEST['user_id']) : '';
        $is_use = !empty($_REQUEST['is_use']) ? htmlspecialchars($_REQUEST['is_use']) : '';
        $data = array(
            'is_use' => $is_use
        );
        $rs = $this->userMod->doEdit($user_id, $data);
        if ($rs) {
            $this->addLog('会员状态改变操作');
            $this->setData($info = array(), $status = 1, $message = '');
        } else {
            $this->setData($info = array(), $status = 0, $message = '');
        }
    }

    /*
     * 添加ODM会员
     * @author wangshuo
     * @date 2018-4-23 18:41:53
     */

    public function add() {
        $username = !empty($_REQUEST['username']) ? htmlspecialchars(trim($_REQUEST['username'])) : '';
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars(trim($_REQUEST['phone'])) : '';
        $email = !empty($_REQUEST['email']) ? htmlspecialchars(trim($_REQUEST['email'])) : '';
        $store_id = !empty($_REQUEST['store_id']) ? rtrim($_REQUEST['store_id'], ',') : '';
        $where = " where `mark` =1   and  is_kefu = 0  and odm_members =0";
        //搜索
        if ($this->lang_id == 1) {
            if (!empty($username)) {
                $where .= "  and u.username like '%" . $username . "%'";
            }
            if (!empty($phone)) {
                $where .= "  and u.phone like '%" . $phone . "%'";
            }
            if (!empty($email)) {
                $where .= "  and u.email like '%" . $email . "%'";
            }
            if (!empty($store_id)) {
                $where .= "  and u.store_id like '%" . $store_id . "%'";
            }
        } else {
            if (!empty($username)) {
                $where .= "  and u.username like '%" . $username . "%'";
            }
            if (!empty($phone)) {
                $where .= "  and u.phone like '%" . $phone . "%'";
            }
            if (!empty($email)) {
                $where .= "  and u.email like '%" . $email . "%'";
            }
            if (!empty($store_id)) {
                $where .= "  and u.store_id like '%" . $store_id . "%'";
            }
        }
        $where .= " order by `id` desc";
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;


        $sql = "select u.`id`,u.`username`,u.`phone`,u.`email`,u.`add_time`,u.`point`,u.`is_use`,s.store_name,u.odm_members from " . DB_PREFIX . "user as u
            left join " . DB_PREFIX . "store_lang as s on u.store_id=s.store_id  and s.distinguish = 0 and s.lang_id = " . $this->defaulLang . $where;
        //echo $sql;exit;
        $rs = $this->userMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($rs['list'] as $k => $v) {
            $rs['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            if ($v['add_time']) {
                $rs['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            } else {
                $rs['list'][$k]['add_time'] = '';
            }
            $rs['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
        }
        $this->assign('p', $p);
        $this->assign('phone', $phone);
        $this->assign('email', $email);
        $this->assign('username', $username);
        $this->assign('list', $rs['list']);
        $this->assign('page', $rs['ph']);
        $this->assign('store_id', $store_id);
        $this->assign('lang_id', $this->lang_id);
//        $this->assign('store', $this->getUseStore());
        $this->display('odmUser/add.html');
    }

    /**
     * 添加ODM会员
     * @author wangshuo
     * @date 2018-04-23
     */
    public function doAdd() {
//        print_r($_REQUEST);exit;
        $this->load($this->lang_id, 'admin/admin');
        $a = $this->langData;
        $lang_id = !empty($_REQUEST['lang_id']) ? htmlspecialchars($_REQUEST['lang_id']) : '';
        $user_id = !empty($_REQUEST['user_id']) ? htmlspecialchars($_REQUEST['user_id']) : '';
        if ($user_id == '') {
            $this->setData(array(), '0', $a['No_added']);
        }
        $user_id = array_filter(explode(',', $user_id));
        foreach ($user_id as $k => $v) {
            $set = array(
                "odm_members" => 1,
            );
            $query = array(
                "table" => "user",
                'cond' => " `id` = '{$v}' and `mark` =1 and `odm_members` =0",
                'set' => $set,
            );
            $rs = $this->userMod->doUpdate($query);
        }
        if ($rs) {
            $this->addLog('商品添加操作');
            $info['url'] = "?app=odmUser&act=index&lang_id=$lang_id";
            $this->setData($info, $status = '1', $a['add_Success']);
        } else {
            $this->setData($info = array(), $status = '0', $a['add_fail']);
        }
    }

//    /**
//     * 获取启用的站点
//     * @author wangshuo 
//     * @date 2018-4-23
//     */
//    public function getUseStore() {
//        $sql = 'SELECT  c.id,l.store_name  FROM  ' . DB_PREFIX . 'store  as c
//                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
//                 WHERE c.is_open = 1  and  l.lang_id =' . $this->defaulLang;
//        $rs = $this->storeMod->querySql($sql);
//
//        return $rs;
//    }

    /**
     * 删除会员
     * @author wanyan
     * @date 2017-09-15
     */
    public function dele() {
        $this->load($this->lang_id, 'admin/admin');
        $a = $this->langData;
        $id = !empty($_REQUEST['id']) ? htmlspecialchars($_REQUEST['id']) : '';
        $data = array(
            'odm_members' => 0
        );
        $rs = $this->userMod->doEdit($id, $data);
        if ($rs) {
            $this->addLog('会员取消操作');
            $this->setData($info = array(), $status = 1, $a['delete_odmSuccess']);
        } else {
            $this->setData($info = array(), $status = 0, $a['delete_odmfail']);
        }
    }

}
