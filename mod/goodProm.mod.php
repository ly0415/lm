<?php
if (!defined('IN_ECM')) { die('Forbidden'); }
class GoodPromMod extends BaseMod{
    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct("promotion_sale");
    }
}