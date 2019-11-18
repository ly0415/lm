<?php
/**
 * 发票
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class OrderInvoiceMod extends BaseMod
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct("order_invoice");
    }

}

?>