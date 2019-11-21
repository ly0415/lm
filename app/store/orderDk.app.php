<?php

/**
 * 代客下单
 * @author wangshuo
 * @date 2018-5-10
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class OrderDkApp extends BaseStoreApp
{
    private $lang_id;
    public $storeGoodsMod;
    public $storeMod;
    public $orderMod;
    public $orderGoodsMod;
    public $userMod;
    private $orderDetailMod;
    private $areaGoodMod;
    private $storeGoodItemPriceMod;
    private $goodsSpecPriceMod;
    private $goodsMod;
    private $amountLogMod;
    private $cartMod;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
        $this->storeGoodsMod = &m('storeGoods');
        $this->storeMod = &m('store');
        $this->orderMod = &m('order');
        $this->orderGoodsMod = &m('orderGoods');
        $this->userMod = &m('user');
        $this->orderDetailMod = &m('orderDetail');
        $this->storeGoodItemPriceMod = &m('storeGoodItemPrice');
        $this->areaGoodMod = &m('areaGood');
        $this->goodsSpecPriceMod =& m('goodsSpecPrice');
        $this->goodsMod =& m('goods');
        $this->amountLogMod = &m('amountLog');
        $this->cartMod = &m('cart');
    }

    /**
     * 代客下单
     * @author wangshuo
     * @date 2018-5-10
     */
    public function index()
    {
        $type = !empty($_REQUEST['type']) ? htmlspecialchars(trim($_REQUEST['type'])) : 1;//1:代客下单2:代客预购
        //获取店铺折扣
        $sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $this->storeId;
        $discountInfo = $this->storeMod->querySql($sql);
        $discount = $discountInfo[0]['store_discount'];
//        if ($this->storeUserId == 259) {
        $info = $this->storeGoodsMod->getDkxdGoodsInfo($this->storeId, $this->languageId);
        $roomId_arr = $info['roomId_arr'];
        $roomInfo = $info['roomInfo'];
//            echo '<pre>';print_r($info);die;
//        } else {
//            //获取二级业务类型
//            $sql = 'select d.id,e.type_name from ' .
//                DB_PREFIX . 'room_type as a left join ' .
//                DB_PREFIX . 'room_type_lang as b on a.id = b.type_id left join ' .
//                DB_PREFIX . 'store_business as c on a.id = c.buss_id left join ' .
//                DB_PREFIX . 'room_type as d on a.id = d.superior_id left join ' .
//                DB_PREFIX . 'room_type_lang as e on d.id = e.type_id ' .
//                ' where a.superior_id = 0 and b.lang_id=' . $this->languageId . ' and c.store_id = ' . $this->storeId .
//                ' and e.lang_id = ' . $this->languageId . ' order by d.sort asc,d.id asc ';
//            $roomInfo = $this->storeGoodsMod->querySql($sql);
//            //获取普通商品
//            $roomId_arr = array();
//            foreach ($roomInfo as &$v) {
//                $roomId_arr[] = $v['id'];
//                $v['goodsInfo'] = $this->storeGoodsMod->getDkxdGoodsInfo($this->storeId, $v['id'], $this->storeUserId);
//                foreach ($v['goodsInfo'] as &$val) {
//                    $val['shop_price'] = number_format($val['shop_price'] * $discount, 2, '.', '');
//                }
//            }
//        }
        //获取热销商品
        $roomIds = implode(',', $roomId_arr);
        $hotInfo = $this->storeGoodsMod->getDkxdRxGoodsInfo($this->storeId, $roomIds);
        foreach ($hotInfo as &$val) {
            $val['shop_price'] = number_format($val['shop_price'] * $discount, 2, '.', '');
        }
        //获取优惠商品,暂时不展示
//        $activityInfo = $this->storeGoodsMod->getDkxdYhGoodsInfo($this->storeId, $roomIds);
        $this->assign('roomInfo', $roomInfo);
        $this->assign('hotInfo', $hotInfo);
//        $this->assign('activityInfo', $activityInfo);
        $this->assign('storeid', $this->storeId);
        $this->assign('type', $type);
        //******获取预购列表********************************************************************************************
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim($_REQUEST['order_sn'])) : '';
        $this->assign('order_sn', $order_sn);
        $where = " and g.mark = 1  ";
        if (!empty($order_sn)) {
            $where .= " and g.order_sn like '%{$order_sn}%'";
        }
        $where .= " and d.phone = '0102' and g.order_state = 10 and g.store_id = " . $this->storeId;
        $sql = 'select distinct g.order_sn, g.is_source,g.order_state,g.sendout,g.pei_time,g.fx_phone,g.source_id,g.order_id,g.goods_amount,g.order_amount,g.discount,g.pd_amount,g.cp_amount,g.shipping_fee,g.buyer_name,g.buyer_phone,g.sub_user,g.add_time,se.img,g.Appoint from '
            . DB_PREFIX . 'order as g right join  '
            . DB_PREFIX . 'order_goods as f ' . ' on f.order_id = g.order_sn left join '
            . DB_PREFIX . 'store_source as se ' . ' on g.source_id = se.id left join '
            . DB_PREFIX . 'user as d ' . ' on g.buyer_id = d.id '
            . ' where 1=1 ' . $where
            . ' order by g.order_id desc';
        $result = $this->orderMod->querySqlPageData($sql);
        $data = $result['list'];
        //订单商品数据
        $userCouponMod =& m("userCoupon");//用户劵表
        foreach ($data as $k => $v) {
            $v_where = "order_id='{$v['order_sn']}'";
            $cond = array(
                'cond' => $v_where
            );
            $sendVoucher = $userCouponMod->getOne(array('cond' => "`order_id`='{$v['order_id']}'", 'id'));//是否赠送了兑换券
            $list = $this->orderGoodsMod->getData($cond);
            $data[$k]['goods_list'] = $list;
            $data[$k]['sendVoucher'] = $sendVoucher;
            if ($data[$k]['sendout'] == 1) {
                if ($this->lang_id == 1) {
                    $data[$k]['shippingMethod'] = 'Self lifting';
                } else {
                    $data[$k]['shippingMethod'] = '自提';
                }
            }
            //赠品
            $sqle = "select * from " . DB_PREFIX . "gift_goods  as g left join "
                . DB_PREFIX . "store_goods as s on g.goods_id = s.id left join "
                . DB_PREFIX . "goods_lang as l on s.goods_id = l.goods_id " . " where g.id=" . $v['gift_id'] . " and  lang_id = " . $this->languageId;
            $res = $this->orderMod->querySql($sqle);
            if ($res[0]['goods_key']) {
                $k_info = $this->get_spec($res[0]['goods_key'], $lang);
                if ($k_info) {
                    $res[0]['goods_key_name'] = $k_info[0]['item_name'];
                }
            }
            $data[$k]['gift'] = $res;
        }
        $this->assign('orderList', $data);
        $this->assign('page_html', $result['ph']);
        $this->assign('total_yugou', $result['total']);
        $OrderStatus = array(
            "0" => $a['Canceled'],
            "10" => $a['Unpaid'],
            "20" => $a['payment'],
            "30" => $a['Shipped'],
            "40" => $a['Shipped_1'],
            "50" => $a['Receivedgoods'],
        );
        //赠送兑换劵活动开启
        $systemConsoleMod =& m('systemConsole');
        $timeData = $systemConsoleMod->getOne(array('cond' => "`type` =3 and status=1", 'fields' => 'start_time,end_time'));

        if (!empty($timeData)) {
            if ($timeData['start_time'] < time() && $timeData['end_time'] > time()) {
                $this->assign('sendVoucher', 1);
            }
        }
        $this->assign('symbol', $this->symbol);
        $this->assign('status', $OrderStatus);
        $this->display('orderDk/index.html');
    }

    /**
     * 修改预购订单号码
     */
    public function changePhone()
    {
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim($_REQUEST['order_sn'])) : '';
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars(trim($_REQUEST['phone'])) : '';
        $code = !empty($_REQUEST['code']) ? htmlspecialchars(trim($_REQUEST['code'])) : '';
        if (empty($order_sn)) {
            $this->jsonError('无效的订单!');
        }
        if (!preg_match('/^\d{11}$/', $phone)) {
            $this->jsonError('无效的手机号');
        }
