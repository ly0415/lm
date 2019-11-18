<?php
/**
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class  ArticleCateMod  extends  BaseMod{

    public function __construct() {
        parent::__construct("article_category");
    }
    /**
     * 检测注册名称等是否存在
     * @author jh
     * @date 2017/06/21
     */
    public function isExist($type, $value, $id = 0) {
        $cond = "{$type}='{$value}'";
        if ($id) {
            $cond .= " AND id!={$id}";
        }
        $query = array('fields' => 'id', 'cond' => $cond);
        $info = $this->getOne($query);
        $id = (int)$info['id'];
        return $id;
    }
}