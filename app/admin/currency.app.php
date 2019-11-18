<?php

/**
 * 币种管理模块
 * @author  wanyan
 * @date 2017-10-09
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class CurrencyApp extends BackendApp {

    private $currencyMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->currencyMod = &m('currency');
    }

    /**
     * 币种管理模块
     * @author  wanyan
     * @date 2017-10-09
     */
    public function index() {
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        $where = "  where `mark` =1";
        if (!empty($name)) {
            $where .= " and `name` like '%" . $name . "%'";
        }
        $where .= " order by  id desc";
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "currency " . $where;
        $totalCount = $this->currencyMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
//      倒叙代码
//        if($total < 20){
//            if($p >=2){
//                if($this->lang_id ==0){
//                    echo '数据总数小于显示数，页码出错!';die;
//                }else{
//                    exit('The total number of data is less than the display number, the page number is wrong!');
//                }
//
//            }
//        }
        $sql = "select * from " . DB_PREFIX . "currency " . $where;
        $rs = $this->currencyMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($rs['list'] as $k => $v) {
            if ($v['add_time']) {
                $rs['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            } else {
                $rs['list'][$k]['add_time'] = '';
            }
            $rs['list'][$k]['sort'] = $k + 20 * ($p - 1) + 1; //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        $this->assign('p', $p);
        $this->assign('name', $name);
        $this->assign('list', $rs['list']);
        $this->assign('page', $rs['ph']);
        $this->assign('lang_id', $this->lang_id);
        $this->display('currency/index.html');

    }

    /**
     * 币种管理模块
     * @author  wanyan
     * @date 2017-10-09
     */
    public function add() {
        $this->assign('act', 'index');
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $this->assign('p', $p);
        $this->display('currency/add.html');

    }

    /**
     * 币种添加模块
     * @author  wanyan
     * @date 2017-10-09
     */
    public function doAdd() {
        $lang_id = !empty($_REQUEST['lang_id']) ? (int) ($_REQUEST['lang_id']) : '0';
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        $short = !empty($_REQUEST['short']) ? htmlspecialchars(trim($_REQUEST['short'])) : '';
        $symbol = !empty($_REQUEST['symbol']) ? htmlspecialchars(trim($_REQUEST['symbol'])) : '';
        $note = !empty($_REQUEST['note']) ? htmlspecialchars(trim($_REQUEST['note'])) : '';
        $rate = !empty($_REQUEST['rate']) ? htmlspecialchars(trim($_REQUEST['rate'])) : '0';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $info = array();
        if (empty($name)) {
            $this->setData($info, $status = 0, $this->langDataBank->project->currency_name_required);
        } else {
            $rs = $this->checkValue('name', $name);
            if ($rs) {
                $this->setData($info, $status = 0, $this->langDataBank->project->currency_name_exist);
            }
        }
        if (empty($short)) {
            $this->setData($info, $status = 0, $this->langDataBank->project->currency_short_required);
        } else {
            $rs = $this->checkValue('short', $short);
            if ($rs) {
                $this->setData($info, $status = 0, $this->langDataBank->project->currency_short_exist);
            }
        }
        if (empty($symbol)) {
            $this->setData($info, $status = 0, $this->langDataBank->project->currency_symbol_required);
        } else {
            $rs = $this->checkValue('symbol', $symbol);
            if ($rs) {
                $this->setData($info, $status = 0, $this->langDataBank->project->currency_symbol_exist);
            }
        }
        if (empty($rate)) {
            $this->setData($info, $status = 0, $this->langDataBank->project->rate_required);
        }
        if ($rate == '0.0000') {
            $this->setData($info, $status = 0, $this->langDataBank->project->rate_error);
        }
        if (!preg_match('/^([1-9]\d*|0)(\.\d{1,4})?$/', $rate)) {
            $this->setData($info, $status = 0, $this->langDataBank->project->rate_format_error);
        }

        $data = array(
            'name' => $name,
            'short' => $short,
            'symbol' => $symbol,
            'note' => $note,
            'rate' => $rate,
            'add_time' => time()
        );
        $insert_id = $this->currencyMod->doInsert($data);
        if ($insert_id) {
            $info['url'] = "admin.php?app=currency&act=index&p={$p}";
            $this->addLog('币种添加操作');
            $this->setData($info, $status = 1, $this->langDataBank->public->add_success);
        } else {
            $this->setData($info, $status = 0, $this->langDataBank->public->add_error);
        }
    }

    /**
     * 检测值是否存在
     * @author  wanyan
     * @date 2017-10-09
     */
    public function checkValue($key, $value, $id = 0) {
        if (!empty($id)) {
            $query = array(
                'cond' => " `{$key}` = '{$value}' and `mark` = '1' and `id` != '{$id}'",
                'fields' => '*'
            );
        } else {
            $query = array(
                'cond' => " `{$key}` = '{$value}' and `mark` = '1'",
                'fields' => '*'
            );
        }
        $rs = $this->currencyMod->getOne($query);
        return $rs;
    }

    /**
     * 币种管理模块
     * @author  wanyan
     * @date 2017-10-09
     */
    public function edit() {

        $id = !empty($_REQUEST['id']) ? htmlspecialchars(trim($_REQUEST['id'])) : '0';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        if (empty($id)) {
            return false;
        }
        $rs = $this->currencyMod->getOne(array('cond' => "`id` ='{$id}'"));
        $this->assign('act', 'index');
        $this->assign('list', $rs);
        $this->assign('p', $p);
        $this->display('currency/edit.html');
    }

    /**
     * 币种编辑模块
     * @author  wanyan
     * @date 2017-10-09
     */
    public function doEdit() {
        $lang_id = !empty($_REQUEST['lang_id']) ? (int) ($_REQUEST['lang_id']) : '0';
        $id = !empty($_REQUEST['id']) ? htmlspecialchars(trim($_REQUEST['id'])) : '0';
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        $short = !empty($_REQUEST['short']) ? htmlspecialchars(trim($_REQUEST['short'])) : '';
        $symbol = !empty($_REQUEST['symbol']) ? htmlspecialchars(trim($_REQUEST['symbol'])) : '';
        $note = !empty($_REQUEST['note']) ? htmlspecialchars(trim($_REQUEST['note'])) : '';
        $rate = !empty($_REQUEST['rate']) ? htmlspecialchars(trim($_REQUEST['rate'])) : '0';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $info = array();
        if (empty($name)) {
            $this->setData($info, $status = 0, $this->langDataBank->project->currency_name_required);
        } else {
            $rs = $this->checkValue('name', $name, $id);
            if ($rs) {
                $this->setData($info, $status = 0, $this->langDataBank->project->currency_name_exist);
            }
        }
        if (empty($short)) {
            $this->setData($info, $status = 0, $this->langDataBank->project->currency_short_required);
        } else {
            $rs = $this->checkValue('short', $short, $id);
            if ($rs) {
                $this->setData($info, $status = 0, $this->langDataBank->project->currency_short_exist);
            }
        }
        if (empty($symbol)) {
            $this->setData($info, $status = 0, $this->langDataBank->project->currency_symbol_required);
        } else {
            $rs = $this->checkValue('symbol', $symbol, $id);
            if ($rs) {
                $this->setData($info, $status = 0, $this->langDataBank->project->currency_symbol_exist);
            }
        }
        if (empty($rate)) {
            $this->setData($info, $status = 0, $this->langDataBank->project->rate_required);
        }
        if ($rate == '0.0000') {
            $this->setData($info, $status = 0, $this->langDataBank->project->rate_error);
        }
        if (!preg_match('/^([1-9]\d*|0)(\.\d{1,4})?$/', $rate)) {
            $this->setData($info, $status = 0, $this->langDataBank->project->rate_format_error);
        }
        $data = array(
            'name' => $name,
            'short' => $short,
            'symbol' => $symbol,
            'note' => $note,
            'rate' => $rate,
            'modify_time' => time()
        );
        $insert_id = $this->currencyMod->doEdit($id, $data);
        if ($insert_id) {
            $info['url'] = "admin.php?app=currency&act=index&p={$p}";
            $this->addLog('币种编辑操作');
            $this->setData($info, $status = 1, $this->langDataBank->public->edit_success);
        } else {
            $this->setData($info, $status = 0, $this->langDataBank->public->edit_fail);
        }
    }

    /**
     * 删除币种模块
     * @author  wanyan
     * @date 2017-10-09
     */
    public function dele() {
        $id = !empty($_REQUEST['id']) ? htmlspecialchars(trim($_REQUEST['id'])) : '0';
        $info = array();
        $storeCate = &m('storeCate');
        $query = array(
            'cond' => "`currency_id` in ({$id}) ",
            'fields' => "`id`"
        );
        $rs = $storeCate->getData($query);
        if (!empty($rs)) {
            $this->setData($info, $status = '0', $this->langDataBank->project->currency_used);
        }

        if (empty($id)) {
            $this->setData($info, $status = 0, $this->langDataBank->project->param_error);
        }
        $rs = $this->currencyMod->doMark($id);
        if ($rs) {
            $this->addLog('币种删除操作');
            $this->setData($info, $status = 1,$this->langDataBank->public->drop_success);
        } else {
            $this->setData($info, $status = 0, $this->langDataBank->public->drop_fail);
        }
    }

}

?>