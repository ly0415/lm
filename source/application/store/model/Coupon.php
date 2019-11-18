<?php

namespace app\store\model;

use app\common\model\coupon\Coupon as CouponModel;

/**
 * 优惠券模型
 * Class Coupon
 * @package app\store\model
 */
class Coupon extends CouponModel
{
    /**
     * 获取优惠券列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($query = [])
    {
        $where['type'] = 1;
        if(isset($query['type']) && !empty($query['type'])){
            $where['type'] = $query['type'];
        }
        return $this->where('mark', '=', 1)
            ->where($where)
            ->order(['add_time' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }

    /**
     * 获取优惠券列表
     */
    public static function getListALL($where)
    {
        return self::where('mark', '=', 1)
            ->where($where)
            ->order(['add_time' => 'desc'])
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
     * 添加抵扣卷/兑换券
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-09
     * Time: 16:51
     */
    public function addCoupon($data){
        if($data['storeValue'] == 1){
            $data['store_id'] = implode(',',array_column(Store::getStoreList(),'id'));
        }
        $data['source'] = 2;
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


    public function formatStoreId($data = []){
        if(empty($data))return '';

    }

}
