<?php

/**
 * 商品促销controller
 * User: wanyan
 * Date: 2017/10/27
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class GoodPromApp extends BaseStoreApp {

    private $goodPromMod;
    private $goodPromDetailMod;
    private $lang_id;
    private $storeGoodsMod;
    private $storeGoodItemPriceMod;
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->goodPromMod = &m('goodProm');
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
        $this->storeGoodsMod = &m('areaGood');
        $this->storeGoodItemPriceMod = &m('storeGoodItemPrice');
        $this->goodPromDetailMod = &m('goodPromDetail');
    }

    /**
     * 商品促销页面
     * User: wanyan
     * Date: 2017/10/27
     */
    public function index() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $prom_name = !empty($_REQUEST['prom_name']) ? htmlspecialchars($_REQUEST['prom_name']) : '';
        $status = !empty($_REQUEST['status']) ? intval($_REQUEST['status']) : '0';
        $where = " where gp.store_id = $this->storeId and mark=1";
        if (!empty($prom_name)) {
            $where .= " and gp.prom_name like '%" . $prom_name . "%'";
        }
        if (!empty($status)) {
            $where .= " and gp.status = '{$status}'";
        }
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "promotion_goods ";
        $totalCount = $this->goodPromMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $where .= " group by gp.id order by gp.add_time desc";
        $sql = "select gp.* from " . DB_PREFIX . "promotion_sale as gp left join  " . DB_PREFIX . "promotion_goods AS pg on gp.id = pg.prom_id " . $where;
        $rs = $this->goodPromMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
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
            $this->goodPromMod->doEdit($v['id'], array('status' => $vstatus));
            $rs['list'][$k]['start_time'] = date('Y-m-d H:i:s', $v['start_time']);
            $rs['list'][$k]['end_time'] = date('Y-m-d H:i:s', $v['end_time']);
            $rs['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        $this->assign('p', $p);
        $this->assign('list', $rs['list']);
        $this->assign('page_html', $rs['ph']);
        $this->assign('isrecom', $status);
        $this->assign('symbol', $this->symbol);
        $this->assign('prom_name', $prom_name);
        $this->assign('lang_id', $this->lang_id);
        $this->display('goodProm/promList.html');
    }

    /**
     * 商品促销添加页面
     * User: wanyan
     * Date: 2017/10/27
     */
    public function add() {
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $this->assign('p', $p);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('act', 'index');
        $this->assign('store_id', $this->storeId);
        $this->assign('symbol', $this->symbol);
        if ($this->lang_id == 1) {
            $this->display('goodProm/promAdd_1.html');
        } else {
            $this->display('goodProm/promAdd.html');
        }
    }

    /**
     * 商品促销添加方法
     * User: wanyan
     * Date: 2017/10/31
     */
    public function getAjaxData() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $prom_name = !empty($_REQUEST['prom_name']) ? htmlspecialchars(trim($_REQUEST['prom_name'])) : '';
        $start_time = !empty($_REQUEST['start_time']) ? htmlspecialchars(trim($_REQUEST['start_time'])) : '';
        $end_time = !empty($_REQUEST['end_time']) ? htmlspecialchars(trim($_REQUEST['end_time'])) : '';
        $sgoods_id = !empty($_REQUEST['goods_id']) ? $_REQUEST['goods_id'] : '';
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->storeId;
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        $dataUrl = !empty($_REQUEST['dataurl']) ? $_REQUEST['dataurl'] : '';
        $dataMprice = !empty($_REQUEST['datamprice']) ? $_REQUEST['datamprice'] : '';

        $discount = !empty($_REQUEST['discount']) ? $_REQUEST['discount'] : '';
        $dzprice = !empty($_REQUEST['zprice']) ? $_REQUEST['zprice'] : '';
        $limit_amount = !empty($_REQUEST['limit_amount']) ? $_REQUEST['limit_amount'] : '0';
        $lessprice = !empty($_REQUEST['lessprice']) ? $_REQUEST['lessprice'] : '0.00';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;

        foreach ($sgoods_id as $k => $v) {
            $goods_id[] = $v;
        }
        if (empty($prom_name)) {
            $this->setData(array(), $status = '0', $a['Prom__home']);
        } else {
            $query = array(
                'cond' => "`prom_name` = '{$prom_name}' and mark ='1' and `store_id` = '{$store_id}'",
                'fields' => "`id`"
            );
            $r = $this->goodPromMod->getOne($query);
            if ($r) {
                $this->setData(array(), $status = '0', $a['Prom__Already']);
            }
        }

        $checkRs = $this->checkRepeat($goods_id, $store_id, $start_time, $end_time);
        if ($checkRs) {
            if (count($checkRs) == 1) {
                $s = explode('-', $checkRs[0]);
                $good_name = $this->getGoodsName($s[0]);
            } else {

                foreach ($checkRs as $k => $v) {
                    $s = explode('-', $v);
                    $good_name[] = $this->getGoodsName($s[0]);
                }
                $good_name = implode(',', $good_name);
            }
            if ($this->lang_id == 1) {
                $this->setData(array(), $status = '0', $message = "( '{$good_name}' ) Promotional goods have duplicate goods in the intersection of time！");
            } else {
                $this->setData(array(), $status = '0', $message = "( '{$good_name}' ) 促销商品在时间交集内有重复商品！");
            }
        }
        // 插入数据到活动主表
        $insert_prom_data = array(
            'prom_name' => $prom_name,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'store_id' => $this->storeId,
            'add_time' => time(),
            'mark' => 1,
        );
        $rm = $this->goodPromMod->doInsert($insert_prom_data);
        if ($rm) {
            foreach ($sgoods_id as $k => $v) {
                $insert_detail_data = array(
                    'prom_id' => $rm,
                    'goods_id' => $v,
                    'goods_name' => $this->getGoodsName($v),
                    'goods_img' => $dataUrl[$k],
                    'goods_key' => '',
                    'goods_key_name' => '',
                    'goods_price' => $dataMprice[$k],
                    'discount_rate' => $discount[$k],
                    'reduce' =>0,
                    'discount_price' => $dzprice[$k],
                    'limit_amount' => $limit_amount[$k],
                    'add_time' => time()
                );
                $drs[] = $this->goodPromDetailMod->doInsert($insert_detail_data);
            }
            if ($drs) {
                $info['url'] = "?app=goodProm&act=index&lang_id={$lang_id}&p={$p}";
                $this->setData($info, $status = '1', $a['add_Success']);
            } else {
                $this->setData(array(), $status = '0', $a['add_fail']);
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
        $rs = $this->goodPromMod->querySql($sql);
        return $rs[0]['goods_storage'];
    }

    /**
     * 判断商品在交集时间类不能重复添加
     * User: wanyan
     * Date: 2017/11/02
     */
    public function checkRepeat($good_id, $store_id, $start_time, $end_time, $id) {
        if (!empty($id)) {
            $sql = "SELECT 	pg.goods_id,pg.goods_key FROM  `bs_promotion_sale` AS ps  LEFT JOIN `bs_promotion_goods` AS pg ON ps.id = pg.prom_id
        WHERE ps.mark = 1 and `store_id` = '{$store_id}' AND 	((start_time >= '{$start_time}' AND start_time <= '{$end_time}') OR 
        (start_time <= '{$start_time}' AND end_time >= '{$end_time}') OR
        (end_time >= '{$start_time}' AND end_time <= '{$end_time}')) and ps.id !='{$id}'";
        } else {
            $sql = "SELECT 	pg.goods_id,pg.goods_key FROM  `bs_promotion_sale` AS ps  LEFT JOIN `bs_promotion_goods` AS pg ON ps.id = pg.prom_id
        WHERE ps.mark = 1 and `store_id` = '{$store_id}' AND 	((start_time >= '{$start_time}' AND start_time <= '{$end_time}') OR 
        (start_time <= '{$start_time}' AND end_time >= '{$end_time}') OR
        (end_time >= '{$start_time}' AND end_time <= '{$end_time}'))";
        }
        $goodsInfo = $this->goodPromMod->querySql($sql);
        foreach ($goodsInfo as $k => $v) {
            $new[] = $v['goods_id'];
        }
        foreach ($good_id as $k1 => $v1) {
            if (in_array($v1, $new)) {
                $ngood[] = $v1;
            }
        }
        if (count($ngood)) {
            return $ngood;
        } else {
            return 0;
        }
    }

    /**
     * 获取当前商品的名称
     * User: wanyan
     * Date: 2017/10/27
     */
    public function getGoodsName($good_id) {
        $sql = "SELECT sg.id, (CASE WHEN ISNULL(sgl.goods_name) THEN sg.goods_name ELSE sgl.goods_name END) as goods_name
       FROM " . DB_PREFIX . "store_goods AS sg LEFT JOIN " . DB_PREFIX . "store_goods_lang AS sgl ON sg.id = sgl.store_good_id WHERE
  	   sg.store_id = $this->storeId  AND sg.is_on_sale = 1  AND sg.mark = 1 and sg.id='{$good_id}'";
        $rs = $this->storeGoodsMod->querySql($sql);
        return addslashes($rs[0]['goods_name']);
    }

    /**
     * 商品促销数据添加
     * 1.上架,2.未删除的,3.当前店铺的
     * User: wanyan
     * Date: 2017/10/27
     */
    public function getGoods() {
        $sql = "SELECT sg.id, (CASE WHEN ISNULL(sgl.goods_name) THEN sg.goods_name ELSE sgl.goods_name END) as goods_name,gl.original_img,
       sg.market_price,sg.shop_price  FROM "
            . DB_PREFIX . "store_goods AS sg LEFT JOIN "
            . DB_PREFIX . "store_goods_lang AS sgl ON sg.id = sgl.store_good_id  LEFT JOIN "
            . DB_PREFIX . "goods AS gl ON sg.goods_id = gl.goods_id WHERE
  	  sg.store_id = $this->storeId  AND sg.is_on_sale = 1  AND sg.mark = 1 ORDER  by sg.id desc ";
        $info = $this->storeGoodsMod->querySqlPageData($sql);
        foreach ($info['list'] as $k => $v) {
            $query = array(
                'cond' => "`store_goods_id` = '{$v['id']}'",
                'fields' => "`id`,`key`,`key_name`,`price`"
            );
            $specInfo = $this->storeGoodItemPriceMod->getData($query);
            if ($specInfo) {
                $info['list'][$k]['child'] = $specInfo;
            }
        }
        $this->assign('list', $info['list']);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('symbol', $this->symbol);
        $this->display('goodProm/dialog.html');
    }

    /**
     * 商品促销编辑页面
     * User: wanyan
     * Date: 2017/10/27
     */
    public function edit() {
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        // 活动数据
        $sql = "select * from " . DB_PREFIX . "promotion_sale where `id` = '{$id}'";
        $prom_main_info = $this->goodPromMod->querySql($sql);
        $sql = "select * from " . DB_PREFIX . "promotion_goods where `prom_id` = '{$id}'";
        $prom_detail_info = $this->goodPromDetailMod->querySql($sql);
        $prom_main_info[0]['start_time'] = date('Y-m-d H:i:s', $prom_main_info[0]['start_time']);
        $prom_main_info[0]['end_time'] = date('Y-m-d H:i:s', $prom_main_info[0]['end_time']);
        foreach($prom_detail_info as $key => $val){
            $prom_detail_info[$key]['hid']=$val['goods_id'].$val['goods_key'];
        }

        $this->assign('prom_main_info', $prom_main_info[0]);
        $this->assign('prom_detail_info', $prom_detail_info);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('store_id', $this->storeId);
        $this->assign('prom_id', $id);
        $this->assign('p', $p);
        $this->assign('act', 'index');
        $this->assign('symbol', $this->symbol);
        if ($this->lang_id == 1) {
            $this->display('goodProm/promEdit_1.html');
        } else {
            $this->display('goodProm/promEdit.html');
        }
    }

    /**
     * 商品促销编辑页面
     * User: wanyan
     * Date: 2017/11/1
     */
    public function promEdit() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $prom_id = !empty($_REQUEST['prom_id']) ? htmlspecialchars(trim($_REQUEST['prom_id'])) : '';
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->storeId;
        $prom_name = !empty($_REQUEST['prom_name']) ? htmlspecialchars(trim($_REQUEST['prom_name'])) : '';
        $start_time = !empty($_REQUEST['start_time']) ? htmlspecialchars(trim($_REQUEST['start_time'])) : '';
        $end_time = !empty($_REQUEST['end_time']) ? htmlspecialchars(trim($_REQUEST['end_time'])) : '';
        $sgoods_id = !empty($_REQUEST['goods_id']) ? $_REQUEST['goods_id'] : '0';
        $dataAttr = !empty($_REQUEST['dataattr']) ? $_REQUEST['dataattr'] : "";
        $dataKey = !empty($_REQUEST['datakey']) ? $_REQUEST['datakey'] : '';

        $dataUrl = !empty($_REQUEST['dataurl']) ? $_REQUEST['dataurl'] : '';
        $dataMprice = !empty($_REQUEST['datamprice']) ? $_REQUEST['datamprice'] : '0.00';
        $discount = !empty($_REQUEST['discount']) ? $_REQUEST['discount'] : '0.00';
        $dzprice = !empty($_REQUEST['zprice']) ? $_REQUEST['zprice'] : '0.00';
        $limit_amount = !empty($_REQUEST['limit_amount']) ? $_REQUEST['limit_amount'] : '0';
        $lessprice = !empty($_REQUEST['lessprice']) ? $_REQUEST['lessprice'] : '0.00';
        if (empty($prom_name)) {
            $this->setData(array(), $status = '0', $a['Prom__home']);
        } else {
            $query = array(
                'cond' => "`prom_name` = '{$prom_name}' and `mark`=1 and `id` !='{$prom_id}' and `store_id` = '{$store_id}'",
                'fields' => "`id`"
            );
            $r = $this->goodPromMod->getOne($query);
            if ($r) {
                $this->setData(array(), $status = '0', $a['Prom__Already']);
            }
        }

        // 检查商品是否有重复..
        foreach ($sgoods_id as $k => $v) {
            $goods_id[] = $v;
        }
        $checkRs = $this->checkRepeat($goods_id, $store_id, $start_time, $end_time, $prom_id);
        if ($checkRs) {
            if (count($checkRs) == 1) {
                $s = explode('-', $checkRs[0]);
                $good_name = $this->getGoodsName($s[0]);
            } else {

                foreach ($checkRs as $k => $v) {
                    $s = explode('-', $v);
                    $good_name[] = $this->getGoodsName($s[0]);
                }
                $good_name = implode(',', $good_name);
            }
            if ($this->lang_id == 1) {
                $this->setData(array(), $status = '0', $message = "( '{$good_name}' ) Promotional goods have duplicate goods in the intersection of time！");
            } else {
                $this->setData(array(), $status = '0', $message = "( '{$good_name}' ) 促销商品在时间交集内有重复商品！");
            }
        }
//        if($start_time >= $end_time){
//            $this->setData(array(),$status='0',$message='活动开始时间大于结束时间！');
//        }
        // 插入数据到活动主表
        $edit_prom_data = array(
            'prom_name' => $prom_name,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'store_id' => $this->storeId,
        );
        $rm = $this->goodPromMod->doEdit($prom_id, $edit_prom_data);
        if ($rm) {
            $this->goodPromDetailMod->doDelete(array('cond' => "`prom_id`='{$prom_id}'"));
            foreach ($sgoods_id as $k => $v) {
                $insert_detail_data = array(
                    'prom_id' => $prom_id,
                    'goods_id' => $v,
                    'goods_name' => $this->getGoodsName($v),
                    'goods_img' => $dataUrl[$k],
                    'goods_price' => $dataMprice[$k],
                    'discount_rate' => $discount[$k],
                    'reduce' => 0,
                    'discount_price' => $dzprice[$k],
                    'limit_amount' => $limit_amount[$k],
                    'add_time' => time()
                );
                $drs[] = $this->goodPromDetailMod->doInsert($insert_detail_data);
            }
            if ($drs) {
                $info['url'] = "?app=goodProm&act=index&lang_id={$lang_id}&p={$p}";
                $this->setData($info, $status = '1', $a['edit_Success']);
            } else {
                $this->setData(array(), $status = '0', $a['edit_fail']);
            }
        }
    }

    /**
     * 商品促销添加页面
     * User: wanyan
     * Date: 2017/10/27
     */
    public function dele() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '0';
        $rs = $this->goodPromMod->doMark($id);
        if ($rs) {
            $this->setData($info = array(), $status = 1, $a['delete_Success']);
        } else {
            $this->setData($info = array(), $status = 0, $a['delete_fail']);
        }
    }

}
