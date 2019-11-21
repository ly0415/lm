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

class FxupgradeApp extends BaseStoreApp {

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

    public function index() {
        $land_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : $this->lang_id;
        $this->assign('land_id', $land_id);
        $store_id = $_REQUEST['store_id']?$_REQUEST['store_id']:$this->storeId;
        $this->assign('store_id', $store_id);
        //获取一级分销
        $sql1 = ' SELECT  t.*,u.real_name,u.`bank_name`,u.`bank_account`,u.`email`,u.`telephone`,u.`add_time`,u.`freeze`,u.`id` as uid
                  FROM  bs_fx_usertree AS t LEFT JOIN bs_fx_user AS u ON t.`user_id` = u.`user_id`
                  WHERE  t.`fx_level` = 1  AND  t.pid=0  and is_check =2  AND  u.`store_id`  = ' . $store_id;
        $res = $this->fxuserTreeMod->querySql($sql1);
        foreach ($res as $key => $val) {
            $lev2 = $this->getlev2($val['id']);
            if (!empty($lev2)) {
                // 2 级
                $res[$key]['childs'] = $lev2;
                // 3级
                foreach ($lev2 as $k => $v) {
                    $lev3 = $this->getlev3($v['id']);
                    if (!empty($lev3)) {
                        $res[$key]['childs'][$k]['childs'] = $lev3;
                    }
                }
            }
        }
        if($this->storeInfo['store_type'] == 1){
            //获取店铺名称
            $storeMod = &m('store');
            $store_data = $storeMod->getStoreArr($this->storeInfo['store_cate_id']);
            $this->assign('store_data', $store_data);
            $this->assign('store_all', "all");
        }
        $this->assign('res', $res);
        $this->display('fxupgrade/memberList.html');
    }

    public function getlev2($pid) {
        $sql1 = ' SELECT  t.*,u.real_name,u.`bank_name`,u.`bank_account`,u.`email`,u.`add_time`,u.`telephone`,u.`freeze`,u.`id` as uid   FROM  bs_fx_usertree AS t LEFT JOIN bs_fx_user AS u ON t.`user_id` = u.`user_id`
                  WHERE  t.`fx_level` = 2  AND  t.pid= ' . $pid;
        $res = $this->fxuserTreeMod->querySql($sql1);
        return $res;
    }

    public function getlev3($pid) {
        $sql1 = ' SELECT  t.*,u.real_name,u.`bank_name`,u.`bank_account`,u.`email`,u.`add_time`,u.`telephone`,u.`freeze`,u.`id` as uid   FROM  bs_fx_usertree AS t LEFT JOIN bs_fx_user AS u ON t.`user_id` = u.`user_id`
                  WHERE  t.`fx_level` = 3  AND  t.pid= ' . $pid;
        $res = $this->fxuserTreeMod->querySql($sql1);
        return $res;
    }

    /**
     * 一级人员的替换
     */
    public function replace() {
        $treeid = $_REQUEST['treeid'];
        $store_id = $_REQUEST['store_id'];
        //1.无下级的一级分销人员 和 正常的用户
        $sql = 'SELECT t.*,u.real_name,u.telephone  FROM bs_fx_usertree  AS t LEFT  JOIN bs_fx_user AS u  ON t.`user_id` = u.`user_id`
                WHERE  u.`freeze`  = 1 AND t.`fx_level` = 1   and  u.store_id=' . $store_id;
        $lev1 = $this->fxuserTreeMod->querySql($sql);
        foreach ($lev1 as $key => $val) {
            $childs = $this->getchilds($val['id']);
            if (!empty($childs)) {
                unset($lev1[$key]);
            }
        }
        //去重复
        foreach ($lev1 as $k => $v) {
            if ($v['id'] == $treeid) {
                unset($lev1[$k]);
            }
        }
        $land_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : $this->lang_id;
        $this->assign('land_id', $land_id);
        $this->assign('treeid', $treeid);
        $this->assign('lev1', $lev1);
        $this->assign('act', 'index');
        $this->display('fxupgrade/replace.html');
    }

    public function getchilds($pid) {
        $sql = 'select * from bs_fx_usertree where  pid =' . $pid;
        $res = $this->fxuserTreeMod->querySql($sql);
        return $res;
    }

