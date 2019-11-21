<?php

/**
 * 订单打印页面
 * @author wangshuo
 * @date 2018-04-18
 */
class printApp extends BaseFrontApp {

    private $footPrintMod;
    private $colleCtionMod;
    private $userArticleMod;
    private $orderMod;
    private $orderGoodsMod;
    private $commentMod;
    private $fxUserMod;
    private $goodsCommentMod;
    private $fxRuleMod;
    private $fxUserTreeMod;
    private $fxRevenueLogMod;
    private $userMod;
    private $storeMod;
    private $fxTreeMod;
    private $cityMod;
    private $fxuserMoneyMod;
    private $countryMod;
    private $giftGoodMod;
    private $storeCateMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->userMod = &m('user');
        $this->cityMod = &m('city');
        $this->storeCateMod = &m('storeCate');
        $this->fxuserMoneyMod = &m('fxuserMoney');
        $this->fxTreeMod = &m('fxuserTree');
        $this->storeMod = &m('store');
        $this->footPrintMod = &m('footprint');
        $this->colleCtionMod = &m('colleCtion');
        $this->userArticleMod = &m('userArticle');
        $this->orderMod = &m('order');
        $this->orderGoodsMod = &m('orderGoods');
        $this->commentMod = &m('goodsComment');
        $this->fxUserMod = &m('fxuser');
        $this->fxRuleMod = &m('fxrule');
        $this->fxRevenueLogMod = &m('fxRevenueLog');
        $this->assign('storeid', $this->storeid);
        $this->goodsCommentMod = &m('goodsComment');
        $this->storeGoodsMod = &m('goods');
        $this->fxUserTreeMod = &m('fxuserTree');
        $this->countryMod = &m('country');
        $this->giftGoodMod = &m('giftGood');
        $this->load($this->shorthand, 'user_login/user_login');
        $this->assign('langdata', $this->langData);
    }

    /**
     * 
     * @author wangshuo
     * @date 2018-04-18
     */
    public function index() {
        $symbol = $this->symbol;
        $this->assign('symbol', $symbol);
        //语言包
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $a = $this->langData;
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //1中文，2英语
        $orderid = $_REQUEST['orderid']; //订单id
        $where = ' g.order_id =' . $orderid;
        $where .= ' and g.mark =' . 1;
        //订单列表页数据
        $sql = 'select g.*,g.`add_time`,ur.`phone` from ' . DB_PREFIX . 'order as g left join ' . DB_PREFIX . 'order_goods as f '
                . 'on f.order_id = g.order_sn  left join ' . DB_PREFIX . 'user as ur on g.buyer_id = ur.id '
                . 'where' . $where;
        $data = $this->orderMod->querySql($sql);
        //获取订单商品
        $sql = "select * from "
                . DB_PREFIX . "order_goods where order_id=" . $data[0]['order_sn'];
        $list = $this->orderGoodsMod->querySql($sql);
        $data[0]['goods_list'] = $list;
        //买赠活动赠品
        $sql = "select * from " . DB_PREFIX . "gift_goods  as g left join "
                . DB_PREFIX . "store_goods as s on g.goods_id = s.id left join "
                . DB_PREFIX . "goods_lang as l on s.goods_id = l.goods_id " . " where g.id=" . $data[0]['gift_id'] . " and  lang_id = " . $lang;
        $res = $this->giftGoodMod->querySql($sql);
        $count=strpos($data[0]['buyer_address'],"_");
        $data[0]['buyer_address']=substr_replace($data[0]['buyer_address'],"",$count,1);
        $data[0]['gift'] = $res;
        $this->assign('info', $data[0]);
        $OrderStatus = array(
            "0" => $a['Canceled'],
            "10" => $a['Unpaid'],
            "20" => $a['payment'],
            "30" => $a['Shipped'],
            "40" => $a['Shipped_1'],
            "50" => $a['Receivedgoods'],
        );
        $this->assign('status', $OrderStatus);
        $this->display('public/print.html');
    }




    public function windowIndex() {
        $symbol = $this->symbol;
        $this->assign('symbol', $symbol);
        //语言包
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $a = $this->langData;
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //1中文，2英语
        $orderid = $_REQUEST['orderid']; //订单id
        $where = ' g.order_id =' . $orderid;
        $where .= ' and g.mark =' . 1;
        //订单列表页数据
        $sql = 'select g.*,g.`add_time`,ur.`phone` from ' . DB_PREFIX . 'order as g left join ' . DB_PREFIX . 'order_goods as f '
            . 'on f.order_id = g.order_sn  left join ' . DB_PREFIX . 'user as ur on g.buyer_id = ur.id '
            . 'where' . $where;
        $data = $this->orderMod->querySql($sql);
        //获取订单商品
        $sql = "select * from "
            . DB_PREFIX . "order_goods where order_id=" . $data[0]['order_sn'];
        $list = $this->orderGoodsMod->querySql($sql);
        $data[0]['goods_list'] = $list;
        //买赠活动赠品
        $sql = "select * from " . DB_PREFIX . "gift_goods  as g left join "
            . DB_PREFIX . "store_goods as s on g.goods_id = s.id left join "
            . DB_PREFIX . "goods_lang as l on s.goods_id = l.goods_id " . " where g.id=" . $data[0]['gift_id'] . " and  lang_id = " . $lang;
        $res = $this->giftGoodMod->querySql($sql);
        $count=strpos($data[0]['buyer_address'],"_");
        $data[0]['buyer_address']=substr_replace($data[0]['buyer_address'],"",$count,1);
        $data[0]['gift'] = $res;
        $this->assign('info', $data[0]);
        $OrderStatus = array(
            "0" => $a['Canceled'],
            "10" => $a['Unpaid'],
            "20" => $a['payment'],
            "30" => $a['Shipped'],
            "40" => $a['Shipped_1'],
            "50" => $a['Receivedgoods'],
        );
        $this->assign('status', $OrderStatus);
        $this->display('public/windowPrint.html');
    }

    /**
     * 获取商品规格
     * @param $goods_id|商品id  $type=1 读取商品原有规格属性  2 读取区域编辑后的规格属性
     * @return array
     */
    public function get_spec($k, $lang) {
        $storeGoodMod = &m("storeGoodItemPrice");
        $k = str_replace('_', ',', $k);
        $sql4 = "SELECT a.`order`,b.*,al.spec_name,bl.`item_name`  FROM " . DB_PREFIX . "goods_spec AS a
                     INNER JOIN " . DB_PREFIX . "goods_spec_item AS b ON a.id = b.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_lang AS al ON a.id=al.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_item_lang as bl ON b.id=bl.item_id
                     WHERE b.id IN($k) and al.lang_id=" . $lang . " and bl.lang_id=" . $lang . " ORDER BY b.id";
        $filter_spec2 = $storeGoodMod->querySql($sql4);
        return $filter_spec2[0];
    }

    /**
     * 获取订单小票打印json
     * @author wangshuo
     * @date 2018-04-18
     */
    public function edit($orderid) {
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : 29;  //1中文，2英语
        $orderid = $_REQUEST['orderid']; //订单id
        $where = ' g.order_id =' . $orderid;
        $where .= ' and g.mark =' . 1;
           //订单列表页数据
        $sql = 'select g.*,g.`add_time`,ur.`phone` from ' . DB_PREFIX . 'order as g left join '. DB_PREFIX . 'user as ur on g.buyer_id = ur.id '
                . 'where' . $where;
        $data = $this->orderMod->querySql($sql);
        //获取订单商品
        $sql = "select * from "
                . DB_PREFIX . "order_goods where order_id=" . $data[0]['order_sn'];
        $list = $this->orderGoodsMod->querySql($sql);
        $data[0]['goods_list'] = $list;
        //买赠活动赠品
        $sql = "select * from " . DB_PREFIX . "gift_goods  as g left join "
                . DB_PREFIX . "store_goods as s on g.goods_id = s.id left join "
                . DB_PREFIX . "goods_lang as l on s.goods_id = l.goods_id " . " where g.id=" . $data[0]['gift_id'] . " and  lang_id = " . $lang;
        $res = $this->giftGoodMod->querySql($sql);
        $data[0]['gift'] = $res;
        $this->assign('info', $data[0]);
        $OrderStatus = array(
            "0" => '商品已取消',
            "10" => '商品未付款',
            "20" => '商品已付款',
            "30" => '商品已发货',
            "40" => '商品配送中',
            "50" => '商品已收货',
        );
        $data[0]['Status'] = $OrderStatus;
        $symbol = $this->symbol;
        $data[0]['symbol'] = $symbol;
        $data[0]['url'] = 'http://www.711home.net/index.php?app=print&act=index&orderid=' . $orderid;
        $info = $data[0];
        $this->setData($info, $status = 'Success', '成功');
    }

    /**
     * 获取商品规格小票打印json
     * @author wangshuo
     * @date 2018-04-18
     */
    public function indexSpec() {
        $symbol = $this->symbol;
        $this->assign('symbol', $symbol);
        //语言包
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $a = $this->langData;
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //1中文，2英语
        $orderid = $_REQUEST['orderid']; //订单id
        $where = ' g.order_id =' . $orderid;
        $where .= ' and g.mark =' . 1;
        $sql = 'select g.*,g.`add_time`,se.store_mobile,sum(f.goods_num) whole_num from '
                . DB_PREFIX . 'order as g left join '
                . DB_PREFIX . 'order_goods as f on f.order_id = g.order_sn  left join '
                . DB_PREFIX . 'store as se on g.store_id = se.id where ' . $where;
        $data = $this->orderMod->querySql($sql);
        //获取订单商品
        $sql = "select * from "
                . DB_PREFIX . "order_goods where order_id=" . $data[0]['order_sn'];
        $list = $this->orderGoodsMod->querySql($sql);
        foreach ($list as $key => $val) {
            $num = intval($val['goods_num']);

            for ($i = 1; $i <= $num; $i++) {

                $good_num[] = $i;
            }

            $list[$key]['good_num'] = $good_num;
            $good_num = array();
        }
        $data[0]['goods_list'] = $list;

          $i=1;
         $this->assign('i',$i);

        //赠品
        $sql = "select * from " . DB_PREFIX . "gift_goods where id=" . $data[0]['gift_id'];
        $res = $this->giftGoodMod->querySql($sql);
        $data[0]['gift'] = $res;
        $this->assign('info', $data[0]);

        $OrderStatus = array(
            "0" => $a['Canceled'],
            "10" => $a['Unpaid'],
            "20" => $a['payment'],
            "30" => $a['Shipped'],
            "40" => $a['Shipped_1'],
            "50" => $a['Receivedgoods'],
        );
        $this->assign('status', $OrderStatus);
        $this->display('public/print_spec.html');
    }



    /**
     * 获取商品规格小票打印json
     * @author wangshuo
     * @date 2018-04-18
     */
    public function windowIndexSpec() {
        $symbol = $this->symbol;
        $this->assign('symbol', $symbol);
        //语言包
        $this->load($this->shorthand, 'userCenter/userCenter');
        $this->assign('langdata', $this->langData);
        $a = $this->langData;
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //1中文，2英语
        $orderid = $_REQUEST['orderid']; //订单id
        $where = ' g.order_id =' . $orderid;
        $where .= ' and g.mark =' . 1;
        $sql = 'select g.*,g.`add_time`,se.store_mobile,sum(f.goods_num) whole_num from '
            . DB_PREFIX . 'order as g left join '
            . DB_PREFIX . 'order_goods as f on f.order_id = g.order_sn  left join '
            . DB_PREFIX . 'store as se on g.store_id = se.id where ' . $where;
        $data = $this->orderMod->querySql($sql);
        //获取订单商品
        $sql = "select * from "
            . DB_PREFIX . "order_goods where order_id=" . $data[0]['order_sn'];
        $list = $this->orderGoodsMod->querySql($sql);
        foreach ($list as $key => $val) {
            $num = intval($val['goods_num']);

            for ($i = 1; $i <= $num; $i++) {

                $good_num[] = $i;
            }

            $list[$key]['good_num'] = $good_num;
            $good_num = array();
        }
        $data[0]['goods_list'] = $list;

        $i=1;
        $this->assign('i',$i);

        //赠品
        $sql = "select * from " . DB_PREFIX . "gift_goods where id=" . $data[0]['gift_id'];
        $res = $this->giftGoodMod->querySql($sql);
        $data[0]['gift'] = $res;
        $this->assign('info', $data[0]);

        $OrderStatus = array(
            "0" => $a['Canceled'],
            "10" => $a['Unpaid'],
            "20" => $a['payment'],
            "30" => $a['Shipped'],
            "40" => $a['Shipped_1'],
            "50" => $a['Receivedgoods'],
        );
        $this->assign('status', $OrderStatus);
        $this->display('public/windowPrint_spec.html');
    }





    /**
     * 
     * @author wangshuo
     * @date 2018-04-18
     */
    public function editSpec($orderid) {
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : 29;  //1中文，2英语
        $orderid = $_REQUEST['orderid']; //订单id
        $where = ' g.order_id =' . $orderid;
        $where .= ' and g.mark =' . 1;
        //订单列表页数据
//        $sql = 'select g.*,g.`add_time` from ' . DB_PREFIX . 'order as g left join ' . DB_PREFIX . 'order_goods as f '
//                . 'on f.order_id = g.order_sn '
//                . 'where' . $where;
        //订单列表页数据
        $sql = 'select g.*,g.`add_time`,se.store_mobile,sum(f.goods_num) whole_num from '
                . DB_PREFIX . 'order as g left join '
                . DB_PREFIX . 'order_goods as f on f.order_id = g.order_sn  left join '
                . DB_PREFIX . 'store as se on g.store_id = se.id where ' . $where;
        $data = $this->orderMod->querySql($sql);
        //获取订单商品
        $sql = "select * from "
                . DB_PREFIX . "order_goods where order_id=" . $data[0]['order_sn'];
        $list = $this->orderGoodsMod->querySql($sql);
        foreach ($list as $key => $val) {
            $num = intval($val['goods_num']);

            for ($i = 1; $i <= $num; $i++) {

                $good_num[] = $i;
            }

            $list[$key]['good_num'] = $good_num;
            $good_num = array();
        }
        $data[0]['goods_list'] = $list;
        $i = 1;
        $this->assign('i', $i);
        //赠品
        $sql = "select * from " . DB_PREFIX . "gift_goods where id=" . $data[0]['gift_id'];
        $res = $this->giftGoodMod->querySql($sql);
        $data[0]['gift'] = $res;
        $this->assign('info', $data[0]);
        $OrderStatus = array(
            "0" => '商品已取消',
            "10" => '商品未付款',
            "20" => '商品已付款',
            "30" => '商品已发货',
            "40" => '商品配送中',
            "50" => '商品已收货',
        );
        $data[0]['Status'] = $OrderStatus;
        $symbol = $this->symbol;
        $data[0]['symbol'] = $symbol;
        $info = $data[0];
        $this->setData($info, $status = 'Success', '成功');
    }

}
