<?php
/**
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class  ArticleCateLangMod  extends  BaseMod{

    public function __construct() {
        parent::__construct("article_category_lang");
    }
      public function isExist($type, $value, $id = 0) {
        $cond = "{$type}='{$value}'";
        if ($id) {
            $cond .= " AND  article_cate_id !={$id}";
        }
        $query = array('fields' => 'id', 'cond' => $cond);
        $info = $this->getOne($query);
        $id = (int)$info['id'];
        return $id;
    }

}