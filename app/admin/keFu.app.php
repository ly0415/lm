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

class  KeFuApp  extends  BackendApp{

    private  $questionMod;
    private  $welMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->questionMod = &m('imQuestion');
        $this->welMod = &m('imWel');
       // $this->msgMod  = &m('imMsg');
    }

    /**
     * 析构函数
     */
    public function __destruct() {

    }

    /**
     * 聊天欢迎语列表
     */
    public function  welIndex(){
        $this->display('imKefu/index.html');
    }
    /*
     * 聊天欢迎语设置
     */
    public function setWel(){

    }
    /*
     * 快速提问、反馈
     */
    public function questionList(){
        $content = !empty($_REQUEST['content']) ? htmlspecialchars(trim($_REQUEST['content'])) : '';
        $type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : '';
        $where = "1 =1";
        if($content){
            $this->assign('content', $content);
            $where .= " and content like '%".$content."%'";
        }
        if($type){
            $this->assign('type', $type);
            $where .= " and type = ".$type;
        }
        $list = $this->questionMod->pageData(array("cond"=>$where));
        $this->assign('res', $list['list']);
        $this->assign('page_html', $list['ph']);
        $this->display("im/index.html");
    }
    /*
     * 添加快捷语
     */
    public function questionAdd(){
        $this->display("im/questionAdd.html");
    }
    public function questionEdit(){
        $id = !empty($_REQUEST['id']) ? ($_REQUEST['id']) : "";
        $info = $this->questionMod->getOne(array("cond"=>"id=".$id));
        $this->assign("info",$info);
        $this->display("im/questionEdit.html");
    }
    /*
     * 添加编辑页处理
     */
    public function doQuestionAdd(){
        $content = !empty($_REQUEST['content']) ? htmlspecialchars(trim($_REQUEST['content'])) : '';
        $type = !empty($_REQUEST['type']) ? ($_REQUEST['type']) : 0;
        $id = !empty($_REQUEST['id']) ? ($_REQUEST['id']) : "";
        if(empty($content)){
            $this->setData(array(), '0', $this->langDataBank->project->fill_shortcut);
        }
        if(empty($type)){
            $this->setData(array(), '0', $this->langDataBank->project->type_select);
        }
        if($id){
            $arr = array(
                "content" => $content,
                "type" => $type
            );

            $res = $this->questionMod->doEdit($id,$arr);
        }else{
            $arr = array(
                "content" => $content,
                "type" => $type,
                "add_time" => time()
            );
           $res = $this->questionMod->doInsert($arr);
        }
        if($res){
            if ($res) {
                $this->addLog('添加客服快捷语');
                $this->setData(array(), '1', $this->langDataBank->public->cz_success);
            } else {
                $this->setData(array(), '0', $this->langDataBank->public->cz_error);
            }
        }
    }

    /*
     * 删除快捷语
     */
    public function delQuestion(){
        $id = !empty($_REQUEST['id']) ? htmlspecialchars($_REQUEST['id']) : '';
        if (empty($id)) {
            return false;
        }
        $rs = $this->questionMod->doDrop($id);
        if ($rs) {
            $this->addLog('快捷语删除操作');
            $this->setData($info = array(), $status = 1, $this->langDataBank->public->drop_success);
        } else {
            $this->setData($info = array(), $status = 0,  $this->langDataBank->public->drop_fail);
        }
    }










}