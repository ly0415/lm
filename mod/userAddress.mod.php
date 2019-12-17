<?php
/**
 * 管理员模块模型
 * @author: jh
 * @date: 2017/6/21
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class userAddressMod extends BaseMod
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct("user_address");
    }

    /**
     * 获取用户的地址
     * @author wanyan
     * @date 2017-10-19
     */
    public function getAddressById($user_id)
    {
        $query = array(
            'cond' => "`user_id` = '{$user_id}'"
        );
        $rs = $this->getOne($query);
        return $rs;
    }

    /**
     * 获取用户的地址
     * @author wanyan
     * @date 2017-10-19
     */
    public function getAddress($address_id)
    {
        $query = array(
            'cond' => "`id` = '{$address_id}'"
        );
        $rs = $this->getOne($query);
        return $rs;
    }

    /**
     * 根据用户id和类型获取信息
     * @author jh
     * @date 2018-10-19
     */
    public function getInfoByUidAndType($user_id, $type = 1)
    {
        $query = array(
            'cond' => "`user_id` = '{$user_id}' and distinguish = {$type}"
        );
        $rs = $this->getOne($query);
        return $rs;
    }

    /**
     * 根据用户id和类型获取信息
     * @author gao
     * @date 2019-01-24
     */
    public function getUserAddress($userId, $type = 1)
    {
        $sql = "SELECT `name`,address,phone,latlon FROM " . DB_PREFIX . "user_address 
                    WHERE distinguish=1 AND user_id=" . 4726 ." ORDER BY default_addr DESC" ;
        $userAddress = $this->querySql($sql);
        if(!empty($userAddress)){
            $userAddress=$userAddress[0];
            $addresss = explode('_', $userAddress['address']);
            $userAddress['generalAddress']=$addresss[0];
            $userAddress['detailAddress']=$addresss[1];
            $userAddress['address']=$addresss[0].$addresss[1];
        }
        return $userAddress;
    }
}