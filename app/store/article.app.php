<?php

//如果需要设置允许所有域名发起的跨域请求，可以使用通配符 *
header("Access-Control-Allow-Origin: *"); // 允许任意域名发起的跨域请求
header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');
header("Access-Control-Allow-Methods:HEAD, GET, POST, DELETE, PUT, OPTIONS");
/**
 * 文章列表
 * @author wh
 * @date 2017-8-9
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class ArticleApp extends BaseStoreApp {

    private $articleMod;
    private $lang_id;
    private $articleLangMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->articleLangMod = &m('articleLang');
        $this->articleMod = &m('article');
        $this->lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : '0'; //获取切换语言
    }

    /**
     * 文章列表展示
     * @author wh
     * @date 2017-8-9
     */
    public function articleList() {
        $storeId = $this->storeId;
        $ctgid = $_REQUEST['cat_id'];
        $isrecom = $_REQUEST['isrecom'];
        $title = !empty($_REQUEST['title']) ? htmlspecialchars(trim($_REQUEST['title'])) : '';
        $english_title = !empty($_REQUEST['english_title']) ? htmlspecialchars(trim($_REQUEST['english_title'])) : '';
        $this->assign('title', $title);
        $this->assign('english_title', $english_title);
        $this->assign('isrecom', $isrecom);
        //文章分类
        $ctgMod = &m('articleCate');
        $sql = 'select  al.article_cate_name,a.id from  ' . DB_PREFIX . 'article_category as a left join ' . DB_PREFIX . 'article_category_lang as al on a.id=al.article_cate_id where al.lang_id =' . $this->defaulLang;
        $res = $ctgMod->querySql($sql);
        /* echo '<pre>';
          var_dump($res);
          echo '</pre>';
          exit;
         */        //$tree = $this->getTree(0, $res, 1);
        if (!empty($ctgid)) {
            $options = $this->getSeleOptions($res, $ctgid);
        } else {
            $options = $this->getSeleOptions($res);
        }
        $this->assign('options', $options);

        //
        $where = '  where 1=1  and  a.store_id =' . $storeId;
        //文章标题
        if ($this->lang_id == 1) {
            $where .= '   and  al.`title`  like  "%' . $english_title . '%"';
        } else {
            $where .= '   and  al.`title`  like  "%' . $title . '%"';
        }

        if (!empty($ctgid)) {
            $sql = 'SELECT * FROM ' . DB_PREFIX . 'category_article WHERE cate_id=' . $ctgid;
            $ctgData = $this->articleMod->querySql($sql);
            foreach ($ctgData as $v) {
                $articleId[] = $v['article_id'];
            }
            $articleIds = implode(',', $articleId);
            $where .= ' AND a.id in(' . $articleIds . ')';
        }
        if (!empty($isrecom)) {
            $where .= '  and  a.isrecom =' . $isrecom;
        }
        // 获取总数

        $sql = 'SELECT count(*) as totalCount  FROM ' . DB_PREFIX . 'article as a LEFT JOIN ' . DB_PREFIX . 'article_lang as al ON a.id =al.article_id ' . $where . ' AND al.lang_id = ' . $this->defaulLang;
        $totalCount = $this->articleMod->querySql($sql);

        $total = $totalCount[0]['totalCount'];



        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;

        $sql = 'SELECT   al.title,a.isrecom,a.add_time,a.id FROM  ' . DB_PREFIX . 'article  AS a
                LEFT JOIN   ' . DB_PREFIX . 'article_lang as al ON a.id=al.article_id 
                ' . $where . ' AND al.lang_id = ' . $this->defaulLang;
        $sql .= '   order  by  a.id  desc';








        $data = $this->articleMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));


        foreach ($data['list'] as $k => $v) {
            $data['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        $list = $data['list'];
        foreach ($list as $k => $v) {
            $listSql = 'SELECT *  FROM ' . DB_PREFIX . 'category_article WHERE article_id=' . $v['id'];
            $listData = $this->articleMod->querySql($listSql);
            $cateId = array();
            foreach ($listData as $kk => $vv) {
                $cateId[] = $vv['cate_id'];
            }
            $cateIds = implode(',', $cateId);
            $cateSql = 'SELECT ac.article_cate_name FROM ' . DB_PREFIX . 'article_category as a LEFT JOIN ' . DB_PREFIX . 'article_category_lang AS ac ON a.id=ac.article_cate_id WHERE a.id in (' . $cateIds . ') AND ac.lang_id=' . $this->defaulLang;
            $cateData = $this->articleMod->querySql($cateSql);
            $cate_name = array();
            foreach ($cateData as $key => $val) {
                $cate_name[] = $val['article_cate_name'];
                continue;
            }
            $article_cate_name = implode(',', array_unique($cate_name));
            if (!empty($article_cate_name)) {
                $list[$k]['article_cate_name'] = $article_cate_name;
            }
        }


        foreach ($list as &$val) {
            //文章标题
            $val['title'] = mb_substr($val['title'], 0, 24, 'utf-8');
            //商品状态
            if ($this->lang_id == 0) {
                switch ($val['isrecom']) {
                    case 1:
                        $val['statusName'] = '是';
                        break;
                    case 2:
                        $val['statusName'] = '否';
                        break;
                }
            } else {
                switch ($val['isrecom']) {
                    case 1:
                        $val['statusName'] = 'Yes';
                        break;
                    case 2:
                        $val['statusName'] = 'no';
                        break;
                }
            }
            //分类路径
            //$val['ctgpath'] = $this->getArticleCtgPath($val['pid'], $val['cname']);
        }
        $this->assign('p', $p);
        $this->assign('list', $list);
        $this->assign('page_html', $data['ph']);
        $this->assign('lang_id', $this->lang_id);
        if ($this->lang_id == 1) {
            $this->display('article/articleList_1.html');
        } else {
            $this->display('article/articleList.html');
        }
    }

    /**
     * 文章分类路径
     * @author ws
     * @date 2017-9-19
     */
    public function getArticleCtgPath($pid, $cname) {
        $ctgMod = &m('articleCate');
        $sql = 'select  name  from  ' . DB_PREFIX . 'article_category  where  id =' . $pid;
        $data = $ctgMod->querySql($sql);
        $str = '';
        $str .= $data[0]['name'] . $cname;
        return $str;
    }

    /**
     * 文章状态的更改
     * @author wh
     * @date 2017-8-9
     */
    public function upIsrecom() {
        $lang_id = $_REQUEST['lang_id'];
        if ($lang_id == 0) {
            //1是，2否
            $id = $_REQUEST['id'];
            $recom = $_REQUEST['recom'];
            if ($recom == '否') {
                $data = array(
                    'isrecom' => 1
                );
            } else {
                $data = array(
                    'isrecom' => 2
                );
            }
            $this->articleMod->doEdit($id, $data);
        } else {
            //1Yes，2no
            $id = $_REQUEST['id'];
            $recom = $_REQUEST['recom'];
            if ($recom == 'no') {
                $data = array(
                    'isrecom' => 1
                );
            } else {
                $data = array(
                    'isrecom' => 2
                );
            }
            $this->articleMod->doEdit($id, $data);
        }
    }

    /**
     * @author wangh
     * @date 2017-06-22
     * 获取分类tree
     * @param $parid
     * @param $channels
     * @param $dep
     * @return array
     */
    public function getTree($parid, $channels, $dep = 1) {
        static $html;
        for ($i = 0; $i < count($channels); $i++) {
            if ($channels[$i]['parent_id'] == $parid) {
                $html[] = array('id' => $channels[$i]['id'], 'name' => $channels[$i]['name'], 'dep' => $dep,);
                $this->getTree($channels[$i]['id'], $channels, $dep + 1);
            }
        }
        return $html;
    }

    public function getChild($parid, $channels) {
        static $childs;
        for ($i = 0; $i < count($channels); $i++) {
            if ($channels[$i]['parent_id'] == $parid) {
                $childs[] = array('id' => $channels[$i]['id'], 'name' => $channels[$i]['name']);
                $this->getChild($channels[$i]['id'], $channels);
            }
        }
        return $childs;
    }

    /**
     * @author wangh
     * @date 2017-06-22
     * 获取selection 组件
     * @param $channels
     * @return string
     */
    public function getSeleOptions($tree, $selected = 0) {
        if ($this->lang_id == 0) {
            $option = '';
            $option .= '<option value="0" >--请选择分类--</option>';
            if (is_array($tree)) {
                foreach ($tree as $val) {
                    if ($val['id'] == $selected) {
                        $option .= '<option  selected  value="' . $val['id'] . '" >' . str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $val['dep']) . '|—-' . $val['article_cate_name'] . '</option>';
                    } else {
                        $option .= '<option    value="' . $val['id'] . '" >' . str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $val['dep']) . '|—-' . $val['article_cate_name'] . '</option>';
                    }
                }
            }
            return $option;
        } else {
            $option = '';
            $option .= '<option value="0" >--please select a type--</option>';
            if (is_array($tree)) {
                foreach ($tree as $val) {
                    if ($val['id'] == $selected) {
                        $option .= '<option  selected  value="' . $val['id'] . '" >' . str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $val['dep']) . '|—-' . $val['article_cate_name'] . '</option>';
                    } else {
                        $option .= '<option    value="' . $val['id'] . '" >' . str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $val['dep']) . '|—-' . $val['article_cate_name'] . '</option>';
                    }
                }
            }
            return $option;
        }
    }

    /**
     * 获取文章分类
     * @author wh
     * @date 2017-8-9
     */
    public function getCtg() {
        $storeId = $this->storeId;
        $id = $_REQUEST['id'];
        $ctgMod = &m('articleCate');
        $sql = 'select  * from  ' . DB_PREFIX . 'article_category  where  parent_id =' . $id;
        $data = $ctgMod->querySql($sql);
        echo json_encode($data);
        exit;
    }

    /**
     * 文章添加
     * @author wh
     * @date 2017-8-9
     */
    public function add() {
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $this->assign('p', $p);
        $this->assign('lang_id', $this->lang_id);
        $ctgMod = &m('articleCate');
        $langMod = &m('language');
        $languageData = $langMod->getData('fields=>name,id,logo,shorthand');
        $sql = 'select  al.article_cate_name,a.id from  ' . DB_PREFIX . 'article_category as a left join ' . DB_PREFIX . 'article_category_lang as al on a.id=al.article_cate_id where al.lang_id =' . $this->defaulLang;
        $ctglev1 = $ctgMod->querySql($sql);
        $this->assign('ctglev1', $ctglev1);
        $this->assign('languageData', $languageData);
        if ($this->lang_id == 1) {
            $this->display('article/articleAdd_1.html');
        } else {
            $this->display('article/articleAdd.html');
        }
    }

    public function doAdd() {
        $langMod = &m('language');
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $article_lang = ($_REQUEST['lang']) ? $_REQUEST['lang'] : '';
        $langList = $langMod->getData('fields=>name,id,logo,shorthand');
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $lang_id = !empty($_REQUEST['lang_id']) ? (int) ($_REQUEST['lang_id']) : '0';
        $storeId = $this->storeId;
        $cPhoto = $_REQUEST['image_id'];
        $isrecom = $_REQUEST['isrecom'];
        //$catP = $_REQUEST['cat_p'];
        $catId = !empty($_REQUEST['cate_id']) ? $_REQUEST['cate_id'] : array();

        foreach ($article_lang as $k => $v) {
            foreach ($langList as $k1 => $v2) {
                if ($k == $v2['id']) {
                    if (empty($v['title'])) {
                        if ($this->lang_id == 1) {
                            $this->setData(array(), $status = '0', $v2['name_en'] . ' Title not null');
                        } else {
                            $this->setData(array(), $status = '0', $v2['name_en'] . '标题不能为空');
                        }
                    }

                    if (empty($v['brief'])) {
                        if ($this->lang_id == 1) {
                            $this->setData(array(), $status = '0', $v2['name_en'] . ' brief introduction not null');
                        } else {
                            $this->setData(array(), $status = '0', $v2['name_en'] . '简介不能为空');
                        }
                    }
                    if (empty($v['body'])) {
                        if ($this->lang_id == 1) {
                            $this->setData(array(), $status = '0', $v2['name_en'] . ' content not null');
                        } else {
                            $this->setData(array(), $status = '0', $v2['name_en'] . '内容不能为空');
                        }
                    }
                }
            }
        }
        foreach ($catId as $k => $v) {
            if ($v == 0) {
                if ($this->lang_id == 1) {
                    $this->setData(array(), $status = '0', ' category not null');
                } else {
                    $this->setData(array(), $status = '0', '分类不能为空');
                }
            }
        }


        if (empty($cPhoto)) {
            if ($this->lang_id == 1) {
                $this->setData(array(), $status = '0', 'Please upload the picture');
            } else {
                $this->setData(array(), $status = '0', '请上传图片');
            }
        }
        $length = count($catId);
        for ($i = 0; $i < $length; $i++) {
            $cate = $catId[$i];
            for ($j = $i + 1; $j < $length; $j++) {
                if ($cate == $catId[$j]) {
                    if ($this->lang_id == 1) {
                        $this->setData(array(), $status = '0', 'Classification has already existed');
                    } else {
                        $this->setData(array(), $status = '0', '分类已存在');
                    }
                }
            }
        }


        $catIds = implode(',', array_unique($catId));




        // 数据
        $data = array(
            'image' => $cPhoto,
            'cat_id' => $catIds,
            'isrecom' => $isrecom,
            'store_id' => $storeId,
            'add_time' => time()
        );


        $categoryArticle = &m('articleCategory');
        $res = $this->articleMod->doInsert($data);
        foreach ($catId as $v) {
            $cateData[] = array('article_id' => $res, 'cate_id' => $v);
        }
        foreach ($cateData as $v) {
            $re = $categoryArticle->doInsert($v);
            if (!$re) {
                $this->setData(array(), '0', 'articleCategory表');
            }
        }



        foreach ($article_lang as $k => $v) {
            $dataLang = array(
                'lang_id' => $k,
                'add_time' => time(),
                'title' => addslashes(trim($v['title'])),
                'body' => addslashes(trim($v['body'])),
                'brif' => addslashes(trim($v['brief'])),
                'article_id' => $res
            );
            $articleinfo = $this->articleLangMod->doInsert($dataLang);
            if (!$articleinfo) {
                $this->setData(array(), '0', 'articleLang表');
            }
        }


        if ($res) {
            $info['url'] = "store.php?app=article&act=articleList&lang_id=" . $lang_id . '&p=' . $p;

            if ($this->lang_id == 1) {
                $this->setData($info, $status = 1, 'Success of Add');
            } else {
                $this->setData($info, $status = 1, $a['edit_Success']);
            }
        } else {

            if ($this->lang_id == 1) {
                $this->setData(array(), $status = 1, 'Failor of Add');
            } else {
                $this->setData(array(), '0', $a['add_fail']);
            }
        }
    }

    /**
     * 文章编辑
     * @author wh
     * @date 2017-8-9
     */
    public function edit() {
        $this->assign('lang_id', $this->lang_id);
        $ctgMod = &m('articleCate');
        $langMod = &m('language');
        $id = $_REQUEST['id'];
        $languageData = $langMod->getData('fields=>name,id,logo,shorthand');
        $sql = 'select  al.article_cate_name,a.id from  ' . DB_PREFIX . 'article_category as a left join ' . DB_PREFIX . 'article_category_lang as al on a.id=al.article_cate_id where al.lang_id =' . $this->defaulLang;
        $ctglev1 = $ctgMod->querySql($sql);

        $sql = 'SELECT a.isrecom, a.image,a.cat_id,al.body,al.brif,al.title,al.lang_id FROM ' . DB_PREFIX . 'article AS a LEFT JOIN ' . DB_PREFIX . 'article_lang as al on a.id = al.article_id where a.id=' . $id;
        $arr = $ctgMod->querySql($sql);
        foreach ($arr as $k => $v) {
            $data['isrecom'] = $v['isrecom'];
            $data['image'] = $v['image'];
            $data['cate_id'] = $v['cat_id'];
            $data[$v['lang_id']]['body'] = $v['body'];
            $data[$v['lang_id']]['brif'] = $v['brif'];
            $data[$v['lang_id']]['title'] = $v['title'];
        }

        $listSql = 'SELECT *  FROM ' . DB_PREFIX . 'category_article WHERE article_id=' . $id;
        $listData = $this->articleMod->querySql($listSql);
        foreach ($listData as $k => $v) {
            $cateId[] = $v['cate_id'];
        }

        $cateIds = implode(',', $cateId);
        $cateSql = 'SELECT ac.article_cate_name,a.id FROM ' . DB_PREFIX . 'article_category as a LEFT JOIN ' . DB_PREFIX . 'article_category_lang AS ac ON a.id=ac.article_cate_id WHERE a.id in (' . $cateIds . ') AND ac.lang_id=' . $this->defaulLang;
        $cateData = $this->articleMod->querySql($cateSql);
        if (empty($cateData)) {

            $cateData = array(array());
        }

        /* var_dump($cateData);exit; */
        /* foreach($cateData as $k=>$v){
          $cate_name[]=$v['id'];
          }
          $article_cate_name=implode(',',array_unique($cate_name)); */

        $this->assign('cateData', $cateData);
        foreach ($data as $k => $v) {
            foreach ($v as $key => $value) {
                $data[$k]['title'] = htmlspecialchars($value);
            }
        }
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $this->assign('p', $p);
        $this->assign('data', $data);
        $this->assign('ctglev1', $ctglev1);
        $this->assign('id', $id);
        $this->assign('languageData', $languageData);
        if ($this->lang_id == 1) {
            $this->display('article/articleEdit_1.html');
        } else {
            $this->display('article/articleEdit.html');
        }
    }

    public function doEdit() {
        $langMod = &m('language');
        $article_lang = ($_REQUEST['lang']) ? $_REQUEST['lang'] : '';
        $langList = $langMod->getData('fields=>name,id,logo,shorthand');
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $lang_id = !empty($_REQUEST['lang_id']) ? (int) ($_REQUEST['lang_id']) : '0';
        $storeId = $this->storeId;
        $cPhoto = $_REQUEST['image_id'];
        $isrecom = $_REQUEST['isrecom'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        //$catP = $_REQUEST['cat_p'];
        $catId = !empty($_REQUEST['cate_id']) ? $_REQUEST['cate_id'] : array();
        $id = !empty($_REQUEST['article_id']) ? intval($_REQUEST['article_id']) : '0';
        $categoryArticle = &m('articleCategory');

        foreach ($article_lang as $k => $v) {
            foreach ($langList as $k1 => $v2) {
                if ($k == $v2['id']) {
                    if (empty($v['title'])) {
                        if ($this->lang_id == 1) {
                            $this->setData(array(), $status = '0', $v2['name_en'] . ' Title not null');
                        } else {
                            $this->setData(array(), $status = '0', $v2['name_en'] . ' 标题不能为空');
                        }
                    }

                    if (empty($v['brief'])) {
                        if ($this->lang_id == 1) {
                            $this->setData(array(), $status = '0', $v2['name_en'] . ' brief introduction not null');
                        } else {
                            $this->setData(array(), $status = '0', $v2['name_en'] . ' 简介不能为空');
                        }
                    }
                    if (empty($v['body'])) {
                        if ($this->lang_id == 1) {
                            $this->setData(array(), $status = '0', $v2['name_en'] . ' content not null');
                        } else {
                            $this->setData(array(), $status = '0', $v2['name_en'] . ' 内容不能为空');
                        }
                    }
                }
            }
        }
        foreach ($catId as $k => $v) {
            if ($v == 0) {
                if ($this->lang_id == 1) {
                    $this->setData(array(), $status = '0', ' category not null');
                } else {
                    $this->setData(array(), $status = '0', '分类不能为空');
                }
            }
        }


        if (empty($cPhoto)) {
            if ($this->lang_id == 1) {
                $this->setData(array(), $status = '0', 'Please upload the picture');
            } else {
                $this->setData(array(), $status = '0', '请上传图片');
            }
        }

        $length = count($catId);
        for ($i = 0; $i < $length; $i++) {
            $cate = $catId[$i];
            for ($j = $i + 1; $j < $length; $j++) {
                if ($cate == $catId[$j]) {
                    if ($this->lang_id == 1) {
                        $this->setData(array(), $status = '0', 'Classification has already existed');
                    } else {
                        $this->setData(array(), $status = '0', '分类已存在');
                    }
                }
            }
        }


        // 数据
        $catIds = implode(',', array_unique($catId));


        $data = array(
            'image' => $cPhoto,
            'cat_id' => $catIds,
            'isrecom' => $isrecom,
            'store_id' => $storeId,
            'add_time' => time()
        );

        $res = $this->articleMod->doEdit($id, $data);



        $where = 'article_id =' . $id;
        $categoryArticle->doDrops($where);

        foreach ($catId as $v) {
            $cateData[] = array('article_id' => $id, 'cate_id' => $v);
        }
        foreach ($cateData as $v) {
            $re = $categoryArticle->doInsert($v);
            if (!$re) {
                $this->setData(array(), '0', 'articleCategory');
            }
        }



        $where1 = 'article_id  in(' . $id . ')';
        $ress = $this->articleLangMod->doDrops($where1);
        foreach ($article_lang as $k => $v) {
            $dataLang = array(
                'lang_id' => $k,
                'add_time' => time(),
                'title' => addslashes(trim($v['title'])),
                'body' => addslashes(trim($v['body'])),
                'brif' => addslashes(trim($v['brief'])),
                'article_id' => $id
            );
            $articleinfo = $this->articleLangMod->doInsert($dataLang);
            if (!$articleinfo) {
                $this->setData(array(), '0', 'articleLang');
            }
        }

        if ($res) {
            $info['url'] = "store.php?app=article&act=articleList&lang_id=" . $this->lang_id . '&p=' . $p;
            if ($this->lang_id == 1) {
                $this->setData($info, $status = 1, 'Success of Editors');
            } else {
                $this->setData($info, $status = 1, $a['edit_Success']);
            }
        } else {
            if ($this->lang_id == 1) {
                $this->setData(array(), 0, 'Failor of Editors');
            } else {
                $this->setData(array(), '0', $a['edit_fail']);
            }
        }
    }

    /**
     * 文章信息
     * @author wh
     * @date 2017-8-9
     */
    public function getArticleInfo($title, $lang_id) {

        $sql = 'SELECT  * FROM ' . DB_PREFIX . 'article AS a  LEFT JOIN ' . DB_PREFIX . 'article_lang AS al ON a.id = al.article_id  where al.title=' . $title;

        $data = $this->articleMod->querySql($sql);

        if (!empty($data)) {
            return $data[0]['id'];
        } else {
            return null;
        }
    }

    public function getArticleInfos($ctgLev2, $english_title, $id = 0) {
        $where = '  where 1=1  and store_id =' . $this->storeId;
        if (!empty($id)) {
            $where .= '  and  cat_id =' . $ctgLev2 . '  and  english_title  = "' . $english_title . '"   and  id !=' . $id;
        } else {
            $where .= '  and  cat_id =' . $ctgLev2 . '  and  english_title  = "' . $english_title . '"';
        }
        $sql = 'select  id  from  ' . DB_PREFIX . 'article ' . $where;
        $data = $this->articleMod->querySql($sql);
        if (!empty($data)) {
            return $data[0]['id'];
        } else {
            return null;
        }
    }

    /**
     * 图片上传
     * @author wh
     * @date 2017-8-9
     */
    public function upload() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        if (IS_POST) {
            $fileName = $_FILES['fileName']['name'];
            $type = strtolower(substr(strrchr($fileName, '.'), 1)); //获取文件类型
            $info = array();
            if (!in_array($type, array('jpg', 'png', 'jpeg', 'gif', 'JPG', 'PNG', 'JPEG', 'GIF'))) {
                $this->setData($info, $status = 'error', $a['please_upload']);
            }
            $savePath = "upload/images/article/" . date("Ymd");
            // 判断文件夹是否存在否则创建
            if (!file_exists($savePath)) {
                @mkdir($savePath, 0777, true);
                @chmod($savePath, 0777);
                @exec("chmod 777 {$savePath}");
            }
            $filePath = $_FILES['fileName']['tmp_name']; //文件路径
            $url = $savePath . '/' . time() . '.' . $type;
            if (!is_uploaded_file($filePath)) {
                exit("临时文件错误");
            }
            //上传文件
            if (!move_uploaded_file($filePath, $url)) {
                $this->setData(array(), '0', $a['add_fail']);
            }
            $data = array(
                "name" => $fileName,
                "status" => 1,
                "url" => $url,
                "add_time" => time()
            );
            echo json_encode($data);
            exit;
        } else {
            $this->setData($info = array(), 2, $a['System_error']);
        }
    }

    /**
     * 文章删除
     * @author wh
     * @date 2017-8-9
     */
    public function dele() {
        $this->load($this->lang_id, 'store/store');
        $a = $this->langData;
        $cateMod=&m('categoryArticle');

        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        if (empty($id)) {
            $this->setData(array(), '0', $a['System_error']);
        }
        // 删除表数据
        $where = 'id  in(' . $id . ')';
        $where1 = 'article_id  in(' . $id . ')';
        $where2 = ' article_id in(' . $id . ')';
        $res = $this->articleMod->doDrops($where);

        $info=$this->articleLangMod->doDrops($where1);
        $categoryArticle=&m('articleCategory');
        $data=$categoryArticle->doDrops($where2);
        $awhere='article_id in ('.$id .')';
        $aRes=$cateMod->doDrops($awhere);

        if ($res) {   //删除成功
            $this->setData(array(), '1', $a['delete_Success']);
        } else {
            $this->setData(array(), '0', $a['delete_fail']);
        }
    }

}
