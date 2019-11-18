<?php

namespace app\store\model;

use app\common\model\StoreConsole as StoreConsoleModel;

/**
 * 控制管理
 * Class StoreConsole
 * @package app\store\model
 */
class StoreConsole extends StoreConsoleModel
{

    /**
     * 获取店铺控制管理数据
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-27
     * Time: 14:24
     */
    public function getList()
    {
        $list = $this->where('mark', '=', 1)
            ->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->select();
        if($list)$list = $list->toArray();
        $data = $this->tree($list);
        return $data;
    }

    /**
     * 根据type获取对应数据
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-27
     * Time: 14:24
     */
    public static function detail($type = 1)
    {
        return self::with('coupon')
            ->where('type','=',$type)
            ->where('mark','=',1)
            ->find();
    }

    /**
     * 获取注册优惠券
     * author fup
     * date 2019-07-11
     */
    public function getInfo($where = []){
        return $this->field('id,create_time,mark',true)
            ->where($where)
            ->find();
    }

    /**
     * 获取所有数据
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-27
     * Time: 14:25
     */
    public  function getListAll($where = []){
        $list = $this->where(['mark'=>1])
            ->where($where)
            ->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->select();
        return $list;
    }

    /**
     * 添加记录
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-27
     * Time: 14:25
     */
    public function add($storeId,$type)
    {
        $data['relation_1'] = implode(',',[$storeId]) ;
        $data['type'] = $type;
        $data['status'] = 2;
        return $this->allowField(true)->save($data);
    }

    /**
     * 添加注册领取抵扣券新记录
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-27
     * Time: 14:26
     */
    public function addConsole($data,$_data = [])
    {
        $data['type'] = 1;
        $data['status'] = 1;
        $data[$data['key']] = $data['val'];
        $this->startTrans();
        try{
            $this->where(['type'=>1,'mark'=>1])
                ->update(['mark'=>0]);
            $this->allowField(true)->save(array_merge($_data,$data));
            $this->commit();
            return true;
        }catch (\Exception $e){
            $this->rollback();
            return false;
        }
    }

    /**
     * 添加注册领取抵扣券新记录
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-27
     * Time: 14:26
     */
    public function addCoupon($data)
    {   $data[$data['key']] = $data['val'];
        $data['type'] = 1;
        $data['status'] = 1;
        $this->allowField(true)->save($data);
    }

    /**
     * 设置客服开关
     * author fup
     * date 2019-07-25
     */
    public function addKeFu($data){
        $data['type'] = 4;
        $this->delCoupon(4);
        return $this->allowField(true)->save($data);
    }

    /**
     * 设置文章抵扣卷
     * author fup
     * date 2019-07-16
     */
    public function addArticleCoupon($data){
//        dump($data);die;
        if(!$data['coupon_id']){
            $this->error = '请选择抵扣卷';
            return false;
        }
        if (!isset($data['relation_2']) || empty($data['relation_2'])) {
            $this->error = '请上传背景图片';
            return false;
        }
        $data['relation_1'] = serialize($data);
        $data['type'] = 2;
        $this->delCoupon(2);
        return $this->allowField(true)->save($data);
    }

    public function delCoupon($type){
        $this->where('mark','=',1)
            ->where('type','=',$type)
            ->update(['mark'=>0]);
    }

    /**
     * 编辑店铺余额支付记录
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-27
     * Time: 14:27
     */
    public function edit($store_id)
    {
        $relation_1 = explode(',',$this->relation_1);
        array_push($relation_1,$store_id);
        $data['relation_1'] = implode(',',$relation_1);
        return $this->allowField(true)->save($data) !== false;
    }

    /**
     * 软删除
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-27
     * Time: 14:27
     */
    public function setDelete($store_id)
    {
        $relation_1 = explode(',',$this->relation_1);
        $data['relation_1'] = implode(',',array_diff($relation_1,[$store_id])) ? : NULL;
        return $this->allowField(true)->save($data) !== false;

    }

    /**
     * 设置新用户注册领取抵扣卷天数
     * author fup
     * date 2019-07-11
     */
    public function setDel()
    {
        return $this->where('type','=',1)
            ->where('mark','=',1)
            ->update(['mark'=>0]) !== false;
    }

    /**
     * 递归查询
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-27
     * Time: 14:27
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

    /**
     * 添加修改店铺配送折扣type：5
     * Created by PhpStorm.
     * $param $type 类型
     * Author: fup
     * Date: 2019-08-27
     * Time: 14:28
     */
    public function addStorePercent($data, $_data = []){
        //设置当前门店打印人员
        if(empty($data['old_printer'])){  //新增
            $this->insert([
                'type'          => 11,
                'status'        => 1,
                'relation_1'    => STORE_ID,
                'relation_2'    => $data['printer'],
                'create_time'   => time()
            ]);
        } elseif($data['old_printer'] && ($data['printer'] != $data['old_printer'])) {
            $this->save(['relation_2'=>$data['printer']], ['id' => $data['printer_id']]);
        }

        if($data['percent'] != $data['old_percent']) {
            $this->where(['type'=>5])->update(['mark'=>0]);
            unset($data['printer']);
            unset($data['printer_id']);
            unset($data['old_printer']);
            $data['relation_1'] = $this->createData($data,$_data);
            $data['type'] = 5;
            $data['status'] = 1;
            return $this->allowField(true)->save($data);
        }
    }

    /**
     * 组装新的relation_1数据
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-27
     * Time: 15:20
     */
    public function createData($data,$_data){
        if(empty($_data)){
            $data['relation_1'] = [STORE_ID=>$data['percent']];
        }else{
            isset($_data[STORE_ID]) && $data['relation_1'] = $_data[STORE_ID] = $data['percent'];
            !isset($data[STORE_ID]) && $data['relation_1'] = $_data + [STORE_ID=>$data['percent']];
        }
        return serialize($data['relation_1']);
    }

    /**
     * 获取店员打印权限
     * author   luffy
     * date     2019-10-29
     */
    public function getPrintAuth($user_id){
        //获取当前门店打印设置
        $info = $this::get(['relation_1'=>STORE_ID, 'type'=>11]);
        if(isset($info) && $info['relation_2'] == $user_id){
            return true;
        }
        return false;
    }
}
