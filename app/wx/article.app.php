<?php
header('Access-Control-Allow-Origin:*');
/**
 * 文章
 *
 *
 */
class ArticleApp extends BaseWxApp {

    private $goodsCommentMod;
    private $userArticleMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->goodsCommentMod = &m('goodsComment');
        $this->userArticleMod = &m('userArticle');
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 文章列表页面
     * @author wangh
     * @date 2017/09/19
     */
    public function index() {
        //接受数据
        $acid = !empty($_REQUEST['acid']) ? $_REQUEST['acid'] : '';  //文章分类
        $page = !empty($_REQUEST['p']) ? $_REQUEST['p'] : 1;
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);

        $tp = !empty($_REQUEST['tp']) ? $_REQUEST['tp'] : 0;  //区别授权的
        if( $tp ){
            //判断是否登录
            header('Location: wx.php?app=user&act=loginOuth&back_app=' . APP . '&back_act=' . ACT . '&storeid=' . $this->storeid . '&lang=' . $this->langid . '&latlon=' . $latlon);
            exit;
        }


        //文章所有分类
        $cidarr = array();
        $artctg = $this->getArticleCtg($lang);
        if (!empty($artctg)) {
            foreach ($artctg as $val) {
                $cidarr[] = $val['id'];
            }
        }
        $cids = implode(',', $cidarr);
        $langMod = &m('language');
        $languageData = $langMod->getData('fields=>name,id,logo,shorthand');
        //所以分类ids
        //分类取值
        if (empty($acid)) {
            $acid = $cids;
            $url = 'index.php?app=articlePage&act=index&storeid=' . $this->storeid . '&lang=' . $this->langid . '&auxiliary=' . $auxiliary.'&latlon=' . $latlon;  //
        } else {
            $url = 'index.php?app=articlePage&act=index&storeid=' . $this->storeid . '&lang=' . $this->langid . '&auxiliary=' . $auxiliary . '&acid=' . $acid.'&latlon=' . $latlon;  //
        }
        //获取分类下的文章列表
        $sql = 'select a.id,al.article_cate_name from ' . DB_PREFIX . 'article_category as a left join ' . DB_PREFIX . 'article_category_lang as al on al.article_cate_id = a.id where al.lang_id=' . $lang;
        $ctgData = $this->goodsCommentMod->querySql($sql);
        $categoryData = array();
        foreach ($ctgData as $k => $v) {
            $categoryData[$v['id']]['id'] = $v['id'];
            $categoryData[$v['id']]['article_cate_name'] = $v['article_cate_name'];
            $categoryData[$v['id']]['list'] = $this->getCtgArticle($this->storeid, $v['id'], $lang);
            ;
        }

