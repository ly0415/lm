<?php
/**
 * 区域业务关联表
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class storebusinessMod extends BaseMod
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct("store_business");
    }

    /**
     * 根据store_id获取buss_id
     */
    public function getInfoByStoreid($store_id)
    {
        $sql = 'select buss_id from ' . $this->table . ' where store_id = ' . $store_id;
        $data = $this->querySql($sql);
        return $data;
    }
}

?>