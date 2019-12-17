<?php
/**
 * 管理员模块模型
 * @author: jh
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class FxuserMod extends BaseMod {
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct("fx_user");
    }
    /**
     * 分销分级
     */
    public $level = array(
        '1' => '一级分销',
        '2' => '二级分销',
        '3' => '三级分销',
    );

    /**
     * 账号来源
     *
     * @var array
     */
    public $source = array(
        '1' => '后台超管',
        '2' => '微信端',
        '3' => '前台',
        '4' => '小程序',
    );

    /**
     * 根据等级获取分销人员列表
     * @author zhangkx
     * @date 2018-10-16
     *
     * @param $level 等级
     * @param $parentId 父级id
     * @return array
     */
    public function getUserListByLevel($level, $parentId)
    {
        $sql = "select * from bs_fx_user where level = {$level} and parent_id = {$parentId} and mark = 1";
        $res = $this->querySql($sql);
        return $res;
    }

    /**
     * 判断指定字段是否存在
     * @author zhangkx
     * @date 2018-10-16
     *
     * @param $fields
     * @param $data
     * @param $id
     * @return array
     */
    public function isExist($fields, $data, $id = 0) {
        $where = " where (is_check = 1 or is_check = 2) and {$fields} = '{$data}'";
        if (!empty($id)) {
            $where .= '   and  id !=' . $id;
        }
        $sql = 'select  id  from bs_fx_user ' . $where;
        $res = $this->querySql($sql);
        return $res;
    }

    /**
     * 校验银行卡号格式
     *
     * @param $bankAccount
     * @return bool
     * 验证银行帐号
     */
    public function checkBankAccount($bankAccount) {
        //$no = '7432810010473523';
        $no = $bankAccount;
        $arr_no = str_split($no);
        $last_n = $arr_no[count($arr_no) - 1];
        krsort($arr_no);
        $i = 1;
        $total = 0;
        foreach ($arr_no as $n) {
            if ($i % 2 == 0) {
                $ix = $n * 2;
                if ($ix >= 10) {
                    $nx = 1 + ($ix % 10);
                    $total += $nx;
                } else {
                    $total += $ix;
                }
            } else {
                $total += $n;
            }
            $i++;
        }
        $total -= $last_n;
        $x = 10 - ($total % 10);
        if ($x == $last_n) {
            // echo '符合Luhn算法';
            return true;
        } else {
            return false;
        }
    }
    /**
     * 根据等级获取会员数量
     * @author Run
     * @date 2018-10-19
     * @param $level 等级
     * @param $fx_user_id 会员ID
     * @return array
     */
    public function countUser($fx_user_id,$level){
        if($level == 1){
            $sql        = "SELECT id from bs_fx_user where parent_id=".$fx_user_id;
            $twoList    = $this->querySql($sql);
            $two_count  = count($twoList);
            foreach ($twoList as $v){
                $ids[] = $v['id'];
            }
            $two_ids    = implode(',',$ids);
            $three_sql  = "SELECT COUNT(id) as rnct from bs_fx_user where parent_id in {$two_ids}";
            $threeList  = $this->querySql($three_sql);
            $count      =  $two_count+$threeList[0]['rnct']+1;
        }else if($level == 2){
            $three_sql = "SELECT COUNT(id) as rnct from bs_fx_user where parent_id =".$fx_user_id;
            $threeList = $this->querySql($three_sql);
            $count     = $threeList[0]['rnct']+1;
        }else if($level == 3){
            // 三级分销下的会员数量
            $fxUserAccountMod = &m('fxUserAccount');
            $count = $fxUserAccountMod->checkUserAccount($fx_user_id,1);
        }
        return $count;
    }


    /**
     * 收货计算分销金额
     * @author gao
     * @date 2018-10-20
     */
    public function getAccount($ordersn){
        $orderMod=&m('order');
        $fxOrderMod = &m('fxOrder');
        $fxOrderSql="SELECT order_id,fx_user_id,rule_id,pay_money,fx_discount FROM ".DB_PREFIX."fx_order WHERE  order_sn = '{$ordersn}' ";
        $fxOrderData=$this->querySql($fxOrderSql);
        $this->getFxMoney($fxOrderData);
    }
    //计算各级分销人员的佣金以及入账
    public function  getFxMoney($fxOrderData){
        $orderMod=&m('order');
        $fxOrderMod = &m('fxOrder');
        //分润规则
        foreach($fxOrderData as $k=>$v){
            $fxRuleSql="SELECT * from bs_fx_rule where id=".$v['rule_id'];
            $fxRuleData=$this->querySql($fxRuleSql);
            $lev1_money=number_format($fxRuleData[0]['lev1_prop']*$v['pay_money']/100,2,".","");
            $lev2_money=number_format($fxRuleData[0]['lev2_prop']*$v['pay_money']/100,2,".","");
            $firsrFxUserData=$this->getParentId($v['fx_user_id']);
            $lev3_money=number_format((($fxRuleData[0]['lev3_prop']-$v['fx_discount'])*$v['pay_money']/100),2,".","");
            $secondFxUserData=$this->getParentId($firsrFxUserData['parent_id']);
            $thirdFxUserData=$this->getParentId($secondFxUserData['parent_id']);
            $this->updateMonery($firsrFxUserData['monery'],$lev3_money,$v['fx_user_id']);
            $this->updateMonery($secondFxUserData['monery'],$lev2_money,$firsrFxUserData['parent_id']);
            $this->updateMonery($thirdFxUserData['monery'],$lev1_money,$secondFxUserData['parent_id']);
            // $sql = "update ".DB_PREFIX."order set fx_status = '1' where order_id = '{$v['order_id']}'";
            // $orderMod->doEditSql($sql);
            // by xt 2019.01.22
            $sql = "update bs_fx_order set is_on = 1 where order_id = {$v['order_id']}";
            $fxOrderMod->doEditSql($sql);
        }
    }


    //获取分销人员的上级人员
    public function  getParentId($id){
        $sql="SELECT monery,parent_id,discount from bs_fx_user where id=".$id;
        $fxUserData=$this->querySql($sql);
        return $fxUserData[0];
    }
    //更新分销人员的金额
    public function updateMonery($newMonery,$oldMonery,$id){
        $monery=$newMonery+$oldMonery;
        $sql="update bs_fx_user set monery=".$monery." where id=".$id;
        $this->doEditSql($sql);
    }



    /**
     * 根据分销人id获取上级人的id
     * @author zhangkx
     * @date 2018-10-20
     *
     * @param $userId 分销人id
     * @return array
     */
    public function getLevUser($userId) {
        $userMod = &m('fxuser');
        $user = $userMod->getRow($userId);
        $level = $user['level'];
        $where = '1 = 1';
        if ($level == 1) {
            $where = 'c.id = '.$userId;
        }
        if ($level == 2) {
            $sql = "select a.id as level_3, b.id as level_2, c.id as level_1 from bs_fx_user as a 
              left join bs_fx_user as b on a.parent_id = b.id 
              left join bs_fx_user as c on b.parent_id = c.id where b.id = {$userId}";
            $where = 'b.id = '.$userId;
        }
        if ($level == 3) {
            $sql = "select a.id as level_3, b.id as level_2, c.id as level_1 from bs_fx_user as a 
              left join bs_fx_user as b on a.parent_id = b.id 
              left join bs_fx_user as c on b.parent_id = c.id where a.id = {$userId}";
        }
        //获取上级分销人id
        $sql = "select a.id as level_3, b.id as level_2, c.id as level_1 from bs_fx_user as a 
          left join bs_fx_user as b on a.parent_id = b.id 
          left join bs_fx_user as c on b.parent_id = c.id where ".$where;
        $data = $this->querySql($sql);
        if ($level == 1) {
            $returnData = array(
                'level_1' => $data[0]['level_1'],
                'level_2' => 0,
                'level_3' => 0,
            );
        }
        if ($level == 2) {
            $returnData = array(
                'level_1' => $data[0]['level_1'],
                'level_2' => $data[0]['level_2'],
                'level_3' => 0,
            );
        }
        if ($level == 3) {
            $returnData = array(
                'level_1' => $data[0]['level_1'],
                'level_2' => $data[0]['level_2'],
                'level_3' =>$data[0]['level_3'],
            );
        }
        echo '<pre>';print_r($sql);die;
        return $data;
    }

    /**
     * 获取分销数据对应的一级分销信息,最多递归10次
     * $count:当前递归次数
     */
    public function getLevel1Info($childid, $count = 1)
    {
        $sql = 'select * from  ' . DB_PREFIX . 'fx_user where id = ' . $childid;
        $res = $this->querySql($sql);
        $data = $res[0];
        if ($data['level'] == 1) {
            return $data;
        } elseif (($count < 10) && ($data['parent_id'] > 0)) {
            return $this->getLevel1Info($data['parent_id'], $count + 1);
        } else {
            return array();
        }
    }

    /**
     * 根据一级分销人更新二级三级分销人的分销规则
     *
     * @author zhangkx
     * @date 2018/11/21
     * @param $level1 一级分销人id
     * @param $ruleId 规则id
     * @return bool
     */
    public function updateRule($level1, $ruleId)
    {
        $level2 = $this->getData(array('cond' => 'parent_id = ' . $level1, 'fields' => 'id'));
        if ($level2) {
            //更新二级分销人的rule_id
            $level2IdsList = array_map('array_shift', $level2);
            $level2Ids = implode($level2IdsList, ',');
            $level2Sql = 'update '.DB_PREFIX.'fx_user set rule_id = ' . $ruleId . ' where id in (' . $level2Ids . ')';
            $this->doEditSql($level2Sql);
            //更新三级分销人的rule_id
            $level3 = $this->getData(array('cond' => 'parent_id IN ( ' . $level2Ids . ')', 'fields' => 'id'));
            if ($level3) {
                $level3IdsList = array_map('array_shift', $level3);
                $level3Ids = implode($level3IdsList, ',');
                $level3Sql = 'update '.DB_PREFIX.'fx_user set rule_id = ' . $ruleId . ' where id in (' . $level3Ids . ')';
                $this->doEditSql($level3Sql);
            }
        }
        return true;
    }
}