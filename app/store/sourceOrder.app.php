<?php

/**
 * 订单列表
 * @author wangshuo
 * @date 2017-10-20
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class sourceOrderApp extends BaseStoreApp {

    private $lang_id;
    private $orderGoodsMod;
    private $orderMod;
    private $storeMod;
    private $giftGoodMod;
    private $sourceListMod;
    private $orderDetailMod;
    private $userMod;
    private $userAddressMod;
    private $cityMod;
    private $countryMod;
    private $zoneMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
        $this->orderGoodsMod = &m('orderGoods');
        $this->orderMod = &m('order');
        $this->storeMod = &m('store');
        $this->giftGoodMod = &m('giftGood');
        $this->sourceListMod = &m('sourceList');
        $this->orderDetailMod = &m('orderDetail');
        $this->userMod = &m('user');
        $this->userAddressMod = &m('userAddress');
        $this->cityMod = &m('city');
        $this->countryMod = &m('country');
        $this->zoneMod = &m('zone');
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 来源订单展示页面
     * @author wangs
     * @date 2018/5/21
     */
    public function index() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $order_sn = !empty($_REQUEST['order_sn']) ? htmlspecialchars(trim(addslashes($_REQUEST['order_sn']))) : '';
        $goods_name = !empty($_REQUEST['goods_name']) ? htmlspecialchars(trim(addslashes($_REQUEST['goods_name']))) : '';
        $store_id = !empty($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : '';
        $source_id = !empty($_REQUEST['source_id']) ? intval($_REQUEST['source_id']) : '';
        $payment_code = !empty($_REQUEST['payment_code']) ? htmlspecialchars(trim($_REQUEST['payment_code'])) : '';
        $buyer_email = !empty($_REQUEST['buyer_email']) ? htmlspecialchars(trim($_REQUEST['buyer_email'])) : '';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $where = '';
        if (!empty($order_sn)) {
            $where .= " and g.order_sn like '%" . $order_sn . "%'";
        }
        if (!empty($goods_name)) {
            $where .= " and f.goods_name like '%" . $goods_name . "%'";
        }
        if (!empty($payment_code)) {
            $where .= " and g.payment_code like '%" . $payment_code . "%'";
        }
        if (!empty($buyer_email)) {
            $where .= " and g.buyer_email like '%" . $buyer_email . "%'";
        }
        if (!empty($store_id)) {
            $where .= " and g.store_id = '{$store_id}'";
        }
        if (!empty($source_id)) {
            $where .= " and g.source_id = '{$source_id}'";
        }
        $this->assign("p", $p);
        $this->assign('order_sn', $order_sn);
        $this->assign('goods_name', $goods_name);
        $this->assign('payment_code', $payment_code);
        $this->assign('buyer_email', $buyer_email);
        $this->assign("store_id", $store_id);
        $this->assign("source_id", $source_id);
        // 1总代理 2经销商
        $auth = $this->auth;
        // 1总代理
        //订单列表页数据
        $sql = 'select distinct g.order_sn, g.*, a.*, g.add_time,sou.img from '
                . DB_PREFIX . 'order as g left join '
                . DB_PREFIX . 'user_address a' . ' on a.user_id = g.buyer_id left join '
                . DB_PREFIX . 'order_goods as f ' . ' on f.order_id = g.order_sn left join '
                . DB_PREFIX . 'store_source as sou ' . ' on sou.id = g.source_id and sou.store_id =' . $this->storeId
                . ' where g.Appoint =2 and g.source_id!=1758421' . $where
                . ' order by g.order_id desc';
        $result = $this->orderMod->querySqlPageData($sql);
        $data = $result['list'];
        //订单商品数据
        foreach ($data as $k => $v) {
            $v_where = "order_id=" . $v['order_sn'];
            $cond = array(
                'cond' => $v_where
            );
            $list = $this->orderGoodsMod->getData($cond);
            $data[$k]['goods_list'] = $list;
//            //赠品
//            $sql = "select * from " . DB_PREFIX . "gift_goods where id=" . $v['gift_id'];
//            $res = $this->giftGoodMod->querySql($sql);
//            $data[$k]['gift'] = $res;
        }
        $this->assign('data', $data);
        $this->assign('page_html', $result['ph']);
        $OrderStatus = array(
            "0" => $a['Canceled'],
            "10" => $a['Unpaid'],
            "20" => $a['payment'],
            "30" => $a['Shipped'],
            "40" => $a['Shipped_1'],
            "50" => $a['Receivedgoods'],
        );
        $this->assign('symbol', $this->symbol);
        $this->assign('status', $OrderStatus);
        $this->assign('auth', $auth);
        $this->assign('store', $this->getUseStore());
        $this->assign('source', $this->getUsesource());
        $this->assign('lang_id', $this->lang_id);
        $this->display('sourceOrder/index.html');
    }

    /**
     * 获取启用的站点
     * @author wang'shuo
     * @date 2017-12-25
     */
    public function getUseStore() {
        $sql = 'SELECT  c.id,l.store_name  FROM  ' . DB_PREFIX . 'store  as c
                 left join  ' . DB_PREFIX . 'store_lang as l on  c.id = l.store_id
                 WHERE c.is_open = 1 and l.distinguish = 0  and  l.lang_id =' . $this->defaulLang . '  and c.store_cate_id=' . $this->country_id . ' order by c.id';
        $res = $this->storeMod->querySql($sql);
        return $res;
    }

    /**
     * 获取省市区的地址
     * @author wangshuo 
     * @date 2018-5-14
     */
    public function getAddress($areaAddress) {
        $areaAddress = explode('_', $areaAddress);

        if (count($areaAddress) == 3) {
            $result = $this->cityMod->getAreaName($areaAddress[0]) . ' ' . $this->cityMod->getAreaName($areaAddress[1]) . ' ' . $this->cityMod->getAreaName($areaAddress[2]);
        } elseif (count($areaAddress) == 2) {
            $country = $this->countryMod->getCountryName($areaAddress[0]);
            $zone = $this->zoneMod->getZoneName($areaAddress[1]);
            $result = $country . ' ' . $zone;
        }
        return $result;
    }

    /**
     * 获取商品来源
     * @author wangshuo
     * @date 2018-5-21
     */
    public function getUsesource() {
        $sql = 'SELECT  id,name  FROM  ' . DB_PREFIX . 'store_source where store_id = ' . $this->storeId . ' order by id';
        $res = $this->sourceListMod->querySql($sql);
        return $res;
    }

    /**
     * 代客下单
     * @author wangshuo
     * @date 2018-5-10
     */
    public function add() {
        $sql = 'select * from ' . DB_PREFIX . 'store_source where store_id= ' . $this->storeId . ' order by sort';
        $res = $this->sourceListMod->querySql($sql);
        $this->assign('info', $res);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('symbol', $this->symbol);
        $this->display('sourceOrder/index_add.html');
    }

    /**
     * 生成订单
     * @author wangshuo
     * @date 2018-5-14
     */
    public function guestOrder() {
        $user_id = !empty($_REQUEST['user_id']) ? htmlspecialchars($_REQUEST['user_id']) : '';
        $lang_id = !empty($_REQUEST['lang_id']) ? htmlspecialchars($_REQUEST['lang_id']) : '';
        $user_name = !empty($_REQUEST['user_name']) ? htmlspecialchars($_REQUEST['user_name']) : '';
        $source_id = !empty($_REQUEST['source']) ? htmlspecialchars($_REQUEST['source']) : '';
        $allPrices = !empty($_REQUEST['allPrices']) ? $_REQUEST['allPrices'] : ''; //商品总数量
        $totalQuantity = !empty($_REQUEST['totalQuantity']) ? $_REQUEST['totalQuantity'] : ''; //商品总价格
        $goods_id = !empty($_REQUEST['goods_id']) ? $_REQUEST['goods_id'] : '';
        $discount = !empty($_REQUEST['discount']) ? $_REQUEST['discount'] : ''; //商品优惠
        $store_goods_id = !empty($_REQUEST['store_goods_id']) ? $_REQUEST['store_goods_id'] : '';
        $store_goods_name = !empty($_REQUEST['store_goods_name']) ? $_REQUEST['store_goods_name'] : '';
        $store_goods_key = !empty($_REQUEST['store_goods_key']) ? $_REQUEST['store_goods_key'] : '';
        $store_goods_attr = !empty($_REQUEST['store_goods_attr']) ? $_REQUEST['store_goods_attr'] : '';
        $store_goods_url = !empty($_REQUEST['store_goods_url']) ? $_REQUEST['store_goods_url'] : '';
        $store_goods_iprice = !empty($_REQUEST['store_goods_iprice']) ? $_REQUEST['store_goods_iprice'] : '';
        $store_goods_number = !empty($_REQUEST['store_goods_number']) ? $_REQUEST['store_goods_number'] : '';
        // 获取用户Email信息
        $sql = 'select email from  ' . DB_PREFIX . 'user  where  mark =1 and is_use=1 and id = ' . $user_id;
        $userEmail = $this->userMod->querySql($sql);
        if ($userEmail[0]['email'] == '') {
            $user_Email = '';
        } else {
            $user_Email = $userEmail[0]['email'];
        }
        $user_addr = $this->userAddressMod->getAddressById($user_id); // 获取用户地址信息
        $useraddress = $this->getAddress($user_addr['store_address']); // 获取用户的地址省市区
        $this->load($lang_id, 'store/store');
        $a = $this->langData;
        if ($user_addr == '') {
            $userid = $user_id;
            $user_name = $user_name;
            if ($userEmail[0]['phone'] == '') {
                $user_phone = '';
            } else {
                $user_phone = $userEmail[0]['phone'];
            }
            $user_address = '门店经营买家上门自提';
        } else {
            $userid = $user_addr['user_id'];
            $user_name = addslashes($user_addr['name']);
            $user_phone = $user_addr['phone'];
            $user_address = $useraddress . ' ' . $user_addr['address'] . ' ' . $user_addr['postal_code'];
        }
        $orderInfo[0]['store_name'] = $this->storeMod->getNameById($this->storeId, $this->languageId); //获取当前站点名称
        //生成订单码
        $rand = $this->buildNo(1);
        $orderNo = date('YmdHis') . $rand[0];
        //生成支付码
        $pay_sn = $this->buildPay_sn(1);
        $orderPay_sn = date('YmdHis') . $pay_sn[0];
        // 先插入主订单
        $insert_main_data = array(
            'order_sn' => $orderNo, //订单编号
            'store_id' => $this->storeId, //卖家店铺id
            'store_name' => $orderInfo[0]['store_name'], //卖家店铺名称
            'buyer_id' => $userid, //用户id
            'buyer_name' => $user_name, //买家姓名
            'buyer_email' => $user_Email, //用户邮箱
            'goods_amount' => $allPrices, //商品总价格
            'order_amount' => $allPrices - $discount, //订单总价格
            'discount' => $discount, //优惠金额
            'pay_sn' => $orderPay_sn, //付款随机生成的付款码
            'payment_code' => '现金支付', //支付方式名称
            'payment_time' => time(), //支付(付款)时间',
            'Appoint' => 2, //1 未指定 2已指定'
            'Appoint_store_id' => $this->storeId, //订单总价格
            'install_time' => time(), //区域配送安装完成时间
            'region_install' => 20, ///10区域未配送 20区域已配送',
            'order_state' => 50, //订单状态：0(已取消)10(默认):未付款;20:已付款;30:已发货;40:区域配送;50:已收货;'
            'order_from' => 1, //1电脑端WEB  2 手机端mobile
            'buyer_address' => $user_address, //买家收货地址
            'buyer_phone' => $user_phone, //买家手机号
            'source_id' => $source_id, //来源ID
            'add_time' => time()                                              //订单生成时间
        );
        $main_rs = $this->orderMod->doInsert($insert_main_data);
        //生成2维码
        $code = $this->goodsZcode($this->storeId, $main_rs);
        $cond['order_url'] = $code;
        $urldata = array(
            "table" => "order",
            'cond' => 'order_id = ' . $main_rs,
            'set' => "order_url='" . $code . "'",
        );
        $ress = $this->orderMod->doUpdate($urldata);

        // 先插入子订单
        if ($main_rs) {
            foreach ($store_goods_id as $k => $v) {
                $insert_sub_data = array(
                    'order_id' => $orderNo, //订单编号
                    'goods_id' => $store_goods_id[$k], //商品ID
                    'goods_name' => addslashes($store_goods_name[$k]), //商品名称
                    'goods_price' => $store_goods_iprice[$k], //商品价格
                    'goods_num' => $store_goods_number[$k], //商品数量
                    'goods_image' => $store_goods_url[$k], //商品图片
                    'goods_pay_price' => $store_goods_iprice[$k], //商品实际成交价
                    'spec_key_name' => $store_goods_attr[$k], //规格名
                    'spec_key' => $store_goods_key[$k], //规格
                    'store_id' => $this->storeId, //店铺ID
                    'buyer_id' => $userid, //买家ID
                    'goods_type' => 1, //1默认2团购商品3限时折扣商品4组合套装5赠品',
                    'order_state' => 50, //'订单状态：0(已取消)10(默认):未付款;20:已付款;30:已发货;40:区域配送;50:已收货;',
                    'shipping_store_id' => $this->storeId, //配送区域站点ID
                    'add_time' => time()//添加时间
                );
                $rs[] = $this->orderDetailMod->doInsert($insert_sub_data);
            }
            $rs = array_filter($rs);
            if (count($rs)) {
                $info['url'] = "?app=sourceOrder&act=index&lang_id={$lang_id}";
                $this->setData($info, $status = 1, $a['source_orderok']);
            } else {
                $this->setData($info = array(), $status = 0, $a['source_orderno']);
            }
        }
    }

    /**
     * 生成不重复的四位随机数
     * @author wangshuo 
     * @date 2018-5-21
     */
    public function buildNo($limit) {
        $begin = pow(10, 3);
        $end = (pow(10, 4) - 1);
        $rand_array = range($begin, $end);
        shuffle($rand_array); //调用现成的数组随机排列函数
        return array_slice($rand_array, 0, $limit); //截取前$limit个
    }

    /**
     * 生成不重复的5位随机数
     * @author wangshuo 
     * @date 2018-5-21
     */
    public function buildPay_sn($limit) {
        $begin = pow(10, 3);
        $end = (pow(10, 5) - 1);
        $rand_array = range($begin, $end);
        shuffle($rand_array); //调用现成的数组随机排列函数
        return array_slice($rand_array, 0, $limit); //截取前$limit个
    }

    //二维码
    public function goodsZcode($storeid, $order_id) {
        include ROOT_PATH . "/includes/classes/class.qrcode.php"; // 生成二维码库
        $mainPath = ROOT_PATH . '/upload/orderCode';
        $this->mkDir($mainPath);
        $timePath = date('Ymd');
        $savePath = $mainPath . '/' . $timePath;
        $this->mkDir($savePath);
        $newFileName = uniqid() . ".png";
        $filename = $savePath . '/' . $newFileName;
        $pathName = 'upload/orderCode/' . $timePath . '/' . $newFileName;
        $http_host = $_SERVER['HTTP_HOST'];
        $system_web = 'www.711home.net';
        $valueUrl = 'http://' . $system_web . "/index.php?app=print&act=index&orderid={$order_id}";
        QRcode::png($valueUrl, $filename);
        return $pathName;
    }

}
