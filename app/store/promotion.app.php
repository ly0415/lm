<?php

/**
 * 促销
 * @author  lee
 * @date 2017-10-23 14:48:28
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class promotionApp extends BaseStoreApp {

    private $groupMod;
    private $groupGoodsMod;
    private $lang_id;
    private $pagesize = 10;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->groupMod = &m('combinedSale');
        $this->groupGoodsMod = &m('combinedGoods');
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
    }

    /**
     * 组合销售
     * @author  lee
     * @date 2017-10-23 14:48:08
     */
    public function groupList() {
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
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "combined_sale ";
        $totalCount = $this->groupMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;

        $sql = 'select  * from  ' . DB_PREFIX . 'combined_sale  ' . $where . ' order by id desc';
        $list = $this->groupMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
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
            $this->display('combined/list_1.html');
        } else {
            $this->display('combined/list.html');
        }
    }

    /*
     * 添加组合销售
     * @author lee
     * @2017-10-24 09:17:28
     */

    public function groupAdd() {
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $this->assign('p', $p);
        $this->assign('symbol', $this->symbol);
        $this->assign('lang_id', $this->lang_id);
        if ($this->lang_id == 1) {
            $this->display('combined/add_1.html');
        } else {
            $this->display('combined/add.html');
        }
    }

    /*
     * 添加组合销售处理
     */

    public function doGroupAdd() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $langid = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : '';
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        $start_time = !empty($_REQUEST['start_time']) ? $_REQUEST['start_time'] : '';
        $end_time = !empty($_REQUEST['end_time']) ? $_REQUEST['end_time'] : '';
        $main_id = !empty($_REQUEST['main_id']) ? htmlspecialchars($_REQUEST['main_id']) : '';
        $main_name = !empty($_REQUEST['main_name']) ? addslashes($_REQUEST['main_name']) : '';
        $main_price = !empty($_REQUEST['main_price']) ? htmlspecialchars($_REQUEST['main_price']) : '';
        $main_key = !empty($_REQUEST['main_key']) ? htmlspecialchars($_REQUEST['main_key']) : '';
        $main_key_name = !empty($_REQUEST['main_key_name']) ? htmlspecialchars($_REQUEST['main_key_name']) : '';
        $main_img = !empty($_REQUEST['main_img']) ? htmlspecialchars($_REQUEST['main_img']) : '';
        $goods_list = !empty($_REQUEST['goods_list']) ? $_REQUEST['goods_list'] : '';
        $has_where = "main_id=" . $main_id . " and status = 1 and store_id=" . $this->storeId;
        if ($main_key) {
            $has_where .= " and main_key='" . $main_key . "'";
        }
        $has = $this->groupMod->getOne(array("cond" => $has_where));
