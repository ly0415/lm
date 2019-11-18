<?php

/**
 * 商品分类模型
 * @author: jh
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class roomTypeCateMod extends BaseMod {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct("room_category");
    }

    /**
     * 获取商品一级分类
     * @author wanyan
     * @date 2017-8-2
     */
    public function getParent() {
        $sql = "select `id`,`name`,`is_hot`,`parent_id`,`is_show`,`sort_order`,`add_time` from " . DB_PREFIX . "goods_category where `parent_id` = '0'";
        $res = $this->querySql($sql);
        return $res;
    }

//    /**
//     * 获取房间名称
//     * @author wanyan
//     * @date 2017-8-2
//     */
//    public function getRoom() {
////        $sql = "select `id`,`room_name` from " . DB_PREFIX . "room_type";
//         $sql = 'SELECT  c.id,l.`type_name`  FROM  ' 
//                 . DB_PREFIX . 'room_type AS c
//                 LEFT JOIN  ' 
//                 . DB_PREFIX . 'room_type_lang AS l  ON c.`id` = l.`type_id`';
//        $res = $this->querySql($sql);
//        return $res;
//    }

    /**
     * 获取商品一级分类
     * @author wanyan
     * @date 2017-8-2
     */
    public function getParentName($id) {
//        $sql = "select `id`,`name`,`parent_id` from ".DB_PREFIX."goods_category where `id` = '{$id}'";
        $sql = 'SELECT  c.id,l.`category_name`,c.`parent_id`  FROM  ' . DB_PREFIX . 'goods_category AS c
                 LEFT JOIN  ' . DB_PREFIX . 'goods_category_lang AS l  ON c.`id` = l.`category_id`
                 WHERE c.`id` = ' . $id;
//         print_r($sql);exit;

        $res = $this->querySql($sql);
        return $res;
    }

    /**
     * 获取商品一级分类
     * @author wanyan
     * @date 2017-8-2
     */
    public function getParentById($id) {
//        $sql = "select `id`,`name`,`parent_id` from " . DB_PREFIX . "goods_category where `parent_id` = '{$id}'";
        $sql = 'SELECT  c.id,l.`category_name`,c.`parent_id`  FROM  ' . DB_PREFIX . 'goods_category AS c
                 LEFT JOIN  ' . DB_PREFIX . 'goods_category_lang AS l  ON c.`id` = l.`category_id`
                 WHERE c.`parent_id` = ' . $id;
        $res = $this->querySql($sql);
        return $res;
    }

}

?>