<?php
/**
 * 商家后台
 * @author lee
 * @date 2017/07/19
 */
if (!defined('IN_ECM')) {die('Forbidden');}
class SupplierApp extends BaseStoreApp {
	/**
	 * 构造函数
	 */
	public function __construct() {
		parent::__construct();
		
	}
	/*
	 * 订单列表
	 * @author lee
	 * @date 2017-8-1 15:08:15
	 */
	public  function supplierList(){
		$orderMod=&m('order');
		$bn=isset($_REQUEST['bn'])?'':trim($_REQUEST['bn']);
		$this->assign("order/list.html");
	}
	
}
?>