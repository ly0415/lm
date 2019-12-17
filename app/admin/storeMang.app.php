<?php
if (!defined('IN_ECM')) {die('Forbidden');}
class  StoreMangApp extends  BackendApp
{

    private $merSettledMod;

    /**
     * 构造函数
     * @auth wanyan
     * @date 2017-07-24
     */
    public function __construct(){
        parent::__construct();
        $this->storeMod  = &m('store');
        $this->merSettledMod = &m('merSettled');
        $this->userMod =&m('user');
        $this->storeGradeMod =&m('storeGrade');
    }
    /**
     * 商家页面
     * @auth wanyan
     * @date 2017-07-24
     */
    public function storeMangIndex(){
        $store_name = !empty($_REQUEST['store_name']) ? htmlspecialchars(trim($_REQUEST['store_name'])) :'';
        $user_name = !empty($_REQUEST['user_name']) ? htmlspecialchars(trim($_REQUEST['user_name'])) :'';
        $grade_id = !empty($_REQUEST['grade_id']) ? intval($_REQUEST['grade_id']) :0;
        $store_state = !empty($_REQUEST['store_state']) ? intval($_REQUEST['store_state']) :0;
        $where = " where 1=1";
        if(!empty($store_name)){
            $where .= "  and `store_name` like '%".$store_name."%'";
        }
        if(!empty($user_name)){
            $where .= " and `user_name` like '%".$user_name."%'";
        }
        if(!empty($grade_id)){
            $where .=  " and `grade_id` = '{$grade_id}'";
        }
        if(!empty($store_state)){
            $where .= " and `store_state` = '{$store_state}'";
        }
        $where .= " order by `store_id` desc";
        $sql = "select `store_id` ,`store_name`,`seller_name`,`user_id`,`user_name`,`grade_id`,`store_end_time`,`store_state` from ".DB_PREFIX."store".$where;
        $storeInfo = $this->storeMod->querySqlPageData($sql);
        $store_states =G('store_state');
        $sg_level = G('sg_level');
        foreach ($storeInfo['list'] as $k=>$v){
           $storeInfo['list'][$k]['store_statename'] = $store_states[$v['store_state']];
           $storeInfo['list'][$k]['store_end_time'] = date('Y-m-d',$v['store_end_time']);
           $storeInfo['list'][$k]['sg_name']  =$this->getGradeName($v['grade_id']);
        }
        $this->assign('sg_level',$sg_level);
        $this->assign('store_states',$store_states);
        $this->assign('store_name',$store_name);
        $this->assign('user_name',$user_name);
        $this->assign('grade_id',$grade_id);
        $this->assign('store_state',$store_state);
        $this->assign('grades',$this->getGrade());
        $this->assign('page',$storeInfo['ph']);
        $this->assign('list',$storeInfo['list']);
        $this->display('storeMang/storeMangIndex.html');
    }
    /**
     * 商家页面
     * @auth wanyan
     * @date 2017-07-24
     */
    public function getGradeName($sg_id){
        $query=array(
            'cond' => "`sg_id`='{$sg_id}'",
            'fields'=>'sg_name'
        );
        $rs =$this->storeGradeMod->getOne($query);
        return $rs['sg_name'];
    }
    /**
     * 商家页面
     * @auth wanyan
     * @date 2017-07-24
     */
    public function storeApply(){
        $store_name = !empty($_REQUEST['store_name']) ? htmlspecialchars(trim($_REQUEST['store_name'])) :'';
        $user_name = !empty($_REQUEST['user_name']) ? htmlspecialchars(trim($_REQUEST['user_name'])) :'';
        $grade_id = !empty($_REQUEST['grade_id']) ? intval($_REQUEST['grade_id']) :0;
        $joinin_state = !empty($_REQUEST['joinin_state']) ? intval($_REQUEST['joinin_state']) :0;
        $where = " where 1=1";
        if(!empty($store_name)){
            $where .= "  and store_name like '%".$store_name."%'";
        }
        if(!empty($user_name)){
            $where .= " and user_name like '%".$user_name."%'";
        }
        if(!empty($grade_id)){
            $where .=  " and sg_id = '{$grade_id}'";
        }
        if(!empty($joinin_state)){
            $where .= " and `joinin_state` = '{$joinin_state}'";
        }
        $sql = "select `id` ,`user_id`,`store_name`,`user_name`,`sg_name`,`joinin_state` from ".DB_PREFIX."store_joinin".$where;
        $joinInfo = $this->merSettledMod->querySqlPageData($sql);
        $joinin_states = G('joinin_state');
        foreach($joinInfo['list'] as $key=>$val){
            $joinInfo['list'][$key]['joinin_statename'] = $joinin_states[$val['joinin_state']];
        }
        $sg_level = G('sg_level');
        $this->assign('sg_level',$sg_level);
        $this->assign('joinin_states',$joinin_states);
        $this->assign('store_name',$store_name);
        $this->assign('user_name',$user_name);
        $this->assign('grade_id',$grade_id);
        $this->assign('joinin_state',$joinin_state);
        $this->assign('page',$joinInfo['ph']);
        $this->assign('list',$joinInfo['list']);
        $this->assign('grades',$this->getGrade());
        $this->display('storeMang/storeApply.html');
    }
    /**
     * 获取等级
     * @author wanyanshaofeng
     * @date 2017-7-20
     */
    public function getGrade(){
        $sql = "select `sg_id`,`sg_name`,`sg_price` from ".DB_PREFIX."store_grade";
        $rs = $this->merSettledMod->querySql($sql);
        return $rs;
    }

