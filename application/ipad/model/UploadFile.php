<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-04
 * Time: 下午 4:15
 */

namespace app\ipad\model;


class UploadFile extends BaseModel
{
    protected $append = ['file_path'];
    /**
     * 获取图片完整路径
     * @param $value
     * @param $data
     * @return string
     */
    public function getFilePathAttr($value, $data)
    {
        if ($data['storage'] === 'local') {
            return self::$base_url . 'uploads/' . $data['file_name'];
        }
        return $data['file_url'] . '/' . $data['file_name'];
    }
}