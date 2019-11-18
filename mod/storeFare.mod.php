<?php

/**
 * 店铺运费名称模块模型
 * @author zhangkx  
 * @date 2019/4/9
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class StoreFareMod extends BaseMod {

    /**
     * 构造函数
     */
    public function __construct() 
    {
        parent::__construct("store_fare");
    }

    
}

?>