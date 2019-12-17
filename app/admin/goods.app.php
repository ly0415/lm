<?php

//如果需要设置允许所有域名发起的跨域请求，可以使用通配符 *
header("Access-Control-Allow-Origin: *"); // 允许任意域名发起的跨域请求
header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');
header("Access-Control-Allow-Methods:HEAD, GET, POST, DELETE, PUT, OPTIONS");
/**
 * 商品列表
 * @author wh
 * @date 2017-8-7
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class GoodsApp extends BackendApp {

    private $goodMod;
    private $languageMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->goodMod = &m('goods');
        $this->languageMod = &m('language');
    }

    /**
     * 商品列表展示
     * @author wh
     * @date 2017-8-7
     */
    public function goodsIndex() {
        $ctgid = $_REQUEST['ctgid']; //商品分类
        $onsale = $_REQUEST['is_onsale']; //商品状态
        $goodsName = !empty($_REQUEST['goods_name']) ? htmlspecialchars(trim($_REQUEST['goods_name'])) : '';
        $goodsSn = !empty($_REQUEST['goods_sn']) ? htmlspecialchars(trim($_REQUEST['goods_sn'])) : '';
        $brandName = !empty($_REQUEST['brand_name']) ? addslashes(trim($_REQUEST['brand_name'])) : '';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $idArr = array();
        $brandMod = &m('goodsBrand');
        $langMod = &m('language');
        //商品分类选择
        $ctgMod = &m('goodsClass');
        $sql = "select c.`id`,c.parent_id,l.category_name as `name`  from " . DB_PREFIX . "goods_category as c left join " . DB_PREFIX . "goods_category_lang as l on c.id=l.category_id  where l.lang_id=" . $this->lang_id . " order by sort_order  asc";
        $res = $ctgMod->querySql($sql);
        $tree = $this->getTree(0, $res, 1);
        if (!empty($ctgid)) {
            $options = $this->getSeleOptions($tree, $ctgid);
        } else {
            $options = $this->getSeleOptions($tree);
        }

        $this->assign('options', $options);
        $this->assign('goodsName', $goodsName);
        $this->assign('goodsSn', $goodsSn);
        $this->assign('brandName', $brandName);
        $this->assign('onsale', $onsale);
        //
        //获取默认语言
        $lang_info = $langMod->getOne(array("cond" => "is_default=2"));
        $where = '  where 1=1 and l.lang_id=' . $lang_info['id'];
        //获取分类的所有子类
        if (!empty($ctgid)) {
            $data = $this->getChild($ctgid, $res);
            // 一维数组
            foreach ($data as $val) {
                $idArr[] = $val['id'];
            }
            $idArr[] = $ctgid;
            $ids = implode(',', $idArr);
            $where .= '  and  c.id in(' . $ids . ')';
        }
            if (!empty($goodsName)) {
                $where .= '  and  l.goods_name  like  "%' . $goodsName . '%"';
            }
            if (!empty($goodsSn)) {
                $where .= '  and  g.goods_sn  like  "%' . $goodsSn . '%"';
            }

            if (!empty($brandName)) {
                $brandSql = "select distinct brand_id from " . DB_PREFIX . "goods_brand_lang where brand_name like '%" . $brandName . "%'";
                $brandList = $this->goodMod->querySql($brandSql);
                $brandIds = $this->arrayColumn($brandList, "brand_id");
                $brandwhere = implode(",", $brandIds);
                $where .= ' and g.brand_id in (' . $brandwhere . ')';
            }
            if (!empty($onsale)) {
                $where .= '  and  g.`is_on_sale` = ' . $onsale;
            }
        //
        $sql = 'SELECT  g.`goods_id`,g.`cat_id`, g.`goods_sn`,l.`goods_name` ,g.`goods_storage` ,g.`brand_id`,g.`original_img`,g.`lang_id`,
                 g.`shop_price`,g.`is_on_sale`,c.`parent_id_path`
                 FROM  ' . DB_PREFIX . 'goods  AS g
                 LEFT JOIN  ' . DB_PREFIX . 'goods_category  AS c  ON g.`cat_id` = c.`id`
                 LEFT JOIN ' . DB_PREFIX . 'goods_lang AS l ON g.`goods_id` = l.`goods_id`
                 ' . $where;
        $sql .= '  order by  g.goods_id   desc';

        $data = $this->goodMod->querySqlPageData($sql);

        $list = $data['list'];
        foreach ($list as &$val) {
            //商品名称
            $val['gname'] = mb_substr($val['goods_name'], 0, 24, 'utf-8');

            //商品状态
            switch ($val['is_on_sale']) {
                case 1:
                    $val['statusName'] = '上架在售';
                    break;
                case 2:
                    $val['statusName'] = '商品下架';
                    break;
            }
            //分类路径
            $val['ctgpath'] = $this->getCtgPath($val['parent_id_path']);

            //品牌
            if ($val['brand_id']) {
                $brand = $brandMod->getLangData($this->lang_id, $val['brand_id']);
                $val['brand_name'] = stripslashes($brand[0]['name']);
            } else {
                $val['brand_name'] = "--";
            }
        }
        $returnUrl =  urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"]);
        $this->assign('returnUrl',$returnUrl);
        $lanuageMod = &m('language');
        $list_l = $lanuageMod->getData(array("field" => "id,name,logo"));
        $this->assign('lang_list', $list_l);
        $this->assign('list', $list);
        $this->assign('p', $p);
        $this->assign('page_html', $data['ph']);
        $this->display('goods/goodsIndex.html');
    }

    /**
     * 获取分类导航
     * @path   0_2_21_844
     * @author wh
     * @date 2017/07/31
     */
    function getCtgPath($path) {
        if (!empty($path)) {
            $arr = explode('_', $path);
            array_shift($arr);
        }
        $str = implode(',', $arr);
        $ctgMod = &m('goodsClass');
        $sql = 'select   category_name as `name`   from   ' . DB_PREFIX . 'goods_category as g
                left join ' . DB_PREFIX . 'goods_category_lang as l on g.id=l.category_id
              where  g.id in(' . $str . ') and l.lang_id=' . $this->lang_id . " order by g.id ";
        $data = $ctgMod->querySql($sql);
        $ctgpath = array();
        foreach ($data as $val) {
            $ctgpath[] = $val['name'];
        }
        $pathStr = implode(' > ', $ctgpath);
        return $pathStr;
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
    
            $option = '';
            $option .= '<option value="0" >--' . $this->langDataBank->project->select_classification . '--</option>';
            if (is_array($tree)) {
                foreach ($tree as $val) {
                    if ($val['id'] == $selected) {
                        $option .= '<option  selected  value="' . $val['id'] . '" >' . str_repeat('&nbsp;&nbsp;&nbsp;', $val['dep']) . '|—-' . $val['name'] . '</option>';
                    } else {
                        $option .= '<option    value="' . $val['id'] . '" >' . str_repeat('&nbsp;&nbsp;&nbsp;', $val['dep']) . '|—-' . $val['name'] . '</option>';
                    }
                }
            }
            return $option;
    }

    /**
     * 商品下架
     * @author wh
     * @date 2017-8-7
     */
    public function offSale() {
        $goodsId = $_REQUEST['id'];
        $data = array(
            'key' => 'goods_id',
            'is_on_sale' => 2
        );
        $res = $this->goodMod->doEdit($goodsId, $data);
        if ($res) {
            $this->addLog('下架商品');
            $this->setData(array(), '1', $this->langDataBank->project->obtained_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->project->obtained_fail);
        }
    }

    /**
     * 商品下架
     * @author wh
     * @date 2017-8-7
     */
    public function onSale() {
        $goodsId = $_REQUEST['id'];
        $data = array(
            'key' => 'goods_id',
            'is_on_sale' => 1
        );
        $res = $this->goodMod->doEdit($goodsId, $data);
        if ($res) {
            $this->addLog('上架商品');
            $this->setData(array(), '1', $this->langDataBank->project->shelve_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->project->shelve_fail);
        }
    }

    /*
     * 选择语言
     * @author lee
     * @date 2017-9-12 14:37:38
     */

    public function add_language() {
        if ($_POST) {
            $lang_id = $_REQUEST['language_id'];
            header("Location: ?app=goods&act=goodAdd&language_id=" . $lang_id);
        } else {
            $lanuageMod = &m('language');
            $list = $lanuageMod->getData(array("field" => "id,name,logo"));
            $this->assign('lang_list', $list);
            $this->display("goods/language.html");
        }
    }

    /*
     * 添加商品
     */

    public function goodAdd() {
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $this->assign('p', $p);
        //$cat_list=$catMod->getData(array('cond'=>"parent_id = 0",'fields'=>"id,name"));
        $catSql = "select c.id,l.category_name as `name` from " . DB_PREFIX . "goods_category as c left join " . DB_PREFIX . "goods_category_lang as l on c.id=l.category_id where c.parent_id =0 and l.lang_id=" . $this->lang_id;
        $cat_list = $this->goodMod->querySql($catSql);
        $brandSql = "select c.id,l.brand_name as `name` from " . DB_PREFIX . "goods_brand as c left join " . DB_PREFIX . "goods_brand_lang as l on c.id=l.brand_id where l.lang_id=" . $this->lang_id;
        $brand_list = $this->goodMod->querySql($brandSql);
        $styleSql = "select c.id ,l.style_name from " . DB_PREFIX . "goods_style as c left join " . DB_PREFIX . "goods_style_lang as l on c.id=l.style_id where l.lang_id=" . $this->lang_id;
        $style_list = $this->goodMod->querySql($styleSql);
        $typeSql = "select c.id ,l.type_name as `name` from " . DB_PREFIX . "goods_type as c left join " . DB_PREFIX . "goods_type_lang as l on c.id=l.type_id where c.mark=1 and  l.lang_id=" . $this->lang_id;
        $type_list = $this->goodMod->querySql($typeSql);
        $storeCateMod = &m('storeCate');
        $cateSql = "select SC.`id`,SCL.`cate_name`  from  " . DB_PREFIX . "store_cate AS SC LEFT JOIN " . DB_PREFIX . "store_cate_lang
        AS SCL ON SC.id = SCL.cate_id where SCL.lang_id = " . $this->lang_id . " and  SC.is_open=1";
        $store_cate = $storeCateMod->querySql($cateSql);
        $lanuageMod = &m('language');
        $lang_list = $lanuageMod->getData(array("field" => "id,name,logo", "cond" => "enable=1"));
        $this->assign('lang_list', $lang_list);
        $this->assign('store_cate', $store_cate);
        $this->assign('style_list', $style_list);
        $this->assign('type_list', $type_list);
        $this->assign('cat_list', $cat_list);
        $this->assign('brand_list', $brand_list);
        $this->display('goods/productadd.html');

    }

    /*
     * 获取商品分类
     * @author lee
     * @date 2017-8-8 15:00:31
     */

    public function getCategory() {
        $parent_id = !empty($_REQUEST['parent_id']) ? $_REQUEST['parent_id'] : 0;
        $mod = &m('goodsClass');
        $sql = "select c.id,l.category_name as `name` from " . DB_PREFIX . "goods_category as c left join " . DB_PREFIX . "goods_category_lang as l on   c.id=l.category_id  where c.parent_id = {$parent_id} and l.lang_id=" . $this->lang_id;
        $list = $mod->querySql($sql);
        if ($list) {
            $this->setData($list, $status = 1);
        } else {
            $this->setData(array(), $status = 0);
        }
    }

    /*
     * 获取业务类型
     * @author  lee
     * @date 2017-9-14 19:30:01
     */

    public function getRooms() {
        $cate_id = !empty($_REQUEST['cate_id']) ? $_REQUEST['cate_id'] : 0;
        $mod = &m('roomType');
        $sql = "select r.id,rl.type_name as room_name from " . DB_PREFIX . "room_type as r
        left join " . DB_PREFIX . "room_category as c on r.id=c.room_type_id
        left join " . DB_PREFIX . "room_type_lang as rl on r.id=rl.type_id
        where c.category_id=" . $cate_id . " and rl.lang_id=" . $this->lang_id;
        $list = $mod->querySql($sql);
        if ($list) {
            $this->setData($list, $status = 1);
        } else {
            $this->setData(array(), $status = 0);
        }
    }

    /**
     * 获取上传页面
     * @auth wanyan
     * @date 2017-08-07
     */
    public function upload() {
        $num = !empty($_REQUEST['num']) ? intval($_REQUEST['num']) : '0';
        $input = !empty($_REQUEST['input']) ? $_REQUEST['input'] : '';
        $path = !empty($_REQUEST['path']) ? $_REQUEST['path'] : 'temp';
        $func = !empty($_REQUEST['func']) ? $_REQUEST['func'] : 'undefined';
        $info = array(
            'num' => $num,
            'title' => '',
            'fileList' => '',
            'size' => '4M',
            'type' => 'jpg,png,gif,jpeg',
            'input' => $input,
            'func' => empty($func) ? 'undefined' : $func,
        );
//        print_r($info);die;
        $this->assign('info', $info);
        $this->display('goods/upload.html');
    }

    /**
     * 获取上传页面
     * @auth wanyan
     * @date 2017-08-07
     */
    public function uploadfy() {
        $title = 'banners';
        $fileName = $_FILES['file']['name'];
        $type = strtolower(substr(strrchr($fileName, '.'), 1)); //获取文件类型
        if (!in_array($type, array('jpg', 'png', 'jpeg', 'gif', 'JPG', 'PNG', 'JPEG', 'GIF', 'jpg!pm'))) {
            $state = 'ERROR' . $this->langDataBank->project->image_format;
        }
        //var_dump($type);die;
        $savePath = "upload/images/goods/" . date("Ymd") . mt_rand(10, 100);
        // 判断文件夹是否存在否则创建
        if (!file_exists($savePath)) {
            @mkdir($savePath, 0777, true);
            @chmod($savePath, 0777);
            @exec("chmod 777 {$savePath}");
        }
        $filePath = $_FILES['file']['tmp_name']; //文件路径
        $url = $savePath . '/' . time() . '.' . $type;
        if (!is_uploaded_file($filePath)) {
            $state = 'ERROR' . $this->langDataBank->project->upload_error;
        }
        //上传文件
        if (!move_uploaded_file($filePath, $url)) {
            $state = 'ERROR' . '';
        } else {
            $state = 'SUCCESS';
        }
        $return_data['title'] = $title;
        $return_data['original'] = ''; // 这里好像没啥用 暂时注释起来
        $return_data['state'] = $state;
        $return_data['path'] = 'goods';
        $return_data['url'] = $url;
        echo json_encode($return_data);
    }

    /**
     * 获取上传页面
     * @auth wanyan
     * @date 2017-08-07
     */
    public function delUpload() {
        $action = !empty($_REQUEST['action']) ? htmlspecialchars($_REQUEST['action']) : '';
        //$filename= I('filename');
        $filename = !empty($_REQUEST['filename']) ? htmlspecialchars($_REQUEST['filename']) : '';
        $filename = str_replace('../', '', $filename);
        $filename = trim($filename, '.');
        $filename = trim($filename, '/');
        if ($action == 'del' && !empty($filename) && file_exists($filename)) {
            $size = getimagesize($filename);
            $filetype = explode('/', $size['mime']);
            if ($filetype[0] != 'image') {
                exit;
            }
            if (unlink($filename)) {
                echo 1;
            } else {
                echo 0;
            }
            exit;
        }
    }

    /*
     * 商品添加
     * @author lee
     * @date 2017-8-8 16:57:26
     */

    public function doAdd() {

        $this->goodMod->sql_b_spec("INSERT INTO " . DB_PREFIX . "data_log ( type, res_data, add_user, add_time ) VALUES (1 ,'" . serialize($_REQUEST) . "', " . $this->accountId . " ," . time() . ")");

        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $goods_lang = ($_POST['lang']) ? $_POST['lang'] : '';
        $cat_id = ($_POST['cat_id']) ? $_POST['cat_id'] : '';
        $brand_id = ($_POST['brand_id']) ? intval($_POST['brand_id']) : '';
//        $goods_remark = ($_POST['goods_remark']) ? $_POST['goods_remark'] : '';
        $market_price = ($_POST['market_price']) ? trim($_POST['market_price']) : '';
        $shop_price = ($_POST['shop_price']) ? trim($_POST['shop_price']) : '';
        $cost_price = ($_POST['cost_price']) ? trim($_POST['cost_price']) : '';
        $goods_sn = ($_POST['goods_sn']) ? trim($_POST['goods_sn']) : '';
        $spu = ($_POST['spu']) ? trim($_POST['spu']) : '';
        $sku = ($_POST['sku']) ? trim($_POST['sku']) : '';
        $suppliers_id = ($_POST['suppliers_id']) ? $_POST['suppliers_id'] : 0;
        $commission = ($_POST['commission']) ? trim($_POST['commission']) : 0;
        $original_img = ($_REQUEST['original_img']) ? $_REQUEST['original_img'] : '';
//        $weight=($_POST['weight'])?trim($_POST['weight']):'';
        $store_count = ($_POST['store_count']) ? trim($_POST['store_count']) : 0;
        $is_free_shipping = ($_POST['is_free_shipping']) ? trim($_POST['is_free_shipping']) : 2;
        $keywords = ($_POST['keywords']) ? trim($_POST['keywords']) : '';
        $goods_content = ($_POST['goods_content']) ? $_POST['goods_content'] : '';
        $goods_images = ($_POST['goods_images']) ? $_POST['goods_images'] : '';
        $item_arr = ($_POST['item']) ? $_POST['item'] : '';
        $item_img = ($_POST['item_img']) ? $_POST['item_img'] : '';
        $goods_type = ($_REQUEST['goods_type']) ? $_REQUEST['goods_type'] : 0;
        $style_id = is_numeric($_POST['style_id']) ? $_POST['style_id'] : 0;
        $room_id = ($_POST['room_id']) ? $_POST['room_id'] : 0;
        $auxiliary_type = ($_POST['auxiliary_type']) ? $_POST['auxiliary_type'] : '';
        $is_limit = ($_POST['is_limit']) ? $_POST['is_limit'] : '';
        $store_cate = ($_POST['store_cate']) ? $_POST['store_cate'] : '';
        $auxiliary_class = ($_POST['area_id']) ? $_POST['area_id'] : '';
        $returnUrl=!empty($_POST['returnUrl']) ? $_POST['returnUrl'] : '';
        $deduction=!empty($_POST['deduction']) ? $_POST['deduction'] : '';
        $goods_storage=!empty($_POST['goods_storage']) ? $_POST['goods_storage'] : '';
        $attributes = !empty($_REQUEST['attributes']) ? $_REQUEST['attributes'] : '';
        $bar_code   = !empty($_REQUEST['bar_code']) ? $_REQUEST['bar_code'] : '';

        $delivery_fee = 0;
        $attributes_str = '';
        for($i=0; $i<count($attributes);$i++ ){
            $attributes_str.=$attributes[$i].",";
        }
        $attributes_str=substr($attributes_str,0,-1);




    
 
        // by xt
        if (in_array(2, explode(',', $attributes_str))) {
            $delivery_fee = $_REQUEST['delivery_fee'] ?: 0;
        }

        $goodMod = &m('goods');
        $langList = $this->languageMod->getLanguage();
        foreach ($goods_lang as $k => $v) {
            foreach ($langList as $k1 => $v2) {
                if ($k == $v2['id']) {
                    if (empty($v['goods_name'])) {
                        $this->setData(array(), $status = '0', $v2['name_en'] . $this->langDataBank->project->good_required);
                    }
                }
            }
        }

        if (empty($cat_id)) {
            $this->setData(array(), $status = '0', $this->langDataBank->project->select_categories);
        }
        if (empty($room_id)) {
            $this->setData(array(), $status = '0', '请选择业务类型'); 
        }
        if (empty($market_price)) {
            $this->setData(array(), $status = '0', $this->langDataBank->project->market_required);
        }
        if (empty($shop_price)) {
            $this->setData(array(), $status = '0', $this->langDataBank->project->shop_required);
        }
        if (empty($original_img)) {
            $this->setData(array(), $status = '0', $this->langDataBank->project->tick_country);
        }
        if(empty($deduction)){
            $this->setData(array(),$status='0',$this->langDataBank->project->select_deduction);
        }
//        if(empty($store_count)){
//            $this -> setData(array(),$status = '0',$message = '总库存不能为空！');
//        }
        //获取品牌名称
        $goodBrandMod = &m('goodsBrand');
        $goodsLangMod = &m('goodsLang');
        $brand_info = $goodBrandMod->getOne(array("cond" => "id=" . $brand_id));
        if ($item_arr){
            foreach($item_arr as $k=>$v){
                $storage +=$v['store_count'];
            }
            $goods_storage=$storage;
        }
        //
        if(empty($goods_storage)){
            $goods_storage=0;
        }
        $data = array(
            'goods_name' => $goods_lang[$this->lang_id]['goods_name'],
            'cat_id' => $cat_id,
            'brand_id' => $brand_id,
            'brand_name' => $brand_info['name'],
//            'goods_remark' => $goods_remark,
            'market_price' => $market_price,
            'shop_price' => $shop_price,
            'cost_price' => $cost_price,
            'spu' => $spu,
            'sku' => $sku,
            'suppliers_id' => $suppliers_id,
            'commission' => $commission,
            'original_img' => $original_img,
//            'weight'=>$weight,
            'goods_storage' => $store_count,
            'is_free_shipping' => $is_free_shipping,
//            'keywords' => $keywords,
//            'goods_content' => $goods_content,
            'goods_type' => $goods_type,
            'auxiliary_class' => $auxiliary_class,
            'auxiliary_type' => $auxiliary_type,
            'style_id' => $style_id,
            'room_id' => $room_id,
            'deduction'=>$deduction,
            'attributes'=>$attributes_str,
            'delivery_fee' => $delivery_fee,
            'goods_storage'=>$goods_storage,
            'bar_code'=>$bar_code,
        );
//        echo '<pre>';print_r($data);die;
        //判断是否选择区域
        if ($is_limit == 2) {
            if (empty($store_cate)) {
                $this->setData(array(), $status = '0', $this->langDataBank->project->select_regional_country);
            }
            $data['store_cate_ids'] = implode(',', $store_cate);
        } else {
            $data['store_cate_ids'] = '';
        }

        $goodImgMod = &m('goodsImg');
        $goodSpecPriceMod = &m('goodsSpecPrice');
        // if ($_POST['goods_id']) {
        //     $goodMod->doEdit($_POST['goods_id'], array('delivery_fee' => 0));
        // }

        // $res = $goodMod->doEdit($_POST['goods_id'], ['delivery_fee' => 0]);
        // die;
        // $goodMod->doEdit($_POST['goods_id'], $data);
        // echo '<pre>';print_r($data);die;
        if ($_POST['goods_id']) {
            $good_id = $_POST['goods_id'];
            $code = $this->goodsZcode($good_id);
            $cond['code_url'] = $code;
            $goodMod->doEdit($good_id, $cond);
            if ($goods_type == 0) {
                //删除原规格
                $this->delAttr($good_id);
            }
            unset($data['lang_id']);
            $res = $goodMod->doEdit($good_id, $data);
            // echo '<pre>';print_r($data);die;
            if($deduction==1){
                $storeGoodMod=&m('areaGood');
                $updateSql="update bs_store_goods set goods_storage = {$goods_storage} where goods_id=".$good_id;
               $res= $storeGoodMod->doEditSql($updateSql);
            }

        } else {
            $good_id = $goodMod->doInsert($data);
            //生成二维码
            $code = $this->goodsZcode($good_id);
            $cond['code_url'] = $code;
            $goodMod->doEdit($good_id, $cond);
        }

        if ($good_id || $res) {
            // 商品货号
            if (empty($goods_sn)) {
                $goods_sn = "AM" . str_pad($good_id, 7, "0", STR_PAD_LEFT);
            } else {
                $sql = "select 1 from bs_goods where goods_sn='" . $goods_sn . "' and goods_id !=" . $good_id;
                $has = $goodMod->querySql($sql);
                if ($has) {
                    $this->setData(array(), $status = '0', $this->langDataBank->project->number_exist);
                }
            }
            //处理语言包
            //删除原有语言模板
            $this->delGoodsLang($good_id);
            foreach ($goods_lang as $k => $v) {
                $arr = array(
                    'goods_id' => $good_id,
                    'goods_name' => $v['goods_name'],
                    'keywords' => $v['keywords'],
                    'goods_remark' => $v['goods_remark'],
                    'goods_content' => $v['goods_content'],
                    'lang_id' => $k
                );
                $re = $goodsLangMod->doInsert($arr);
            }
            //商品图片
            if ($goods_images) {
                //删除原始图片
                $this->delImg($good_id);
                foreach ($goods_images as $k => $v) {
                    if ($v) {
                        $arr = array("goods_id" => $good_id, "image_url" => $v);
                        $goodImgMod->doInsert($arr);
                    }
                }
            }

	   //判断规格有无变化
            $check_spec     = !empty($_REQUEST['check_spec']) ? $_REQUEST['check_spec'] : '';
            //商品规格
            if ($item_arr) {
                //删除原规格
                $this->delAttr($good_id);
                $sql = "INSERT INTO bs_goods_spec_price ( GOODS_ID, `key`, KEY_NAME, PRICE, goods_storage, SKU, bar_code  ) VALUES ";
                foreach ($item_arr as $k => $v) {
                    //$arr=array("'key'"=>$k,'key_name'=>$v['key_name'],'price'=>$v['price'],'store_count'=>$v['store_count'],'sku'=>$v['sku'],'goods_id'=>$good_id);
                    $sql .= "( $good_id, '" . $k . "', '" . $v['key_name'] . "'," . $v['price'] . " , " . $v['store_count'] . ", '" . $v['sku'] . "', '" . $v['bar_code'] . "' ),";
                }
                $sql = substr($sql, 0, strlen($sql) - 1);
                $res = $goodSpecPriceMod->sql_b_spec($sql); //规格表
            } 
 	 if($check_spec){
                $storeGoodSpecPriceMod=&m('storeGoodsSpecPrice');
                $storeSql="SELECT id FROM bs_store_goods where mark = 1 AND goods_id=".$good_id;
                $storeGoodsData=$storeGoodSpecPriceMod->querySql($storeSql);
                if($storeGoodsData){
                    $this->delStoreAttr($good_id);
                    foreach($storeGoodsData as $key=>$val){
                        $storesql = "INSERT INTO bs_store_goods_spec_price ( GOODS_ID, `key`, KEY_NAME, PRICE, goods_storage, SKU, bar_code,store_goods_id ) VALUES ";
                        foreach ($item_arr as $k => $v) {
                            //$arr=array("'key'"=>$k,'key_name'=>$v['key_name'],'price'=>$v['price'],'store_count'=>$v['store_count'],'sku'=>$v['sku'],'goods_id'=>$good_id);
                            $storesql .= "( $good_id, '" . $k . "', '" . $v['key_name'] . "'," . $v['price'] . " , " . $v['store_count'] . ", '" . $v['sku'] . "', '" . $v['bar_code'] . "',".$val['id']." ),";
                        }
                        $storesql = substr($storesql, 0, strlen($storesql) - 1);
                        $storeGoodSpecPriceMod->sql_b_spec($storesql); //规格表
                    }
                }
            }

   	   $all_spec = $goodSpecPriceMod->getData(array("cond" => "goods_id=" . $good_id));
            if($deduction==1 && empty($check_spec)){
                foreach($all_spec as $value){
                    //修改同步扣除情况的商品规格内的价格和库存
                    $updateSql = "update bs_store_goods_spec_price as a set a.goods_storage = {$value['goods_storage']},a.price = {$value['price']},a.sku = '{$value['sku']}',a.bar_code = '{$value['bar_code']}' where a.goods_id = ".$good_id." AND a.key = '{$value['key']}'";
                    $goodSpecPriceMod->doEditSql($updateSql);
                }
            }elseif($deduction==2 && empty($check_spec)){
                foreach($all_spec as $value){
                    //修改分开扣除情况的商品规格内的条形码
                    $goodSpecPriceMod->doEditSql("update bs_store_goods_spec_price as a set a.bar_code = '{$value['bar_code']}'  where a.goods_id = ".$good_id." AND a.key = '{$value['key']}'");
                }
            }
            //规格图片
            if ($item_img) {
                //删除原始规格图片
                $this->delAttrImg($good_id);
                foreach ($item_img as $k2 => $v2) {
                    if ($v2) {
                        $sql2 = "INSERT INTO bs_goods_spec_image ( goods_id,spec_image_id, src) VALUES ( $good_id, '" . $k2 . "' , '" . $v2 . "' )";
                        $goodSpecPriceMod->sql_b_spec($sql2); //规格表
                    }
                }
            }

            //删除关联数据
            $this->goodMod->sql_b_spec("delete from " . DB_PREFIX . "goods_auxiliary_class where goods_sn = '" . $goods_sn."'");

            if($auxiliary_type){
                //增加关联数据
                $aaaa = explode(':', $auxiliary_type);
                $bbbb = explode(':', $auxiliary_class);
                foreach ($aaaa as $key => $value){

                    $this->goodMod->sql_b_spec("INSERT INTO " . DB_PREFIX . "goods_auxiliary_class ( goods_sn, cate_id, business_id ) VALUES ('" . $goods_sn . "', " . $bbbb[$key] . " ," . $value . ")");
                }
                if(!in_array($room_id, $aaaa)){

                    $this->goodMod->sql_b_spec("INSERT INTO " . DB_PREFIX . "goods_auxiliary_class ( goods_sn, cate_id, business_id ) VALUES ('" . $goods_sn . "' , " . $bbbb[$key] . " ," . $room_id . ")");
                }
            } else {
                $this->goodMod->sql_b_spec("INSERT INTO " . DB_PREFIX . "goods_auxiliary_class ( goods_sn, cate_id, business_id ) VALUES ('" . $goods_sn . "' , " . $cat_id . " ," . $room_id . ")");
            }


            //商品属性
            $r1 = $this->saveGoodsAttr($good_id, $goods_type);
            $r2 = $goodMod->doEdit($good_id, array('goods_sn' => $goods_sn));
            //修改区域端商品
            $updateSql = "update bs_store_goods set goods_name = '{$goods_lang[$this->lang_id]['goods_name']}',goods_sn = '{$goods_sn}',bar_code = '{$bar_code}',cat_id = {$cat_id},room_id = {$room_id},market_price = '{$market_price}',original_img = '{$original_img}' where goods_id = ".$good_id." AND mark = 1";
            $goodMod->doEditSql($updateSql);  
            $this->addLog("商品添加");
            if(!empty($returnUrl)){
                $info['url']=$returnUrl;
            }else{
                $info['url'] = "admin.php?app=goods&act=goodsIndex&p={$p}";
            }
            $this->setData($info, $status = '1', $this->langDataBank->public->success);
        } else {
            $this->setData(array(), $status = '0', $this->langDataBank->public->fail);
        }
    }



