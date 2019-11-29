<?php

namespace app\store\model;

use app\common\model\RechargePoint as RechargePointModel;

/**
 * 余额充值规则模型
 * Class RechargePoint
 * @package app\store\model
 */
class RechargePoint extends RechargePointModel
{


    /**
     * 获取优惠券列表
     */
    public static function getListALL($where = [])
    {
        return self::field('id,c_money,s_money,integral,percent')
            ->where('mark', '=', 1)
            ->where($where)
            ->order(['c_money' => 'ASC'])
            ->select();
    }

    /**
     * 添加新记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        $data[$data['key']] = $data['val'];
        $data['is_special'] = 1;
        $data['add_user'] = session('yoshop_store.user')['store_user_id'];
        return $this->allowField(true)->save($data);
    }

    /**
     * 更新记录
     * @param $data
     * @return bool|int
     */
    public function edit($data,$_data = [])
    {
        $data[$data['key']] = $data['val'];
        $data['is_special'] = 1;
        $data['add_user'] = session('yoshop_store.user')['store_user_id'];
        try{
            $this->where(['type'=>1,'is_special'=>1,'mark'=>1])
                ->update(['mark'=>0]);
            return $this->allowField(true)->save(array_merge($_data,$data));
            $this->commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return false;
        }

    }

    /**
     * 删除记录 (软删除)
     * @return bool|int
     */
    public function setDelete()
    {
        return $this->save(['mark' => 0]) !== false;
    }

}
