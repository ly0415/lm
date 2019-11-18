<?php
/**
 * 商品模型模型
 * @author wh
 * @date 2017-06-30
 */
if (!defined('IN_ECM')) {die('Forbidden');}
class  GoodsStyleMod  extends  BaseMod{
    public function __construct(){
        parent::__construct("goods_style");
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
    /*
     * 获取对应语言的模型
     * @author lee
     * @date 2017-10-10 15:50:41
     */
    public function getLangData($lang_id){
        $styleSql="select c.id ,l.style_name from ".DB_PREFIX."goods_style as c left join ".DB_PREFIX."goods_style_lang as l on c.id=l.style_id where l.lang_id=".$lang_id;
        $style_list=$this->querySql($styleSql);
        return $style_list;
    }


}