        $first = current($categoryData);
        $this->assign('first', $first['id']);
        $this->load($this->shorthand, 'articlepage/list');
        $this->assign('categoryData', $categoryData);
        $this->assign('langdata', $this->langData);
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang);
        $this->assign('storeid', $storeid);
        $this->assign('acid', $acid);
        $this->assign('user_id', $this->userId);
        $this->assign('artctg', $artctg);
        $this->display('article/articleList.html');
    }

    /**
     * 文章列表详情页面
     * @author wangh
     * @date 2017/09/19
     */
    public function detail() {
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $this->load($this->shorthand, 'WeChat/goods');
        $this->assign('langdata', $this->langData);

        $articleMod = &m('article');
        //接受数据
        $artid = !empty($_REQUEST['artid']) ? $_REQUEST['artid'] : '';  // 文章id
        //文章所以分类
        $storeid = !empty($_REQUEST['storeid']) ? $_REQUEST['storeid'] : $this->storeid;

        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        $artctg = $this->getArticleCtg($lang);


        //商品详情
        $sql = 'SELECT * FROM ' . DB_PREFIX . 'article AS a LEFT JOIN ' . DB_PREFIX . 'article_lang AS al ON a.id=al.article_id where a.id=' . $artid . '
         AND al.lang_id=' . $lang;
        $detail = $articleMod->querySql($sql);


        $recommGoods = $this->getRcommGoods($this->storeid);
        // 商品评价星级
        foreach ($recommGoods as $k => $v) {
            $good_id = $v['id'];
            $sql = 'select  sum(goods_rank) / count(goods_rank) as res,count(*) as num from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $good_id;
            $trance = $this->goodsCommentMod->querySql($sql);
            $recommGoods[$k]['rate'] = $trance[0]['res'];
            $recommGoods[$k]['num'] = $trance[0]['num'];
        }
        //更多精彩
        $catid = $detail[0]['cat_id']; //该文章的分类
        $moreArticles = $this->getMoreArticle($this->storeid, $catid, $artid, $limit = 5);
        $this->load($this->shorthand, 'articlepage/detail');
        //收藏文章
        $userId = $this->userId;
        $sql_collection = 'select * from ' . DB_PREFIX . 'user_article where user_id=' . $this->userId . ' and store_id=' . $this->storeid;
//            echo $sql_collection;exit;
        $data_collection = $articleMod->querySql($sql_collection);
//            var_dump($data_collection);exit;
        foreach ($data_collection as &$collertion) {
            if ($collertion['article_id'] == $detail[0]['article_id']) {
                $detail[0]['type'] = 1;
            }
        }

        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang);
        $this->assign('artctg', $artctg);
        $this->assign('detail', $detail[0]);
        /*     echo '<pre>';
          var_dump($detail[0]);
          echo '</pre>';
          exit; */
        $this->assign('storeid', $storeid);
        $this->assign('recommGoods', $recommGoods);
        $this->assign('moreArticles', $moreArticles);
        $this->assign('langdata', $this->langData);
        $this->assign('user_id', $this->userId);
        $this->display('article/articleDetail.html');
    }

    /**
     * 文章分类
     * @author wangh
     * @date 2017/09/19
     */
    public function getArticleCtg($lang) {
        $artCtgMod = &m('articleCate');
        $sql = 'SELECT a.id,ac.article_cate_name,ac.lang_id FROM ' . DB_PREFIX . 'article_category AS a LEFT JOIN ' . DB_PREFIX . 'article_category_lang AS ac ON a.id=ac.article_cate_id where ac.lang_id=' . $lang;
        $data = $artCtgMod->querySql($sql);

        return $data;
    }

    /**
     * 获取分类下的文章列表
     * @author wangh
     * @date 2017/09/13
     */
    public function getCtgArticle($storeid, $acid, $lang) {
        $articleMod = &m('article');

        $sql = 'SELECT * FROM ' . DB_PREFIX . 'category_article WHERE cate_id=' . $acid;
        $ctgData = $articleMod->querySql($sql);
        foreach ($ctgData as $v) {
            $articleId[] = $v['article_id'];
        }
        $articleIds = implode(',', $articleId);


        $where = '  where   a.store_id =' . $storeid;



        //具体数据
        $sql = 'SELECT  a.id,a.`image`,al.`brif`,a.br_num,c.`id`  AS  cid,ac.`article_cate_name`,al.lang_id,al.title
                 FROM   ' . DB_PREFIX . 'article AS a LEFT JOIN  ' . DB_PREFIX . 'article_category AS c ON a.`cat_id` = c.`id` 
                LEFT JOIN ' . DB_PREFIX . 'article_lang as al ON  a.id = al.article_id 
                LEFT JOIN ' . DB_PREFIX . 'article_category_lang as ac ON c.id=ac.article_cate_id'
                . $where . ' AND al.lang_id=' . $lang . ' AND ac.lang_id=' . $lang . ' AND a.id in(' . $articleIds . ')';
        $sql .= '  order  by  a.br_num desc,a.id desc';
        $arr = $articleMod->querySql($sql);


        //收藏文章
        $userId = $this->userId;
        $sql_article = 'select * from ' . DB_PREFIX . 'user_article where user_id=' . $userId . ' and store_id=' . $this->storeid;
        $data_article = $this->userArticleMod->querySql($sql_article);
        foreach ($arr as $k => $v) {
            foreach ($data_article as $k1 => $v1) {
                if ($v['id'] == $v1['article_id']) {
                    $arr[$k]['type'] = 1;
                }
            }
        }
        /* var_dump($arr);exit; */

        return $arr;
    }

    /**
     * 商品推荐 取销量前5的
     * @author wangh
     * @date 2017/09/13
     */
    public function getRcommGoods($storeid) {
        //语言包加载
        $this->load($this->shorthand, 'articlepage/detail');
        $a = $this->langData;
        $storeGoodsMod = &m('areaGood');
        $limit = '  limit  5';
        $where = '  where   mark =1  and  store_id =' . $storeid . '  and   is_on_sale =1';
        $sql = 'select g.*,l.*, g.id,gl.original_img  from  '
                . DB_PREFIX . 'store_goods as g inner join '
                 . DB_PREFIX . 'goods as gl on g.goods_id = gl.goods_id inner join '
                . DB_PREFIX . 'goods_lang as l on l.goods_id = g.goods_id and l.lang_id=' . $this->langid
                . $where;
        $sql .= '  order by l.id  desc' . $limit;
        $data = $storeGoodsMod->querySql($sql);
        foreach ($data as &$item) {
            //是否包邮
            switch ($item['is_free_shipping']) {
                case 1:
                    $item['isfree'] = $a['article_Free'];
                    break;
                case 2:
                    $item['isfree'] = $a['article_No'];
                    break;
                default:
                    $item['isfree'] = $a['article_No'];
            }
            //
        }
        return $data;
    }

    /**
     * 更多精彩 5条
     * @author wangh
     * @date 2017/09/13
     */
    public function getMoreArticle($storeid, $catid, $artid, $limit = 5) {
        $articleMod = &m('article');

        if (!empty($catid)) {
            $where = '  where   a.store_id =' . $storeid . '  and   a.cat_id  in(' . $catid . ')  and  a.id !=' . $artid;
        } else {
            $where = '  where   a.store_id =' . $storeid . '  and  a.id !=' . $artid;
        }

        $sql = 'SELECT  a.id,a.`title`,a.`english_title`,a.`cover_photo`,a.`brief`,a.store_id,a.br_num,c.`id`  AS  cid,c.`name`
                 FROM   ' . DB_PREFIX . 'article AS a LEFT JOIN  ' . DB_PREFIX . 'article_category AS c ON a.`cat_id` = c.`id` ' . $where;
        $sql .= '  order  by  a.id  desc  limit ' . $limit;
        $res = $articleMod->querySql($sql);
        $data = array();
        $total = 0;
        if (!empty($res)) {
            foreach ($res as $key => $val) {
                $data[] = $val;
                $total++;
            }
        }
        //如果不够5条
        if ($total < 5) {
            $sql2 = 'SELECT  a.id,a.`title`,a.`english_title`,a.`cover_photo`,a.`brief`,a.store_id,a.br_num,c.`id`  AS  cid,c.`name`
                 FROM   ' . DB_PREFIX . 'article AS a LEFT JOIN  ' . DB_PREFIX . 'article_category AS c ON a.`cat_id` = c.`id`
                 where  a.store_id =' . $storeid . '   and  a.id!=' . $artid . '  and  a.cat_id  not in(' . $catid . ')  order by a.id  desc  limit ' . ($limit - $total );

            $res2 = $articleMod->querySql($sql2);
            if (!empty($res2)) {
                foreach ($res2 as $v) {
                    $data[] = $v;
                }
            }
        }

        return $data;
    }

    /*
     * 收藏的文章ajax判断
     * @author wangshuo
     * @date 2017-11-6 
     */

    public function docollectionarticle() {
        $type = $_GET['type'];
        $article_id = $_GET['id'];
        $userId = $this->userId;
        $storeid = $_GET['store_id'];


        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : 1;  //1中文，2英语
        if ($type == 'false') {

            $data = array(
                'table' => 'user_article',
                'user_id' => $userId,
                'article_id' => $article_id,
                'store_id' => $storeid,
                'adds_time' => time(),
                    // 'statu' => 1,
            );

            $res = $this->userArticleMod->doInsert($data);
            if ($res) {
                $data['statu'] = 1;
            }
        } else {
            $where = ' article_id = ' . $article_id . ' AND user_id=' . $userId;
            $res = $this->userArticleMod->doDrops($where);

            if ($res) {
                $data['statu'] = 0;
            }
        }
        $data['id'] = $article_id;
        echo json_encode($data);
        exit;
    }


    //收藏店铺
    public function docollectionStore() {
        $type=$_REQUEST['type'];
        $userId = $this->userId;
        $userStoreMod=&m('userStore');
        $storeid=$_REQUEST['store_id'];

        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : 1;  //1中文，2英语

        if ($type == 'false') {
            $data = array(
                'table' => 'user_store',
                'user_id' => $userId,
                'store_id' => $storeid,
                'add_time' => time(),
            );
            $res = $userStoreMod->doInsert($data);
            if ($res) {
                $data['statu'] = 1;
            }
        } else {
            $where = ' store_id = ' . $storeid . ' AND user_id=' . $userId;
            $res = $userStoreMod->doDrops($where);

            if ($res) {
                $data['statu'] = 0;
            }
        }
        $data['id'] = $storeid;
        echo json_encode($data);
        exit;
    }

    //收藏商品
    public function docollectionGoods() {
        $type=$_REQUEST['type'];
        $userId = $this->userId;
        $userStoreMod=&m('colleCtion');
        $storeid=$_REQUEST['store_id'];
        $id=$_REQUEST['id'];
        $good_id=$_REQUEST['good_id'];

        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : 1;  //1中文，2英语

        if ($type == 'false') {

            $data = array(
                'table' => 'user_collection',
                'user_id' => $userId,
                'store_id' => $storeid,
                'adds_time' => time(),
                'good_id'=>$good_id,
                'store_good_id'=>$id
            );

            $res = $userStoreMod->doInsert($data);
            if ($res) {
                $data['statu'] = 1;
            }
        } else {
            $where = ' store_id = ' . $storeid . ' AND user_id=' . $userId . ' AND good_id='.$good_id . ' AND store_good_id='.$id;
            $res = $userStoreMod->doDrops($where);

            if ($res) {
                $data['statu'] = 0;
            }
        }
        $data['id'] = $id;
        echo json_encode($data);
        exit;
    }

    /**
     * 文章领劵页面
     * author fup
     * date 2019-07-17
     */
    public function couponList(){
        $articleMod = &m('article');
        $sql = 'SELECT * FROM ' .DB_PREFIX . 'store_console  WHERE status = 1 AND mark = 1 AND `type` = 2';
        $console = $articleMod->querySql($sql);
        if($console){
            $console[0]['relation_1'] = unserialize($console[0]['relation_1']);
        }
        $this->assign('console',$console);
//        var_dump($console);die;
        $this->display('article/couponList.html');
    }

    /**
     * 文章领劵
     * author fup
     * date 2019-07-17
     */
    public function doCoupon(){
//        echo date('Y-m-d',1563351001),
//        date('Y-m-d',1578903001);die;
        $id = intval($_REQUEST['id']) ? : 0;
        $phone = trim($_REQUEST['phone']) ? : '';

        if(!preg_match("/^1\d{10}$/", $phone)){
            $this->setData(array(),0,'请正确填写手机号');
        }
        $user_coupon = &m('userCoupon');
        $coupon = &m('coupon');
        $userData = $coupon->getOne(array('cond'=>'phone = '. $phone .' AND mark = 1','table'=>'user'));
        if(!$userData){
            $url = 'wx.php?app=user&act=quickLogin';
            $this->setData(array('url'=>$url),2,'手机号未注册');
//            header("Location:$url");
//            exit();
        }
//        $sql = 'SELECT * FROM ' .DB_PREFIX . 'store_console WHERE id = '. $id . ' AND mark = 1 AND status = 1 limit 1' ;
        $console = $coupon->getOne(array('cond'=>'id = '. $id . ' AND type = 2 AND mark = 1 AND status = 1','table'=>'store_console'));
        if(!$console){
            $this->setData(array(),0,'活动暂未开启');
        }
        if($relation_1 = unserialize($console['relation_1'])){
            $cou = $coupon->getOne(array('cond'=>'id = '.$relation_1['coupon_id'].' AND type = 1 AND mark = 1'));
            if(!$cou){
                $this->setData(array(),0,'劵码已被领完啦');
            }
        }else{
            $this->setData(array(),0,'出错啦，请稍后再试');
        }
        $res = $user_coupon->getOne(array('cond'=>'user_id = '.$userData['id'] .' AND c_id = '. $cou['id']));
        if($res){
            $this->setData(array(),0,'您已经领取过啦');
        }
        $now = time();
        $data = array(
            'user_id' => $userData['id'],
            'c_id' => $cou['id'],
            'remark' => '文章领劵',
            'source' => 3,
            'start_time' => $now,
            'end_time' => $now + 3600 * 24 * $cou['limit_times'],
            'add_user' => $userData['id'],
            'add_time' => time()
        );
        if($user_coupon->doInsert($data)){
            $this->setData(array('phone'=>substr_replace($userData['phone'], '****', 3, 4),'money'=>(int) $cou['discount']),1,'领取成功');
        }
        $this->setData(array(),0,'领取失败');

    }


}
