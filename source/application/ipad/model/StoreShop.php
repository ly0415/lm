<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-04
 * Time: 下午 3:51
 */

namespace app\ipad\model;


class StoreShop extends BaseModel
{
    /**
     * 获取店铺信息
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getInfo($store_id)
    {
        return $this->with('file')->where(['shop_id'=>$store_id])->find();

    }
    /**
     * 关联文件库
     * @return \think\model\relation\BelongsTo
     */
    public function file()
    {
        return $this->belongsTo('UploadFile', 'logo_image_id', 'file_id')
            ->bind(['file_path', 'file_name', 'file_url']);
    }
}