<?php
/**
 * 店铺活动模型
 * @author zhangkx
 * @date 2019-03-20
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class StoreActivityMod extends BaseMod {
    private $langDataBank, $accountId;
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct("store_activity");
        //加载语言包
        $this->langDataBank = languageFun($this->shorthand);
        $this->accountId    = $_SESSION['account_id'];
    }

    /**
     * 校验数据
     * @author zhangkx
     * @date 2019/3/21
     * @param $data
     * @param $id
     * @return bool
     */
    public function checkData($data, $id)
    {
        if (empty($data['store_id'])) {
            $this->setData(array(), '0', $this->langDataBank->project->select_regional_store);
        }
        if (empty($data['type'])) {
            $this->setData(array(), '0', $this->langDataBank->project->select_activity_type);
        }
        $cond = ' mark = 1 and type = '.$data['type'].' and store_id = '.$data['store_id'].' and is_use = 1 and begin_time <= '.time().' and end_time >= '.time();
        if ($id) {
            $cond .= ' and id != '.$id;
        }
        $info = $this->getData(array('cond' => $cond));
        if ($info) {
            $this->setData(array(), '0', $this->langDataBank->project->activity_exist);
        }
        if ($data['type'] == 2 && empty($data['fission_id'])) {
            $this->setData(array(), '0', $this->langDataBank->project->select_fx_rule);
        }
        if (empty($data['name'])) {
            $this->setData(array(), '0', $this->langDataBank->project->add_activity_name);
        }
        if (empty($data['begin_time'])) {
            $this->setData(array(), '0', $this->langDataBank->project->select_begin_time);
        }
        if (empty($data['end_time'])) {
            $this->setData(array(), '0', $this->langDataBank->project->select_end_time);
        }
        if (strtotime($data['begin_time']) > strtotime($data['end_time'])) {
            $this->setData(array(), '0', $this->langDataBank->project->time_format);
        }
        if (empty($data['description'])) {
            $this->setData(array(), '0', $this->langDataBank->project->add_desc);
        }
        return true;
    }

    /**
     * 组装数据
     * @author zhangkx
     * @date 2019/3/21
     * @param $data
     * @param $id
     * @return array
     */
    public function buildData($data, $id)
    {
        $data['store_id'] = !empty($data['store_id'])? (int)$data['store_id'] : '';  //店铺id
        $data['type'] = !empty($data['type'])? (int)$data['type'] : '';  //类型
        $data['fission_id'] = !empty($data['fission_id'])? (int)$data['fission_id'] : '';  //规则id
        $data['name'] = !empty($data['name'])? trim($data['name']) : '';  //活动名称
        $data['begin_time'] = !empty($data['begin_time'])? trim($data['begin_time']) : '';  //开始时间
        $data['end_time'] = !empty($data['end_time'])? trim($data['end_time']) : '';  //结束时间
        $data['is_use'] = !empty($data['is_use'])? (int)$data['is_use'] : '';  //状态
        $data['description'] = !empty($data['description'])? $data['description'] : '';  //活动描述
        $result = array(
            'store_id' => $data['store_id'],
            'name' => $data['name'],
            'type' => $data['type'],
            'fission_id' => $data['fission_id'],
            'begin_time' => strtotime($data['begin_time']),
            'end_time' => strtotime($data['end_time']),
            'is_use' => $data['is_use'],
            'description' => $data['description'],
        );
        if ($id) {
            if($result['id']) unset($result['id']);
            $result['upd_user'] = $this->accountId;
            $result['upd_time'] = time();
        } else {
            $result['add_user'] = $this->accountId;
            $result['add_time'] = time();
        }
        return $result;
    }
}