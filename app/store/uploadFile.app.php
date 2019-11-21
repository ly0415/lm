<?php
/**
 * 图片上传类
 */
class UploadFileApp extends BaseStoreApp{
	
	public function __construct() {
		parent::__construct();
	}
	public function __destruct() {}

    /**
     *单图片上传
     * @author zhangkx
     * @date 2019/3/27
     */
    public function uploadImg()
    {
        if (IS_POST) {
            $uplodename = $_REQUEST['uplodename'] ?: 'image_file';
            $savePath = !empty($_REQUEST['save_path']) ? htmlspecialchars(trim($_REQUEST['save_path'])) : '';
            $size_limit = !empty($_REQUEST['size_limit']) ? htmlspecialchars(trim($_REQUEST['size_limit'])) : '';//图片大小限制，单位KB
            $fileInfo = $_FILES[$uplodename];
            $fileName = $fileInfo['name'];     //获取上传的文件名
            $filePath = $fileInfo['tmp_name'];    //临时文件文件路径
            $size = number_format($fileInfo['size'] / 1024, 2, '.', '');
            if (!$fileName) {
                $this->setData(array(), '0', '请选择图片！');
            }
            $type = strtolower(substr(strrchr($fileName, '.'), 1)); //获取文件类型
            $imagetype = array(
                'jpg', 'png', 'jpeg', 'gif', 'JPG', 'PNG', 'JPEG', 'GIF'
            );
            if (!in_array($type, $imagetype)) {
                $this->setData(array(), '0', '图片的格式不对!');
            }
            if ($size_limit && ($size > $size_limit)) {
                $this->jsonError('请上传小于' . $size_limit . 'KB的图片!');
            }
            //判断文件夹是否存在否则创建
            $savePath = "upload/images/" . $savePath . date("Ymd");
            if (!file_exists($savePath)) {
                @mkdir($savePath, 0777, true);
                @chmod($savePath, 0777);
                @exec("chmod 777 {$savePath}");
            }
            $url = $savePath . '/' . uniqid() . rand(10000,99999) . '.' . $type;
            if (!is_uploaded_file($filePath)) {
                $this->setData(array(), '0', '临时文件错误');
            }
            //上传文件
            if (!move_uploaded_file($filePath, $url)) {
                $this->setData(array(), '0', '上传失败');
            }
            //返回信息
            $info = array("status" => "success", "message" => "修改成功", "imgurl" => $url);
            $this->setData($info, '1', '上传成功');
        } else {
            $this->setData(array(), '0', '系统错误');
        }
    }
}