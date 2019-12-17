<?php

/**
 * 前台首页
 * @author lvji
 * @date 2016-08-01
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class BaseFrontApp extends BaseApp {

    public $userId, $userName;
    public $mrstoreid, $mrlangid, $mrstorecate; //默认站点
    public $storeid, $langid, $shorthand, $countryId;
    public $langData = array();   // 语言包数据
    public $symbol, $syshort;  //币种符号
    public $pagesize = 15; //每页显示多少个商品
    public $commpagesize = 5; // 商品评论的每页的显示条数
    public $location; //定位
    public $cart_count; //定位
    public $session_id;
    public $checkInfo;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->userId = $_SESSION['userId'];
        $this->userName = $_SESSION['userName'];

        if( $_SERVER['REQUEST_SCHEME'] == 'https' ){
            $this->assign('SITE_URL', SITE_URL_SSL);
            $this->assign('STATIC_URL', STATIC_URL_SSL);
        } else {
            $this->assign('SITE_URL', SITE_URL);
            $this->assign('STATIC_URL', STATIC_URL);
        }

        $this->assign('userId', $this->userId);
        $this->assign('userName', $this->userName);

        //在没选择站点的情况下 根据 国家来获取 默认的站点
        if (empty($this->mrstoreid)) {

            if (!empty($ipInfo)) {
                $country = $ipInfo['country'];
                $city = $ipInfo['city'];
                $this->checkInfo = $this->checkSite($city);
            } else {
                $country = '中国';
            }
            $this->location = $country;
            //获取国家分类id（有默认的站点的国家），如果没定位到则获取有总代理的第一个国家
            $storecate = $this->getStorecateid($country);
            $storecateid = $storecate['id'];

            //获取总代理的站点为 默认站点 ，如果都没有则获取第一个站点的数据
            if (empty($city) || $this->checkInfo == false) {
                $mrdata = $this->getMrStore($storecateid);
                $this->mrstoreid = $mrdata['id'];
                $this->mrlangid = $mrdata['lang_id'];
            } else {
                $mrdata = $this->checkInfo;
                $this->mrstoreid = $mrdata['id'];
                $this->mrlangid = $mrdata['lang_id'];
            }
        }

        //接受前台数据
        $this->storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;   //所选的站点id
        $this->langid = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->mrlangid;  //所选站点的语言
        //站点国家分类
        $storeCate = $this->getStoreCate();
        foreach ($storeCate as $k => $v) {
            //当前所选的同类站点
            if ($_SESSION['userId']) {
                $user_id = $_SESSION['userId'];
                $userMod = &m('user');
                $sql = 'SELECT *  FROM  ' . DB_PREFIX . 'user WHERE  id  =' . $user_id; //odm_members
                $datas = $userMod->querySql($sql);
                if ($datas[0]['odm_members'] == 0) {
                    $where = ' and s.store_type <4 ';
                } else {
                    $where = '';
                }
            } else {
                $where = ' and store_type <4 ';
            }
            $storeMod = &m('store');
            $sql = 'SELECT s.id,l.store_name,s.store_cate_id,s.lang_id,l.distinguish  FROM  ' . DB_PREFIX . 'store AS s
                 LEFT JOIN  ' . DB_PREFIX . 'store_lang AS l ON s.`id` = l.`store_id`
                  WHERE s.is_open = 1  and  store_cate_id =' . $v['id'] . ' and  l.lang_id =' . $this->langid . $where;
            $data = $storeMod->querySql($sql);
            $storeCate[$k]['store'] = $data;
        }
        $this->assign('storeCate', $storeCate);
        //当前所选的国家
        $curstoreid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->mrstoreid;
        $curCountry = $this->getCurCountry($curstoreid);
        $this->countryId = $curCountry['cid'];
        $this->assign('curCountry', $curCountry);
        $this->assign('cid', $curCountry['cid']);
        //当前所选的同类站点
        $curstores = $this->getCurStores($curCountry['cid']);
        $this->assign('curstores', $curstores);
        //当前站点信息
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $curStoreInfo = $this->getCurStoreInfo($curstoreid, $auxiliary);
        $this->assign('curStoreInfo', $curStoreInfo);
        //语言数据
        $langs = $this->getLangs();
        $this->assign('langs', $langs);
        //当前语言信息
        $langinfo = $this->getShorthand($this->langid);
        $this->shorthand = $langinfo['shorthand'];
        $this->assign('langinfo', $langinfo);
        // 获取币种符号
        $syData = $this->getStoreSymbol($this->storeid);
        $this->symbol = $syData['symbol'];   // 符号
        $this->syshort = $syData['short'];  //简称
        //顶部分类导航
         //顶部分类导航
        $oneCategory = &m('goodsCategory');
        $ctgtree = $oneCategory->getRelationDatas();
        //业务分类购物
        $frontroomtype = $this->getRoomType($this->langid);
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang_id);
        //语言包
        $this->load($this->shorthand, 'public/daohang');
        //搜索关键字
        $searchD = !empty($_REQUEST['gname']) ? $_REQUEST['gname'] : '';

        // 执行检测商品下架或者删除的商品
        $this->checkCart();
        //网站配置
        $webconf = $this->webconf();
        $this->assign('webconf', $webconf);

        $this->assign('searchD', $searchD);
        $this->assign('symbol', $this->symbol); //币种符号
        $this->assign('storeid', $this->storeid);
        $this->assign('langid', $this->langid);
        $this->assign('frontroomtype', $frontroomtype);
        $this->assign('ctgtree', $ctgtree);
        $this->cart_count = $this->getCarNum();
        $this->assign('cart_count', $this->cart_count);
        $this->assign('cart_total', $this->getCarTotal());

        //加载当前控制器方法所对应的Css文件
        $this->viewCss();

        //加载当前控制器方法所对应的Js文件
        $this->viewJs();
    }

    /**
     *  检测当前城市是否有站点
     */
    public function checkSite($city) {
        $storeMod = &m('store');
        $sql = 'SELECT s.id,l.store_name,s.currency_id,s.lang_id  FROM  ' . DB_PREFIX . 'store AS s
                 LEFT JOIN  ' . DB_PREFIX . 'store_lang AS l ON s.`id` = l.`store_id` 
                  WHERE l.lang_id =' . $this->langid . " and l.store_name like '%" . $city . "%'";
//        $sql = "select  id,store_name,lang_id,currency_id  from  " . DB_PREFIX . "store where store_name like '%" . $city . "%'";
        $data = $storeMod->querySql($sql);
        if (empty($data)) {
            return false;
        } else {
            return $data[0];
        }
    }

    /**
     * 检测用户是否操作是否过期
     */
    public function checkUserStatus() {
        $frontAccountMod = &m('frontAccountSession');
        if ($this->userId) {
            $httpHost = $_SERVER['HTTP_HOST'];
            $url = 'http://' . $httpHost . "/bspm711/index.php?app=user&act=login&storeid={$this->storeid}&lang={$this->langid}";
            $accountSessionInfo = $frontAccountMod->getOne(array('cond' => "`user_id` = {$this->userId}", 'fields' => 'login_time,session_id,id'));
            if ($accountSessionInfo['session_id']) {
                if (session_id() != $accountSessionInfo['session_id']) {
                    session_destroy();
                    if (isset($_COOKIE[session_name()])) {
                        setcookie(session_name(), '', time() - 3600);
                    }
                    header("Location: {$url}");
                }
                if (time() - $accountSessionInfo['login_time'] > 600) {
//                    if (session_id() != $accountSessionInfo['session_id']) {
                    session_destroy();
                    if (isset($_COOKIE[session_name()])) {
                        setcookie(session_name(), '', time() - 3600);
                    }
                    header("Location: {$url}");
//                    }
                } else {
                    $sessEditData = array(
                        'login_time' => time()
                    );
                    $id = $frontAccountMod->doEdit($accountSessionInfo['id'], $sessEditData);
                }
            }
        }
    }

    /**
     * 检测用户过期的方法
     */
    public function checkExpire() {
        if ($this->userId) {
            // 删除表中session_id
            $frontAccountMod = &m('frontAccountSession');
            $frontAccountMod->doDelete(array('`user_id`' => $this->userId));
            unset($_SESSION['userId']);
            //第二步：删除$_SESSION全部变量数组
            $_SESSION = array();
            session_destroy();
            //第三步：删除实际的session
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 3600);
            }
        }
    }

    /**
     * 网站配置
     */
    public function webconf() {
        $configMod = &m('config');
        $sql = 'select * from bs_config  where  inc_type = "shop_info" ';
        $data = $configMod->querySql($sql);
        $res = array();
        foreach ($data as $key => $val) {
            $res[$val['name']] = $val;
        }
        return $res;
    }

    /**
     * 检测购物车里商品是否下架或者删除
     * @author wanyan
     * @date 2017-11-09
     */
    public function checkCart() {
        $cartMod = &m('cart');
        $sql = "select * from " . DB_PREFIX . "cart where `user_id` = '{$this->userId}' and `store_id` = $this->storeid";
        $rsSql = $cartMod->querySql($sql);
        foreach ($rsSql as $k1 => $v1) {
            $status = $this->checkOnSale($v1['goods_id']); // 下架整个商品 不区分下架某种规格的商品
            if ($status == 2) {
                $cartMod->doDelete(array('cond' => "`goods_id`='{$v1['goods_id']}'"));
            }
            $mark = $this->checkDelete($v1['goods_id']);
            if ($mark == 0) {
                $cartMod->doDelete(array('cond' => "`goods_id` = {$v1['goods_id']}"));
            }
        }
    }

    /**
     * 获取省市区的地址
     * @author wanyan
     * @date 2017-1-17
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
     * 获取当前商品是否下架
     * @author wanyan
     * @date 2017-11-09
     */
    public function checkOnSale($goods_id) {
        $storeGoodsMod = &m('storeGoods');
        $query = array(
            'cond' => "`id`='{$goods_id}'",
            'fields' => "is_on_sale"
        );
        $rs = $storeGoodsMod->getOne($query);
        return $rs['is_on_sale'];
    }

    /**
     * 获取当前商品是否删除
     * @author wanyan
     * @date 2017-11-09
     */
    public function checkDelete($goods_id) {
        $storeGoodsMod = &m('storeGoods');
        $query = array(
            'cond' => "`id`='{$goods_id}'",
            'fields' => "mark"
        );
        $rs = $storeGoodsMod->getOne($query);
        return $rs['mark'];
    }

    /*     * 所选的国家的信息
     * @param $storeid
     */

    public function getCurCountry($storeid) {
        $storeMod = &m('store');
        $sql = 'SELECT s.id,c.`id` AS cid,c.`cate_name`,l.cate_name as lcatename  FROM  ' . DB_PREFIX . 'store AS s
                 LEFT JOIN  ' . DB_PREFIX . 'store_cate AS c ON s.`store_cate_id` = c.`id`  left join bs_store_cate_lang  as l on c.id=l.cate_id
                  WHERE s.id =' . $storeid . '  and  l.lang_id =' . $this->langid;
        $data = $storeMod->querySql($sql);
        return $data[0];
    }

    public function getCurStores($cateid) {
        if ($_SESSION['userId']) {
            $user_id = $_SESSION['userId'];
            $userMod = &m('user');
            $sql = 'SELECT *  FROM  ' . DB_PREFIX . 'user WHERE  id  =' . $user_id; //odm_members
            $datas = $userMod->querySql($sql);
            if ($datas[0]['odm_members'] == 0) {
                $where = ' and s.store_type <4 ';
            } else {
                $where = '';
            }
        } else {
            $where = ' and s.store_type <4 ';
        }
        $storeMod = &m('store');
        $sql = 'SELECT s.id,l.store_name,s.store_cate_id,s.lang_id,l.id as lid,s.store_start_time,s.store_end_time,s.store_notice,s.store_mobile  FROM  ' . DB_PREFIX . 'store AS s
                 LEFT JOIN  ' . DB_PREFIX . 'store_lang AS l ON s.`id` = l.`store_id`
                  WHERE s.is_open = 1  and  store_cate_id =' . $cateid . ' and  l.lang_id =' . $this->langid . $where;
//        $sql = 'SELECT  id,store_name,store_cate_id,lang_id   FROM  ' . DB_PREFIX . 'store WHERE  is_open = 1 and store_cate_id =' . $cateid;
        $data = $storeMod->querySql($sql);
        return $data;
    }

    public function getCurStoreInfo($storeid, $auxiliary = 0) {
        $storeMod = &m('store');
        $sql = 'SELECT s.id,l.store_name,s.store_cate_id,s.lang_id,s.store_start_time,s.store_end_time,s.store_notice,s.store_mobile  FROM  ' . DB_PREFIX . 'store AS s
                 LEFT JOIN  ' . DB_PREFIX . 'store_lang AS l ON s.`id` = l.`store_id` and l.distinguish=' . $auxiliary . '  
                  WHERE  s.id =' . $storeid . ' and  l.lang_id =' . $this->langid;
//        $sql = 'SELECT  id,store_name,store_cate_id,lang_id   FROM  ' . DB_PREFIX . 'store WHERE  id =' . $storeid;
        $data = $storeMod->querySql($sql);
        return $data[0];
    }

    /**
     * 获取总代理的数据
     * @param $cateid
     */
    public function getMrStore($cateid) {
        $storeMod = &m('store');
        $sql = 'select  id,store_name,lang_id,currency_id  from  ' . DB_PREFIX . 'store where store_type  = 1 and store_cate_id =' . $cateid;
        $data = $storeMod->querySql($sql);
        if (empty($data)) {
            $stores = $this->getStores();
            $firstItem = array_shift($stores);
            return $firstItem;
        } else {
            return $data[0];
        }
    }

    /**
     * 获取站点国家的分类
     * @param $country
     */
    public function getStorecateid($country) {
        $storeCateMod = &m('storeCate');
        $sql = ' SELECT c.id,l.`cate_name`,l.`lang_id` FROM bs_store_cate AS c LEFT JOIN bs_store_cate_lang AS l ON c.id = l.`cate_id`
                 WHERE l.`lang_id` = 29  and  c.is_open = 1 AND l.`cate_name` = "' . $country . '" order by c.id';
        $data = $storeCateMod->querySql($sql);
        if (empty($data)) {
            $storeCate = $this->getStoreCate1();  //取有总代理的国家分类
            $firstcate = array_shift($storeCate);
            return $firstcate;
        } else {
            return $data[0];
        }
    }

    /**
     * 获取三个客服信息
     * @author wanyan
     * @date 2018/1/2
     */
    public function getKf() {
        $kfMod = &m('kf');
        $rs = $kfMod->getData(array('cond' => " 1=1 limit 0,3", 'fields' => "kf_name,kf_QQ"), true);
        return $rs;
    }

    /**
     * 获取当前用户购物车商品数量
     * @author wanyan
     * @date 2017/10/18
     */
    public function getCarNum() {
        $cartMod = &m('cart');
        $sql = "select sum(goods_num) as count from " . DB_PREFIX . "cart where `user_id` = '{$this->userId}' and `store_id` = $this->storeid";
        $countInfo = $cartMod->querySql($sql);
        if ($countInfo[0]['count']) {
            $total = $countInfo[0]['count'];
        } else {
            $total = 0;
        }
        return $total;
    }

    /**
     * 获取当前用户购物车商品价格综合
     * @author wanyan
     * @date 2017/10/18
     */
    public function getCarTotal() {
        $cartMod = &m('cart');
        $sql = "select sum(goods_price*goods_num) as total from " . DB_PREFIX . "cart where `user_id` = '{$this->userId}' and `store_id` = $this->storeid";
        $countInfo = $cartMod->querySql($sql);
        if (empty($countInfo[0]['total'])) {
            return 0;
        }
        return $countInfo[0]['total'];
    }

    /**
     * 获取商品的总价
     * @author wanyan
     * @date 2018-1-17
     */
    public function getGoodTotal($item_ids) {
        $cartMod = &m('cart');
        $sql = "select sum(goods_price * goods_num)  as total from " . DB_PREFIX . "cart where `id` in ({$item_ids})";
        $rs = $cartMod->querySql($sql);
        return $rs[0]['total'];
    }

    /**
     * 获取当前站点所属币种
     * @author wanyan
     * @date 2017/10/17
     */
    public function getStoreCurrency() {
        $storeMod = &m('store');
        $sql = "select c.short,c.symbol from " . DB_PREFIX . "store  as s left join  " . DB_PREFIX . "currency as c on s.currency_id = c.id where s.id =$this->storeid";
        $rs = $storeMod->querySql($sql);
        if (empty($rs)) {
            return array();
        } else {
            return $rs[0];
        }
    }

    public function getStoreSymbol($storeid) {
        $storeMod = &m('store');
        $sql = 'SELECT  c.`symbol`,c.`short`   FROM    ' . DB_PREFIX . 'store AS s LEFT JOIN  ' . DB_PREFIX . 'currency AS c ON s.`currency_id` = c.`id`  WHERE  s.`id` = ' . $storeid;
        $res = $storeMod->querySql($sql);
        return $res[0];
    }

    /**
     * 有总代理的站点国家数据
     * 获取站点国家的数据
     */
    public function getStoreCate1() {
        $storeCateMod = &m('storeCate');

        $sql = 'SELECT c.id,c.`lang_id` FROM  bs_store  AS s LEFT JOIN  bs_store_cate AS c ON s.`store_cate_id`  = c.`id`
                WHERE s.`store_type` = 1 AND c.`is_open` = 1  order by c.id';

        $res = $storeCateMod->querySql($sql);
        return $res;
    }

    /**
     * 获取站点国家的数据
     */
    public function getStoreCate() {
        $storeCateMod = &m('storeCate');
        $sql = 'SELECT  c.id,l.cate_name as lcatename  FROM  ' . DB_PREFIX . 'store_cate  as c
                 left join  ' . DB_PREFIX . 'store_cate_lang as l on  c.id = l.cate_id
                  LEFT  JOIN  bs_store AS s ON c.id = s.`store_cate_id`
                 WHERE c.is_open = 1  and  l.lang_id =' . $this->langid . '   AND s.`store_type` = 1  order by c.id';
        $res = $storeCateMod->querySql($sql);
        return $res;
    }

    /**
     * 站点
     * @author wangh
     * @date 2017/09/14
     */
    public function getStores() {
        $storeMod = &m('store');
        $sql = 'select  id,store_name,lang_id,currency_id  from ' . DB_PREFIX . 'store  WHERE  is_open = 1  order by id asc ';
        $data = $storeMod->querySql($sql);
        return $data;
    }

    /**
     * 语言
     * @author wangh
     * @date 2017/09/14
     */
    public function getLangs() {
        $languageMod = &m('language');
        $sql = 'select  *  from ' . DB_PREFIX . 'language where enable=1';
        $data = $languageMod->querySql($sql);
        return $data;
    }

    /**
     * 语言的简写
     * @author wangh
     * @date 2017/09/14
     */
    public function getShorthand($langid) {
        $languageMod = &m('language');
        $sql = 'select  id,name,shorthand,logo  from ' . DB_PREFIX . 'language  where id=' . $langid;
        $data = $languageMod->querySql($sql);
        return $data[0];
    }

    /**
     * 获取站点的数据
     * @author wangh
     * @date 2017/09/14
     */
    public function getStoreData($storeid) {
        $storeMod = &m('store');
        $sql = 'select  id,store_name,lang_id  from ' . DB_PREFIX . 'store  WHERE  id =  ' . $storeid;
        $data = $storeMod->querySql($sql);
        return $data[0];
    }

    public function getChilds($pid, $langid) {
        $ctgMod = &m('goodsClass');
        if (!empty($langid)) {
            $and = '    AND  l.`lang_id`  = ' . $langid;
        } else {
            $and = '    AND  l.`lang_id`  = ' . $this->mrlangid;
        }

        $sql = 'SELECT c.id,c.`parent_id`,c.`parent_id_path`,l.`category_name`,l.`lang_id`
                FROM  ' . DB_PREFIX . 'goods_category  AS c  LEFT JOIN  ' . DB_PREFIX . 'goods_category_lang AS l  ON c.id=l.`category_id`
                WHERE   c.parent_id = ' . $pid . $and . ' ORDER BY  c.sort_order';

        $data = $ctgMod->querySql($sql);
        return $data;
    }

    /**
     * 获取业务分类
     * @author wangh
     * @date 2017/08/22
     */
    public function getRoomType($langid) {
        $roomTypeMod = &m('roomType');
        if (!empty($langid)) {
            $where = '    where  t.superior_id=0 and l.`lang_id`  = ' . $langid;
        } else {
            $where = '    where  t.superior_id=0 and l.`lang_id`  = ' . $this->mrlangid;
        }
        $sql = 'SELECT  t.`id`,l.`type_name`,t.sort    FROM  ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN   ' . DB_PREFIX . 'room_type_lang AS l  ON t.`id` = l.`type_id`  ' . $where . '   ORDER BY  t.`sort`  LIMIT  10  ';
        $data = $roomTypeMod->querySql($sql);
        foreach ($data as $k => $v) {
            if (!empty($langid)) {
                $where = '    where  t.superior_id=' . $v['id'] . ' and l.`lang_id`  = ' . $langid;
            } else {
                $where = '    where  t.superior_id=' . $v['id'] . ' and l.`lang_id`  = ' . $this->mrlangid;
            }
            $sql = 'SELECT  t.`id`,l.`type_name` ,t.`room_img`  as rimg ,t.`room_adv_img`  as advimg ,t.`sort`    FROM  ' . DB_PREFIX . 'room_type AS t
                 LEFT JOIN   ' . DB_PREFIX . 'room_type_lang AS l  ON t.`id` = l.`type_id`  ' . $where . '   ORDER BY  t.`sort`  LIMIT  10  ';
            $res = $roomTypeMod->querySql($sql);
            $data[$k]['secondLevel'] = $res;
        }
        return $data;
    }

    /**
     * 数据封装
     * @author lvji
     * @param $status 表示返回数据状态
     * @param $message 对返回状态说明
     * @param $info 返回数据信息
     * @date 2015-03-10
     */
    public function setData($info = array(), $status = 'success', $message = 'ok') {
        $data = array(
            'status' => $status,
            'message' => $message,
            'info' => $info,
        );
        echo json_encode($data);
        exit();
    }

    /**
     * 获取当前页面的url
     * @return string
     */
    public function curPageURL() {
        $pageURL = 'http';
        if ($_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://";

        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }

    /**
     * wangh
     * 加载语言包文件
     * @param $langid
     * @param $filename  如: default/index
     * @return array
     */
    public function load($shorthand, $filename) {

        $_ = array(); //语言包数组

        if ($shorthand == 'ZH') {
            $folders = 'chinese';
        } else if ($shorthand == 'EN') {
            $folders = 'english';
        } else {
            $folders = 'english';
        }

        $file = ROOT_PATH . '/languages/' . $folders . '/' . $filename . '.php';

        if (is_file($file)) {
            require($file);
        } else {
            return array();
        }

        $this->langData = array_merge($this->langData, $_);
    }

    /*
     * 报错页面
     */

    public function getError() {
        $url = "?app=baseFront&act=errorPage";
        header("location:" . $url);
        exit;
    }

    public function errorPage() {
        $this->display("public/error.html");
    }

    public function error_404($arr, $url) {
        if (count($arr) == 0 || !is_array($arr)) {
            echo '参数输入错误！';
            exit;
        }
        if (!strstr($url, 'public/error.html')) {
            echo '请求地址有误！';
            exit;
        }
        $this->assign('data', $arr);
        // var_dump($arr);die;
        $this->display($url);
    }

    /**
     * 生成不重复的四位随机数
     * @author wanyan
     * @date 2017-10-23
     */
    public function buildNo($limit) {
        $begin = pow(10, 3);
        $end = (pow(10, 4) - 1);
        $rand_array = range($begin, $end);
        shuffle($rand_array); //调用现成的数组随机排列函数
        return array_slice($rand_array, 0, $limit); //截取前$limit个
    }

    /**
     * 根据用户的IP地址，定位用户所在的城市
     * @return string
     */
    public function GetIp() {
        $realip = '';
        $unknown = 'unknown';
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach ($arr as $ip) {
                    $ip = trim($ip);
                    if ($ip != 'unknown') {
                        $realip = $ip;
                        break;
                    }
                }
            } else if (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']) && strcasecmp($_SERVER['HTTP_CLIENT_IP'], $unknown)) {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            } else if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)) {
                $realip = $_SERVER['REMOTE_ADDR'];
            } else {
                $realip = $unknown;
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), $unknown)) {
                $realip = getenv("HTTP_X_FORWARDED_FOR");
            } else if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), $unknown)) {
                $realip = getenv("HTTP_CLIENT_IP");
            } else if (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), $unknown)) {
                $realip = getenv("REMOTE_ADDR");
            } else {
                $realip = $unknown;
            }
        }
        $realip = preg_match("/[\d\.]{7,15}/", $realip, $matches) ? $matches[0] : $unknown;
        return $realip;
    }

    /** 根据新浪的接口获取所在的城市
     * @param string $ip
     * @return bool|mixed
     */
    public function GetIpLookup($ip = '') {
        if (empty($ip)) {
            $ip = $this->GetIp();
        }
//        $ip = '115.239.212.133';
        $res = @file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=' . $ip);
        if (empty($res)) {
            return false;
        }
        $jsonMatches = array();
        preg_match('#\{.+?\}#', $res, $jsonMatches);
        if (!isset($jsonMatches[0])) {
            return false;
        }
        $json = json_decode($jsonMatches[0], true);
        if (isset($json['ret']) && $json['ret'] == 1) {
            $json['ip'] = $ip;
            unset($json['ret']);
        } else {
            return false;
        }
        return $json;
    }

    /**
     * 模仿array_column方法，获取二位数组中的某个字段
     * @author jh
     * @date 2017/07/03
     */
    public function arrayColumn($info, $value, $key = '') {
        $data = array();
        if ($key) {
            foreach ($info as $v) {
                $data[$v[$key]] = $v[$value];
            }
        } else {
            foreach ($info as $v) {
                $data[] = $v[$value];
            }
        }
        return $data;
    }

    /*
     * 通过区域商品ID 获取商品对应语言信息
     * @author lee
     * @param $store_goods_id 区域商品ID $lang_id 商品ID
     * @date 2018-1-9 15:44:44
     */

    public function getGoodsLang($store_goods_id, $lang_id) {
        $areaGMod = &m('areaGood');
        $where = " where sg.id=" . $store_goods_id . " and gl.lang_id=" . $lang_id;
        $sql = "select gl.goods_name from " . DB_PREFIX . "store_goods as sg left join " . DB_PREFIX . "goods as g on sg.goods_id=g.goods_id
              left join " . DB_PREFIX . "goods_lang as gl on g.goods_id=gl.goods_id
              " . $where;
        $res = $areaGMod->querySql($sql);
        return $res[0];
    }

    /*
     * 商品规格处理
     */

    public function getkeyName($key) {
        $specItemMod = &m('goodsSpecItem');
        $id = explode('_', $key);
        $ids = implode(',', $id);
        $sql = 'SELECT  i.id,l.`item_name`,l.`lang_id`   FROM   bs_goods_spec_item AS i LEFT JOIN   bs_goods_spec_item_lang AS l ON i.id = l.`item_id`
                 WHERE  i.id IN(' . $ids . ')  AND  l.`lang_id` = ' . $this->langid;
        $data = $specItemMod->querySql($sql);

        $res = array();
        foreach ($data as $key => $val) {
            $data[$key]['item_name'] = ' ' . $val['item_name'];
            $res[] = $data[$key]['item_name'];
        }

        return implode(' ', $res);
    }

    /**
     * ### getBaseUrl function
     * // utility function that returns base url for
     * // determining return/cancel urls
     *
     * @return string
     */
    function getBaseUrl() {
        if (PHP_SAPI == 'cli') {
            $trace = debug_backtrace();
            $relativePath = substr(dirname($trace[0]['file']), strlen(dirname(dirname(__FILE__))));
            echo "Warning: This sample may require a server to handle return URL. Cannot execute in command line. Defaulting URL to http://localhost$relativePath \n";
            return "http://localhost" . $relativePath;
        }
        $protocol = 'http';
        if ($_SERVER['SERVER_PORT'] == 443 || (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on')) {
            $protocol .= 's';
        }
        $host = $_SERVER['HTTP_HOST'];
        $request = $_SERVER['PHP_SELF'];
        return dirname($protocol . '://' . $host . $request);
    }

    public function dd($info) {

        if (is_object($info) || is_array($info)) {
            $info_text = var_export($info, true);
        } elseif (is_bool($info)) {
            $info_text = $info ? 'true' : 'false';
        } else {
            $info_text = $info;
        }

        file_put_contents('./dd.txt', $info_text);
    }

    /*
     * 获取当前客服信息
     * modify by lee
     * @param store_id
     */

    public function getKfData($store_id, $uid) {
        $kfMod = &m('user');
        $msgMod = &m('imMsg');
        $data = $kfMod->getData(array("cond" => "is_kefu = 1 and store_id=" . $store_id . " and kf_status =1"));
        foreach ($data as $k => $v) {
            $has_mess = $msgMod->getOne(array("cond" => "fid=" . $v['id'] . " and tid=" . $uid . " and `status`=0"));
            if ($has_mess) {
                $data[$k]['has_message'] = 1;
            } else {
                $data[$k]['has_message'] = 2;
            }
        }
        return $data;
    }

    /**
     * 视图对应CSS
     * @author	luffy
     * @param	array $app
     * @return	void
     */
    protected function viewCss(){
        $viewCss = '';
        if (file_exists(ROOT_PATH . '/assets/front/app/css/' . strtolower(APP) . '/' . strtolower(ACT) . '.css')) {
            $viewCss = STATIC_URL . '/front/app/css/' . strtolower(APP) . '/' . strtolower(ACT) . '.css';
        }
        $this->assign('viewCss', $viewCss);
    }

    /**
     * 视图对应JS
     * @author	luffy
     * @param	array $app
     * @return	void
     */
    protected function viewJs(){
        $viewJs = '';
        if (file_exists(ROOT_PATH . '/assets/front/app/js/' . strtolower(APP) . '/' . strtolower(ACT) . '.js')) {
            $viewJs = STATIC_URL . '/front/app/js/' . strtolower(APP) . '/' . strtolower(ACT) . '.js';
        }
        $this->assign('viewJs', $viewJs);
    }
}

?>