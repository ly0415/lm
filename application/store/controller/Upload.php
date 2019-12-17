<?php

namespace app\store\controller;

use app\store\model\UploadFile;
use app\common\library\storage\Driver as StorageDriver;
use app\store\model\Setting as SettingModel;
use think\Db;

/**
 * 文件库管理
 * Class Upload
 * @package app\store\controller
 */
class Upload extends Controller
{
    private $config;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        // 存储配置信息
        $this->config = SettingModel::getItem('storage');
    }

    /**
     * 图片上传接口
     * @param int $group_id
     * @return array
     * @throws \think\Exception
     */
    public function image($group_id = -1,$moudle = null)
    {
        // 实例化存储驱动
        $StorageDriver = new StorageDriver($this->config);
        // 设置上传文件的信息
        $StorageDriver->setUploadFile('iFile');
        $date = date('Ymd');
        // 上传图片
        if (!$StorageDriver->upload($date,$moudle)) {
            return json(['code' => 0, 'msg' => '图片上传失败' . $StorageDriver->getError()]);
        }

        // 图片上传路径
        $fileName = $StorageDriver->getFileName();
        // 图片信息
        $fileInfo = $StorageDriver->getFileInfo();

        // 添加文件库记录
        $uploadFile = $this->addUploadFile($group_id,BUSINESS_ID.'/'.$moudle.'/'.$date. '/' . $fileName, $fileInfo, 'image');
        // 图片上传成功
        return json(['code' => 1, 'msg' => '图片上传成功', 'data' => $uploadFile]);
    }

    /**
     * 添加文件库上传记录
     * @param $group_id
     * @param $fileName
     * @param $fileInfo
     * @param $fileType
     * @return UploadFile
     */
    private function addUploadFile($group_id, $fileName, $fileInfo, $fileType)
    {
        // 存储引擎
        $storage = $this->config['default'];
        // 存储域名
        $fileUrl = isset($this->config['engine'][$storage]['domain'])
            ? $this->config['engine'][$storage]['domain'] : '';
        // 添加文件库记录
        $model = new UploadFile;
        $model->add([
            'group_id' => $group_id > 0 ? (int)$group_id : 0,
            'storage' => $storage,
            'file_url' => $fileUrl,
            'file_name' => $fileName,
            'file_size' => $fileInfo['size'],
            'file_type' => $fileType,
            'extension' => pathinfo($fileInfo['name'], PATHINFO_EXTENSION),
        ]);
        return $model;
    }

    /**
     * 生成缩略图
     * @access protected
     * @param  string $dir 目录
     * @return boolean
     */
    public function listDir($dir = '')
    {
        // 上传目录
        set_time_limit(0);
//        $uplodSmall = WEB_PATH . 'uploads/small/';
        $dir = $dir ? $dir : WEB_PATH . 'uploads/big';
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ((is_dir($dir . "/" . $file)) && $file != "." && $file != "..") {
                        $this->listDir($dir . "/" . $file . "/");
                    } else {
                        if ($file != "." && $file != "..") {
                            $s_dir = str_replace('big','small',$dir);
                            if(file_exists($s_dir . $file)){
                                continue;
                            }
                            $image = \think\Image::open($dir.$file);
                            $this->checkPath($s_dir);
                            // 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.png
                            $image->thumb(50,50,\think\Image::THUMB_SCALING)->save($s_dir . $file);
                            sleep(1);
                        }
                    }
                }
                closedir($dh);
            }
        }
    }

    /**
     * 获取原始图
     * @access public
     * @param  string $path 目录
     * @return boolean
     */
    public function getImage(){
//        $uplodSmall = WEB_PATH;
        $uplodSmall = WEB_PATH . 'uploads/big/';
        $img = Db::table('bs_goods')->field('goods_id,original_img')->select();
        foreach ($img as $v){
            $arr = explode('/',$v['original_img']);
            if(!file_exists(WEB_PATH . $v['original_img'])){
                continue;
            }
            $image = \think\Image::open(WEB_PATH . $v['original_img']);

//将图片裁剪为300x300并保存为crop.png
            $this->checkPath($uplodSmall . $arr[count($arr)-2] . '/');

            $image->save($uplodSmall . $arr[count($arr)-2] . '/' . $arr[count($arr)-1]);

        }
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
