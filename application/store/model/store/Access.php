<?php

namespace app\store\model\store;

use app\common\model\store\Access as AccessModel;

/**
 * 商家用户权限模型
 * @author  fup
 * @date    2019-05-20
 */
class Access extends AccessModel{

    //订单状态
    private $black_ruth = [
        '10502','10503','10504','10505','10506','10507','10508','10509','10510','10511','10512','10513','10514','10546'         //代客下单、获取规格、获取价格库存、购物车、获取用户、获取商品、微信支付、微信二维码、查询订单状态、获取用户优惠券、支付宝支付、获取分销码、获取验证码,'扫码购'
    ];

    /**
     * 获取权限列表 jstree格式
     * @author  fup
     * @date    2019-05-20
     */
    public function getJsTree($role_id = null, $where=[], $is_store = 0)
    {
        if($is_store){
            $where      = array_merge($where, ['access_id'=>['not in',$this->black_ruth ]]);
        }
        $accessIds  = is_null($role_id) ? [] : RoleAccess::getAccessIds($role_id);
        $jsTree     = [];
        foreach ($this->getAll($where) as $item) {
            $jsTree[]   = [
                'id'    => $item['access_id'],
                'parent'=> $item['parent_id'] > 0 ? $item['parent_id'] : '#',
                'text'  => $item['name'],
                'state' => [
                    'selected' => (in_array($item['access_id'], $accessIds) && !$this->hasChildren($item['access_id']))
                ]
            ];
        }
        return json_encode($jsTree);
    }

    /**
     * 是否存在子集
     * @author  fup
     * @date    2019-05-20
     */
    private function hasChildren($access_id)
    {
        foreach (self::getAll() as $item) {
            if ($item['parent_id'] == $access_id)
                return true;
        }
        return false;
    }

}