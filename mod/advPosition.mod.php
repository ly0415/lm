<?php
/**
* 广告模型
* @author: jh
* @date: 2017/6/21
*/
if (!defined('IN_ECM')) { die('Forbidden'); }
class AdvPositionMod extends BaseMod{
    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct("ad_position");
    }


    /**
     * 检测注册名称等是否存在
     * @author jh
     * @date 2017/06/21
     */
    public function isExist($type, $value, $id = 0) {
        $cond = "{$type}='{$value}'";
        if ($id) {
            $cond .= " AND position_id!={$id}";
        }
        $query = array('fields' => 'position_id', 'cond' => $cond);
        $info = $this->getOne($query);
        $id = (int)$info['position_id'];
        return $id;
    }
    /*
     * 编辑
     * @author wangshuo
     * @date 2017/09/12
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
        $cond = $data['key'] ? "{$data['key']}={$id}" : "position_id={$id}";
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
    } 

}
?>