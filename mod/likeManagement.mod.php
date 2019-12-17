<?php
/**
 * 活动点赞模型
 * @author zhangkx
 * @date 2019/5/14
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class LikeManagementMod extends BaseMod
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct("like_management");
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
        if (empty($data['wx_code'])) {
            $this->setData(array(), '0', '请填写微信号');
        }
        $likeData = $this->getOne(array('cond'=>'mark = 1 and wx_code = "'.$data['wx_code'].'"'));
        if ($likeData) {
            $this->setData(array(), '0', '微信号已存在');
        }
        if (empty($data['store_id'])) {
            $this->setData(array(), '0', '请选择区域店铺');
        }
        if (empty($data['activity_id'])) {
            $this->setData(array(), '0', '请选择活动');
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
        $data['wx_code'] = !empty($data['wx_code'])? trim($data['wx_code']) : '';  //微信号
        $data['store_id'] = !empty($data['store_id'])? trim($data['store_id']) : '';  //店铺id
        $data['activity_id'] = !empty($data['activity_id'])? trim($data['activity_id']) : '';  //活动id
        $result = array(
            'wx_code' => $data['wx_code'],
            'store_id' => $data['store_id'],
            'activity_id' => $data['activity_id'],
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