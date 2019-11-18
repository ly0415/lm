<?php
/**
 * 后台图片上传类
 */
class UploadFileApp extends BackendApp{
	
	public function __construct() {
		parent::__construct();
	}
	public function __destruct() {}   

	/*上传图片并返回图片的预览图
	 * @author wl
	 * @date   2016-10-12
	 */
	public function upload(){
		$mod = &m('image');
		$id = $_GET['id'];
		if(IS_POST){
			import('class.image');
			$imageId = !empty($_POST['image_id']) ? htmlspecialchars($_POST['image_id']) : '0';
			$fileName = $_FILES['fileName']['name'];
			$type = strtolower(substr(strrchr($fileName, '.'), 1)); //获取文件类型
			$imgObj = new Image();
			$savePath = UPLOAD_IMG_SAVE_PATH.date("Y/m/d/");
			//判断文件夹是否存在否则创建
			if (! file_exists ($savePath)) {
				@mkdir ( $savePath, 0777, true );
				@chmod($savePath,0777);
				@exec("chmod 777 {$savePath}");
			}
			$imgInfo = @$imgObj->upload('fileName', $savePath, $type);
			$url = IMG_URL."/upload".$imgInfo[0];
			$size = getimagesize($url);
			$width = (int) $size['0'];
			$height = (int) $size['1'];
			$data = array(
				"name"=>$fileName,
				"width"=>$width,
				"height"=>$height,
				"url"=>$url,
				"add_time"=>time(),
			);
			$info = array("status"=>"error","message"=>"上传失败","imgurl"=>"");
			if($imgInfo){
				if(!empty($imageId)){
					$query = array(
							"cond"=>"id={$imageId}",
							"field"=>"*",
					);
					$image = $mod->getOne($query);
					//unlink($image['url']); //变量的值是从数据库中读取出来的
					$url = substr($image['url'],22);
					unlink(ROOT_PATH.$url);
					$res = $mod->edit($data,$imageId);
					if($res){
						$info = array("status"=>"success","message"=>"修改成功","imgurl"=>IMG_URL."/upload".$imgInfo[0]);
						echo str_replace("\\/", "/",  json_encode($info));
					}else{
						echo json_encode($info);
					}
				}else{
					$res = $mod->doInsert($data);
					if($res){
						$info = array("status"=>"1","message"=>"上传成功","imgurl"=>IMG_URL."/upload".$imgInfo[0]);
						echo str_replace("\\/", "/",  json_encode($info));
					}else{
						echo json_encode($info);
					}
				}
			}else{
				echo json_encode($info);
			}
		}else{
			$query = array(
				"cond"=>"id={$id}",
				"field"=>"*",
			);
			$image = $mod->getOne($query);
			$this->assign("image",$image);
			$this->display('upload/form.html');
		}
	}
	/*上传图片列表
	 * @author wl
	 * @date   2016-10-12
	 */
	public function uploadList(){
		$mod = &m("image");
		$keyword=$_GET['keyword'];
		$query['cond'] = "name like '%$keyword%'";
		$array['mode']=1;
		$array['pre_page']=10;
		$query['order_by'] = "id desc";
		$data = $mod->pageData($query,$array);
		$this->assign("data",$data['list']);
		$this->assign("keyword",$keyword);
		$this->assign("ph",$data['ph']);
		$this->display("upload/index.html");
	}
	/*删除
	 * @author wl
	 * @date   2016-10-18
	 */
	public function uploadDel(){
		$mod = &m('image');
		$id = $_REQUEST['id'];
		$query = array(
			"cond"=>"id={$id}",
		);
		$image = $mod->getOne($query);
		$url = substr($image['url'],22);
		unlink(ROOT_PATH.$url);
		$res = $mod->doDelete($query);
		if($res){
			$this -> setData($info=array() , $status = '1' , $message = '删除图片成功');//删除会员成功
		}else{
			$this -> setData($info=array() , $status = '2' , $message = '删除图片失败');//删除会员失败
		}
	}
	/*
	 *批量删除图片
	 * @author wl
	 * @date   2016年10月25日
	 */
	public function uploadDelAll(){
		$data = $_REQUEST;
		$mod = &m('image');
		$info = array();
		$ids = explode(",",$data['ids']);
		foreach($ids as $vo){
			$query = array(
					"cond"=>"id={$vo}",
			);
			$image = $mod->getOne($query);
			$url = substr($image['url'],22);
			unlink(ROOT_PATH.$url);
		}
		$res = $mod->doDrops('id',$data['ids']);
		if($res){
			$this -> setData($info , $status = '1' , $message = '删除图片成功');//添加会员成功
		}else{
			$this -> setData($info , $status = '2' , $message = '删除图片失败');//添加会员失败
		}
	}
	
	function checkType($type,$info) {
		$img = $info[0];
		$size = getimagesize(ATTACHEMENT_PATH.$img);
		$width = (int) $size['0'];
		$height = (int) $size['1'];
		if ($type==2) {
			if ($width> 100||$height>100) {
				//return false;  //大小不进行限制~若需要则取消注释~
			}
			return $info[0];
		}
		return $img;
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