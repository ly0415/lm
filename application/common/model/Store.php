<?php

namespace app\common\model;
use think\Cache;

/**
 * 店铺模型
 * @author  luffy
 * @date    2019-08-1
 */
class Store extends BaseModel{

	protected $name = 'store';
	protected $createTime = 'add_time';
	protected $updateTime = 'modity_time';

    public $is_open = [
        1   =>  '开启',
        2   =>  '关闭'
    ];

    /**
     * 获取门店列表
     * author fup
     * date 2019-08-05
     */
    public static function getStoreList($isSelf = false, $business_id = 0, $fields = ['id','store_name'], $store_cate_id = 17){
        $store = [];
        $data = self::getCacheAll();
        if(!is_array($data) || empty($data)){
            return [];
        }
        foreach ($data as $k => $val){
            if($val['store_cate_id'] == $store_cate_id){
                if(!empty($fields)){
                    $tmp = [];
                    foreach ($fields as $f){
                        isset($val[$f]) && $tmp[$f] = $val[$f];
                    }
                    $store[$k] = $tmp;
                }else{
                    $store[$k] = $val;
                }
                if(!$isSelf && $val['store_type'] == 1){
                    unset($store[$k]);
                }
                if(!empty($business_id) && isset($val['business_id']) && $business_id !=  $val['business_id']){
                    unset($store[$k]);
                }
            }
        }
        return $store;
    }

    /**
     * 重置所有店铺缓存
     * @author  luffy
     * @date    2019-09-05
     */
    public static function resetCache(){
        Cache::clear('store');
        return self::getStoreCache();
    }

	/**
	 * 获取所有店铺
	 * @author  luffy
	 * @date    2019-08-1
	 */
	public static function getCacheAll(){
		return self::getStoreCache()['all'];
	}

	/**
	 * 所有店铺
	 * @author  luffy
	 * @date    2019-08-1
	 */
	public static function getStoreCache(){
		$model = new static;
		if (!Cache::get('store')) {
			$data 	= $model->order(['sort' => 'ASC', 'add_time' => 'DESC'])->select();
			$all = [];
			if(!empty($data)){
				foreach($data->toArray() as $key => $value){
					$all[$value['id']] = $value;
				}
			}
			Cache::tag('store')->set('store', compact('all'));
		}
		return Cache::get('store');
	}

    /**
     * 获取列表页面非超管后台的店铺ID，业务品牌的是第一个店铺
     * @author  luffy
     * @date    2019-08-1
     */
    public static function getListStoreId(){
        $store_id = 0;
         if(T_GENERAL){
             $store_id = STORE_ID;
         }else{
             $all = self::getStoreList(TRUE, BUSINESS_ID);
             if($all){
                 $store_id = current($all)['id'];
             }
         }
         return $store_id;
    }

    /**
     * 获取店铺折扣
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-22
     * Time: 10:20
     */
	public static function getStoreDiscount($store_id = null){
	    $store_discount = 1;
        $storeInfo = self::getStoreList(true,'',['id','store_discount']);
        isset($storeInfo[$store_id ? :STORE_ID]) && $store_discount = $storeInfo[$store_id ? : STORE_ID]['store_discount'];
        return $store_discount;
    }

}