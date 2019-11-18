<?php
/**
 * 客服管理模块
 * @author wanyan
 * @date 2018-1-2
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class KfApp extends BackendApp{

    private $lang_id;
    private $kfMod;
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->kfMod = &m('kf');
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
    }

    /**
     * 客服管理模块
     * @author wanyan
     * @date 2018-1-2
     */
    public function index(){
        $name = !empty($_REQUEST['name']) ? htmlspecialchars($_REQUEST['name']) :'';
        $where = " where `mark` = '1' order by add_time desc";
        if(!empty($name)){
            $where .= " and kf_name like '%".$name."%'";
        }
        $sql = "select * from ".DB_PREFIX."kf ". $where;
        $list = $this->kfMod -> querySqlPageData($sql);
        foreach ($list['list'] as $k =>$v){
            $list['list'][$k]['add_time'] =date('Y-m-d H:i:s',$v['add_time']);
        }
        $this->assign('list',$list['list']);
        $this->assign('page',$list['ph']);
        $this->assign('name',$name);
        if($this->lang_id == 0){
            $this->display('keFu/index.html');
        }else{
            $this->display('keFu/index_en.html');
        }

    }
    /**
     * 客服添加模块
     * @author wanyan
     * @date 2018-1-2
     */
    public function add(){
        $this->assign('lang_id',$this->lang_id);
        if($this->lang_id==0){
            $this->display('keFu/add.html');
        }else{
            $this->display('keFu/add_en.html');
        }
    }
    /**
     * 客服添加模块
     * @author wanyan
     * @date 2018-1-2
     */
    public function doAdd(){
        $this->load($this->lang_id, 'admin/admin');
        $a = $this->langData;
        $kf_name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) :'';
        $kf_QQ   = !empty($_REQUEST['QQ']) ?   htmlspecialchars(trim($_REQUEST['QQ'])) :'';
        $lang_id  = !empty($_REQUEST['lang_id']) ?   htmlspecialchars(trim($_REQUEST['lang_id'])) :'';
        if(empty($kf_name)){
            $this->setData($info=array(),$status='0',$a['kf_name']);
        }else{
            $rs = $this->kfMod->getOne(array('cond'=>"`kf_name` = '{$kf_name}' and `mark` = '1'",'fields' =>"kf_name"));
            if($rs['kf_name']){
                $this->setData($info=array(),$status='0',$a['kf_name_exist']);
            }
        }
        if(mb_strlen($kf_name) < 2 || mb_strlen($kf_name) > 10){
            $this->setData($info=array(),$status='0',$a['kf_name_length']);
        }
        if(empty($kf_QQ)){
            $this->setData($info=array(),$status='0',$a['kf_QQ']);
        }else{
            $rs = $this->kfMod->getOne(array('cond'=>"`kf_QQ` = '{$kf_QQ}' and `mark` = '1'",'fields' =>"kf_QQ"));
            if($rs['kf_QQ']){
                $this->setData($info=array(),$status='0',$a['kf_QQ_exist']);
            }
        }
        if(!preg_match("/^[1-9][0-9]{4,12}$/",$kf_QQ)){
            $this->setData($info=array(),$status='0',$a['kf_QQ_patten']);
        }
        $data =array(
            'kf_name' =>$kf_name,
            'kf_QQ' =>$kf_QQ,
            'add_time' =>time(),
        );
        $rs = $this->kfMod->doInsert($data);
        if($rs){
            $info['url'] = "?app=kf&act=index&lang_id=".$lang_id;
            $this->setData($info,$status='1',$a['add_Success']);
        }else{
            $this->setData($info=array(),$status='0',$a['add_fail']);
        }

    }

    /**
     * 客户编辑模块
     * @author wanyan
     * @date 2018-1-2
     */
    public function edit(){
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '';
        $lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0';
        if(empty($id)){
            return false;
        }
        $rs = $this->kfMod->getOne(array('cond'=>"`id` = '{$id}'"));
        $this->assign('list',$rs);
        $this->assign('lang_id',$lang_id);
        if($this->lang_id==0){
            $this->display('keFu/edit.html');
        }else{
            $this->display('keFu/edit_en.html');
        }
    }
    /**
     * 客户编辑模块
     * @author wanyan
     * @date 2018-1-2
     */
    public function doEdit(){
        $this->load($this->lang_id, 'admin/admin');
        $a = $this->langData;
        $kf_name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) :'';
        $kf_QQ   = !empty($_REQUEST['QQ']) ?   htmlspecialchars(trim($_REQUEST['QQ'])) :'';
        $lang_id  = !empty($_REQUEST['lang_id']) ?   htmlspecialchars(trim($_REQUEST['lang_id'])) :'';
        $id = !empty($_REQUEST['id']) ?   intval($_REQUEST['id']) :'';
        if(empty($kf_name)){
            $this->setData($info=array(),$status='0',$a['kf_name']);
        }else{
            $rs = $this->kfMod->getOne(array('cond'=>"`kf_name` = '{$kf_name}' and `mark` = '1' and `id` <> '{$id}'",'fields' =>"kf_name"));
            if($rs['kf_name']){
                $this->setData($info=array(),$status='0',$a['kf_name_exist']);
            }
        }
       if(mb_strlen($kf_name) < 2 || mb_strlen($kf_name) > 10){
           $this->setData($info=array(),$status='0',$a['kf_name_length']);
       }
        if(empty($kf_QQ)){
            $this->setData($info=array(),$status='0',$a['kf_QQ']);
        }else{
            $rs = $this->kfMod->getOne(array('cond'=>"`kf_QQ` = '{$kf_QQ}' and `mark` = '1' and `id` <> '{$id}'",'fields' =>"kf_QQ"));
            if($rs['kf_QQ']){
                $this->setData($info=array(),$status='0',$a['kf_QQ_exist']);
            }
        }
        if(!preg_match("/^[1-9][0-9]{4,12}$/",$kf_QQ)){
            $this->setData($info=array(),$status='0',$a['kf_QQ_patten']);
        }
        $data =array(
            'kf_name' =>$kf_name,
            'kf_QQ' =>$kf_QQ,
        );
        $rs = $this->kfMod->doEdit($id,$data);
        if($rs){
            $info['url'] = "?app=kf&act=index&lang_id=".$lang_id;
            $this->setData($info,$status='1',$a['edit_Success']);
        }else{
            $this->setData($info=array(),$status='0',$a['edit_fail']);
        }
    }

    /**
     * 客服删除模块
     * @author wanyan
     * @date 2018-1-2
     */

    public function dele(){
       $this->load($this->lang_id, 'admin/admin');
       $a = $this->langData;
       $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '';
       if(empty($id)){
           return false;
       }
       $rs = $this->kfMod->doMark($id);
       if($rs){
           $this->setData($info=array(),$status='1',$a['delete_Success']);
       }else{
           $this->setData($info=array(),$status='0',$a['delete_fail']);
       }

    }



}