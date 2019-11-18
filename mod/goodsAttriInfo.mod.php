<?php
/**
 * 商品模型模型
 * @author wh
 * @date 2017-06-30
 */
if (!defined('IN_ECM')) {die('Forbidden');}
class  GoodsAttriInfoMod  extends  BaseMod{
    public function __construct(){
        parent::__construct("goods_attr");
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
    /**
     * 编辑信息
     * @author lvj
     * @date 2011-6-3
     * @param int $id - 记录编号
     * @param array $data - 编辑数据
     * @return bool
     */
    public function doEdit($id, $data, $is_sql = false){
        // 参数错误
        $id = intval($id);
        if (!$id || !$data || !is_array($data)) {
            return false;
        }
        // 对象表
        $table = $data['table'] ? (DB_PREFIX . $data['table']) : $this->table;
        unset ($data ['table']);
        // key字段
        $cond = $data['key'] ? "{$data['key']}={$id}" : "attr_id={$id}";
        unset ($data ['key']);

        $flag = true;
        foreach ($data as $key => $val) {
            if ($flag) {
                $str .= "{$key} = '{$val}'";
                $flag = false;
            } else
                $str .= ",{$key} = '{$val}'";
        }
        $set = $str;
        $sql = "update {$table} set {$set} where {$cond}";
        if ($is_sql) {
            echo $sql . "<BR>\r\n";
        }
        $ret = $this->db->Execute($sql);
        return $ret ? true : false;
    } // end of doEdit
    /*
 * 获取对应语言下的分类
 * @author lee
 * @date 2017-10-10 15:31:34
 */
    public function getLangData($good_id,$lang_id){
        $catSql="select c.*,l.`name` as attr_name from ".DB_PREFIX."goods_attr as c
        left join ".DB_PREFIX."goods_attr_lang as l on c.attr_id=l.a_id
        where c.goods_id=".$good_id."  and l.lang_id=".$lang_id;
        $cat_list = $this->querySql($catSql);
        return $cat_list;
    }


}