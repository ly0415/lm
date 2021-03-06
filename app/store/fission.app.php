<?php
/**
 * 推荐规则
 * @author wangshuo
 * @date 2019-3-27
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class FissionApp extends BaseStoreApp {

    private $fissionMod;
    private $fissionRulesMod;

    public function __construct() {
        parent::__construct();
        $this->fissionMod = &m('fission');
        $this->fissionRulesMod = &m('fissionRules');
    }

    /**
     * 推荐规则名称列表
     * @author wangshuo
     * @date 2019-03-27
     */
    public function index()
    {
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        $where = 'a.mark =1 and a.store_id = ' . $this->storeId;
        if (!empty($name)) {
            $where .= ' and a.name like "%'.$name.'%"';
            $this->assign('name', $name);
        }
        $sql = 'select a.*,b.username from '
            . DB_PREFIX . 'fission as a left join '
            . DB_PREFIX . 'store_user as b on a.add_user = b.id where '.$where;
        $res = $this->fissionMod->querySql($sql);
        $this->assign('tree', $res);
        $this->display('fission/index.html');
    }

    /**
     * 添加推荐规则名称
     * @author wangshuo
     * @date 2019-03-27
     */
    public function add()
    {
        if (IS_POST) {
            $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
            if (empty($name)) {
                $this->setData(array(), '0', '规则名称不可以为空');
            }
            $data = array(
                'name' => $name,
                'add_user' => $this->storeUserId,
                'mark' => 1,
                'store_id' => $this->storeId,
                'add_time' => time()
            );
            $res = $this->fissionMod->doInsert($data);
            if ($res) {
                $this->setData(array(), '1', '添加成功');
            } else {
                $this->setData(array(), '0', '添加失败');
            }
        }
        $this->display('fission/add.html');
    }

    /**
     * 删除推荐规则名称
     */
    public function drop()
    {
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        if (empty($id)) {
            $this->setData(array(), '0', '系统错误');
        }
        // 伪删除表数据
        $res = $this->fissionMod->doMark($id);
        if ($res) {   //删除成功
            $this->setData(array(), '1', '删除成功');
        } else {
            $this->setData(array(), '0', '删除失败');
        }
    }

    /**
     * 推荐获取奖励的规则表
     * @author wangshuo
     * @date 2019-03-27
     */
    public function addRules()
    {
        $fissionId= !empty($_REQUEST['fission_id']) ? htmlspecialchars(trim($_REQUEST['fission_id'])) : '';
        $this->assign('fission_id', $fissionId);
        if (IS_POST) {
            $edit = !empty($_REQUEST['edit']) ? intval($_REQUEST['edit']) : 0;
            if($edit==0){
                if (empty($edit)) {
                    $this->setData(array(), '0', '未有任何修改信息');
                }
            }
            $min_persons = $_REQUEST['min_persons'];
            $symbol_one   = $_REQUEST['symbol_one'];
            $max_persons     = $_REQUEST['max_persons'];
            $symbol_two = $_REQUEST['symbol_two'];
            $num = $_REQUEST['num'];
            $money = $_REQUEST['money'];
            $fission_id = $_REQUEST['fission_id'];
            $arr = array();
            foreach ($min_persons as $k => $v){
                if(empty($min_persons[$k])){
                    $this->setData(array(), '0', '最小人数不可为空');
                }
                if(empty($max_persons[$k])){
                    $this->setData(array(), '0', '最大人数不可为空');
                }
                if(empty($num[$k])){
                    $this->setData(array(), '0', '赠送金额不能为空');
                }
                if($k < 1){
                    if($max_persons[$k]){
                        $sql = "SELECT max(max_persons) as max_persons  FROM ".DB_PREFIX."fission_rules where mark =1 and fission_id= ".$fission_id." order by id ";
                        $datas = $this->fissionRulesMod->querySql($sql);
                        if($v <= $datas[0]['max_persons']){
                            $this->setData(array(), '0', '最小人数不能小于或等于已填写规则最大人数');
                        }
                        if($max_persons[$k] <= $v){
                            $this->setData(array(), '0', '当前规则最大人数不能小于或等于同规则最小人数');
                        }
                    }
                }else if($k > 0){
                    if($max_persons[$k-1]==-1){
                        $this->setData(array(), '0', ' ∞ 等于无穷，最大化，无法添加下一条');
                    }
                    if($v <= $max_persons[$k-1] ){
                        $this->setData(array(), '0', '最小人数不能小于或等于已填写规则最大人数');
                    }
                    if($max_persons[$k]){
                        if($v >= $max_persons[$k]){
                            $this->setData(array(), '0', '当前规则最大人数不能小于或等于同规则最小人数');
                        }
                    }
                }
                if($max_persons[$k]=='∞'){
                    $max_persons[$k]=-1;
                }
                $arr[] = array($min_persons[$k],$symbol_one[$k],$max_persons[$k],$symbol_two[$k],$num[$k],$money[$k]);
            }
            foreach ($arr as $v){
                $data = array(
                    'fission_id' => $fission_id,
                    'min_persons' => $v[0],
                    'symbol_one' => $v[1],
                    'max_persons' => $v[2],
                    'symbol_two' => $v[3],
                    'num'  => $v[4],
                    'money'  => $v[5],
                    'store_id' => $this->storeId,
                    'add_user' => $this->storeUserId,
                    'mark' => 1,
                    'add_time' => time()
                );
                $res = $this->fissionRulesMod->doInsert($data);
                if (!$res) {
                    $this->setData(array(),0,'修改失败');
                }
            }
            $info['url'] = "?app=recommendedList&act=addRules&fission_id=".$fission_id;
            $this->setData($info,1,'修改成功');
        }
        $sql = "SELECT * FROM ".DB_PREFIX."fission_rules where mark =1 and fission_id= ".$fissionId." order by id ";
        $data = $this->fissionRulesMod->querySql($sql);
        $this->assign('data',$data);
        $this->display('fission/addRules.html');
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
        $res = $this->fissionRulesMod->doMark($id);
        if ($res) {   //删除成功
            $this->setData(array(), '1', '删除成功');
        } else {
            $this->setData(array(), '0', '删除失败');
        }
    }

}
