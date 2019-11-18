<?php

/**
 * 秒杀
 * @author  lee
 * @date 2017-10-23 14:48:28
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class seckillApp extends BaseStoreApp {

    private $secMod;
    private $storeGoods;
    private $storeGoodMod;
    private $lang_id;
    private $pagesize = 10;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->secMod = &m('spikeActivity');
        $this->storeGoods = &m('areaGood');
        $this->storeGoodMod = &m("storeGoodItemPrice");
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
    }

    /**
     * 限时秒杀
     * @author  lee
     * @date 2017-10-23 14:48:08
     */
    public function seckillList() {
        $name = !empty($_REQUEST['name']) ? trim($_REQUEST['name']) : '';
        $status = !empty($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
        $where = " where 1=1 and store_id=" . $this->storeId;
        if ($name) {
            $where .= " and name like '%" . $name . "%'";
        }
        if ($status) {
            $where .= " and status=" . $status;
        }
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "spike_activity ";
        $totalCount = $this->secMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;

        $sql = 'select  * from  ' . DB_PREFIX . 'spike_activity  ' . $where . ' order by id desc';
        $list = $this->secMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($list['list'] as $k => $v) {
            if ($v['add_time']) {
                $list['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            } else {
                $list['list'][$k]['add_time'] = '';
            }
            $list['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        $this->assign('p', $p);
        $this->assign('name', $name);
        $this->assign('status', $status);
        $this->assign('list', $list['list']);
        $this->assign('page', $list['ph']);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('symbol', $this->symbol);
        if ($this->lang_id == 1) {
            $this->display('seckill/list_1.html');
        } else {
            $this->display('seckill/list.html');
        }
    }

    /*
     * 添加组合销售
     * @author lee
     * @2017-10-24 09:17:28
     */

    public function seckillAdd() {
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $this->assign('p', $p);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('symbol', $this->symbol);
        if ($this->lang_id == 1) {
            $this->display('seckill/add_1.html');
        } else {
            $this->display('seckill/add.html');
        }
    }

    /*
     * 添加组合销售处理
     */

    public function doSecAdd() {
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $langid = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : '';
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        $start_time = !empty($_REQUEST['start_time']) ? $_REQUEST['start_time'] : '';
        $end_time = !empty($_REQUEST['end_time']) ? $_REQUEST['end_time'] : '';
        $start_our = !empty($_REQUEST['start_our']) ? $_REQUEST['start_our'] : '';
        $end_our = !empty($_REQUEST['end_our']) ? $_REQUEST['end_our'] : '';
        $store_goods_id = !empty($_REQUEST['store_goods_id']) ? htmlspecialchars($_REQUEST['store_goods_id']) : '';
        $limit_num = !empty($_REQUEST['limit_num']) ? htmlspecialchars($_REQUEST['limit_num']) : '';
        $o_num = !empty($_REQUEST['o_num']) ? htmlspecialchars($_REQUEST['o_num']) : '';
        $goods_img = !empty($_REQUEST['goods_img']) ? $_REQUEST['goods_img'] : '';
        $content = !empty($_REQUEST['content']) ? $_REQUEST['content'] : '';
        $goods_name = !empty($_REQUEST['goods_name']) ? htmlspecialchars(addslashes($_REQUEST['goods_name'])) : '';
        $item_key = !empty($_REQUEST['item_key']) ? $_REQUEST['item_key'] : '';
        $item_name = !empty($_REQUEST['item_name']) ? $_REQUEST['item_name'] : '';
        $discount = !empty($_REQUEST['discount']) ? $_REQUEST['discount'] : '';
        $o_price = !empty($_REQUEST['o_price']) ? $_REQUEST['o_price'] : '';
        $id = !empty($_REQUEST['id']) ? htmlspecialchars($_REQUEST['id']) : '';
        if (empty($name)) {
            $this->setData(array(), $status = '0', $a['seckill__name']);
        }
        if (empty($start_time)) {
            $this->setData(array(), $status = '0', $a['seckill__start']);
        }
        if (empty($end_time)) {
            $this->setData(array(), $status = '0', $a['seckill__End']);
        }
        if (empty($start_our) || empty($end_our)) {
            $this->setData(array(), $status = '0', $a['seckill__hour']);
        }
        if (empty($discount)) {
            $this->setData(array(), $status = '0', $a['seckill__discount']);
        }
        if (empty($store_goods_id)) {
            $this->setData(array(), $status = '0', $a['seckill__commodity']);
        }

        $start_time = strtotime($start_time);

        $end_time = strtotime($end_time+$end_our);
        //时分秒处理
        $s_hour = (strtotime($start_our) - mktime(0, 0, 0, date("m"), date("d"), date("Y")));
        $e_hour = (strtotime($end_our) - mktime(0, 0, 0, date("m"), date("d"), date("Y")));

        if ($start_time > $end_time) {
            $this->setData(array(), $status = '0', $a['seckill__timeerr']);
        }
        if ($s_hour >= $e_hour) {
            $this->setData(array(), $status = '0', $a['seckill__hourerr']);
        }
        //判断主商品在该类型下是否已经在活动
        if ($id) {
            $hasWhere = " where store_goods_id=" . $store_goods_id . "  and start_time <= " . $start_time . " and end_time >= " . $end_time .
                "  and  start_our <= ".$s_hour.  "    and end_our >=" . $e_hour . " and id !=" . $id;
        } else {
            $hasWhere = " where store_goods_id=" . $store_goods_id . "  and start_time <= " . $start_time . " and end_time >= " . $end_time .
                " and start_our <= ".$s_hour."   and end_our >=" . $e_hour;
        }
        $hasSql = "select 1 from " . DB_PREFIX . "spike_activity " . $hasWhere;
        $r = $list = $this->secMod->querySql($hasSql);
        if ($r) {
            $this->setData(array(), $status = '0', $a['seckill__activity']);
        }
        //end
        $info = array(
            'name' => $name,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'start_our' => $s_hour,
            'end_our' => $e_hour,
            'add_time' => time(),
            'store_id' => $this->storeId,
            'store_goods_id' => $store_goods_id,
            'goods_img' => $goods_img,
            'content' => $content,
            'goods_name' => $goods_name,
            'item_key' => $item_key,
            'item_name' => $item_name,
            'discount' => $discount,
            'o_price' => $o_price,
            'price' => ($o_price * $discount) / 10,
            'limit_num' => $limit_num
        );
        if ($id) {
            $res = $this->secMod->doEdit($id, $info);
        } else {
            $res = $this->secMod->doInsert($info);
        }
        if ($res) {
            //更新商品库存
//            $num = $o_num - $goods_num;
//            $this->setGoodsNum($store_goods_id, $num, $item_key);
            $this->setData(array('url' => "?app=seckill&act=seckillList&lang_id={$langid}&p={$p}"), $status = '1', $a['seckill__Success']);
        } else {
            $this->setData(array(), $status = '0', $a['seckill__fail']);
        }
    }

    /*
     * 更新库存
     * @author lee
     * @date 2017-11-6 14:15:42
     */

    public function setGoodsNum($store_goods_id, $num, $item_key = null) {
        if (empty($item_key)) {
            $res = $this->storeGoods->doEdit($store_goods_id, array("goods_storage" => $num));
        } else {
            $info = $this->storeGoodMod->getOne(array("cond" => "store_goods_id=" . $store_goods_id . " and `key`=" . $item_key));
            if ($info) {
                $res = $this->storeGoodMod->doEdit($info['id'], array("goods_storage" => $num));
            }
        }
        return $res;
    }

    /*
     * 编辑活动
     * @author lee
     * @date 2017-10-30 14:38:19
     */

    public function edit() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $this->assign('lang_id', $this->lang_id);
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '';
        $info = $this->secMod->getOne(array("cond" => "id=" . $id));
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        //sdate('h:i',);
        $sgipMod = &m('storeGoodItemPrice');
        $sgMod = &m('areaGood');
        $info['start_our'] = (mktime(0, 0, 0, date("m"), date("d"), date("Y")) + $info['start_our']);
        $info['start_our'] = date('H:i', $info['start_our']);
        $info['end_our'] = (mktime(0, 0, 0, date("m"), date("d"), date("Y")) + $info['end_our']);
        $info['end_our'] = date('H:i', $info['end_our']);
        if ($id) {
            $hasWhere = " where store_goods_id=" . $info['store_goods_id'] . "  and start_time<=" . $info['start_time'] . " and end_time>=" . $info['end_time'] .
                " and start_our<=" . $info['start_our'] . " and end_our>=" . $info['end_our'] . " and id!=" . $id;
        } else {
            $hasWhere = " where store_goods_id=" . $info['store_goods_id'] . "  and start_time<=" . $info['start_time'] . " and end_time>=" . $info['end_time'] .
                " and start_our<=" . $info['start_our']  . " and end_our>=" .$info['end_our'] ;
        }
        $hasSql = "select 1 from " . DB_PREFIX . "spike_activity " . $hasWhere;
        $r = $list = $this->secMod->querySql($hasSql);
        if ($r) {
            $this->setData(array(), $status = '0', $a['seckill__activity']);
        }

        $curtime = time();
        $end_time = $info['end_time'] + $info['end_our'];
        if ($end_time < $curtime) {
            $expire = 1;
        } else {
            $expire = 0;
        }





        if ($info['item_key']) {
            $goods = $sgipMod->getOne(array("cond" => "store_goods_id=" . $info['store_goods_id'] . " and `key`=" . $info['item_key']));
        } else {
            $goods = $sgMod->getOne(array("cond" => "id=" . $info['store_goods_id']));
        }
        $info['o_num'] = $goods['goods_storage'];

        $this->assign('expire', $expire);
        $this->assign("info", $info);
        $this->assign("p", $p);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('symbol', $this->symbol);
        if ($this->lang_id == 1) {
            $this->display('seckill/edit_1.html');
        } else {
            $this->display('seckill/edit.html');
        }
    }

    /*
     * 选择分页跳转
     */

    public function getOneGoods_2() {
        $p = $_REQUEST['p'];
        //分页定义
        $currentPage = !empty($p) ? $p : 1;
        $pagesize = $this->pagesize; //每页显示的条数
        $start = ($currentPage - 1) * $pagesize;
        $end = $pagesize;
        $limit = '  limit  ' . $start . ',' . $end;
        $id = ($_REQUEST['id']) ? $_REQUEST['id'] : '';
        $main_id = ($_REQUEST['main_id']) ? $_REQUEST['main_id'] : '';
        $goods_name = ($_REQUEST['goods_name']) ? addslashes($_REQUEST['goods_name']) : '';
        $areaGoodsMod = &m('areaGood');
        $goodsItem = &m('storeGoodItemPrice');
        $area_cond = "where sg.mark=1 and sg.store_id=" . $this->storeId . " and sg.is_on_sale=1 and gl.lang_id=" . $this->storeInfo['lang_id'];
        if ($goods_name) {
            $area_cond .= " and (gl.goods_name like '%{$goods_name}%' or sg.goods_name like '%{$goods_name}%')";
            $this->assign("goods_name", $goods_name);
        }
        $area_cond .= " order by sg.id desc ";
        $sql = "select sg.id,sg.goods_id,sg.goods_sn,sg.market_price, (CASE
           WHEN gl.goods_name <> '' THEN gl.goods_name
        ELSE sg.goods_name END) as goods_name, sgl.original_img,sg.shop_price,sg.store_id,sg.goods_storage,sg.add_time,sg.is_on_sale,sg.is_free_shipping,sg.is_recommend
                from " . DB_PREFIX . "store_goods as sg left join  " . DB_PREFIX . "goods as sgl on sg.goods_id = sgl.goods_id
                left join  " . DB_PREFIX . "goods_lang as gl on gl.goods_id = sgl.goods_id
                " . $area_cond . $limit;
        $goods_list = $areaGoodsMod->querySql($sql);
        //$goods_list = $areaGoodsMod->getLangList(array("cond" => $area_cond), $area_cond, $this->storeInfo['lang_id']);
        foreach ($goods_list as $k => $v) {
            //处理商品规格
            $item = $goodsItem->getData(array("cond" => "store_goods_id=" . $v['id']));
            $goods_list[$k]['item_child'] = $item;
        }
        $this->assign('symbol', $this->symbol);
        $this->assign("goods_list", $goods_list);
        $this->assign('lang_id', $this->lang_id);
        $this->display('seckill/single_list.html');
    }

    /*
     * 分页初始化
     */

    public function getOneGoods() {
        $areaGoodsMod = &m('areaGood');
        $goodsItem = &m('storeGoodItemPrice');
        //获取第一页数据
        $where = "where sg.mark=1 and sg.store_id=" . $this->storeId . " and sg.is_on_sale=1 and gl.lang_id=" . $this->storeInfo['lang_id'];
        $sql = "select COUNT(sg.id) as total
                from " . DB_PREFIX . "store_goods as sg left join  " . DB_PREFIX . "goods as sgl on sg.goods_id = sgl.goods_id
                left join  " . DB_PREFIX . "goods_lang as gl on gl.goods_id = sgl.goods_id
                " . $where;
        $res = $areaGoodsMod->querySql($sql);
        $total = $res[0]['total'];
        $pagesize = $this->pagesize;
        $totalpage = ceil($total / $pagesize);
        if (empty($totalpage)) {
            $totalpage = 1;
        }
        $this->assign('totalpage', $totalpage);
        //分页定义
        $currentPage = 1;
        $start = ($currentPage - 1) * $pagesize;
        $end = $pagesize;
        $limit = '  limit  ' . $start . ',' . $end;
        $where .= " order by sg.id desc ";
        $sql = "select sg.id,sg.goods_id,sg.goods_sn,sg.market_price, (CASE
           WHEN gl.goods_name <> '' THEN gl.goods_name
        ELSE sg.goods_name END) as goods_name, sgl.original_img,sg.shop_price,sg.store_id,sg.goods_storage,sg.add_time,sg.is_on_sale,sg.is_free_shipping,sg.is_recommend
                from " . DB_PREFIX . "store_goods as sg left join  " . DB_PREFIX . "goods as sgl on sg.goods_id = sgl.goods_id
                left join  " . DB_PREFIX . "goods_lang as gl on gl.goods_id = sgl.goods_id
                " . $where . $limit;
        $data = $areaGoodsMod->querySql($sql);
        foreach ($data as $key => $val) {
            //规格数据
            $item = $goodsItem->getData(array("cond" => "store_goods_id=" . $val['id']));
            $data[$key]['item_child'] = $item;
        }
        $this->assign("goods_list", $data);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('symbol', $this->symbol);
        if ($this->lang_id == 1) {
            $this->display('seckill/dialog-single_1.html');
        } else {
            $this->display('seckill/dialog-single.html');
        }
    }

    /*
     * 模糊查询
     */

    public function seachSingle() {
        $areaGoodsMod = &m('areaGood');
        $goods_name = addslashes($_REQUEST['seach_name']);
        $area_cond = "where sg.mark=1 and sg.store_id=" . $this->storeId . " and sg.is_on_sale=1 and gl.lang_id=" . $this->storeInfo['lang_id'];
        if ($goods_name) {
            $area_cond .= " and (gl.goods_name like '%{$goods_name}%' or sg.goods_name like '%{$goods_name}%')";
            $this->assign("seach_name", $goods_name);
        }
        $sql = "select COUNT(sg.id) as total
                from " . DB_PREFIX . "store_goods as sg left join  " . DB_PREFIX . "goods as sgl on sg.goods_id = sgl.goods_id
                left join  " . DB_PREFIX . "goods_lang as gl on gl.goods_id = sgl.goods_id
                " . $area_cond;
        $res = $areaGoodsMod->querySql($sql);
        $total = $res[0]['total'];
        $pagesize = $this->pagesize;
        $totalpage = ceil($total / $pagesize);
        if (!empty($totalpage)) {
            echo json_encode(array('total' => $totalpage));
            exit;
        } else {
            echo json_encode(array('total' => 1));
            exit;
        }
    }

    /*
     * 删除地址
     * @author lee
     * @date 2017-9-25 13:48:06
     */

    public function groupDele() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $id = empty($_REQUEST['id']) ? 0 : $_REQUEST['id'];
        $res = $this->secMod->doDrop($id);
        if ($res) {
            $this->setData(array(), $status = '1', $a['delete_Success']);
        } else {
            $this->setData(array(), $status = '0', $a['delete_fail']);
        }
    }

}
