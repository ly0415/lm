<?php

namespace app\store\model;

use app\common\model\Business as BusinessModel;

/**
 * 业务类型模型
 * @author  fup
 * @date    2019-08-23
 */
class Business extends BusinessModel
{
    /**
     * 获取全部业务类型列表
     * @author  fup
     * @date    2019-08-23
     */
    public  function getListAll($where = []){
        $list = $this->where(['mark'=>1])
            ->where($where)
            ->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->select();
        return $list;
    }

    /**
     * 添加业务类型
     * @author  fup
     * @date    2019-08-23
     */
    public function add($data)
    {
        $data['level'] = 1;
        if(!$data['name']){
            $this->error = '请选择业务名称';
            return false;
        }
        if($data['pid'] > 0){

            $data['level'] = 2;
        }
        if($data['b_pid_3']){
            if($this->where('name','=',$data['name'])
                ->where('cate_id','=',$data['b_pid_3'])->find()){
                $this->error = '业务类型相对应的商品分类已经存在';
                return false;
            }
        }
        $data['cate_id'] = $data['b_pid_3'];
        $data['create_user'] = USER_ID;
        return $this->allowField(true)->save($data);
    }

    /**
     * 编辑业务类型
     * @author  fup
     * @date    2019-08-23
     */
    public function edit($data)
    {
        $data['level'] = 1;
        if(!$data['name']){
            $this->error = '请选择业务名称';
            return false;
        }
        if($data['pid'] > 0){

            $data['level'] = 2;
        }
        if($data['b_pid_3']){
            if($this->where('name','=',$data['name'])
                ->where('cate_id','=',$data['b_pid_3'])->find()){
                $this->error = '业务类型相对应的商品分类已经存在';
                return false;
            }
        }
        $data['cate_id'] = $data['b_pid_3'];
        $data['update_user'] = USER_ID;
        return $this->allowField(true)->save($data) !== false;
    }

    /**
     * 业务类型软删除
     * @author  fup
     * @date    2019-08-23
     */
    public function setDelete()
    {
        return $this->save(['mark'=>0]);
    }

    /**
     * 递归查询
     * @author  fup
     * @date    2019-08-23
     */
    public function tree($array, $pid = 0 )
    {
        $tree = array();
        foreach ($array as $key => $value) {
            if ($value['pid'] == $pid) {
                $value['child'] = $this->tree($array, $value['id']);
                if (!$value['child']) {
                    unset($value['child']);
                }
                $tree[] = $value;
            }
        }
        return $tree;
    }

    public static function getBusinessByCateId($category_id = 0){
        $business = [];
        $all = self::getCacheTree();
        foreach ($all as $k => $first){
            if(isset($first['child'])){
                foreach ($first['child'] as $two){
                    if($category_id > 0 && $two['cate_id'] == $category_id){
                        $business = [
                            'first'=>['id'=>$first['id'],'name'=>$first['name'],'sort'=>$first['sort'],'create_time'=>$first['create_time']],
                            'two'=>['id'=>$two['id'],'name'=>$two['name'],'sort'=>$two['sort'],'create_time'=>$two['create_time']]
                        ];
                    }
                }
            }

        }
        return $business;
    }

}
