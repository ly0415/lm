<?php

/**
 * 我的分销团队
 * @author wanyan
 * @date 2017/11/27
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class MyFxTeamApp extends BaseFrontApp {

    private $fxRevenueLogMod;
    private $fxUserMod;
    private $fxUserTreeMod;
    private $fxuserRuleMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->fxRevenueLogMod = &m('fxRevenueLog');
        $this->fxUserMod = &m('fxuser');
        $this->fxUserTreeMod = &m('fxuserTree');
        $this->fxuserRuleMod = &m('fxuserRule');
    }

    /**
     * 我的分销团队
     * @author wanyan
     * @date 2017/11/27
     */
    public function index() {
        $arr =array();
        $this->assign('storeid', $this->storeid);
        $this->assign('langid', $this->langid);
        $this->load($this->shorthand, 'myFxTeam/myFxTeam');
        $this->assign('langdata', $this->langData);
        $sql = "select fur.fx_level,fur.id,fur.user_id,fur.pid,fur.pidpid from `bs_fx_usertree` AS fur LEFT JOIN
        bs_fx_user as fu ON fur.user_id = fu.user_id WHERE fur.user_id=$this->userId";
       // var_dump($sql);die;
        $info = $this->fxUserTreeMod->querySql($sql);
        $sql_1 = "select fur.fx_level,fur.id,fur.user_id,fur.pid from `bs_fx_usertree` AS fur LEFT JOIN
        bs_fx_user as fu ON fur.user_id = fu.user_id";
        $rs = $this->fxUserTreeMod->querySql($sql_1);
        if ($info[0]['fx_level'] == 1) {
            $tree = $this->getTree($rs, $info[0]['id']);
            $info[0]['child'] = $tree;
            $arr = $this->pingArray($info);
            $flag = 1;
        } elseif ($info[0]['fx_level'] == 2) {
            // 向上找到1级
            $lev1_info = $this->fxUserTreeMod->getData(array('cond' => "`id`='{$info[0]['pid']}'"));
            $tree = $this->getTree($rs, $info[0]['id']);
            $info[0]['child'] = $tree;
            $lev_info = $this->fxLevUser($info,$lev1_info);
            $arr = $lev_info;
            $flag = 2;
        } elseif ($info[0]['fx_level'] == 3) {
//            $lev2_info = $this->fxUserTreeMod->getData(array('cond' => "`id`='{$info[0]['pid']}'"));
//            $lev1_info = $this->fxUserTreeMod->getData(array('cond' => "`id`='{$lev2_info[0]['pid']}'"));
//            $lev2_info[0]['child'] = $info;
//            $lev1_info[0]['child'] = $lev2_info;
            $fxLevUser = $this->getFxUserPhone($info[0]['user_id']);
            $lev1_user = $this->fxUserTreeMod->getOne(array('cond'=>"`id` = '{$info[0]['pidpid']}'",'fields' =>'user_id'));
            $lev_prop = $this->getRulerJin($lev1_user['user_id']);
            $lev3_info['fx_level'] = $info[0]['fx_level'];
            $lev3_info['id'] = $info[0]['id'];
            $lev3_info['user_id'] = $info[0]['fx_level'];
            $lev3_info['pid'] = $info[0]['pid'];
            $lev3_info['lev3_prop'] =  $lev_prop['lev3_prop'];
            $lev3_info['real_name'] = $fxLevUser['real_name'];
            $lev3_info['telephone'] = $fxLevUser['telephone'];
            $arr = $lev3_info;
            $flag = 3;
        }
        //$tree = $this->getUserLevel($rs,0);
//        echo '<pre>';
//       var_dump($this->pingArray($info));die;
        $this->assign('rs', $arr);
        $this->assign('flag', $flag);
        $this->display('myFxTeam/myTearm.html');
    }

    /**
     * 获取用户的等级关系
     * @author wanyan
     * @date 2017/11/27
     */
    public function getTree($list, $pid) {
        $tree = array();
        foreach ($list as $v) {
            if ($v['pid'] == $pid) {
////                $v['category_name'] = $v['category_name'];
                if ($this->getTree($list, $v['id'])) {
                    $v['child'] = $this->getTree($list, $v['id']);
                }
                $tree[] = $v;
            }
        }
        return $tree;
    }

    /**
     * 获取一级分销人员的比列
     */
    public function pingArray($tree) {
//        $info =array();
        foreach ($tree as $k => $v) {
            $lev_prop = $this->getRulerJin($v['user_id']);
            $tree[$k]['lev1_prop'] = $lev_prop['lev1_prop'];
            $fxLev1User = $this->getFxUserPhone($v['user_id']);
            $tree[$k]['real_name'] = $fxLev1User['real_name'];
            $tree[$k]['telephone'] = $fxLev1User['telephone'];
            foreach ($v['child'] as $k1 => $v1) {
                $fxLev2User = $this->getFxUserPhone($v1['user_id']);
                $tree[$k]['child'][$k1]['lev2_prop'] = $lev_prop['lev2_prop'];
                $tree[$k]['child'][$k1]['real_name'] = $fxLev2User['real_name'];
                $tree[$k]['child'][$k1]['telephone'] = $fxLev2User['telephone'];
                foreach ($v1['child'] as $k2 => $v2) {
                    $fxLev3User = $this->getFxUserPhone($v2['user_id']);
                    $tree[$k]['child'][$k1]['child'][$k2]['lev3_prop'] = $lev_prop['lev3_prop'];
                    $tree[$k]['child'][$k1]['child'][$k2]['real_name'] = $fxLev3User['real_name'];
                    $tree[$k]['child'][$k1]['child'][$k2]['telephone'] = $fxLev3User['telephone'];
//                   var_dump($v2);die;
                }
            }
        }
        return $tree;
    }

    /**
     * 获取二级分销人员及三级分销人员的信息
     */
    public function fxLevUser($tree,$lev1_info) {
//        $info =array();
        $lev_prop = $this->getRulerJin($lev1_info[0]['user_id']);
        foreach ($tree as $k => $v) {
            $tree[$k]['lev2_prop'] = $lev_prop['lev2_prop'];
            $fxLev1User = $this->getFxUserPhone($v['user_id']);
            $tree[$k]['real_name'] = $fxLev1User['real_name'];
            $tree[$k]['telephone'] = $fxLev1User['telephone'];
            foreach ($v['child'] as $k1 => $v1) {
                $fxLev2User = $this->getFxUserPhone($v1['user_id']);
                $tree[$k]['child'][$k1]['lev3_prop'] = $lev_prop['lev3_prop'];
                $tree[$k]['child'][$k1]['real_name'] = $fxLev2User['real_name'];
                $tree[$k]['child'][$k1]['telephone'] = $fxLev2User['telephone'];
            }
        }
        return $tree;
    }

    /**
     * 获取用户的佣金规则
     * @author wanyan
     * @date 2017/11/27
     */
    public function getRulerJin($user_id) {
        $sql = "SELECT fr.`lev1_prop`,fr.lev2_prop,fr.lev3_prop FROM " . DB_PREFIX . "fx_user_rule AS fur 
        LEFT JOIN " . DB_PREFIX . "fx_rule as fr ON fur.rule_id = fr.id WHERE fur.user_id = '{$user_id}'";
        $rs = $this->fxuserRuleMod->querySql($sql);
        return $rs[0];
    }

    /**
     * 获取用户的佣金规则
     * @author wanyan
     * @date 2017/11/27
     */
    public function getFxUserPhone($user_id) {
        $sql = "SELECT `real_name`,`telephone` FROM `" . DB_PREFIX . "fx_user` WHERE `user_id` = '{$user_id}'";
        $rs = $this->fxuserRuleMod->querySql($sql);
        return $rs[0];
    }

}
