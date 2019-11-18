<?php
/**
 * 管理员模块模型
 * @author: jh
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class CurrencyMod extends BaseMod{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct("currency");
    }
    /**
     * 获取币种方法
     * @author: wanyan
     * @date: 2017/10/09
     */
    public function getCurrency(){
        $query=array(
            'cond' => " `mark` =1",
            'fields' =>" `id`,`name`"
        );
        $rs = $this->getData($query);
        return $rs;
    }
    /**
     * 获取币种汇率
     * @author: wanyan
     * @date: 2017/10/09
     */
    public function getCurrencyRate($id){
        $rs = $this->getOne(array('cond'=>"`mark`=1 and `id` = '{$id}'",'fields'=>"rate"));
        return $rs['rate'];
    }
    /**
     * 获取币种汇率
     * @author: wanyan
     * @date: 2017/10/09
     */
    public function getCurrencyName($id){
        $rs = $this->getOne(array('cond'=>"`mark`=1 and `id` = '{$id}'",'fields'=>"symbol,short"));
        return $rs;
    }
    /**
     * 根据店铺ID获取币种汇率
     * @author: wanyan
     * @date: 2017/10/09
     */
    public function getCurrencyRateById($store_id){
        $sql = "select c.rate FROM bs_store as s LEFT JOIN bs_currency as c ON s.currency_id = c.id WHERE s.id={$store_id} ";
        $rs = $this->querySql($sql);
        return $rs[0]['rate'];
    }
    /**
     * 根据店铺ID获取币信息
     * @author: wanyan
     * @date: 2017/10/09
     */
    public function getCurrencyById($store_id){
        $sql = "select c.id,c.name,c.short,c.symbol FROM bs_store as s LEFT JOIN bs_currency as c ON s.currency_id = c.id WHERE s.id={$store_id} ";
        $rs = $this->querySql($sql);
        return $rs[0];
    }

}
?>