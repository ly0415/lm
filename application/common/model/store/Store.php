<?php

namespace app\common\model\store;

use app\common\model\BaseModel;

/**
 * 商家门店模型
 * Class Shop
 * @package app\common\model\store
 */
class Store extends BaseModel
{
    protected $name = 'store';

    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'add_time';
    protected $updateTime = false;

    /**
     * 追加字段
     * @var array
     */
//    protected $append = ['region'];

    /**
     * 关联文章封面图
     * @return \think\model\relation\HasOne
     */
    public function logo()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->hasOne("app\\{$module}\\model\\UploadFile", 'file_id', 'logo_image_id');
    }

    /**
     * 地区名称
     * @param $value
     * @param $data
     * @return array
     */
//    public function getRegionAttr($value, $data)
//    {
//        return [
//            'province' => RegionModel::getNameById($data['province_id']),
//            'city' => RegionModel::getNameById($data['city_id']),
//            'region' => $data['region_id'] == 0 ? '' : RegionModel::getNameById($data['region_id']),
//        ];
//    }

    /**
     * 门店详情
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-22
     * Time: 17:41
     */
    public static function detail($store_id)
    {
        return static::get($store_id,['storeBusiness']);
    }


    /**
     * 门店业务类型
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-16
     * Time: 18:30
     */
    public function storeBusiness(){
        return $this->hasOne('StoreBusiness','store_id','id');
    }

    /**
     * 轮播图图片
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-22
     * Time: 17:53
     */
    public function getBackgroundImgAttr($value){
        return unserialize($value);
    }

    /**
     * 地址省市区
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-22
     * Time: 18:01
     */
    public function getStoreAddressAttr($val){
        return ['text'=>explode('_',$val),'value'=>$val];
    }

}