<?php
/**
 * 日志模块模型
 * @author jh
 * @date 2017-06-30
 */
if (!defined('IN_ECM')) {die('Forbidden');}
class SystemLogMod extends BaseMod {
    public function __construct(){
        parent::__construct("system_log");
    }
}