    public function doreplace() {
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : 0;
        $this->load($lang_id, 'store/store');
        $a = $this->langData;
        $treeid = $_REQUEST['treeid']; //old
        $new_treeid = $_REQUEST['new_treeid'];
        if (empty($new_treeid)) {
            $this->setData(array(), '0', $a['level_Required']);
        }
        //老数据
        $sql = ' SELECT  * FROM  bs_fx_usertree   WHERE id=' . $treeid;
        $oldInfo = $this->fxuserTreeMod->querySql($sql);
        //新数据
        $sql2 = ' SELECT  * FROM  bs_fx_usertree   WHERE id=' . $new_treeid;
        $newInfo = $this->fxuserTreeMod->querySql($sql2);
        //1.替换人的树节点 和 被替换人 互换
        $data_new = array(
            'user_id' => $newInfo[0]['user_id']
        );
        $this->fxuserTreeMod->doEdit($treeid, $data_new);
        //
        $data_old = array(
            'user_id' => $oldInfo[0]['user_id']
        );
        $this->fxuserTreeMod->doEdit($new_treeid, $data_old);
        //2.冻结老数据中 userid 账户
        $data_u = array(
            'freeze' => 2,
            'key' => 'user_id'
        );
        $res = $this->fxuserMod->doEdit($oldInfo[0]['user_id'], $data_u);

        if ($res) {
            $info['url'] = "store.php?app=fxupgrade&act=index&lang_id={$lang_id}";
            $this->setData($info, '1', $a['level_Success']);
        } else {
            $this->setData(array(), '0', $a['level_fail']);
        }
    }

    /**
     * 二级升一级
     */
    public function levOne() {
        $land_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : $this->lang_id;
        $this->assign('land_id', $land_id);
        $treeid = $_REQUEST['treeid'];
        //老数据
        $sql = ' SELECT  * FROM  bs_fx_usertree   WHERE id=' . $treeid;
        $oldInfo = $this->fxuserTreeMod->querySql($sql);
        //本组的三级分销人员
        $sql2 = 'SELECT t.*,u.real_name,u.telephone  FROM  bs_fx_usertree  AS t LEFT  JOIN bs_fx_user AS u  ON t.`user_id` = u.`user_id`
                 WHERE pidpid =' . $oldInfo[0]['pid'];
        $lev3 = $this->fxuserTreeMod->querySql($sql2);

        if (!empty($lev3)) {
            $flag = 1;
        } else {
            $flag = 0;
        }
        //分销规则
        $sql_rule = "SELECT  *  FROM  bs_fx_rule  WHERE  mark = 1 AND  (FIND_IN_SET('" . $this->storeId . "',store_id)  OR store_id =0)";
        $res_rule = $this->fxruleMod->querySql($sql_rule);
        $this->assign('res_rule', $res_rule);

        $this->assign('act', 'index');
        $this->assign('lev3', $lev3);
        $this->assign('flag', $flag);
        $this->assign('treeid', $treeid);
        $this->display('fxupgrade/levone.html');
    }

    public function dolevOne() {
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : 0;
        $this->load($lang_id, 'store/store');
        $a = $this->langData;
        $treeid = $_REQUEST['treeid'];
        $flag = $_REQUEST['flag'];
        $fx_rule = !empty($_REQUEST['fx_rule']) ? $_REQUEST['fx_rule'] : 0;
        $rep_lev3 = !empty($_REQUEST['rep_lev3']) ? $_REQUEST['rep_lev3'] : 0;  //替换的treeid
        //
        $sql = ' SELECT  * FROM  bs_fx_usertree   WHERE id=' . $treeid;
        $oldInfo = $this->fxuserTreeMod->querySql($sql);

        if (empty($fx_rule)) {
            $this->setData(array(), '0', $a['level_rule']);
        }
        // 2中情况
        if (!empty($flag)) {  //这个组有 三级分销人员
            if (empty($rep_lev3)) {
                $this->setData(array(), '0', $a['level_Required']);
            }
            //替换数据
            $sql = ' SELECT  * FROM  bs_fx_usertree   WHERE id=' . $rep_lev3;
            $newInfo = $this->fxuserTreeMod->querySql($sql);
            //3级先替换2级的位置
            $data_p = array(
                'user_id' => $newInfo[0]['user_id']
            );
            $this->fxuserTreeMod->doEdit($treeid, $data_p);
            // 删除3级的节点
            $where = ' id =' . $rep_lev3;
            $this->fxuserTreeMod->doDrops($where);
            // 2级升到1级 添加一条
            $data_t = array(
                'user_id' => $oldInfo[0]['user_id'],
                'fx_level' => 1,
                'pid' => 0,
                'pidpid' => 0
            );
            $this->fxuserTreeMod->doInsert($data_t);
        } else {  //没有三级分销人员
            //2级升到1级 编辑
            $data_t = array(
                'fx_level' => 1,
                'pid' => 0,
                'pidpid' => 0
            );
            $this->fxuserTreeMod->doEdit($treeid, $data_t);
        }

        //4.向 user_rule 变 插入数据
        $data_r = array(
            'user_id' => $oldInfo[0]['user_id'],
            'rule_id' => $fx_rule
        );

        $res = $this->fxuserRuleMod->doInsert($data_r);

        if ($res) {
            $info['url'] = "store.php?app=fxupgrade&act=index&lang_id={$lang_id}";
            $this->setData($info, '1', $a['level_Success']);
        } else {
            $this->setData(array(), '0', $a['level_fail']);
        }
    }

