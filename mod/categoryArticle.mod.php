<?php
/**
 * 文章分类关联表
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class categoryArticleMod extends BaseMod{
    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct("category_article");
    }

}
?>