/*        public function updateUrl(){
            $sql="select goods_id from bs_goods";
            $goodData=$this->goodMod->querySql($sql);

            foreach($goodData as $key=>$val){

                $code = $this->goodsZcode($val['goods_id']);

                $cond['code_url'] = 12334;
                $this->goodMod->doEdit($val['goods_id'], $cond);

            }


            echo 1231;


        }*/
    /*
     * 根据语言ID获取语言名称
     * @author wanyan
     * @date 2018-03-06 10:41:33
     */

    public function getLanguageById($lang_id) {
        
    }

    /*
     * 删除商品源多语言
     * @author lee
     * @date 2017-12-11 10:41:33
     */

    public function delGoodsLang($goods_id) {
        $sql = "delete from " . DB_PREFIX . "goods_lang where goods_id=" . $goods_id;
        $this->goodMod->sql_b_spec($sql);
    }

    /*
     * 删除原图片
     * @author lee
     * @2017-8-10 15:24:13
     */

    public function delImg($good_id) {
        $goodImgMod = &m('goodsImg');
        $list = $goodImgMod->getData(array("cond" => "goods_id=" . $good_id));
        foreach ($list as $k => $v) {
            $file = SITE_URL . "/" . $v['image_url'];
            @unlink($file);
        }
        $goodImgMod->doDrops("goods_id=" . $good_id);
    }

    /*
     * 删除原规格
     */

    public function delAttr($good_id) {
        $goodSpecPriceMod = &m('goodsSpecPrice');
        $sql = "delete from " . DB_PREFIX . "goods_spec_price where goods_id=" . $good_id;
        $goodSpecPriceMod->sql_b_spec($sql);
    }

    public  function delStoreAttr($good_id){
        $storeGoodSpecPriceMod=&m('storeGoodsSpecPrice');
        $sql = "delete from " . DB_PREFIX . "store_goods_spec_price where goods_id=" . $good_id;

        $storeGoodSpecPriceMod->sql_b_spec($sql);
    }

    /*
     * 删除原规格图片
     */

    public function delAttrImg($good_id) {
        $goodSpecPriceMod = &m('goodsSpecPrice');
        $sql1 = "select * from " . DB_PREFIX . "goods_spec_image where goods_id=" . $good_id;
        $list = $goodSpecPriceMod->sql_b_spec($sql1);
        foreach ($list as $k => $v) {
            $file = SITE_URL . "/" . $v['src'];
            @unlink($file);
        }
        $sql2 = "delete from " . DB_PREFIX . "goods_spec_image where goods_id=" . $good_id;
        $goodSpecPriceMod->sql_b_spec($sql2);
    }

    /*
     * 编辑商品
     * @author lee
     * @date 2017-8-10 09:26:18
     */

    public function editGood() {
        $id = ($_REQUEST['goods_id']) ? $_REQUEST['goods_id'] : 0;
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $returnUrl=!empty($_REQUEST['returnUrl']) ? $_REQUEST['returnUrl'] : '';
        $this->assign('returnUrl',$returnUrl);
        $goodMod = &m('goods');
        $catMod = &m('goodsClass');
        $brandMod = &m('goodsBrand');
        $goodImg = &m('goodsImg');
        $typeMod = &m('goodsType');
        $styleMod = &m('goodsStyle');
        $roomMod = &m('roomTypeCate');
        $goodsLangMod = &m('goodsLang');
     
        $cond       = array("cond" => "goods_id=" . $id);
        $info       = $goodMod->getOne($cond);
        $info_lang  = $goodsLangMod->getData($cond);
        //业务类型
        $sql = "select r.id,l.type_name as room_name from " . DB_PREFIX . "room_type as r
               left join " . DB_PREFIX . "room_category as c on r.id=c.room_type_id
               left join " . DB_PREFIX . "room_type_lang as l on l.type_id=r.id
               where c.category_id=" . $info['cat_id'] . " and l.lang_id=" . $this->lang_id;
        $room_list = $roomMod->querySql($sql);
        $brand_list = $brandMod->getLangData($this->lang_id);
        $img_arr = $goodImg->getData(array('cond' => "goods_id=" . $info['goods_id']));
        $type_list = $typeMod->getLangData($this->lang_id);
        $style_list = $styleMod->getLangData($this->lang_id);

 
        //校验分类与业务类型是否存在关联，不存在重新编辑
        $retation_data = $roomMod -> getData(array('cond' => " category_id = " . $info['cat_id'] ." AND room_type_id = " . $info['room_id']));
        if($retation_data){
            $cate3 = $catMod->getOne(array("cond" => "id=" . $info['cat_id']));         //三级分类ID
            $cate2 = $catMod->getOne(array("cond" => "id=" . $cate3['parent_id']));     //二级分类ID
            $cate1 = $catMod->getOne(array("cond" => "id=" . $cate2['parent_id']));     //一级分类ID
            $this->assign('cat_1', $cate1['id']);
            $this->assign('cat_2', $cate2['id']);
        }

        //编辑分类聚焦
        $cat_list_1 = $catMod->getLangData(0, $this->lang_id);
        $cat_list_2 = $catMod->getLangData($cate1['id'], $this->lang_id);
        $cat_list_3 = $catMod->getLangData($cate2['id'], $this->lang_id); 

        $business_list  = $roomMod->querySql("select cate_id,business_id from bs_goods_auxiliary_class where business_id != ".$info['room_id']." AND goods_id = ".$info['goods_id']);
        if($business_list){
            foreach ($business_list as $k => $v) {
                $business_info  = $roomMod->querySql("select id,name from bs_business where id = ".$v['business_id']);
                $auxiliary[$k]['type_name']         = $business_info[0]['name'];
                $cate_3 = $catMod->getOne(array("fields" =>"id category_id,name category_name,parent_id", "cond" => "id=" . $v['cate_id']));            //三级分类ID
                $cate_2 = $catMod->getOne(array("fields" =>"id category_id,name category_name,parent_id", "cond" => "id=" . $cate_3['parent_id']));     //二级分类ID
                $cate_1 = $catMod->getOne(array("fields" =>"id category_id,name category_name", "cond" => "id=" . $cate_2['parent_id']));               //一级分类ID
                $auxiliary[$k]['auxiliary_list']    = $cate_1;
                $auxiliary[$k]['auxiliary_lists']   = $cate_2;
                $auxiliary[$k]['auxiliary_liste']   = $cate_3;
                $cate[] = $v['cate_id'];
                $buss[] = $v['business_id'];
            }
            $this->assign('ch_scope',           implode(':', $cate));
            $this->assign('auxiliary_type',     implode(':', $buss));
            $this->assign('auxiliary',          array_values($auxiliary));
        }

        $attributes_arr = explode(",", $info['attributes']);
        foreach ($attributes_arr as $k => $v) {
            if($v ==1){
                $attributes_1=$v;
            }else if($v ==2){
                $attributes_2=$v;
            }else if($v ==3){
                $attributes_3=$v;
            }else if($v ==4){
                $attributes_4=$v;
            }
        }
//        print_r($attributes_arr);exit;
        $this->assign('attributes_1', $attributes_1);
        $this->assign('attributes_2', $attributes_2);
        $this->assign('attributes_3', $attributes_3);
        $this->assign('attributes_4', $attributes_4);
        //区域选择判断 modify by lee 2017-11-16 11:18:44
        if ($info['store_cate_ids']) {
            $info['is_limit'] = 2;
        } else {
            $info['is_limit'] = 1;
        }
        $storeCateMod = &m('storeCate');
        $cateSql = "select SC.`id`,SCL.`cate_name`  from  " . DB_PREFIX . "store_cate AS SC LEFT JOIN " . DB_PREFIX . "store_cate_lang
        AS SCL ON SC.id = SCL.cate_id where SCL.lang_id = " . $this->lang_id . " and  SC.is_open=1";
        $store_cate = $storeCateMod->querySql($cateSql);
        $this->assign('store_cate', $store_cate);
        //end

        $lanuageMod = &m('language');
        $lang_list = $lanuageMod->getData(array("field" => "id,name,logo"));
        foreach ($lang_list as $k => $v) {
            foreach ($info_lang as $k1 => $v1) {
                if ($v['id'] == $v1['lang_id']) {
                    $lang_list[$k]['info'] = $v1;
                }
            }
        }

        //判断当前商品有无规格
        $is_spec = $goodMod->querySql("SELECT count('*') is_spec FROM bs_goods_spec_price where goods_id=".$id);
        $this->assign('is_spec', $is_spec[0]);

        $this->assign('lang_list', $lang_list);
        $this->assign('deduction',$info['deduction']);
        $this->assign('room_list', $room_list);
        $this->assign('style_list', $style_list);
        $this->assign('type_list', $type_list);
        $this->assign('cat_list_1', $cat_list_1);
        $this->assign('cat_list_class', $cat_list_1);
        $this->assign('cat_list_2', $cat_list_2);
        $this->assign('cat_list_3', $cat_list_3);
        $this->assign('brand_list', $brand_list);
        $this->assign('img_arr', $img_arr);
        $this->assign('info', $info);
        $this->assign('p', $p);
        $this->display('goods/productedit.html');
    }

    /**
     * 获取分类名称
     * @author wang'shuo    
     * @date 2018-4-03
     */
    public function getCategoryLang($id, $lang) {
        $catlangMod = &m('goodsClassLang');
        $sql = "select `category_id`,`category_name` from " . DB_PREFIX . "goods_category_lang where `category_id`='{$id}' and lang_id = '{$lang}'";
        $rs = $catlangMod->querySql($sql);
        return $rs;
    }

    /**
     *  给指定商品添加属性 或修改属性 更新到 tp_goods_attr
     * @param int $goods_id  商品id
     * @param int $goods_type  商品类型id
     */
    public function saveGoodsAttr($goods_id, $goods_type) {
        $GoodsAttr = &m('goodsAttri');
        //$Goods = M("Goods");
        // 属性类型被更改了 就先删除以前的属性类型 或者没有属性 则删除
        if ($goods_type == 0) {
//            $GoodsAttr->where('goods_id = '.$goods_id)->delete();
            $sql = "delete from " . DB_PREFIX . "goods_attr where goods_id=" . $goods_id;
            $res = $GoodsAttr->sql_b_spec($sql);
            return;
        }
        $sql = "select * from " . DB_PREFIX . "goods_attr where goods_id=" . $goods_id;
        $GoodsAttrList = $GoodsAttr->querySql($sql);
//        $GoodsAttrList = $GoodsAttr->where('goods_id = '.$goods_id)->select();


        $old_goods_attr = array(); // 数据库中的的属性  以 attr_id _ 和值的 组合为键名
        foreach ($GoodsAttrList as $k => $v) {
            $old_goods_attr[$v['attr_id'] . '_' . $v['attr_value']] = $v;
        }
        //  $this->pre($old_goods_attr);exit;
        // post 提交的属性  以 attr_id _ 和值的 组合为键名
        $post_goods_attr = array();
        $post = $_POST;
        foreach ($post as $k => $v) {
            $attr_id = str_replace('attr_', '', $k);
            if (!strstr($k, 'attr_'))
                continue;
            foreach ($v as $k2 => $v2) {
                $v2 = str_replace('_', '', $v2); // 替换特殊字符
                $v2 = str_replace('@', '', $v2); // 替换特殊字符
                $v2 = trim($v2);

                if (empty($v2))
                    continue;

                $sql = "INSERT INTO " . DB_PREFIX . "goods_attr ( goods_id,attr_id, attr_value) VALUES ( $goods_id, '" . $attr_id . "' , '" . $v2 . "' )";
                $GoodsAttr->sql_b_spec($sql);
            }
        }
        //file_put_contents("b.html", print_r($post_goods_attr,true));
        // 没有被 unset($old_goods_attr[$tmp_key]); 掉是 说明 数据库中存在 表单中没有提交过来则要删除操作
        foreach ($old_goods_attr as $k => $v) {
            $sql = "delete from " . DB_PREFIX . "goods_attr where goods_attr_id=" . $v['goods_attr_id'];
            $res = $GoodsAttr->sql_b_spec($sql);
        }
    }

    /**
     * 动态获取商品规格选择框 根据不同的数据返回不同的选择框
     */
    public function ajaxGetSpecSelect() {
        $goods_id = !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
        $spec_type = !empty($_REQUEST['spec_type']) ? intval($_REQUEST['spec_type']) : 0;
        $lang_id = !empty($_REQUEST['lang_id']) ? intval($_REQUEST['lang_id']) : 0;
        // $GoodsLogic = new GoodsLogic();
        //$_GET['spec_type'] =  13;
        $sql_spec = "select g.`id`,g.`type_id`,g.`order`,l.`spec_name` as `name` from " . DB_PREFIX . "goods_spec as g
                     left join " . DB_PREFIX . "goods_spec_lang as l on g.id=l.spec_id
                    where `type_id` = '{$spec_type}' and l.lang_id=" . $this->lang_id;
        $specInfo = $this->goodMod->querySql($sql_spec);
        // $specList = M('Spec')->where("type_id = ".I('get.spec_type/d'))->order('`order` desc')->select();
        foreach ($specInfo as $k => $v) {
            $sql_item_spec = "select g.`id`,l.`item_name` as `item` from " . DB_PREFIX . "goods_spec_item as g
                              left join " . DB_PREFIX . "goods_spec_item_lang as l on g.id=l.item_id
                             where g.`spec_id` = '{$v['id']}' and l.lang_id=" . $this->lang_id . " order by g.`id`";
            $spec_item = $this->goodMod->querySql($sql_item_spec);
            $specInfo[$k]['spec_item'] = $spec_item; // 获取规格项
        }

        $sql_spec_goods_price = "select `key` AS items_id  from " . DB_PREFIX . "goods_spec_price where `goods_id` = '{$goods_id}'";
        $items_id = $this->goodMod->querySql($sql_spec_goods_price);
        $res_item = "";

        foreach ($items_id as $k => $v) {
            $res_item .= $v['items_id'] . "_";
        }

        $res = substr($res_item, 0, strlen($res_item) - 1);
        $items_ids = explode('_', $res);

//        $items_id = M('SpecGoodsPrice')->where('goods_id = '.$goods_id)->getField("GROUP_CONCAT(`key` SEPARATOR '_') AS items_id");
//        $items_ids = explode('_', $items_id);
//
//        // 获取商品规格图片
        if ($goods_id) {
            $sql_image = "select `spec_image_id`,`src` from " . DB_PREFIX . "goods_spec_image where `goods_id` = '{$goods_id}'";
            $specImageList = $this->goodMod->querySql($sql_image);
//            $specImageList = M('SpecImage')->where("goods_id = $goods_id")->getField('spec_image_id,src');
//            $specImgArr=array();
//
            foreach ($specImageList as $k => $v) {
                $specImgArr[$v['spec_image_id']] = $v;
//                $specImgArr[$v['spec_image_id']]['src']="/".$v['src'];
            }
        }
        $this->assign('goods_id', $goods_id);
        $this->assign('specImageList', $specImgArr);
        $this->assign('items_ids', $items_ids);
        $this->assign('specList', $specInfo);
        $this->display('goods/ajax_spec_select.html');
    }

    /**
     * 动态获取商品规格输入框 根据不同的数据返回不同的输入框
     */
    public function ajaxGetSpecInput() {
        //$GoodsLogic = new GoodsLogic();
        $goods_id = !empty($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0;
        $spec_arr = !empty($_REQUEST['spec_arr']) ? $_REQUEST['spec_arr'] : array(array());
        $str = $this->getSpecInput($goods_id, $spec_arr);
        exit($str);
    }

    /**
     * 获取 tp_spec_item表 指定规格id的 规格项
     * @param int $spec_id 规格id
     * @return array 返回数组
     */
    public function getSpecItem($spec_id) {
        $model = M('SpecItem');
        $arr = $model->where("spec_id = $spec_id")->order('id')->select();
        $arr = get_id_val($arr, 'id', 'item');
        return $arr;
    }

    /**
     * 获取 规格的 笛卡尔积
     * @param $goods_id 商品 id
     * @param $spec_arr 笛卡尔积
     * @return string 返回表格字符串
     */
    public function getSpecInput($goods_id, $spec_arr) {
        // 排序
        foreach ($spec_arr as $k => $v) {
            $spec_arr_sort[$k] = count($v);
        }
        asort($spec_arr_sort);
        foreach ($spec_arr_sort as $key => $val) {
            $spec_arr2[$key] = $spec_arr[$key];
        }
        $clo_name = array_keys($spec_arr2);
        $spec_arr2 = combineDika($spec_arr2); //  获取 规格的 笛卡尔积
        // 获取所有规格表
        $specMod = &m('goodsSpec');
        $specInfo = $specMod->getLangData($this->lang_id);
        foreach ($specInfo as $k => $v) {
            $spec[$v['id']] = $v['name'];
        }
        //$spec = M('Spec')->getField('id,name'); // 规格表
        // 获取所有的规格项
        $sql_item_spec = "select g.*,l.`item_name` as `item` from " . DB_PREFIX . "goods_spec_item as g
                              left join " . DB_PREFIX . "goods_spec_item_lang as l on g.id=l.item_id
                             where l.lang_id=" . $this->lang_id;
        $specItemInfo = $this->goodMod->sql_b($sql_item_spec);
        foreach ($specItemInfo as $k => $v) {
            $specItem[$v['id']]['id'] = $v['id'];
            $specItem[$v['id']]['item'] = $v['item'];
            $specItem[$v['id']]['spec_id'] = $v['spec_id'];
        }
        //$specItem = M('SpecItem')->getField('id,item,spec_id');//规格项
        // 获取所有的规格项图片
        $sql_image = "select `key`,key_name,price,goods_storage,bar_code,sku from " . DB_PREFIX . "goods_spec_price where `goods_id` = '{$goods_id}'";
        $keySpecGoodsPriceInfo = $this->goodMod->querySql($sql_image);
        //  print_r($keySpecGoodsPriceInfo);exit;
        //$keySpecGoodsPrice = M('SpecGoodsPrice')->where('goods_id = '.$goods_id)->getField('key,key_name,price,store_count,bar_code,sku');//规格项
        foreach ($keySpecGoodsPriceInfo as $k => $v) {
            $keySpecGoodsPrice[$v['key']]['key'] = $v['key'];
            $keySpecGoodsPrice[$v['key']]['key_name'] = $v['key_name'];
            $keySpecGoodsPrice[$v['key']]['price'] = $v['price'];
            $keySpecGoodsPrice[$v['key']]['store_count'] = $v['goods_storage'];
            $keySpecGoodsPrice[$v['key']]['bar_code'] = $v['bar_code'];
            $keySpecGoodsPrice[$v['key']]['sku'] = $v['sku'];
        }
        $str = "<table class='table table-bordered' id='spec_input_tab'>";
        $str .= "<tr>";
        // 显示第一行的数据
        foreach ($clo_name as $k => $v) {
            if ($v) {
                $str .= " <td><b>{$spec[$v]}</b></td>";
            } else {
                $str .= "  <td><b> </b></td>";
            }
        }
        if ($this->lang_id == 1) {
            $str .= "<td><b>Price</b></td>
               <td><b>Stock</b></td>
               <td><b>SKU</b></td>
	      <td><b>条形码</b></td>
             </tr>";
        } else {
            $str .= "<td><b>" . $this->langDataBank->public->price . "</b></td>
               <td><b>" . $this->langDataBank->public->stock . "</b></td>
               <td><b>SKU</b></td>
	      <td><b>条形码</b></td>
             </tr>";
        }

        // 显示第二行开始
        foreach ($spec_arr2 as $k => $v) {
            $str .= "<tr>";
            $item_key_name = array();
            foreach ($v as $k2 => $v2) {

                $str .= "<td>{$specItem[$v2][item]}</td>";
                $item_key_name[$v2] = $spec[$specItem[$v2]['spec_id']] . ':' . $specItem[$v2]['item'];
            }
            ksort($item_key_name);
            $item_key = implode('_', array_keys($item_key_name));
            $item_name = implode(' ', $item_key_name);
 
            $keySpecGoodsPrice[$item_key][price] ? false : $keySpecGoodsPrice[$item_key][price] = 0; // 价格默认为0
            $keySpecGoodsPrice[$item_key][store_count] ? false : $keySpecGoodsPrice[$item_key][store_count] = 0; //库存默认为0
            $str .= "<td><input name='item[$item_key][price]' value='{$keySpecGoodsPrice[$item_key][price]}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")' /></td>";
            $str .= "<td><input name='item[$item_key][store_count]' value='{$keySpecGoodsPrice[$item_key][store_count]}' onkeyup='this.value=this.value.replace(/[^\d.]/g,\"\")' onpaste='this.value=this.value.replace(/[^\d.]/g,\"\")'/></td>";
            $str .= "<td><input name='item[$item_key][sku]' value='{$keySpecGoodsPrice[$item_key][sku]}' /><input type='hidden' name='item[$item_key][key_name]' value='$item_name' /></td>";
    	   $str .= "<td><input name='item[$item_key][bar_code]' value='{$keySpecGoodsPrice[$item_key][bar_code]}' /></td>";
            $str .= "</tr>";
        }
        $str .= "</table>"; 
        return $str;
    }

    /**
     * 动态获取商品属性输入框 根据不同的数据返回不同的输入框类型
     */
    public function ajaxGetAttrInput() {
        $goods_id = !empty($_REQUEST['goods_id']) ? $_REQUEST['goods_id'] : 0;
        $type_id = !empty($_REQUEST['type_id']) ? $_REQUEST['type_id'] : '0';
        $str = $this->getAttrInput($goods_id, $type_id);
        exit($str);
    }

    /**
     * 动态获取商品属性输入框 根据不同的数据返回不同的输入框类型
     * @param int $goods_id 商品id
     * @param int $type_id 商品属性类型id
     */
    public function getAttrInput($goods_id, $type_id) {
        header("Content-type: text/html; charset=utf-8");
        $attrMod = &m('goodsAttri');
        //$sql_spec = "select * from ".DB_PREFIX."goods_attribute where `type_id` = '{$type_id}'";
        $attributeList = $attrMod->getLangData($type_id, $this->lang_id);
//        $GoodsAttribute = D('GoodsAttribute');
//        $attributeList = $GoodsAttribute->where("type_id = $type_id")->select();
        foreach ($attributeList as $key => $val) {

            $curAttrVal = $this->getGoodsAttrVal(NULL, $goods_id, $val['attr_id']);
            //促使他 循环
            if (count($curAttrVal) == 0)
                $curAttrVal[] = array('goods_attr_id' => '', 'goods_id' => '', 'attr_id' => '', 'attr_value' => '', 'attr_price' => '');

            foreach ($curAttrVal as $k => $v) {
                $str .= "<tr class='attr_{$val['attr_id']}'>";
                $addDelAttr = ''; // 加减符号
                // 单选属性 或者 复选属性
                if ($val['attr_type'] == 1 || $val['attr_type'] == 2) {
                    if ($k == 0)
                        $addDelAttr .= "<a onclick='addAttr(this)' href='javascript:void(0);'>[+]</a>&nbsp&nbsp";
                    else
                        $addDelAttr .= "<a onclick='delAttr(this)' href='javascript:void(0);'>[-]</a>&nbsp&nbsp";
                }

                $str .= "<td>$addDelAttr {$val['attr_name']}</td> <td>";

                // if($v['goods_attr_id'] > 0) //tp_goods_attr 表id
                //     $str .= "<input type='hidden' name='goods_attr_id[]' value='{$v['goods_attr_id']}'/>";
                // 手工录入
                if ($val['attr_input_type'] == 0) {
                    $str .= "<input type='text' size='40' value='" . ($goods_id ? $v['attr_value'] : $val['attr_values']) . "' name='attr_{$val['attr_id']}[]' />";
                }
                // 从下面的列表中选择（一行代表一个可选值）
                if ($val['attr_input_type'] == 1) {
                    $str .= "<select name='attr_{$val['attr_id']}[]'>";
                    $tmp_option_val = explode(PHP_EOL, $val['attr_values']);
                    foreach ($tmp_option_val as $k2 => $v2) {
                        // 编辑的时候 有选中值
                        $v2 = preg_replace("/\s/", "", $v2);
                        if ($v['attr_value'] == $v2)
                            $str .= "<option selected='selected' value='{$v2}'>{$v2}</option>";
                        else
                            $str .= "<option value='{$v2}'>{$v2}</option>";
                    }
                    $str .= "</select>";
                    //$str .= "属性价格<input type='text' maxlength='10' size='5' value='{$v['attr_price']}' name='attr_price_{$val['attr_id']}[]'>";
                }

                // 多行文本框
                if ($val['attr_input_type'] == 2) {
                    $str .= "<textarea cols='40' rows='3' name='attr_{$val['attr_id']}[]'>" . ($goods_id ? $v['attr_value'] : $val['attr_values']) . "</textarea>";
                    //$str .= "属性价格<input type='text' maxlength='10' size='5' value='{$v['attr_price']}' name='attr_price_{$val['attr_id']}[]'>";
                }

                $str .= "</td></tr>";
                //$str .= "<br/>";
            }
        }
        return $str;
    }

    /**
     * 获取 tp_goods_attr 表中指定 goods_id  指定 attr_id  或者 指定 goods_attr_id 的值 可是字符串 可是数组
     * @param int $goods_attr_id tp_goods_attr表id
     * @param int $goods_id 商品id
     * @param int $attr_id 商品属性id
     * @return array 返回数组
     */
    public function getGoodsAttrVal($goods_attr_id = 0, $goods_id = 0, $attr_id = 0) {
        if ($goods_attr_id > 0) {
            $sql_spec = "select * from " . DB_PREFIX . "goods_attr where `goods_attr_id` = '{$goods_attr_id}'";
            $attributeList = $this->goodMod->querySql($sql_spec);
            return $attributeList;
        }
        if ($goods_id > 0 && $attr_id > 0) {
            $sql_spec = "select * from " . DB_PREFIX . "goods_attr where `goods_id` = '{$goods_id}' and `attr_id` = '{$attr_id}'";
            $attributeList = $this->goodMod->querySql($sql_spec);
            return $attributeList;
        }
    }





}
