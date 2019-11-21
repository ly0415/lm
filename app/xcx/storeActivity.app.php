<?php
/**
 * 文章控制器
 * @author zhangkx
 * @date: 2019/3/21
 */
class StoreActivityApp extends BasePhApp
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = &m('storeActivity');
        $this->storeMod = &m('store');
        $this->applyMod = &m('storeActivityApply');
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
    }

    /**
     * 店铺活动
     * @author zhangkx
     * @date 2019/3/21
     */
    public function index()
    {
        $id = $_REQUEST['activity_id'] ? $_REQUEST['activity_id'] : 0;
        $userId = $_REQUEST['user_id'] ? $_REQUEST['user_id'] : 0;
        if (empty($id)) {
            $this->setData(array(), '0', '未指定活动id');
        }
        if (empty($userId)) {
            $this->setData(array(), '0', '未指定用户id');
        }
        $info = $this->model->getRow($id);
        $apply = $this->applyMod->getOne(array('cond' => 'activity_id = '.$id.' and user_id = '.$userId));
        $info['status'] = $apply['status'] == 1 ? 1 : 2;
        $this->setData($info,'1','');
    }

    /**
     * 报名/取消报名店铺活动
     * @author zhangkx
     * @date 2019/3/21
     */
    public function apply()
    {
        if (IS_POST) {
            $data = $_POST;
            $data['source'] = 2;
            $data['status'] = 1;
            if (method_exists($this->applyMod,  'checkData')) {
                $this->applyMod->checkData($data);
            }
            //组装数据
            if (method_exists($this->applyMod,  'buildData')) {
                $data = $this->applyMod->buildData($data);
            }
            //插入数据
            $result = $this->applyMod->doInsert($data);
            if (!$result) {
                $this->setData(array(), '0', '报名失败');
            }
            $this->setData(array(),'1','报名成功');

        }
    }

    /**
     * 我的奖励
     * @author zhangkx
     * @date 2019/3/29
     */
    public function myRewards()
    {
        $userId = $_REQUEST['user_id'] ? $_REQUEST['user_id'] : 4328;
        $page = $_REQUEST['page'] ? $_REQUEST['page'] : 0;
        $page = $page * 5;
        $sql = 'select a.*,c.phone, d.money, d.num from '.DB_PREFIX.'store_balance_user as a 
                left join '.DB_PREFIX.'user as b on a.recomend_user_id = b.id 
                left join '.DB_PREFIX.'user as c on a.login_user_id = c.id 
                left join '.DB_PREFIX.'fission_rules as d on a.fission_rules_id = d.id where a.recomend_user_id = '.$userId.' order by a.id desc limit '.$page.',5';
        $data = $this->model->querySql($sql);
        $moneySql = 'select sum(b.money) as total_money, sum(b.num) as total_num, count(*) as total from '.DB_PREFIX.'store_balance_user as a 
                left join '.DB_PREFIX.'fission_rules as b on a.fission_rules_id = b.id where a.recomend_user_id = '.$userId;
        $money = $this->model->querySql($moneySql);
        $totalMoney = $money[0]['total_money'] + $money[0]['total_num'];
        foreach ($data as $key => &$value) {
            $value['money'] = $value['money'] + $value['num'];
            $value['add_time'] = date('Y-m-d', $value['add_time']);
        }
        $info = array(
            'data' => $data,
            'count' => count($data),
            'money' => $totalMoney
        );
        $this->setData($info, '1', '我的奖励');
    }

}