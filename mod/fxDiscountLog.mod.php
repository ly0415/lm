<?php
/**
 * 管理员模块模型
 * @author: jh
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class FxDiscountLogMod extends BaseMod {
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct("fx_discount_log");
    }
    /**
     * 查询有无正在审核的申请
     * @author: run
     * @date: 2018/10/18
     */
    public function haveCheck($fx_user_id){
        $info = $this->getOne(array("cond" => "is_check = 1 and mark = 1 and fx_user_id=" . $fx_user_id));
        return $info;
    }

}