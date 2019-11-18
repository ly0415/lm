<?php
/**
* 广告模型
* @author: jh
* @date: 2017/6/21
*/
if (!defined('IN_ECM')) { die('Forbidden'); }
class ImMsgMod extends BaseMod{
    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct("im_msg");
    }
    //加载历史记录
    public function loadHistory($fid, $tid, $id = null)
    {
        $and = $id ? " and id=$id" : '';
        $order = " order by add_time ASC";
        $sql = "select * from ".DB_PREFIX."im_msg where ((fid=$fid and tid = $tid) or (tid=$fid and fid = $tid))" . $and.$order;
        $data = $this->querySql($sql);
        return $data;
    }

    //加载历史记录
    public function loadHistoryByStore($uid, $store_id, $id = null)
    {
        $order = " order by add_time ASC";
        $sql = "select * from ".DB_PREFIX."im_msg where (fid=$uid  or tid=$uid)" .$order;
        $data = $this->querySql($sql);
        return $data;
    }

    public function loadKf($fid){
        $order = " order by add_time ASC";
        $sql = "select * from ".DB_PREFIX."im_msg where ((fid=$fid) or (tid=$fid))" .$order."group by ";
        $data = $this->querySql($sql);
        return $data;
    }

    //生成历史记录
    public function addMsg($fid,$tid,$cont){
       $cont=   addslashes($cont);
        $info =  array(
            'content'=>$cont,
            'tid'=>$tid,
            'fid'=>$fid,
            'add_time'=>time()
        );
        $res = $this->doInsert($info);
        return $res;
    }



}
?>