<?php

/**
 * 商品秒杀controller
 * User: gao
 * Date: 2018/12/17
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class SpikeActiviesApp extends BaseStoreApp {
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
    }

    //商品秒杀活动页面
    public function index() {
        $spikeActiviesMod=&m('spikeActivies');
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $name = !empty($_REQUEST['name']) ? htmlspecialchars($_REQUEST['name']) : '';
        $where = " where sa.store_id = {$this->storeId} and sa.mark=1";
        if (!empty($name)) {
            $where .= " and sa.name like '%" . $name . "%'";
        }
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "spike_activity";
        $totalCount = $spikeActiviesMod->querySql($totalSql);
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $where .= " group by sa.id order by sa.add_time desc";
        $sql = "select sa.* from " . DB_PREFIX . "spike_activity as sa left join  " . DB_PREFIX . "spike_goods AS sg on sa.id = sg.spike_id " . $where;
        $rs =$spikeActiviesMod ->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($rs['list'] as $k => $v) {
            $rs['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
        }
        $this->assign('p', $p);
        $this->assign('list', $rs['list']);
        $this->assign('page_html', $rs['ph']);
        $this->assign('symbol', $this->symbol);
        $this->assign('name', $name);
        $this->assign('lang_id', $this->lang_id);
        $this->display('spikeActivies/index.html');
    }

    //秒杀商品添加
    public function add() {
        $this->assign('lang_id', $this->lang_id);
        $this->assign('store_id', $this->storeId);
        $this->assign('symbol', $this->symbol);
        $this->display('spikeActivies/add.html');
    }

    /**
     * 商品添加
     * User: wanyan
     * Date: 2017/10/31
     */
    public function getAjaxData() {
        $spikeActiviesMod=&m('spikeActivies');
        $spikeActiviesGoodsMod=&m('spikeActiviesGoods');
        $storeGoodsItemsMod = &m("storeGoodItemPrice");
        $storeGoodsMod=&m("areaGood");
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $prom_name = !empty($_REQUEST['prom_name']) ? htmlspecialchars(trim($_REQUEST['prom_name'])) : '';
        $start_time = !empty($_REQUEST['start_time']) ? htmlspecialchars(trim($_REQUEST['start_time'])) : '';
        $end_time = !empty($_REQUEST['end_time']) ? htmlspecialchars(trim($_REQUEST['end_time'])) : '';
        $sgoods_id = !empty($_REQUEST['goods_id']) ? $_REQUEST['goods_id'] : '';
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->storeId;
        $lang_id = !empty($_REQUEST['lang_id']) ? $_REQUEST['lang_id'] : $this->lang_id;
        $dataAttr = !empty($_REQUEST['dataattr']) ? $_REQUEST['dataattr'] : '';
        $dataKey = !empty($_REQUEST['datakey']) ? $_REQUEST['datakey'] : '';
        $dataUrl = !empty($_REQUEST['dataurl']) ? $_REQUEST['dataurl'] : '';
        $dataMprice = !empty($_REQUEST['datamprice']) ? $_REQUEST['datamprice'] : '';
        $discount = !empty($_REQUEST['discount']) ? $_REQUEST['discount'] : '';
        $dzprice = !empty($_REQUEST['zprice']) ? $_REQUEST['zprice'] : '';
        $lessprice = !empty($_REQUEST['lessprice']) ? $_REQUEST['lessprice'] : '0.00';
        $goods_num=!empty($_REQUEST['goods_num']) ? $_REQUEST['goods_num'] : '';
        $limit_num=!empty($_REQUEST['limit_num']) ? $_REQUEST['limit_num'] : '';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;

        foreach ($sgoods_id as $k => $v) {
            $goods_id[] = $v . '-' . $dataKey[$k];
        }
        if (empty($prom_name)) {
                $this->setData(array(), $status = '0', '活动名称不能为空');
            } else {
                $query = array(
                    'cond' => "`name` = '{$prom_name}' and mark ='1' and `store_id` = '{$store_id}'",
                    'fields' => "`id`"
                );
                $r = $spikeActiviesMod->getOne($query);
                if ($r) {
                    $this->setData(array(), $status = '0', '该活动已存在');
                }
            }

        //库存以及限购数量判断
        foreach($sgoods_id as $k=>$v){
            if(!empty($dataKey[$k])){
                $storeGoodsStorage=$storeGoodsItemsMod->getSpecAccount($v,$dataKey[$k]);
            }else{

                $storeGoodsStorage=$storeGoodsMod->getStorage($v);


            }
            $storeGoodsName=$this->getGoodsName($v);
            if($storeGoodsStorage< $goods_num[$k]){
                $this->setData('',0,$storeGoodsName.'商品库存为'.$storeGoodsStorage);
            }
            if($goods_num[$k]<$limit_num[$k]){
                $this->setData('',0,$storeGoodsName.'限购数量超过了商品数量');
            }
        }

        //判断商品在交集时间类不能重复添加
        $checkRs = $spikeActiviesMod->checkRepeat($goods_id, $store_id, $start_time, $end_time);
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
                $this->setData(array(), $status = '0', $message = "( '{$good_name}' ) 商品在时间交集内有重复商品！");
        }
        $insert_prom_data = array(
            'name' => $prom_name,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'store_id' => $this->storeId,
            'add_time' => time(),
            'mark' => 1,
        );
        $rm = $spikeActiviesMod->doInsert($insert_prom_data);
        if ($rm) {
            foreach ($sgoods_id as $k => $v) {
                $insert_detail_data = array(
                    'spike_id' => $rm,
                    'store_goods_id' => $v,
                    'goods_name' => $this->getGoodsName($v),
                    'goods_img' => $dataUrl[$k],
                    'goods_key' => $dataKey[$k],
                    'goods_key_name' => $dataAttr[$k],
                    'goods_price' => $dataMprice[$k],
                    'reduce' => $lessprice[$k],
                    'discount_price' => $dzprice[$k],
                    'discount'=>$discount[$k],
                    'limit_num' => $limit_num[$k],
                    'goods_num'=>$goods_num[$k],
                    'mark'=>1,
                    'add_time' => time()
                );
                $drs[] = $spikeActiviesGoodsMod->doInsert($insert_detail_data);
            }
            if ($drs) {
                $info['url'] = "?app=spikeActivies&act=index&lang_id={$lang_id}&p={$p}";
                $this->setData($info, $status = '1', $a['add_Success']);
            } else {
                $this->setData(array(), $status = '0', $a['add_fail']);
            }
        }
    }
    //删除活动
    public function del(){
            $spikeActiviesMod=&m('spikeActivies');
            $spikeActiviesGoodsMod=&m('spikeActiviesGoods');
            $id=!empty($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
            $rs = $spikeActiviesMod->doMark($id);
            if($rs){
                $sql="update ".DB_PREFIX."spike_goods set mark = 0 where spike_id in (".$id.")";
                $res=$spikeActiviesGoodsMod->sql_b_spec($sql);
            }
            if ($res) {
                $this->setData($info = array(), $status = 1, "删除成功");
             } else {
                $this->setData($info = array(), $status = 0, "删除失败");
            }
    }
    /**
     * 获取当前商品的名称
     * User: wanyan
     * Date: 2017/10/27
     */
    public function getGoodsName($good_id) {
        $storeGoodsMod=&m('areaGood');
        $sql = "SELECT sg.id, (CASE WHEN ISNULL(sgl.goods_name) THEN sg.goods_name ELSE sgl.goods_name END) as goods_name
       FROM " . DB_PREFIX . "store_goods AS sg LEFT JOIN " . DB_PREFIX . "store_goods_lang AS sgl ON sg.id = sgl.store_good_id WHERE
  	   sg.store_id = $this->storeId  AND sg.is_on_sale = 1  AND sg.mark = 1 and sg.id='{$good_id}'";
        $rs = $storeGoodsMod->querySql($sql);
        return addslashes($rs[0]['goods_name']);
    }
    /**
     * 商品促销编辑页面
     * User: wanyan
     * Date: 2017/10/27
     */
    public function edit() {
        $spikeActiviesMod=&m('spikeActivies');
        $spikeActiviesGoodsMod=&m('spikeActiviesGoods');
        $storeGoodsItemsMod = &m("storeGoodItemPrice");
        $storeGoodsMod=&m("areaGood");
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        // 活动数据
        $sql = "select * from " . DB_PREFIX . "spike_activity where `id` = '{$id}' and mark=1 ";
        $spikeData = $spikeActiviesMod->querySql($sql);
        $sql = "select * from " . DB_PREFIX . "spike_goods where `spike_id` = '{$id}' and mark=1";
        $spikeGoodsData = $spikeActiviesGoodsMod->querySql($sql);
        $spikeData[0]['start_time'] = date('Y-m-d H:i:s', $spikeData[0]['start_time']);
        $spikeData[0]['end_time'] = date('Y-m-d H:i:s', $spikeData[0]['end_time']);
        foreach($spikeGoodsData as $key => $val){
            $spikeGoodsData[$key]['hid']=$val['store_goods_id'].$val['goods_key'];
        }
        $this->assign('spikeData', $spikeData[0]);
        $this->assign('spikeGoodsData', $spikeGoodsData);
        $this->assign('lang_id', $this->lang_id);
        $this->assign('store_id', $this->storeId);
        $this->assign('spike_id', $id);
        $this->assign('p', $p);
        $this->assign('act', 'index');
        $this->assign('symbol', $this->symbol);
        $this->display('spikeActivies/edit.html');
    }

    /**
     * 商品促销编辑页面
     * User: wanyan
     * Date: 2017/11/1
     */
    public function spikeEdit() {
        $spikeActiviesMod=&m('spikeActivies');
        $spikeActiviesGoodsMod=&m('spikeActiviesGoods');
        $storeGoodsItemsMod = &m("storeGoodItemPrice");
        $storeGoodsMod=&m("areaGood");
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
        $goods_num=!empty($_REQUEST['goods_num']) ? $_REQUEST['goods_num'] : '';
        $limit_num=!empty($_REQUEST['limit_num']) ? $_REQUEST['limit_num'] : '';
        foreach ($sgoods_id as $k => $v) {
            $goods_id[] = $v . '-' . $dataKey[$k];
        }
        if (empty($prom_name)) {
            $this->setData(array(), $status = '0', '活动名称不能为空');
        }
        //库存以及限购数量判断
        foreach($sgoods_id as $k=>$v){
            if(!empty($dataKey[$k])){
                $storeGoodsStorage=$storeGoodsItemsMod->getSpecAccount($v,$dataKey[$k]);
            }else{
                $storeGoodsStorage=$storeGoodsMod->getStorage($v);
            }
            $storeGoodsName=$this->getGoodsName($v);
            if($storeGoodsStorage< $goods_num[$k]){
                $this->setData('',0,$storeGoodsName.'商品库存为'.$storeGoodsStorage);
            }
            if($goods_num[$k]<$limit_num[$k]){
                $this->setData('',0,$storeGoodsName.'限购数量超过了商品数量');
            }
        }

        //判断商品在交集时间类不能重复添加
        $checkRs = $spikeActiviesMod->checkRepeat($goods_id, $store_id, $start_time, $end_time,$prom_id);
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
            $this->setData(array(), $status = '0', $message = "( '{$good_name}' ) 商品在时间交集内有重复商品！");
        }
        // 插入数据到活动主表
        $edit_spike_data = array(
            'name' => $prom_name,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'store_id' => $this->storeId,
        );
        $rm =$spikeActiviesMod ->doEdit($prom_id, $edit_spike_data);
        if ($rm) {
            $spikeActiviesGoodsMod->doDelete(array('cond' => "`spike_id`='{$prom_id}'"));

            foreach ($sgoods_id as $k => $v) {
                $insert_detail_data = array(
                    'spike_id' => $prom_id,
                    'store_goods_id' => $v,
                    'goods_name' => $this->getGoodsName($v),
                    'goods_img' => $dataUrl[$k],
                    'goods_key' => $dataKey[$k],
                    'goods_key_name' => $dataAttr[$k],
                    'goods_price' => $dataMprice[$k],
                    'reduce' => $lessprice[$k],
                    'discount_price' => $dzprice[$k],
                    'discount'=>$discount[$k],
                    'limit_num' => $limit_num[$k],
                    'goods_num'=>$goods_num[$k],
                    'mark'=>1,
                    'add_time' => time()
                );

                $drs[] = $spikeActiviesGoodsMod->doInsert($insert_detail_data);
            }

            if ($drs) {
                $info['url'] = "?app=spikeActivies&act=index&lang_id={$lang_id}&p={$p}";
                $this->setData($info, $status = '1', $a['edit_Success']);
            } else {
                $this->setData(array(), $status = '0', $a['edit_fail']);
            }
        }
    }

    public function delData(){
        $spikeActiviesGoodsMod=&m('spikeActiviesGoods');
        $spikeGoodId = !empty($_REQUEST['spikeGoodId']) ? $_REQUEST['spikeGoodId'] : 0;
        $spikeActiviesGoodsMod->doDrop($spikeGoodId);
    }


}
