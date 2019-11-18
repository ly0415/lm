<?php

/**
 * 商品团购页面
 * @author wangh
 * @date 2017/08/22
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class GroupbuyApp extends BaseFrontApp {

    private $groupbuyMod;
    private $goodsCommentMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->groupbuyMod = &m('groupbuy');
        $this->goodsCommentMod = &m('goodsComment');
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 团购商品列表
     */
    public function goodslist() {
        $curpage = !empty($_REQUEST['p']) ? $_REQUEST['p'] : 1;  //当前第几页
        $curtime = time();
        //1.跟新活动是否结束
        $where1 = '  where  c.store_id = ' . $this->storeid . ' and  c.mark =1 and sg.mark=1 and sg.is_on_sale =1 ';
        $sqle = "SELECT  c.id,c.goods_id,c.store_id,c.start_time,c.end_time,c.group_goods_price,c.virtual_num,sgl.original_img,c.goods_price,c.goods_name  FROM  "
                . DB_PREFIX . "goods_group_buy   as c left join  "
                . DB_PREFIX . "store_goods as sg on c.goods_id = sg.id left join  "
                . DB_PREFIX . "goods as sgl on sg.goods_id = sgl.goods_id  " . $where1;
        $data = $this->groupbuyMod->querySql($sqle);
        foreach ($data as $k => $val) {
            //活动的状态的更改
            if ($curtime < $val['start_time']) {
                //未开始
                $this->groupbuyMod->doEdit($val['id'], array('is_end' => 3));
            } else if (( $curtime > $val['start_time'] ) && ( $curtime < $val['end_time'] )) {
                //进行中
                $this->groupbuyMod->doEdit($val['id'], array('is_end' => 1));
            } else if ($curtime > $val['end_time']) {
                //结束
                $this->groupbuyMod->doEdit($val['id'], array('is_end' => 2));
            }
        }
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        //添加分页类
        include(ROOT_PATH . '/data/page/pageClass.php');
        $url = 'index.php?app=groupbuy&act=goodslist&storeid=' . $this->storeid . '&lang=' . $this->langid . '&auxiliary=' . $auxiliary;  //
        $pagesize = $this->pagesize; //每页显示条数
        $limit = '  limit ' . ($curpage - 1 ) * $pagesize . ',' . $pagesize;
        //
        $where = 'WHERE  l.`lang_id` = ' . $this->langid . '  and  b.store_id =' . $this->storeid . '  AND b.is_end =1 AND b.mark = 1 AND b.group_goods_num and g.mark=1 and g.is_on_sale =1';
        //统计条数
        $sqltotal = 'SELECT  COUNT(id)  as total   FROM  ' . DB_PREFIX . 'goods_group_buy ' . $where;

        $res = $this->groupbuyMod->querySql($sqltotal);
        $total = $res[0]['total'];  //总条数
        //实例化分页类
        $pageClass = new page2($total, $pagesize, $curpage, $url, 2);
        // 输出分页html
        $pagelink = $pageClass->myde_write();
        //活动列表
        $sql = 'SELECT  b.id,b.goods_id,b.store_id,b.end_time,b.group_goods_price,b.virtual_num,lg.original_img,b.goods_price,l.`goods_name`
                FROM  bs_goods_group_buy   AS b  LEFT JOIN   bs_store_goods AS g ON b.`goods_id` = g.id
                LEFT JOIN  bs_goods_lang AS l ON g.`goods_id` = l.`goods_id`  LEFT JOIN  bs_goods AS lg ON g.`goods_id` = lg.`goods_id` ' . $where . $limit;
        $arr = $this->groupbuyMod->querySql($sql);
        //组装数据
        $res = array();
        $res['data'] = $arr;
        $res['pagelink'] = $pagelink;
        $this->assign('res', $res);
        //语言包
        $this->load($this->shorthand, 'Promotion/Promotion');
        $this->assign('langdata', $this->langData);
        $langguages = $this->shorthand;
        $this->assign('langguages', $langguages);
        $this->display('groupbuy/goodslist.html');
    }

    /**
     * 商品详情页面
     */
    public function goodsInfo() {
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : '';  //团购活动id
        $fxCode = !empty($_REQUEST['fxCode']) ? $_REQUEST['fxCode'] : '';  //团购活动id
        if (empty($id)) {
            $this->display('error/404.html');
        }
        $sql = 'select b.`id`,b.`store_id`,b.`title`,b.`start_time`,b.`end_time`,b.`goods_id`,b.`goods_spec_key`,b.`key_name`,b.`group_goods_price`,b.`group_goods_num`,
                b.`buy_num`,b.`order_num`,b.`virtual_num`,b.`rebate`,lg.`original_img`,b.`goods_price`,b.`goods_name`,b.`views`,b.`is_end`,b.`add_time`,b.`mark` from  '
                . DB_PREFIX . 'goods_group_buy  AS b  LEFT JOIN  '
                . DB_PREFIX . 'store_goods AS g ON b.`goods_id` = g.id LEFT JOIN  '
                . DB_PREFIX . 'goods_lang AS l ON g.`goods_id` = l.`goods_id`  LEFT JOIN  '
                . DB_PREFIX . 'goods AS lg ON g.`goods_id` = lg.`goods_id`  where  b.id =  ' . $id;
        $groupinfo = $this->groupbuyMod->querySql($sql);
        //商品信息运费
        $storeGoodsMod = &m('areaGood');
        $sql2 = 'SELECT  g.id,g.goods_id,g.is_free_shipping,g.shipping_price,l.goods_name,l.goods_content   FROM  ' . DB_PREFIX . 'store_goods  as g
                 left join   bs_goods_lang AS l  on g.goods_id = l.`goods_id`   WHERE  l.`lang_id` = ' . $this->langid . '  and  g.id =' . $groupinfo[0]['goods_id'];
        $goodsinfo = $storeGoodsMod->querySql($sql2);

        //获取商品图片
        $goodsImgMod = &m('goodsImg');
        $sql3 = 'select  image_url,goods_id  from  ' . DB_PREFIX . 'goods_images  where goods_id =' . $goodsinfo[0]['goods_id'];
        $imgArr = $goodsImgMod->querySql($sql3);
        //商品规格
        $goodsSpecMod = &m('goodsSpec');
        $specItemLangMod = &m('goodsSpecItemLang');
        $goodsspec = $groupinfo[0]['goods_spec_key'];
        $specitems = array();

        if (!empty($goodsspec)) {
            $specArr = explode('_', $goodsspec);
            foreach ($specArr as $key => $val) {
                //
                $sqlspec = 'SELECT sp.`type_id`,sp.`id`,l.`spec_name`  FROM  ' . DB_PREFIX . 'goods_spec  AS sp
                            LEFT JOIN  ' . DB_PREFIX . 'goods_spec_item AS i ON sp.`id` = i.`spec_id`
                            LEFT JOIN  ' . DB_PREFIX . 'goods_spec_lang AS l ON sp.`id` = l.`spec_id`
                            WHERE i.`id` = ' . $val . ' AND l.`lang_id` = ' . $this->langid;
                $res = $goodsSpecMod->querySql($sqlspec);
                $specitems[$key] = $res[0];
                //
                $sqlitem = 'SELECT  item_id,item_name,lang_id  FROM  ' . DB_PREFIX . 'goods_spec_item_lang  WHERE item_id =' . $val . '  AND  lang_id = ' . $this->langid;
                $res2 = $specItemLangMod->querySql($sqlitem);
                $specitems[$key]['items'] = $res2[0];
            }
        }

        $this->dd($goodsspec);

        //商品简介
//        $goodscnt = $this->getStoreGoodsLang($goodsinfo[0]['id'], $this->langid);
//        $gcontent = $goodscnt['goods_content'];
//        if (empty($gcontent)) {
//            //获取原始的goods的商品内容
//            $goodsMod = &m('goods');
//            $sqlg = 'select  goods_content from  ' . DB_PREFIX . 'goods where goods_id =' . $goodsinfo[0]['goods_id'];
//            $resg = $goodsMod->querySql($sqlg);
//            $gcontent = $resg[0]['goods_content'];
//        }
        //商品属性
        $goodsAttrMod = &m('goodsAttri');
        $sqlattr = 'SELECT  al.`name`,ga.`attr_value`  FROM   ' . DB_PREFIX . 'goods_attr  AS ga
                    LEFT JOIN  ' . DB_PREFIX . 'goods_attr_lang al  ON  ga.`attr_id` = al.`a_id`
                    WHERE goods_id = ' . $goodsinfo[0]['goods_id'] . '  AND  al.`lang_id` = ' . $this->langid;
        $attrArr = $goodsAttrMod->querySql($sqlattr);
        //商品，综合评分
        $good_rank_sql = "select goods_rank , count(1) as good_num from bs_goods_comment  where goods_id ={$goodsinfo[0]['id']}  group BY goods_rank";
        $good_rank_sta = $this->goodsCommentMod->querySql($good_rank_sql);
        $good_all_num = " select count(1) as all_num from bs_goods_comment  where goods_id ={$goodsinfo[0]['id']}";
        $good_all_num = $this->goodsCommentMod->querySql($good_all_num);
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
        $this->assign('all_rate', $all_rate);
        //评论列表
        //1.统计多少页数
        $sqlt = 'select  count(*)  as total  from ' . DB_PREFIX . 'goods_comment
                     where  goods_id  = ' . $goodsinfo[0]['id'] . '  and  store_id = ' . $this->storeid;
        $totalD = $this->goodsCommentMod->querySql($sqlt);
        $total = $totalD[0]['total'];
        $totalpage = ceil($total / $this->commpagesize);
        if (empty($totalpage)) {
            $totalpage = 1;
        }
        $this->assign('commtotalpage', $totalpage);
        //2.获取第一页的信息
        $commlimit = '  limit 0,' . $this->commpagesize;
        $eva_sql = 'select  comment_id, username, goods_rank, add_time, content, img  from ' . DB_PREFIX . 'goods_comment
                    where goods_id  = ' . $goodsinfo[0]['id'] . '  and  store_id = ' . $this->storeid . '   order by comment_id  desc ' . $commlimit;
        $commlist = $this->goodsCommentMod->querySql($eva_sql);
        $new_list = array_map(function($vo) {
            $vo['img'] = explode(',', $vo['img']);
            return $vo;
        }, $commlist);
        $this->assign('list', $new_list); //评论
        $this->assign('attrArr', $attrArr);
        // $this->assign('gcontent', $gcontent);
        $this->assign('groupinfo', $groupinfo[0]);
        $this->assign('goodsinfo', $goodsinfo[0]);
        $this->assign('specitems', $specitems);
        $this->assign('imgArr', $imgArr);
        $this->assign('groupinfo', $groupinfo[0]);
        //语言包
        $this->load($this->shorthand, 'Promotion/Promotion');
        $this->assign('langdata', $this->langData);
        $this->assign('lang_id', $this->langid);
        $this->assign('fxCode', $fxCode);
        $this->display('groupbuy/detail.html');
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

    public function ajaxcheck() {
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : '';
        $group_id = !empty($_REQUEST['group_id']) ? $_REQUEST['group_id'] : '';
        $buy_num = !empty($_REQUEST['buy_num']) ? $_REQUEST['buy_num'] : '';
        $sql = 'select group_goods_num from  ' . DB_PREFIX . 'goods_group_buy  where id=' . $group_id;
        $data = $this->groupbuyMod->querySql($sql);
        if ($buy_num > $data[0]['group_goods_num']) {
            echo json_encode(array('status' => 0));
        } else {
            echo json_encode(array('status' => 1));
        }
    }

}
