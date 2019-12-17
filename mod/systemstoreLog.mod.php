<?php
/**
 * 日志模块模型
 * @author jh
 * @date 2017-06-30
 */
if (!defined('IN_ECM')) {die('Forbidden');}
class systemstoreLogMod extends BaseMod {
    public function __construct(){
        parent::__construct("system_store_log");
    }
}