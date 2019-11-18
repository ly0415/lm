<?php
/**
 * 店铺模块模型
 * @author: jh
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class StoreUserMod extends BaseMod {
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct("store_user");
    }

    /**
     * 根据 store_id 获取用户
     * @param $store_id
     * @return array
     */
    public function storeUsers($store_id)
    {
        $sql = "select id,real_name as name from bs_store_user where store_id = {$store_id} order by id desc";
        $data = $this->querySql($sql);

        return $data;
    }

    /**
     * 获取店铺被选中通知的店员
     */
    public function getUserByStore($store_id)
    {
        $sql = "select id,real_name as username,user_name as login_name,store_id from bs_store_user where store_id = {$store_id}";
        $data = $this->querySql($sql);

        $sql2 = "SELECT voucher_id from bs_system_console where type = 5 and rebate_id = {$store_id} limit 1";
        $selected_data = $this->querySql($sql2);
        $selected_user_id = $selected_data[0]['voucher_id'];

        foreach ($data as &$v){
            if($v['id'] == $selected_user_id){
                $v['is_notice'] = 1;
            }else{
                $v['is_notice'] = 0;
            }
        }
        return $data;
    }
}
?>