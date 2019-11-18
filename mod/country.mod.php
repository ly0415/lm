<?php
/**
*  国家地区模型
* @author: jh
* @date: 2017/6/21
*/
if (!defined('IN_ECM')) { die('Forbidden'); }
class CountryMod extends BaseMod{
    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct("country");
    }
    /**
     *  国家地区模型
     * @author: wanyan
     * @date: 2017/08/31
     */
    public function getCountryNodes(){
        $rs = $this->getData(array('cond'=>"`status` ='1'",'fields'=>"`country_id`,`name`"));
        return $rs;
    }
    /**
     * 获取国家的名称
     * @author wanyan
     * @date 2017-1-17
     */
    public function getCountryName($id){
        $rs = $this->getOne(array('cond'=>"`country_id` ='{$id}'",'fields'=>"`name`"));
        return $rs['name'];
    }





}
?>