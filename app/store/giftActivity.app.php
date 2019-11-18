<?php

/**
 * 买赠活动
 * User: wanyan
 * Date: 2017/11/06
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class GiftActivityApp extends BaseStoreApp {

    private $giftActivityMod;
    private $giftGoodMod;
    private $lang_id;
    private $storeGoodsMod;
    private $storeGoodItemPriceMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->giftActivityMod = &m('giftActivity');
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
        $store_lang = $this->storeInfo;
        $this->store_lang = $store_lang['lang_id'];
        $this->storeGoodsMod = &m('areaGood');
        $this->storeGoodItemPriceMod = &m('storeGoodItemPrice');
        $this->giftGoodMod = &m('giftGood');
    }

    /**
     * 买赠活动首页
     * User: wanyan
     * Date: 2017/11/06
     */
    public function index() {
        $this->load($this->lang_id, 'store/store');
        $this->assign('langdata', $this->langData);
        $a = $this->langData;
        $prom_name = !empty($_REQUEST['prom_name']) ? htmlspecialchars(trim($_REQUEST['prom_name'])) : '';
        $status = !empty($_REQUEST['status']) ? $_REQUEST['status'] : '0';
        $where = " where gp.store_id = $this->storeId and mark=1";
        if (!empty($prom_name)) {
            $where .= " and gp.prom_name like '%" . $prom_name . "%'";
        }
        if (!empty($status)) {
            $where .= " and gp.status = '{$status}'";
        }
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "gift_goods ";
        $totalCount = $this->giftActivityMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;

        $where .= " group by gp.id order by gp.add_time desc";
        $sql = "select gp.* from " . DB_PREFIX . "gift_activity as gp left join  " . DB_PREFIX . "gift_goods AS pg on gp.id = pg.gift_id " . $where;
        $rs = $this->giftActivityMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($rs['list'] as $k => $v) {
            if ($v['start_time'] > time()) {
                $rs['list'][$k]['status_name'] = $a['gift__not'];
                $vstatus = 1;
            } elseif ($v['start_time'] <= time() && $v['end_time'] >= time()) {
                $rs['list'][$k]['status_name'] = $a['gift__Conduct'];
                $vstatus = 2;
            } elseif ($v['end_time'] < time()) {
                $rs['list'][$k]['status_name'] = $a['gift__already'];
                $vstatus = 3;
            }
            $this->giftActivityMod->doEdit($v['id'], array('status' => $vstatus));
            $rs['list'][$k]['start_time'] = date('Y-m-d H:i:s', $v['start_time']);
            $rs['list'][$k]['end_time'] = date('Y-m-d H:i:s', $v['end_time']);
            switch ($v['active_id']) {
                case 1:
                    $rs['list'][$k]['cate'] = $a['gift__Full'];
                    break;
                case 2:
                    $rs['list'][$k]['cate'] = $a['gift__pieces'];
                    break;
            }
            $rs['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
        }
//        var_dump($rs);die;
        $this->assign('p', $p);
        $this->assign('list', $rs['list']);
        $this->assign('page_html', $rs['ph']);
        $this->assign('isrecom', $status);
        $this->assign('prom_name', $prom_name);
        $this->assign('lang_id', $this->lang_id);
        $this->display('giftActivity/promList.html');
    }

    /**
     * 添加活动
     * User: wanyan
     * Date: 2017/11/06
     */
    public function add() {
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $this->assign('p', $p);
        $this->load($this->lang_id, 'store/store');
        $this->assign('langdata', $this->langData);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('act', 'index');
        $this->assign('store_id', $this->storeId);
        if ($this->lang_id == 1) {
            $this->display('giftActivity/activityAdd_1.html');
        } else {
            $this->display('giftActivity/activityAdd.html');
        }
    }

    /**
     * 添加活动功能
     * User: wanyan
     * Date: 2017/11/06
     */
    public function doAdd() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $langid = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : '';
        $active_id = !empty($_REQUEST['flag']) ? $_REQUEST['flag'] : '';
        $active_name = !empty($_REQUEST['active_name']) ? htmlspecialchars($_REQUEST['active_name']) : '';
        $start_time = !empty($_REQUEST['start_time']) ? htmlspecialchars($_REQUEST['start_time']) : '';
        $end_time = !empty($_REQUEST['end_time']) ? htmlspecialchars($_REQUEST['end_time']) : '';
        $price = !empty($_REQUEST['price']) ? $_REQUEST['price'] : array();
        $goodNum = !empty($_REQUEST['goodNum']) ? $_REQUEST['goodNum'] : array();
        $goodinfo = !empty($_REQUEST['arr']) ? $_REQUEST['arr'] : array();
        $lang_id = $this->store_lang;
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : '0';
//        if (!empty($active_name)) {
//            $rs = $this->giftActivityMod->getOne(array('cond' => "`prom_name` = '{$active_name
//                }' and mark =1 and `store_id` =' {$store_id}'"));
//            if ($rs) {
//                $this->setData($info = array(), $status = '0', $a['gift__ActivityName']);
//            }
//        }
        if (empty($active_name)) {
            $this->setData($info = array(), $status = '0', $a['gift__ActivityName']);
        }
        // 检查库存的是否满足
        foreach ($goodinfo as $k => $v) {
            $goods = explode(',', $v);
            $goods_id = $goods[0];
            $goods_key = $goods[1];
            $rs = $this->checkInventory($goods_id, $goods_key);
            if ($rs < $goodNum[$k]) {
                $rs_1[] = $rs;
            }
        }
        if (count($rs_1)) {
            $this->setData($info = array(), $status = 0, $a['gift__over']);
        }
        if (count($price) != count(array_unique($price))) {
            $this->setData($info = array(), $status = '0', $a['gift__ConsumerPrice']);
        }
        $checkInfo = $this->checkRepeat($store_id, $active_id, 0, strtotime($start_time), strtotime($end_time));
        if (!empty($checkInfo)) {
            $this->setData($info = array(), $status = '0', $a['gift__Setup']);
        }
        $checkInfo_1 = $this->checkRepeat($store_id, 0, 0, strtotime($start_time), strtotime($end_time));
        if (!empty($checkInfo_1)) {
            $this->setData($info = array(), $status = '0', $a['gift__FullPieces']);
        }
        $insert_main_data = array(
            'active_id' => $active_id,
            'start_time' => strtotime($start_time),
            'end_time' => strtotime($end_time),
            'prom_name' => $active_name,
            'store_id' => $store_id,
            'add_time' => time(),
            'status' => 1
        );
        $rs = $this->giftActivityMod->doInsert($insert_main_data);
        if ($rs) {
            foreach ($goodinfo as $k => $v) {
                $info = explode(',', $v);
                $storeGood = $this->getStoreGoodsName($info[0], $info[1], $lang_id);
                $insert_secondary_data = array(
                    'gift_id' => $rs,
                    'amount' => $price[$k],
                    'goods_id' => $info[0],
                    'goods_name' => addslashes($storeGood['goods_name']),
                    'goods_price' => $info[2],
                    'goods_img' => $storeGood['original_img'],
                    'goods_key' => $info[1],
                    'goods_key_name' => $storeGood['key_name'],
                    'gift_num' => $goodNum[$k],
                    'add_time' => time()
                );
                $res[] = $this->giftGoodMod->doInsert($insert_secondary_data);
            }

            $res = array_filter($res);
            if (count($res)) {
                $info['url'] = "?app=giftActivity&act=index&lang_id=" . $langid . '&p=' . $p;
                $this->setData($info, $status = 1, $a['add_Success']);
            } else {
                $this->setData($info = array(), $status = 0, $a['add_fail']);
            }
        }
    }

    /**
     * 判断当前活动时间区间是否存在重复
     * User: wanyan
     * Date: 2017/11/06
     */
    public function checkRepeat($store_id, $active_id, $id, $start_time, $end_time) {
        $sql = "SELECT `id` FROM  `bs_gift_activity`  
        WHERE mark = 1 AND `store_id` =' {$store_id}' AND  ((start_time >= ' {$start_time}' AND start_time <= ' {$end_time}') OR 
        (start_time <= ' {$start_time}' AND end_time >= ' {$end_time}') OR (end_time >= ' {$start_time}' AND end_time <= ' {$end_time}'))";
        if ($active_id) {
            $sql .= " AND active_id = ' {$active_id}' ";
        }
        if ($id) {
            $sql .= " AND `id` != ' {$id}' ";
        }
        $activeInfo = $this->giftActivityMod->querySql($sql);
        return $activeInfo[0];
    }

    /**
     * 添加活动功能
     * User: wanyan
     * Date: 2017/11/06
     */
    public function getStoreGoodsName($goods_id, $goods_key, $lang_id) {
        $sql = "SELECT (CASE WHEN ISNULL(sgl.goods_name) THEN sg.goods_name ELSE sgl.goods_name END) AS goods_name,gl.original_img
        ";
//        if(!empty($goods_key)){
//        $sql .=" ,gsp.key_name";
//        }
        $sql .= " FROM `" . DB_PREFIX . "store_goods` as sg LEFT JOIN "
                . DB_PREFIX . "store_goods_lang as sgl ON sg.id = sgl.store_good_id  LEFT JOIN "
                . DB_PREFIX . "goods as gl ON sg.goods_id = gl.goods_id";
//        if(!empty($goods_key)){
//            $sql .=" LEFT JOIN `".DB_PREFIX."goods_spec_price` as gsp ON sg.goods_id = gsp.goods_id";
//        }
        $sql .= " WHERE sg.id= ' {$goods_id}'";
        $rs_1 = $this->storeGoodsMod->querySql($sql);

        $sql_1 = "select item_name  from " . DB_PREFIX . "goods_spec_item_lang where item_id =' {$goods_key}' and `lang_id` = ' {$lang_id}'";
        $rs_2 = $this->storeGoodsMod->querySql($sql_1);
        $arr['goods_name'] = $rs_1[0]['goods_name'];
        $arr['original_img'] = $rs_1[0]['original_img'];
        $arr['key_name'] = $rs_2[0]['item_name'];
        return $arr;
    }

    /**
     * 买赠活动编辑页面页面
     * User: wanyan
     * Date: 2017/11/06
     */
    public function edit() {
        $this->load($this->lang_id, 'store/store');
        $this->assign('langdata', $this->langData);
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '0';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $giftActivity = $this->giftActivityMod->getOne(array('cond' => "`id`=' {$id}'")); // 主活动的商品
        $giftActivity['start_time'] = date('Y-m-d H:i:s', $giftActivity['start_time']);
        $giftActivity['end_time'] = date('Y-m-d H:i:s', $giftActivity['end_time']);
        $this->assign('info', $giftActivity);
        if ($giftActivity['active_id'] == 1) {
            $giftGood = $this->giftGoodMod->getData(array('cond' => "`gift_id`=' {$id}'"));
            $this->assign('giftGood', $giftGood); // 满额
        } else {
            $giftGood_1 = $this->giftGoodMod->getData(array('cond' => "`gift_id`=' {$id}'"));
            $this->assign('giftGood_1', $giftGood_1); // 满件
        }
        $this->assign('lang_id', $this->lang_id);
        $this->assign('id', $id);
        $this->assign('store_id', $this->storeId);
        $this->assign('p', $p);
        $this->assign('act', 'index');
        if ($this->lang_id == 1) {
            $this->display('giftActivity/activityEdit_1.html');
        } else {
            $this->display('giftActivity/activityEdit.html');
        }
    }

    /**
     * 买赠活动编辑功能
     * User: wanyan
     * Date: 2017/11/06
     */
    public function doEdit() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $langid = $_REQUEST['lang_id'];
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '0';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $active_id = !empty($_REQUEST['flag']) ? $_REQUEST['flag'] : '';
        $active_name = !empty($_REQUEST['active_name']) ? htmlspecialchars(trim($_REQUEST['active_name'])) : '';
        $start_time = !empty($_REQUEST['start_time']) ? htmlspecialchars($_REQUEST['start_time']) : '';
        $end_time = !empty($_REQUEST['end_time']) ? htmlspecialchars($_REQUEST['end_time']) : '';
        $price = !empty($_REQUEST['price']) ? $_REQUEST['price'] : array();
        $goodNum = !empty($_REQUEST['goodNum']) ? $_REQUEST['goodNum'] : array();
        $goodinfo = !empty($_REQUEST['arr']) ? $_REQUEST['arr'] : array();
        $lang_id = $this->store_lang;
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : '0';
        if (!empty($active_name)) {
            $rs = $this->giftActivityMod->getOne(array('cond' => "`prom_name` = ' {$active_name}' and id != ' {$id}' and mark =1 and `store_id` =' {$store_id}'"));
            if ($rs) {
                $this->setData($info = array(), $status = '0', $a['gift__ActivityName']);
            }
        }
        foreach ($goodinfo as $k => $v) {
            $goods = explode(',', $v);
            $goods_id = $goods[0];
            $goods_key = $goods[1];
            $rs = $this->checkInventory($goods_id, $goods_key);
            if ($rs < $goodNum[$k]) {
                $rs_1[] = $rs;
            }
        }
        if (count($rs_1)) {
            $this->setData($info = array(), $status = 0, $a['gift__over']);
        }

        if (count($price) != count(array_unique($price))) {
            $this->setData($info = array(), $status = '0', $a['gift__ConsumerPrice']);
        }
        $checkInfo = $this->checkRepeat($store_id, $active_id, $id, strtotime($start_time), strtotime($end_time));
        if (!empty($checkInfo)) {
            $this->setData($info = array(), $status = '0', $a['gift__Setup']);
        }
        $checkInfo_1 = $this->checkRepeat($store_id, 0, $id, strtotime($start_time), strtotime($end_time));
        if (!empty($checkInfo_1)) {
            $this->setData($info = array(), $status = '0', $a['gift__FullPieces']);
        }
        $insert_main_data = array(
            'active_id' => $active_id,
            'start_time' => strtotime($start_time),
            'end_time' => strtotime($end_time),
            'prom_name' => $active_name,
            'store_id' => $store_id,
        );
        $rs = $this->giftActivityMod->doEdit($id, $insert_main_data);
        if ($rs) {
            $this->giftGoodMod->doDelete(array('cond' => "`gift_id`=' {$id}'"));
            foreach ($goodinfo as $k => $v) {
                $info = explode(',', $v);
                $storeGood = $this->getStoreGoodsName($info[0], $info[1], $lang_id);
                $insert_secondary_data = array(
                    'gift_id' => $id,
                    'amount' => $price[$k],
                    'goods_id' => $info[0],
                    'goods_name' => addslashes($storeGood['goods_name']),
                    'goods_price' => $info[2],
                    'goods_img' => $storeGood['original_img'],
                    'goods_key' => $info[1],
                    'goods_key_name' => $storeGood['key_name'],
                    'gift_num' => $goodNum[$k],
                    'add_time' => time()
                );

                $res[] = $this->giftGoodMod->doInsert($insert_secondary_data);
            }

            $res = array_filter($res);
            if (count($res)) {
                $info['url'] = "?app=giftActivity&act=index&lang_id={$langid}&p={$p}";
                $this->setData($info, $status = 1, $a['edit_Success']);
            } else {
                $this->setData($info = array(), $status = 0, $a['edit_fail']);
            }
        }
    }

    /**
     * 判断商品库存是否足够
     * User: wanyan
     * Date: 2017/11/02
     */
    public function checkInventory($goods_id, $goods_key) {
        $sql = "select `goods_storage` from " . DB_PREFIX . "store_goods_spec_price where `store_goods_id` = '{$goods_id}' and `key` = '{$goods_key}'";
        $rs = $this->giftGoodMod->querySql($sql);
        return $rs[0]['goods_storage'];
    }

    /**
     * 买赠活动页面
     * User: wanyan
     * Date: 2017/11/06
     */
    public function dele() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '0';
        $rs = $this->giftActivityMod->doMark($id);
        if ($rs) {
            $this->setData($info = array(), $status = 1, $a['delete_Success']);
        } else {
            $this->setData($info = array(), $status = 0, $a['delete_fail']);
        }
    }

}
