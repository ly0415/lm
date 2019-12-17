<?php
/**
* 足迹模型
* @author: wangshuo
* @date: 2017/9/18
*/
if (!defined('IN_ECM')) { die('Forbidden'); }
class footPrintMod extends BaseMod{
    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct("user_footprint");
    }

}
?>