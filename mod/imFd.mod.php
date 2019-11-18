<?php
/**
*
* @author:

*/
if (!defined('IN_ECM')) { die('Forbidden'); }
class ImFdMod extends BaseMod{
    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct("im_fd");
    }

    //删除会话
    public function unBind($fd, $uid = null,$to_id)
    {
        if ($uid && $to_id) {
            $sql = "delete from ".DB_PREFIX."im_fd where uid=$uid"." and uid_2=".$to_id;

        } else {
            $sql = "delete from ".DB_PREFIX."im_fd where fd=$fd";
        }
       $res = $this->sql_b_spec($sql);
    }

    //查询关联对话
    public function getFd($uid,$to_id){
        if($uid && $to_id){
            $res =  $this->getOne(array("cond"=>"uid=".$uid." and uid_2=".$to_id));
        }else{
            $res = array();
        }
        return $res;
    }

    //查询 当前关联的对话
    public function getOneFd($uid){
        $res =  $this->getOne(array("cond"=>"uid=".$uid));
        return $res;
    }
    //绑定关联
    public function bind($uid,$fd,$to_id){
        $info = array(
            'uid'=>$uid,
            'fd'=>$fd,
            'uid_2'=>$to_id
        );
        $res = $this->doInsert($info);
        return $res;
    }

}
?>