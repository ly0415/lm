<?php
/**
 * 余额充值规则
 * @author: gao
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class rechargeAmountMod extends BaseMod{
    private $langDataBank;
    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct("recharge_point");
        //加载语言包
        $this->langDataBank = languageFun($this->shorthand);
    }

    /**
     * 获取充值规则
     * @author zhangkx
     * @date 2019/4/2
     * @param $id
     * @return string
     */
    public function getRule($id)
    {
        $data = $this->getRow($id);
        $name = $this->langDataBank->project->recharge.$data['c_money'].$this->langDataBank->project->send. $data['s_money'].$this->langDataBank->project->send.$data['integral'].$this->langDataBank->project->point_ratio.$data['percent'].'%';
        return $name;
    }


}
?>