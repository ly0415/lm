<?php
/**
 * 商品模型模型
 * @author wh
 * @date 2017-06-30
 */
if (!defined('IN_ECM')) {die('Forbidden');}
class  GoodsTypeMod  extends  BaseMod{
    public function __construct(){
        parent::__construct("goods_type");
    }

    /**
     * 检测注册名称等是否存在
     * @author jh
     * @date 2017/06/21
     */
    public function isExist($type, $value, $id = 0) {
        $cond = "{$type}='{$value}'";
        $cond .= '  and mark =1  ';
        if ($id) {
            $cond .= " AND id!={$id}";
        }
        $query = array('fields' => 'id', 'cond' => $cond);
        $info = $this->getOne($query);
        $id = (int)$info['id'];
        return $id;
    }
    /*
     * 获取默认语言ID
     */
    public function getLangData($lang_id){
        $typeSql="select c.id ,l.type_name as `name` from ".DB_PREFIX."goods_type as c
                  left join ".DB_PREFIX."goods_type_lang as l on c.id=l.type_id
                  where c.mark=1 and  l.lang_id=".$lang_id;
        $type_list=$this->querySql($typeSql);
        return $type_list;
    }


}