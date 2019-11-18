<?php
/**
 * 商品模型模型
 * @author wh
 * @date 2017-06-30
 */
if (!defined('IN_ECM')) {die('Forbidden');}
class  GoodsSpecMod  extends  BaseMod{
    public function __construct(){
        parent::__construct("goods_spec");
    }
    /*
     * 获取对应语言的字段
     */
    public function getLangData($lang_id){
        $sql_spec = "select `id`,`name` from ".DB_PREFIX."goods_spec as g
                     left join ".DB_PREFIX."goods_spec_lang as l on g.id = l.spec_id where l.lang_id=".$lang_id;
        $specInfo = $this->querySql($sql_spec);
        return $specInfo;
    }
}