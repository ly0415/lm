<?php

/**
 * 异步或弹窗查询数据
 * User: jh
 * Date: 2018/6/7
 * Time: 20:35
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class AjaxSearchApp extends BaseApp
{
    private $defaultMod;
    private $pageSize = 5; //每页显示条数

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->defaultMod = &m('user');
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
    }

    /**
     * 异步检索用户手机号
     * @author jh
     * @date 2018/6/7
     */
    public function userPhone()
    {
        $phone = $_REQUEST['phone'] ? htmlspecialchars(trim($_REQUEST['phone'])) : 0;
        $storeId = $_REQUEST['storeId'] ? htmlspecialchars(trim($_REQUEST['storeId'])) : 0;
        $where = ' where a.mark = 1 and a.is_use = 1 and a.is_kefu = 0 ';
        if ($phone) {
            $where .= ' and phone like "%' . $phone . '%" ';
        }
        $sql = "select a.id,a.phone,count(a.id) as buyer_num from bs_user as a " .
            " left join bs_order_{$storeId} as b on a.id = b.buyer_id " .
            $where ." group by a.id order by buyer_num desc ";
        $data = $this->defaultMod->querySql($sql);
        echo json_encode($data);
        exit();
    }

    /**
     * 查找带回单个人员
     * @author jh
     * @date 2017-08-10
     */
    public function userDialog()
    {
        $ids = !empty($_REQUEST['ids']) ? htmlspecialchars(trim($_REQUEST['ids'])) : '';
        $param = !empty($_REQUEST['param']) ? htmlspecialchars(trim($_REQUEST['param'])) : '';
        $currentPage = !empty($_REQUEST['p']) ? htmlspecialchars(trim($_REQUEST['p'])) : 1;
        $is_ajax = !empty($_REQUEST['is_ajax']) ? htmlspecialchars(trim($_REQUEST['is_ajax'])) : 0;
        $this->assign('param', $param);
        $this->assign('is_ajax', $is_ajax);
        //wheret条件
        $where = ' where a.mark = 1 and a.is_use = 1 and a.is_kefu = 0 ';
        if (!empty($ids)) {
            $where .= " and a.id not in (" . $ids . ")";
        }
        if (!empty($param)) {
            $where .= ' and (a.username like "%' . $param . '%" or a.phone like "%' . $param . '%") ';
        }
        //计算总页数
        $sql = 'select count(*) as total from ' . DB_PREFIX . 'user as a ' . $where;
        $totalInfo = $this->defaultMod->querySql($sql);
        $total = $totalInfo[0]['total'];//总数据量
        $pagesize = $this->pageSize;//每页数据量
        $totalpage = ceil($total / $pagesize);//总页数
        if (empty($totalpage)) {
            $totalpage = 1;
        }
        $this->assign('totalpage', $totalpage);
        //获取当前页数据
        $start = ($currentPage - 1) * $pagesize;
        $end = $pagesize;
        $limit = ' limit ' . $start . ',' . $end;
        $sql = 'select a.id,a.username,a.phone from ' . DB_PREFIX . 'user as a ' . $where . ' order by a.id desc' . $limit;
        $data = $this->defaultMod->querySql($sql);
        $this->assign('list', $data);
        $this->display('ajaxSearch/userDialog.html');
    }

    /**
     * 查找带回单个分销信息
     * @author jh
     * @date 2017-08-10
     */
    public function fxUserDialog()
    {
        $param = !empty($_REQUEST['param']) ? htmlspecialchars(trim($_REQUEST['param'])) : '';
        $currentPage = !empty($_REQUEST['p']) ? htmlspecialchars(trim($_REQUEST['p'])) : 1;
        $is_ajax = !empty($_REQUEST['is_ajax']) ? htmlspecialchars(trim($_REQUEST['is_ajax'])) : 0;
        $this->assign('param', $param);
        $this->assign('is_ajax', $is_ajax);
        //wheret条件
        $where = ' where a.mark = 1 and a.is_check = 2 and a.level = 3 ';
        if (!empty($param)) {
            $where .= ' and a.fx_code like "%' . $param . '%" ';
        }
        //计算总页数
        $sql = 'select count(*) as total from ' . DB_PREFIX . 'fx_user as a ' . $where;
        $totalInfo = $this->defaultMod->querySql($sql);
        $total = $totalInfo[0]['total'];//总数据量
        $pagesize = $this->pageSize;//每页数据量
        $totalpage = ceil($total / $pagesize);//总页数
        if (empty($totalpage)) {
            $totalpage = 1;
        }
        $this->assign('totalpage', $totalpage);
        //获取当前页数据
        $start = ($currentPage - 1) * $pagesize;
        $end = $pagesize;
        $limit = ' limit ' . $start . ',' . $end;
        $sql = 'select a.id,a.fx_code,a.discount,a.real_name,a.phone from ' . DB_PREFIX . 'fx_user as a ' . $where . ' order by a.id desc' . $limit;
        $data = $this->defaultMod->querySql($sql);
        $this->assign('list', $data);
        $this->display('ajaxSearch/fxUserDialog.html');
    }
}