//        if (empty($code)) {
//            $this->jsonError('验证码必填!');
//        }
//        $smsCode = $this->getSmsCode($phone);
//        if ($code != $smsCode) {
//            $this->jsonError('验证码不正确!');
//        }
        $userInfo = $this->userMod->getInfoByPhone($phone, $this->storeId, $this->storecate);
        $user_id = $userInfo['id'];
        //获取地址表数据
        $userAddressMod = &m('userAddress');
        $user_addr = $userAddressMod->getInfoByUidAndType($user_id);
        $buyer_name = empty($user_addr['name']) ? $phone : $user_addr['name'];
        $buyer_phone = empty($user_addr['phone']) ? $phone : $user_addr['phone'];
        $address = empty($user_addr['address']) ? '门店经营买家上门自提' : $user_addr['address'];
        $oldOrder = array(
            'buyer_id' => $user_id,
            'buyer_name' => $buyer_name,
            'buyer_phone' => $buyer_phone,
            'buyer_address' => $address,
        );
        $orderGoods = array(
            'buyer_id' => $user_id,
        );
        $newOrder = array(
            'buyer_id' => $user_id,
        );
        $this->orderMod->doEditSpec(array('order_sn' => $order_sn), $oldOrder);
        $this->orderGoodsMod->doEditSpec(array('order_id' => $order_sn), $orderGoods);
        $newOrderMod = &m("order" . $this->storeId);
        $newOrderMod->doEditSpec(array('order_sn' => $order_sn), $newOrder);
        $this->jsonResult('修改成功!');
    }

    /**
     * 代客下单页面，异步获取规格数据
     */
    public function ajaxGoodsSpec()
    {
        $type = !empty($_REQUEST['type']) ? htmlspecialchars(trim($_REQUEST['type'])) : 1;//1:普通商品2:秒杀商品:3:团购商品4:促销商品
        $store_goods_id = !empty($_REQUEST['store_goods_id']) ? htmlspecialchars(trim($_REQUEST['store_goods_id'])) : 0;
        $typeid = !empty($_REQUEST['typeid']) ? htmlspecialchars(trim($_REQUEST['typeid'])) : 0;//活动表对应的Id
        //获取商品下的规格属性
        $specKeyInfo = array();
        switch ($type) {
            case 1:
            case 2:
                $sql = 'select `key` as spec_key from ' . DB_PREFIX . 'store_goods_spec_price where store_goods_id=' . $store_goods_id;
                $specKeyInfo = $this->storeGoodsMod->querySql($sql);
                break;
            case 3:
                $sql = 'select goods_spec_key as spec_key from ' . DB_PREFIX . 'goods_group_buy where id=' . $typeid;
                $specKeyInfo = $this->storeGoodsMod->querySql($sql);
                break;
            case 4:
                $sql = 'select goods_key as spec_key from ' . DB_PREFIX . 'promotion_goods where prom_id = ' . $typeid . ' and goods_id=' . $store_goods_id;
                $specKeyInfo = $this->storeGoodsMod->querySql($sql);
                break;
            default:
                break;
        }
        //拼凑属性id
        $keyIds = array();
        foreach ($specKeyInfo as $v) {
            $keyIds = array_merge($keyIds, explode('_', $v['spec_key']));
        }
        $keyIds = array_unique($keyIds);
        //获取规格及属性详细信息
        $sql = 'select a.id,b.id as item_id,c.spec_name,d.item_name from ' .
            DB_PREFIX . 'goods_spec as a left join ' .
            DB_PREFIX . 'goods_spec_item as b on a.id = b.spec_id left join ' .
            DB_PREFIX . 'goods_spec_lang as c on a.id = c.spec_id left join ' .
            DB_PREFIX . 'goods_spec_item_lang as d on b.id = d.item_id ' .
            ' where b.id in (' . implode(',', $keyIds) . ') and c.lang_id=' . $this->languageId . ' and d.lang_id = ' . $this->languageId . ' order by b.id asc ';
        $data = $this->storeGoodsMod->querySql($sql);
        $specInfo = array();
        foreach ($data as $v) {
            if (isset($specInfo[$v['id']])) {
                $specInfo[$v['id']]['itemInfo'][] = array(
                    'item_id' => $v['item_id'],
                    'item_name' => $v['item_name']
                );
            } else {
                $specInfo[$v['id']] = array(
                    'id' => $v['id'],
                    'spec_name' => $v['spec_name'],
                    'itemInfo' => array(array(
                        'item_id' => $v['item_id'],
                        'item_name' => $v['item_name']
                    ))
                );
            }
        }
        $this->assign('specInfo', $specInfo);
        $str = self::$smarty->fetch("orderDk/ajaxGoodsSpec.html");
        $this->jsonResult('', $str);
    }

    /**
     * 代客下单页面，异步获取商品价格
     */
    public function ajaxGoodsPrice()
    {
        $type = !empty($_REQUEST['type']) ? htmlspecialchars(trim($_REQUEST['type'])) : 1;//1:普通商品2:秒杀商品:3:团购商品4:促销商品
        $store_goods_id = !empty($_REQUEST['store_goods_id']) ? htmlspecialchars(trim($_REQUEST['store_goods_id'])) : 0;
        $spec_key = !empty($_REQUEST['spec_key']) ? htmlspecialchars(trim($_REQUEST['spec_key'])) : '';
        $typeid = !empty($_REQUEST['typeid']) ? htmlspecialchars(trim($_REQUEST['typeid'])) : 0;//活动表对应的Id
        $discount = 0;
        $priceInfo = array();
        $spec_arr = array();
        if ($spec_key) {
            $key_arr = explode('_', $spec_key);
            $key_pailie = $this->arrangement($key_arr, count($key_arr));
            foreach ($key_pailie as $v) {
                $spec_arr[] = implode('_', $v);
            }
        }
        switch ($type) {
            case 1://普通商品，没特殊情况
                break;
            case 2://秒杀商品，获取秒杀折扣
                $sql = 'select discount from ' . DB_PREFIX . 'spike_activity where id=' . $typeid;
                $discountInfo = $this->storeGoodsMod->querySql($sql);
                $discount = $discountInfo[0]['discount'] / 10;
                break;
            case 3://团购商品，直接获取价格
                $sql = 'select group_goods_price as price from ' . DB_PREFIX . 'goods_group_buy where id=' . $typeid;
                $priceInfo = $this->storeGoodsMod->querySql($sql);
                break;
            case 4://促销商品，直接获取价格
                $sql = 'select discount_price as price from ' . DB_PREFIX . 'promotion_goods where prom_id = ' . $typeid . ' and goods_id=' . $store_goods_id . ' and goods_key = "' . $spec_key . '"';
                $priceInfo = $this->storeGoodsMod->querySql($sql);
                break;
            default:
                break;
        }
        if (!empty($priceInfo)) {
            $price = number_format($priceInfo[0]['price'], 2, '.', '');
        } else {//没有获取到价格，则取普通商品价格
            if (!$discount) {
                //获取店铺折扣
                $sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $this->storeId;
                $discountInfo = $this->storeGoodsMod->querySql($sql);
                $discount = $discountInfo[0]['store_discount'];
            }
            //获取店铺下的规格键对应的价格
            $sql = "select price from " . DB_PREFIX . "store_goods_spec_price where store_goods_id=" . $store_goods_id . ' and `key` in ("' . implode('","', $spec_arr) . '")';
            $priceInfo = $this->storeGoodsMod->querySql($sql);
            $price = number_format($priceInfo[0]['price'] * $discount, 2, '.', '');
        }
        $this->jsonResult('', $price);
    }

    /**
     * 生成订单
     * @author jh
     * @date 2018-9-12
     */
    public function addOrder()
    {
        $type = !empty($_REQUEST['type']) ? htmlspecialchars(trim($_REQUEST['type'])) : 1;//1:代客下单2:补单
        $allPrices = !empty($_REQUEST['allPrices']) ? $_REQUEST['allPrices'] : '';//商品总价
        $store_goods_id = !empty($_REQUEST['store_goods_id']) ? $_REQUEST['store_goods_id'] : '';//商品id
        $store_goods_name = !empty($_REQUEST['store_goods_name']) ? $_REQUEST['store_goods_name'] : '';//商品名称
        $store_goods_speckey = !empty($_REQUEST['store_goods_speckey']) ? $_REQUEST['store_goods_speckey'] : '';//规格键id
        $store_goods_specname = !empty($_REQUEST['store_goods_specname']) ? $_REQUEST['store_goods_specname'] : '';//规格名称
        $store_goods_url = !empty($_REQUEST['store_goods_url']) ? $_REQUEST['store_goods_url'] : '';//商品图片
        $store_goods_price = !empty($_REQUEST['store_goods_price']) ? $_REQUEST['store_goods_price'] : '';//商品实际价格
        $store_goods_number = !empty($_REQUEST['store_goods_number']) ? $_REQUEST['store_goods_number'] : '';//商品数量
        $prom_type = !empty($_REQUEST['prom_type']) ? $_REQUEST['prom_type'] : '';//1:普通商品2:秒杀商品:3:团购商品4:促销商品
        $prom_id = !empty($_REQUEST['prom_id']) ? $_REQUEST['prom_id'] : '';//活动id

        if (empty($store_goods_id)) {
            $this->jsonError('请选择商品!');
        }
        //判断商品配送方式
        if ($type == 1) {
            $store_goods_info = $this->storeGoodsMod->getData(array('cond' => "id in (" . implode(',', $store_goods_id) . ")"));
            if (empty($store_goods_info)) {
                $this->jsonError('获取商品信息失败!');
            } else {
                $ziti = true;
                $peisong = true;
                foreach ($store_goods_info as $v) {
                    $temp = explode(',', $v['attributes']);
                    if (empty($v['attributes'])) {//无属性默认自提
                        $peisong = false;
                    } else {
                        if (!in_array(1, $temp)) {
                            $ziti = false;
                        }
                        if (!in_array(2, $temp)) {
                            $peisong = false;
                        }
                    }
                }
                if (!$ziti && !$peisong) {
                    $this->jsonError("商品配送属性不统一!");
                }
                unset($v);
            }
        }
        //生成订单编号
        $rand = $this->buildNo(1);
        $uniquecode = date('YmdHis') . $rand[0];
        //加入购物车
        foreach ($store_goods_id as $k => $v) {
            $insert_cart_data = array(
                'store_id' => $this->storeId, //店铺ID
                'goods_id' => $store_goods_id[$k], //商品ID
                'goods_name' => addslashes($store_goods_name[$k]), //商品名称
//                'goods_price' => $store_goods_price[$k], //商品价格
                'goods_price' => $this->orderMod->getPrice($this->storeId, $store_goods_id[$k], $store_goods_speckey[$k]),
                'member_goods_price' => $store_goods_price[$k], //商品实际成交价
                'goods_num' => $store_goods_number[$k], //商品数量
                'spec_key' => $store_goods_speckey[$k], //规格
                'spec_key_name' => $store_goods_specname[$k], //规格名
                'add_time' => time(),//添加时间
                'prom_type' => $prom_type[$k] - 1, //0 普通商品,1 限时抢购,2团购,3促销优惠,4,组合销售,5.买赠活动
                'prom_id' => $prom_id[$k], //活动ID
                'shipping_store_id' => $this->storeId, //配送区域站点ID
                'uniquecode' => $uniquecode, //唯一标识
                'delivery_type' => 1
            );
            $this->cartMod->doInsert($insert_cart_data);
        }
        $this->jsonResult('下单成功!', array('url' => "?app=orderDk&act=payment&uniquecode={$uniquecode}&type={$type}"));
    }

    //获取商品id

    function  getGoodId($id)
    {
        $sql = "select goods_id from " . DB_PREFIX . 'store_goods where id=' . $id;
        $goodInfo = $this->orderDetailMod->querySql($sql);
        return $goodInfo[0]['goods_id'];

    }

    //获取商品扣除方式
    function getDeduction($id)
    {
        $sql = "select deduction from " . DB_PREFIX . 'store_goods where id=' . $id;
        $goodInfo = $this->orderDetailMod->querySql($sql);
        return $goodInfo[0]['deduction'];
    }

    /**
     *代客订单
     */
    public function payment()
    {
        $type = !empty($_REQUEST['type']) ? htmlspecialchars(trim($_REQUEST['type'])) : 1;//1:代客下单2:补单
        $uniquecode = !empty($_REQUEST['uniquecode']) ? htmlspecialchars(trim($_REQUEST['uniquecode'])) : '';//购物车唯一标识
        //获取子订单信息
        $sql = 'select a.goods_id,a.goods_name,a.goods_num,a.member_goods_price,a.spec_key_name,b.room_id,b.original_img,b.attributes,c.superior_id as room_pid, d.auxiliary_type,d.goods_id as goodsId from ' . DB_PREFIX . 'cart as a
            left join ' . DB_PREFIX . 'store_goods as b on a.goods_id = b.id 
            left join ' . DB_PREFIX . 'room_type as c on b.room_id = c.id 
            left join ' . DB_PREFIX . 'goods as d on b.goods_id = d.goods_id ' .
            ' where a.store_id=' . $this->storeId . ' and a.uniquecode=' . $uniquecode;
        $orderGoodsInfo = $this->orderMod->querySql($sql);
        if (empty($orderGoodsInfo)) {//购物车数据已清空
//            header("Location: ?app=customerOrder&act=index&lang_id=" . $this->languageId);
            header("Location: ?app=order&act=index&lang_id=" . $this->languageId);
        } else {
            //获取订单来源
            $sql = 'select * from ' . DB_PREFIX . 'store_source where store_id= ' . $this->storeId . ' order by sort';
            $sourceInfo = $this->orderMod->querySql($sql);
            //订单总价格和运费、配送方式
            $goods_amount = 0;
            $goodsList = array();
            $storeFareRuleMod = &m('storeFareRule');
            $ziti = true;//true表示可以自提
            $peisong = true;//true表示可以配送
            foreach ($orderGoodsInfo as $v) {
                $goods_amount += $v['member_goods_price'] * $v['goods_num'];
                $temp = array(array('goods_id' => $v['goodsId'], 'number' => $v['goods_num']));
                $goodsList = array_merge($goodsList, $temp);
                //配送方式
                unset($temp);
                $temp = explode(',', $v['attributes']);
                if (empty($v['attributes'])) {//无属性默认自提
                    $peisong = false;
                } else {
                    if (!in_array(1, $temp)) {
                        $ziti = false;
                    }
                    if (!in_array(2, $temp)) {
                        $peisong = false;
                    }
                }
            }
            $goods_amount = number_format($goods_amount, 2, '.', '');
            $pei_discount = $storeFareRuleMod->getFare($goodsList, $this->storeId);
            $this->assign('orderGoodsInfo', $orderGoodsInfo);
            $this->assign('sourceInfo', $sourceInfo);
            $this->assign('uniquecode', $uniquecode);
            $this->assign('goods_amount', $goods_amount);
            $this->assign('pei_discount', $pei_discount);
            $this->assign('ziti', $ziti);
            $this->assign('peisong', $peisong);
            $this->assign('type', $type);
            $this->assign('storeId', $this->storeId);
            $this->display('orderDk/payment.html');
        }
    }

    /**
     *代客下单支付
     */
    public function orderPay()
    {
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim($_REQUEST['order_sn'])) : '';//订单编号
        //获取主订单信息
        $sql = "select * from bs_order where order_state=10 and store_id={$this->storeId} and order_sn='{$order_sn}'";
        $data = $this->orderMod->querySql($sql);
        $orderInfo = $data[0];
        if (empty($orderInfo)) {//订单状态已改变，不能进入支付页面
//            header("Location: ?app=customerOrder&act=index&lang_id=" . $this->languageId);
            header("Location: ?app=order&act=index&lang_id=" . $this->languageId);
        } else {
            //获取子订单信息
            $sql = 'select a.order_id,a.goods_id,a.goods_name,a.goods_num,a.goods_image,a.goods_pay_price,a.spec_key_name,b.room_id,b.original_img,c.superior_id as room_pid from ' .
                DB_PREFIX . 'order_goods as a left join ' .
                DB_PREFIX . 'store_goods as b on a.goods_id = b.id left join ' .
                DB_PREFIX . 'room_type as c on b.room_id = c.id ' .
                ' where a.order_state=10 and a.store_id=' . $this->storeId . ' and a.order_id=' . $order_sn;
            $orderGoodsInfo = $this->orderMod->querySql($sql);
            //获取订单来源
            $sql = 'select * from ' . DB_PREFIX . 'store_source where store_id= ' . $this->storeId . ' order by sort';
            $sourceInfo = $this->orderMod->querySql($sql);
            //获取抵扣睿积分
            if ($orderInfo['pd_amount']) {
                $pointLogMod = &m("pointLog");
                $pointInfo = $pointLogMod->getOne(array("cond" => "order_sn='{$order_sn}'"));
                $this->assign('pd_point', $pointInfo['expend']);
            }
            //获取分销金额
            $fxMoney = '0.00';
            if ($orderInfo['fx_user_id']) {
                $fxuserMod = &m('fxuser');
                $fxuserInfo = $fxuserMod->getOne(array('cond' => "id={$orderInfo['fx_user_id']}"));
                $fxMoney = number_format($orderInfo['goods_amount'] * $fxuserInfo['discount'] * 0.01, 2, '.', '');
            }
            //获取优惠券信息
            if ($orderInfo['cid']) {
                $couponMod = &m('coupon');
                $couponInfo = $couponMod->getOne(array("cond" => "id={$orderInfo['cid']}"));
                $this->assign('couponInfo', $couponInfo);
            }
            $this->assign('orderInfo', $orderInfo);
            $this->assign('orderGoodsInfo', $orderGoodsInfo);
            $this->assign('sourceInfo', $sourceInfo);
            $this->assign('fx_money', $fxMoney);
            $this->display('orderDk/orderPay.html');
        }
    }

    /**
     * 订单支付页面，异步获取优惠券
     */
    public function ajaxCoupon()
    {
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars(trim($_REQUEST['phone'])) : '';//1:用户手机号码
        $goods_amount = !empty($_REQUEST['goods_amount']) ? htmlspecialchars(trim($_REQUEST['goods_amount'])) : 0;//订单金额
        $order_sn = !empty($_REQUEST['uniquecode']) ? htmlspecialchars(trim($_REQUEST['uniquecode'])) : '';//订单编号

        $userCouponMod = &m('userCoupon');
        $userMod = &m('user');
        $sql = "SELECT id FROM bs_user WHERE phone=" . $phone;
        $userInfo = $userMod->querySql($sql);
        $lang_id = !empty($lang_id) ? intval($_REQUEST['lang_id']) : $this->languageId;
        //获取订单对应商品的业务类型及其金额
        $orderGoodsInfo = $this->orderGoodsMod->getRoomtypeidByOrdersn($order_sn);
        $storeId = $this->storeId;
        //优惠券
        $infoYHJ = $userCouponMod->getValidCoupons($userInfo[0]['id'], $lang_id, 1, $storeId, $goods_amount);
        //兑换券
        $infoDHJ = $userCouponMod->getValidCoupons($userInfo[0]['id'], $lang_id, 2, 0, 0, $orderGoodsInfo);
        $this->assign('infoYHJ', $infoYHJ);
        $this->assign('infoDHJ', $infoDHJ);
//        $str = self::$smarty->fetch("orderDk/ajaxCoupon.html");
        $str = self::$smarty->fetch("orderDk/coupon.html");
        $this->jsonResult('', $str);
    }

    /**
     * 订单支付页面，异步获取睿积分
     */
    public function ajaxPoints()
    {
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars(trim($_REQUEST['phone'])) : '';//1:用户手机号码
        $goods_amount = !empty($_REQUEST['goods_amount']) ? htmlspecialchars(trim($_REQUEST['goods_amount'])) : 0;//订单金额
        //获取用户剩余积分
        $sql = 'select id,point from ' . DB_PREFIX . 'user where mark=1 and is_use=1 and phone=' . $phone;
        $userInfo = $this->orderMod->querySql($sql);
        $final_point = 0;
        $final_money = 0.00;
        if (!empty($userInfo)) {
            $userPoint = $userInfo[0]['point'];
            //获取积分和人民币的兑换比率
            $userPointSiteMod = &m('point');
            $userSite = $userPointSiteMod->getOne(array("cond" => "1=1"));
            //获取积分抵扣最大比率
            $storePointSiteMod = &m('storePoint');
            $storeSite = $storePointSiteMod->getOne(array("cond" => "store_id=" . $this->storeId));
            //获取当前店铺币种
            $storeInfo = $this->storeMod->getOne(array("cond" => "id=" . $this->storeId));
            //获取当前币种和RMB的比例
            $currencyMod = &m('currency');
            $rate = $currencyMod->getCurrencyRate($storeInfo['currency_id']);
            //获取最终抵扣积分和抵扣金额
            $final_point = floor($goods_amount * $rate * $storeSite['point_price'] / 100 * $userSite['point_rate']);
            if ($final_point > $userPoint) {
                $final_point = $userPoint;
            }
            $final_money = number_format($final_point / $userSite['point_rate'] / $rate, 2, '.', '');
        }
        $this->assign('final_point', $final_point);
        $this->assign('final_money', $final_money);
        $str = self::$smarty->fetch("orderDk/ajaxPoints.html");
        $this->jsonResult('', $str);
    }

    /**
     * 异步检索分销码信息
     * @author jh
     * @date 2018/6/7
     */
