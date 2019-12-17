<?php
/**
 * 商品分类模型
 * @author: jh
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class goodsBrandMod extends BaseMod{
    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct("goods_brand");
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
    /*
     * 获取默认语言的品牌
     * @author lee
     * @date 2017-10-10 15:48:18
     */
    public function  getLangData($lang_id,$id){
        $where="l.lang_id=".$lang_id;
        if($id){
            $where.=" and c.id=".$id;
        }
        $brandSql="select c.id,l.brand_name as `name` from ".DB_PREFIX."goods_brand as c left join ".DB_PREFIX."goods_brand_lang as l on c.id=l.brand_id where ".$where;
        $brand_list=$this->querySql($brandSql);
        return $brand_list;
    }


}
?>