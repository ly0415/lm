<?php
/**
 * 充值送睿积分
 * @author tangp
 * @date 2018-09-26
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}
class RechargeApp extends BackendApp
{
    private $lang_id;
    private $rechargeMod;
    public function __construct()
    {
        parent::__construct();

        $this->rechargeMod = &m('recharge');
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0';
    }

    /**
     * 设置页面
     */
    public function index()
    {
        $this->load($this->lang_id,'admin/admin');
//        $langData = $this->langData;var_dump($langData);die;
        $this->assign('langdata',$this->langData);
        $sql = "SELECT * FROM ".DB_PREFIX."recharge_point";
        $data = $this->rechargeMod->querySql($sql);
        $this->assign('res',$data);
        $this->assign('lang_id',$this->lang_id);
        $this->display('recharge/site.html');
    }

    /**
     * 保存充值送睿积分配置
     * @author tangp
     * @date 2018-09-26
     */
    public function saveRecharge()
    {
        $edit = !empty($_REQUEST['edit']) ? intval($_REQUEST['edit']) : 0;
        $lang_id = $_REQUEST['lang_id'];
        if (empty($_REQUEST['start_charge'])){
            $this->setData('',0,'请添加小金额');
        }else{
            $diff_start_charge = array_unique($_REQUEST['start_charge']);
            if (count($diff_start_charge) != count($_REQUEST['start_charge'])){
                $this->setData('',0,'重复输入小金额！');
            }
            foreach ($_REQUEST['start_charge'] as $key => $value){
                $name = htmlspecialchars(trim($value));
                if (empty($name)){
                    $this->setData('',0,'小金额必填!');
                }
            }
        }
        if (empty($_REQUEST['end_charge'])){
            $this->setData('',0,'请添加大金额!');
        }else{
            $diff_end_charge = array_unique($_REQUEST['end_charge']);
            if (count($diff_end_charge) != count($_REQUEST['end_charge'])){
                $this->setData('',0,'重复输入大金额！');
            }
            foreach ($_REQUEST['end_charge'] as $key => $value){
                $name = htmlspecialchars(trim($value));
                if (empty($name)){
                    $this->setData('',0,'大金额必填！');
                }
            }
        }
        if (empty($_REQUEST['recharge'])){
            $this->setData('',0,'请添加积分！');
        }else{
            $diff_recharge = array_unique($_REQUEST['recharge']);
            if (count($diff_recharge) != count($_REQUEST['recharge'])){
                $this->setData('',0,'积分重复输入!');
            }
            foreach ($_REQUEST['recharge'] as $key => $value){
                $name = htmlspecialchars(trim($value));
                if (empty($name)){
                    $this->setData('',0,'积分必填！');
                }
            }
        }
        if ($edit){
            foreach ($_REQUEST['dropIds'] as $key => $value){
                if (in_array($value,$_REQUEST['val'])){
                    $this->rechargeMod->doEdit($value,array(
                        'start_charge' => $_REQUEST['start_charge'][$key],
                        'end_charge'   => $_REQUEST['end_charge'][$key],
                        'recharge'     => $_REQUEST['recharge'][$key]
                    ));
                    unset($_REQUEST['start_charge'][$key]);
                    unset($_REQUEST['end_charge'][$key]);
                    unset($_REQUEST['recharge'][$key]);
                }else{
                    $this->rechargeMod->doDropEs($value);
                }
            }
            $start_charge = $_REQUEST['start_charge'];
            $end_charge = $_REQUEST['end_charge'];
            $recharge = $_REQUEST['recharge'];
            $arr = array();
            foreach ($start_charge as $k => $v){
                $arr[] = array($start_charge[$k],$end_charge[$k],$recharge[$k]);
            }
//            echo '<pre>';
//            var_dump($arr);die;
            foreach ($arr as $v){
               $data = array(
                   'start_charge' => $v[0],
                   'end_charge'   => $v[1],
                   'recharge'     => $v[2]
               );
               $this->rechargeMod->doInsert($data);
            }
//            echo '<pre>';
//            var_dump($data);die;
            $info['url'] = "?app=recharge&index&lang_id=$lang_id";
            $this->setData($info,1,'编辑成功');
        }else{
            $start_charge = $_REQUEST['start_charge'];
            $end_charge   = $_REQUEST['end_charge'];
            $recharge     = $_REQUEST['recharge'];
            $arr = array();
            foreach ($start_charge as $k => $v){
                $arr[] = array($start_charge[$k],$end_charge[$k],$recharge[$k]);
            }
            foreach ($arr as $v){
                $data = array(
                    'start_charge' => $v[0],
                    'end_charge'   => $v[1],
                    'recharge'     => $v[2]
                );
                $this->rechargeMod->doInsert($data);
            }
            $info['url'] = "?app=recharge&index&lang_id=$lang_id";
            $this->setData($info,1,'添加成功');
        }
    }
}