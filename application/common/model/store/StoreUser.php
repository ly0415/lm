<?php

namespace app\common\model\store;

use think\Db;
use app\common\model\BaseModel;

/**
 * 店铺店员模型
 * @author  fup
 * @date    2019-05-20
 */
class StoreUser extends BaseModel
{
    protected $name = 'store_user';

    /**
     * 判断是否为总站
     * author fup
     * date   2019-07-12
     */
    public function isAdmin($store_user_id){
        $data = $this->with('store')
            ->where('id','=',$store_user_id)
            ->find();
        return isset($data['store']) && $data['store']['store_type'] == 1 ? true : false;
    }

    /**
     * 获取所属店铺
     */
    public function store()
    {
        return $this->belongsTo('Store','store_id','id');
    }

    /**
     * 订单列表
     * @author  luffy
     * @date    2019-07-17
     */
    public function getUserBusiness($store_user_id){
        $business_id = Db::name('store_user_business')->where('store_user_id','=',$store_user_id)->find();
        return (!empty($business_id['business_id']) ? $business_id['business_id'] : 0);
    }

    /**
     * 店员详情
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-11-27
     * Time: 10:52
     */
    public static function detail($id){
        $filter['mark'] = 1;
        if(is_array($id)){
            array_merge($filter,$id);
        }else{
            $filter['id'] = (int)$id;
        }
        return self::get($filter);
    }
}
