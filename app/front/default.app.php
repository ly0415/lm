<?php

/**
 * 前台首页
 * @author wangh
 * @date 2017/07/19
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class DefaultApp extends BaseFrontApp {

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 即时聊天
     * @author peter
     * @date 2018-02-25
     */
    public function imIndex() {
        if ($_SESSION['userId']) {
            //加载商品信息
            $gid = $_REQUEST['gid'] ? $_REQUEST['gid'] : '';
            $kf_id = $_REQUEST['kf_id'] ? $_REQUEST['kf_id'] : '';
            $goodMod = &m('goods');
            $storeGoods = &m('areaGood');
            //加载语言包
            $this->load($this->shorthand, 'goods/goods');
            $this->assign('langdata', $this->langData);
            $info = $storeGoods->getLangInfo($gid, $this->langid, $this->storeid);
            $goods_info = $goodMod->getOne(array("cond" => "goods_id=" . $info['goods_id']));
            $info['original_img'] = $goods_info['original_img'];
            $this->assign("info", $info);
            //加载客服信息
            $fdMod = &m('imFd');
            $msgMod = &m('imMsg');
            $uid = $_SESSION['userId'];
            $this->assign("uid", $uid);
            $this->assign("kf_id", $kf_id);
            $msg_history = $msgMod->loadHistory($uid, $kf_id);
            $this->assign("msg_data", $msg_history);
            //
            $this->display('im/index.html');
        } else {
            $this->load($this->shorthand, 'user_login/user_login');
            $this->assign('langdata', $this->langData);
            $this->display('public/login.html');
        }
    }

    /**
     * 空操作
     * @author lvji
     * @date 2015-03-20
     */
    public function emptyOperate() {
        $info = array();
        $this->setData($info);
    }

    /**
     * 首页
     * @author wangh
     * @date 2017/08/22
     */
    public function index() {
        $lang = $_REQUEST['lang'];
        //获取一级分类
        $oneCategory = &m('goodsCategory');
        $ctglev1 = $oneCategory->getOneCategory();

        //获取第一级分类数据
        $threeChilds = $oneCategory->getRelationDatas();
        // 首页banner
        $banner = $this->getBanner(110000, $this->storeid);
        // 广告横幅
        $rowadv1 = $this->getBanner(110001, $this->storeid);
        $rowadv2 = $this->getBanner(110002, $this->storeid);
        //购物车
        //推荐商品
        $recomGoods = $this->recommendGoods($this->storeid);

        //限时打折
        $prom_arr = $this->getGoodsDiscount($this->storeid);
        //为你推荐
        $recommYou = $this->recommendForYou($this->storeid);

        //用户足迹
        //推荐文章
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang_id);
        $articles = $this->getArticles($this->storeid, $lang);

        $this->load($this->shorthand, 'default/index');
        $this->assign('prom_arr', $prom_arr);
        $this->assign('articles', $articles);
        $this->assign('rowadv1', $rowadv1[0]);
        $this->assign('rowadv2', $rowadv2[0]);
        $this->assign('user_id', $this->userId);
        $this->assign('banner', $banner);
        $this->assign('ctglev1', $ctglev1);
        $this->assign('recomGoods', $recomGoods);
        $this->assign('recommYou', $recommYou);
        $this->assign('langdata', $this->langData);
        $this->display('index.html');
    }

    public function register() {
        $this->display('public/register.html');
    }

    /**
     * 获取首页Banner
     * @author wangh
     * @date 2017/08/22
     */
    public function getBanner($positionName, $storeid) {
        $advMod = &m('adv');
        $where = '   where 1=1  and  p.`position_num`  = ' . $positionName . '  and  a.store_id=' . $storeid;
        $sql = 'SELECT   a.ad_code,a.goods_id,p.`position_id`,p.`position_num`,a.store_id,a.ad_name
                FROM  ' . DB_PREFIX . 'ad  AS a
                LEFT JOIN  ' . DB_PREFIX . 'ad_position  AS p  ON a.`ps_id` = p.`position_id` ' . $where;
        $res = $advMod->querySql($sql);
        return $res;
    }

    /**
     * 获取推荐的商品
     * @author wangh
     * @date 2017/08/22
     */
    public function recommendGoods($storeid) {
        $storeGoodsMod = &m('areaGood');
        $where = '  WHERE  s.is_recommend =1 AND s.mark =1 AND s.is_on_sale =1  AND s.store_id = ' . $storeid . ' AND l.`lang_id` =' . $this->langid;
        $sql = 'SELECT s.id,s.`goods_id`,s.`cat_id`,s.`store_id`,l.`goods_name`,l.`lang_id`,s.`shop_price`,s.`market_price`,s.`brand_id`,gl.`original_img`
                FROM  ' . DB_PREFIX . 'store_goods AS s LEFT JOIN  ' . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id` LEFT JOIN  ' . DB_PREFIX . 'goods AS gl ON s.`goods_id` = gl.`goods_id` ' . $where;
        $sql .= '  ORDER  BY  s.id    LIMIT 6';
        $arr = $storeGoodsMod->querySql($sql);

        foreach ($arr as $key => $val) {
            //店铺商品打折
            $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $this->storeid;
            $store_arr = $storeGoodsMod->querySql($store_sql);
            $arr[$key]['shop_price'] = number_format($val['shop_price'] * $store_arr[0]['store_discount'], 2);
//            //多语言版本信息
//            $gname = $this->getStoreGoodsLang($val['id'], $this->langid);
//            if ($gname) {
//                $arr[$key]['goods_name'] = $gname;
//            }
        }

        return $arr;
    }

    /**
     * 获取为你推荐的商品
     * @author wangh
     * @date 2017/08/22
     */
    public function recommendForYou($storeid) {
        $ctgMod = &m('goodsClass');
        $userId = $this->userId;
        $sql = "select  `id`   from " . DB_PREFIX . "goods_category ";
        $res = $ctgMod->querySql($sql);
        $cid = array();
        foreach ($res as $val) {
            $cid[] = $val['id'];
        }
        $cids = implode(',', $cid);
        //
        $storeGoodsMod = &m('areaGood');
        $where = '  WHERE   s.cat_id  in (' . $cids . ')  and  s.mark =1  AND  s.is_on_sale =1  AND s.store_id = ' . $storeid . ' AND l.`lang_id` =' . $this->langid;
        $sql2 = 'SELECT s.id,s.`goods_id`,s.`cat_id`,s.`store_id`,l.`goods_name`,l.`lang_id`,s.`shop_price`,s.`market_price`,s.`brand_id`,gl.`original_img`
                FROM  ' . DB_PREFIX . 'store_goods AS s LEFT JOIN  ' . DB_PREFIX . 'goods_lang AS l ON s.`goods_id` = l.`goods_id` LEFT JOIN  ' . DB_PREFIX . 'goods AS gl ON s.`goods_id` = gl.`goods_id` ' . $where;
        $sql2 .= '  ORDER  BY  s.goods_salenum  desc ,  s.id desc   LIMIT 12';
        $arr = $storeGoodsMod->querySql($sql2);

        foreach ($arr as $key => $val) {
            //店铺商品打折
            $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $this->storeid;
            $store_arr = $storeGoodsMod->querySql($store_sql);
            $arr[$key]['shop_price'] = number_format($val['shop_price'] * $store_arr[0]['store_discount'], 2);
            //为你推荐的收藏商品
            $sql_collection = 'select * from ' . DB_PREFIX . 'user_collection where user_id=' . $userId . ' and store_id=' . $storeid;
            $data_collection = $storeGoodsMod->querySql($sql_collection);
            foreach ($data_collection as &$collertion) {
                if ($collertion['store_good_id'] == $val['id']) {
                    $arr[$key]['type'] = 1;
                }
            }
        }
        return $arr;
    }

    /*
     * 获取四种优惠
     * @author lee
     * @date 2017-11-29 14:14:51
     * @param storeid 区域ID
     */

    public function getGoodsDiscount($storeid) {
        $lang_id = $this->langid;
        //取四种优惠商品
        $now = time();
        $combinedMod = &m('combinedSale'); //组合销售
        $promSaleMod = &m('goodProm'); //商品促销
        $groupMod = &m('groupbuy'); //团购
        $skillMod = &m('spikeActivity'); //秒杀

        $comb_field = "id as prom_id,main_img as goods_img,name as prom_name,main_name as goods_name,main_id";
        $comb_arr = $combinedMod->getOne(array("cond" => "store_id=" . $storeid . " and  status=1", "fields" => $comb_field));
        if ($comb_arr) {
            $com_lang = $this->getGoodsLang($comb_arr['main_id'], $lang_id);
            if ($com_lang) {
                $comb_arr['goods_name'] = $com_lang['goods_name'];
            }
            $comb_arr['url'] = "?app=combined&act=c_index&storeid={$storeid}&lang={$lang_id}";
        }

        $prom_field = "s.id as prom_id,sgl.original_img as goods_img,s.prom_name,g.goods_name,g.discount_rate as prom_rate,s.end_time,g.goods_id,goods_price as o_price,discount_price as price,g.goods_key";
        $prom_sql = "select " . $prom_field . " from "
                . DB_PREFIX . "promotion_sale as s left join "
                . DB_PREFIX . "promotion_goods as g on s.id=g.prom_id left join  "
                . DB_PREFIX . "store_goods as sg on g.goods_id = sg.id left join  "
                . DB_PREFIX . "goods as sgl on sg.goods_id = sgl.goods_id  where s.store_id=" . $storeid . " and s.status=2 and sg.mark=1 and sg.is_on_sale=1 and s.mark=1 and s.start_time<=" . $now . " and s.end_time>=" . $now;
        $prom_arr = $promSaleMod->querySql($prom_sql);


        if ($prom_arr) {
            $prom_lang = $this->getGoodsLang($prom_arr[0]['goods_id'], $lang_id);
            if ($prom_lang) {
                $prom_arr[0]['goods_name'] = $prom_lang['goods_name'];
            }
            $prom_arr[0]['url'] = "?app=goods&act=goodInfo&storeid={$storeid}&lang={$lang_id}&auxiliary=0&source=3&cid=" . $prom_arr[0]['prom_id'] . "&gid=" . $prom_arr[0]['goods_id'] . "&key=" . $prom_arr[0]['goods_key'];
        }

//        $group_field = "id as prom_id,original_img as goods_img,goods_name,rebate as prom_rate,end_time,goods_id,goods_price as o_price,group_goods_price as price";
//        $group_arr = $groupMod->getOne(array("cond" => "store_id=" . $storeid . " and start_time<=" . $now . " and end_time>=" . $now . " and is_end=1 and mark=1", "fields" => $group_field));
//       Array
//(
//    [prom_id] => 28
//    [goods_img] => upload/images/goods/2018053196/1527746641q.png
//    [goods_name] => 鲜奶什么都有
//    [prom_rate] => 0.00
//    [end_time] => 1530359700
//    [goods_id] => 4106
//    [o_price] => 30.00
//    [price] => 118.00
//)
        $sqle = "SELECT  c.id as prom_id,sgl.original_img as goods_img,c.goods_name as prom_name,c.rebate as prom_rate,c.end_time,c.goods_id,c.goods_price as o_price,c.group_goods_price as price  FROM  "
                . DB_PREFIX . "goods_group_buy  as c left join  "
                . DB_PREFIX . "store_goods as sg on c.goods_id = sg.id left join  "
                . DB_PREFIX . "goods as sgl on sg.goods_id = sgl.goods_id  where c.store_id=" . $storeid . " and c.start_time<=" . $now . " and c.end_time>=" . $now . " and c.is_end=1 and c.mark=1 and sg.mark=1 and sg.is_on_sale=1";
        $group_arr = $groupMod->querySql($sqle);
        if ($group_arr) {
            $group_lang = $this->getGoodsLang($group_arr[0]['goods_id'], $lang_id);
            if ($group_lang) {
                $group_arr[0]['goods_name'] = $group_lang['goods_name'];
            }
            $group_arr[0]['url'] = "?app=goods&act=goodInfo&storeid={$storeid}&lang={$lang_id}&auxiliary=0&source=2&cid=" . $group_arr[0]['prom_id'] . "&gid=" . $group_arr[0]['goods_id'];
        }
//        $skill_field = "id as prom_id,name as prom_name,goods_name,discount as prom_rate,end_time,goods_img,store_goods_id,price ,o_price";
//        $skill_arr = $skillMod->getOne(array("cond" => "store_id=" . $storeid . " and start_time<=" . $now . " and end_time>=" . $now, "fields" => $skill_field));
        $skill_field = "SELECT c.id as prom_id,c.name as prom_name,c.goods_name,c.discount as prom_rate,c.end_time,sgl.original_img as goods_img,c.store_goods_id,c.price ,c.o_price FROM  "
                . DB_PREFIX . "spike_activity  as c left join  "
                . DB_PREFIX . "store_goods as sg on c.store_goods_id = sg.id left join  "
                . DB_PREFIX . "goods as sgl on sg.goods_id = sgl.goods_id where c.store_id=" . $storeid . " and c.start_time<=" . $now . " and sg.mark=1 and sg.is_on_sale=1 and c.end_time>=" . $now;
        $skill_arr = $skillMod->querySql($skill_field);
        if ($skill_arr) {
            $skill_lang = $this->getGoodsLang($skill_arr[0]['store_goods_id'], $lang_id);
            if ($skill_lang) {
                $skill_arr[0]['goods_name'] = $skill_lang['goods_name'];
            }
            $skill_arr[0]['url'] = "?app=goods&act=goodInfo&storeid={$storeid}&lang={$lang_id}&auxiliary=0&source=1&cid=" . $skill_arr[0]['prom_id'] . "&gid=" . $skill_arr[0]['store_goods_id'];
        }
        $arr[] = $prom_arr[0];
        $arr[] = $group_arr[0];
        /* $arr[] = $comb_arr; */
        $arr[] = $skill_arr[0];
        return $arr;
    }

    /**
     * 获取商品的多语言信息
     * @param $goodsId
     * @param $langid
     * @return mixed
     */
    public function getStoreGoodsLang($goodsId, $langid) {
        $storeGLMod = &m('storeGoodsLang');
        $sql = 'SELECT  goods_name  FROM  ' . DB_PREFIX . 'store_goods_lang   WHERE  store_good_id = ' . $goodsId . ' AND   lang_id =' . $langid;
        $res = $storeGLMod->querySql($sql);
        return $res[0]['goods_name'];
    }

    /**
     * 获取推荐的文章
     * @author wangh
     * @date 2017/08/22
     */
    public function getArticles($storeid, $lang) {
        if (empty($lang)) {
            $lang = $this->langid;
        }
        $artCtgMod = &m('articleCate');
        $sql = 'SELECT   al.title,a.image,a.add_time,a.id,al.body,al.brif,al.lang_id,a.store_id FROM  ' . DB_PREFIX . 'article  AS a
                LEFT JOIN   ' . DB_PREFIX . 'article_lang as al ON a.id=al.article_id  where al.lang_id = ' . $lang . ' AND a.store_id = ' . $storeid . ' AND a.isrecom = 1';
        $sql .= '   order  by  a.add_time  desc limit 5';
        $data = $artCtgMod->querySql($sql);

        return $data;
    }

