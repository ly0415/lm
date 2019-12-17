<?php

namespace app\store\model\shop;

use Think\Db;
use app\common\model\TableNumber    as TableNumberModel;

/**
 *
 * @author  fup
 * @date    2019-12-6
 */
class TableNumber extends TableNumberModel
{

    /**
     *
     * Author:ly
     * Date: 2019-12-06
     * Time: 10:55
     */
    public function getList(){
        return $this->alias('a')
                     ->field('a.id,a.number,a.mark,st.real_name,a.add_time,a.store_id,s.store_name')
                     ->join('store_user st','st.id=a.add_user')
                     ->join('store s','s.id=a.store_id')
                     ->where('a.mark',1)
                     ->where('a.store_id',STORE_ID)
                     ->paginate(15, false, ['query' => \request()->request()]);
    }

    /**
     *t添加
     * @author ly
     * @date 2019-12-6
     */
    public function add($number='')
    {
        if (!is_numeric($number)) {
            $this->error = '桌号大于0';
            return false;
        }

        if (empty($number) || $number < 0) {
            $this->error = '桌号必须大于0';
            return false;
        }
        $numberlist = $this->where(['number'=>$number,'mark'=>1])->find();
        if(!empty($numberlist)){
            $this->error = '桌号已存在!';
            return false;

        }
        $this->startTrans();
        try {
            $data['number']     = $number;
            $data['mark']       = 1;
            $data['store_id']   = STORE_ID;
            $data['add_time']   = time();
            $data['add_user']   = USER_ID;
            $this->allowField(true)->save($data);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     *   删除 软删
     * @author ly
     * @date 2019-12-6
     */
    public function delete($id=''){
        if(empty($id)){
            $this->error = '请选中需要删除的';
            return false;
        }
        $data['mark']=0;
        return $this->where('id',$id)->update($data);

    }
}
