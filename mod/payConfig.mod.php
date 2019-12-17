<?php
  /**
   *  配置支付模块
  */
if (!defined('IN_ECM')) { die('Forbidden'); }
class  PayConfigMod  extends  BaseMod{

    public function __construct() {
        parent::__construct("pay");
    }
}