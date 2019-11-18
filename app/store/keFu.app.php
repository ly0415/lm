<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/27
 * Time: 15:03
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class  KeFuApp  extends  BaseStoreApp{

  //  private $msgMod;
    private $userMod;
    private $lang_id;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();

       // $this->msgMod  = &m('imMsg');
        $this->userMod = &m('user');
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言

    }

    /**
     * 析构函数
     */
    public function __destruct() {

    }



    /**
     * 客服列表
     */
    public function  index(){
        $kf_name = trim(htmlspecialchars($_REQUEST['kf_name']));
        $where = '  where  is_kefu = 1  and store_id = '.$this -> storeId;
        if(!empty($kf_name)){
            $where .= '  and  kf_name  like "%'.$kf_name.'%"';
        }
        $sql = 'SELECT  id,username,phone,kf_name,kf_status  FROM  bs_user '.$where;
        $kfList = $this -> userMod -> querySql($sql);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('kfList', $kfList);
        $this -> assign('kf_name',$kf_name);
        if($this -> lang_id){
            $this->display('kefu/index_1.html');
        }else{
            $this->display('kefu/index.html');
        }

    }

    /**
     * 改变状态
     */
    public function ajaxStatus(){
        $uid = $_REQUEST['uid'];
        $to_status = $_REQUEST['to_status'];
        $data = array('kf_status' => $to_status);
        $res = $this->userMod->doEdit($uid, $data);

        if ($res) {
            echo json_encode(array('res' => 1));
            exit;
        } else {
            echo json_encode(array('res' => 0));
            exit;
        }

    }

    /**
     * 添加
     */
    public function add(){
        $this->assign('lang_id', $this->lang_id);
        if($this->lang_id){
            $this->display('kefu/kefuAdd_1.html');
        }else{
            $this->display('kefu/kefuAdd.html');
        }

    }

    public function doAdd(){
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;

        $phone = !empty($_REQUEST['phone']) ? trim($_REQUEST['phone']) : '';
        $email = !empty($_REQUEST['email']) ? trim($_REQUEST['email']) : '';
        $passwd = !empty($_REQUEST['passwd']) ? trim($_REQUEST['passwd']) : '';
        $kf_name = !empty($_REQUEST['kf_name']) ? trim($_REQUEST['kf_name']) : '';

        if (empty($email)) {
            $this->setData(array(), '0', $a['kf_email']);
        }

        if (empty($phone)) {
            $this->setData(array(), '0', $a['kf_phone'] );
        }

        if (empty($passwd)) {
            $this->setData(array(), '0', $a['kf_pwd']);
        }

        if (empty($kf_name)) {
            $this->setData(array(), '0', $a['kf_nickname']);
        }

        if (!preg_match('/^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])\d{8}$/', $phone)) {
            $this->setData(array(), $status = '0', $a['personnel_legitimate']);
        }

        $data = array(
            'username' => $phone,
            'phone' => $phone,
            'email' => $email,
            'password' => md5($passwd),
            'kf_name' => $kf_name,
            'is_kefu' => 1,
            'kf_status' => 1,
            'store_id' => $this -> storeId,
            'store_cate_id' => $this -> storecate,
            'add_time' => time()
        );

        $res = $this -> userMod -> doInsert($data);

        if ($res ) {
            $info['url'] = "store.php?app=keFu&act=index&lang_id={$this->lang_id}";
            $this->setData($info, '1', $a['add_sussess']);
        } else {
            $this->setData(array(), '0', $a['add_fail']);
        }


    }

    /**
     * 编辑
     */
    public function edit(){
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0 ;
        if(empty($id)){
            return '';
        }
        $sql = 'select  id,username,phone,email,kf_name  from  bs_user where id = '.$id;
        $data = $this -> userMod -> querySql($sql);
        $this -> assign('data',$data[0]);

        $this->assign('lang_id', $this->lang_id);

        if($this -> lang_id){
            $this->display('kefu/kefuEdit_1.html');
        }else{
            $this->display('kefu/kefuEdit.html');
        }

    }


    public function doEdit(){
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;

        $phone = !empty($_REQUEST['phone']) ? trim($_REQUEST['phone']) : '';
        $email = !empty($_REQUEST['email']) ? trim($_REQUEST['email']) : '';
        $passwd = !empty($_REQUEST['passwd']) ? trim($_REQUEST['passwd']) : '';
        $kf_name = !empty($_REQUEST['kf_name']) ? trim($_REQUEST['kf_name']) : '';
        $id = $_REQUEST['id'];

        if (empty($email)) {
            $this->setData(array(), '0', $a['kf_email']);
        }

        if (empty($phone)) {
            $this->setData(array(), '0',  $a['kf_phone']);
        }

        if (!preg_match('/^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])\d{8}$/', $phone)) {
            $this->setData(array(), $status = '0', $a['personnel_legitimate']);
        }

        if (empty($kf_name)) {
            $this->setData(array(), '0',  $a['kf_nickname']);
        }



        if(!empty($passwd)){
            $data = array(
                'username' => $phone,
                'phone' => $phone,
                'email' => $email,
                'password' => md5($passwd),
                'kf_name' => $kf_name,
            );
        }else{
            $data = array(
                'username' => $phone,
                'phone' => $phone,
                'email' => $email,
                'kf_name' => $kf_name,
            );
        }

        $res = $this -> userMod -> doEdit($id,$data);

        if ($res ) {
            $info['url'] = "store.php?app=keFu&act=index&lang_id={$this->lang_id}";
            $this->setData($info, '1', $a['edit_sussess']);
        } else {
            $this->setData(array(), '0', $a['edit_fail']);
        }


    }

    /**
     * 删除
     */
    public function dele(){
        $this->load($this->lang_id, 'admin/admin');
        $a = $this->langData;
        $uid = $_REQUEST['id'];
        $res = $this->userMod->doDrop($uid);
        if($res){
            $this->setData($info=array(),$status='1',$a['delete_Success']);
        }else{
            $this->setData($info=array(),$status='0',$a['delete_fail']);
        }
    }





}