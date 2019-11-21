<?php
if (!defined('IN_ECM')) {die('Forbidden');}
class  MemberApp extends  BackendApp{

    private  $memberMod ;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();

        $this -> memberMod = &m('user');
    }

    /**
     * 析构函数
     */
    public function __destruct() {
    }


    /**
     * 用户列表
     */
    public function memberIndex()
    {
        $username = trim($_REQUEST['username']);
        $phone = trim($_REQUEST['phone']);
        $this->assign('username',$username);
        $this->assign('phone',$phone);
        $where=" where 1=1";
        if($username){
            $where.=" and username like '%{$username}%'";
        }
        if($phone){
            $where.=" and phone like '%{$phone}%'";
        }
        $sql="select * from bs_user ".$where;
        $data=$this->memberMod->querySqlPageData($sql);
        $this->assign('list',$data['list']);
        $this->assign('page_html',$data['ph']);

        $this -> display('member/index.html');
    }

    /**
     * 用户添加
     */
    public function memberAdd()
    {
        
        $this->display('member/memberAdd.html');
    }

    public function do_add()
    {
        $username = trim( $_REQUEST['username']);
        $phone = trim($_REQUEST['phone']);
        $password = $_REQUEST['password'];
        $company_id = $_REQUEST['company_id'];
        $project_id = $_REQUEST['project_id'];

        if(empty($username)){
            $this->setData(array(),'0','会员名称必填！');
        }
        if(empty($phone)){
            $this->setData(array(),'0','电话号码必填！');
        }
        if(! preg_match('/^1[34578]\d{9}$/',$phone)){
            $this->setData(array(),'0','手机号码格式不正确！');
        }

        $sql_u = 'select id from  wx_member  where   username = "'.$username.'"  ';
        $res = $this->memberMod -> querySql($sql_u);
        if(!empty($res)){
            $this->setData(array(),'0','会员名称重复！');
        }
        $sql_p = 'select id from wx_member  where   phone = "'.$phone.'" ';;
        $res2 = $this->memberMod -> querySql($sql_p);
        if(!empty($res2)){
            $this->setData(array(),'0','手机号码重复！');
        }

        if(empty($password)){
            $this->setData(array(),'0','登录密码必填！');
        }
        if(empty($company_id)){
            $this->setData(array(),'0','公司必选！');
        }
        if(empty($project_id)){
            $this->setData(array(),'0','项目必选！');
        }

        //member表 插入信息
        $data = array(
            'username' => $username,
            'phone' => $phone,
            'password' => md5($password),
            'company_id' => $company_id,
            'project_id' => $project_id,
            'addtime' => time()

        );

        $res = $this-> memberMod ->doInsert($data);

        if($res){
            $this->setData(array(),'1','添加成功！');
        }else{
            $this->setData(array(),'0','添加失败！');
        }

    }


    /**
     * 用户编辑
     */
    public function edit()
    {
        $id = $_REQUEST['id'];
        if(empty($id)){
            $this -> display('member/edit.html');
        }

        //公司列表
        $companyMod = &m('company');
        $sql_com = 'select  id,company_name from  wx_company ';
        $company_list =  $companyMod -> querySql($sql_com);
        $this->assign('company_list',$company_list);

        // 项目列表
        $projectMod = &m('com_cus_man');
        $sql_pr = 'select  id,name  from  wx_project';
        $project_list = $projectMod -> querySql($sql_pr);
        $this->assign('project_list',$project_list);

        $where = '   where  m.`id`= '.$id;
        $sql = 'SELECT   m.`id`, m.`username`,m.`password`,m.`phone`,m.`project_id`,m.`company_id`,m.`addtime`,c.`company_name`,p.`name` AS project_name
                  FROM   wx_member AS m
                  LEFT JOIN wx_project AS p
                    ON m.`project_id` = p.`id`
                  LEFT JOIN wx_company AS c
                    ON m.`company_id` = c.`id`'.$where;

        $data = $this->memberMod -> querySql($sql);

        $this->assign('data',$data[0]);

        $this -> display('member/memberEdit.html');
    }

    public function do_edit()
    {
        $id = $_REQUEST['id'];
        $username = trim( $_REQUEST['username']);
        $phone = trim($_REQUEST['phone']);
        $password = $_REQUEST['password'];
        $company_id = $_REQUEST['company_id'];
        $project_id = $_REQUEST['project_id'];

        if(empty($id)){
            $this->setData(array(),'0','系统错误！');
        }
        if(empty($username)){
            $this->setData(array(),'0','管理员名称必填！');
        }
        if(empty($phone)){
            $this->setData(array(),'0','电话号码必填！');
        }
        if(! preg_match('/^1[34578]\d{9}$/',$phone)){
            $this->setData(array(),'0','手机号码格式不正确！');
        }

        $sql_u = 'select id from wx_member  where   username = "'.$username.'"  and  id !='.$id;
        $res = $this->memberMod -> querySql($sql_u);
        if(!empty($res)){
            $this->setData(array(),'0','管理员名称重复！');
        }

        $sql_p = 'select id from wx_member  where   phone = "'.$phone.'"  and  id !='.$id;
        $res2 = $this->memberMod -> querySql($sql_p);
        if(!empty($res2)){
            $this->setData(array(),'0','手机号码重复！');
        }

        if(empty($company_id)){
            $this->setData(array(),'0','公司必选！');
        }
        if(empty($project_id)){
            $this->setData(array(),'0','项目必选！');
        }
        //判断密码是否改变
        if($password != '******' ){

            $data = array(
                'username' => $username,
                'phone' => $phone,
                'password' => md5($password),
                'company_id' => $company_id,
                'project_id' => $project_id,
                'addtime' => time()

            );

        }else{

            $data = array(
                'username' => $username,
                'phone' => $phone,
                'company_id' => $company_id,
                'project_id' => $project_id,
                'addtime' => time()

            );
        }

        $res = $this-> memberMod ->doEdit($id,$data);

        if($res ){
            $this->setData(array(),'1','修改成功！');
        }else{
            $this->setData(array(),'0','修改失败！');
        }

    }

    /**
     * 用户删除
     */
    public function dele()
    {

        $id = $_REQUEST['id'];

        if(empty($id)){
            $this->setData(array(),'0','删除失败！');
        }

        $query = array(  "cond"=>"id={$id}" );
        $res = $this->memberMod->doDelete($query);

        if($res){
            $this->setData(array(),'1','删除成功！');
        }else{
            $this->setData(array(),'1','删除失败！');
        }


    }



}