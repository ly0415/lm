<?php

namespace app\store\model;

use app\common\model\StoreActivity as StoreActivityModel;

/**
 * 规格/属性(组)模型
 * Class Tag
 * @package app\store\model
 */
class StoreActivity extends StoreActivityModel
{

    /**
     * 列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-23
     * Time: 17:44
     */
    public function getList(){

        return $this->where('mark', '=', 1)
            ->order(['id' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ])->each(function ($item){
                $item['store_name'] = Store::getStoreList(true)[$item['store_id']]['store_name'];
                $item['type'] = ['text'=>self::$activity_type[$item['type']],'value'=>$item['type']];
                return $item;
            });
    }

    /**
     * 添加活动
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-23
     * Time: 15:46
     */
    public function add($data)
    {
        if(!isset($data['store_ids']) || empty($data['store_ids'])){
            $this->error = '请选择店铺';
            return false;
        }
        if (!isset($data['thumb']) || empty($data['thumb'])) {
            $this->error = '请上传活动图片';
            return false;
        }
        if (!isset($data['time']) || empty($data['time'])) {
            $this->error = '请选择活动时间范围';
            return false;
        }
        $data['content'] = isset($data['content']) ? $data['content'] : '';
        $time = explode('-',$data['time']);
        $data['start_time'] = strtotime($time[0]);
        $data['end_time'] = strtotime($time[1]);
        $data['add_user'] = USER_ID;
//        dump($data);die;
        $info = array_map(function ($s)use($data){
            $data['store_id'] = $s;
            return $data;
        },$data['store_ids']);
        return $this->allowField(true)->saveAll($info);
    }


    /**
     * 编辑活动
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-11
     * Time: 20:54
     */
    public function edit($data){

        if (!isset($data['thumb']) || empty($data['thumb'])) {
            $this->error = '请上传活动图片';
            return false;
        }
        if (!isset($data['time']) || empty($data['time'])) {
            $this->error = '请选择活动时间范围';
            return false;
        }
        $data['content'] = isset($data['content']) ? $data['content'] : '';
        $time = explode('-',$data['time']);
        $data['start_time'] = strtotime($time[0]);
        $data['end_time'] = strtotime($time[1]);
        $data['add_user'] = USER_ID;
        return $this->allowField(true)->save($data);

    }

    public function setStatus($state)
    {
        return $this->save(['status' => $state ? 1 : 2]) !== false;
    }

    /**
     * 软删除
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-10-12
     * Time: 15:13
     */
    public function setDelete()
    {
        return $this->save(['mark' => 0]);
    }

}
