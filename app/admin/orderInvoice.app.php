<?php

if (!defined('IN_ECM')) {
    die('Forbidden');
}

class OrderInvoiceApp extends BackendApp
{
    private $orderInvoiceMod;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->orderInvoiceMod = &m('orderInvoice');
    }

    /**
     * 发票列表
     */
    public function index()
    {
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim($_REQUEST['order_sn'])) : '';
        $status = !empty($_REQUEST['status']) ? htmlspecialchars(trim($_REQUEST['status'])) : '';
        $this->assign('order_sn', $order_sn);
        $this->assign('status', $status);
        $where = " where 1=1 ";
        if (!empty($order_sn)) {
            $where .= " and a.order_sn like '%{$order_sn}%' ";
        }
        if (!empty($status)) {
            $where .= " and a.status = {$status} ";
        }
        $sql = "select a.id,a.order_sn,a.invoice_money,a.head_type,a.head_title,a.status,a.filepath,a.add_time,b.store_name,c.username ".
            " from bs_order_invoice as a ".
            " left join bs_store_lang as b on a.store_id = b.store_id and b.lang_id = {$this->lang_id} and b.distinguish=0 ".
            " left join bs_user as c on a.user_id = c.id";
        $data = $this->orderInvoiceMod->querySqlPageData($sql . $where . " order by a.id desc ", $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($data['list'] as &$v) {
            $v['add_time'] = date("Y-m-d", $v['add_time']);
        }
        $this->assign('list', $data['list']);
        $this->assign('page', $data['ph']);
        $this->display('orderInvoice/index.html');
    }

    /**
     * 电子发票路径保存
     */
    public function saveFilePath()
    {
        $id = !empty($_REQUEST['id']) ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        $filepath = !empty($_REQUEST['filepath']) ? htmlspecialchars(trim($_REQUEST['filepath'])) : '';
        $this->orderInvoiceMod->doEdit($id, array('filepath' => $filepath, 'status' => 2));
        jsonResult('success');
    }

    /**
     * 详情
     */
    public function info()
    {
        $id = !empty($_REQUEST['id']) ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        $sql = "select a.*,b.store_name,c.username ".
            " from bs_order_invoice as a ".
            " left join bs_store_lang as b on a.store_id = b.store_id and b.lang_id = {$this->lang_id} and b.distinguish=0 ".
            " left join bs_user as c on a.user_id = c.id".
            " where a.id = {$id}";
        $res = $this->orderInvoiceMod->querySql($sql);
        $data = $res[0];
        $data['add_time'] = date("Y-m-d", $data['add_time']);
        $this->assign('data', $data);
        $this->display('orderInvoice/info.html');
    }

    /**
     * 删除
     */
    public function dele()
    {
        $id = !empty($_REQUEST['id']) ? htmlspecialchars($_REQUEST['id']) : '';
        $rs = $this->orderInvoiceMod->doDrop($id);
        if ($rs) {
            jsonResult('删除成功');
        } else {
            jsonError('删除失败');
        }
    }

}
