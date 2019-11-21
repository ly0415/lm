<?php

/**
 * Created by PhpStorm.
 * User: wangh
 * Date: 2017/11/16
 * Time: 15:44
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class FxcashApp extends BaseStoreApp {

    private $lang_id;
    private $fxuserMod;
    private $fxuserMoneyMod;
    private $fxOutmoneyLogMod;
    private $fxOutmoneyApplyMod;
    private $storeMod;

//    private  $fxuserRuleMod;
//    private  $fxuserTreeMod;
//    private  $userMod;
//    private  $fxruleMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
        $this->assign('lang_id', $this->lang_id);

        $this->fxuserMod = &m('fxuser');
        $this->fxuserMoneyMod = &m('fxuserMoney');
        $this->fxOutmoneyApplyMod = &m('fxOutmoneyApply');
        $this->fxOutmoneyLogMod = &m('fxOutmoneyLog');
        $this->storeMod = &m('store');

        //$this -> fxuserRuleMod = &m('fxuserRule');
        // $this -> fxuserTreeMod = &m('fxuserTree');
        //$this -> fxruleMod = &m('fxrule');
        //$this -> userMod=&m('user');
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 提现列表
     */
    public function cashlist() {
        $land_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : 0;
        $source = $_REQUEST['source'] ? $_REQUEST['source'] : 0;
        //账号来源
        $fxOutmoneyApplyMod = &m('fxOutmoneyApply');
        $this->assign('sourceList', $fxOutmoneyApplyMod->source);
        $this->assign('lang_id', $land_id);
        if($this->storeInfo['store_type'] == 1 ){
            $ids = $this->getStoreIds($this->storeId);
        }else{
            $ids = $this->storeId;
        }
        $real_name = !empty($_REQUEST['real_name']) ? htmlspecialchars(trim($_REQUEST['real_name'])) : '';
        $status = !empty($_REQUEST['status']) ? $_REQUEST['status'] : 0;
        $where = '    WHERE  u.`freeze` = 1  AND u.`is_check` = 2   AND  a.`store_id`in(' . $ids.')';
        if (!empty($status)) {
            $where .= '   AND  a.`status` = ' . $status;
        }
        if (!empty($real_name)) {
            $where .= '   and   u.`real_name`  like  "%' . $real_name . '%"';
        }
        if ($source) {
            $where .= " and a.source =".$source;
            $this->assign('source', $source);
        }
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "fx_outmoney_apply " . $where;
        $totalCount = $this->fxOutmoneyApplyMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;

        $sql = ' SELECT  a.*,u.`bank_name`,u.`bank_account`,u.`telephone`,u.`real_name`,su.`username`  FROM   bs_fx_outmoney_apply AS a
                LEFT  JOIN  bs_fx_user AS u ON a.`user_id` = u.`user_id`
                LEFT  JOIN  bs_store_user AS su  ON  a.`store_user_id` = su.id ' . $where . '  order by a.id desc';

        $data = $this->fxOutmoneyApplyMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($data['list'] as $k => $v) {
            $data['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        $this->assign('p', $p);
        $this->assign('res', $data['list']);
        $this->assign('page_html', $data['ph']);
        $this->assign('status', $status);
        $this->assign('real_name', $real_name);
        $this->display('fxcash/cashList.html');
    }

    public function cash() {
        $land_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : 0;
        $store_id  = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : $this->storeId;
        $this->assign('lang_id', $land_id);
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        if (!empty($id)) {
            $where = '   WHERE  a.`id` = ' . $id . '  AND m.`store_id` = ' . $store_id;
        } else {
            return false;
        }
        $sql = ' SELECT  a.*,u.`bank_name`,u.`bank_account`,u.`telephone`,u.`real_name`,m.`money`,m.id as mid   FROM   bs_fx_outmoney_apply AS a
                LEFT  JOIN  bs_fx_user AS u ON a.`user_id` = u.`user_id`
                LEFT  JOIN   bs_fx_user_money  AS m ON a.`user_id` = m.`user_id`' . $where;

        $data = $this->fxOutmoneyApplyMod->querySql($sql);
        $data[0]['method'] = 2;
        $this->assign('p', $p);
        $this->assign('store_id', $store_id);
        $this->assign('data', $data[0]);
        $this->assign('act', 'cashlist');
        $this->display('fxcash/cash.html');
    }

    /**
     * 处理提现
     */
    public function doCash() {
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : 0;
        $this->load($lang_id, 'store/store');
        $a = $this->langData;
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : 0;
        $mid = !empty($_REQUEST['mid']) ? $_REQUEST['mid'] : 0;
        $user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;
        $apply_money = !empty($_REQUEST['apply_money']) ? $_REQUEST['apply_money'] : 0;
        $money = !empty($_REQUEST['money']) ? $_REQUEST['money'] : 0;
//        $real_name = !empty($_REQUEST['real_name']) ? htmlspecialchars(trim($_REQUEST['real_name'])) : '';
//        $telephone = !empty($_REQUEST['telephone']) ? trim($_REQUEST['telephone']) : '';
        $bank_name = !empty($_REQUEST['bank_name']) ? htmlspecialchars(trim($_REQUEST['bank_name'])) : '';
        $bank_account = !empty($_REQUEST['bank_account']) ? trim($_REQUEST['bank_account']) : '';
        $method = !empty($_REQUEST['method']) ? trim($_REQUEST['method']) : '';
        $store_id = !empty($_REQUEST['store_id']) ? trim($_REQUEST['store_id']) : $this->storeId;

        //金额判断
        $sql = ' select  * from  bs_fx_user_money where user_id =' . $user_id . '  and  store_id =' . $store_id;
        $data = $this->fxuserMoneyMod->querySql($sql);
        $accountMoney = $data[0]['money'];
        if ($accountMoney < $apply_money) {
            //改变状态
            // $this -> notCash($id);
            $info['url'] = "store.php?app=fxcash&act=cashlist&lang_id={$this->lang_id}";
            $this->setData($info, '1', $a['Withdrawals_insufficient']);
            return false;
        }

        if ($method == 1) {
            $userOpen = $this->fxOutmoneyApplyMod->getOne(array('cond' => "`id`='{$id}'", 'fields' => "open_id,apply_money"));
            $open_id = $userOpen['open_id'];
            $applyMoney = $userOpen['apply_money'];
            $randNo = $this->buildNo(1);
            $wx_res = $this->onLinePay($open_id, $this->storeId, $randNo, $applyMoney);
            if ($wx_res['return_code'] == 'SUCCESS' && $wx_res['result_code'] == 'SUCCESS') {
                // 编辑 bs_fx_outmoney_apply
                $data_a = array(
                    'status' => 2,
                    'store_user_id' => $this->storeUserId
                );
                $res = $this->fxOutmoneyApplyMod->doEdit($id, $data_a);
                // 添加  bs_fx_outmoney_log
                $data_l = array(
                    'user_id' => $user_id,
                    'out_money' => $apply_money,
                    'bank_name' => '0',
                    'bank_account' => '0',
                    'order_sn' => $wx_res['partner_trade_no'],
                    'payment_no' => $wx_res['payment_no'],
                    'store_user_id' => $this->storeUserId,
                    'store_id' => $this->storeId,
                    'open_id' => $open_id,
                    'status' => 1,
                    'source' => 2,
                    'add_time' => strtotime($wx_res['payment_time'])
                );
                $res2 = $this->fxOutmoneyLogMod->doInsert($data_l);
                // 编辑 bs_fx_user_money
                $data_m = array(
                    'id' => $mid,
                    'money' => $money - $apply_money
                );
                $res3 = $this->fxuserMoneyMod->doEdit($mid, $data_m);
            }
        } else {
            // 编辑 bs_fx_outmoney_apply
            $data_a = array(
                'status' => 2,
                'store_user_id' => $this->storeUserId
            );
            $res = $this->fxOutmoneyApplyMod->doEdit($id, $data_a);
            // 添加  bs_fx_outmoney_log
            $data_l = array(
                'user_id' => $user_id,
                'out_money' => $apply_money,
                'bank_name' => $bank_name,
                'bank_account' => $bank_account,
                'store_user_id' => $this->storeUserId,
                'store_id' => $store_id,
                'source' => 1,
                'add_time' => time()
            );
            $res2 = $this->fxOutmoneyLogMod->doInsert($data_l);
            // 编辑 bs_fx_user_money
            $data_m = array(
                'id' => $mid,
                'money' => $money - $apply_money
            );
            $res3 = $this->fxuserMoneyMod->doEdit($mid, $data_m);
        }
        if ($res && $res2 && $res3) {
            $info['url'] = "store.php?app=fxcash&act=cashlist&lang_id={$lang_id}&p={$p}";
            $this->setData($info, '1', $a['Withdrawals_Success']);
        } else {
            $this->setData(array(), '0', $a['Withdrawals_fail']);
        }
    }

    /**
     * 企业付款到零钱接口
     *
     */
    public function onLinePay($open_id, $store_id, $randNo, $applyMoney) {
        $wxPayInfo = $this->getWxInfo($store_id);
        //结算
        $data = array(
            'mch_appid' => trim($wxPayInfo['weixin_APPID']), //商户账号appid
            'mchid' => trim($wxPayInfo['weixin_account']), //商户号
            'nonce_str' => $this->getNonceStr(32), //随机字符串
            'partner_trade_no' => date('YmdHis') . $randNo[0], //商户订单号
            'openid' => $open_id, //用户openid
            'check_name' => 'NO_CHECK', //校验用户姓名选项,
//            're_user_name' => '蒲松林',//收款用户姓名
            'amount' => $applyMoney * 100, //金额
            'desc' => '企业付款到零钱测试1', //企业付款描述信息
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR']//Ip地址
        );
        $secrect_key = trim($wxPayInfo['weixin_KEY']); ///这个就是个API密码。32位的。。随便MD5一下就可以了
        $data = array_filter($data);
        ksort($data);
        $str = '';
        foreach ($data as $k => $v) {
            $str .= $k . '=' . $v . '&';
        }
        $str .= 'key=' . $secrect_key;
        $data['sign'] = strtoupper(md5($str));
        $xml = $this->arraytoxml($data);
        // echo $xml;
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        $res = $this->curl($xml, $url);
        $result = $this->xmltoarray($res);
        return $result;
    }

    /**
     *  获取微信配置的参数
     */
    public function getWxInfo($store_id) {
        $Tstore = '';
        $wxPay = array();
        $rs = $this->storeMod->getOne(array('cond' => "`id` = '{$store_id}'", 'fields' => "store_type,store_cate_id"));
        if ($rs['store_type'] == 1) {
            $Tstore = $store_id;
        } else {
            $info = $this->storeMod->getOne(array('cond' => "`store_cate_id` = '{$rs['store_cate_id']}' and `store_type` = 1", 'fields' => "id"));
            $Tstore = $info['id'];
        }

        $sql = "select pd.mkey,pd.key_name from " . DB_PREFIX . "pay as p left join " . DB_PREFIX . "pay_detail as pd on p.id = pd.pay_id where p.store_id = '{$Tstore}' and p.`code` = 'weixin' and p.is_use =1";

        $payInfo = $this->storeMod->querySql($sql);
        foreach ($payInfo as $k => $v) {
            $wxPay[$v['mkey']] = $v['key_name'];
        }
        return $wxPay;
    }

    public function arraytoxml($data) {
        $str = '<xml>';
        foreach ($data as $k => $v) {
            $str .= '<' . $k . '>' . $v . '</' . $k . '>';
        }
        $str .= '</xml>';
        return $str;
    }

    public function xmltoarray($xml) {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring), true);
        return $val;
    }

    public function curl($param = "", $url) {

        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();                                      //初始化curl
        curl_setopt($ch, CURLOPT_URL, $postUrl);                 //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);                    //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);            //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);                      //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);           // 增加 HTTP Header（头）里的字段
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);        // 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSLCERT, ROOT_PATH . '/includes/libraries/WxPaysdk/cert/apiclient_cert.pem'); //这个是证书的位置绝对路径
        curl_setopt($ch, CURLOPT_SSLKEY, ROOT_PATH . '/includes/libraries/WxPaysdk/cert/apiclient_key.pem'); //这个也是证书的位置绝对路径
        $data = curl_exec($ch);                                 //运行curl
        curl_close($ch);
        return $data;
    }

    /**
     *
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return 产生的随机字符串
     */
    public function getNonceStr($length = 32) {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 拒绝提现
     */
    public function notCash($id) {
        $data = array(
            'status' => 3,
            'store_user_id' => $this->storeUserId
        );
        $this->fxOutmoneyApplyMod->doEdit($id, $data);
    }

    /**
     * 拒绝提现
     */
    public function ajaxNotCash() {
        $id = $_REQUEST['id'];
        $data = array(
            'status' => 3,
            'store_user_id' => $this->storeUserId
        );
        $res = $this->fxOutmoneyApplyMod->doEdit($id, $data);

        if ($res) {
            echo json_encode(array('status' => 1));
        } else {
            echo json_encode(array('status' => 0));
        }
    }

}
