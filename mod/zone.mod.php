<?php
/**
* 广告模型
* @author: jh
* @date: 2017/6/21
*/
if (!defined('IN_ECM')) { die('Forbidden'); }
class ZoneMod extends BaseMod{
    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct("zone");
    }

    /**
     * 获取国家的名称
     * @author wanyan
     * @date 2017-1-17
     */
    public function getZoneName($id){
        $rs = $this->getOne(array('cond'=>"`zone_id` ='{$id}'",'fields'=>"`name`"));
        return $rs['name'];
    }


}
?>