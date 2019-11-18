<?php
/**
 * 活动模型
 * @author zhangkx
 * @date 2019/5/14
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class ActivityMod extends BaseMod
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct("activity");
    }

    /**
     * 校验数据
     * @author zhangkx
     * @date 2019/5/14
     * @param $data
     * @return bool
     */
    public function checkData($data)
    {
        if (empty($data['name'])) {
            $this->setData(array(), '0', '请填写活动名称');
        }
        if (empty($data['begin_time'])) {
            $this->setData(array(), '0', '请选择开始时间');
        }
        if (empty($data['end_time'])) {
            $this->setData(array(), '0', '请选择结束时间');
        }
        if (strtotime($data['begin_time']) > strtotime($data['end_time'])) {
            $this->setData(array(), '0', '开始时间不能大于结束时间');
        }
        return true;
    }

    /**
     * 组装数据
     * @author zhangkx
     * @date 2019/3/21
     * @param $data
     * @param $accountId
     * @param $id
     * @return array
     */
    public function buildData($data, $accountId, $id)
    {
        $data['name'] = !empty($data['name'])? trim($data['name']) : '';  //活动名称
        $data['begin_time'] = !empty($data['begin_time'])? trim($data['begin_time']) : '';  //开始时间
        $data['end_time'] = !empty($data['end_time'])? trim($data['end_time']) : '';  //结束时间
        $data['description'] = !empty($data['description'])? $data['description'] : '';  //活动描述
        $result = array(
            'name' => $data['name'],
            'begin_time' => strtotime($data['begin_time']),
            'end_time' => strtotime($data['end_time']),
            'description' => $data['description'],
        );
        if ($id) {
            if($result['id']) unset($result['id']);
        } else {
            $result['add_user'] = $accountId;
            $result['add_time'] = time();
        }
        return $result;
    }
}