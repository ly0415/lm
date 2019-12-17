<?php

/**
 * 商品分类模块
 * @author wh
 * @date 2017-7-20
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class goodsClassApp extends BackendApp {

    private $goodsClassMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->goodsClassMod = &m('goodsClass');
    }

    /**
     * 商品分类展示
     * @author wh
     * @date 2017-7-31
     */
    public function classIndex() {
        $goodsCategoryMod=&m('goodsCategory');
        $rs=$goodsCategoryMod->getRelationDatas();

    /*   $cates = $this->getParent();
        $sql = "SELECT  c.`id`,l.`category_name`,c.`parent_id`,c.`sort_order`,c.`image`,c.`add_time`   FROM  " . DB_PREFIX . "goods_category  AS c
                 LEFT JOIN   " . DB_PREFIX . "goods_category_lang  l  ON  c.id = l.`category_id`  where   l.lang_id =" . $this->lang_id . '  order by  c.`sort_order`';
        $res = $this->goodsClassMod->querySql($sql);
        foreach ($res as $k => $v) {
            $res[$k]['add_time'] = !empty($v['add_time']) ? date('Y-m-d H:i:s', $v['add_time']) : '';
        }
        $rs = $this->getTree($res, $pid = 0);

        $this->assign('cates', $cates);*/

        $this->assign('res', $rs);
        $this->display('goodsClass/goodsClass.html');
    }

    /**
     * 无限递归，获取分类树
     * @author xiayy
     * @date 2016-10-08
     */
    public function getTree($list, $pid = 0) {
        $tree = array();
        foreach ($list as $v) {
            if ($v['parent_id'] == $pid) {
////                $v['category_name'] = $v['category_name'];
                if ($this->getTree($list, $v['id'])) {
                    $v['child'] = $this->getTree($list, $v['id']);
                }
                $tree[] = $v;
            }
        }
        return $tree;
    }

    /**
     * 商品分类添加页面
     * @author wh
     * @date 2017-7-31
     */
    public function classAdd() {
        //多语言版本
        $langMod = &m('language');
        $sql = 'select  id,name  from   ' . DB_PREFIX . 'language';
        $data = $langMod->querySql($sql);
        $html = '';
        foreach ($data as $val) {
            $html .= ' <span class="mt10 mb5 inblock">' . $val['name'] . '：</span><input type="text" class="form-control" name="name[' . $val['id'] . ']" >';
        }
        //
        $cates = $this->getParent();
        $this->assign('act', 'classIndex');
        $this->assign('cates', $cates);
        $this->assign('html', $html);
        $this->display('goodsClass/classAdd.html');
    }

    /**
     * 获取商品一级分类
     * @author wanyan
     * @date 2017-8-1
     */
    public function getParent($id) {
        if ($id) {
            $where = ' c.parent_id =0  and  l.lang_id = ' . $this->lang_id . ' and  c.id  not in(' . $id . ')';
        } else {
            $where = ' c.parent_id =0  and  l.lang_id = ' . $this->lang_id;
        }
        $sql = "SELECT  c.`id`,l.`category_name`   FROM  " . DB_PREFIX . "goods_category  AS c
                 LEFT JOIN   " . DB_PREFIX . "goods_category_lang  l   ON c.id = l.`category_id`  where " . $where;
        $res = $this->goodsClassMod->querySql($sql);
        return $res;
    }

    /**
     * 获取商品子分类
     * @author wanyan
     * @date 2017-8-1
     */
    public function getCityAjaxData() {
        $pro_id = !empty($_REQUEST['pro_id']) ? intval($_REQUEST['pro_id']) : '0';
        $sql = "SELECT  c.`id`,l.`category_name`   FROM  " . DB_PREFIX . "goods_category  AS c
                 LEFT JOIN   " . DB_PREFIX . "goods_category_lang  l   ON c.id = l.`category_id`  where c.parent_id =" . $pro_id . "  and  l.lang_id =" . $this->lang_id;
        $res = $this->goodsClassMod->querySql($sql);
        echo json_encode($res);
        exit;
    }

    /**
     * 分类添加功能
     * @author wanyan
     * @date 2017-8-1
     */
    public function doAdd() {
        $name = !empty($_REQUEST['name']) ? $_REQUEST['name'] : array();
        $pro_id = !empty($_REQUEST['pro_id']) ? intval($_REQUEST['pro_id']) : '0';  //一级分类
        $city_id = !empty($_REQUEST['city_id']) ? intval($_REQUEST['city_id']) : '0';  //二级分类
//        $is_show = !empty($_REQUEST['is_show']) ? intval($_REQUEST['is_show']) :'0' ;
        $image_id = !empty($_REQUEST['image_id']) ? htmlspecialchars($_REQUEST['image_id']) : '';
        $image_id2 = !empty($_REQUEST['image_id2']) ? htmlspecialchars($_REQUEST['image_id2']) : '';
        $sort_order = !empty($_REQUEST['sort_order']) ? intval($_REQUEST['sort_order']) : '';

        //判断数据
        foreach ($name as $key => $val) {
            $name[$key] = trim($val);
        }
        foreach ($name as $val) {
            if (empty($val)) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->project->category_required);
                break;
            } else {
//                $aff = $this->getOneInfo($val);
//                if (!empty($aff)) {
//                    $this->setData($info = array(), $status = '0', $a['good_class_names']);
//                    break;
//                }
            }
        }
        //
        if (empty($image_id)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->picture_name_required);
        }
        if (empty($image_id2)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->upload_ad_picture);
        }
        if (empty($sort_order)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->sort_required);
        }

        if (empty($pro_id) && empty($city_id)) {
            $level = 1;
            $parent_id = 0;
        }
        if (!empty($pro_id) && empty($city_id)) {
            $level = 2;
            $parent_id = $pro_id;
        }
        if (!empty($pro_id) && !empty($city_id)) {
            $level = 3;
            $parent_id = $city_id;
        }
        $insertData = array(
            'parent_id' => $parent_id,
            'level' => $level,
            'sort_order' => $sort_order,
            'image' => $image_id,
            'adv_img' => $image_id2,
            'add_time' => time()
        );
        $goodsCategoryMod=&m('goodsCategory');
        $insert_id = $goodsCategoryMod->doInsert($insertData);

        if ($insert_id) {
            //添加物品分类的路径
            $aff_id = $this->pathEdit($insert_id, $pro_id, $city_id);
            if ($aff_id) {
                //添加多语言版本信息
                $this->doLangData($name, $insert_id);
                //
                $this->addLog('分类添加操作');
                $info['url'] = "admin.php?app=goodsClass&act=classIndex";
                $this->setData($info, $status = '1', $this->langDataBank->public->add_success);
            } else {
                $this->setData($info, $status = '0', $this->langDataBank->public->add_error);
            }
        } else {
            $this->setData($info, $status = '0', $this->langDataBank->public->add_error);
        }
    }

    /**
     * 获取分类信息
     * @author wanyan
     * @date 2017-8-1
     */
    public function getOneInfo($name, $id = 0) {
        $gCLangMod = &m('goodsClassLang');
        $where = '  where 1=1';
        if (empty($id)) {
            //添加
            $where .= '  and  category_name = "' . $name . '"';
        } else {
            //编辑
            $where .= '  and   id!=' . $id . '  and  category_name = "' . $name . '"';
        }
        $sql = 'select id  from  ' . DB_PREFIX . 'goods_category_lang' . $where;
        $res = $gCLangMod->querySql($sql);
        return $res;
    }

    /**
     * 添加多语言版本信息
     */
    public function doLangData($name, $insert_id) {
        $gCLangMod = &m('goodsClassLang');
        $goodsCategoryMod=&m('goodsCategory');
        $data = array();
        foreach ($name as $key => $val) {
            $data[] = array(
                'lang_id' => $key,
                'category_name' => $val,
                'category_id' => $insert_id,
                'add_time' => time()
            );
        }
        // 循环插入数据
        foreach ($data as $v) {
            $res = $gCLangMod->doInsert($v);
            if ($res) {
                continue;
            } else {
                return false;
                break;
            }
        }
        $goodsCategoryMod->_cacheReset($insert_id);
        return true;
    }

    /**
     * 更新导航路径
     * @author wanyan
     * @date 2017-8-1
     */
    public function pathEdit($id, $pro_id, $city_id) {
        if (empty($pro_id) && empty($city_id)) {
            $parent_id_path = "0_" . $id;
        }
        if (!empty($pro_id) && empty($city_id)) {
            $parent_id_path = "0_" . $pro_id . '_' . $id;
        }
        if (!empty($pro_id) && !empty($city_id)) {
            $parent_id_path = "0_" . $pro_id . '_' . $city_id . '_' . $id;
        }
        $affData = array(
            'parent_id_path' => $parent_id_path,
            'modify_time' => time()
        );
        $affId = $this->goodsClassMod->doEdit($id, $affData);
        return $affId;
    }

    /**
     * 分类编辑页面
     * @author wanyan
     * @date 2017-8-1
     */
    public function classEdit() {
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '0';
        //多语言版本
        $gCLangMod = &m('goodsClassLang');
        $sql = 'select  id,lang_id,category_name,category_id  from   ' . DB_PREFIX . 'goods_category_lang  where  category_id =' . $id;
        $data = $gCLangMod->querySql($sql);
        $langMod = &m('language');
        $sql = 'select  id,name  from   ' . DB_PREFIX . 'language';
        $res = $langMod->querySql($sql);
        $html = '';
        foreach ($res as $key => $val) {
            foreach ($data as $item) {
                if ($val['id'] == $item['lang_id']) {
                    $html .= ' <span class="mt10 mb5 inblock">' . $val['name'] . '：</span>
                               <input type="text" class="form-control" name="name[' . $item['lang_id'] . ']"  value="' . $item['category_name'] . '"  >';
                    unset($res[$key]);
                }
            }
        }
        //如果以后再添加新的语言
        if (!empty($res)) {
            foreach ($res as $v) {
                $html .= ' <span class="mt10 mb5 inblock">' . $v['name'] . '：</span>
                               <input type="text" class="form-control" name="name[' . $v['id'] . ']"  value=""  >';
            }
        }
        $this->assign('html', $html);
        //获取具体的分类信息
        $query = array(
            'cond' => "`id`='{$id}'"
        );
        $r = $this->goodsClassMod->getOne($query);
        $parent_id_path = explode('_', $r['parent_id_path']);
        if ($r['level'] == 1) {
            $this->assign('parent_id', '0');
            $this->assign('sub_id', '0');
        } elseif ($r['level'] == 2) {
            $this->assign('parent_id', $parent_id_path[1]);
            $this->assign('sub_id', '0');
        } else {
            $this->assign('parent_id', $parent_id_path[1]);
            $this->assign('sub_id', $parent_id_path[2]);
        }
        //二级分类
        $this->assign('subClass', $this->getSub());
        //一级分类
        $cates = $this->getParent($id);
        $this->assign('cates', $cates);
        //
        $this->assign('res', $r);
        $this->assign('act', 'classIndex');
        $this->assign('id', $id);
        $this->display('goodsClass/classEdit.html');
    }

    /**
     * 获取子分类
     * @author wanyan
     * @date 2017-8-1
     */
    public function getSub() {
        $sql = "SELECT  c.`id`,l.`category_name`   FROM  " . DB_PREFIX . "goods_category  AS c
                 LEFT JOIN   " . DB_PREFIX . "goods_category_lang  l   ON c.id = l.`category_id`  where c.level =2  and  l.lang_id =" . $this->lang_id;
        $res = $this->goodsClassMod->querySql($sql);
        return $res;
    }

    /**
     * 分类编辑功能
     * @author wanyan
     * @date 2017-8-1
     */
    public function doEdit() {
        $id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : ''; //分类id
        $name = !empty($_REQUEST['name']) ? $_REQUEST['name'] : array();
        $pro_id = !empty($_REQUEST['pro_id']) ? intval($_REQUEST['pro_id']) : '0';
        $city_id = !empty($_REQUEST['city_id']) ? intval($_REQUEST['city_id']) : '0';
//        $is_show = !empty($_REQUEST['is_show']) ? intval($_REQUEST['is_show']) :'0' ;
        $image_id = !empty($_REQUEST['image_id']) ? htmlspecialchars($_REQUEST['image_id']) : '';
        $image_id2 = !empty($_REQUEST['image_id2']) ? htmlspecialchars($_REQUEST['image_id2']) : '';
        $sort_order = !empty($_REQUEST['sort_order']) ? intval($_REQUEST['sort_order']) : '';


        //判断数据
        foreach ($name as $key => $val) {
            $name[$key] = trim($val);
        }
        foreach ($name as $val) {
            if (empty($val)) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->project->category_required);
                break;
            } else {
                //
//                $ctglandId = $this->getCtgLang($val, $id);  //category_lang 表里的id
//                $aff = $this->getOneInfo($val, $ctglandId); //判断是否命名重复
//                if (!empty($aff)) {
//                    $this->setData($info = array(), $status = '0', $a['good_class_names']);
//                    break;
//                }
            }
        }
        //
        if (empty($sort_order)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->sort_required);
        }
        if (empty($image_id)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->picture_name_required);
        }
        if (empty($image_id2)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->upload_ad_picture);
        }
        //
        if (empty($pro_id) && empty($city_id)) {
            $level = 1;
            $parent_id = 0;
        }
        if (!empty($pro_id) && empty($city_id)) {
            $level = 2;
            $parent_id = $pro_id;
        }
        if (!empty($pro_id) && !empty($city_id)) {
            $level = 3;
            $parent_id = $city_id;
        }
        $data = array(
            'parent_id' => $parent_id,
            'level' => $level,
            'sort_order' => $sort_order,
            'image' => $image_id,
            'adv_img' => $image_id2,
        );
        $goodsCategoryMod=&m('goodsCategory');
        $edit_id = $goodsCategoryMod->doEdit($id, $data);
        if ($edit_id) {
            //添加物品分类的路径
            $aff_id = $this->pathEdit($id, $pro_id, $city_id);
            if ($aff_id) {
                //删除原来的多版本信息
                $gCLangMod = &m('goodsClassLang');
                $where = '  category_id =' . $id;
                $gCLangMod->doDrops($where);
                //添加多语言版本信息
                $this->doLangData($name, $id);
                //
                $this->addLog('分类更新操作');
                $info['url'] = "admin.php?app=goodsClass&act=classIndex";
                $this->setData($info, $status = '1', $this->langDataBank->public->edit_success);
            } else {
                $this->setData($info, $status = '0', $this->langDataBank->public->edit_fail);
            }
        } else {
            $this->setData($info, $status = '0', $this->langDataBank->public->edit_fail);
        }
    }

    /**
     * 获取多语言的版本的id
     */
    public function getCtgLang($name, $id) {
        //多语言版本
        $gCLangMod = &m('goodsClassLang');
        $sql = 'select  id,lang_id,category_name,category_id  from   ' . DB_PREFIX . 'goods_category_lang
                 where  category_id =' . $id . '   and   category_name = "' . $name . '"';
        $item = $gCLangMod->querySql($sql);
        if (!empty($item)) {
            return $item[0]['id'];
        } else {
            return 0;
        }
    }

    /**
     * 附件图片上传
     * @author zhangr
     * @date 2017-6-21
     */
    public function dele() {
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '0';
        //判断是否有子类
        $query_cond = array(
            'cond' => "`parent_id` ='{$id}'"
        );
        $res = $this->goodsClassMod->getOne($query_cond);
        if ($res) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->classification_delete);
        }
        //删除主表的数据
        $query = array(
            'cond' => "`id` ='{$id}'"
        );
        $goodsCategoryMod=&m('goodsCategory');
        $del_id = $goodsCategoryMod->doDrop($id);
        $goodsCategoryMod->_cacheReset($id);
        //删除子表的信息
        $gCLangMod = &m('goodsClassLang');
        $where = '  category_id =' . $id;
        $ctglangids = $gCLangMod->doDrops($where);
        //
        if ($del_id && $ctglangids) {
            $this->addLog('分类删除操作');
            $this->setData($info = array(), $status = '1', $this->langDataBank->public->drop_success);
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->drop_fail);
        }
    }

    /**
     * 附件图片上传
     * @author zhangr
     * @date 2017-6-21
     */
    public function upload() {
        if (IS_POST) {
            $fileName = $_FILES['fileName']['name'];
            $type = strtolower(substr(strrchr($fileName, '.'), 1)); //获取文件类型
            $info = array();
            if (!in_array($type, array('jpg', 'png', 'jpeg', 'gif', 'JPG', 'PNG', 'JPEG', 'GIF'))) {
                $this->setData($info, $status = 'error', $this->langDataBank->project->upload_picture);
            }
            $savePath = "upload/images/cates/" . date("Ymd");
            // 判断文件夹是否存在否则创建
            if (!file_exists($savePath)) {
                @mkdir($savePath, 0777, true);
                @chmod($savePath, 0777);
                @exec("chmod 777 {$savePath}");
            }
            $filePath = $_FILES['fileName']['tmp_name']; //文件路径
            $url = $savePath . '/' . time() . '.' . $type;
            if (!is_uploaded_file($filePath)) {
                exit($this->langDataBank->project->temp_file_error);
            }
            //上传文件
            if (!move_uploaded_file($filePath, $url)) {
                $this->setData(array(), '0', $this->langDataBank->public->add_success);
            }
            $data = array(
                "name" => $fileName,
                "status" => 1,
                "url" => $url,
                "add_time" => time()
            );
            $this->addLog('图片上传操作');
            echo json_encode($data);
        } else {
            $this->setData($info = array(), 2, $this->langDataBank->public->system_error);
        }
    }

}

?>