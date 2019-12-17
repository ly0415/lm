<?php

/**
 * 店铺分类模块模型
 * @author: jh
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class storeCateMod extends BaseMod {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct("store_cate");

        //定义缓存键名
        $this->redis_name   = $this->modName . '_relation';
    }

    /**
     * 区域国家关联数据缓存-下拉区域国家
     * @author: luffy
     * @date  : 2019-01-24
     */
    public function relationRedis(){
        //查询分类树形图
        $sql = "select a.`id`,b.cate_name from ".DB_PREFIX."store_cate as a left join "
            .DB_PREFIX."store_cate_lang as b  ON a.id = b.cate_id where a.is_open = 1 and b.lang_id = {$this->lang_id}";
        $res = $this->querySql($sql);

        //缓存数据
        if( $this->redis->get($this->redis_name) ){
            $this->redis->drop($this->redis_name);
        }
        $this->redis->set($this->redis_name, $res);
    }

    /**
     * 获取区域国家关联数据-下拉区域国家
     * @author: luffy
     * @date  : 2019-01-24
     */
    public function getRelationDatas(){
        return  $this->redis->get($this->redis_name);
    }

    /**
     * 获取区域数组
     * @author: luffy
     * @date  : 2017-11-06
     */
    public function getAreaArr($type = 0, $lang_id = null) {
        $langMod = &m('storeCateLang');
//        $query = array(
//            'cond' => "is_open = 1",
//            'fields' => 'id, cate_name'
//        );
//        $areaArr = $this->getData($query);
        $sql = 'SELECT  c.id,l.cate_name  FROM  ' . DB_PREFIX . 'store_cate  as c
                 left join  ' . DB_PREFIX . 'store_cate_lang as l on  c.id = l.cate_id
                 WHERE c.is_open = 1  and  l.lang_id =29';
        $areaArr = $langMod->querySql($sql);
        if ($type) {
            $result = array();
            foreach ($areaArr as $k => $v) {
                if ($lang_id) {
                    $info = $langMod->getOne(array("cond" => "cate_id=" . $v['id'] . " and lang_id=" . $lang_id));
                    $result[$v['id']] = $info['cate_name'];
                } else {
                    $result[$v['id']] = $v['cate_name'];
                }
            }
            $areaArr = $result;
        }
        return $areaArr;
    }

    public function getCateList($lang_id) {
        $langMod = &m('storeCateLang');
        $list = $this->getData(array("cond" => "is_open=1"));

        foreach ($list as $k => $v) {
            $info = $langMod->getOne(array("cond" => "cate_id=" . $v['id'] . " and lang_id=" . $lang_id));
            if ($info['cate_name'] ) {
                $list[$k]['cate_name'] = $info['cate_name'];
            }
        }
        foreach($list as $k =>$v){
            if(empty($v['cate_name'])){
                unset($list[$k]);
            }
        }
    
       return $list;
    }

}

?>