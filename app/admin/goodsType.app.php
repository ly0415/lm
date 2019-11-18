<?php

/**
 * 商品模型控制器
 * @author  wh
 * @date 2017-07-31
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class GoodsTypeApp extends BackendApp {

    private $goodsType;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->goodsType = &m('goodsType');
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 模型列表
     * @author wh
     * @date 2017/07/31
     */
    public function typeIndex() {
        $name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        $this->assign('name', $name);
        $where = '  where 1=1  and t.mark =1  and  l.lang_id =' . $this->lang_id;
        //搜索
            if (!empty($name)) {
                $where .= '  and  l.`type_name` like "%' . $name . '%"';
            }
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "goods_type ";
        $totalCount = $this->goodsType->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        //列表页数据
        $sql = 'SELECT  t.`id`,l.`type_name`,t.`category_id`,c.`parent_id_path`,l.`lang_id`  FROM  ' . DB_PREFIX . 'goods_type AS  t
                 LEFT JOIN  ' . DB_PREFIX . 'goods_type_lang AS l ON t.`id` = l.`type_id`
                 LEFT JOIN   ' . DB_PREFIX . 'goods_category AS  c  ON  c.id = t.`category_id`' . $where;
        $sql .= '   order by t.id desc ';
        $data = $this->goodsType->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        $list = $data['list'];
        foreach ($list as $key => $val) {
            $list[$key]['ctgpath'] = $this->getCtgPath($val['parent_id_path']);

            if ($val['add_time']) {
                $list[$key]['add_time'] = date('Y-m-d H:i:s', $val['add_time']);
            } else {
                $list[$key]['add_time'] = '';
            }
            $list[$key]['sort_id'] = $key + 20 * ($p - 1) + 1; //正序
//            $list['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        $this->assign('list', $list);
        $this->assign('page_html', $data['ph']);
        $this->display('goodsType/typeIndex.html');
    }

    /**
     * 获取分类导航
     * @path   0_2_21_844
     * @author wh
     * @date 2017/07/31
     */
    public function getCtgPath($path) {
        if (!empty($path)) {
            $arr = explode('_', $path);
            array_shift($arr);
        }
        $str = implode(',', $arr);
        $ctgMod = &m('goodsClass');
        $sql = 'select  c.id,l.category_name   from   ' . DB_PREFIX . 'goods_category  as c
                left join  ' . DB_PREFIX . 'goods_category_lang as l  on c.id =l.category_id
                where  l.lang_id =' . $this->lang_id . '  and  c.id in(' . $str . ')  ORDER BY c.`level`  ASC ';
        $data = $ctgMod->querySql($sql);
        $ctgpath = array();
        foreach ($data as $val) {
            $ctgpath[] = $val['category_name'];
        }
        $pathStr = implode(' > ', $ctgpath);
        return $pathStr;
    }

    /**
     * 模型添加
     * @author wh
     * @date 2017/07/31
     */
    public function add() {
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
        $this->display('goodsType/typeAdd.html');
    }

    public function doAdd() {
        $ctglev3 = $_REQUEST['ctglev3'];
        $name = !empty($_REQUEST['name']) ? $_REQUEST['name'] : array();
        //判断数据
        if (empty($ctglev3)) {
            $this->setData(array(), '0', $this->langDataBank->project->select_categories);
        }
        //
        foreach ($name as $key => $val) {
            $name[$key] = trim($val);
        }
        foreach ($name as $val) {
            if (empty($val)) {
                $this->setData(array(), $status = '0', $this->langDataBank->project->model_name_required);
                break;
            } else {
                $info = $this->getInfo($val);
                if (!empty($info)) {
                    $this->setData(array(), $status = '0',$this->langDataBank->project->model_name_exist);
                    break;
                }
            }
        }
        /* 判断分类的唯一性质 */
        if ($this->goodsType->isExist('category_id', $ctglev3)) {
            $this->setData(array(), '0', $this->langDataBank->project->model_exist);
        }

        $data = array(
            'category_id' => $ctglev3
        );
        $res = $this->goodsType->doInsert($data);
        if ($res) {
            //添加多语言版本
            $this->insertLangData($name, $res);
            //
            $this->addLog('添加商品模型');
            $this->setData(array(), '1', $this->langDataBank->public->add_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->add_error);
        }
    }

    /**
     *  获取模型多语言信息
     */
    public function getInfo($name, $id = 0) {
        $gtLandMod = &m('goodsTypeLang');
        $where = '  where 1=1';
        if (empty($id)) {
            //添加
            $where .= ' and l,type_name = "' . $name . '"';
            //编辑
        } else {
            $where .= ' and  id!=' . $id . '  and  l.type_name = "' . $name . '"';
        }
        $sql = 'select * from  ' . DB_PREFIX . 'goods_type as r  left join ' . DB_PREFIX . 'goods_type_lang
          as l on r.id=l.type_id' . $where;
        $data = $gtLandMod->querySql($sql);
        return $data;
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
     * 添加多语言数据
     * @author wh
     * @date 2017/07/31
     */
    public function insertLangData($name, $insertId) {
        $gtLandMod = &m('goodsTypeLang');
        $data = array();
        foreach ($name as $key => $val) {
            $data[] = array(
                'lang_id' => $key,
                'type_name' => $val,
                'type_id' => $insertId,
                'add_time' => time()
            );
        }
        // 循环插入数据
        foreach ($data as $v) {
            $res = $gtLandMod->doInsert($v);
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
     * 模型编辑
     * @author wh
     * @date 2017/07/31
     */
    public function edit() {
        $id = $_REQUEST['id']; //商品模型id
        //多语言版本
        $langMod = &m('language');
        $sql = 'select  id,name  from   ' . DB_PREFIX . 'language';
        $res = $langMod->querySql($sql);
        // 商品模型的具体信息
        $where = '  where  t.id =' . $id;
        $sql = 'SELECT  t.`id`,l.`type_name`,t.`category_id`,c.`parent_id_path`,l.`lang_id`  FROM  ' . DB_PREFIX . 'goods_type AS  t
                 LEFT JOIN  ' . DB_PREFIX . 'goods_type_lang AS l ON t.`id` = l.`type_id`
                 LEFT JOIN   ' . DB_PREFIX . 'goods_category AS  c  ON  c.id = t.`category_id`' . $where;
        $data = $this->goodsType->querySql($sql);
        //
        $html = '';
        foreach ($res as $key => $val) {
            foreach ($data as $item) {
                if ($val['id'] == $item['lang_id']) {
                    $html .= ' <span class="mt10 mb5 inblock">' . $val['name'] . '：</span>
                               <input type="text" class="form-control" name="name[' . $item['lang_id'] . ']"  value="' . $item['type_name'] . '"  >';
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
        //模型分类
        $Parentpath = $data[0]['parent_id_path'];
        $patharr = explode('_', $Parentpath);
        //获取一级分类选项
        $options1 = $this->getOptions(0, $patharr[1]);
        // 获取二级分类选项
        $options2 = $this->getOptions($patharr[1], $patharr[2]);
        // 获取三级分类选项
        $options3 = $this->getOptions($patharr[2], $patharr[3]);
        $this->assign('id', $id);
        $this->assign('name', $data[0]['name']);
        $this->assign('options1', $options1);
        $this->assign('options2', $options2);
        $this->assign('options3', $options3);
        $this->display('goodsType/typeEdit.html');
    }

    /**
     * 编辑处理
     */
    public function doEdit() {
        $id = $_REQUEST['id'];
        $ctglev3 = $_REQUEST['ctglev3'];
        $ctglev2 = $_REQUEST['ctglev2'];
        $name = !empty($_REQUEST['name']) ? $_REQUEST['name'] : array();
        if (empty($ctglev3) || empty($ctglev2)) {
            $this->setData(array(), '0', $this->langDataBank->project->select_categories);
        }
        //判断模型名称
        foreach ($name as $key => $val) {
            $name[$key] = trim($val);
        }
        foreach ($name as $val) {
            if (empty($val)) {
                $this->setData(array(), $status = '0', $this->langDataBank->project->model_name_required);
                break;
            } else {
                $typeLandId = $this->getLangTypeId($val);
                $info = $this->getInfo($val, $typeLandId);
                if (!empty($info)) {
                    $this->setData(array(), $status = '0', $this->langDataBank->project->model_name_exist);
                    break;
                }
            }
        }
        /* 判断分类的唯一性质 */
        if ($this->goodsType->isExist('category_id', $ctglev3, $id)) {
            $this->setData(array(), '0', $this->langDataBank->project->model_exist);
        }
        $data = array(
            'category_id' => $ctglev3
        );
        $res = $this->goodsType->doEdit($id, $data);
        if ($res) {
            //删除以前的多语言数据
            $gtLandMod = &m('goodsTypeLang');
            $where = '  type_id =' . $id;
            $gtLandMod->doDrops($where);
            //添加多语言版本
            $this->insertLangData($name, $id);
            //
            $this->addLog('编辑商品模型');
            $this->setData(array(), '1', $this->langDataBank->public->edit_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->edit_fail);
        }
    }

    /**
     * 获取模型语言id
     */
    public function getLangTypeId($name) {
        $gtLandMod = &m('goodsTypeLang');
        $sql = 'select  id,type_name   from  ' . DB_PREFIX . 'goods_type_lang  where  type_name = "' . $name . '"';
        $res = $gtLandMod->querySql($sql);
        return $res[0]['id'];
    }

    /**
     * 模型删除
     * @author wh
     * @date 2017/07/31
     */
    public function dele() {
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        if (empty($id)) {
            $this->setData(array(), '0', $this->langDataBank->public->system_error);
        }
        // 删除数据
        $where = 'id  in(' . $id . ')';
        $res = $this->goodsType->doDrops($where);
        //删除多语言信息
        $gtLandMod = &m('goodsTypeLang');
        $where2 = 'type_id  in(' . $id . ')';
        $res2 = $gtLandMod->doDrops($where2);
        //
        if ($res && $res2) {  //删除成功
            $this->addLog('删除商品模型');
            $this->setData(array(), '1',  $this->langDataBank->public->drop_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->drop_fail);
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
        $option = '<option value="0" >' . $this->langDataBank->project->select_classification . '</option>';
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