//        if($has){
//            $this->setData(array(), $status = '0', $a['promo__same']);
//        }
        if (empty($name)) {
            $this->setData(array(), $status = '0', $a['promo__name']);
        }
        if (empty($main_id)) {
            $this->setData(array(), $status = '0', $a['promo__main']);
        }
        if (empty($goods_list)) {
            $this->setData(array(), $status = '0', $a['promo__son']);
        }

        $this->checkComGoods($goods_list);

        $info = array(
            'name' => $name,
            'main_id' => $main_id,
            'main_name' => $main_name,
            'main_price' => $main_price,
            'main_img' => $main_img,
            'status' => 1,
            'add_time' => time(),
            'store_id' => $this->storeId,
            'main_key' => $main_key,
            'main_key_name' => $main_key_name
        );
        $res = $this->groupMod->doInsert($info);
        if ($res) {
//团购商品处理
            if (is_array($goods_list)) {
                $this->doComGoods($goods_list, $res, $main_id, $main_key);
            }
            $this->setData(array('url' => "?app=promotion&act=groupList&lang_id={$langid}&p={$p}"), $status = '1', $a['add_Success']);
        } else {
            $this->setData(array(), $status = '0', $a['add_fail']);
        }
    }

    public function checkComGoods($arr) {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        foreach ($arr as $k => $v) {
            if ($v['item_id'] == 1) {
                if (empty($v['price'])) {
                    $this->setData(array(), $status = '0', $a['promo__good_price']);
                }
            } else {
                foreach ($v as $k1 => $v1) {
                    if (empty($v1['price'])) {
                        $this->setData(array(), $status = '0', $a['promo__good_price']);
                    }
                }
            }
        }
    }

    /*
     * 处理组合销售商品
     * @author lee
     * @date 2017-10-30 11:07:21
     * @param $arr 商品数组 $com_id 活动ID
     */

    public function doComGoods($arr, $com_id, $main_id = null, $main_key = null) {
        $now = time();
        // print_r($arr);exit;
        foreach ($arr as $k => $v) {
            if ($v['item_id'] == 1) {
                $info = array(
                    'com_id' => $com_id,
                    'price' => $v['o_price'],
                    'store_goods_id' => $k,
                    'time' => time(),
                    'c_price' => $v['price'],
                    'add_time' => $now,
//                    'item_num' => $v['num'],
                    'discount' => $v['discount'],
                    'z_pirce' => $v['lessprice'],
                    'goods_img' => $v['img'],
                    'item_name' => addslashes($v['item_name'])
                );
                if ($k != $main_id) {
                    $this->groupGoodsMod->doInsert($info);
                }
            } else {
                foreach ($v as $k1 => $v1) {
                    $info = array(
                        'com_id' => $com_id,
                        'price' => $v1['o_price'],
                        'store_goods_id' => $k,
                        'time' => time(),
                        'c_price' => $v1['price'],
                        'item_key' => $k1,
                        'sip_id' => $v1['item_id'],
                        'add_time' => $now,
//                        'item_num' => $v1['num'],
                        'goods_img' => $v1['img'],
                        'discount' => $v1['discount'],
                        'z_pirce' => $v1['lessprice'],
                        'item_name' => addslashes($v1['item_name'])
                    );
                    if ($k == $main_id) {

                        if ($k1 != $main_key) {

                            $this->groupGoodsMod->doInsert($info);
                        }
                    } else {
                        $r = $this->groupGoodsMod->doInsert($info);
                    }
                }
            }
        }
    }

    /*
     * 编辑活动
     * @author lee
     * @date 2017-10-30 14:38:19
     */

    public function edit() {
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '';
        $info = $this->groupMod->getOne(array("cond" => "id=" . $id));
        if ($info) {
            $list = $this->groupGoodsMod->getData(array("cond" => "com_id=" . $id));
            $info['main_name'] = stripslashes($info['main_name']);
            foreach ($list as $k => $v) {
                $list[$k]['item_name'] = stripslashes($v['item_name']);
            }
            $info['item'] = $list;
        }
        $this->assign("info", $info);
        $this->assign("p", $p);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('symbol', $this->symbol);
        if ($this->lang_id == 1) {
            $this->display('combined/edit_1.html');
        } else {
            $this->display('combined/edit.html');
        }
    }

    /*
     * 处理编辑
     * @author lee
     * @date 2017-11-1 20:38:30
     */

    public function doEdit() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $langid = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : '';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        $main_id = !empty($_REQUEST['main_id']) ? htmlspecialchars($_REQUEST['main_id']) : '';
        $main_name = !empty($_REQUEST['main_name']) ? htmlspecialchars(addslashes($_REQUEST['main_name'])) : '';
        $main_price = !empty($_REQUEST['main_price']) ? htmlspecialchars($_REQUEST['main_price']) : '';
        $main_key = !empty($_REQUEST['main_key']) ? htmlspecialchars($_REQUEST['main_key']) : '';
        $main_key_name = !empty($_REQUEST['main_key_name']) ? htmlspecialchars($_REQUEST['main_key_name']) : '';
        $main_img = !empty($_REQUEST['main_img']) ? htmlspecialchars($_REQUEST['main_img']) : '';
        $goods_list = !empty($_REQUEST['goods_list']) ? $_REQUEST['goods_list'] : '';
        $id = !empty($_REQUEST['id']) ? htmlspecialchars($_REQUEST['id']) : '';
        if (empty($name)) {
            $this->setData(array(), $status = '0', $a['promo__name']);
        }
        if (empty($main_id)) {
            $this->setData(array(), $status = '0', $a['promo__main']);
        }
        if (empty($goods_list)) {
            $this->setData(array(), $status = '0', $a['promo__son']);
        }
        $this->checkComGoods($goods_list);
        $info = array(
            'name' => $name,
            'main_id' => $main_id,
            'main_name' => $main_name,
            'main_price' => $main_price,
            'main_img' => $main_img,
            'status' => 1,
            'add_time' => time(),
            'store_id' => $this->storeId,
            'main_key' => $main_key,
            'main_key_name' => $main_key_name
        );
        $res = $this->groupMod->doEdit($id, $info);
        if ($res) {
            //团购商品处理
            if (is_array($goods_list)) {
                //先清空原组合商品记录
                $sql = "delete from " . DB_PREFIX . "combined_goods where com_id=" . $id;
                $r = $this->groupGoodsMod->sql_b_spec($sql);
                $this->doComGoods($goods_list, $id, $main_id, $main_key);
            }
            $this->setData(array('url' => "?app=promotion&act=groupList&lang_id={$langid}&p={$p}"), $status = '1', $a['edit_Success']);
        } else {
            $this->setData(array(), $status = '0', $a['edit_fail']);
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
        $type = ($_REQUEST['type']) ? $_REQUEST['type'] : '';

        $areaGoodsMod = &m('areaGood');
        $goodsItem = &m('storeGoodItemPrice');
        $mod = $this->groupMod;
        $where = array("cond" => "com_id=" . $id);
        $has = $mod->getOne($where);
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
        $this->assign("goods_list", $goods_list);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('symbol', $this->symbol);
        if ($type) {
            $this->display('combined/dialog_list.html');
        } else {
            $this->display('combined/single_list.html');
        }
    }

    /*
     * 分页初始化
     */

    public function getOneGoods() {
        $areaGoodsMod = &m('areaGood');
        $goodsItem = &m('storeGoodItemPrice');
        $main_id = ($_REQUEST['main_id']) ? $_REQUEST['main_id'] : '';
        $main_key = ($_REQUEST['main_key']) ? $_REQUEST['main_key'] : '';
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
        foreach ($data as $k => $v) {
            //规格数据
            $item = $goodsItem->getData(array("cond" => "store_goods_id=" . $v['id']));
//            if ($main_key) {
//                foreach ($item as $k1 => $v1) {
//                    if (($v['id'] == $main_id) && ($v1['key'] == $main_key)) {
//                        if (count($item) == 1) {
//                            unset($data[$k]);
//                            unset($item[$k1]);
//                        } else {
//                            unset($item[$k1]);
//                        }
//                    }
//                }
//            } else {
//                if ($v['id'] == $main_id) {
//                    unset($item[$k]);
//                }
//            }
            $data[$k]['item_child'] = $item;
        }
        $this->assign("goods_list", $data);
        $this->assign('lang_id', $this->lang_id);
        $type = ($_REQUEST['type']) ? $_REQUEST['type'] : '';
        $this->assign('symbol', $this->symbol);
        if ($type) {
            if ($this->lang_id == 1) {
                $this->display('combined/dialog_1.html');
            } else {
                $this->display('combined/dialog.html');
            }
        } else {
            if ($this->lang_id == 1) {
                $this->display('combined/dialog-single_1.html');
            } else {
                $this->display('combined/dialog-single.html');
            }
        }
    }

    /*
     * 模糊查询
     */

    public function seachSingle() {
        $areaGoodsMod = &m('areaGood');
        $goods_name = addslashes($_REQUEST['goods_name']);
        $area_cond = "where sg.mark=1 and sg.store_id=" . $this->storeId . " and sg.is_on_sale=1 and gl.lang_id=" . $this->storeInfo['lang_id'];
        if ($goods_name) {
            $area_cond .= " and (gl.goods_name like '%{$goods_name}%' or sg.goods_name like '%{$goods_name}%')";
            $this->assign("goods_name", $goods_name);
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
     * 获取商品区域商品信息初始页
     * @author lee
     * @date 2017-10-24 09:18:03
     *
     */

    public function getGoods() {
        $id = ($_REQUEST['id']) ? $_REQUEST['id'] : '';
        $main_id = ($_REQUEST['main_id']) ? $_REQUEST['main_id'] : '';
        $main_key = ($_REQUEST['main_key']) ? $_REQUEST['main_key'] : '';
        $areaGoodsMod = &m('areaGood');
        $goodsItem = &m('storeGoodItemPrice');
        $mod = $this->groupGoodsMod;
        $where = array("cond" => "com_id=" . $id);
        $has_list = $mod->getData($where);
        $has_ids = array();
        //提取指定数组元素
        array_walk($has_list, function($value, $key) use (&$has_ids) {
            $has_ids[] = $value['store_goods_id'];
        });
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
        $goods_list = $areaGoodsMod->querySql($sql);
        $good_data = array();
        foreach ($goods_list as $k => $v) {
            //处理商品是否已经包含在团购列表里
            //处理商品规格
            $item = $goodsItem->getData(array("cond" => "store_goods_id=" . $v['id']));
            if ($main_key) {
                foreach ($item as $k1 => $v1) {
                    if (($v['id'] == $main_id) && ($v1['key'] == $main_key)) {
                        if (count($item) == 1) {
                            unset($goods_list[$k]);
                            unset($item[$k1]);
                        } else {
                            unset($item[$k1]);
                        }
                    }
                }
            } else {
                if ($v['id'] == $main_id) {
                    unset($item[$k]);
                }
            }
            if ($item) {
                $goods_list[$k]['item_child'] = $item;
            }
        }
        $goods_list = array_filter($goods_list);
        $this->assign("goods_list", $goods_list);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('symbol', $this->symbol);
        if ($this->lang_id == 1) {
            $this->display('combined/dialog_1.html');
        } else {
            $this->display('combined/dialog.html');
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
        $res = $this->groupMod->doDrop($id);
        if ($res) {
//删除对应的组合销售商品
            $this->groupGoodsMod->doDelete(array("cond" => "com_id=" . $id));
            $this->setData(array(), $status = '1', $a['delete_Success']);
        } else {
            $this->setData(array(), $status = '0', $a['delete_fail']);
        }
    }

    /*
     * 改变状态
     * @author lee
     * @date 2017-11-8 20:23:14
     */

    public function changeSales() {
        $id = $_REQUEST['id'];
        $is_on_sale = $_REQUEST['is_on_sale'];
        $data = array(
            'status' => $is_on_sale
        );
        $rs = $this->groupMod->doEdit($id, $data);
        if ($rs) {
            $this->setData($info = array(), $status = 1, $message = '成功！');
        } else {
            $this->setData($info = array(), $status = 0, $message = '失败！');
        }
    }

}
