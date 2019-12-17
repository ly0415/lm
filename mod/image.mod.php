<?php
/**
 * 外推媒体批量帐号管理模型
 * @author lvj
 * @date 2013-01-04
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class ImageMod extends BaseMod{

	public function __construct() {
		parent::__construct("image");
	}
	/**
	 * 添加与修改
	 * @param array $data
	 * @param int $id
	 * @return blooe
	 */
	function edit($data , $id = 0){
		if($id){
			$return = $this->doEdit($id, $data) ;
			$row_id = $id ;
		}else{
			$return = $this->doInsert($data) ;
			$row_id = $return ;
		}
		if($return){
			$this->_cacheReset($row_id);
			$this->_cacheResetMemberAll();
			return $row_id ;
		}else{
			return false ;
		}
	}

	//获取管理员列表
	function getRoleList(){
		$query = array(
			"cond" 		=> "1 = 1" ,
			"order_by" => "sort desc",
		);
		$rs = $this->getData($query);

		return $rs;
	}

	/**
	 * 获取单条数据	 *
	 * @param int $id
	 */

	function getRowById($id){
		$query = array(
			"cond" => "id = '{$id}'" ,
		);
		$rs = $this->getOne($query);
		return $rs ;
	}

	/**
	 * 获取单条数据	 *
	 * @param int $id
	 */

	function getUrlById($id){
		$query = array(
			"cond" => "id = '{$id}'" ,
			"fields" => "url"
		);
		$rs = $this->getOne($query);
		return $rs ;
	}


	/**
	 * 删除数据	 *

	 * @param int $id
	 */
	function drop($id){
		$this->do_mark($id) ;
		$this->deleteCache("admin_member_{$id}") ;
		$this->_cacheResetMemberAll();
	}
	
	/**
	 * 物理删除记录
	 * @author xiayy
     * @date 2016-10-18
	 * @param int $id
	 */
	function dropImage($id){
		$result = $this->doDrop($id) ;
		$this->deleteCache("admin_member_{$id}") ;
		$this->_cacheResetMemberAll();
		return $result;
	}

	/**
	 * 检测注册名称等是否存在
	 * @date 2013-4-23
	 * @param string $username - 字段名称
	 * @param int $id - 编号
	 * @return int
	 */
	public function isExist($type , $value , $id = 0) {
		$cond = "{$type}='{$value}'";
		if ($id) {
			$cond .= " AND id!={$id}";
		}
		$query = array('fields'=>'id','cond'=>$cond);
		$info = $this->getOne($query );
		$id = (int)$info['id'];
		return $id;
	}


	/**
	 *@author zl
	 * @date 2016-8-5
	 * @description 获取用户的头像
	 * @param int $user_id
	 */
	public function getUserImgById($image_id){
		$query['cond'] = " id ={$image_id}";
		$query['fields'] = " * ";
		$info = $this->getOne($query );
		return $info;
	}

	/**
	 *@author zl
	 * @date 2016-8-8
	 * @param int $user_id
	 */
	function getInfoById($user_id){
		$query['cond'] = " id = '{$user_id}' ";
		$query['fields'] = '*';
		$info = $this->getOne($query );
		return $info ;
	}

	/**
	 * 获取单个数据
	 *
	 * @param int $id
	 * @return array
	 */
	function getInfo($id){
		$info = $this->getCache("admin_member_{$id}" , "member" , $id);
		$info['extern'] = $info['department_id'] == 35;
		return $info ;
	}

	function getAll($limit){
		$query['cond'] = " 1 = 1";
		$query['fields'] = " * ";
		$query['order_by'] = "add_time desc";
		$query['limit'] = $limit;
		$info = $this->getData($query);
		return $info ;
	}



	/**
	 * 缓存单条数据
	 *
	 * @param int $id
	 * @return array
	 */
	function _cacheMember($id){
		$info = $this->getRow($id );
		$infoMod = &m('adminMemberInfo');
		$mInfo = $infoMod->getInfo($id);
		$mInfo = $mInfo ? $mInfo : array();
		$temp = @array_merge($info , $mInfo);
		return $temp ;
	}

	/**
	 * 重置缓存
	 *
	 * @param int $id
	 * @return array
	 */
	function _cacheReset($id){
		$this->resetCache("admin_member_{$id}" , 'member' , $id) ;
	}


	function _cacheMemberAll(){
		$query = array(
				"pri" 	=> "id" ,
				"fields" => "id" ,
				"cond" 	=> "mark = 1" ,
		);

		$rs = $this->getData($query);
		$list = array();
		foreach($rs as $key => $key){
			$info = $this->getInfo($key);
			$list[$key] = $info;
		}
		return $list ;
	}

	function _cacheResetMemberAll(){
		$this->resetCache("admin_member_all" , 'MemberAll') ;
	}


}
?>