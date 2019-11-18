<?php
/**
 * 手机app
 * @author lvji
 * @date 2015-3-10
 */
if (!defined('IN_ECM')) {die('Forbidden');}
class UploadApp extends BackendApp {
	/**
	 * 构造函数
	 */
	public function __construct() {
		parent::__construct();
		
	}
	
	/**
	 * 析构函数
	 */
	public function __destruct() {
	}
	/**
	 * 空操作
	 * @author lvji
	 * @date 2015-03-20
	 */
	public function emptyOperate(){
		$info = array();
		$this->setData($info);
	}
	
	/**
	 * 首页
	 * @author wl
	 * @date 2016-9-23
	 */
	//图片上传列表
	public function uploadList(){

	}
	//图片上传并增加到数据库
	public function uploadAdd(){
		if(isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST"){
			$picname = $_FILES['mypic']['name'];
			$picsize = $_FILES['mypic']['size'];
			if ($picname != "") {
				if ($picsize > 1024000) {
					echo '图片大小不能超过1M';
					exit;
				}
				$type = strstr($picname, '.');
				if ($type != ".gif" && $type != ".jpg"&& $type != ".png") {
					echo '图片格式不对！';
					exit;
				}
				$rand = rand(100, 999);
				$pics = date("YmdHis") . $rand . $type;
				//上传路径
				$pic_path = "upload/admin/". $pics;
				move_uploaded_file($_FILES['mypic']['tmp_name'], $pic_path);

			}
			$size = round($picsize/1024,2);
			$arr = array(
					'name'=>$picname,
					'pic'=>$pics,
					'size'=>$size
			);
			echo json_encode($arr);
		}else{
			$this->display('upload/uploadAdd.html');
		}
	}
}
?>