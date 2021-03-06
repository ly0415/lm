<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/26
 * Time: 15:44
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class UserDialogApp extends BaseStoreApp {

    public $userMod;
    private $pagesize = 10;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->userMod = &m('user');
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 商品的单选弹窗
     */
    public function userDialog() {

        //获取第一页数据
        $lang_id = $_REQUEST['lang_id'];
        $where = '  where  is_fx = 2';
        $sql = 'select  COUNT(*)  as total  from  ' . DB_PREFIX . 'user ' . $where;
        $res = $this->userMod->querySql($sql);
        $total = $res[0]['total'];
        $pagesize = $this->pagesize;
        $totalpage = ceil($total / $pagesize);
        if (empty($totalpage)) {
            $totalpage = 1;
        }
        $this->assign('totalpage', $totalpage);
        //分页定义
        $currentPage = 1;
        $start = ($currentPage - 1) * $pagesize;
        $end = $pagesize;
        $limit = '  limit  ' . $start . ',' . $end;
        $sql = 'select  id,username,email,add_time  from  ' . DB_PREFIX . 'user' . $where . $limit;
        $data = $this->userMod->querySql($sql);
        $this->assign('data', $data);
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
        $this->assign('lang_id', $this->lang_id);
        $this->display('userDialog/userdialog.html');
    }

    /**
     * 获取商品列表
     */
    public function getUserList() {
        $p = $_REQUEST['p'];
        $username = $_REQUEST['username'];
        //分页定义
        $currentPage = !empty($p) ? $p : 1;
        $pagesize = $this->pagesize; //每页显示的条数
        $start = ($currentPage - 1) * $pagesize;
        $end = $pagesize;
        $limit = '  limit  ' . $start . ',' . $end;
        $where = '  where  is_fx = 2';

        if (!empty($username)) {
            $where .= '  and   username  like "%' . $username . '%"';
        }
        $sql = 'select  id,username,email,add_time  from  ' . DB_PREFIX . 'user' . $where . $limit;
        $data = $this->userMod->querySql($sql);

        $this->assign('data', $data);
        $this->display('userDialog/userlist.html');
    }

    /**
     * 搜索物品，统计条数
     * @author wangh
     * @date 2017-06-26
     */
    public function totalPage() {
        $username = $_REQUEST['username'];
        $where = '  where  is_fx = 2';
        if (!empty($username)) {
            $where .= '  and  username  like "%' . $username . '%"';
        }
        $sql = 'select  COUNT(*)  as total  from  ' . DB_PREFIX . 'user' . $where;
        $res = $this->userMod->querySql($sql);
        $total = $res[0]['total'];
        $pagesize = $this->pagesize;
        $totalpage = ceil($total / $pagesize);
        if (!empty($totalpage)) {
            echo json_encode(array('total' => $totalpage));
            exit;
        } else {
            echo json_encode(array('total' => 1));
            exit;
        }
    }

}
