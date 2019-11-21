<?php

/**
 * 订单列表
 * @author wangshuo
 * @date 2017-10-20
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class balanceAuditApp extends BackendApp {

    private $userMod;
    private $amountLogMod;
    private $pointLogMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->userMod = &m('user');
        $this->amountLogMod = &m('amountLog');
        $this->pointLogMod = &m("pointLog");
        $this->rechargePoint = &m('rechargeAmount');
       /* $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言*/
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 订单展示
     * @author wangs
     * @date 2017/10/24
     */
    public function index() {
        $username = !empty($_REQUEST['username']) ? htmlspecialchars(trim($_REQUEST['username'])) : '';
        $this->assign('username', $username);
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars(trim($_REQUEST['phone'])) : '';
        $this->assign('phone', $phone);
        $source = !empty($_REQUEST['source']) ? htmlspecialchars(trim($_REQUEST['source'])) : '';
        $this->assign('source', $source);
        $where = ' g.mark =1 and g.type = 4';
        if (!empty($username)) {
            $where .= ' and  f.username like "%' . $username . '%"';
        }
        if (!empty($phone)) {
            $where .= ' and  f.phone like "%' . $phone . '%"';
        }
        if (!empty($source)) {
            $where .= ' and  g.source =' . $source;
        }
        //列表页数据
        $sql = 'select f.username,f.phone,a.account_name,g.* from '
                . DB_PREFIX . 'amount_log as g left join '
                . DB_PREFIX . 'account as a on a.id = g.check_user left join '
                . DB_PREFIX . 'user as f on f.id = g.add_user where '
                . $where . ' order by g.add_time desc';
        //余额明细
        $result = $this->userMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        $info = $result['list'];
        foreach ($info as $k => &$v) {
            if ($v['source'] == 1) {
                $v['source'] = $this->langDataBank->project->accounts;
            }
            if ($v['source'] == 2) {
                $v['source'] = $this->langDataBank->project->applets;
            }
            if ($v['source'] == 3) {
                $v['source'] = $this->langDataBank->project->pc;
            }
            if ($v['status'] == 1) {
                $v['audit_status'] = $this->langDataBank->public->pending_review;
            } elseif ($v['status'] == 2) {
                $v['audit_status'] = $this->langDataBank->public->success_review;
            } else {
                $v['audit_status'] = $this->langDataBank->public->audit_fail;
            }
            $v['add_time'] = date("Y-m-d H:i", $v['add_time']);
            $v['pay_time'] = $v['pay_time'] ? date("Y-m-d H:i", $v['pay_time']) : '---';
            $sql_point = "SELECT percent from bs_recharge_point where id =" . $v['point_rule_id'];
            $point = $this->userMod->querySql($sql_point);
            $info[$k]['percent'] = $point[0]['percent'];
            $v['check_user'] = $v['account_name'] ? $v['account_name'] : '---';
            $v['rule_name'] = $this->rechargePoint->getRule($v['point_rule_id']);
        }
        $this->assign('page_html', $result['ph']);
        $this->assign('info', $info);
        $this->display('balanceAudit/pointLog.html');
    }

    /**
     * 线下付款通过
     * @author wangs
     * @date 2017/10/24
     */
    public function editAdopt() {
        $id = $_REQUEST['data'];
        // $sql = "SELECT * from bs_amount_log where id =" . $id;
        $sql = "SELECT al.*,rp.integral as point,rp.s_money from bs_amount_log as al left join bs_recharge_point as rp on al.point_rule_id = rp.id where al.id =" . $id;
        $rs  = $this->amountLogMod->querySql($sql);
        if($rs[0]['status'] ==2){
            $this->setData(array(), $status = 0, '当前审核已被其他管理员处理');
        }else if($rs[0]['status'] ==3){
            $this->setData(array(), $status = 0, '当前审核已被其他管理员处理');
        }
        //修改记录状态
         $data =array(
            'pay_time' => time(),
            'check_user' => $this->accountId,
            'status' => 2,
          );
        $res =$this->amountLogMod->doEdit($rs[0]['id'], $data);
        if ($res) {
             $amount=$rs[0]['c_money']+$rs[0]['s_money'];
             //更新用户信息
              $result=$this->updateAmount($amount,$rs[0]['point_rule_id'],$rs[0]['add_user'],$rs[0]['point']);
              //生成积分日志
                $sql='SELECT point,username,phone FROM '.DB_PREFIX.'user WHERE is_kefu = 0 AND id = '.$rs[0]['add_user'];
                $info = $this->userMod->querySql($sql);
                $note='充值赠送'.$rs[0]['point'].'睿积分';
                $expend='-';
                $this->addPointLog($info[0]['phone'],$note,$rs[0]['add_user'],$rs[0]['point'],$expend);
                 //赠送劵
                 $userCouponMod=&m('userCoupon');
                 $userCouponMod->addCouponByRecharge($rs[0]['add_user'],$rs[0]['point_rule_id']);
                 if($result){
                      $this->setData(array(), $status = 1, $this->langDataBank->public->cz_success);//cz_success
                    } else {
                      $this->setData(array(), $status = 0, $this->langDataBank->public->cz_error);//cz_error
                    }
            
        }
    }
//更新用户的余额和睿积分抵扣规则
    public function  updateAmount($amount,$rechargeId,$userId,$point){
        $userData=$this->userMod->getOne(array('cond'=>"`id` = '{$userId} and mark=1'",'fields'=>'amount,point'));
        $data=array(
            'recharge_id'=>$rechargeId,
            'amount'=>$userData['amount']+$amount,
            'point'=>$userData['point']+$point
        );
        $res=$this->userMod->doEdit($userId,$data);
        return $res;
    }
      //生成睿积分充值日志
    public  function addPointLog($username,$note,$userid,$deposit,$expend = "-"){
        if(empty($this->accountName)){
            $accountName='--';
        }
        $logData = array(
            'operator' => $accountName,
            'username' => $username,
            'add_time' => time(),
            'deposit'=>$deposit,
            'expend'=>$expend,
            'note'=>$note,
            'userid'=>$userid
        );
        $this->pointLogMod->doInsert($logData);
    }
    /**
     * 线下付款不通过
     * @author wangs
     * @date 2017/10/24
     */
    public function editPass() {
        $id = $_REQUEST['data'];
        $sql = "SELECT id,status from bs_amount_log where id =" . $id;
        $rs  = $this->amountLogMod->querySql($sql);
         if($rs[0]['status'] ==3){
            $this->setData(array(), $status = 0, '当前审核已被其他管理员处理');
        }else if($rs[0]['status'] ==2){
            $this->setData(array(), $status = 0, '当前审核已被其他管理员处理');
        }
        $set = array(
            "status" => 3,
            'pay_time' => time(),
            'check_user' => $this->accountId,
        );
        $data = array(
            "table" => "amount_log",
            'cond' => 'id = ' . $id,
            'set' => $set,
        );
        $res = $this->userMod->doUpdate($data);
        if ($res) {
            $this->setData(array(), $status = 1, $this->langDataBank->public->cz_success);//cz_success
        } else {
            $this->setData(array(), $status = 0, $this->langDataBank->public->cz_error);//cz_error
        }
    }

}
