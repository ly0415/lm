<?php
/**退款退货表管理模型*/
if (!defined('IN_ECM')) { die('Forbidden'); }
class refundReturnMod  extends  BaseMod{

    public function __construct() {
        parent::__construct("refund_return");
    }
}