    /**
     * 三级升二级
     */
    public function upTwo() {
        $land_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : $this->lang_id;
        $this->assign('land_id', $land_id);
        $treeid = $_REQUEST['treeid'];
        $sql = ' SELECT  * FROM  bs_fx_usertree   WHERE id=' . $treeid;
        $oldInfo = $this->fxuserTreeMod->querySql($sql);
        $pidpid = $oldInfo[0]['pidpid'];
        //改变后的 上级
        $sql2 = 'SELECT  t.id,u.`real_name`  FROM  bs_fx_usertree  AS t
                  LEFT JOIN  bs_fx_user AS u ON t.`user_id` = u.`user_id`  WHERE t.id= ' . $pidpid;
        $newInfo = $this->fxuserTreeMod->querySql($sql2);
        //
        $this->assign('act', 'index');
        $this->assign('treeid', $treeid);
        $this->assign('newInfo', $newInfo[0]);
        $this->display('fxupgrade/uptwo.html');
    }

    public function douptwo() {
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : 0;
        $this->load($lang_id, 'store/store');
        $a = $this->langData;
        $treeid = $_REQUEST['treeid'];
        $pid = $_REQUEST['pid'];
        $data = array(
            'fx_level' => 2,
            'pid' => $pid,
            'pidpid' => 0
        );
        $res = $this->fxuserTreeMod->doEdit($treeid, $data);
        if ($res) {
            $info['url'] = "store.php?app=fxupgrade&act=index&lang_id={$this->lang_id}";
            $this->setData($info, '1', $a['level_Success']);
        } else {
            $this->setData(array(), '0', $a['level_fail']);
        }
    }

    /**
     * 三级升一级
     */
    public function upOne() {
        $land_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : $this->lang_id;
        $this->assign('land_id', $land_id);
        $store_id = $_REQUEST['store_id']?$_REQUEST['store_id']:$this->storeId;
        $treeid = $_REQUEST['treeid'];
        //分销规则
        $sql_rule = "SELECT  *  FROM  bs_fx_rule  WHERE  mark = 1 AND  (FIND_IN_SET('" . $store_id . "',store_id)  OR store_id =0)";
        $res_rule = $this->fxruleMod->querySql($sql_rule);
        $this->assign('res_rule', $res_rule);

        $this->assign('act', 'index');
        $this->assign('treeid', $treeid);
        $this->display('fxupgrade/upone.html');
    }

    public function doupone() {
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : 0;
        $this->load($lang_id, 'store/store');
        $a = $this->langData;
        $treeid = $_REQUEST['treeid'];
        $fx_rule = $_REQUEST['fx_rule'];
        //
        if (empty($fx_rule)) {
            $this->setData(array(), '0', $a['level_rule']);
        }
        //老数据
        $sql = ' SELECT  *  FROM  bs_fx_usertree   WHERE id=' . $treeid;
        $oldInfo = $this->fxuserTreeMod->querySql($sql);
        $userid = $oldInfo[0]['user_id'];
        //先该数据 usertree
        $data_t = array(
            'fx_level' => 1,
            'pid' => 0,
            'pidpid' => 0
        );
        $res = $this->fxuserTreeMod->doEdit($treeid, $data_t);
        //加 user_rule 数据
        if ($res) {
            $data_r = array(
                'user_id' => $userid,
                'rule_id' => $fx_rule
            );
            $res = $this->fxuserRuleMod->doInsert($data_r);

            if ($res) {
                $info['url'] = "store.php?app=fxupgrade&act=index&lang_id={$lang_id}";
                $this->setData($info, '1', $a['level_Success']);
            } else {
                $this->setData(array(), '0', $a['level_fail']);
            }
        } else {
            $this->setData(array(), '0', $a['level_fail']);
        }
    }

}
