<?php

namespace app\common\model;
use app\store\model\Store as StoreModel;

/**
 * 业务分类
 * Class StoreConsole
 * @package app\common\model
 */
class StoreConsole extends BaseModel
{
    protected $name = 'store_console';

    protected $updateTime = false;

    public static $consoleType = [
        '1' => '注册领取抵扣券',
        '2' => '文章抵扣卷领取',
        '3' => '店铺余额支付开关',
        '4' => '客服开关'
    ];

    protected $append = ['big_file_path','small_file_path','closed_store'];

    /**
     * 关联电子卷表
     * author fup
     * date 2019-07-11
     */
    public function coupon(){
        return $this->belongsTo('Coupon','type','is_special');
    }

    /**
     * 反序列化
     * author fup
     * date 2019-07-16
     */
    public function getRelation_1Attr($value,$data){
       if($data['type'] == 2 || $data['type'] == 5){
           return unserialize($value);
       }
//       else if ($data['type'] == 3 && isset($data['relation_1'])){
////           dump($data);die;
//           return (new StoreModel)->getStoreInfo(['id'=>['in',explode(',',$data['relation_1'])]]);
//       }
       return $value;
    }

    public function getBigFilePathAttr($value,$data){
       return 'uploads/big/' . $data['relation_2'];
    }
    public function getSmallFilePathAttr($value,$data){
        return 'uploads/small/' . $data['relation_2'];
    }

    /**
     * 已关闭店铺
     * author fup
     * date 2019-07-16
     */
    public function getClosedStoreAttr($value,$data){
        if ($data['type'] == 3 && isset($data['relation_1'])){
//           dump($data);die;
           return (new StoreModel)->getStoreInfo(['id'=>['in',explode(',',$data['relation_1'])],'store_cate_id'=>17]);
       }
       return [];
    }

    /**
     * 关联图片库表
     * @return \think\model\relation\BelongsTo
     */
    public function image()
    {
        return $this->belongsTo('UploadFile','relation_2','file_id');
    }



    /**
     * 获取店铺运费折扣
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-27
     * Time: 16:48
     */
    public static function getStorePercent($storeId = 0){
        $data = self::detail(5);
        if($data && isset($data['relation_1'][$storeId])){
            return $data['relation_1'][$storeId] / 100;
        }
        return 1;
    }

    /**
     * 获取控制管理数据
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-27
     * Time: 16:57
     */
    public static function detail($type = 1){
        return self::where('type','=',$type)
            ->where('mark','=',1)
            ->find();
    }

}
