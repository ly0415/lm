<?php

/**
 * 微信聊天
 * @author lee
 * @date 2018-4-9 09:23:12
 */
class WxImApp extends BaseWXApp {


    public function __construct() {
        parent::__construct();

    }

    /*
     * 打开聊天窗
     * @author lee
     * @date 2018-4-9 09:23:50
     */
    public function imIndex(){
        $fid = empty($_REQUEST['uid'])?'':$_REQUEST['uid'];
        $gid = $_REQUEST['gid']?$_REQUEST['gid']:'';
        $tid = $_REQUEST['kf_id']?$_REQUEST['kf_id']:'';
        $store_id = $_REQUEST['store_id']?$_REQUEST['store_id']:'';
        $lang_id = $_REQUEST['lang_id']?$_REQUEST['lang_id']:'';
        $userMod = &m('user');
        //验证客服是否存在
//        $has_kf = $userMod->getOne(array("cond"=>"id=".$tid." and is_kefu=1"));
//        if(empty($has_kf)){
//            $this->display("error/404.html");
//        }
        $store_info = $this->getCurStoreInfo($store_id);
        $kf_info = $userMod->getOne(array("cond"=>"id=".$tid));
        $syData = $this->getStoreSymbol($store_id);
        $nowsy = $syData['symbol'];

        $this->assign("nowsy",$nowsy);
        $this->assign("curStoreInfo",$store_info);
        $this->assign("kf_info",$kf_info);
        //验证商品是否存在
        $goodMod = &m('goods');
        $storeGoods = &m('areaGood');
        if(!empty($gid)){
            $info = $storeGoods->getLangInfo($gid, $lang_id, $store_id);
//            if (empty($info)) {
//                $this->display("error/404.html");
//            }
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
        $msg_history = $msgMod->loadHistoryByStore($fid,$store_id);
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

}
