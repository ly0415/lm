<?php
/**
 * 管理员模块模型
 * @author: jh
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class CombinedGoodsMod extends BaseMod {
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct("combined_goods");
    }
    /*
     * 获取团购商品详情
     */
    public function getLangInfo($id,$lang_id){
        $info=$this->getOne(array("cond"=>"id=".$id));
        $sqlLang="select * from ".DB_PREFIX."store_goods_lang where store_good_id=".$id." and lang_id=".$lang_id;
        $langInfo=$this->querySql($sqlLang);
        if($langInfo){
            $info['goods_name']=$langInfo[0]['goods_name'];
            $info['goods_remark']=$langInfo[0]['goods_remark'];
            $info['keywords']=$langInfo[0]['keywords'];
            $info['goods_content']=$langInfo[0]['goods_content'];
        }
        return $info;
    }
}
?>