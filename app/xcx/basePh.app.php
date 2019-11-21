<?php
/**
 * 小程序接口开发
 * @author  luffy
 * @date    2018-08-14
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}
class BasePhApp extends BaseApp{

    public $storeMod, $store_id, $lang_id, $shorthand, $langData,$latlon;
    public $symbol,$countryId;
    public $appId = 'wxb9ad06ed40bf2fda';
    public $appSecret = 'e8fbc6522ea3a1038a10cb6cbed3ba91';
    public $userId, $user_id_bank;
    public $pagesize=15;
    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct();

        if (strpos($_SERVER['HTTP_HOST'], 'njbsds')) {
            $this->appId = 'wx80d07d72079c04db';
            $this->appSecret = 'cb06dfe09354ada01688cea7173b3c45';
        }

        $this->storeMod = &m('store');
        //获取当前站点信息
        $current_store_info     = $this->storeMod->getOpenStoreArr();
        if( empty($current_store_info) ){
            //尚未开通站点的情况不考虑
        }
        //接受前台数据
        $this->store_id  = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $current_store_info['id'];       //所选的站点id
        $this->lang_id   = !empty($_REQUEST['lang_id'])  ? $_REQUEST['lang_id']  : $current_store_info['lang_id'];  //所选站点的语言id

        //获取语言简写
        $languageSql    = 'select id,shorthand from ' . DB_PREFIX . 'language where id = ' . $this->lang_id;
        $language_info  = $this->storeMod->querySql($languageSql);
        $this->shorthand= $language_info[0]['shorthand'];

        //获取语言包
        $this->langData = languageFun($this->shorthand);

        // 获取币种符号
        $syData = $this->getStoreSymbol($this->store_id);
        $this->symbol = $syData['symbol'];   // 符号

        //获取国家信息
        $curCountry = $this->getCurCountry($this->store_id);
        $this->countryId = $curCountry['cid'];
        if (SYSTEM_WEB == "www.njbsds.cn"){
            $this->userId = $_REQUEST['user_id'] ? $_REQUEST['user_id'] : 434;
        }else{
            //获取user_id
//            $this->userId = $_REQUEST['user_id'] ? $_REQUEST['user_id'] : $this->user_id_bank;
            $this->userId = $_REQUEST['user_id'] ? $_REQUEST['user_id'] : '';
        }

    }

    /**
     * 数据封装
     * @author  luffy
     * @date    2018-08-14
     * @param $status 表示返回数据状态
     * @param $message 对返回状态说明
     * @param $info 返回数据信息
     */
    public function setData($info = array(), $status = 'success', $message = 'ok'){
        $data = array(
            'status' => $status,
            'message' => $message,
            'info' => $info,
        );
        echo json_encode($data);
        exit();
    }

    //获取币种
    public function getStoreSymbol($storeid) {
        $storeMod = &m('store');
        $sql = 'SELECT  c.`symbol`,c.`short`   FROM    ' . DB_PREFIX . 'store AS s LEFT JOIN  ' . DB_PREFIX . 'currency AS c ON s.`currency_id` = c.`id`  WHERE  s.`id` = ' . $storeid;
        $res = $storeMod->querySql($sql);
        return $res[0];
    }

    /** 所选的国家的信息
     * @param $storeid
     */
    public function getCurCountry($storeid) {
        $storeMod = &m('store');
        $sql = 'SELECT s.id,c.`id` AS cid,c.`cate_name`,l.cate_name as lcatename  FROM  ' . DB_PREFIX . 'store AS s
                 LEFT JOIN  ' . DB_PREFIX . 'store_cate AS c ON s.`store_cate_id` = c.`id`  left join bs_store_cate_lang  as l on c.id=l.cate_id
                  WHERE s.id =' . $storeid . '  and  l.lang_id =' . $this->lang_id;
        $data = $storeMod->querySql($sql);
        return $data[0];
    }


    /**
     * 获取openid
     * @author  luffy
     * @date    2018-08-23
     */
    public function getOpenid(){
        $code=$_REQUEST['code'];
        $url="https://api.weixin.qq.com/sns/jscode2session?appid=".$this->appId."&secret=".$this->appSecret."&js_code=JSCODE&grant_type=authorization_code";

    }
    /**
     * wangh
     * 加载语言包文件
     * @param $langid
     * @param $filename  如: default/index
     * @return array
     */
    public function load($shorthand, $filename) {

        $_ = array(); //语言包数组

        if ($shorthand == 'ZH') {
            $folders = 'chinese';
        } else if ($shorthand == 'EN') {
            $folders = 'english';
        } else {
            $folders = 'english';
        }

        $file = ROOT_PATH . '/languages/' . $folders . '/' . $filename . '.php';

        if (is_file($file)) {
            require($file);
        } else {
            return array();
        }

        $this->langData = array_merge($this->langData, $_);
    }

    /**
     * 生成不重复的四位随机数
     * @author wanyan
     * @date 2017-10-23
     */
    public function buildNo($limit) {
        $begin = pow(10, 3);
        $end = (pow(10, 4) - 1);
        $rand_array = range($begin, $end);
        shuffle($rand_array); //调用现成的数组随机排列函数
        return array_slice($rand_array, 0, $limit); //截取前$limit个
    }
}
?>
