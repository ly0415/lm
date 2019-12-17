<?php

namespace app\common\library\storage\engine;

/**
 * 本地文件驱动
 * Class Local
 * @package app\common\library\storage\drivers
 */
class Local extends Server
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 上传图片文件
     * @return array|bool
     */
    public function upload($date,$m)
    {
        // 上传目录
        $uplodDir = WEB_PATH . 'uploads/big/'.BUSINESS_ID.'/'.$m.'/'.$date.'/';
        $uplodSmall = WEB_PATH . 'uploads/small/'.BUSINESS_ID.'/'.$m.'/'.$date.'/';

        // 验证文件并上传
        $info = $this->file->validate(['size' => 4 * 1024 * 1024, 'ext' => 'jpg,jpeg,png,gif'])
            ->move($uplodDir, $this->fileName);
        if (empty($info)) {
            $this->error = $this->file->getError();
            return false;
        }
        $image = \think\Image::open($uplodDir . DS. $this->fileName);
        // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.png
        // 检测目录
        if (false === $this->checkPath($uplodSmall)) {
            return false;
        }
        $image->thumb(150,150,\think\Image::THUMB_CENTER)->save($uplodSmall.$this->fileName);
        return true;
    }

    /**
     * 删除文件
     * @param $fileName
     * @return bool|mixed
     */
    public function delete($fileName)
    {
        // 文件所在目录
        $filePath = WEB_PATH . "uploads/{$fileName}";
        return !file_exists($filePath) ?: unlink($filePath);
    }

    /**
     * 返回文件路径
     * @return mixed
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * 检查目录是否可写
     * @access protected
     * @param  string $path 目录
     * @return boolean
     */
    protected function checkPath($path)
    {


        if (is_dir($path) || mkdir($path, 0755, true)) {
            return true;
        }

        $this->error = ['directory {:path} creation failed', ['path' => $path]];

        return false;
    }

}
