<?php
/**
* 充值送积分模型
* @author:tangp
* @date:2018-08-31
*/
if (!defined('IN_ECM')) { die('Forbidden'); }
class RechargeMod  extends  BaseMod{

    public function __construct() {
        parent::__construct("recharge_point");
    }

    /**
     * 获得积分
     * @param string $paymoney 充值金额
     * @return int $point 积分
     * @author tangp
     * @date 2018-10-10
     */
    public function getPoint($paymoney)
    {
        $sql = "select * from " . DB_PREFIX . " recharge_point";
        $res = $this->querySql($sql);
        foreach ($res as $key => $value) {
            $arr1[] = $value['start_charge'];
            $arr2[] = $value['end_charge'];
        }
        $min = min($arr1);
        $max = max($arr2);
        if($paymoney < $min){
            $point = 0;
        }
        if ($paymoney > $max) {
            $point = 0;
        }
        foreach($res as $key => $value){
            if($paymoney>=$value['start_charge'] && $paymoney <= $value['end_charge']){
                $point = $value['recharge'];
            }
        }
        return $point;
    }
}


 ?>
