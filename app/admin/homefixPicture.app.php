<?php

/**
 * 商品列表
 * @author gao
 * @date 2018-11-1
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class HomefixPictureApp extends BackendApp {

    private $homefixPictureMod;
    private $pictureImagesMod;
    private $lang_id;
    private  $systemTypeMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->homefixPictureMod = &m('homefixPicture');
        $this->pictureImagesMod = &m('pictureImages');
        $this->systemTypeMod=&m('systemType');
        $this->lang_id=29;
    }

    //美图列表
    public function pictureIndex() {
        $onsale = $_REQUEST['is_onsale'];
        $pictureName = !empty($_REQUEST['picture_name']) ? htmlspecialchars(trim($_REQUEST['picture_name'])) : '';
        $area = !empty($_REQUEST['area']) ? htmlspecialchars(trim($_REQUEST['area'])) : '';
        $style = !empty($_REQUEST['style']) ? htmlspecialchars(trim($_REQUEST['style'])) : '';
        $list=$this->homefixPictureMod->getList($pictureName,$onsale,$area,$style);
        $area_list=$this->systemTypeMod->typeData(1);//空间数据
        $style_list=$this->systemTypeMod->typeData(2);
        foreach($list as $k=>$v){
                $areaData=$this->systemTypeMod->typeData(1,$v['area_id']);
                $styleData=$this->systemTypeMod->typeData(2,$v['style_id']);
                $list[$k]['area_name']=$areaData[0]['title'];
                $list[$k]['style_name']=$styleData[0]['title'];
        }
        $this->assign('area',$area);
        $this->assign('onsale',$onsale);
        $this->assign('style',$style);
        $this->assign('pictureName',$pictureName);
        $this->assign('area_list',$area_list);
        $this->assign('style_list',$style_list);
        $this->assign('list',$list);
        $this->display('picture/pictureIndex.html');
    }


    /**
     * 商品下架
     * @author wh
     * @date 2017-8-7
     */
    public function offSale() {
        $pictureId = $_REQUEST['id'];
        $data = array(
         'mark'=>2
        );
        $res = $this->homefixPictureMod->doEdit($pictureId, $data);
        if ($res) {

            $this->setData(array(), '1', '下架成功');
        } else {
            $this->setData(array(), '0', '下架失败');
        }
    }

    /**
     * 商品下架
     * @author wh
     * @date 2017-8-7
     */
    public function onSale() {
        $pictureId = $_REQUEST['id'];
        $data = array(
            'mark' => 1
        );
        $res = $this->homefixPictureMod->doEdit($pictureId, $data);
        if ($res) {
            $this->setData(array(), '1', '上架成功');
        } else {
            $this->setData(array(), '0', '上架失败');
        }
    }



    /*
     * 添加美图
     */
    public function pictureAdd() {
        $area_list=$this->systemTypeMod->typeData(1);//空间数据
        $style_list=$this->systemTypeMod->typeData(2);
        $this->assign('area_list',$area_list);
        $this->assign('style_list',$style_list);
        $this->display('picture/pictureAdd.html');

    }
    //编辑美图
 public  function editPicture(){
        $id=!empty($_REQUEST['id'])? $_REQUEST['id']:0;
        $area_list=$this->systemTypeMod->typeData(1);//空间数据
        $style_list=$this->systemTypeMod->typeData(2);
        $pictureData=$this->homefixPictureMod->getOne(array("cond"=>"id=".$id));
        $area_name=$this->systemTypeMod->getOne(array("cond"=>"id=".$pictureData['area_id']));
        $style_name=$this->systemTypeMod->getOne(array("cond"=>"id=".$pictureData['style_id']));

       $img_arr=$this->pictureImagesMod->getData(array('cond' => "picture_id=" . $id));
        $this->assign('img_arr',$img_arr);
        $this->assign('pictureData',$pictureData);
        $this->assign('area_list',$area_list);
        $this->assign('style_list',$style_list);
        $this->assign('area_name',$area_name);
        $this->assign('style_name',$style_name);
        $this->display('picture/pictureEdit.html');
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
        if ($this->lang_id == 1) {
            $this->display('goods/upload_1.html');
        } else {
            $this->display('goods/upload.html');
        }
    }

    /**
     * 获取上传页面
     * @auth wanyan
     * @date 2017-08-07
     */
    public function uploadfy() {
        $this->load($this->lang_id, 'admin/admin');
        $a = $this->langData;
        $title = 'banners';
        $fileName = $_FILES['file']['name'];
        $type = strtolower(substr(strrchr($fileName, '.'), 1)); //获取文件类型
        if (!in_array($type, array('jpg', 'png', 'jpeg', 'gif', 'JPG', 'PNG', 'JPEG', 'GIF', 'jpg!pm'))) {
            $state = 'ERROR' . $a['img_format'];
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
            $state = 'ERROR' . $a['img_upload'];
        }
        //上传文件
        if (!move_uploaded_file($filePath, $url)) {
            $state = 'ERROR' . $a[''];
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
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $original_img = ($_REQUEST['original_img']) ? $_REQUEST['original_img'] : '';
        $goods_images = ($_POST['goods_images']) ? $_POST['goods_images'] : '';
        $style_id = $_POST['style_id'] ? $_POST['style_id'] : 0;
        $area_id = ($_POST['area_id']) ? intval($_POST['area_id']) : 0;
        $picture_name=($_REQUEST['picture_name']) ? $_REQUEST['picture_name'] : '';
        $designer_id=($_REQUEST['designer_id']) ? $_REQUEST['designer_id'] : 0;
        $returnUrl=!empty($_POST['returnUrl']) ? $_POST['returnUrl'] : '';
    /*    if(empty($original_img)){
            $this->setData(array(),0,"请上传家装美图封面图片");
        }
        if(empty($style_id)){
            $this->setData(array(),0,"请选择风格");
        }
        if(empty($area_id)){
            $this->setData(array(),0,"请选择空间");
        }
        if(empty($designer_id)){
            $this->setData(array(),0,"请选择设计师");
        }*/
        $data=array(
            'name'=>$picture_name,
            'area_id'=>$area_id,
            'style_id'=>$style_id,
            'designer_id'=>$designer_id,
            'face_img'=>$original_img,
            'store_id'=>0,
            'add_user'=>$this->accountId,
        );
        if($_POST['picture_id']){

            if ($goods_images) {
                //删除原始图片
                $this->delImg($_POST['picture_id']);
                foreach ($goods_images as $k => $v) {
                    if ($v) {
                        $arr = array("picture_id" => $_POST['picture_id'], "img_url" => $v);
                        $this->pictureImagesMod->doInsert($arr);
                    }
                }
            }
            $data['upd_time']=time();

            $res= $this->homefixPictureMod->doEdit($_POST['picture_id'],$data);
        }else{
            $data['add_time']=time();
            $res=  $this->homefixPictureMod->doInsert($data);
            foreach ($goods_images as $k => $v) {
                if ($v) {
                    $arr = array("picture_id" => $res, "img_url" => $v);
                    $this->pictureImagesMod->doInsert($arr);
                }
            }
        }
        $info['url'] = "admin.php?app=homefixPicture&act=pictureIndex";
         if($res){
             $this->setData($info, $status = '1','保存成功');
         } else{
             $this->setData(array(), $status = '0','保存失败');
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

    public function delImg($picture_id) {

        $list = $this->pictureImagesMod->getData(array("cond" => "picture_id=" . $picture_id));
        foreach ($list as $k => $v) {
            $file = SITE_URL . "/" . $v['image_url'];
            @unlink($file);
        }
        $this->pictureImagesMod->doDrops("picture_id=" . $picture_id);
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
        $cond = array("cond" => "goods_id=" . $id);
        $info = $goodMod->getOne($cond);
        $info_lang = $goodsLangMod->getData($cond);
        //业务类型
        $sql = "select r.id,l.type_name as room_name from " . DB_PREFIX . "room_type as r
               left join " . DB_PREFIX . "room_category as c on r.id=c.room_type_id
               left join " . DB_PREFIX . "room_type_lang as l on l.type_id=r.id
               where c.category_id=" . $info['cat_id'] . " and l.lang_id=" . $this->lang_id;
        $room_list = $roomMod->querySql($sql);
        $class = $catMod->getOne(array("cond" => "id=" . $info['cat_id']));
        $cat_arr = explode("_", $class['parent_id_path']);
        $cat_list_1 = $catMod->getLangData(0, $this->lang_id);
        $cat_list_2 = $catMod->getLangData($cat_arr[1], $this->lang_id);
        $cat_list_3 = $catMod->getLangData($cat_arr[2], $this->lang_id);
        $brand_list = $brandMod->getLangData($this->lang_id);
        $img_arr = $goodImg->getData(array('cond' => "goods_id=" . $info['goods_id']));
        $type_list = $typeMod->getLangData($this->lang_id);
        $style_list = $styleMod->getLangData($this->lang_id);
        $ch_scope = $info['auxiliary_class'];
        $this->assign('ch_scope', $ch_scope);
        $auxiliary_arr = explode(":", $info['auxiliary_class']);
        foreach ($auxiliary_arr as $k => $v) {
            $cat_arrs = explode("_", $v);
            $auxiliary_class = $catMod->getOne(array("cond" => "id=" . $cat_arrs[3]));
            $cat_arre = explode("_", $auxiliary_class['parent_id_path']);
            $auxiliary_list_1 = $this->getCategoryLang($cat_arre[1], $this->lang_id);
            $auxiliary_list_2 = $this->getCategoryLang($cat_arre[2], $this->lang_id);
            $auxiliary_list_3 = $this->getCategoryLang($cat_arre[3], $this->lang_id);
            $auxiliary[$k]['auxiliary_list'] = $auxiliary_list_1[0];
            $auxiliary[$k]['auxiliary_lists'] = $auxiliary_list_2[0];
            $auxiliary[$k]['auxiliary_liste'] = $auxiliary_list_3[0];
        }
        $this->assign('auxiliary', $auxiliary);
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
        $this->assign('lang_list', $lang_list);
        $this->assign('deduction',$info['deduction']);
        $this->assign('room_list', $room_list);
        $this->assign('style_list', $style_list);
        $this->assign('type_list', $type_list);
        $this->assign('cat_1', $cat_arr[1]);
        $this->assign('cat_2', $cat_arr[2]);
        $this->assign('cat_list_1', $cat_list_1);
        $this->assign('cat_list_class', $cat_list_1);
        $this->assign('cat_list_2', $cat_list_2);
        $this->assign('cat_list_3', $cat_list_3);
        $this->assign('brand_list', $brand_list);
        $this->assign('img_arr', $img_arr);
        $this->assign('info', $info);


        $this->assign('p', $p);
        if ($this->lang_id == 1) {
            $this->display('goods/productedit_1.html');
        } else {
            $this->display('goods/productedit.html');
        }
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
        if ($lang_id) {
            $this->display('goods/ajax_spec_select_1.html');
        } else {
            $this->display('goods/ajax_spec_select.html');
        }
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
             </tr>";
        } else {
            $str .= "<td><b>价格</b></td>
               <td><b>库存</b></td>
               <td><b>SKU</b></td>
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
            $str .= "<td><input name='item[$item_key][sku]' value='{$keySpecGoodsPrice[$item_key][sku]}' />
                <input type='hidden' name='item[$item_key][key_name]' value='$item_name' /></td>";
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
