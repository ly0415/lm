<?php

namespace app\common\model;

/**
 * 文章模型
 * Class Article
 * @package app\common\model
 */
class StoreBargain extends BaseModel
{
    protected $name = 'store_bargain';


    /**
     * 获取正在进行中的砍价产品
     * @param string $field
     */
    public function activityGoods(){
        return $this->hasMany('ActivityGoods','store_bargain_id','id');
    }

    /**
     * 关联商品
     * @return \think\model\relation\HasOne
     */
    public function user()
    {
        return $this->hasOne('StoreUser', 'id', 'add_user')->bind(['user_name']);
    }
    /**
     * 砍价详情
     * @param $id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($id)
    {
//        $model = self::get($id, ['goods.image.file']);
        $model = self::get($id, ['activityGoods.goods']);
        return $model;
        dump($model->toArray());die;
    }

    /**
     * 设置开始时间为时间戳
     * @param $start_time
     * @return null|static
     * @throws \think\exception\DbException
     */
    public function setStartTimeAttr($val){
        return strtotime($val);
    }

    /**
     * 设置结束时间为时间戳
     * @param $stop_time
     * @return null|static
     * @throws \think\exception\DbException
     */
    public function setEndTimeAttr($val){
        return strtotime($val);
    }

    /**
     * 关联活动图片表
     * @return \think\model\relation\HasMany
     */
//    public function image()
//    {
//        return $this->hasMany('ActivityImage','store_bargain_id','id')->order(['id' => 'asc']);
//    }


    /**
     * 关联活动商品表
     * @return \think\model\relation\HasMany
     */
    public function goodsId()
    {
        return $this->hasMany('ActivityGoods')->order(['id' => 'asc']);
    }

}