//    /**
//     * 获取国家下的站点
//     */
//    public function storePopup() {
//        $storeMod = &m('store');
//        $cateid = !empty($_REQUEST['cateid']) ? $_REQUEST['cateid'] : $this->mrstorecate;  //所选的站点国家
//        $sql = 'SELECT s.id,l.store_name,s.currency_id,s.lang_id  FROM  ' . DB_PREFIX . 'store AS s
//                 LEFT JOIN  ' . DB_PREFIX . 'store_lang AS l ON s.`id` = l.`store_id` 
//                  WHERE l.lang_id =' . $this->langid . "  and s.is_open =1  and s.store_cate_id =" . $cateid;
////        $sql = 'SELECT  id,store_name,lang_id,currency_id    FROM  ' . DB_PREFIX . 'store  WHERE  is_open =1 AND   store_cate_id = ' . $cateid;
//        $res = $storeMod->querySql($sql);
//        $this->assign('res', $res);
//        $this->assign('cateid', $cateid);
//        $this->display("public/store.html");
//    }
    /**
     * 获取国家下的站点
     */
    public function ajaxCateStores() {
        $storeMod = &m('store');
        $cateid = !empty($_REQUEST['cateid']) ? $_REQUEST['cateid'] : $this->mrstorecate;  //所选的站点国家
        $sql = 'SELECT s.id,l.store_name,s.currency_id,s.lang_id  FROM  ' . DB_PREFIX . 'store AS s
                 LEFT JOIN  ' . DB_PREFIX . 'store_lang AS l ON s.`id` = l.`store_id` 
                  WHERE l.lang_id =' . $this->langid . "  and s.is_open =1  and s.store_cate_id =" . $cateid;
//        $sql = 'SELECT  id,store_name,lang_id,currency_id    FROM  ' . DB_PREFIX . 'store  WHERE  is_open =1 AND   store_cate_id = ' . $cateid;
        $res = $storeMod->querySql($sql);
        echo json_encode($res);
    }

    /**
     * 站点切换的ajax交互
     * @author wangh
     * @date 2017/08/22
     */