//    public function fxUserByCode()
//    {
//        $type = $_REQUEST['type'] ? htmlspecialchars(trim($_REQUEST['type'])) : '';//0:根据分销码取数据，1:根据手机号取数据
//        $phone = $_REQUEST['phone'] ? htmlspecialchars(trim($_REQUEST['phone'])) : '';//用户号码
//        $fx_code = $_REQUEST['fx_code'] ? htmlspecialchars(trim($_REQUEST['fx_code'])) : '';//分销码
//        $where = ' where a.mark = 1 and a.is_check = 2 and a.level = 3 ';
//        if ($type == 1) {
//            $sql = 'select a.fx_user_id from ' .
//                DB_PREFIX . 'fx_user_account as a left join ' .
//                DB_PREFIX . 'user as b on a.user_id = b.id ' .
//                ' where b.mark = 1 and b.is_use = 1 and b.phone = ' . $phone;
//            $info = $this->orderMod->querySql($sql);
//            if (!empty($info)) {//该用户有分销人
//                $where .= ' and a.id = ' . $info[0]['fx_user_id'];
//            } else {//该用户没分销人
//                $where .= ' and 1 = 0 ';
//            }
//        } elseif ($fx_code) {
//            $where .= " and a.fx_code = '{$fx_code}' ";
//        } else {
//            $where .= ' and 1 = 0 ';
//        }
//        $sql = 'select a.id,a.discount,a.fx_code from ' .
//            DB_PREFIX . 'fx_user as a ' . $where;
//        $data = $this->orderMod->querySql($sql);
//        $result = isset($data[0]) ? $data[0] : array();
//        if (!empty($result)) {
//            $this->jsonResult('', $result);
//        } else {
//            $this->jsonError('', $result);
//        }
//    }
    /**
     * 新的带出分销码
     * @author tangp
     * @date 2019-02-02
     */
    public function fxUserByCode()
    {
        $type = $_REQUEST['type'] ? htmlspecialchars(trim($_REQUEST['type'])) : '';//0:根据分销码取数据，1:根据手机号取数据
        $phone = $_REQUEST['phone'] ? htmlspecialchars(trim($_REQUEST['phone'])) : '';//用户号码
        $fx_code = $_REQUEST['fx_code'] ? htmlspecialchars(trim($_REQUEST['fx_code'])) : '';//分销码
        $where = ' where a.mark = 1 and a.is_check = 2 and a.level = 3 ';
        $sqls = "SELECT id FROM bs_user WHERE mark=1 AND is_use =1 and phone=" . $phone;
        $rr = $this->orderMod->querySql($sqls);
        $sqll = "SELECT * FROM bs_fx_user WHERE mark=1 AND is_check=2 AND user_id=" . $rr[0]['id'];
        $rrr = $this->orderMod->querySql($sqll);
        if ($type == 1) {
            if ($rrr[0]['level'] == 3) {
                $w = " where a.id=" . $rrr[0]['id'];
                $sql = 'select a.level,a.id,a.discount,a.fx_code from ' .
                    DB_PREFIX . 'fx_user as a ' . $w;
//                echo $sql;die;
                $data = $this->orderMod->querySql($sql);
                $result = isset($data[0]) ? $data[0] : array();
            } else {
                $sql = 'select a.fx_user_id from ' .
                    DB_PREFIX . 'fx_user_account as a left join ' .
                    DB_PREFIX . 'user as b on a.user_id = b.id ' .
                    ' where b.mark = 1 and b.is_use = 1 and b.phone = ' . $phone;
                $info = $this->orderMod->querySql($sql);

                if (!empty($info)) {//该用户有分销人
                    $where .= ' and a.id = ' . $info[0]['fx_user_id'];
                } else {//该用户没分销人
                    $where .= ' and 1 = 0 ';
                }
                $sql = 'select a.id,a.discount,a.fx_code from ' .
                    DB_PREFIX . 'fx_user as a ' . $where;
                $data = $this->orderMod->querySql($sql);
                $result = isset($data[0]) ? $data[0] : array();
            }
        } elseif ($fx_code) {
            $where .= " and a.fx_code = '{$fx_code}' ";
            $sql = 'select a.id,a.discount,a.fx_code from ' .
                DB_PREFIX . 'fx_user as a ' . $where;
            $data = $this->orderMod->querySql($sql);
            $result = isset($data[0]) ? $data[0] : array();
        } else {
            $where .= ' and 1 = 0 ';
            $sql = 'select a.id,a.discount,a.fx_code from ' .
                DB_PREFIX . 'fx_user as a ' . $where;
            $data = $this->orderMod->querySql($sql);
            $result = isset($data[0]) ? $data[0] : array();
        }
        if (!empty($result)) {
            $this->jsonResult('', $result);
        } else {
            $this->jsonError('', $result);
        }
    }

    /*
     * 代客下单-支付入口
     * @auth jh
     * @date 2018-9-26
     */
    public function payDkxd()
    {
        if (!$this->storeId) {
            $this->jsonError('登录失效，请重新登录!');
        }

        //语言包
        $this->load($this->languageId, 'comfirmOrder/index');
        $a = $this->langData;
        //接受参数
        $yinlian = !empty($_REQUEST['yinlian']) ? htmlspecialchars(trim($_REQUEST['yinlian'])) : 0;
        $flag = !empty($_REQUEST['flag']) ? htmlspecialchars(trim($_REQUEST['flag'])) : 1;//支付入口1:未生成订单2:已生成订单
        $order_id = !empty($_REQUEST['order_id']) ? htmlspecialchars(trim($_REQUEST['order_id'])) : 0;//订单id
        $order_sn = !empty($_REQUEST['uniquecode']) ? htmlspecialchars(trim($_REQUEST['uniquecode'])) : '';//订单编号
        if( $yinlian ){
            //获取当前订单信息
            $orderInfo = $this->orderMod->getOne(array('cond'=>'order_sn = '.$order_sn));
            if($orderInfo){
                $this->jsonError('订单已生成，前往支付！');
            }
        }
        $goods_amount = !empty($_REQUEST['goods_amount']) ? htmlspecialchars(trim($_REQUEST['goods_amount'])) : 1;//订单总价
        $sendout = !empty($_REQUEST['sendout']) ? htmlspecialchars(trim($_REQUEST['sendout'])) : 1;//配送方式1:自提2:配送
        $phone = !empty($_REQUEST['phone']) ? htmlspecialchars(trim($_REQUEST['phone'])) : '';//用户手机号
        $pd_amount = !empty($_REQUEST['pd_amount']) ? htmlspecialchars(trim($_REQUEST['pd_amount'])) : 0;//积分抵扣金额
        $pd_point = !empty($_REQUEST['pd_point']) ? htmlspecialchars(trim($_REQUEST['pd_point'])) : 0;//抵扣积分
        $couponid = !empty($_REQUEST['couponid']) ? htmlspecialchars(trim($_REQUEST['couponid'])) : 0;//优惠券id
        $usercouponid = !empty($_REQUEST['usercouponid']) ? htmlspecialchars(trim($_REQUEST['usercouponid'])) : 0;//优惠券-用户关联表id
        $coupon_amount = !empty($_REQUEST['coupon_amount']) ? htmlspecialchars(trim($_REQUEST['coupon_amount'])) : 0;//优惠券金额
        $discount_type = !empty($_REQUEST['discount_type']) ? htmlspecialchars(trim($_REQUEST['discount_type'])) : 1;//折扣方式1:折扣优惠2:金额优惠
        $discount_num = !empty($_REQUEST['discount_num']) ? htmlspecialchars(trim($_REQUEST['discount_num'])) : 0;//折扣数
        $discount_amount = !empty($_REQUEST['discount_amount']) ? htmlspecialchars(trim($_REQUEST['discount_amount'])) : 0;//折扣后减价
        $order_amount = !empty($_REQUEST['order_amount']) ? htmlspecialchars(trim($_REQUEST['order_amount'])) : 0;//应付金额
        $fx_user_id = !empty($_REQUEST['fx_user_id']) ? htmlspecialchars(trim($_REQUEST['fx_user_id'])) : 0;//分销id
        $fx_money = !empty($_REQUEST['fx_dis_amount']) ? htmlspecialchars(trim($_REQUEST['fx_dis_amount'])) : 0;//分销优惠金额
        $source_id = !empty($_REQUEST['source_id']) ? htmlspecialchars(trim($_REQUEST['source_id'])) : 0;//来源id
        $buyer_address = !empty($_REQUEST['buyer_address']) ? htmlspecialchars(trim($_REQUEST['buyer_address'])) : '';//补充地址
        $paytype = !empty($_REQUEST['paytype']) ? htmlspecialchars(trim($_REQUEST['paytype'])) : 1;//付款方式1:微信2:支付宝3:线下4:余额支付5:免费兑换9:预购
        $pei_time = !empty($_REQUEST['pei_time']) ? $_REQUEST['pei_time'] : 0;//配送时间
        $pei_money = !empty($_REQUEST['pei_price']) ? htmlspecialchars(trim($_REQUEST['pei_price'])) : 0;//配送金额
        $delivery = !empty($_REQUEST['delivery']) ? htmlspecialchars(trim($_REQUEST['delivery'])) : '';//配送地址
        $pei_longitude = !empty($_REQUEST['pei_longitude']) ? htmlspecialchars(trim($_REQUEST['pei_longitude'])) : 0;//配送地址经度
        $pei_latitude = !empty($_REQUEST['pei_latitude']) ? htmlspecialchars(trim($_REQUEST['pei_latitude'])) : 0;//配送地址纬度
        $phoneSource = !empty($_REQUEST['phoneSource']) ? trim($_REQUEST['phoneSource']) : 3;
        $pei_time = $pei_time ? strtotime($pei_time) : 0;
        $pei_money = $sendout == 1 ? 0 : $pei_money;
        if ($flag == 1) {
            if ($paytype == 9) {
                if ($phone != '0102') {
                    $this->jsonError('预购号码有误!');
                }
                if ($fx_user_id) {
                    $this->jsonError('预购禁止使用分销码!');
                }
            } else {
                if (!preg_match('/^\d{11}$/', $phone)) {
                    $this->jsonError('无效的手机号');
                }
            }
            //获取购物车信息
            $sql = "select a.*,b.original_img from bs_cart as a left join bs_store_goods as b on a.goods_id = b.id where a.uniquecode='{$order_sn}'";
            $orderGoodsInfo = $this->orderMod->querySql($sql);
            if (empty($orderGoodsInfo)) {
                $this->jsonError('获取订单信息失败!');
            }
            //获取用户信息
            $sql = 'select id,phone,point,amount from  ' . DB_PREFIX . 'user  where  mark =1 and is_use=1 and phone = ' . $phone;
            $res = $this->orderMod->querySql($sql);
            $userInfo = $res[0];
            if (empty($userInfo)) {//用户不存在，自动注册
                //获取注册积分
                $pSql = "SELECT *  FROM " . DB_PREFIX . 'user_point_site';
                $res = $this->userMod->querySql($pSql);
                $register_point = $res[0]['register_point'];
                $register_recharge = $res[0]['register_recharge'];
                $tmp = array(
                    'phone' => $phone,
                    'username' => $phone,
                    'password' => md5('123456'),
                    'add_time' => time(),
                    'login_type' => 'member',
                    'store_id' => $this->storeId,
                    'store_cate_id' => $this->storecate,
                    'point' => $register_point,
                    'resource' => 2
//                    'amount' => $register_recharge
                );
                $user_id = $this->userMod->doInsert($tmp);
//                $amountData = array(
//                    'order_sn' => '',
//                    'type'     => 3,
//                    'status'   => 4,
//                    'c_money'  => $register_recharge,
//                    'new_money'=> $register_recharge,
//                    'source'   => 4,
//                    'add_user' => $user_id,
//                    'add_time' => time(),
//                    'mark'     => 1
//                );
//                $amountLog = &m('amountLog');
//                $amountLog->doInsert($amountData);

                //注册赠卷
                $coupon = &m('coupon');
                $userCoupon = &m('userCoupon');
                $c_time = time();
                $juan = $coupon->getOne(array('cond'=>'id = 92'));
                if($juan){
                    $uc = array(
                        'user_id' => $user_id,
                        'c_id' => $juan['id'],
                        'remark' => '新用户注册赠卷',
                        'source' => 4,
                        'start_time' => $c_time,
                        'end_time' => $c_time + 3600 * 24 * $juan['limit_times'],
                        'add_user' => $user_id,
                        'add_time' => $c_time
                    );
                    if($userCoupon->doInsert($uc)){
                        $this->userMod->sendMessage($user_id);
                    }
                }

                //注册日志
                $logData = array(
                    'operator' => '--',
                    'username' => $phone,
                    'add_time' => time(),
                    'note' => '注册获得' . $register_point . '睿积分',
                    'userid' => $user_id,
                    'deposit' => $register_point,
                    'expend' => '-',
                );
                $pointLogMod = &m("pointLog");
                $pointLogMod->doInsert($logData);

                //注册成功发短信
                $userMod = &m('user');
                $userMod->sendSms($phone);
                //获取设置注册送电子券
                $systemConsoleMod = &m('systemConsole');
                $userCoupon =& m('userCoupon');
                $getCouponActivityStatus = $systemConsoleMod->getCouponActivityStatus();//获取设置注册送电子券是否开启
                if ($getCouponActivityStatus['1'] == 1) {
                    $coupon = $systemConsoleMod->getSetCoupon();
                    $duiCoupon = $systemConsoleMod->getSetDuiCoupon();
                    $limitTiems = $coupon[0]['limit_times'] * 3600 * 24;
                    $limit = $duiCoupon[0]['limit_times'] * 3600 * 24;
                    if (!empty($coupon)) {
                        $data = array(
                            'c_id' => $coupon[0]['id'],
                            'remark' => '注册送抵扣券',
                            'add_time' => time(),
                            'start_time' => time(),
                            'end_time' => time() + $limitTiems,
                            'user_id' => $user_id
                        );
                        $userCoupon->doInsert($data);
                        //赠券发短信提醒
                        $userMod->sendMessage($user_id);
                    }
                    if (!empty($duiCoupon)) {
                        $data = array(
                            'c_id' => $duiCoupon[0]['id'],
                            'remark' => '注册送兑换券',
                            'add_time' => time(),
                            'start_time' => time(),
                            'end_time' => time() + $limit,
                            'user_id' => $user_id
                        );
                        $userCoupon->doInsert($data);
                        //赠券发短信提醒
                        $userMod->sendMessage($user_id);
                    }

                }
            } else {//用户存在，初始化用户id
                $user_id = $userInfo['id'];
            }
            if ($paytype == 4) {
                $code = $_REQUEST['code'];
                if (empty($code)) {
                    $this->jsonError('验证码必填!');
                }
                $smsCode = $this->getSmsCode($phone);
                if ($code != $smsCode) {
                    $this->jsonError('验证码不正确!');
                }

                if ($userInfo['amount'] < $order_amount) {
                    $this->jsonError('用户余额不足');
                }
            }
            //获取地址表数据
            $userAddressMod = &m('userAddress');
            $user_addr = $userAddressMod->getInfoByUidAndType($user_id);
            $buyer_name = empty($user_addr['name']) ? $phone : $user_addr['name'];
            $buyer_phone = empty($user_addr['phone']) ? $phone : $user_addr['phone'];
            $address = empty($user_addr['address']) ? '门店经营买家上门自提' : $user_addr['address'];
            //生成小票编号
            $number_order = $this->orderMod->createNumberOrder($this->storeId);
            //获取当前站点名称
            $store_name = $this->storeMod->getNameById($this->storeId, $this->languageId);
            try {
                //事务开始
                $this->orderMod->begin();
                // 先插入主订单
                $insert_main_data = array(
                    'order_sn' => $order_sn, //订单编号
                    'store_id' => $this->storeId, //卖家店铺id
                    'number_order' => $number_order, //生成小票编号
                    'store_name' => $store_name, //卖家店铺名称
                    'goods_amount' => $goods_amount, //商品总价格
                    'order_state' => 10, //订单状态：0(已取消)10(默认):未付款;20:已付款;30:已发货;40:区域配送;50:已收货;'
                    'order_from' => 1, //1电脑端WEB  2 手机端mobile
                    'singleperson' => $_SESSION['store']['userId'], //操作人员ID
                    'is_old' => 2,//新代客下单
                    'add_time' => time(),//订单生成时间
                    'sendout' => $sendout,
                    'buyer_id' => $user_id,
                    'buyer_name' => $buyer_name,
                    'buyer_phone' => $buyer_phone,
                    'buyer_address' => $address,
                    'order_amount' => $order_amount,
                    'pd_amount' => $pd_amount,
                    'discount_type' => $discount_type,
                    'discount_num' => $discount_amount > 0 ? $discount_num : 0,
                    'discount' => $discount_amount,
                    'cid' => $couponid,
                    'cp_amount' => $coupon_amount,
                    'Appoint_store_id' => $this->storeId,
                    'pei_time' => $pei_time,
                    'shipping_fee' => $pei_money,
                    'fx_user_id' => $fx_user_id,
                    'is_source' => 2,
                );
                //获取分销信息
                $fxInfo = array();
                $fxUserMod = &m('fxuser');
                if ($fx_user_id) {
                    $fxInfo = $fxUserMod->getOne(array('cond' => "id={$fx_user_id}"));
                }
                if (!empty($fxInfo)) {
                    $insert_main_data['fx_phone'] = $fxInfo['fx_code'];
                    $insert_main_data['fx_discount_rate'] = $fxInfo['discount'];
                }
                $order_id = $this->orderMod->doInsert($insert_main_data);
                //清空购物车
                $this->cartMod->doDrops("uniquecode='{$order_sn}'");
                // 先插入子订单
                foreach ($orderGoodsInfo as $k => $v) {
                    $insert_sub_data = array(
                        'order_id' => $order_sn, //订单编号
                        'goods_id' => $v['goods_id'], //商品ID
                        'goods_name' => addslashes($v['goods_name']), //商品名称
                        'goods_price' => $v['goods_price'], //商品价格
                        'goods_num' => $v['goods_num'], //商品数量
                        'goods_image' => $v['original_img'], //商品图片
                        'goods_pay_price' => $v['member_goods_price'], //商品实际成交价
                        'spec_key_name' => $v['spec_key_name'], //规格名
                        'spec_key' => $v['spec_key'], //规格
                        'prom_type' => $v['prom_type'], //0 普通商品,1 限时抢购,2团购,3促销优惠,4,组合销售,5.买赠活动
                        'prom_id' => $v['prom_id'], //活动ID
                        'store_id' => $this->storeId, //店铺ID
                        'order_state' => 10, //'订单状态：0(已取消)10(默认):未付款;20:已付款;30:已发货;40:区域配送;50:已收货;',
                        'shipping_store_id' => $this->storeId, //配送区域站点ID
                        'add_time' => time(),//添加时间
                        'good_id' => $this->getGoodId($v['goods_id']),
                        'deduction' => $this->getDeduction($v['goods_id']),
                        'buyer_id' => $user_id
                    );
                    $this->orderGoodsMod->doInsert($insert_sub_data);
                }
                //新订单表数据插入
                $payment_type = $paytype;
                $pay_name = '';
                switch ($paytype) {
                    case 1:
                        $payment_type = 2;
                        $pay_name = '微信支付';
                        break;
                    case 2:
                        $payment_type = 1;
                        $pay_name = '支付宝支付';
                        break;
                    case 3:
                        $payment_type = 4;
                        $pay_name = '线下支付';
                        break;
                    case 4:
                        $payment_type = 3;
                        $pay_name = '余额支付';
                        break;
                    case 5:
                        $payment_type = 5;
                        $pay_name = '免费兑换';
                        break;
                    default:
                        break;
                }
                $newOrderInfo = array();
                $newOrderInfo['store_id'] = $this->storeId;
                $newOrderInfo['order_sn'] = $order_sn;
                $newOrderInfo['buyer_id'] = $user_id;
                $newOrderInfo['goods_amount'] = $goods_amount;
                $newOrderInfo['order_amount'] = $order_amount;
                $newOrderInfo['sendout'] = $sendout;
                $newOrderInfo['shipping_fee'] = $pei_money;
                $newOrderInfo['delivery'] = !empty($delivery) ? $delivery : $buyer_address;
                $newOrderInfo['delivery_lal'] = $pei_longitude . ',' . $pei_latitude;
                $newOrderInfo['seller_msg'] = '';
                $newOrderInfo['discount_num'] = $discount_amount > 0 ? $discount_num : 0;
                $newOrderInfo['discount'] = $discount_amount;
                $newOrderInfo['fx_user_id'] = $fx_user_id;
                $newOrderInfo['fx_money'] = $fx_money;
                $newOrderInfo['cp_amount'] = $coupon_amount;
                $newOrderInfo['pd_amount'] = $pd_amount;
                $newOrderInfo['number_order'] = $number_order;
                $newOrderInfo['payment_source'] = $source_id;
                $newOrderInfo['pei_time'] = $pei_time;
                $createOrderRes = $this->orderMod->createOrder($newOrderInfo, 3, $_SESSION['store']['userId']);
                //记录用券日志
                if ($couponid !== 0) {
                    $couponLogMod = &m('couponLog');
                    $data = array(
                        'user_coupon_id' => $usercouponid,
                        'coupon_id' => $couponid,
                        'user_id' => $user_id,
                        'order_id' => $order_id,
                        'order_sn' => $order_sn,  // by xt 2019.03.21
                        'add_time' => time()
                    );
                    $couponLogMod->doInsert($data);
                }
                //扣除用户睿积分
                if ($pd_point) {
                    $user_point = $userInfo['point'] - $pd_point;
                    $this->userMod->doEdit($user_id, array("point" => $user_point));
                    //睿积分日志
                    $pointLogMod = &m("pointLog");
                    $logMessage = $a['Rui_order'] . '：' . $order_sn . ' ' . $a['Rui_use'] . "：" . $pd_point . $a['Rui_fen'];
                    $pointLogMod->add($phone, $logMessage, $user_id, 0, $pd_point, $order_sn);
                }
                if (empty($order_id) || empty($createOrderRes)) {
                    //事务回滚
                    $this->orderMod->rollback();
                    $this->jsonError('订单保存失败!');
                } else {
                    //事务提交
                    $this->orderMod->commit();
                }
            } catch (Exception $e) {
                //事务回滚
                $this->orderMod->rollback();
                writeLog($e->getMessage());
                $this->jsonError('订单保存失败!');
            }
        } else {
            //获取用户信息
            $sql = 'select id,phone,point,amount from  ' . DB_PREFIX . 'user  where  mark =1 and is_use=1 and phone = ' . $phone;
            $res = $this->orderMod->querySql($sql);
            $userInfo = $res[0];
            if ($paytype == 4) {
                $code = $_REQUEST['code'];
                if (empty($code)) {
                    $this->jsonError('验证码必填!');
                }
                $smsCode = $this->getSmsCode($phone);
                if ($code != $smsCode) {
                    $this->jsonError('验证码不正确!');
                }

                if ($userInfo['amount'] < $order_amount) {
                    $this->jsonError('用户余额不足');
                }
            }
            //新订单表数据插入
            $payment_type = $paytype;
            $pay_name = '';
            switch ($paytype) {
                case 1:
                    $payment_type = 2;
                    $pay_name = '微信支付';
                    break;
                case 2:
                    $payment_type = 1;
                    $pay_name = '支付宝支付';
                    break;
                case 3:
                    $payment_type = 4;
                    $pay_name = '线下支付';
                    break;
                case 4:
                    $payment_type = 3;
                    $pay_name = '余额支付';
                    break;
                case 5:
                    $payment_type = 5;
                    $pay_name = '免费兑换';
                    break;
                default:
                    break;
            }
        }
        if (in_array($paytype, array(3, 4, 5))) {
            switch ($paytype) {
                case 3:
                    $payment_code = '现金付款';
                    break;
                case 4:
                    $payment_code = '余额支付';
                    $source_id = '1758421';
                    break;
                case 5:
                    $payment_code = '免费兑换';
                    $source_id = '1758421';
                    break;
                default:
                    $payment_code = '';
                    break;
            }
            //更新订单主表
            $orderData = array(
                'payment_code' => $payment_code,
                'payment_time' => time(),
                'Appoint' => 1, //1未被指定 2被指定
                'install_time' => time(), //区域配送安装完成时间
                'source_id' => $source_id,
                'buyer_address' => $buyer_address,
            );
            $orderData['order_state'] = 20;
            $orderData['region_install'] = 10;//10未配送 20已配送
            $orderData['singleperson'] = $_SESSION['store']['userId']; //操作人员ID
            $this->orderMod->doEditSpec(array('order_sn' => $order_sn), $orderData);
            //更新订单子表
            $detailData = array(
                'order_state' => 20
            );
            $this->orderGoodsMod->doEditSpec(array('order_id' => $order_sn), $detailData);
            //新订单表更新
            $this->orderMod->update_pay_time($this->storeId, $order_sn, $pay_name, $payment_type, $orderData['order_state']);
            //分销订单
            $fxOrderMod = &m('fxOrder');
            $fxOrderMod->addFxOrderByOrderSn($order_sn, 1);
            if ($paytype == 4) {
                $orderSms = array(
                    'type' => 1,
                );
                $smsMod = &m('sms');
                $smsMod->doEditSpec(array('code' => $code), $orderSms);
                if (!empty($order_amount)) {
                    $orderInfo = $this->orderMod->getOne(array('cond' => "`order_sn` = '{$order_sn}'", 'fields' => "order_amount"));
                    $userData = $this->userMod->getOne(array('cond' => "`id` = '{$userInfo['id']}'", 'fields' => 'amount'));
                    $amountLogData = array(
                        'type' => 2,
                        'status' => 2,
                        'c_money' => $orderInfo['order_amount'],
                        'old_money' => $userData['amount'],
                        'new_money' => $userData['amount'] - $orderInfo['order_amount'],
                        'source' => 4,
                        'add_user' => $userInfo['id'],
                        'add_time' => time(),
                        'order_sn' => $order_sn,
                        'class' => 0
                    );
                    $userdata = array(
                        'amount' => $userData['amount'] - $orderInfo['order_amount']
                    );
                    $this->userMod->doEdit($userInfo['id'], $userdata);
                    $this->createAmountlog($amountLogData);
                }
            }
            //更新库存
            $this->updateStock($order_sn);
        }
        $message = '付款成功';
        if ($paytype == 9) {
            $message = '预购成功';
        }
//        $this->jsonResult($message, array('url' => "?app=customerOrder&act=index&lang_id=" . $this->languageId, 'order_id' => $order_id));
        $this->jsonResult($message, array('url' => "?app=order&act=index&lang_id=" . $this->languageId, 'order_id' => $order_id));
    }

    //验证码
    public function getSmsCode($phone)
    {
        $smsMod = &m('sms');
        $sql = 'select phone, code from bs_sms where type=0 and phone = ' . $phone . ' order by id desc limit 1';
        $data = $smsMod->querySql($sql);
        return $data[0]['code'];
    }

    //生成充值记录
    public function  createAmountlog($data)
    {
        $amountLogId = $this->amountLogMod->doInsert($data);
        return $amountLogId;
    }

    // 更新规格库存 和 无规格库存
    public function updateStock($out_trade_no)
    {
        //  更新库存
        $sql = "SELECT sg.goods_id,sg.spec_key,sg.goods_num,sg.deduction,sg.good_id FROM " .
            DB_PREFIX . "order as r LEFT JOIN " .
            DB_PREFIX . "order_goods as sg ON  r.order_sn = sg.order_id WHERE sg.order_id =" . $out_trade_no;
        $orderRes = $this->areaGoodMod->querySql($sql);
        foreach ($orderRes as $k => $v) {
            if (!empty($v['spec_key'])) {
                if ($v['deduction'] == 1) {
                    $query_id = "select `id` from " . DB_PREFIX . "store_goods_spec_price where `goods_id` = '{$v['good_id']}' and `key` = '{$v['spec_key']}' ";
                    $res_query = $this->storeGoodItemPriceMod->querySql($query_id);
                    $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods_spec_price WHERE `goods_id` = '{$v['good_id']}' and `key` = '{$v['spec_key']}'";
                    $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                    foreach ($res_query as $key => $val) {
                        $goodStorage = $specInfo[0]['goods_storage'] - $v['goods_num'];
                        if ($goodStorage <= 0) {
                            $goodStorage = 0;
                        }
                        $condition = array(
                            'goods_storage' => $goodStorage
                        );
                        $res = $this->storeGoodItemPriceMod->doEdit($val['id'], $condition);
                    }
                    if ($res) {
                        $infoSql = "select goods_storage,id from  " . DB_PREFIX . "store_goods WHERE `goods_id` = '{$v['good_id']}'";
                        $Info = $this->areaGoodMod->querySql($infoSql);
                        $goodsStorage = $Info[0]['goods_storage'] - $v['goods_num'];
                        if ($goodsStorage <= 0) {
                            $goodsStorage = 0;
                        }
                        $cond = array(
                            'goods_storage' => $goodsStorage
                        );
                        foreach ($Info as $key1 => $val1) {
                            $this->areaGoodMod->doEdit($val1['id'], $cond);
                        }
                    }
                    $Sql = "select goods_storage from  " . DB_PREFIX . "goods_spec_price WHERE `goods_id` = '{$v['good_id']}' and `key` = '{$v['spec_key']}'";

                    $goodsSpec = $this->areaGoodMod->querySql($Sql);
                    $conditionalStorage = $goodsSpec[0]['goods_storage'] - $v['goods_num'];
                    if ($conditionalStorage <= 0) {
                        $conditionalStorage = 0;
                    }
                    $conditional = array(
                        'goods_storage' => $conditionalStorage
                    );
                    $goodsSpecSql = "update " . DB_PREFIX . "goods_spec_price set goods_storage = " . $conditional['goods_storage'] . " where goods_id=" . $v['good_id'] . " and `key` ='{$v['spec_key']}'";
                    $result = $this->goodsSpecPriceMod->doEditSql($goodsSpecSql);
                    if ($result) {
                        $goodSql = "select goods_storage from  " . DB_PREFIX . "goods WHERE `goods_id` = '{$v['good_id']}'";
                        $goodInfo = $this->areaGoodMod->querySql($goodSql);
                        $goodCondStorage = $goodInfo[0]['goods_storage'] - $v['goods_num'];
                        if ($goodCondStorage <= 0) {
                            $goodCondStorage = 0;
                        }
                        $goodCond = array(
                            'goods_storage' => $goodCondStorage
                        );
                        $this->goodsMod->doEdit($v['good_id'], $goodCond);
                    }
                } else {
                    $query_id = "select `id` from " . DB_PREFIX . "store_goods_spec_price where `store_goods_id` = '{$v['goods_id']}' and `key` = '{$v['spec_key']}' ";
                    $res_query = $this->storeGoodItemPriceMod->querySql($query_id);
                    $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods_spec_price WHERE `store_goods_id` = '{$v['goods_id']}' and `key` = '{$v['spec_key']}'";
                    $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                    $conditionStorage = $specInfo[0]['goods_storage'] - $v['goods_num'];
                    if ($conditionStorage <= 0) {
                        $conditionStorage = 0;
                    }
                    $condition = array(
                        'goods_storage' => $conditionStorage
                    );
                    $res = $this->storeGoodItemPriceMod->doEdit($res_query[0]['id'], $condition);
                    if ($res) {
                        $infoSql = "select goods_storage from  " . DB_PREFIX . "store_goods WHERE `id` = '{$v['goods_id']}'";
                        $Info = $this->areaGoodMod->querySql($infoSql);
                        $condStorage = $Info[0]['goods_storage'] - $v['goods_num'];
                        if ($condStorage <= 0) {
                            $condStorage = 0;
                        }
                        $cond = array(
                            'goods_storage' => $condStorage
                        );
                        $this->areaGoodMod->doEdit($v['goods_id'], $cond);
                    }
                }
            } else {
                if ($v['deduction'] == 1) {
                    $infoSql = "select goods_storage,id from  " . DB_PREFIX . "store_goods WHERE `goods_id` = '{$v['good_id']}'";
                    $Info = $this->areaGoodMod->querySql($infoSql);
                    $condStorage = $Info[0]['goods_storage'] - $v['goods_num'];
                    if ($condStorage <= 0) {
                        $condStorage = 0;
                    }
                    $cond = array(
                        'goods_storage' => $condStorage
                    );
                    foreach ($Info as $key1 => $val1) {
                        $this->areaGoodMod->doEdit($val1['id'], $cond);
                    }
                    $goodSql = "select goods_storage from  " . DB_PREFIX . "goods WHERE `goods_id` = '{$v['good_id']}'";

                    $goodInfo = $this->areaGoodMod->querySql($goodSql);
                    $goodCondStorage = $goodInfo[0]['goods_storage'] - $v['goods_num'];
                    if ($goodCondStorage <= 0) {
                        $goodCondStorage = 0;
                    }
                    $goodCond = array(
                        'goods_storage' => $goodCondStorage
                    );
                    $this->goodsMod->doEdit($v['good_id'], $goodCond);
                } else {
                    $specInfoSql = "select goods_storage from  " . DB_PREFIX . "store_goods WHERE `id` = '{$v['goods_id']}'";
                    $specInfo = $this->areaGoodMod->querySql($specInfoSql);
                    $condition = $specInfo[0]['goods_storage'] - $v['goods_num'];
                    if ($condition <= 0) {
                        $condition = 0;
                    }
                    $condition = array(
                        'goods_storage' => $condition
                    );
                    $this->areaGoodMod->doEdit($v['goods_id'], $condition);
                }

            }
        }
    }

    /**
     * 订单删除
     * @author wangshuo
     * @date 2017-11-20
     */
    public function dele()
    {
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim($_REQUEST['order_sn'])) : '';//订单编号
//        $store_id = !empty($_REQUEST['store_id']) ? htmlspecialchars(trim($_REQUEST['store_id'])) : 0;//店铺ID
        $userMod = &m('user');
        $pointLogMod = &m("pointLog");
        $neworderMod = &m("order" . $this->storeId);
        //返还用户的积分值
        $point_log = $pointLogMod->getOne(array("cond" => "order_sn='{$order_sn}'"));
        if ($point_log) {
            $user_id = $point_log['userid'];
            $user_info = $userMod->getOne(array("cond" => "id=" . $user_id));
            $user_point = $user_info['point'] + $point_log['expend'];
            $res = $userMod->doEdit($user_id, array("point" => $user_point));
            //积分日志
            if ($res) {
                $logMessage = "取消订单：" . $order_sn . " 获取：" . $point_log['expend'] . "睿积分";
                $pointLogMod->add($user_info['phone'], $logMessage, $user_id, $point_log['expend'], '-');
            }
        }
        //删除订单信息
        $this->orderMod->doMarkKey('order_sn', $order_sn);
        $this->orderGoodsMod->doMarkKey('order_id', $order_sn);
        $neworderMod->doMarkKey('order_sn', $order_sn);
        $this->jsonResult('订单已取消!');
    }

    /**
     * 订单取消
     * @author wangshuo
     * @date 2017-11-20
     */
    public function cancle()
    {
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim($_REQUEST['order_sn'])) : '';//订单编号
        $this->orderMod->cancleOrder($order_sn);
        $this->jsonResult('订单已取消!');
    }

    /**
     * 获取排列数组
     */
    public function arrangement($a, $m)
    {
        $r = array();

        $n = count($a);
        if ($m <= 0 || $m > $n) {
            return $r;
        }

        for ($i = 0; $i < $n; $i++) {
            $b = $a;
            $t = array_splice($b, $i, 1);
            if ($m == 1) {
                $r[] = $t;
            } else {
                $c = $this->arrangement($b, $m - 1);
                foreach ($c as $v) {
                    $r[] = array_merge($t, $v);
                }
            }
        }

        return $r;
    }

    public function getOrderStatus(){
        $orderSn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim($_REQUEST['order_sn'])) : '';
        $data   = $this->orderMod->getOne(array("cond" => "order_sn='{$orderSn}'"));
        $this->setData(array('order_state' => $data['order_state']), 0);
    }
}
