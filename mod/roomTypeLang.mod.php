<?php
/**
 * 商品分类模型
 * @author: jh
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class roomTypeLangMod extends BaseMod{
    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct("room_type_lang");
    }
    /**
     * 获取商品一级分类
     * @author wanyan
     * @date 2017-8-2
     */
    public function getParent(){
        $sql = "select `id`,`name`,`is_hot`,`parent_id`,`is_show`,`sort_order`,`add_time` from ".DB_PREFIX."goods_category where `parent_id` = '0'";
        $res =$this->querySql($sql);
        return $res;
    }

}
?>