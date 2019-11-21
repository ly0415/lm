<?php

/**
 * 订单发票
 * @author jh
 * @date 2019-03-22
 */
class OrderInvoiceApp extends BasePhApp
{
    private $orderInvoiceMod;
    private $invoiceheadMod;

    public function __construct()
    {
        parent::__construct();
        $this->orderInvoiceMod = &m('orderInvoice');
        $this->invoiceheadMod = &m('storeUserInvoicehead');
    }

    /**
     * 申请发票
     */
    public function add()
    {
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars(trim($_REQUEST['store_id'])) : '';
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim($_REQUEST['order_sn'])) : '';
        //判断是否存在开票申请
        $invoiceInfo = $this->orderInvoiceMod->getOne(array("cond" => "store_id={$store_id} and order_sn='{$order_sn}'"));
        if (!empty($invoiceInfo)) {
            $this->setData(array(), '0', '已存在开票申请');
        }
        //获取订单信息
        $userOrderMod = &m('userOrder');
        $orderInfo = $userOrderMod->getOne(array("cond" => "store_id={$store_id} and order_sn='{$order_sn}'"));
        if (empty($orderInfo)) {
            $this->setData(array(), '0', '获取订单信息失败');
        }
        //获取默认抬头信息
        $invoiceheadInfo = $this->invoiceheadMod->getOne(array("cond" => "user_id={$this->userId} and is_default=2 and mark=1"));
        //映射页面
        $data['orderInfo'] = $orderInfo;
        $data['invoiceheadInfo'] = $invoiceheadInfo;
        $this->setData($data, '1', 'success');
    }

    /**
     * 提交发票申请
     */
    public function doAdd()
    {
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars(trim($_REQUEST['store_id'])) : '';
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim($_REQUEST['order_sn'])) : '';
        $invoice_money = !empty($_REQUEST['invoice_money']) ? htmlspecialchars(trim($_REQUEST['invoice_money'])) : 0;
        $head_type = !empty($_REQUEST['head_type']) ? htmlspecialchars(trim($_REQUEST['head_type'])) : 1;
        $head_title = !empty($_REQUEST['head_title']) ? htmlspecialchars(trim($_REQUEST['head_title'])) : '';
        $taxnum = !empty($_REQUEST['taxnum']) ? htmlspecialchars(trim($_REQUEST['taxnum'])) : '';
        $bankname = !empty($_REQUEST['bankname']) ? htmlspecialchars(trim($_REQUEST['bankname'])) : '';
        $bankaccount = !empty($_REQUEST['bankaccount']) ? htmlspecialchars(trim($_REQUEST['bankaccount'])) : '';
        $address = !empty($_REQUEST['address']) ? htmlspecialchars(trim($_REQUEST['address'])) : '';
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars(trim($_REQUEST['phone'])) : '';
        $is_default = !empty($_REQUEST['is_default']) ? htmlspecialchars(trim($_REQUEST['is_default'])) : 1;
        if (empty($this->userId)) {
            $this->setData(array(), '0', '获取用户信息失败');
        }
        if (empty($store_id) || empty($order_sn)) {
            $this->setData(array(), '0', '系统错误');
        }
        if (empty($head_title)) {
            $this->setData(array(), '0', '请填写抬头名称');
        }
        //清除默认抬头
        if ($is_default == 2) {
            $this->invoiceheadMod->doUpdate(array("set" => "is_default = 1", "cond" => "user_id={$this->userId}"));
        }
        //插入或修改发票抬头
        $isQY = ($head_type == 2) ? true : false;//是否是企业发票
        $headData['user_id'] = $this->userId;
        $headData['head_type'] = $head_type;
        $headData['head_title'] = $head_title;
        $headData['taxnum'] = $isQY ? $taxnum : '';
        $headData['bankname'] = $isQY ? $bankname : '';
        $headData['bankaccount'] = $isQY ? $bankaccount : '';
        $headData['address'] = $isQY ? $address : '';
        $headData['phone'] = $isQY ? $phone : '';
        $headData['is_default'] = $is_default;
        $headData['modify_time'] = time();
        $oldheadInfo = $this->invoiceheadMod->getOne(array("cond" => "mark=1 and user_id={$this->userId} and head_title='{$head_title}'"));
        if (empty($oldheadInfo)) {
            $headData['add_time'] = time();
            $this->invoiceheadMod->doInsert($headData);
        } else {
            $this->invoiceheadMod->doEdit($oldheadInfo['id'], $headData);
        }
        //插入订单发票表
        $rand = $this->buildNo(1);
        $invoice_sn = date('YmdHis') . $rand[0];
        $invoiceData['order_sn'] = $order_sn;
        $invoiceData['store_id'] = $store_id;
        $invoiceData['user_id'] = $this->userId;
        $invoiceData['invoice_sn'] = $invoice_sn;
        $invoiceData['invoice_money'] = $invoice_money;
        $invoiceData['head_type'] = $headData['head_type'];
        $invoiceData['head_title'] = $headData['head_title'];
        $invoiceData['taxnum'] = $headData['taxnum'];
        $invoiceData['bankname'] = $headData['bankname'];
        $invoiceData['bankaccount'] = $headData['bankaccount'];
        $invoiceData['address'] = $headData['address'];
        $invoiceData['phone'] = $headData['phone'];
        $invoiceData['add_time'] = time();
        $this->orderInvoiceMod->doInsert($invoiceData);
        $this->setData(array(), '1', '申请成功');
    }

    /**
     * 查看发票
     */
    public function info()
    {
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars(trim($_REQUEST['store_id'])) : '';
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim($_REQUEST['order_sn'])) : '';
        //判断是否存在开票申请
        $invoiceInfo = $this->orderInvoiceMod->getOne(array("cond" => "store_id={$store_id} and order_sn='{$order_sn}'"));
        if (empty($invoiceInfo)) {
            $this->setData(array(), '0', '获取发票信息失败');
        }
        $invoiceInfo['add_time'] = date('Y-m-d', $invoiceInfo['add_time']);
        if ($invoiceInfo['filepath']) {
            $invoiceInfo['filepath'] = SITE_URL . '/' . $invoiceInfo['filepath'];
        }
        $this->setData($invoiceInfo, '1', 'success');
    }

    /**
     * 编辑发票信息
     */
    public function edit()
    {
        $id = !empty($_REQUEST['id']) ? htmlspecialchars(trim($_REQUEST['id'])) : 0;
        $data = $this->orderInvoiceMod->getOne(array("cond" => "id={$id}"));
        if (empty($data)) {
            $this->setData(array(), '0', '获取发票信息失败');
        }
        $data['add_time'] = date('Y-m-d', $data['add_time']);
        if ($data['filepath']) {
            $data['filepath'] = SITE_URL . '/' . $data['filepath'];
        }
        $this->setData($data, '1', 'success');
    }

    /**
     * 修改发票申请
     */
    public function doEdit()
    {
        $id = !empty($_REQUEST['id']) ? htmlspecialchars(trim($_REQUEST['id'])) : 0;
        $head_type = !empty($_REQUEST['head_type']) ? htmlspecialchars(trim($_REQUEST['head_type'])) : 1;
        $head_title = !empty($_REQUEST['head_title']) ? htmlspecialchars(trim($_REQUEST['head_title'])) : '';
        $taxnum = !empty($_REQUEST['taxnum']) ? htmlspecialchars(trim($_REQUEST['taxnum'])) : '';
        $bankname = !empty($_REQUEST['bankname']) ? htmlspecialchars(trim($_REQUEST['bankname'])) : '';
        $bankaccount = !empty($_REQUEST['bankaccount']) ? htmlspecialchars(trim($_REQUEST['bankaccount'])) : '';
        $address = !empty($_REQUEST['address']) ? htmlspecialchars(trim($_REQUEST['address'])) : '';
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars(trim($_REQUEST['phone'])) : '';
        $is_default = !empty($_REQUEST['is_default']) ? htmlspecialchars(trim($_REQUEST['is_default'])) : 1;
        if (empty($id)) {
            $this->setData(array(), '0', '系统错误');
        }
        if (empty($head_title)) {
            $this->setData(array(), '0', '请填写抬头名称');
        }
        //清除默认抬头
        if ($is_default == 2) {
            $this->invoiceheadMod->doUpdate(array("set" => "is_default = 1", "cond" => "user_id={$this->userId}"));
        }
        //插入或修改发票抬头
        $isQY = ($head_type == 2) ? true : false;//是否是企业发票
        $headData['user_id'] = $this->userId;
        $headData['head_type'] = $head_type;
        $headData['head_title'] = $head_title;
        $headData['taxnum'] = $isQY ? $taxnum : '';
        $headData['bankname'] = $isQY ? $bankname : '';
        $headData['bankaccount'] = $isQY ? $bankaccount : '';
        $headData['address'] = $isQY ? $address : '';
        $headData['phone'] = $isQY ? $phone : '';
        $headData['is_default'] = $is_default;
        $headData['modify_time'] = time();
        $oldheadInfo = $this->invoiceheadMod->getOne(array("cond" => "mark=1 and user_id={$this->userId} and head_title='{$head_title}'"));
        if (empty($oldheadInfo)) {
            $headData['add_time'] = time();
            $this->invoiceheadMod->doInsert($headData);
        } else {
            $this->invoiceheadMod->doEdit($oldheadInfo['id'], $headData);
        }
        //插入订单发票表
        $invoiceData['head_type'] = $headData['head_type'];
        $invoiceData['head_title'] = $headData['head_title'];
        $invoiceData['taxnum'] = $headData['taxnum'];
        $invoiceData['bankname'] = $headData['bankname'];
        $invoiceData['bankaccount'] = $headData['bankaccount'];
        $invoiceData['address'] = $headData['address'];
        $invoiceData['phone'] = $headData['phone'];
        $this->orderInvoiceMod->doEdit($id, $invoiceData);
        $this->setData(array(), '1', '修改成功');
    }

    /**
     * 发送邮箱
     * @author jh
     * @date 2019/03/29
     */
    public function saveEmail()
    {
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : 0;
        $email = $_REQUEST['email'] ? htmlspecialchars(trim($_REQUEST['email'])) : '';
        if (empty($id)) {
            jsonError('系统错误!');
        }
        if (empty($email)) {
            jsonError('请填写邮箱!');
        }
        if (!isEmail($email)) {
            jsonError('邮箱填写错误!');
        }
        $invoiceInfo = $this->orderInvoiceMod->getOne(array("cond" => "id={$id}"));
        if (empty($invoiceInfo) || empty($invoiceInfo['filepath'])) {
            jsonError('系统错误!');
        }
        require_once ROOT_PATH . '/includes/classes/class.sendEmail.php';
        $emailMod = new sendEmail();
        $link = ROOT_PATH . "/" . $invoiceInfo['filepath'];
        $body = "亲爱的艾美瑞用户:<br/>您的订单编号为【{$invoiceInfo['order_sn']}】的电子发票已生成，请通过附件下载。";
        $rs = $emailMod->send($email, '电子发票', $body, '艾美瑞', $link);
        if ($rs) {
            jsonResult('发送成功!');
        } else {
            jsonError('发送失败!');
        }
    }
}