<?php
/**
 * 店铺运费规则控制器
 * @author zhangkx
 * @date 2019/4/9
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class StoreFareApp extends BackendApp {

    private $storeFareMod;
    private $storeFareRuleMod;

    public function __construct() {
        parent::__construct();
        $this->storeFareMod = &m('storeFare');
        $this->storeFareRuleMod = &m('storeFareRule');
    }

    /**
     * 运费规则名称列表
     * @author wangshuo
     * @date 2019-03-27
     */
    public function index()
    {
        // echo "<pre>";print_r($_SESSION);
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars(trim($_REQUEST['store_id'])) : '';
        $where = 'a.mark =1 ';
        if (!empty($name)) {
            $where .= ' and a.name like "%'.$name.'%"';
            $this->assign('name', $name);
        }
        if (!empty($store_id)) {
            $where .= " and a.store_id = {$store_id} ";
            $this->assign('store_id', $store_id);
        }
        $sql = 'select a.*,b.username from '
            . DB_PREFIX . 'store_fare as a left join '
            . DB_PREFIX . 'store_user as b on a.add_user = b.id where '.$where;
        $res = $this->storeFareMod->querySql($sql);
        $this->assign('tree', $res);
        $this->display('storeFare/index.html');
    }

    /**
     * 添加运费规则名称
     * @author wangshuo
     * @date 2019-03-27
     */
    public function add()
    {
        if (IS_POST) {
            $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
            $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars(trim($_REQUEST['store_id'])) : '';
            if (empty($name)) {
                $this->setData(array(), '0', '规则名称不可以为空');
            }
            if (empty($store_id)) {
                $this->setData(array(), '0', '店铺不可以为空');
            }
            $data = array(
                'name' => $name,
                // 'add_user' => $_SESSION['account_id'],
                'mark' => 1,
                'store_id' => $store_id,
                'add_time' => time()
            );
            $res = $this->storeFareMod->doInsert($data);
            if ($res) {
                $this->setData(array(), '1', '添加成功');
            } else {
                $this->setData(array(), '0', '添加失败');
            }
        }
        // 区域列表
        $area_data = &m('storeCate')->getAreaArr(1,$this->lang_id);

        $service_area_data = array_map(function ($i, $m) {
            return array('id' => $i, 'name' => $m);
        }, array_keys($area_data), $area_data);

        $this->assign('service_area_data', $service_area_data);
        $this->display('storeFare/add.html');
    }

    /**
     * 删除运费规则名称
     */
    public function drop()
    {
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        if (empty($id)) {
            $this->setData(array(), '0', '系统错误');
        }
        // 伪删除表数据
        $res = $this->storeFareMod->doMark($id);
        if ($res) {   //删除成功
            $this->setData(array(), '1', '删除成功');
        } else {
            $this->setData(array(), '0', '删除失败');
        }
    }

    /**
     * 运费规则表
     * @author wangshuo
     * @date 2019-03-27
     */
    public function addRules()
    {
        $storeFareId= !empty($_REQUEST['store_fare_id']) ? htmlspecialchars(trim($_REQUEST['store_fare_id'])) : '';
        $storeId= !empty($_REQUEST['store_id']) ? htmlspecialchars(trim($_REQUEST['store_id'])) : '';
        $this->assign('store_fare_id', $storeFareId);
        $this->assign('store_id', $storeId);
        if (IS_POST) {
            $edit = !empty($_REQUEST['edit']) ? intval($_REQUEST['edit']) : 0;
            if($edit==0){
                if (empty($edit)) {
                    $this->setData(array(), '0', '未有任何修改信息');
                }
            }
            $min_number = $_REQUEST['min_number'];
            $min_symbol   = $_REQUEST['min_symbol'];
            $max_number     = $_REQUEST['max_number'];
            $max_symbol = $_REQUEST['max_symbol'];
            $percent = $_REQUEST['percent'];
            $store_fare_id = $_REQUEST['store_fare_id'];
            $store_id = $_REQUEST['store_id'];
            $arr = array();

            foreach ($max_number as &$item) {
                if ($item == '∞') {
                    $item = 99; // ∞ 字符串转化为数字
                }
            }

            $sql = "SELECT max(max_number) as max_number  FROM ".DB_PREFIX."store_fare_rule where mark =1 and fare_id= ".$store_fare_id." order by id ";
            $datas = $this->storeFareRuleMod->querySql($sql);

            foreach ($min_number as $k => $v){
                if (!is_numeric($v)) {
                    $this->setData(array(), '0', '最小数必须为数字');
                }

                if(!($max_number[$k] > 0)){
                    $this->setData(array(), '0', '最大数不可为空');
                }

                if ($v >= 99) {
                    $this->setData(array(), '0', '最小数必须不能大于等于99');
                }

                if($max_number[$k] > 99){
                    $this->setData(array(), '0', '最大数不能大于99');
                }

                if (!is_numeric($v)) {
                    $this->setData(array(), '0', '最小数必须为数字');
                }

                if ($v >= $max_number[$k]) {
                    $this->setData(array(), '0', '当前规则最大数不能小于或等于同规则最小数');
                }

                if(!is_numeric($percent[$k])){
                    $this->setData(array(), '0', '百分比必须为数字');
                }

                if ($min_number[$k] < $max_number[$k-1] && $max_number[$k-1] > 0) {
                    $this->setData(array(), '0', '最小数不能小于已填写规则最大数');
                }

                if ($datas && $v < $datas[0]['max_number']) {
                    $this->setData(array(), '0', '最小数不能小于或等于已填写规则最大数');
                }

                $arr[] = array($min_number[$k],$min_symbol[$k],$max_number[$k],$max_symbol[$k],$percent[$k]);
            }
            foreach ($arr as $v){
                $data = array(
                    'fare_id' => $store_fare_id,
                    'min_number' => $v[0],
                    'min_symbol' => $v[1],
                    'max_number' => $v[2],
                    'max_symbol' => $v[3],
                    'percent'  => $v[4],
                    'store_id' => $store_id,
                    // 'add_user' => $this->storeUserId,
                    'mark' => 1,
                    'add_time' => time()
                );
                $res = $this->storeFareRuleMod->doInsert($data);
                if (!$res) {
                    $this->setData(array(),0,'修改失败');
                }
            }
            $info['url'] = "?app=storeFare&act=index";
            $this->setData($info,1,'修改成功');
        }
        $sql = "SELECT * FROM ".DB_PREFIX."store_fare_rule where mark =1 and fare_id= ".$storeFareId." order by id ";
        $data = $this->storeFareRuleMod->querySql($sql);
        $this->assign('data',$data);
        $this->display('storeFare/addRules.html');
    }

    /**
     * 删除规则
     */
    public function dropRules()
    {
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        if (empty($id)) {
            $this->setData(array(), '0', '系统错误');
        }
        // 伪删除表数据
        $res = $this->storeFareRuleMod->doMark($id);
        if ($res) {   //删除成功
            $this->setData(array(), '1', '删除成功');
        } else {
            $this->setData(array(), '0', '删除失败');
        }
    }
}
