<?php
/**
 * 管理员模块模型
 * @author: jh
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class FxOutmoneyApplyMod extends BaseMod {
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct("fx_outmoney_apply");
    }

    /**
     * 提现申请来源
     *
     * @var array
     */
    public $source = array(
        '1' => '微信端',
        '2' => '前台',
        '3' => '小程序',
    );

}