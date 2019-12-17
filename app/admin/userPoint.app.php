<?php
/**
 * 积分管理模块
 * @author wanyan
 * @date 2018-1-2
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class UserPointApp extends BackendApp{

    private $pointMod;
    private $userMod;
    private $storePointMod;
    private $pointLogMod;
    private $rechargeMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();

        $this->pointMod = &m('point');
        $this->storePointMod = &m('storePoint');
        $this->userMod = &m('user');
        $this->pointLogMod = &m('pointLog');
        $this->rechargeMod = &m('recharge');
    }
    //设置页面
    public function index(){
        $sql="SELECT * FROM ".DB_PREFIX.'user_point_site';
        $data = $this->pointMod->querySql($sql);
        $sql1 = "SELECT * FROM ".DB_PREFIX."recharge_point where mark =1 order by c_money ";
        $data1 = $this->rechargeMod->querySql($sql1);
//        var_dump($data1);die;
        $this->assign('res', $data[0]);
        $this->assign('ress',$data1);
        $this->display('userPoint/site.html');
        }


    /*
     * 保存区域规则配置
     * @author lee
     * @date 2018-6-20 16:18:42
     */
    public function saveStorePoint(){
        $store_id = !empty($_REQUEST['store_id']) ? intval(trim($_REQUEST['store_id'])) : 0;
        $order_point = !empty($_REQUEST['order_point']) ? intval(trim($_REQUEST['order_point'])) : 0;
        $point_price = !empty($_REQUEST['point_price']) ? intval(trim($_REQUEST['point_price'])) : 0;
        $point_id = !empty($_REQUEST['point_id']) ? intval(trim($_REQUEST['point_id'])) : 0;

        if(empty($order_point)){
                $this->setData(array(),'0',$this->langDataBank->project->order_point);
        }
        if(empty($point_price)){
                $this->setData(array(),'0',$this->langDataBank->project->deduction_required);
        }
        if (!preg_match("/^[1-9][0-9]*$/",$order_point) || $order_point>100) {
                $this->setData(array(),'0',$this->langDataBank->project->get_deduction);
        }
        if (!preg_match("/^[1-9][0-9]*$/",$point_price) || $order_point>100) {
                $this->setData(array(),'0',$this->langDataBank->project->get_rate);
        }
        $data=array(
            'store_id'=>$store_id,
            'order_point'=>$order_point,
            'add_time'=>time(),
            'point_price'=>$point_price,
        );

        if (empty($point_id)){
            $res = $this->storePointMod->doInsert($data);
        } else {
            $res = $this->storePointMod->doEdit($point_id, $data);
        }
        if($res){
            $this->setData(array("url"=>"?app=areaStore&act=index"), '0',$this->langDataBank->public->success_save);
        }
    }
        //保存设置
    public function pointSave(){
         $register_point=!empty($_REQUEST['register_point']) ? intval(trim($_REQUEST['register_point'])) : 0;
         $first_point=!empty($_REQUEST['first_point']) ? intval(trim($_REQUEST['first_point'])) : 0;
        $second_point=!empty($_REQUEST['second_point']) ? intval(trim($_REQUEST['second_point'])) : 0;
        $third_point=!empty($_REQUEST['third_point']) ? intval(trim($_REQUEST['third_point'])) : 0;
        $point_id=!empty($_REQUEST['point_id']) ? intval(trim($_REQUEST['point_id'])) : 0;
        $point_rate=!empty($_REQUEST['point_rate']) ? intval(trim($_REQUEST['point_rate'])) : 0;
        $register_recharge=!empty($_REQUEST['register_recharge']) ? trim($_REQUEST['register_recharge']) : 0;
//        $first_recharge=!empty($_REQUEST['first_recharge']) ? intval(trim($_REQUEST['first_recharge'])) : 0;
//        $charge = !empty($_REQUEST['charge']) ? $_REQUEST['charge'] : 0;
        $edit = !empty($_REQUEST['edit']) ? intval($_REQUEST['edit']) : 0;
        if(empty($register_point)){
                $this->setData(array(),'0',$this->langDataBank->project->register_required);
        }
        if(empty($first_point)){
                $this->setData(array(),'0',$this->langDataBank->project->recommend_required);
        }
        if(empty($second_point)){
                $this->setData(array(),'0',$this->langDataBank->project->two_required);
        }
        if(empty($third_point)){
                $this->setData(array(),'0',$this->langDataBank->project->three_required);
        }
        if(empty($point_rate)){
                $this->setData(array(),'0',$this->langDataBank->project->rmb_rate_required);
        }
        if(empty($register_recharge)){
                $this->setData(array(),'0',$this->langDataBank->project->recharge_required);
        }
//        if (empty($first_recharge)){
//            if ($this->lang_id==0){
//                $this->setData(array(),'0','首次充值送的积分不能为空');
//            }else{
//                $this->setData(array(),'0','The first recharge integral cannot be empty');
//            }
//        }
//        if (empty($charge)){
//            if ($this->lang_id==0){
//                $this->setData(array(),'0','首次充值的满的金额不能为空');
//            }else{
//                $this->setData(array(),'0','The full amount of the first recharge cannot be empty');
//            }
//        }
        if (empty($_REQUEST['start_charge'])){
            $this->setData('',0,$this->langDataBank->project->add_recharge);
        }else{
            $diff_start_charge = array_unique($_REQUEST['start_charge']);
            if (count($diff_start_charge) != count($_REQUEST['start_charge'])){
                $this->setData('',0,$this->langDataBank->project->repeat_recharge);
            }
            foreach ($_REQUEST['start_charge'] as $key => $value){
                $name = htmlspecialchars(trim($value));
                if (empty($name)){
                    $this->setData('',0,$this->langDataBank->project->amount_required);
                }
            }
            
        foreach ($_REQUEST['start_charge'] as $k=>$v){
              $temp=$v;
         }
         if(($temp)){
                 $sql_qc = "select * from bs_recharge_point  WHERE mark = 1  order by id desc";
                 $res_qc = $this->pointMod->querySql($sql_qc);
                  foreach ($res_qc as $k=>$v){
                    if($v['c_money']> $temp){
                    $this->setData('',0,$this->langDataBank->project->amount_rule); 
                 }  
               }
             }
         }
        if (empty($_REQUEST['end_charge'])){
            $this->setData('',0,$this->langDataBank->project->add_send_amount);
        }else{
            $diff_end_charge = array_unique($_REQUEST['end_charge']);
            if (count($diff_end_charge) != count($_REQUEST['end_charge'])){
                $this->setData('',0,$this->langDataBank->project->repeat_amount);
            }
            $res_qc = $this->pointMod->querySql($sql_qc);
            foreach ($_REQUEST['end_charge'] as $key => $value){
                $name = htmlspecialchars(trim($value));
                if (empty($name)){
                    $this->setData('',0,$this->langDataBank->project->send_required);
                }
            }
        }
        if (empty($_REQUEST['recharge'])){
            $this->setData('',0,$this->langDataBank->project->add_point);
        }else{
            $diff_recharge = array_unique($_REQUEST['recharge']);
            if (count($diff_recharge) != count($_REQUEST['recharge'])){
                $this->setData('',0,$this->langDataBank->project->repeat_point);
            }
            foreach ($_REQUEST['recharge'] as $key => $value){
                $name = htmlspecialchars(trim($value));
                if (empty($name)){
                    $this->setData('',0,$this->langDataBank->project->point_required);
                }
            }
        }
        $data=array(
            'register_point'=>$register_point,
            'first_point'=>$first_point,
            'second_point'=>$second_point,
            'third_point'=>$third_point,
            'point_rate'=>$point_rate,
            //'first_recharge'=>$first_recharge,
            // 'charge' => $charge,
            'register_recharge'=>$register_recharge,
            'add_time'=>time(),
        );
        if (empty($point_id)){
             $res = $this->pointMod->doInsert($data);
         } else {
             $res = $this->pointMod->doEdit($point_id, $data);
         }
        if ($edit){
            foreach ($_REQUEST['dropIds'] as $key => $value){
                      $sql_rule = "select * from bs_recharge_point  WHERE mark = 1 and id = {$value}";
                      $res_rule = $this->pointMod->querySql($sql_rule);
                    if($res_rule[0]['c_money'] == $_REQUEST['start_charge'][$key]&& $res_rule[0]['s_money'] == $_REQUEST['end_charge'][$key]&& $res_rule[0]['percent'] == $_REQUEST['percent'][$key] && $res_rule[0]['integral'] == $_REQUEST['recharge'][$key]){
                        $this->rechargeMod->doEdit($value,array(
                        'c_money' => $_REQUEST['start_charge'][$key],
                        's_money'   => $_REQUEST['end_charge'][$key],
                        'integral'     => $_REQUEST['recharge'][$key],
                        'percent'     => $_REQUEST['percent'][$key],
                        'add_user'  =>$_SESSION['account_id'],
                        'add_time'  =>time()
                    ));
                    unset($_REQUEST['start_charge'][$key]);
                    unset($_REQUEST['end_charge'][$key]);
                    unset($_REQUEST['recharge'][$key]);
                    unset($_REQUEST['percent'][$key]);
                    }else{
                     $this->rechargeMod->doMark($value);
                }
            }
            $start_charge = $_REQUEST['start_charge'];
            $end_charge = $_REQUEST['end_charge'];
            $recharge = $_REQUEST['recharge'];
            $percent = $_REQUEST['percent'];
            $arr = array();
            foreach ($start_charge as $k => $v){
                $arr[] = array($start_charge[$k],$end_charge[$k],$recharge[$k],$percent[$k]);
            }
            foreach ($arr as $v){
                $data = array(
                    'c_money' => $v[0],
                    's_money' => $v[1],
                    'integral' => $v[2],
                    'percent'  => $v[3],
                    'add_user'  =>$_SESSION['account_id'],
                    'add_time'  =>time()
                );
                $this->rechargeMod->doInsert($data);
            }
            $info['url'] = "?app=userPoint&act=index";
            $this->setData($info,1,$this->langDataBank->public->edit_success);
        }else{
            $start_charge = $_REQUEST['start_charge'];
            $end_charge   = $_REQUEST['end_charge'];
            $recharge     = $_REQUEST['recharge'];
            $percent = $_REQUEST['percent'];
            $arr = array();
            foreach ($start_charge as $k => $v){
                $arr[] = array($start_charge[$k],$end_charge[$k],$recharge[$k],$percent[$k]);
            }
            foreach ($arr as $v){
                $data = array(
                    'c_money' => $v[0],
                    's_money' => $v[1],
                    'integral' => $v[2],
                    'percent'  => $v[3],
                    'add_user'  =>$_SESSION['account_id'],
                    'add_time'  =>time()
                );
                $this->rechargeMod->doInsert($data);
            }
            $info['url'] = "?app=userPoint&act=index";
            $this->setData($info,1,$this->langDataBank->public->add_success);
        }
        if($res){
//            $info['url'] = "?app=userPoint&act=index";
            $this->setData(array(), '0',$this->langDataBank->public->success_save);
        }
     }
     //推荐会员
    public function recom(){
        $phone= !empty($_REQUEST['phone']) ? htmlspecialchars(trim($_REQUEST['phone'])) : '';
        $this->assign('phone',$phone);
        $username = !empty($_REQUEST['username']) ? htmlspecialchars(trim($_REQUEST['username'])) : '';
        $this->assign('username',$username);
        $email = !empty($_REQUEST['email']) ? htmlspecialchars(trim($_REQUEST['email'])) : '';
        $this->assign('email',$email);
        if(!empty($phone)){
            $where =" AND phone like '%".$phone."%'";
        }
        if(!empty($email)){
            $where =" AND email like '%".$email."%'";
        }
        if(!empty($username)){
            $where =" AND username like '%".$username."%'";
        }
        $cates = $this->getParent($where);
        $sql='SELECT id,point,phone_email,phone,email,add_time,username,mark FROM '.DB_PREFIX.'user WHERE is_kefu = 0 AND mark=1 '.$where;
        $res = $this->userMod->querySql($sql);
        foreach ($res as $k => $v) {
            $res[$k]['add_time'] = !empty($v['add_time']) ? date('Y-m-d H:i:s', $v['add_time']) : '';
        }
        $rs = $this->getTree($res, $pid = 0);
        $this->assign('cates', $cates);
        $this->assign('res', $rs);
        $this->display('userPoint/pointList.html');
    }
    //设置积分
    public function setPoint(){
        $userId=$_REQUEST['id'];
        $sql='SELECT point FROM '.DB_PREFIX.'user WHERE is_kefu = 0 AND id = '.$userId;
        $res = $this->userMod->querySql($sql);
        $user_point=$res[0]['point'];
        $this->assign('userId',$userId);
        $this->assign('point',$user_point);
        $this->display('userPoint/recom.html');
    }
    public function doSet(){
        $userId=$_REQUEST['userId'];
        $point=!empty($_REQUEST['point']) ? $_REQUEST['point'] : 0;
        $note=$_REQUEST['mark'];
        $data = array(
            "table" => "user",
            'cond' => 'id = ' . $userId,
            'set' => "point ='".$point."'",
        );
        $sql='SELECT point,username FROM '.DB_PREFIX.'user WHERE is_kefu = 0 AND mark=1 AND id = '.$userId;
        $res = $this->userMod->querySql($sql);
        $user_point=$res[0]['point'];
        $info=$point-$user_point;
        $username=$res[0]['username'];
        $ress = $this->userMod->doUpdate($data);
        if($ress){
            if($info>0){
                $deposit=$info;
                $expend='-';
                if(empty($note)){
                    $note='增加'.$deposit.'睿币';
                }
                $this->addPointLog($username,$note,$userId,$deposit,$expend,$this->accountName);
            }
            if ($info<0){
                $expend=$info;
                $deposit='-';
                if(empty($note)){
                    $note='减少'.$expend.'睿币';
                }
                $this->addPointLog($username,$note,$userId,$deposit,$expend,$this->accountName);
            }
            $this->setData(array(),'1',$this->langDataBank->public->success_save);
            }else {
            $this->setData(array(),'0',$this->langDataBank->public->fail);
        }

    }

    public function getTree($list, $pid = 0) {
        $tree = array();
        foreach ($list as $v) {
            if ($v['phone_email'] == $pid) {
                if ($this->getTree($list, $v['phone'])) {
                    $v['child'] = $this->getTree($list, $v['phone']);
                }else{
                    $v['child']=$this->getTree($list,$v['email']);
                }
                $tree[] = $v;
            }
        }
        return $tree;
    }
    public function getParent($where) {
        $sql='SELECT id,point,phone_email,phone,email,add_time,username,mark FROM '.DB_PREFIX.'user WHERE mark=1 AND phone_email =0 AND is_kefu = 0'.$where;
        $res =$this->userMod->querySql($sql);
        return $res;
    }


    //生成日志
    public  function addPointLog($username,$note,$userid,$deposit,$expend,$accountName){
        $logData = array(
            'operator' => $accountName,
            'username' => $username,
            'add_time' => time(),
            'deposit'=>$deposit,
            'expend'=>$expend,
            'note'=>$note,
            'userid'=>$userid
        );
        $pointLogMod = &m("pointLog");
        $pointLogMod->doInsert($logData);
    }


        //个人睿积分查看
        public function  seeIntegral(){
            $userId=!empty($_REQUEST['userId']) ? $_REQUEST['userId'] : '';
            $startTime = !empty($_REQUEST['start_time']) ? strtotime($_REQUEST['start_time']) : '';
            $endTime = !empty($_REQUEST['end_time']) ? strtotime($_REQUEST['end_time']) : '';


            if (empty($startTime) && empty($endTime)) {
                $startTime = strtotime(date('Y-m-d', strtotime('-7 days')));
                $endTime = strtotime(date('Y-m-d'));
            }
            if (!empty($startTime) && !empty($endTime) && ($startTime > $endTime)) {
                $t = $endTime;
                $endTime = $startTime;
                $startTime = $t;
            }
            if (!empty($endTime)) {
                $endTime = $endTime + 24 * 3600 - 1;
            }
            $this->assign('stime', date('Y/m/d', $startTime));
            $this->assign('etime', date('Y/m/d', $endTime));

            $where = '   where  1=1 ';
            //站点选择

            // 筛选条件
            if (!empty($startTime)) {
                $where .= '  and  add_time >= ' . $startTime;
            }
            if (!empty($endTime)) {
                $where .= '  and  add_time <= ' . $endTime;
            }
            $sql = ' select  *   from  ' . DB_PREFIX . 'point_log '.$where.'  and    userid=' . $userId . '  order by id desc ';
/*            var_dump($sql);exit;*/
            $data = $this->pointLogMod->querySqlPageData($sql);
            $list = $data['list'];
            $this->assign('list', $list);
            $this->assign('userId',$userId);
            $this->display('userPoint/seeIntegral.html');


        }

        //导出个人睿积分

    /*
* 交班报表
* @author wangs
* @date 2018-7-19
*/

    public function exportOrder() {
        $userId=!empty($_REQUEST['userId']) ?  $_REQUEST['userId'] : '';
        $startTime = !empty($_REQUEST['start_time']) ? strtotime($_REQUEST['start_time']) : '';
        $endTime = !empty($_REQUEST['end_time']) ? strtotime($_REQUEST['end_time']) : '';
        $this->load($this->lang_id, 'store/store');

        $where = '';
        if (!empty($startTime)) {
            $where .= '  and  add_time >= ' . $startTime;
        }
        if (!empty($endTime)) {
            $where .= '  and  add_time <= ' . $endTime;
        }
        $userMod = &m('user');
        $sql = ' select  *   from  ' . DB_PREFIX . 'point_log   where   userid=' . $userId .$where. '  order by id desc ';
        $userList = $this->pointLogMod->querySql($sql);

        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=个人睿积分信息.xls");
        echo iconv('utf-8', 'gb2312', "日期") . "\t";
        echo iconv('utf-8', 'gb2312', "存入") . "\t";
        echo iconv('utf-8', 'gb2312', "支出") . "\t";
        echo iconv('utf-8', 'gb2312', "信息摘要") . "\t";
        echo "\n";

        foreach ($userList as $k => $v) {
            echo iconv('utf-8', 'gb2312', date('Y-m-d', $v['add_time'])) . "\t"; //日期
            echo iconv('utf-8', 'gb2312', $v['deposit']) . "\t";              //买家姓名
            echo iconv('utf-8', 'gb2312', $v['expend']) . "\t";                   //买家手机
            echo iconv('utf-8', 'gb2312',$v['note']) . "\t";                  //订单来源

            echo "\n";
        }

    }

    //推荐人员
    public function  userRecom(){
        $userMod=&m('user');
        $couponMod=&m('coupon');
        $userId= !empty($_REQUEST['userId']) ? $_REQUEST['userId'] : 0;
        $personUserData=$userMod->getOne(array('cond'=>"`id` = '{$userId}'",'fields'=>'username,phone,point,amount,add_time,phone_email'));
        $personUserData['level']=0;
        $sql="select username,phone,point,amount,add_time,phone_email from ".DB_PREFIX."user";
        $userData=$userMod->querySql($sql);
        $parentUserData=$userMod->getOne(array('cond'=>"`phone` = '{$personUserData['phone_email']}'",'fields'=>'username,phone,point,amount,add_time,phone_email'));
        $res=$userMod->getChildUser($personUserData['phone'],1,4,$personUserData,$parentUserData);
        foreach($res as $k=>$v){
            $show ='';
            $show =str_repeat('|&nbsp;&nbsp;',$v["level"]-1);
            if($v['level']>0){
                $show .="|—&nbsp;";
            }
            $res[$k]['username'] = $show.$v['username'];
            $res[$k]['add_time']=date("Y-m-d",$v['add_time']);
        }
        $parentData=$userMod->getParentUser($userData,$personUserData['phone_email'],1,$personUserData);

        if(count($parentData)==1){
            $this->assign("display",1);
        }
        $end=end($parentData);
        $endKey=key($parentData);
        $parentData[$endKey]['end']=1;
        $this->assign('parentData',$parentData);
        $this->assign('recomUserData',$res);
        $this->display('userPoint/pointList.html');
    }

}
