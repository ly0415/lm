<?php

/**
 * 秒杀
 * @author lee
 * @date 2017-11-8 13:48:51
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class SeckillApp extends BaseFrontApp {

    private $groupbuyMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->seckMod = &m('spikeActivity');
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 秒杀列表
     */
    public function goodslist() {
        $curpage = !empty($_REQUEST['p']) ? $_REQUEST['p'] : 1;  //当前第几页
        $curtime = time();
        $today = strtotime(date('Y-m-d', time()));
        $now = $curtime - $today;
        $storeId = $this->storeid;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $storeGoodsMod = &m('areaGood');
        //添加分页类
        include(ROOT_PATH . '/data/page/pageClass.php');
        $url = 'index.php?app=seckill&act=goodslist&storeid=' . $storeId . '&lang=' . $this->langid . '&auxiliary=' . $auxiliary;  //
        $pagesize = $this->pagesize; //每页显示条数
        $limit = '  limit ' . ($curpage - 1 ) * $pagesize . ',' . $pagesize;
        //
        $where = 'GROUP BY c.id   HAVING c.store_id =' . $storeId . '  and  ' . $curtime . ' > stime AND   etime > ' . $curtime . ' and sg.mark=1 and sg.is_on_sale=1' ;
        //统计条数
        $sqltotal = 'SELECT  COUNT(id)  as total   FROM  ' . DB_PREFIX . 'spike_activity GROUP BY id   HAVING store_id =' . $storeId . '  and  ' . $curtime . ' > stime AND   etime > ' . $curtime ;
        $res = $this->seckMod->querySql($sqltotal);
        $total = $res[0]['total'];  //总条数
        //实例化分页
        $pageClass = new page2($total, $pagesize, $curpage, $url, 2);
        // 输出分页html
        $pagelink = $pageClass->myde_write();
        //活动列表
        $sql = "SELECT  c.id,c.`name`,c.start_time,c.end_time,c.start_our,sgl.original_img as goods_img,c.end_our,c.store_id,c.store_goods_id,c.content,c.goods_name,c.item_name,c.item_key,c.discount,c.o_price,c.price,goods_num,(c.start_time+c.start_our) as stime,(c.end_time+c.end_our) as etime,sg.mark,sg.is_on_sale  
                FROM  " . DB_PREFIX . "spike_activity  as c left join  "
                . DB_PREFIX . "store_goods as sg on c.store_goods_id = sg.id left join  "
                . DB_PREFIX . "goods as sgl on sg.goods_id = sgl.goods_id " . $where . $limit;
        $arr = $this->seckMod->querySql($sql);

        foreach ($arr as $k => $item) {
            //多语言版本信息
            $gname = $this->getStoreGoodsLang($item['store_goods_id'], $this->langid);
            if ($gname) {
                $arr[$k]['goods_name'] = $gname['goods_name'];
            }
            if ($item['shipping_price'] == '') {
                $item['shipping_price'] = '0.00';
            }
            //翻译处理
            $child_info = $storeGoodsMod->getLangInfo($item['store_goods_id'], $this->langid);
            if ($item['item_key']) {
                $k_info = $this->getkeyName($item['item_key']);
                $arr[$k]['item_name'] = $k_info;
            }
            if ($child_info) {
                $k_name = $child_info['goods_name'];
                $arr[$k]['goods_name'] = $k_name;
            }
            //end
            //确认订单页跳转
            $mes = array(
                'store_id' => $storeId,
                'goods_id' => $item['store_goods_id'],
                'item_id' => $item['item_key'],
                'goods_num' => 1,
                'prom_id' => $item['id'],
                'goods_price' => $item['price'],
                'shipping_price' => $item['shipping_price'],
                'source' => 1,
                'goods_name' => $item['goods_name'],
                'goods_img' => $item['goods_img'],
                'goods_key_name' => str_replace(":", " ", $item['item_name']),
                'discount_rate' => $item['discount'],
                'origin_goods_price' => $item['o_price']
            );
//            $mes="&store_id=".$storeId."&goods_id=".$item['store_goods_id']."item_id=".$item['item_key']."goods_num=1&prom_id=".$item['id']."&goods_price
//            =".$item['price']."&shipping_price=".$item['shipping_price']."&source=1&goods_name=".$item['goods_name']."&goods_img=".$item['goods_img']
//            ."&goods_key_name=".$item['item_name']."discount_rate=".$item['discount']."&o_pirce=".$item['o_price'];
            $arr[$k]['goods_token'] = base64_encode(json_encode($mes));
            //判断该秒杀判断
            if (($curtime > $item['start_time']) && ($curtime < ($item['end_time'] + 3600 * 24 - 1))) {
                if (($now >= $item['start_our']) && ($now <= $item['end_our'])) {
                    $arr[$k]['in_time'] = 2;
                } else {
                    $arr[$k]['in_time'] = 1;
                }
            } else {
                $arr[$k]['in_time'] = 3;
            }
            $arr[$k]['end_time'] = $arr[$k]['end_our'] - $now;
            $arr[$k]['start_our'] = (mktime(0, 0, 0, date("m"), date("d"), date("Y")) + $arr[$k]['start_our']);
            $arr[$k]['end_our'] = (mktime(0, 0, 0, date("m"), date("d"), date("Y")) + $arr[$k]['end_our']);
        }
        //组装数据
        $res = array();
        $res['data'] = $arr;
        $res['pagelink'] = $pagelink;
        $this->assign('res', $res);
        ;
        //语言包
        $this->load($this->shorthand, 'Promotion/Promotion');
        $this->assign('langdata', $this->langData);
        $langguages = $this->shorthand;
        $this->assign('langguages', $langguages);
        $this->display('seckill/goodslist.html');
    }

    /**
     * 商品详情页面
     */
    public function goodsDetail(){
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $sid = ($_REQUEST['sid']) ? $_REQUEST['sid'] : 0;
        if(empty($sid)){
            $this->display("error/404.html");
        }
        //获取秒杀商品价格
        $kill_info = $this->seckMod->getOne(array("cond"=>"id =".$sid));
        if(empty($kill_info)){
            $this->display("error/404.html");
        }
        $id = ($kill_info['store_goods_id']) ? $kill_info['store_goods_id'] : 0;
        $goodMod = &m('goods');
        $goodClassMod = &m('goodsClass');
        $goodAttrMod = &m('goodsAttriInfo');
        $goodImgMod = &m('goodsImg');
        $storeGoods = &m('areaGood');
        $storeGoodMod = &m("storeGoodItemPrice");
        $footPrintMod  = &m('footprint');
        $goodsCommentMod = &m('goodsComment');
        if (empty($id)) {
            $this->display("error/404.html");
        }
        $info = $storeGoods->getLangInfo($id, $this->langid, $this->storeid);
        if (empty($info)) {
            $this->display("error/404.html");
        }
        if ($info['is_on_sale'] != 1) {
            $this->display("error/goodserror.html");
        }
        $goods_info = $goodMod->getOne(array("cond" => "goods_id=" . $info['goods_id']));
        if ($goods_info['is_on_sale'] != 1) {
            $this->display("error/goodserror.html");
        }
        $this->assign("kill_info",$kill_info);
        //分类信息
        $info['original_img'] = $goods_info['original_img'];

        $cat_3 = $goodClassMod->getLangInfo($goods_info['cat_id'], $this->langid);
        $cat_2 = $goodClassMod->getLangInfo($cat_3[0]['parent_id'], $this->langid);
        $cat_1 = $goodClassMod->getLangInfo($cat_2[0]['parent_id'], $this->langid);
        $this->assign("cat_3", $cat_3[0]);
        $this->assign("cat_2", $cat_2[0]);
        $this->assign("cat_1", $cat_1[0]);
        //商品图片页
        $img_arr = $goodImgMod->getData(array('cond' => "goods_id=" . $info['goods_id']));
        //商品规格
        $spec_data = $storeGoodMod->getData(array('cond' => "store_goods_id=" . $id));
        foreach ($spec_data as $k => $v) {
            $spec_arr[$v['key']] = $v;
        }
        //商品属性(暂时死数据展示，参数一 原始商品ID  参数二 语言ID)
        $attr_arr = $goodAttrMod->getLangData($info['goods_id'], $this->langid);
        //获取区域商品规格价格
        $spec_img = $this->get_spec($info['goods_id'], $id, 2);
        $where = ' f.user_id =' . $this->userId . ' and f.store_good_id =g.id';
        $sql = 'select distinct f.*,g.*,l.*,l.goods_name,gs.original_img,f.store_good_id  from '
            . DB_PREFIX . 'user_footprint as f inner join '
            . DB_PREFIX . 'store_goods as g on f.good_id = g.goods_id inner join '
            . DB_PREFIX . 'goods as gs on f.good_id = gs.goods_id inner join '
            . DB_PREFIX . 'goods_lang as l on l.goods_id = g.goods_id and l.lang_id=' . $this->langid . ' where '
            . $where . ' and g.mark = 1  and f.store_id =' . $this->storeid .
            ' group by f.good_id order by f.adds_time desc limit 0, 4 ';
        $data = $footPrintMod->querySql($sql);
        foreach ($data as $k => $v) {
            $store_good_id = $v['store_good_id'];
            $sql = 'select  sum(goods_rank) / count(goods_rank) as res from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $store_good_id;
            $trance = $goodsCommentMod->querySql($sql);
            $data[$k]['rate'] = $trance[0]['res'];
        }

        $this->assign('store_goods_id', $id);
        //获取币种信息
        $sql = "select c.* from " . DB_PREFIX . "currency as c inner join " . DB_PREFIX . "store as s on c.id=s.currency_id where s.id=" . $info['store_id'];
        $cur_info = $storeGoods->querySql($sql);
        //获取商品综合评分
        $good_rank_sql = "select goods_rank , count(1) as good_num from bs_goods_comment  where goods_id ={$info['id']}  group BY goods_rank";
        $good_rank_sta = $goodsCommentMod->querySql($good_rank_sql);
        $good_all_num = " select count(1) as all_num from bs_goods_comment  where goods_id ={$info['id']}";
        $good_all_num = $goodsCommentMod->querySql($good_all_num);
        $this->assign('good_all_num', $good_all_num[0]);
        $all_star = 0;
        foreach ($good_rank_sta as $key => $value) {
            $all_star = $all_star + ($value['goods_rank'] * $value['good_num']);
            $pre = $good_all_num[0]['all_num'] ? $value['good_num'] / $good_all_num[0]['all_num'] : 0;
            $good_comment_sta[$value['goods_rank']]['good_num'] = $value['good_num'];
            $good_comment_sta[$value['goods_rank']]['pre'] = round($pre, 2) * 100;
        }
        $all_rate = round($all_star / $good_all_num[0]['all_num']);
        $this->assign('good_comment_sta', $good_comment_sta);
        //获取评价列表信息
        //1.统计多少页数
        $sqlt = 'select  count(*)  as total  from ' . DB_PREFIX . 'goods_comment
                 where goods_id  = ' . $info['id'] . '  and  store_id = ' . $this->storeid;
        $totalD = $goodsCommentMod->querySql($sqlt);
        $total = $totalD[0]['total'];
        $totalpage = ceil($total / $this->commpagesize);
        if (empty($totalpage)) {
            $totalpage = 1;
        }
        $this->assign('commtotalpage', $totalpage);
        //2.获取第一页的信息
        $commlimit = '  limit 0,' . $this->commpagesize;
        $eva_sql = 'select  comment_id, username, goods_rank, add_time, content, img, revert  from ' . DB_PREFIX . 'goods_comment
                     where  goods_id  = ' . $info['id'] . ' and store_id = ' . $this->storeid . '   order by comment_id desc ' . $commlimit;
        $list = $goodsCommentMod->querySql($eva_sql);
        $new_list = array_map(function($vo) {
            $vo['img'] = explode(',', $vo['img']);
            return $vo;
        }, $list);
        $this->assign('all_rate', $all_rate);
        $this->assign('list', $new_list);
        $this->assign("store_id", $this->storeid);
        $this->assign("user_id", $this->userId);
        $this->assign("attr_arr", $attr_arr);
        $this->assign("cur_info", $cur_info[0]);
        $this->assign("spec_img", $spec_img);
        $this->assign("spec_arr", json_encode($spec_arr));
        $this->assign("info", $info);
        $this->assign("data", $data);
        $this->assign("img_arr", $img_arr);
        $this->assign('langId', $this->langid);
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang_id);
        //加载语言包
        $this->load($this->shorthand, 'goods/goods');
        $this->assign('langdata', $this->langData);
        $this->display('seckill/detail.html');
    }

    /**
     * 获取商品的多语言信息
     * @param $goodsId
     * @param $langid
     * @return mixed
     */
    public function getStoreGoodsLang($goodsId, $langid) {
        $storeGLMod = &m('storeGoodsLang');
        $sql = 'SELECT  goods_name,goods_content  FROM  ' . DB_PREFIX . 'store_goods_lang   WHERE  store_good_id = ' . $goodsId . ' AND   lang_id =' . $langid;
        $res = $storeGLMod->querySql($sql);
        return $res[0];
    }
    /**
     * 获取商品规格
     * @param $goods_id|商品id  $type=1 读取商品原有规格属性  2 读取区域编辑后的规格属性
     * @return array
     */
    public function get_spec($goods_id, $store_goods_id, $type = 1) {
        $storeGoodMod = &m("storeGoodItemPrice");
        //商品规格 价钱 库存表 找出 所有 规格项id
        //$keys = M('SpecGoodsPrice')->where("goods_id", $goods_id)->getField("GROUP_CONCAT(`key` SEPARATOR '_') ");
        if ($type == 1) {
            $sql = "select `key` as item_key from  " . DB_PREFIX . "goods_spec_price where goods_id=" . $goods_id;
        } else {
            $sql = "select `key` as item_key from  " . DB_PREFIX . "store_goods_spec_price where store_goods_id=" . $store_goods_id;
        }
        $keys = $storeGoodMod->querySql($sql);
        $filter_spec = array();
        if ($keys) {
            $key_str = "";
            foreach ($keys as $k => $v) {
                $key_str .= $v['item_key'] . "_";
            }
            $res_item = substr($key_str, 0, strlen($key_str) - 1);
            $keys = str_replace('_', ',', $res_item);
            $specImage = array();
            $sql3 = "select spec_image_id,src from " . DB_PREFIX . "goods_spec_image where goods_id=" . $goods_id; // 规格对应的 图片表， 例如颜色
            $img_list = $storeGoodMod->querySql($sql3);
            foreach ($img_list as $k => $v) {
                $specImage[$v['spec_image_id']] = $v['src'];
            }
            $sql4 = "SELECT a.`order`,b.*,al.spec_name,bl.`item_name`  FROM " . DB_PREFIX . "goods_spec AS a
                     INNER JOIN " . DB_PREFIX . "goods_spec_item AS b ON a.id = b.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_lang AS al ON a.id=al.spec_id
                     LEFT JOIN " . DB_PREFIX . "goods_spec_item_lang as bl ON b.id=bl.item_id
                     WHERE b.id IN($keys) and al.lang_id=" . $this->langid . " and bl.lang_id=" . $this->langid . " ORDER BY a.sort";
            $filter_spec2 = $storeGoodMod->querySql($sql4);
            foreach ($filter_spec2 as $key => $val) {
                $filter_spec[$val['spec_name']][] = array(
                    'item_id' => $val['id'],
                    'item' => $val['item_name'],
                    'src' => $specImage[$val['id']],
                );
            }
        }
        return $filter_spec;
    }

    public function infoFootPrint($goods_id, $id) {
        $userId = $this->userId;
        $sql = "select id,good_id from  " . DB_PREFIX . "user_footprint where user_id=" . $userId . " and store_good_id=" . $id . " order by adds_time desc";
        $keys = $this->footPrintMod->querySql($sql);
        if (empty($keys)) {
            if ($goods_id != $keys[0]['good_id']) {
                $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;  //所选的站点id
                $data['user_id'] = $userId;
                $data['good_id'] = $goods_id;
                $data['store_id'] = $storeid;
                $data['adds_time'] = time();
                $data['store_good_id'] = $id;
                $re = $this->footPrintMod->doInsert($data);
            }
        } else {
            $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
            $data['table'] = "user_footprint";
            $data['cond'] = "id=" . $keys[0]['id'];
            $data['set'] = array(
                'adds_time' => time(),
            );
            $re = $this->footPrintMod->doUpdate($data);
        }
    }

}
