<?php
/**
 * 城市模块模型
 * @author: jh
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class CityMod extends BaseMod {
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct("city");
    }
    /**
     * 获取父节点
     * @author: wanyan
     * @date: 2017/08/31
     */
    public function getParentNodes(){
        $rs = $this->getData(array('cond'=>"`parent_id` ='1'",'fields'=>"`id`,`name`"));
        return $rs;
    }

    /**
     * 获取省市区的名称
     * @author wanyan
     * @date 2017-1-17
     */
    public function getAreaName($id){
        $rs = $this->getOne(array('cond'=>"`id` ='{$id}'",'fields'=>"`name`"));
        return $rs['name'];
    }

}
?>