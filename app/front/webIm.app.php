<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/10
 * Time: 10:13
 */

class  WebImApp extends  BaseFrontApp{

    private $msgMod;
    private $fdMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->msgMod = &m('imMsg');
        $this->fdMod = &m('imFd');


    }

    /**
     * 析构函数
     */
    public function __destruct() {


    }


    public function im(){
        $fid = $_REQUEST['fid'];
        $gid = $_REQUEST['gid']?$_REQUEST['gid']:'';
        $tid = $_REQUEST['tid']?$_REQUEST['tid']:'';
        //验证用户是否存在
        $userMod = &m('user');
        $has_user = $userMod->getOne(array("cond"=>"id=".$fid));
        if(empty($has_user)){
            $referer = $_SERVER['HTTP_REFERER']; // 并不能真实获取上一级的页面。
            $url = "index.php?app=user&act=login&pageUrl=" . urlencode($referer);
            header("Location:$url");
        }
        //验证客服是否存在
        $has_kf = $userMod->getOne(array("cond"=>"id=".$tid." and is_kefu=1"));
        if(empty($has_kf)){
            $this->display("error/404.html");
        }
        //验证商品是否存在
        $goodMod = &m('goods');
        $storeGoods = &m('areaGood');
        if(!empty($gid)){
            $info = $storeGoods->getLangInfo($gid, $this->langid);
        }
        //加载语言包
        $this->load($this->shorthand, 'goods/goods');
        $this->assign('langdata', $this->langData);
        $goods_info = $goodMod->getOne(array("cond" => "goods_id=" . $info['goods_id']));
        $info['original_img'] = $goods_info['original_img'];
        $this->assign("info", $info);

        //加载客服信息
        $msgMod = &m('imMsg');
        $this->assign("tid",$tid);
        $msg_history = $msgMod->loadHistory($fid,$tid);
        foreach($msg_history as $k=>$v){
            $msg_history[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            //展示已读信息，并修改为已读
            $msgMod->doEdit($v['id'],array("status"=>1));
        }

        //加载快捷语
        $questionMod = &m('imQuestion');
        $question_list = $questionMod->getData(array("cond"=>"type = 2"));
        $this->assign("question_list",$question_list);

        $this->assign("msg_data",$msg_history);
        $this->assign("gid",$gid);
        $this->assign("fid",$fid);
        $this->display('im/im.html');

    }


    /**
     * 打开 向数据库添加 fd
     */
    public function  ajaxInsertFd(){
        $uid = $_REQUEST['uid'];
        $fd = $_REQUEST['fd'];
        $uid_2 = $_REQUEST['tid'];
        $gid = $_REQUEST['gs_id'];
        if(empty($uid)){
            $this  -> setData(array(), '0', '用户id不能为0');
        }
        if(empty($fd)){
            $this -> setData(array(),'0','客户端绑定的id不能为0');
        }
        $insertData = array(
            'uid' => $uid,
            'fd' => $fd,
            'uid_2'=>$uid_2,
            'gid'=>$gid
        );
        $this -> fdMod -> doInsert($insertData);
    }


}