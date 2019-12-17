<?php

/**
 * Class FxUserApp
 * wangh
 */
class FxUserApp extends BaseWxApp {

    public $fxUserMod, $storeCateMod, $storeMod, $userMod, $fxuserMoneyMod;

    public function __construct() {
        parent::__construct();
        $this->fxUserMod = &m('fxuser');
//        $this->fxTreeMod = &m('fxuserTree');
        $this->storeCateMod = &m('storeCate');
        $this->storeMod = &m('store');
        $this->userMod = &m('user');
        $this->fxuserMoneyMod = &m('fxuserMoney');
    }

    /**
     * 申请成为分销人员
     */
    public function apply() {
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //多语言商品
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        //正在审核或者审核不通过情况
        $fxUserMod = &m('fxuser');
        $sql = "select * from bs_fx_user where   user_id=".$this->userId;
        $info = $fxUserMod->querySql($sql);
        $fx_user_code = $fxUserMod->getRow($info[0]['parent_id']);
        if($info[0]['is_check']==3){
            $info['0']['parent_code'] = $fx_user_code['fx_code'];
            $this->assign('info', $info[0]);
        }
        //语言包
        $this->load($this->shorthand, 'WeChat/Easydis');
        $this->assign('langdata', $this->langData);
        $this->assign('lang', $lang);
        $this->assign('storeid', $storeid);
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang_id);
        $this->display('fxuser/apply.html');
    }
    public function doAction()
    {
        $this->load($this->shorthand, 'WeChat/Easydis');
        $a = $this->langData;
        $fxUserMod = &m('fxuser');
        $is_have = !empty($_REQUEST['is_have']) ? htmlspecialchars(intval($_REQUEST['is_have'])) : '0';
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;
        $real_name = !empty($_REQUEST['realname']) ? $_REQUEST['realname'] : '';
        $telephone = !empty($_REQUEST['telephone']) ? $_REQUEST['telephone'] : '';
        $tj_code   = !empty($_REQUEST['fx_code']) ? $_REQUEST['fx_code'] : '';
        $bank_name = !empty($_REQUEST['bank_name']) ? $_REQUEST['bank_name'] : '';
        $bank_account = !empty($_REQUEST['bank_account']) ? $_REQUEST['bank_account'] : '';
        if (empty($real_name)){
            $this->setData(array(),'0',$a['user_Realname']);
        }
        if (empty($telephone)){
            $this->setData(array(),'0','手机号必填！');
        }
        if (!preg_match('/^1[34578]\d{9}$/', $telephone)) {
            $this->setData(array(), '0', $a['user_legitimate']);
        }
        //验证手机号的唯一性（add）
        if ($this->getPhoneInfo($telephone)) {
            $this->setData(array(), '0', $a['user_istelphone']);
        }
        if (empty($bank_name)) {
            $this->setData(array(), '0', $a['user_Openaccount']);
        }
        if (empty($bank_account)) {
            $this->setData(array(), '0', $a['user_Accounts']);
        }
        if (empty($tj_code)){
            $this->setData(array(),'0','推荐人分销码必填！');
        }
        $fx_info = $this->fxUserMod->getOne(array("cond" => "fx_code='" . $tj_code . "'"));
        if (empty($fx_info)){
            $this->setData(array(),'0','您写的分销码不存在');
        }else{
            $sql = "select * from bs_fx_user where user_id=".$this->userId;
            $info = $fxUserMod->querySql($sql);
            if (!empty($info) && $info[0]['is_check'] != 3){
                $this->setData(array(),$status='0','已存在该用户！');
            }
            $user_info = $this->userMod->getOne(array("cond" => "id=" . $this->userId));
            $store_info = $this->storeMod->getOne(array("cond" => "id=" . $storeid));
//            $store_cate = $this->storeCateMod->getOne(array("cond" => "id=" . $store_info['store_cate_id']));
            if ($fx_info['level'] == 1){
                $data = array(
                    'parent_id'    => $fx_info['id'],
                    'level'        => 2,
                    'rule_id'      => $fx_info['rule_id'],
                    'user_id'      => $this->userId,
                    'real_name'    => $real_name,
                    'phone'        => $telephone,
                    'fx_code'      => $this->unique_rand(100000,999999,1),
                    'bank_name'    => $bank_name,
                    'bank_account' => $bank_account,
                    'store_cate'   => $store_info['store_cate_id'],
                    'store_id'     => $fx_info['store_id'],
                    'status'       => 1,
                    'is_check'     => 1,
                    'source'       => 2,
                    'discount'     => 0,
                    'add_time'     => time(),
                    'add_user'     => $this->userId,
                    'mark'         => 1,
                    'email'        => $user_info['email']
                );
                if($is_have){
                    $result = $fxUserMod->doEdit($is_have,$data);
                }else{
                    $result = $fxUserMod->doInsert($data);
                }
                if ($result){
                    $this->setData(array("url" => "wx.php?app=easyDis&act=index&storeid={$storeid}&lang={$lang}&latlon={$latlon}"), $status = '1', $a['order_Success']);
                }else{
                    $this->setData(array(), $status = '0', '失败');
                }
            }elseif ($fx_info['level'] == 2){
                $data = array(
                    'parent_id'    => $fx_info['id'],
                    'level'        => 3,
                    'rule_id'      => $fx_info['rule_id'],
                    'user_id'      => $this->userId,
                    'real_name'    => $real_name,
                    'phone'        => $telephone,
                    'fx_code'      => $this->unique_rand(100000,999999,1),
                    'bank_name'    => $bank_name,
                    'bank_account' => $bank_account,
                    'store_cate'   => $store_info['store_cate_id'],
                    'store_id'     => $fx_info['store_id'],
                    'status'       => 1,
                    'is_check'     => 1,
                    'source'       => 2,
                    'discount'     => 0,
                    'add_time'     => time(),
                    'add_user'     => $this->userId,
                    'mark'         => 1,
                    'email'        => $user_info['email']
                );
                if($is_have){
                    $result = $fxUserMod->doEdit($is_have,$data);
                }else{
                    $result = $fxUserMod->doInsert($data);
                }
                if ($result){
                    $this->setData(array("url" => "wx.php?app=easyDis&act=index&storeid={$storeid}&lang={$lang}&latlon={$latlon}"), $status = '1', $a['order_Success']);
                }else{
                    $this->setData(array(), $status = '0', '失败');
                }
            }elseif ($fx_info['level'] == 3){
                $this->setData(array(),'0','您填写的分销码有误！');
            }
        }

    }

    /**
     * 生成一定数量的不重复随机数
     * @param $min 最小值
     * @param $max 最大值
     * @param $num 数量
     * @return int 随机数
     */
    public function unique_rand($min, $max, $num) {
        //初始化变量为0
        $count = 0;
        //建一个新数组
        $return = array();
        while ($count < $num) {
            //在一定范围内随机生成一个数放入数组中
            $return[] = mt_rand($min, $max);
            //去除数组中的重复值用了“翻翻法”，就是用array_flip()把数组的key和value交换两次。这种做法比用 array_unique() 快得多。
            $return = array_flip(array_flip($return));
            //将数组的数量存入变量count中
            $count = count($return);
        }
        //为数组赋予新的键名
        shuffle($return);
        return $return[0];
    }



    /**
     * @param $telphone
     * 分销手机号码是否存在
     */
    public function getPhoneInfo($telphone) {
        $fxUserMod = &m('fxuser');
        $sql = 'select  id from  bs_fx_user  where  telephone =' . $telphone . '  limit 1';
        $data = $fxUserMod->querySql($sql);
        if (empty($data[0])) {
            return null;
        } else {
            return $data[0]['id'];
        }
    }

    public function isfxUser() {
        $fxUserMod = &m('fxuser');
        $sql = 'select  id,is_check   from bs_fx_user  where   user_id = ' . $this->userId;
        $data = $fxUserMod->querySql($sql);
        if (!empty($data)) {
            return $data[0];
        } else {
            return array();
        }
    }

    /*
     * 新增分销用户关系树，余额
     * @author lee
     * @date 2017-11-21 20:29:31W
     */

    public function doFxmoney($cate_id) {
        $storeMod = &m('store');
        $sql = 'select id as store_id  from  ' . DB_PREFIX . 'store  where   store_cate_id =' . $cate_id;
        $res = $storeMod->querySql($sql);
        if (!empty($res)) {
            foreach ($res as $key => $val) {
                $arr_store[$key]['user_id'] = $this->userId;
                $arr_store[$key]['store_cate'] = $cate_id;
                $arr_store[$key]['money'] = 0.00;
                $arr_store[$key]['store_id'] = $val['store_id'];
            }
            foreach ($arr_store as $k => $v) {
                $res = $this->fxuserMoneyMod->doInsert($v);
            }
        }
    }

    public function updateUser($userid) {
        $data = array(
            'is_fx' => 1
        );
        $this->userMod->doEdit($userid, $data);
    }

    /**
     * @return int|mixed
     * 分销码生成
     */
    public function make_fxcode() {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789abcdefghijklmnopqrstuvwxyz';
        //$string=time();
        $string = rand(10000, 99999);
        for ($len = 5; $len >= 1; $len--) {
            $position = rand() % strlen($chars);
            $position2 = rand() % strlen($string);
            $string = substr_replace($string, substr($chars, $position, 1), $position2, 0);
        }
        return $string;
    }

    /*
     * 推荐分享自己的推荐码
     * @author lee
     * @date 2017-11-25 13:38:40
     * @param $fx_code 分销码
     */

    public function shareMyfxcode($fx_code) {
        if (!$fx_code) {
            $this->setData(array(), $status = 'error', $message = 'Lack of order number');
        }
        include_once ROOT_PATH . "/includes/classes/class.qrcode.php";
        $value = "http://" . SYSTEM_WEB . "/" . SYSTEM_FILE_NAME . "/wx.php?app=user&act=doFxuser&fx_code=" . $fx_code; //二维码内容
        if ($_SERVER["PHP_SELF"]) {
            $_SERVER["PHP_SELF"] = str_replace("/wx.php", "", $_SERVER["PHP_SELF"]);
        }
        $qrUrl = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER["PHP_SELF"] . "/upload/qr/" . $fx_code . ".png";
        $out_file = ROOT_PATH . "/upload/qr/" . $fx_code . ".png";
        $errorCorrectionLevel = 'L'; //容错级别
        $matrixPointSize = 6; //生成图片大小
        $path = ROOT_PATH . "/upload/qr/";
        $this->MkFolder($path);
        //生成二维码图片
        $QRcode = QRcode::png($value, $out_file, $errorCorrectionLevel, $matrixPointSize, 2);
        return $qrUrl;
    }

    /**
     * 生成路径
     * @author WQQ 2017-02-16 14:53:25
     * @param $path
     */
    public function MkFolder($path) {
        if (!is_readable($path)) {
            $this->MkFolder(dirname($path));
            if (!is_file($path))
                mkdir($path, 0777);
        }
    }

}
