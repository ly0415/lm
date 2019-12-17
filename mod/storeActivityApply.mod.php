<?php
/**
 * 店铺活动报名模型
 * @author zhangkx
 * @date 2019/3/20
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class StoreActivityApplyMod extends BaseMod {
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct("store_activity_apply");
        //加载语言包
        $this->langDataBank = languageFun($this->shorthand);
        $this->accountId    = $_SESSION['account_id'];
    }

    /**
     * 校验数据
     * @author zhangkx
     * @date 2019/3/21
     * @param $data
     * @return bool
     */
    public function checkData($data)
    {
        $info = $this->getData(array('cond' => 'activity_id = '.$data['activity_id'].' and user_id = '.$data['user_id'].' and status = 1'));
        if ($info) {
            $this->setData(array(), '0', $this->langDataBank->project->activity_exist);
        }
        return true;
    }

    /**
     * 组装数据
     * @author zhangkx
     * @date 2019/3/21
     * @param $data
     * @return array
     */
    public function buildData($data)
    {
        $data['activity_id'] = !empty($data['activity_id'])? (int)$data['activity_id'] : '';  //活动id
        $data['source'] = !empty($data['source'])? (int)$data['source'] : '';  //来源
        $data['user_id'] = !empty($data['user_id'])? (int)$data['user_id'] : '';  //用户id
        $data['status'] = !empty($data['status'])? (int)$data['status'] : '';  //状态
        $result = array(
            'activity_id' => $data['activity_id'],
            'source' => $data['source'],
            'user_id' => $data['user_id'],
            'status' => $data['status'],
            'add_time' => time(),
        );
        return $result;
    }
}