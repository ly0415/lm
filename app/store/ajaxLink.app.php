<?php

/**
 * 多级联动控制器
 * User: jh
 * Date: 2018/6/7
 * Time: 20:35
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class AjaxLinkApp extends BaseStoreApp
{
    private $defaultMod;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->defaultMod = &m('order');
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
    }

    /**
     * 业务类型多级联动
     * @author jh
     * @date 2018/6/7
     */
    public function roomType()
    {
        $parent_id = $_REQUEST['parent_id'] ? htmlspecialchars(trim($_REQUEST['parent_id'])) : 0;
        $sql = "select a.id,b.type_name as name from " .
            DB_PREFIX ."room_type as a left join " .
            DB_PREFIX . "room_type_lang as b on a.id = b.type_id ".
            " where b.lang_id = {$this->languageId} and a.superior_id = {$parent_id} order by a.sort asc,a.id asc ";
        $data = $this->defaultMod->querySql($sql);
        echo json_encode($data);
        exit();
    }
}