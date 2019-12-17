<?php

/**
 * 发票抬头列表
 * @author jh
 * @date 2019-03-22
 */
class StoreUserInvoiceheadApp extends BaseWxApp
{
    private $invoiceheadMod;

    public function __construct()
    {
        parent::__construct();
        $this->invoiceheadMod = &m('storeUserInvoicehead');
    }

    /**
     * 异步获取发票抬头列表并展示
     */
    public function getHeadList()
    {
        $sql = "select id,head_type,head_title,is_default from bs_store_user_invoicehead where mark=1 and user_id={$this->userId}";
        $data = $this->invoiceheadMod->querySql($sql);
        $this->assign('data', $data);
        $str = self::$smarty->fetch("orderInvoice/getHeadList.html");
        $this->setData($str, '1', 'success');
    }

    /**
     * 异步获取单个发票抬头信息并填充
     */
    public function getHeadCurrent()
    {
        $id = !empty($_REQUEST['id']) ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        $invoiceheadInfo = $this->invoiceheadMod->getOne(array("cond" => "id={$id}"));
        $this->assign('invoiceheadInfo', $invoiceheadInfo);
        $str = self::$smarty->fetch("orderInvoice/getHeadCurrent.html");
        $this->setData($str, '1', 'success');
    }

    /**
     * 编辑发票抬头
     */
    public function edit()
    {
        $id = !empty($_REQUEST['id']) ? htmlspecialchars(trim($_REQUEST['id'])) : 0;
        $data = $this->invoiceheadMod->getOne(array("cond" => "id={$id}"));
        $this->assign('webtitle', "编辑抬头");
        $this->assign('invoiceheadInfo', $data);
        $this->display("orderInvoice/headEdit.html");
    }

    /**
     * 提交抬头编辑
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
        $isQY = ($head_type == 2) ? true : false;//是否是企业发票
        $data['head_type'] = $head_type;
        $data['head_title'] = $head_title;
        $data['taxnum'] = $isQY ? $taxnum : '';
        $data['bankname'] = $isQY ? $bankname : '';
        $data['bankaccount'] = $isQY ? $bankaccount : '';
        $data['address'] = $isQY ? $address : '';
        $data['phone'] = $isQY ? $phone : '';
        $data['is_default'] = $is_default;
        $data['modify_time'] = time();
        $this->invoiceheadMod->doEdit($id, $data);
        $this->setData(array(), '1', '编辑成功');
    }

    /**
     * 删除
     */
    public function dele()
    {
        $id = !empty($_REQUEST['id']) ? htmlspecialchars($_REQUEST['id']) : '';
        $rs = $this->invoiceheadMod->doMark($id);
        if ($rs) {
            $this->setData(array(), '1', '删除成功');
        } else {
            $this->setData(array(), '0', '删除失败');
        }
    }
}