<?php

/**
 * 文章页面
 * @author wangh
 * @date 2017/08/22
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class ArticlePageApp extends BaseFrontApp {

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
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        //文章所有分类
        $cidarr = array();
        $artctg = $this->getArticleCtg($lang);
        $firstCid=$artctg[0]['id'];
        if (!empty($artctg)) {
            foreach ($artctg as $val) {
                $cidarr[] = $val['id'];
            }
        }

        /* var_dump($artctg);exit; */


        $cids = implode(',', $cidarr);
        $langMod = &m('language');
        $languageData = $langMod->getData('fields=>name,id,logo,shorthand');

        //所以分类ids
        //分类取值
        if (empty($acid)) {
         /*   $acid = $cids;*/
            $url = 'index.php?app=articlePage&act=index&storeid=' . $this->storeid . '&lang=' . $this->langid . '&auxiliary=' . $auxiliary;  //
        } else {
            $url = 'index.php?app=articlePage&act=index&storeid=' . $this->storeid . '&lang=' . $this->langid . '&auxiliary=' . $auxiliary . '&acid=' . $acid;  //
        }

        //获取分类下的文章列表
        $articles = $this->getCtgArticle($this->storeid, $acid, $url, $page, $lang,$firstCid);


        //语言包加载
        $this->load($this->shorthand, 'articlepage/list');

        $this->assign('langdata', $this->langData);
        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang);

        $this->assign('user_id', $this->userId);
        $this->assign('artctg', $artctg);
        $this->assign('articles', $articles);
        $this->display('articlepage/list.html');
    }

    /**
     * 文章列表详情页面
     * @author wangh
     * @date 2017/09/19
     */
    public function detail() {
        $articleMod = &m('article');
        //接受数据
        $artid = !empty($_REQUEST['artid']) ? $_REQUEST['artid'] : '';  // 文章id
        //文章所以分类

        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->langid;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $artctg = $this->getArticleCtg($lang);


        //商品详情
        $sql = 'SELECT a.id,a.add_time,al.title,al.brif,al.body,a.image,a.cat_id FROM ' . DB_PREFIX . 'article AS a LEFT JOIN ' . DB_PREFIX . 'article_lang AS al ON a.id=al.article_id where a.id=' . $artid . ' AND
        al.lang_id=' . $lang;
        $detail = $articleMod->querySql($sql);
        /*      echo '<pre>';
          var_dump($detail);
          echo '</pre>';
          exit; */

        //商品推荐
        $recommGoods = $this->getRcommGoods($this->storeid);
        // 商品评价星级
        foreach ($recommGoods as $k => $v) {
             //店铺商品打折
            $store_sql = 'select store_discount from  ' . DB_PREFIX . 'store where id=' . $this->storeid;
            $store_arr = $articleMod->querySql($store_sql);
            $recommGoods[$k]['shop_price'] = number_format($v['shop_price'] * $store_arr[0]['store_discount'],2);
            
            $good_id = $v['id'];
            $sql = 'select  sum(goods_rank) / count(goods_rank) as res,count(*) as num from ' . DB_PREFIX . 'goods_comment where goods_id = ' . $good_id;
            $trance = $this->goodsCommentMod->querySql($sql);
            $recommGoods[$k]['rate'] = $trance[0]['res'];
            $recommGoods[$k]['num'] = $trance[0]['num'];
        }
        //更多精彩
        $catid = $detail[0]['cat_id']; //该文章的分类
        $moreArticles = $this->getMoreArticle($this->storeid, $catid, $lang, $artid, $limit = 5);
        $this->load($this->shorthand, 'articlepage/detail');
        //收藏文章
        $userId = $this->userId;
        $sql_collection = 'select * from ' . DB_PREFIX . 'user_article where user_id=' . $userId . ' and store_id=' . $this->storeid;
//            echo $sql_collection;exit;
        $data_collection = $articleMod->querySql($sql_collection);
//            var_dump($data_collection);exit;
        foreach ($data_collection as &$collertion) {
            if ($collertion['article_id'] == $detail[0]['id']) {
                $detail[0]['type'] = 1;
            }
        }
        /*  $detail[0]['title']=htmlspecialchars($detail[0]['title']);
          $detail[0]['brif']=htmlspecialchars($detail[0]['brif']);
          $detail[0]['body']=htmlspecialchars($detail[0]['body']); */

        $lang_id = $this->shorthand;
        $this->assign('lang_id', $lang);
        $this->assign('artctg', $artctg);
        $this->assign('detail', $detail[0]);
        $this->assign('recommGoods', $recommGoods);
        $this->assign('moreArticles', $moreArticles);
        $this->assign('langdata', $this->langData);
        $this->assign('user_id', $this->userId);
        $this->display('articlepage/detail.html');
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
    public function getCtgArticle($storeid, $acid, $url, $page, $lang,$firstCid) {
        $articleMod = &m('article');
        include(ROOT_PATH . '/data/page/pageClass.php');
        $pagesize = 5; //每页显示条数
        $curpage = $page;  //当前页数
        $limit = '  limit ' . ($curpage - 1 ) * $pagesize . ',' . $pagesize;
        if(!empty($acid)){
            $sql = 'SELECT * FROM ' . DB_PREFIX . 'category_article WHERE cate_id=' . $acid;
        }else{
            $sql = 'SELECT * FROM ' . DB_PREFIX . 'category_article WHERE cate_id=' . $firstCid;
        }
        $ctgData = $articleMod->querySql($sql);
        foreach ($ctgData as $v) {
            $articleId[] = $v['article_id'];
        }
        $articleIds = implode(',', $articleId);


        $where = '  where   a.store_id =' . $storeid;
        //统计条数
        $sqltotal = 'SELECT  COUNT(a.`id`)   as  total   FROM   ' . DB_PREFIX . 'article AS a
                     LEFT JOIN  ' . DB_PREFIX . 'article_category AS c ON a.`cat_id` = c.`id` ' . $where;
        $res = $articleMod->querySql($sqltotal);
        /* var_dump($res);exit; */
        $total = $res[0]['total'];
        //实例化分页类
        $pageClass = new page2($total, $pagesize, $curpage, $url, 2);
        // 输出分页html
        $pagelink = $pageClass->myde_write();

        //具体数据
        $sql = 'SELECT  a.id,a.`image`,al.`brif`,a.br_num,c.`id`  AS  cid,ac.`article_cate_name`,al.lang_id,al.title
                 FROM   ' . DB_PREFIX . 'article AS a LEFT JOIN  ' . DB_PREFIX . 'article_category AS c ON a.`cat_id` = c.`id` 
                LEFT JOIN ' . DB_PREFIX . 'article_lang as al ON  a.id = al.article_id 
                LEFT JOIN ' . DB_PREFIX . 'article_category_lang as ac ON c.id=ac.article_cate_id'
                . $where . ' AND al.lang_id=' . $lang . ' AND ac.lang_id=' . $lang . ' AND a.id in(' . $articleIds . ')';
        $sql .= '  order  by  a.br_num desc,a.id desc' . $limit;
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
        $res = array();
        $res['data'] = $arr;
        $res['pagelink'] = $pagelink;

        return $res;
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
        $where = '  where   mark =1  and  store_id =' . $storeid . '  and   g.is_on_sale =1';
        $sql = 'select g.*,l.*, g.id,gl.original_img  from  '
                . DB_PREFIX . 'store_goods as g inner join '
                . DB_PREFIX . 'goods_lang as l on l.goods_id = g.goods_id and l.lang_id=' . $this->langid . ' LEFT JOIN '
                . DB_PREFIX . 'goods as gl on g.goods_id = gl.goods_id '
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
    public function getMoreArticle($storeid, $catid, $lang, $artid, $limit = 5) {
        $articleMod = &m('article');

        $sql = 'SELECT article_id FROM ' . DB_PREFIX . 'category_article WHERE cate_id in ( ' . $catid . ')' . ' AND article_id !=' . $artid;
        $info = $articleMod->querySql($sql);
        /*   $info=array_unique($info); */
        foreach ($info as $k => $v) {
            $aId[] = $v['article_id'];
        }
        $aIds = implode(',', array_unique($aId));



        $where = '  where   a.store_id =' . $storeid;


        /* $sql = 'SELECT  a.id,a.`title`,a.`english_title`,a.`cover_photo`,a.`brief`,a.store_id,a.br_num,c.`id`  AS  cid,c.`name`
          FROM   ' . DB_PREFIX . 'article AS a LEFT JOIN  ' . DB_PREFIX . 'article_category AS c ON a.`cat_id` = c.`id` ' . $where; */
        $sql = 'SELECT a.id,a.image,al.title,al.brif FROM ' . DB_PREFIX . 'article AS a LEFT JOIN ' . DB_PREFIX . 'article_lang AS al ON a.id=al.article_id' . $where . ' AND al.lang_id= ' . $lang . ' AND a.id in (' . $aIds . ')';

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

            $data['statu'] = 1;
        } else {
            $res = $this->userArticleMod->doDrops('article_id =' . $article_id);
            $data['statu'] = 0;
        }
        $data['id'] = $article_id;
        echo json_encode($data);
        exit;
    }

}
