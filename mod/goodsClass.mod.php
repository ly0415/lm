<?php
/**
* 商品分类模型
* @author: jh
* @date: 2017/6/21
*/
if (!defined('IN_ECM')) { die('Forbidden'); }
class goodsClassMod extends BaseMod{
    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct("goods_category");
    }
    /**
     *  数组
     * @param type $cat_id
     */
    function find_parent_cat($cat_id)
    {
        if($cat_id == null)
            return array();

        $list =   $this->getData(array("cond"=>"1=1",'fields'=>'id,parent_id,level'));
        foreach($list as $k=>$v){
            $cat_list[$v['id']]=$v;
        }
        $cat_level_arr[$cat_list[$cat_id]['level']] = $cat_id;

        // 找出他老爸
        $parent_id = $cat_list[$cat_id]['parent_id'];
        if($parent_id > 0)
            $cat_level_arr[$cat_list[$parent_id]['level']] = $parent_id;
        // 找出他爷爷
        $grandpa_id = $cat_list[$parent_id]['parent_id'];
        if($grandpa_id > 0)
            $cat_level_arr[$cat_list[$grandpa_id]['level']] = $grandpa_id;

        // 建议最多分 3级, 不要继续往下分太多级
        // 找出他祖父
        $grandfather_id = $cat_list[$grandpa_id]['parent_id'];
        if($grandfather_id > 0)
            $cat_level_arr[$cat_list[$grandfather_id]['level']] = $grandfather_id;
        return $cat_level_arr;
    }
    /*
     * 获取对应语言下的分类
     * @author lee
     * @date 2017-10-10 15:31:34
     */
    public function getLangData($parent=0,$lang_id){
        $catSql="select c.id,l.category_name as `name`,c.parent_id from ".DB_PREFIX."goods_category as c
        left join ".DB_PREFIX."goods_category_lang as l on c.id=l.category_id
        where c.parent_id=".$parent."  and l.lang_id=".$lang_id;
        $cat_list = $this->querySql($catSql);
        return $cat_list;
    }
    /*
    * 获取对应语言下的分类
    * @author lee
    * @date 2017-10-10 15:31:34
    */
    public function getLangInfo($id,$lang_id){
        $catSql="select c.id,l.category_name as `name`,c.parent_id from ".DB_PREFIX."goods_category as c
        left join ".DB_PREFIX."goods_category_lang as l on c.id=l.category_id
        where c.id=".$id."  and l.lang_id=".$lang_id;
        $cat_list = $this->querySql($catSql);
        return $cat_list;
    }
}
?>