<?php
/**
* 商品分类模型
* @author: luffy
* @date  : 2018-12-10
*/
if (!defined('IN_ECM')) { die('Forbidden'); }
class goodsCategoryMod extends BaseMod{

    public $relation_name = '';

    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct("goods_category");

        //定义缓存键名
        $this->redis_name   = $this->modName . '_relation';
    }

    /**
     * 分类关联数据缓存-分类树
     * @author: luffy
     * @date  : 2018-12-11
     */
    public function relationRedis(){
        //查询分类树形图
        $sql = 'SELECT c.id,c.`parent_id`,c.image,c.adv_img,c.sort_order,c.add_time,l.category_name FROM '
            . DB_PREFIX . 'goods_category AS c LEFT JOIN '
            . DB_PREFIX . 'goods_category_lang AS l ON c.id = l.category_id WHERE l.lang_id = '.$this->lang_id.' ORDER BY c.sort_order ASC';
        $res = $this->querySql($sql);

        //递归成树
        $datas =  getTree($res);

        //缓存数据
        if( $this->redis->get($this->redis_name) ){
            $this->redis->drop($this->redis_name);
        }
        $this->redis->set($this->redis_name, $datas);
    }

    /**
     * 获取分类关联数据-分类树
     * @author: luffy
     * @date  : 2018-12-11
     */
    public function getRelationDatas(){
       return  $this->redis->get($this->redis_name);
    }

    /**
     * 获取分类关联数据-分类整条数据
     * @author: luffy
     * @date  : 2018-12-13
     */
    public function relationRedisRow($id){
        $sql = 'SELECT c.id,c.`parent_id`,c.image,c.adv_img,c.sort_order,c.add_time,l.category_name FROM '
            . DB_PREFIX . 'goods_category AS c LEFT JOIN '
            . DB_PREFIX . 'goods_category_lang AS l ON c.id = l.category_id WHERE l.lang_id = '.$this->lang_id.' AND c.id = '.$id;
        $res = $this->querySql($sql);
        return  $res;
    }

    /**
     * 获取分类关联数据- 一级分类
     * @author: luffy
     * @date  : 2018-12-17
     */
    public function getOneCategory(){
        //分类树
        $relationDatas = $this->getRelationDatas();

        $result = array();
        //获取一级分类
        foreach($relationDatas as $key => $value){
            unset($value['childs']);
            $result[$key] = $value;
        }
        return  $result;
    }
}
?>