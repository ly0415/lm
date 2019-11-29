<?php

namespace app\store\model;

use app\common\model\SpikeActivity as SpikeActivityModel;

/**
 * 砍价模型
 * Class Article
 * @package app\store\model
 */
class SpikeActivity extends SpikeActivityModel
{
    /**
     * 获取秒杀列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-15
     * Time: 17:00
     */
    public function getList()
    {
        return $this->where('mark', '=', 1)
            ->where('store_id','=',STORE_ID)
            ->order(['add_time' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);

    }

    /**
     * 新增记录
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-16
     * Time: 16:23
     */
    public function add($data)
    {
//        dump($data);die;

        if (empty($data['name'])) {
            $this->error = '请输入活动标题';
            return false;
        }
        if (empty($data['start_time'])) {
            $this->error = '请选择活动开始时间';
            return false;
        }
        if (empty($data['end_time'])) {
            $this->error = '请选择活动结束时间';
            return false;
        }
        if(!isset($data['spike_goods']) || empty($data['spike_goods'])){
            $this->error = '请选择秒杀商品';
            return false;
        }
        if(strtotime($data['start_time']) >= strtotime($data['end_time'])){
            $this->error = '结束时间应大于开始时间';
            return false;
        }
        $data['type'] = !empty($data['type']) ? implode(',',$data['type']) : '';
        $data['store_id'] = STORE_ID;
        $data['creater_user'] = USER_ID;
        $this->startTrans();
        try {
            // 添加活动
            $this->allowField(true)->save($data);
            // 商品规格
            $this->addActivityGoods($data['spike_goods']);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 更新记录
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-22
     * Time: 10:21
     */
    public function edit($data)
    {
        if (empty($data['name'])) {
            $this->error = '请输入活动标题';
            return false;
        }
        if (empty($data['start_time'])) {
            $this->error = '请选择活动开始时间';
            return false;
        }
        if (empty($data['end_time'])) {
            $this->error = '请选择活动结束时间';
            return false;
        }
        if(!isset($data['spike_goods']) || empty($data['spike_goods'])){
            $this->error = '请选择秒杀商品';
            return false;
        }
        if(strtotime($data['start_time']) >= strtotime($data['end_time'])){
            $this->error = '结束时间应大于开始时间';
            return false;
        }
        $data['type'] = !empty($data['type']) ? implode(',',$data['type']) : '';
        $this->startTrans();
        try {
            // 更新秒杀活动
            $this->allowField(true)->save($data);
            // 商品
            $this->addActivityGoods($data['spike_goods'],true);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     *  软删除活动
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-15
     * Time: 17:07
     */
    public function setDelete()
    {
        return $this->save(['mark' => 0]);
    }

    /**
     *  修改活动状态
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-15
     * Time: 17:07
     */
    public function setState($state)
    {
        return $this->save(['status' => $state ? 1 : 2]) !== false;
    }

    /**
     * 添加秒杀活动产品
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-15
     * Time: 15:54
     */
    private function addActivityGoods($goods,$isUpdate = false)
    {
        $model = new SpikeGoods();
        $isUpdate && $model->remove($this['id']);
        foreach ($goods as &$v){
            $v['reduce'] = bcsub($v['goods_price'],$v['discount_price'],2);
            $v['creater_user'] = USER_ID;
        }
        $this->goodsId()->saveAll($goods);
    }

    /**
     * 格式化数据
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-21
     * Time: 16:47
     */
    public function formatData(){
        $time = [
            1 => [],
            5 => [],
            10 => [],
            15 => [],
            20 => []
        ];
        foreach ($this['spike_goods'] as $k => $v){
            $time[$v['time_point']][] = $v;
        }
        $this['format_data'] = $time;

        return $this;
    }

}