<?php

namespace app\api\model;
use app\common\model\UserOrder as UserOrderModel;
use think\Db;

/**
 * 订单模型
 * Class Order
 * @package app\api\model
 */
class UserOrder extends UserOrderModel
{

    /**
     * 用户中心订单列表
     * @param $user_id
     * @param string $type
     */
    public function getList($user_id, $type = 'all')
    {
//        // 筛选条件
//        $filter = [];
//        // 订单数据类型
//        switch ($type) {
//            case 'all':
//                break;
//            case 'payment';
//                $filter['pay_status'] = PayStatusEnum::PENDING;
//                $filter['order_status'] = 10;
//                break;
//            case 'delivery';
//                $filter['pay_status'] = PayStatusEnum::SUCCESS;
//                $filter['delivery_status'] = 10;
//                $filter['order_status'] = 10;
//                break;
//            case 'received';
//                $filter['pay_status'] = PayStatusEnum::SUCCESS;
//                $filter['delivery_status'] = 20;
//                $filter['receipt_status'] = 10;
//                $filter['order_status'] = 10;
//                break;
//            case 'comment';
//                $filter['is_comment'] = 0;
//                $filter['order_status'] = 30;
//                break;
//        }





       $res = $this->field('order_sn,store_id')
           ->where('user_id','=',$user_id)
           ->select()->toArray();
        $data = [];
        foreach ($res as $k => $v){
            $info = Db::name('order_'.$v['store_id'])
                ->where('order_sn','=',$v['order_sn'])
                ->where('mark','=',1)
                ->select()->toArray();
            if(empty($info))continue;
            foreach ($info as $k1 => $v1){
                $info[$k1]['num'] = Db::name('order_goods')
                    ->where('order_id','=',$v1['order_sn'])
                    ->count();
                $list = DB::name('order_goods')
                    ->alias('a')
                    ->field('a.*,a.goods_id ogoods_id')
                    ->join('store_goods b','a.goods_id = b.id','LEFT')
                    ->where('a.order_id = '.$v1['order_sn'])
                    ->select();
                $info[$k1]['goods_list'] = $list;
                foreach ($list as $k2 => $v2) {
                    if ($v2['spec_key']) {
                        $k_info = $this->get_spec($v2['spec_key']);
                        foreach ($k_info as $k5 => $v5) {
                            $list[$k2]['spec_key_name'] = $v5['item_name'];
                        }
                    }
                }
                $info[$k1]['statusName'] = (new Order)->getOrderStatusName($v1['sendout'], $v1['order_state'], $v1['evaluation_state']);
                $info[$k1]['storeName'] = Store::getStoreList()[$v1['store_id']]['store_name'];
                if($v1['sendout'] == 2){
                    $info[$k1]['shipping_fee'] = Db::name('order_details_'.$v['store_id'])
                        ->where('order_sn','=',$v1['id'])
                        ->value('shipping_fee');
                }
                $info[$k1]['invoice_status'] = Db::name('order_invoice')
                    ->where('order_sn','=',$v['order_sn'])->select() ? 1 : 0;
            }
            if ($info){
                $data[] = $info;
            }
        }
        $da = array(
            'langData' => [],
            'listData' => $data
        );
        dump($da);die;
        return $da;

    }


    /**
     * 获取商品规格
     * @param $goods_id|商品id  $type=1 读取商品原有规格属性  2 读取区域编辑后的规格属性
     */
    public function get_spec($k, $lang =29) {
        $k = str_replace('_', ',', $k);
        return Db::name('goods_spec')
            ->alias('a')
            ->join('goods_spec_item b','a.id = b.spec_id','INNER')
            ->join('goods_spec_lang al','a.id = al.spec_id','LEFT')
            ->join('goods_spec_item_lang bl','b.id = bl.item_id','LEFT')
            ->where('b.id','in',$k)
            ->where('al.lang_id','=',$lang)
            ->where('bl.lang_id','=',$lang)
            ->order('b.id')
            ->select()->toArray();
//        $sql4 = "SELECT a.`order`,b.*,al.spec_name,bl.`item_name`  FROM " . DB_PREFIX . "goods_spec AS a
//                     INNER JOIN " . DB_PREFIX . "goods_spec_item AS b ON a.id = b.spec_id
//                     LEFT JOIN " . DB_PREFIX . "goods_spec_lang AS al ON a.id=al.spec_id
//                     LEFT JOIN " . DB_PREFIX . "goods_spec_item_lang as bl ON b.id=bl.item_id
//                     WHERE b.id IN($k) and al.lang_id=" . $lang . " and bl.lang_id=" . $lang . " ORDER BY b.id";
//        $filter_spec2 = $storeGoodMod->querySql($sql4);
//        return $filter_spec2;
    }
}
