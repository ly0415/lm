<?php

/**
 * 商品品牌模块
 * @author wh
 * @date 2017-7-20
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class goodsBrandApp extends BackendApp {

    private $goodsBrandMod;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->goodsBrandMod = &m('goodsBrand');
        $this->goodsClassMod = &m('goodsClass');
    }

    /**
     * 商品品牌首页
     * @author wanyan
     * @date 2017-08-01
     */
    public function brandIndex() {
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        $this->assign('name', $name);
        $where = ' where 1=1  and  l.lang_id =' . $this->lang_id;
        //搜索
        if (!empty($name)) {
            $where .= '  and  l.brand_name like "%' . $name . '%"';
        }
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "goods_brand ";
        $totalCount = $this->goodsBrandMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;

        //展示页面
        $sql = "select  b.`id`,l.`brand_name`,b.`logo`,b.`cat_name`,b.`is_hot`,b.`sort`,b.`add_time`,b.`max_cat_id`,b.`parent_cat_id`,b.`cat_id`   from  " . DB_PREFIX . "goods_brand  AS b
                 left join   " . DB_PREFIX . "goods_brand_lang  l  ON  b.id = l.`brand_id`" . $where;
        $sql .= '   order by b.sort ';
        $res = $this->goodsBrandMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($res['list'] as $k => $v) {
            $res['list'][$k]['brand_name'] = stripslashes($v['brand_name']);
            if ($v['is_hot'] == 1) {
                $res['list'][$k]['is_hot'] = '是';
            } else {
                $res['list'][$k]['is_hot'] = '否';
            }
            //$res['list'][$k]['cate_name'] = $this->getSub($v['max_cat_id']) . '>' . $this->getSub($v['parent_cat_id']) . '>' . $this->getSub($v['cat_id']);
            $res['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);

            if ($v['add_time']) {
                $res['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            } else {
                $res['list'][$k]['add_time'] = '';
            }
            $res['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        $this->assign('list', $res['list']);
//        $this->assign('page', $res['ph']);
            $this->display('goodsBrand/goodBrand.html');
    }

    /**
     * 查找商品子分类
     * @author wanyan
     * @date 2017-8-1
     */
    public function getSub($id) {
        $where = '  where 1=1  and  l.lang_id =' . $this->lang_id . " and c.`id` = '{$id}'";
        $sql = "select c.`id`,l.`category_name` from " . DB_PREFIX . "goods_category as c left join " .
                DB_PREFIX . "goods_category_lang as l on l.category_id = c.id" . $where;

        $res = $this->goodsBrandMod->querySql($sql);
        return $res[0]['category_name'];
    }

    /**
     * 商品品牌添加页面
     * @author wanyan
     * @date 2017-08-01
     */
    public function brandAdd() {
        $ctgMod = &m('goodsClass');
        $sql = 'SELECT  c.id,l.`category_name`,c.`parent_id`  FROM  ' . DB_PREFIX . 'goods_category AS c
                 LEFT JOIN  ' . DB_PREFIX . 'goods_category_lang AS l  ON c.`id` = l.`category_id`
                 WHERE c.`parent_id` = 0  AND  l.`lang_id` =' . $this->lang_id;
        $res = $ctgMod->querySql($sql);
        $this->assign('ctglev1', $res);
        //多语言版本
        $langMod = &m('language');
        $sql = 'select  id,name  from   ' . DB_PREFIX . 'language';
        $data = $langMod->querySql($sql);
        $html = '';
        foreach ($data as $val) {
            $html .= ' <span class="mt10 mb5 inblock">' . $val['name'] . '：</span><input type="text" class="form-control" name="name[' . $val['id'] . ']" >';
        }
        $this->assign('html', $html);
        $this->display('goodsBrand/brandAdd.html');
    }

    /**
     * 分类的三级联动
     */
    public function getctglist() {
        $id = $_REQUEST['id'];
        $ctgMod = &m('goodsClass');
        $sql = 'SELECT  c.id,l.`category_name`,c.`parent_id`  FROM  ' . DB_PREFIX . 'goods_category AS c
                 LEFT JOIN   ' . DB_PREFIX . 'goods_category_lang AS l  ON c.`id` = l.`category_id`
                 WHERE c.`parent_id` = ' . $id . '  AND  l.`lang_id` =' . $this->lang_id;
        $data = $ctgMod->querySql($sql);
        echo json_encode($data);
        exit;
    }

    /**
     * 商品品牌添加功能
     * @author wanyan
     * @date 2017-08-02
     */
    public function doAdd() {
        $lang_id = !empty($_REQUEST['lang_id']) ? (int) ($_REQUEST['lang_id']) : '0';
        $name = !empty($_REQUEST['name']) ? $_REQUEST['name'] : array();
        $is_hot = !empty($_REQUEST['is_hot']) ? intval($_REQUEST['is_hot']) : '0';
        $image_id = !empty($_REQUEST['image_id']) ? htmlspecialchars(trim($_REQUEST['image_id'])) : '';
        $sort = !empty($_REQUEST['sort']) ? intval($_REQUEST['sort']) : '0';
        $desc = !empty($_REQUEST['desc']) ? htmlspecialchars(trim($_REQUEST['desc'])) : '';
        //判断数据
        foreach ($name as $key => $val) {
            // $name[$key] = addslashes($val);
            if (strstr($val, '\'')) {
                $name[$key] = str_replace('\'', '‘', $val);
            }
        }
        //判断数据
        foreach ($name as $val) {
            if (empty($val)) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->project->brand_required);
                break;
            } else {
                $aff = $this->getOneInfo($val);
                if (!empty($aff)) {
                    $this->setData($info = array(), $status = '0', $this->langDataBank->project->brand_exist);
                    break;
                }
            }
        }
        if (empty($image_id)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->picture_name_required);
        }
        if (empty($sort)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->sort_required);
        }
        $insert_data = array(
            'logo' => $image_id,
            'descrption' => $desc,
            'sort' => $sort,
            'is_hot' => $is_hot,
            'add_time' => time()
        );
        $insert_id = $this->goodsBrandMod->doInsert($insert_data);
        if ($insert_id) {
            //添加多语言版本信息
            $this->doLangData($name, $insert_id);
            $this->addLog('品牌添加操作');
            $info['url'] = "admin.php?app=goodsBrand&act=brandIndex";
            $this->setData($info, $status = '1', $this->langDataBank->public->add_success);
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->add_error);
        }
    }

    /**
     * 添加多语言版本信息
     */
    public function doLangData($name, $insert_id) {
        $gCLangMod = &m('goodsBrandLang');
        $data = array();
        foreach ($name as $key => $val) {
            $data[] = array(
                'lang_id' => $key,
                'brand_name' => addslashes(trim($val)),
                'brand_id' => $insert_id,
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
        return true;
    }

    /**
     * 获取分类信息
     * @author wanyan
     * @date 2017-8-1
     */
    public function getOneInfo($name, $id = 0) {
        $gCLangMod = &m('goodsBrandLang');
        $where = '  where 1=1';
        if (empty($id)) {
            //添加
            $where .= '  and  l.brand_name = "' . $name . '"';
        } else {
            //编辑
            $where .= '  and   id!=' . $id . '  and  l.brand_name = "' . $name . '"';
        }
        $sql = 'select * from  ' . DB_PREFIX . 'goods_brand as b  left join ' . DB_PREFIX . 'goods_brand_lang
          as l on b.id=l.brand_id' . $where;
        $res = $gCLangMod->querySql($sql);
        return $res;
    }

    /**
     * 商品品牌编辑页面
     * @author wanyan
     * @date 2017-08-03
     */
    public function brandEdit() {
        $id = $_REQUEST['id']; //商品模型id
        //多语言版本  
        $langMod = &m('language');
        $sql = 'select  id,name  from   ' . DB_PREFIX . 'language';
        $res = $langMod->querySql($sql);
        // 商品模型的具体信息
        $gCLangMod = &m('goodsBrandLang');
        $sql = 'select  id,lang_id,brand_name,brand_id  from   ' . DB_PREFIX . 'goods_brand_lang  where  brand_id =' . $id;
        $data = $gCLangMod->querySql($sql);
        $html = '';
        foreach ($res as $key => $val) {
            foreach ($data as $item) {
                if ($val['id'] == $item['lang_id']) {
                    $html .= ' <span class="mt10 mb5 inblock">' . $val['name'] . '：</span>
                               <input type="text" class="form-control" name="name[' . $item['lang_id'] . ']"  value="' . stripslashes($item['brand_name']) . '"  >';
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

        //遍历
        $query = array(
            'cond' => "`id` ='{$id}'"
        );
        $res = $this->goodsBrandMod->getOne($query);
        //模型分类
        $Parentpath = $data[0]['parent_id_path'];
        $patharr = explode('_', $Parentpath);
        //获取一级分类选项
        $options1 = $this->getOptions(0, $res['max_cat_id']);
        // 获取二级分类选项
        $options2 = $this->getOptions($res['max_cat_id'], $res['parent_cat_id']);
        // 获取三级分类选项
        $options3 = $this->getOptions($res['parent_cat_id'], $res['cat_id']);
        $this->assign('name', $data[0]['name']);
        $this->assign('id', $id);
        $this->assign('options1', $options1);
        $this->assign('options2', $options2);
        $this->assign('options3', $options3);
        $this->assign('brandInfo', $res);
        $this->display('goodsBrand/brandEdit.html');
    }

    /**
     * 处理品牌编辑功能
     * @author wanyan
     * @date 2017-08-03
     */
    public function doEdit() {
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '0';
        $name = !empty($_REQUEST['name']) ? $_REQUEST['name'] : array();
        $is_hot = !empty($_REQUEST['is_hot']) ? intval($_REQUEST['is_hot']) : '0';
        $image_id = !empty($_REQUEST['image_id']) ? htmlspecialchars(trim($_REQUEST['image_id'])) : '';
        $sort = !empty($_REQUEST['sort']) ? intval($_REQUEST['sort']) : '0';
        $desc = !empty($_REQUEST['desc']) ? htmlspecialchars(trim($_REQUEST['desc'])) : '';
        //判断数据
        foreach ($name as $key => $val) {
            //  $name[$key] = addslashes(trim($val));
            if (strstr($val, '\'')) {
                $name[$key] = str_replace('\'', '‘', $val);
            }
        }
        //判断数据
        foreach ($name as $val) {
            if (empty($val)) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->project->brand_required);
                break;
            } else {
                //
                $styleId = $this->getCtgLang($val, $id);  //style_lang 表里的id
                $aff = $this->getOneInfo($val, $styleId); //判断是否命名重复 
                if (!empty($aff)) {
                    $this->setData($info = array(), $status = '0', $this->langDataBank->project->brand_exist);
                    break;
                }
            }
        }
        if (empty($image_id)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->picture_name_required);
        }
        if (empty($sort)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->sort_required);
        }
        $insert_data = array(
            'logo' => $image_id,
            'descrption' => $desc,
            'sort' => $sort,
            'is_hot' => $is_hot,
            'modify_time' => time()
        );
        $insert_id = $this->goodsBrandMod->doEdit($id, $insert_data);
        if ($insert_id) {
            //删除原来的多版本信息
            $gCLangMod = &m('goodsBrandLang');
            $where = '  brand_id =' . $id;
            $gCLangMod->doDrops($where);
            //添加多语言版本信息

            $this->doLangData($name, $id);
            $this->addLog('品牌更新操作');
            $info['url'] = "admin.php?app=goodsBrand&act=brandIndex";
            $this->setData($info, $status = '1', $this->langDataBank->public->edit_success);
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->edit_fail);
        }
    }

    /**
     * 获取多语言的版本的id
     */
    public function getCtgLang($name, $id) {
        //多语言版本
        $gCLangMod = &m('goodsBrandLang');
        $sql = 'select  id,lang_id,brand_name,brand_id  from   ' . DB_PREFIX . 'goods_brand_lang
                 where  brand_id =' . $id . '   and   brand_name = "' . $name . '"';
        $item = $gCLangMod->querySql($sql);
        if (!empty($item)) {
            return $item[0]['id'];
        } else {
            return 0;
        }
    }

    /**
     * 删除功能
     * @author wanyan
     * @date 2017-08-03
     */
    public function dele() {
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '0';
        if (empty($id)) {
            return false;
        }
        //删除主表的数据
        $query = array(
            'cond' => "`id`='{$id}'"
        );
        $aff_id = $this->goodsBrandMod->doDelete($query);
        //删除子表的信息
        $gCLangMod = &m('goodsBrandLang');
        $where = '  brand_id =' . $id;
        $ctglangids = $gCLangMod->doDrops($where);
        if ($aff_id && $ctglangids) {
            $this->addLog('品牌删除操作');
            $this->setData($info = array(), $status = '1',  $this->langDataBank->public->drop_success);
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->drop_fail);
        }
    }

    /**
     * 根据parentid ，[  $selected ]来获取 多选项
     * @param $parentId
     * @param int $selected
     */
    public function getOptions($parentId, $selected = 0) {
        $ctgMod = &m('goodsClass');
        $sql = 'SELECT  c.id,l.category_name  FROM  ' . DB_PREFIX . 'goods_category  as c  left join  ' . DB_PREFIX . 'goods_category_lang  as l on c.id = l.category_id
                WHERE  c.parent_id =' . $parentId . '   and  l.lang_id = ' . $this->lang_id;
        $data = $ctgMod->querySql($sql);
        $option = '<option value="0" > ' . $this->langDataBank->project->select_classification . '</option>';
        if (!empty($selected)) {
            foreach ($data as $val) {
                if ($val['id'] == $selected) {
                    $option .= '<option  value=' . $val['id'] . '  selected  >' . $val['category_name'] . '</option>';
                } else {
                    $option .= '<option  value=' . $val['id'] . '   >' . $val['category_name'] . '</option>';
                }
            }
        } else {
            foreach ($data as $val) {
                $option .= '<option  value=' . $val['id'] . ' >' . $val['category_name'] . '</option>';
            }
        }
        return $option;
    }

}

?>