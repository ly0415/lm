<?php

/**
 * 手机APP
 * @author lvji
 * @date 2016-08-01
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class BaseKfApp extends BaseApp {

    public $langData = array();   // 语言包数据
    public $langid;   // 语言包数据
    public $storeid;   // 语言包数据

    /**
     * 构造函数
     */

    public function __construct() {
        parent::__construct();
        $this->assign('SITE_URL', SITE_URL);
        $this->assign('STATIC_URL', STATIC_URL);
        //不检测登录
        $nologin = array(
            'kfLogin',
            'doLogin'
        );
        if (!in_array(ACT, $nologin)) {
            if (!isset($_SESSION['kf']['kf_id'])) {
                if (IS_AJAX) {
                    header("Location: ?app=baseKf&act=kfLogin&url=" . urlencode(pageUrl()));
                } else {
                    header("Location: ?app=baseKf&act=kfLogin&url=" . urlencode(pageUrl()));
                }
                exit();
            }else{
                $this->storeid = $_SESSION['kf']['kf_store'];
                $storeMod = &m('store');
                $store_info = $storeMod->getOne(array("cond"=>"id=".$this->storeid));
                $this->langid = $store_info['lang_id'];
            }
        }
    }

    //客服登录页面
    public function kfLogin(){
        $this->display("kfindex/login.html");

    }

    /**
     * 登录操作
     * @author lee
     * @date 2018-3-17 11:09:44
     */
    public function doLogin() {
        $account_name = !empty($_REQUEST['account_name']) ? htmlspecialchars(trim($_REQUEST['account_name'])) : '';
        $password = !empty($_REQUEST['password']) ? htmlspecialchars($_REQUEST['password']) : '';

        $info = array();
        if (!$account_name) {
            $this->setData($info, $status = 0, "请填写登录名称");
        }

        //获取用户信息
        $info_account = $this->getuserinfo($account_name);
        //各种判断
        if (!$info_account) {
            $this->setData($info, $status = 0, "账号不存在");
        }
        if (!$password) {
            $this->setData($info, $status = 0, "请填写密码");
        }

        $md5Pass = md5($password); //md5加密
        if (!empty($info_account)) {
            if ($md5Pass == $info_account['password']) {  //判断输入的密码加密后和注册时的密码是否一致
                $_SESSION['kf']['kf_id'] = $info_account['id'];  //存入session
                $_SESSION['kf']['kf_store'] = $info_account['store_id'];  //存入session
                $_SESSION['kf']['kf_name'] = $info_account['kf_name'];
                $_SESSION['kf']['info'] = $info_account;

                $this->setData($info_account, $status = 1, "登录成功", $url = '?app=baseKf&act=kf_list');
            } else {
                $this->setData($info, $status = 0, "密码错误");
            }
        } else {
            $this->setData($info, $status = 0, "网络错误");
        }
    }
    //加载客服页面
    public function kf_list(){
        $fid = $_SESSION['kf']['kf_id'];
        $msgMod = &m('imMsg');
        $userMod = &m("user");
        $sql = "select tid as uid from ".DB_PREFIX."im_msg where fid = {$fid} group by tid UNION  select fid as uid from ".DB_PREFIX."im_msg where tid = {$fid} group by fid ";
        $data = $msgMod->querySql($sql);
        $user_data = array();
        foreach($data as $k=>$v){
            $info = $userMod->getOne(array("cond"=>"id=".$v['uid']));
            if($info){
                $user_data[]=$info;
            }
        }
        $this->assign("user_data",$user_data);
        $this->display("kfindex/chat1.html");
    }
    public function  no_user(){
        echo "暂无用户";exit;
    }
    public function kf_info(){
        $fid = $_SESSION['kf']['kf_id'];

        $tid = $_REQUEST['uid']?$_REQUEST['uid']:'';
        //验证用户是否存在
        $userMod = &m('user');
        $has_user = $userMod->getOne(array("cond"=>"id=".$tid));
        $this->assign("has_user",$has_user);
        $fdMod = &m('imFd');
        $user_fd = $fdMod->getOne(array("cond"=>"uid=".$tid." and uid_2=".$fid));
        $gid = $user_fd['gid']?$user_fd['gid']:'';
        //验证商品是否存在
        $goodMod = &m('goods');
        $storeGoods = &m('areaGood');
        if(!empty($gid)){
            $info = $storeGoods->getLangInfo($gid, $this->langid, $this->storeid);
            if (empty($info)) {
                $this->display("error/404.html");
            }
            $goods_info = $goodMod->getOne(array("cond" => "goods_id=" . $info['goods_id']));
            $info['original_img'] = $goods_info['original_img'];
            $this->assign("gid", $gid);
            $this->assign("info", $info);
        }
        //加载客服信息
        $msgMod = &m('imMsg');

        $msg_history = $msgMod->loadHistory($fid,$tid);
        foreach($msg_history as $k=>$v){
            $msg_history[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            //展示已读信息，并修改为已读
            $msgMod->doEdit($v['id'],array("status"=>1));
        }
        //加载快捷语
        $questionMod = &m('imQuestion');
        $question_list = $questionMod->getData(array("cond"=>"type = 1"));
        $this->assign("question_list",$question_list);

        $this->assign("msg_data",$msg_history);
        $this->assign("tid",$tid);//被发送者ID
        $this->assign("fid",$fid);//自己的ID（发送者）
        $this->assign("gid",$gid);//商品ID

        $this->display("kfindex/page1.html");
    }
    /**
     * 获取客服信息
     */
    public function getuserinfo($name) {
        $userMod = &m('user');
        $where = '   where  mark =1  and  username = "' . $name . '" and is_kefu=1 and kf_status = 1';
        $sql = 'select  * from   ' . DB_PREFIX . 'user ' . $where;
        $data = $userMod->querySql($sql);
        return $data[0];
    }

    /**
     * 数据封装
     * @author lvji
     * @param $status 表示返回数据状态
     * @param $message 对返回状态说明
     * @param $info 返回数据信息
     * @date 2015-03-10
     */
    public function setData($info = array(), $status = 'success', $message = 'ok',$url=null) {
        $data = array(
            'status' => $status,
            'message' => $message,
            'info' => $info,
        );
        if($url){
            $data['url'] = $url;
        }
        echo json_encode($data);
        exit();
    }
    /**
     * 退出系统
     * @author lvji
     * @date 2015-03-10
     */
    public function logout() {

        unset( $_SESSION['kf']['kf_id']);
        //第一步：删除服务器端
        $_SESSION = array();  //第三步：删除$_SESSION全部变量数组
        session_destroy();
        //第二步：删除实际的session
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600);
        }
        $this->setData($info = array('url' => 'kf.php?app=baseKf&act=kfLogin'), 1, '退出成功！');
    }


}

?>