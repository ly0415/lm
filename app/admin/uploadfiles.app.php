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
                jsonError('请选择图片！');
            }
            $type = strtolower(substr(strrchr($fileName, '.'), 1)); //获取文件类型
            $imagetype = array(
                'jpg', 'png', 'jpeg', 'gif', 'JPG', 'PNG', 'JPEG', 'GIF'
            );
            if (!in_array($type, $imagetype)) {
                jsonError('图片的格式不对!');
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
                jsonError('临时文件错误');
            }
            //上传文件
            if (!move_uploaded_file($filePath, $url)) {
                jsonError('上传失败');
            }
            //返回信息
            $info = array("status" => "success", "message" => "修改成功", "imgurl" => $url);
            jsonResult('上传成功', $info);
        } else {
            jsonError('系统错误');
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
                    jsonError('图片的格式不对!');
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
                    jsonError('临时文件错误');
                }
                //上传文件
                if (!move_uploaded_file($filePath[$k], $url)) {
                    jsonError('上传失败');
                }
                $imgurls[] = $url;
            }
            if (empty($imgurls)) {
                jsonError('请选择图片!');
            }
            $info = array("status" => "success", "message" => "修改成功", "imgurl" => $imgurls);
            jsonResult('上传成功', $info);
        } else {
            jsonError('系统错误');
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
                jsonError('请选择文件！');
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
                jsonError('临时文件错误');
            }
            //上传文件
            if (!move_uploaded_file($filePath, $url)) {
                jsonError('上传失败');
            }
            //文件大小和扩展名
            $info = array("status" => "success", "message" => "修改成功", "filepath" => $url, "size" => $fileInfo['size'], "extension" => $type, "filename" => $fileName);
            jsonResult('上传成功', $info);
        } else {
            jsonError('系统错误');
        }
    }

    /**
     *多文件上传
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
                    jsonError('临时文件错误');
                }
                //上传文件
                if (!move_uploaded_file($filePath[$k], $url)) {
                    jsonError('上传失败');
                }
                $filepaths[] = $url;
                $filenames[] = $v;
            }
            if (empty($filepaths)) {
                jsonError('请选择文件!');
            }
            $info = array("status" => "success", "message" => "修改成功", "filepath" => $filepaths, "filename" => $filenames);
            jsonResult('上传成功', $info);
        } else {
            jsonError('系统错误');
        }
    }
}