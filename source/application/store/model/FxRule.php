<?php

namespace app\store\model;

use app\common\model\FxRule as FxRuleModel;

/**
 * 分销规则
 * @author  fup
 * @date    2019-09-10
 */
class FxRule extends FxRuleModel{
    /**
     * 分销规则列表显示
     * @author ly
     * @date 2019-10-22
     */
    public function getList($name=''){
        !empty($name) && $this->where('rule_name', 'like', "%$name%");
        return $this->where('mark', '=', 1)
            ->paginate(15, false, [
                'query' => \request()->request()
            ])->each(function ($item){
                return FxRule::getStoreDetail($item);
            });
    }

    /**
     * 分销规则列表添加
     * @author ly
     * @date 2019-10-22
     */
    public function add($data=[])
    {
//        print_r($data);die;
        if(!empty($data['store_ids'])){
            $data['store_id']=implode(',',$data['store_ids']);
        }
        $data['add_time'] = time();
//        print_r($data);die;
        return $this->allowField(true)->save($data);
    }

    /**
     *编辑分销规则  名称未更改mark不变
     * @author ly
     * @date 2019-10-22
     */
    public function edit($id='',$data='')
    {
        $FxUser = FxRule::get($id);
        if(!empty($data['store_id'])){
            $data['store_id']=implode(',',$data['store_id']);
        }
        if($FxUser['rule_name']==$data['rule_name']){
            try {
                $FxUser->allowField(true)->save($data);
                return true;
            } catch (\Exception $e) {
                $this->error = $e->getMessage();
                return false;
            }
        }else{
            $this->startTrans();
            try {
                $data['add_time'] = time();
                $item['mark']=0;
                $FxUser->allowField(true)->save($item);
                $this->allowField(true)->save($data);
                $this->commit();
                return true;
            } catch (\Exception $e) {
                $this->error = $e->getMessage();
                $this->rollback();
                return false;
            }

        }
    }
    /**
     *规则删除
     * @author ly
     * @date 2019-10-22
     */
    public function remove($id)
    {
        $data['mark']=0;
        return  $this->allowField(true)->save($data);
    }

    public function getStore(){
        return Store::getStoreList(true);
    }

    /**
     *获得店铺信息
     * @author ly
     * @date 2019-10-22
     */
    public function getFxRule($id){

        return FxRule::getStoreDetail(FxRuleModel::get($id));
    }

    /**
     *得到所属店铺信息
     * @author ly
     * @date 2019-10-22
     */
    public function getStoreDetail($item=''){
        if(!empty($item['store_id'])){
                $rolename=explode(',',$item['store_id']);
                foreach($rolename as $d){
                    $names[] = Store::getStoreList(true)[$d];
                    $namesl[] = Store::getStoreList(true)[$d]['store_name'];
                    $storeid[]=$d;
                }
                $item['store_name']=$names?$names:'';
                $item['store_names']=$namesl?implode(',',$namesl):'';
                $item['sto_id']=$storeid?$storeid:array();
                }else{
            $item['store_name']='';
            $item['store_names']='';
            $item['sto_id']=array();
        }
                return $item;

    }

}