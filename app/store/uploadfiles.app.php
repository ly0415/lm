<?php
/**
 * 文件上传
 * @author jh
 * @date 2017-08-29
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class UploadfilesApp extends BaseApp
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
    }

    /**
     * 数据封装
     * @author lvji
     * @param $status 表示返回数据状态
     * @param $message 对返回状态说明
     * @param $info 返回数据信息
     * @date 2015-03-10
     */
    public function setData($info = array(), $status = 'success', $message = 'ok')
    {
        $data = array(
            'status' => $status,
            'message' => $message,
            'info' => $info,
        );
        echo json_encode($data);
        exit();
    }

    /**
     * 返回正确消息
     * @author jh
     * @date 2017/06/22
     */
    public function jsonResult($message = 'ok', $info = array())
    {
        $this->setData($info, 1, $message);
    }

    /**
     * 返回错误消息
     * @author jh
     * @date 2017/06/22
     */
    public function jsonError($message = 'error', $info = array())
    {
        $this->setData($info, 0, $message);
    }

    /**
     *单图片上传
     * @author jh
     * @date 2017-08-29
     */
    public function uploadImg()
    {
        if (IS_POST) {
            $uplodename = $_REQUEST['uplodename'] ?: 'image_file';
            $fileInfo = $_FILES[$uplodename];
            $fileName = $fileInfo['name'];     //获取上传的文件名
            $filePath = $fileInfo['tmp_name'];    //文件路径
            if (!$fileName) {
                $this->jsonError('请选择图片！');
            }
            $type = strtolower(substr(strrchr($fileName, '.'), 1)); //获取文件类型
            $imagetype = array(
                'jpg', 'png', 'jpeg', 'gif', 'JPG', 'PNG', 'JPEG', 'GIF'
            );
            if (!in_array($type, $imagetype)) {
                $this->jsonError('图片的格式不对!');
            }
            // 判断文件夹是否存在否则创建
            $savePath = "upload/images/" . date("Ymd");
            if (!file_exists($savePath)) {
                @mkdir($savePath, 0777, true);
                @chmod($savePath, 0777);
                @exec("chmod 777 {$savePath}");
            }
            $url = $savePath . '/' . time() . rand(100,999) . '.' . $type;
            if (!is_uploaded_file($filePath)) {
                $this->jsonError('临时文件错误');
            }
            //上传文件
            if (!move_uploaded_file($filePath, $url)) {
                $this->jsonError('上传失败');
            }
            //返回信息
            $info = array("status" => "success", "message" => "修改成功", "imgurl" => $url);
            $this->jsonResult('上传成功', $info);
        } else {
            $this->jsonError('系统错误');
        }
    }

    /**
     *多图片上传
     * @author jh
     * @date 2017-08-29
     */
    public function uploadImgList()
    {
        if (IS_POST) {
            $uplodename = $_REQUEST['uplodename'] ?: 'list_image_file';
            $fileInfo = $_FILES[$uplodename];
            $fileName = $fileInfo['name'];     //获取上传的文件名
            $filePath = $fileInfo['tmp_name'];    //文件路径
            $fileSize = $fileInfo['size'];    //文件大小
            $imagetype = array(
                'jpg', 'png', 'jpeg', 'gif', 'JPG', 'PNG', 'JPEG', 'GIF'
            );
            $imgurls = array();
            foreach ($fileName as $k => $v) {
                if (!$v) {
                    continue;
                }
                $type = strtolower(substr(strrchr($v, '.'), 1)); //获取文件类型
                if (!in_array($type, $imagetype)) {
                    $this->jsonError('图片的格式不对!');
                }
                // 判断文件夹是否存在否则创建
                $savePath = "upload/images/" . date("Ymd");
                if (!file_exists($savePath)) {
                    @mkdir($savePath, 0777, true);
                    @chmod($savePath, 0777);
                    @exec("chmod 777 {$savePath}");
                }
                $url = $savePath . '/' . time() . rand(100,999) . '.' . $type;
                if (!is_uploaded_file($filePath[$k])) {
                    $this->jsonError('临时文件错误');
                }
                //上传文件
                if (!move_uploaded_file($filePath[$k], $url)) {
                    $this->jsonError('上传失败');
                }
                $imgurls[] = $url;
            }
            if (empty($imgurls)) {
                $this->jsonError('请选择图片!');
            }
            $info = array("status" => "success", "message" => "修改成功", "imgurl" => $imgurls);
            $this->jsonResult('上传成功', $info);
        } else {
            $this->jsonError('系统错误');
        }
    }

    /**
     *单文件上传
     * @author jh
     * @date 2017-08-29
     */
    public function uploadFile()
    {
        if (IS_POST) {
            $uplodename = $_REQUEST['uplodename'] ?: 'uploadfile';
            $fileInfo = $_FILES[$uplodename];
            $fileName = $fileInfo['name'];     //获取上传的文件名
            $filePath = $fileInfo['tmp_name'];    //文件路径
            if (!$fileName) {
                $this->jsonError('请选择文件！');
            }
            $type = strtolower(substr(strrchr($fileName, '.'), 1)); //获取文件类型
            // 判断文件夹是否存在否则创建
            $savePath = "upload/file/" . date("Ymd");
            if (!file_exists($savePath)) {
                @mkdir($savePath, 0777, true);
                @chmod($savePath, 0777);
                @exec("chmod 777 {$savePath}");
            }
            $url = $savePath . '/' . time() . rand(100,999) . '.' . $type;
            if (!is_uploaded_file($filePath)) {
                $this->jsonError('临时文件错误');
            }
            //上传文件
            if (!move_uploaded_file($filePath, $url)) {
                $this->jsonError('上传失败');
            }
            //文件大小和扩展名
            $info = array("status" => "success", "message" => "修改成功", "filepath" => $url, "size" => $fileInfo['size'], "extension" => $type, "filename" => $fileName);
            $this->jsonResult('上传成功', $info);
        } else {
            $this->jsonError('系统错误');
        }
    }

    /**
     *多图片上传
     * @author jh
     * @date 2017-08-29
     */
    public function uploadFileList()
    {
        if (IS_POST) {
            $uplodename = $_REQUEST['uplodename'] ?: 'uploadfilelist';
            $fileInfo = $_FILES[$uplodename];
            $fileName = $fileInfo['name'];     //获取上传的文件名
            $filePath = $fileInfo['tmp_name'];    //文件路径
            $fileSize = $fileInfo['size'];    //文件大小
            $filepaths = array();
            $filenames = array();
            foreach ($fileName as $k => $v) {
                if (!$v) {
                    continue;
                }
                $type = strtolower(substr(strrchr($v, '.'), 1)); //获取文件类型
                // 判断文件夹是否存在否则创建
                $savePath = "upload/file/" . date("Ymd");
                if (!file_exists($savePath)) {
                    @mkdir($savePath, 0777, true);
                    @chmod($savePath, 0777);
                    @exec("chmod 777 {$savePath}");
                }
                $url = $savePath . '/' . time() . rand(100,999) . '.' . $type;
                if (!is_uploaded_file($filePath[$k])) {
                    $this->jsonError('临时文件错误');
                }
                //上传文件
                if (!move_uploaded_file($filePath[$k], $url)) {
                    $this->jsonError('上传失败');
                }
                $filepaths[] = $url;
                $filenames[] = $v;
            }
            if (empty($filepaths)) {
                $this->jsonError('请选择文件!');
            }
            $info = array("status" => "success", "message" => "修改成功", "filepath" => $filepaths, "filename" => $filenames);
            $this->jsonResult('上传成功', $info);
        } else {
            $this->jsonError('系统错误');
        }
    }
}