    /**
     * 商家审核页面
     * @auth wanyan
     * @date 2017-07-24
     */
    public function storeMangCheck(){
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) :'0';
        $query=array(
            'cond' => "`id`='{$id}'"
        );
        $rs = $this->merSettledMod->getOne($query);
        $this->assign('rs',$rs);
        $this->assign('id',$id);
        $this->assign('join_state',$join_state);
        $this->assign('act','storeApply');
        $this->display('storeMang/checkInfo.html');
    }
    /**
     * 商家审核功能
     * @auth wanyan
     * @date 2017-07-24
     */
    public function doChechInfo(){
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) :'0';
        $joinin_state = !empty($_REQUEST['joinin_state']) ? intval($_REQUEST['joinin_state']) :'0';
        $joinin_message = !empty($_REQUEST['joinin_message']) ? htmlspecialchars($_REQUEST['joinin_state']) :'';
        if($joinin_state ==10){
            $data=array(
                'joinin_state'=>20,
                'joinin_message'=>$joinin_message

            );
        }else{
            $data=array(
                'joinin_state'=>40,
                'joinin_message'=>$joinin_message,
                'paying_money_certificate_explain'=>'已付款'
            );
        }
        $a_id =$this->merSettledMod->doEdit($id,$data);
        $query=array(
            'cond' =>"`id`='{$id}'"
        );
        $storeJoinInfo = $this->merSettledMod->getOne($query);
        if($storeJoinInfo['joinin_state'] == 40){
            $store_data=array(
                'store_name'=>$storeJoinInfo['store_name'],
                'grade_id'=>$storeJoinInfo['sg_id'],
                'user_id'=>$storeJoinInfo['user_id'],
                'user_name'=>$storeJoinInfo['user_name'],
                'seller_name'=>$storeJoinInfo['seller_name'],
                'store_company_name'=>$storeJoinInfo['company_name'],
                'area_info'=>$storeJoinInfo['company_address'],
                'store_address'=>$storeJoinInfo['company_address_detail'],
                'store_state'=>1,
                'store_time'=>time(),
                'store_end_time'=>strtotime("+{$storeJoinInfo['joinin_year']} year"),
                'add_time'  =>time()
            );
            $insert_id = $this->storeMod->doInsert($store_data);
            if(!empty($insert_id)) {
                $member_data =array(
                    'login_type' =>'business'
                );
                $affact_id = $this->userMod->doEdit($storeJoinInfo['user_id'],$member_data);
            }
        }
        if($a_id){
            $info['url'] = '?app=storeMang&act=storeApply';
            $this->setData($info,$state='1',$message='操作成功！');
        }else{
            $this->setData($info=array(),$state='2',$message='操作失败！');
        }
    }
    /**
     * 商家审核功能
     * @auth wanyan
     * @date 2017-07-24
     */
    public function doChechfail(){
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) :'0';
        $joinin_state = !empty($_REQUEST['joinin_state']) ? intval($_REQUEST['joinin_state']) :'0';
        $joinin_message = !empty($_REQUEST['joinin_message']) ? htmlspecialchars($_REQUEST['joinin_message']) :'';
        if(empty($joinin_message)){
            $this->setData($info=array(),$state='2',$message='审核意见不能为空！');
        }
        if($joinin_state ==10){
            $data=array(
                'joinin_state'=>30,
                'joinin_message'=>$joinin_message

            );
        }else{
            $data=array(
                'joinin_state'=>31,
                'joinin_message'=>$joinin_message
            );
        }
        $a_id =$this->merSettledMod->doEdit($id,$data);
        if($a_id){
            $info['url'] = '?app=storeMang&act=storeApply';
            $this->setData($info,$state='1',$message='操作成功！');
        }else{
            $this->setData($info=array(),$state='2',$message='操作失败！');
        }
    }
    /**
     * 商家审核功能
     * @auth wanyan
     * @date 2017-07-24
     */
    public function doCheck(){
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) :'0';
        $join_state = !empty($_REQUEST['join_state']) ? intval($_REQUEST['join_state']) :'0';
        $check_state = !empty($_REQUEST['check_state']) ? intval($_REQUEST['check_state']) :'0';
        $query =array(
            'cond' =>"`id` ='{$id}'"
        );
        $storeJoinInfo = $this->merSettledMod->getOne($query);
        $store_data=array(
            'store_name'=>$storeJoinInfo['store_name'],
            'grade_id'=>$storeJoinInfo['sg_id'],
            'user_id'=>$storeJoinInfo['user_id'],
            'user_name'=>$storeJoinInfo['user_name'],
            'seller_name'=>$storeJoinInfo['seller_name'],
            'sc_id'=>$storeJoinInfo['sc_id'],
            'store_company_name'=>$storeJoinInfo['company_name'],
            'area_info'=>$storeJoinInfo['company_address'],
            'store_address'=>$storeJoinInfo['company_address_detail'],
            'store_state'=>1,
            'store_time'=>time(),
            'store_end_time'=>strtotime('+1 year'),
            'add_time'  =>time()
        );
        if($check_state ==1){
            if($join_state == 10 || $join_state == 30){
                $data =array(
                    'joinin_state' =>20
                );
                $res = $this->merSettledMod->doEdit($id,$data);
            }elseif($join_state == 11 || $join_state == 31 ){
                $data =array(
                    'joinin_state' =>40
                );
                $res = $this->merSettledMod->doEdit($id,$data);
                $store_id = $this->storeMod ->doInsert($store_data);
                if(!empty($store_id)) {
                    $member_data =array(
                        'login_type' =>'business'
                    );
                    $affact_id = $this->userMod->doEdit($storeJoinInfo['user_id'],$member_data,true);
                }
            }
        }else{
            if($join_state == 10 || $join_state == 30){
                $data =array(
                    'joinin_state' =>30
                );
                $res = $this->merSettledMod->doEdit($id,$data);
            }elseif($join_state == 11 || $join_state == 31){
                $data =array(
                    'joinin_state' =>31
                );
                $res = $this->merSettledMod->doEdit($id,$data);
            }
        }
        if(!empty($res)){
                $this->setData($info=array(),$state='1',$message='操作成功！');
        }else{
            $this->setData($info=array(),$state='2',$message='操作失败！');
        }
    }
    /**
     * 商家申请查看
     * @auth wanyan
     * @date 2017-07-24
     */
    public function storeApplyScan(){
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) :'0';
        $query=array(
            'cond' => "`id`='{$id}'"
        );
        $rs = $this->merSettledMod->getOne($query);
        $this->assign('rs',$rs);
        $this->assign('act','storeApply');
        $this->display('storeMang/storeApplyScan.html');
    }
    /**
     * 商家申请查看
     * @auth wanyan
     * @date 2017-07-24
     */
    public function storeScan(){
        $store_id = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) :'0';
        $query=array(
            'cond' => "`store_id`='{$store_id}'"
        );
        $rs = $this->storeMod->getOne($query);
        $this->assign('sg_name',$this->getGradeName($rs['grade_id']));
        $this->assign('rs',$rs);
        $this->assign('act','storeApply');
        $this->display('storeMang/storeScan.html');
    }
    /**
     * 商家申请删除
     * @auth wanyan
     * @date 2017-07-25
     */
    public function dele(){
        $user_id = !empty($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) :'0';
        if(empty($user_id)){
            return false;
        }
        $query_store =array(
            'cond' =>" `user_id` ='{$user_id}'"
        );
        $store_dele = $this->storeMod->doDelete($query_store);
        $storeJoinin_dele = $this->merSettledMod->doDelete($query_store);
        if($store_dele && $storeJoinin_dele){
            $this->setData($info=array(),$status='1',$message='删除成功！');
        }else{
            $this->setData($info=array(),$status='2',$message='删除失败！');
        }
    }
    /**
     * 商家申请删除
     * @auth wanyan
     * @date 2017-07-25
     */
    public function addStore(){
        $this->display('storeMang/addStore.html');
    }
}
?>
