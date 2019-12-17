<?php
/**
 * 商品模型模型
 * @author wh
 * @date 2017-06-30
 */
if (!defined('IN_ECM')) {die('Forbidden');}
class  GoodsAttriLangMod  extends  BaseMod{
    public function __construct(){
        parent::__construct("goods_attr_lang");
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


}