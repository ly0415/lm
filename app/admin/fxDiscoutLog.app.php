<?php

/**
 * 分销优惠申诉
 * @author  lee
 * @date 2017-11-28 16:23:42
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class fxDiscoutLogApp extends BackendApp {

    private $fxDiscountLogMod;
    private $fxUserMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->fxDiscountLogMod = &m('fxDiscountLog');
        $this->fxUserMod = &m('fxuser');
    }

    /*
     * 审核记录
     * @author lee
     * @date 2017-11-28 16:27:23
     */

    public function logList() {
        $fxruleMod = &m('fxrule');
        $username = $_REQUEST['name'] ? trim($_REQUEST['name']) : '';
        $status = $_REQUEST['status'] ? trim($_REQUEST['status']) : '';
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars(($_REQUEST['store_id'])) : '';
        $area_id = !empty($_REQUEST['area_id']) ? htmlspecialchars(($_REQUEST['area_id'])) :'';

            $whe = " where a.mark = 1"; // mark 为1 by xt 2019.03.06
        if ($username) {
            $whe .= " and b.real_name like '%" . $username . "%'";
        }
        if ($status) {
            $whe .= " and a.is_check=" . $status;
        }
        if ($area_id) {
            $whe .= " and b.store_cate=" . $area_id;
        }
        if ($store_id) {
            $whe .= " and b.store_id=" . $store_id;
        }
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "fx_discount_log where mark = 1";  // mark 为1 by xt 2019.03.06
        $totalCount = $this->fxDiscountLogMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $sql = "SELECT a.*,b.real_name FROM ".DB_PREFIX."fx_discount_log as a
                LEFT JOIN ".DB_PREFIX."fx_user as b on b.id = a.fx_user_id".$whe." ORDER BY a.is_check asc,a.add_time DESC ";

        $list = $this->fxDiscountLogMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($list['list'] as $k => $v) {
            if ($v['add_time']) {
                $list['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            } else {
                $list['list'][$k]['add_time'] = '';
            }
//            $list['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
            $list['list'][$k]['lev3_prop'] = $fxruleMod->getLev3Percent($v['fx_user_id']); //分销比例
            $list['list'][$k]['sort_id'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        //获取区域数组
        $storeCateMod = &m('storeCate');
        $areaArr = $storeCateMod->getAreaArr(1,$this->lang_id);
        $areaOption = make_option($areaArr, $area_id);
        $this->assign('areaOption', $areaOption);
        if ($area_id) {
            $this->assign('area_id', $area_id);
            if ($store_id) {
                $this->assign('store_id', $store_id);
            }
            //获取区域店铺
            $storeMod = &m('store');
            $storeArr = $storeMod->getStoreArr($area_id, 1);
            $storeOption = make_option($storeArr, $store_id);
            $this->assign('storeList', $storeOption);
//            echo "<pre>";print_r($storeArr);
        }
//        echo "<pre>";print_r($areaArr);
        $this->assign('p', $p);
        $this->assign('username', $username);
        $this->assign('status', $status);
        $this->assign('list', $list['list']);
        $this->assign('page', $list['ph']);
        $this->display('fxDiscount/logList.html');
    }

    /*
     * 审核
     */

    public function setFxdiscount() {
        $id = $_REQUEST['id'] ? $_REQUEST['id'] : '';
        $log_info = $this->fxDiscountLogMod->getOne(array("cond" => "id=" . $id));
        $arr_log = array(
            'is_check' => 2,
            'check_time' => time(),
            'check_user' => $this->accountId,
        );
        $arr_user = array(
            'discount' => $log_info['fx_discount']
        );
        $res = $this->fxUserMod->doUpdate(array("cond" => "id=" . $log_info['fx_user_id'], "set" => $arr_user));
        if ($res) {
            $r = $this->fxDiscountLogMod->doEdit($id, $arr_log);
            if ($r) {
                $this->setData($info = array('url' => '?app=fxDiscoutLog&act=logList'), $status = '1', $this->langDataBank->public->success);
            } else {
                $this->setData($info = array(), $status = '0', $this->langDataBank->public->fail);
            }
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->fail);
        }
    }

    /*
     * 删除
     */

    public function dele() {
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '0';
        if (empty($id)) {
            return false;
        }
        //删除主表的数据
        // $query = array(
        //     'cond' => "`id`='{$id}'"
        // );
        // $aff_id = $this->fxDiscountLogMod->doDelete($query);
        $aff_id = $this->fxDiscountLogMod->doMark($id); // by xt 2019.03.06 软删除
        if ($aff_id) {
            $this->addLog('分销优惠申请删除操作');
            $this->setData($info = array(), $status = '1', $this->langDataBank->public->drop_success);
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->drop_fail);
        }
    }

}
