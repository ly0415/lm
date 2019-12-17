<?php
/**
 * 管理员模块模型
 * @author: jh
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class FxruleMod extends BaseMod {
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct("fx_rule");
    }

    /**
     * 根据一级分销人id获取三级分销比例
     * @author zhangkx
     * @date 2018-10-16
     *
     * @param $userId 一级分销人id
     * @return int
     */
    public function getLev3Prop($userId) {
        $sql = "select b.lev3_prop  from bs_fx_user as a left join bs_fx_rule as b on a.rule_id = b.id where a.id = {$userId}";
        $data = $this->querySql($sql);
        return $data[0]['lev3_prop'];
    }

    /**
     * 获取分润规则
     * @author luffy
     * @date 2018-10-16
     */
    public function getFxRule($userId) {
        $fxuserMod  = &m('fxuser');
        $fxInfo     = $fxuserMod  ->getRow($userId);

        if( $fxInfo['level'] != 1 ){
            return $this->getFxRule($fxInfo['parent_id']);
        }
        return $fxInfo['rule_id'];
    }

    /**
     * 根据三级分销人id获取三级分销比例
     * @author zhangkx
     * @date 2018-10-17
     *
     * @param $userId 三级分销人id
     * @return int
     */
    public function getLev3Percent($userId) {
        //获取一级分销人id
        $sql = "select c.id from bs_fx_user as a 
          left join bs_fx_user as b on a.parent_id = b.id 
          left join bs_fx_user as c on b.parent_id = c.id where a.id = {$userId}";
        $data = $this->querySql($sql);
        //获取三级分销比例
        $propSql = "select b.lev3_prop  from bs_fx_user as a left join bs_fx_rule as b on a.rule_id = b.id where a.id = {$data[0]['id']}";
        $propData = $this->querySql($propSql);
        return $propData[0]['lev3_prop'];
    }

    /**
     * 判断是否为小数
     * @author zhangkx
     * @date 2018/10/17
     * @param $lev_prop
     * @return bool|string
     */
    public function isDecimal($lev_prop) {
        $a = explode('.', $lev_prop);
        $b = "0." . $a[1];
        if ($b == '0.00') {
            return $a[0];
        } else {
            $c = strrev($lev_prop);
            if ($c[0] == "0") {
                $c = substr($c, 1);
                $c = strrev($c);
                return $c;
            }
            return $lev_prop;
        }
    }

    /**
     * 根据分销人id获取分销比例
     * @author zhangkx
     * @date 2018-10-20
     *
     * @param $userId 分销人id
     * @param $level 分销人等级
     * @param $rank 第N级优惠比例
     * @return int
     */
    public function getLevPercent($userId, $level, $rank) {
        $where = '1 = 1';
        if ($level == 1) {
            $where = 'c.id = '.$userId;
        }
        if ($level == 2) {
            $where = 'b.id = '.$userId;
        }
        if ($level == 3) {
            $where = 'a.id = '.$userId;
        }
        if ($rank == 1) {
            $fields = 'b.lev1_prop';
            $returnData = 'lev1_prop';
        } elseif ($rank == 2) {
            $fields = 'b.lev2_prop';
            $returnData = 'lev2_prop';
        } elseif ($rank == 3) {
            $fields = 'b.lev3_prop';
            $returnData = 'lev3_prop';
        } else {
            $fields = 'b.lev1_prop, b.lev2_prop, b.lev3_prop';
            $returnData = 1;
        }
        //获取一级分销人id
        $sql = "select c.id from bs_fx_user as a 
          left join bs_fx_user as b on a.parent_id = b.id 
          left join bs_fx_user as c on b.parent_id = c.id where ".$where;
        $data = $this->querySql($sql);
        //获取分销比例
        $propSql = "select {$fields} from bs_fx_user as a left join bs_fx_rule as b on a.rule_id = b.id where a.id = {$data[0]['id']}";
        $propData = $this->querySql($propSql);
        if ($returnData == 1) {
            return $propData[0];
        } else {
            return $propData[0][$returnData];
        }
    }
}