//    public function ajaxStoreUrl() {
//        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;  //所选的站点id
//        $storeData = $this->getStoreData($storeid);
//        $data = json_encode($storeData);
//        echo $data;
//        exit;
//    }

    /**
     * 语言切换的ajax交互
     * @author wangh
     * @date 2017/08/22
     */
    public function ajaxLangUrl() {
        $langid = !empty($_REQUEST['langid']) ? $_REQUEST['langid'] : $this->mrlangid;
        $cururl = $_REQUEST['cururl'];  //当前页面的url
        //
        if (empty($cururl)) {
            $cururl = '?storeid=' . $this->mrstoreid . '&lang=' . $this->mrlangid;
        }
        if ($cururl == '?app=default&act=index') {
            $cururl = '?storeid=' . $this->mrstoreid . '&lang=' . $this->mrlangid;
        }
        $arr = explode("&", $cururl);
        foreach ($arr as $key => $val) {
            if (strpos($val, 'lang') !== false) {
                $arrlang = explode('=', $val);
                array_pop($arrlang);
                array_push($arrlang, $langid);
                $arr[$key] = implode('=', $arrlang);
            }
        }
        $reurl = implode('&', $arr);
        $urljson = json_encode(array('reurl' => $reurl));
        echo $urljson;
    }

    /**
     * 测试项目接口
     * @author lvji
     * @date 2016-09-12
     */
    public function test() {
        $info = array();
        $info['msg'] = '测试接口';
        $data = $this->setData($info);
        echo json_encode($data);
    }

    /**
     * 创建验证码
     */
    public function createCode() {
        import('captcha.lib');
        $captchaObj = new Captcha();
    }

    /**
     * 退出系统
     * @author wangh
     * @date 2217/07/19
     */
    public function logout() {
        // 删除表中session_id
//        $frontAccountMod = &m('frontAccountSession');
//        $frontAccountMod ->doDelete(array('`user_id`' => $this->userId));
        //第一步：删除服务器端
        unset($_SESSION['userId']);
        //第二步：删除$_SESSION全部变量数组
        $_SESSION = array();
        session_destroy();
        //第三步：删除实际的session
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600);
        }
        $info['url'] = "index.php?app=default&act=index";
        $this->setData($info, $status = '1', $message = '退出成功！');
    }

    /**
     * 获取版本号
     * @author lvji
     * @date 2015-03-20
     */
    public function fetchVersion() {
        $info = array();
        $info['version'] = APPVERSION;
        $this->setData($info);
    }

    /**
     * 生成路径
     * @author WQQ 2017-02-16 14:53:25
     * @param $path
     */
    public function MkFolder($path) {
        if (!is_readable($path)) {
            $this->MkFolder(dirname($path));
            if (!is_file($path))
                mkdir($path, 0777);
        }
    }

    /**
     * 二维码
     * @author lvji
     * @date 2015-03-10
     */
    public function createQR($orderBn) {
        if (!$orderBn) {
            $this->setData($info = array(), $status = 'error', $message = 'Lack of order number');
        }
        include_once ROOT_PATH . "/includes/classes/class.qrcode.php";
        $value = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER["PHP_SELF"] . "?app=print&act=index&orderBn=" . $orderBn; //二维码内容
        if ($_SERVER["PHP_SELF"]) {
            $_SERVER["PHP_SELF"] = str_replace("/admin.php", "", $_SERVER["PHP_SELF"]);
        }
        $qrUrl = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER["PHP_SELF"] . "/upload/qr/" . $orderBn . ".png";
        $out_file = ROOT_PATH . "/upload/qr/" . $orderBn . ".png";
        $errorCorrectionLevel = 'L'; //容错级别
        $matrixPointSize = 6; //生成图片大小
        $path = ROOT_PATH . "/upload/qr/";
        $this->MkFolder($path);
        //生成二维码图片
        $QRcode = QRcode::png($value, $out_file, $errorCorrectionLevel, $matrixPointSize, 2);
        return $qrUrl;
    }

    public function check_phpinfo() {
        phpinfo();
    }

}

?>