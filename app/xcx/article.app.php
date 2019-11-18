<?php
/**
 * 文章控制器
 * @author zhangkx
 * @date: 2018-08-15
 */
class ArticleApp extends BasePhApp
{
    private $userArticleMod;
    private $goodsCommentMod;
    /**
     * 构造函数
     */
    public function __construct(){
        parent::__construct();
        $this->goodsCommentMod = &m('goodsComment');
        $this->userArticleMod = &m('userArticle');

    }

    /**
     * 析构函数
     */
    public function __destruct(){
    }

    /**
     * 文章列表
     *
     * @author zhangkx
     * @date 2018-08-15
     */
    public function index() {
        //接受数据
        $lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : $this->lang_id;
        $storeid = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : $this->store_id;
        $auxiliary = !empty($_REQUEST['auxiliary']) ? htmlspecialchars(trim($_REQUEST['auxiliary'])) : '0';
        $this->assign('auxiliary', $auxiliary);
        $latlon = !empty($_REQUEST['latlon']) ? htmlspecialchars(trim($_REQUEST['latlon'])) : '0';
        $this->assign('latlon', $latlon);
        //文章所有分类
        $cidarr = array();
        $artctg = $this->getArticleCtg($lang);
        if (!empty($artctg)) {
            foreach ($artctg as $val) {
                $cidarr[] = $val['id'];
            }
        }
        //获取分类下的文章列表
        $sql = 'select a.id,al.article_cate_name from ' . DB_PREFIX . 'article_category as a left join ' . DB_PREFIX . 'article_category_lang as al on al.article_cate_id = a.id where al.lang_id=' . $lang;
        $ctgData = $this->goodsCommentMod->querySql($sql);
        $categoryData = array();
        foreach ($ctgData as $k => $v) {
            $categoryData[] = array(
                'id' => $v['id'],
                'article_cate_name' => $v['article_cate_name'],
                'list' => $this->getCtgArticle($storeid, $v['id'], $lang),
            );
        }
//        echo '<pre>';
//        print_r($categoryData);die;
        $articleCateMod = &m('articleCate');
        $sql = 'SELECT a.id,ac.article_cate_name,ac.lang_id FROM ' . DB_PREFIX . 'article_category AS a LEFT JOIN '
            . DB_PREFIX . 'article_category_lang AS ac ON a.id=ac.article_cate_id where ac.lang_id=' . $lang;
        $data = $articleCateMod->querySql($sql);
        $info = array(
            'category' => $data,
            'data' => $categoryData
        );
        $this->setData($info,'1','');
    }

    /**
     * 获取分类下的文章列表
     *
     * @author zhangkx
     * @date 2018-08-15
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
        $sql_article = 'select * from ' . DB_PREFIX . 'user_article where user_id=' . $userId . ' and store_id=' . $this->store_id;
        $data_article = $this->userArticleMod->querySql($sql_article);
        foreach ($arr as $k => $v) {
            foreach ($data_article as $k1 => $v1) {
                if ($v['id'] == $v1['article_id']) {
                    $arr[$k]['type'] = 1;
                }
            }
        }
        return $arr;
    }

    /**
     * 文章分类
     *
     * @author zhangkx
     * @date 2018-08-15
     */
    public function getArticleCtg($lang) {
        $artCtgMod = &m('articleCate');
        $sql = 'SELECT a.id,ac.article_cate_name,ac.lang_id FROM ' . DB_PREFIX . 'article_category AS a LEFT JOIN ' . DB_PREFIX . 'article_category_lang AS ac ON a.id=ac.article_cate_id where ac.lang_id=' . $lang;
        $data = $artCtgMod->querySql($sql);
        return $data;
    }

    /**
     * 领劵页面
     * author fup
     * date 2019-07-18
     */
    public function couponList(){
        $couponMod = &m('coupon');
        $console = $couponMod->getOne(array('cond'=>'status = 1 AND mark = 1 AND type = 2','table'=>'store_console'));
        if($console){
            $console['relation_1'] = unserialize($console['relation_1']);
        }
        // var_dump($console);die;
        $console['relation_1']['relation_2'] = 'http://product.lmeri.com/uploads/big/' . $console['relation_2'];
        // $console['relation_1']['color'] = '#bb2f04';
        $console['relation_2'] = 'http://product.lmeri.com/uploads/big/' . $console['relation_2'];;
//        var_dump($console);die;
        $this->setData($console,1,'SUCCESS');
    }

    /**
     * 领取优惠券
     * author fup
     * date 2019-07-18
     *
     */
    public function addCoupon(){
        $id = intval($_REQUEST['id']) ? : 0;
        $phone = trim($_REQUEST['phone']) ? : '';
        if(!preg_match("/^1\d{10}$/", $phone)){
            $this->setData(array(),0,'请正确填写手机号');
        }
        $userMod = &m('user');
        $userCouponMod = &m('userCoupon');
        $couponMod = &m('coupon');
        $userData = $userMod->getOne(array('cond'=>'phone = '. $phone .' AND mark = 1'));
        if(!$userData){
            $this->setData(array(),0,'手机号未注册');
        }

        $console = $couponMod->getOne(array('cond'=>'id = '. $id . ' AND mark = 1 AND status = 1','table'=>'store_console'));
        if(!$console){
            $this->setData(array(),0,'领劵活动暂未开启');
        }
        if($relation_1 = unserialize($console['relation_1'])){
            $cou = $couponMod->getOne(array('cond'=>'id = '.$relation_1['coupon_id'].' AND type = 1 AND mark = 1'));
            if(!$cou){
                $this->setData(array(),0,'劵码已被领完啦');
            }
        }else{
            $this->setData(array(),0,'出错啦，请稍后再试');
        }
        $res = $userCouponMod->getOne(array('cond'=>'user_id = '.$userData['id'] .' AND c_id = '. $cou['id']));
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

        if($userCouponMod->doInsert($data)){
            $this->setData(array('phone'=>substr_replace($userData['phone'], '****', 3, 4),'money'=>(int) $cou['discount']),1,'领取成功');
        }
        $this->setData(array(),0,'领取失败');
    }

}