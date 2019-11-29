<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-03
 * Time: 下午 1:55
 */

namespace app\store\model;

use app\common\model\StoreSource as StoreSourceModel;

class StoreSource extends StoreSourceModel
{
    /**
     * 来源列表显示（去重）
     * @author
     * @date 2019-10-22
     */
//    public function getList($where = []){
//        if(!IS_ADMIN){
//            $this->where('store_id','=',STORE_ID);
//        }
//        return array_unique($this->field('id,name')->column('name','id'));
//    }
    /**
     * 来源列表显示
     * @author ly
     * @date 2019-10-22
     */
    public function getList($sourcename=''){
        // 检索条件：来源名称
        !empty($sourcename) && $this->where('name', 'like', "%$sourcename%");
        return $this->order(['sort' => 'asc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }


    /**
     * 获取所有数据
     * @author  luffy
     * @date    2019-10-22
     */
    public function getAll(){
        return $this->where(['mark'=>1])->order(['sort' => 'ASC'])->column('id, img');
    }

    /**
     * 来源列表添加
     * @author ly
     * @date 2019-10-22
     */
    public function add($data)
    {
        !array_key_exists('img', $data) && $data['img'] = '';
        $data['add_time'] = time();
        return $this->allowField(true)->save($data);
    }

    /**
     * 来源列表编辑
     * @author ly
     * @date 2019-10-22
     */
    public function edit($data)
    {
        !array_key_exists('img', $data) && $data['img'] = '';
        $data['add_time'] = time();
        return $this->allowField(true)->save($data) !== false;
    }

    /**
     * 来源列表删除
     * @author ly
     * @date 2019-10-22
     */
    public function remove($source_id)
    {
        return $this->delete();
    }
}