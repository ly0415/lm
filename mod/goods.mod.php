<?php
/**
* 商品分类模型
* @author: jh
* @date: 2017/6/21
*/
if (!defined('IN_ECM')) { die('Forbidden'); }
class GoodsMod extends BaseMod{
    public $redis_name;

    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct("goods");
        //定义缓存键名
        $this->redis_name   = $this->modName . '_relation';
    }

    /**
     * 热销商品数据缓存
     */
    public function relationRedis()
    {
        $orderGoodsMod = &m('orderGoods');
        $sql = <<<SQL
                SELECT
                    sg.id,
                    og.rec_id,
                    og.store_id,
                    sum( og.goods_num ) AS total_amount 
                FROM
                    bs_store_goods sg
                    LEFT JOIN bs_order_goods og ON sg.id = og.goods_id 
                    AND og.order_state IN ( 20, 30, 40, 50 ) 
                WHERE
                    sg.is_on_sale = 1 
                    AND sg.mark = 1 
                GROUP BY
                    sg.store_id,
                    sg.id 
                ORDER BY
                    og.store_id DESC,
                    total_amount DESC
SQL;
        $orderGoods = $orderGoodsMod->querySql($sql);

        //缓存数据
        if( $this->redis->get($this->redis_name) ){
            $this->redis->drop($this->redis_name);
        }
        $this->redis->set($this->redis_name, $orderGoods);
    }

    /**
     * 获取缓存数据
     * @return mixed
     */
    public function getHotGoods()
    {
        return $this->redis->get($this->redis_name);
    }

    /**
     * 二维数组根据字段进行排序
     * @params array $array 需要排序的数组
     * @params string $field 排序的字段
     * @params string $sort 排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
     */
    function arraySequence($array, $field, $sort = 'SORT_DESC')
    {
        $arrSort = array();
        foreach ($array as $uniqid => $row) {
            foreach ($row as $key => $value) {
                $arrSort[$key][$uniqid] = $value;
            }
        }
        array_multisort($arrSort[$field], constant($sort), $array);
        return $array;
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
        $cond = $data['key'] ? "{$data['key']}={$id}" : "goods_id={$id}";
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
     * 获取对应的语言翻译
     */
    public function getGoodsName($id,$lang_id){
        $info = $this->getOne(array("cond"=>"goods_id=".$id));
        $langMod = &m('goodsLang');
        $lang_info = $langMod->getOne(array("cond"=>"goods_id=".$id." and lang_id=".$lang_id));
        if($lang_info){
            $info['goods_name']=$lang_info['goods_name'];
        }else{
            $lang_info = $langMod->getOne(array("cond"=>"goods_id=".$id));
            $info['goods_name']=$lang_info['goods_name'];
        }
        return $info;
    }

    public function getGoodsInfo($id,$lang_id){
        $info =  $this->getOne(array("cond"=>"id=".$id));
        $goods_info = $this->getGoodsName($info['goods_id'],$lang_id);
        return $goods_info;
    }


}
?>