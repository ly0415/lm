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

class FxLevApp extends BaseStoreApp {

    private $lang_id;
    private $fxuserMod;
    private $fxruleMod;
    private $fxuserMoneyMod;
    private $fxuserRuleMod;
    private $fxuserTreeMod;
    private $userMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
        $this->assign('lang_id', $this->lang_id);
        $this->fxuserMod = &m('fxuser');
        $this->fxuserMoneyMod = &m('fxuserMoney');
        $this->fxuserRuleMod = &m('fxuserRule');
        $this->fxuserTreeMod = &m('fxuserTree');
        $this->fxruleMod = &m('fxrule');
        $this->userMod = &m('user');
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    public function checkList() {
        $real_name = !empty($_REQUEST['real_name']) ? htmlspecialchars(trim($_REQUEST['real_name'])) : '';
        $ischeck = !empty($_REQUEST['ischeck']) ? $_REQUEST['ischeck'] : 0;
        $land_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : $this->lang_id;
        $where = '  where  source = 2   AND  store_id = ' . $this->storeId;
        if (!empty($real_name)) {
            $where .= '  and  real_name  like "%' . $real_name . '%"';
        }
        if (!empty($ischeck)) {
            $where .= '  and  is_check = ' . $ischeck;
        } else {
            $where .= '  AND ( is_check = 1  OR  is_check = 3 ) ';
        }
// 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "bs_fx_user " . $where;
        $totalCount = $this->fxuserMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;

        $sql = 'SELECT  *  FROM  bs_fx_user ' . $where . '  order by id desc';
        $res = $this->fxuserMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($res['list'] as $k => $v) {
            if ($v['add_time']) {
                $res['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            } else {
                $res['list'][$k]['add_time'] = '';
            }
            $res['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        if($this->storeInfo['store_type'] == 1){
            //获取店铺名称
            $storeMod = &m('store');
            $store_data = $storeMod->getStoreArr($this->storeInfo['store_cate_id']);
            $this->assign('store_data', $store_data);
            $this->assign('is_all', "all");
        }
        $this->assign('p', $p);
        $this->assign('res', $res['list']);
        $this->assign('land_id', $land_id);
        $this->assign('page_html', $res['ph']);
        $this->assign('ischeck', $ischeck);
        $this->assign('real_name', $real_name);
        $this->display('fxLev/checkList.html');
    }

    /**
     * 通过
     */
    public function pass() {
        $id = $_REQUEST['id'];
        $land_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : $this->lang_id;
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        //分销规则
        $sql_rule = "SELECT  *  FROM  bs_fx_rule  WHERE  mark = 1 AND  (FIND_IN_SET('" . $this->storeId . "',store_id)  OR store_id =0)";
        $res_rule = $this->fxruleMod->querySql($sql_rule);
        $this->assign('res_rule', $res_rule);
        $this->assign('id', $id);
        $this->assign('p', $p);
        $this->assign('land_id', $land_id);
        $this->assign('act', 'checkList');
        $this->display('fxLev/pass.html');
    }

    public function dopass() {
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : 0;
        $this->load($lang_id, 'store/store');
        $a = $this->langData;
        $id = $_REQUEST['id'];
        $fx_rule = $_REQUEST['fx_rule'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        if (empty($fx_rule)) {
            $this->setData(array(), '0', $a['Toexamine_rule']);
        }
        $sql = 'select  user_id  from bs_fx_user where id=' . $id;
        $data = $this->fxuserMod->querySql($sql);
        $user_id = $data[0]['user_id'];

        // 2.fx_usertree 插入数据
        $data_usertree = array(
            'user_id' => $user_id,
            'fx_level' => 1,
            'pid' => 0,
            'pidpid' => 0
        );
        $insert_utreeid = $this->fxuserTreeMod->doInsert($data_usertree);

        //3. fx_user_rule 插入数据
        $data_userrule = array(
            'user_id' => $user_id,
            'rule_id' => $fx_rule
        );
        $insert_res = $this->fxuserRuleMod->doInsert($data_userrule);

        // 4.fx_uer_money 插入数据
        $arr_store = $this->getstores();
        if (!empty($arr_store)) {
            foreach ($arr_store as $key => $val) {
                $arr_store[$key]['user_id'] = $user_id;
                $arr_store[$key]['store_cate'] = $this->country_id;
                $arr_store[$key]['money'] = 0.00;
            }
        }
        foreach ($arr_store as $k => $v) {
            $res = $this->fxuserMoneyMod->doInsert($v);
        }

        //5.更新user表
        $data_user = array(
            'is_fx' => 1
        );
        $res2 = $this->userMod->doEdit($user_id, $data_user);

        //6.更新 fx_user 表
        $data_fxu = array('is_check' => 2);
        $res = $this->fxuserMod->doEdit($id, $data_fxu);

        if ($res && $res2) {
            $info['url'] = "store.php?app=fxLev&act=checkList&lang_id={$lang_id}&p={$p}";
            $this->setData($info, '1', $a['Toexamine_Success']);
        } else {
            $this->setData(array(), '0', $a['Toexamine_fail']);
        }
    }

    public function getstores() {
        $storeMod = &m('store');
        $sql = 'select id as store_id  from  bs_store  where   store_cate_id =' . $this->country_id;
        $res = $storeMod->querySql($sql);
        return $res;
    }

    /**
     * 审核未通过
     */
    public function ajaxNotPass() {

        $id = $_REQUEST['id'];
        $data = array('is_check' => 3);
        $res = $this->fxuserMod->doEdit($id, $data);

        if ($res) {
            echo json_encode(array('status' => 1));
        } else {
            echo json_encode(array('status' => 0));
        }
    }

}
