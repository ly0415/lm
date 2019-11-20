<?php

namespace app\common\model;

/**
 * 商品图片模型
 * Class GoodsImage
 * @package app\common\model
 */
class SpikeGoods extends BaseModel
{
    protected $name = 'spike_goods';
    protected $updateTime = false;
    protected $createTime = 'add_time';

    /**
     * 时间段
     */
    public static $time = [
        1   =>  '08',
        5   =>  '10',
        10  =>  '12',
        15  =>  '14',
        20  =>  '16'
    ];

        //活动状态
    public static $status = ['即将开始','抢购中','已结束'];

    /**
     * 关联商品
     * @return \think\model\relation\BelongsTo
     */
    public function goods()
    {
        return $this->belongsTo('StoreGoods', 'store_goods_id', 'id')->field('id,goods_name,original_img,store_id,store_name,is_on_sale,attributes')->where('mark','=',1);
    }

    /**
     * 关联活动
     * @return \think\model\relation\BelongsTo
     */
    public function activity()
    {
        return $this->belongsTo('SpikeActivity', 'spike_id', 'id');
    }
    /**
     * 关联商品图片表
     * @return \think\model\relation\HasMany
     */
    public function image()
    {
        return $this->hasMany('GoodsImage','goods_id','goods_id');
    }
    /**
     * 关联文件库
     * @return \think\model\relation\BelongsTo
     */
    public function file()
    {
        return $this->belongsTo('UploadFile', 'image_id', 'file_id')
            ->bind(['file_path', 'file_name', 'file_url']);
    }

    /**
     * 关联秒杀活动
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-14
     * Time: 11:51
     */
    public function spike(){
        return $this->belongsTo('Spike','spike_id','id');
    }
}
