<?php

/**
 * 业务类型控制器
 * @author  wanyan
 * @date 2017-07-31
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class roomTypeCateApp extends BackendApp {

    private $roomTypeCateMod;
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->roomTypeCateMod = &m('roomTypeCate');
    }

    /**
     * 业务类型对应分类页面
     * @author  wanyan
     * @date 2017-08-03
     */
    public function roomTypeCateIndex() {
        $type_id = ($_REQUEST['type_id']);
        $room_name = !empty($_REQUEST['name']) ? htmlspecialchars(trim($_REQUEST['name'])) : '';
        $room_sname = !empty($_REQUEST['sname']) ? htmlspecialchars(trim($_REQUEST['sname'])) : '';
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '0';
        $where = "where 1=1 and  l.lang_id =" . $this->lang_id;
        if ($this->lang_id == 1) {
            $where .= " and l.`type_name` like '%" . $room_sname . "%'";
        } else {
            $where .= " and l.`type_name` like '%" . $room_name . "%'";
        }
        if (!empty($id)) {
            $where .= " and rt.`id` ='{$id}'";
        }
        if (!empty($type_id)) {
            $where .= " and l.`type_id` ='{$type_id}'";
        }
        $where .= " order by rc.`sort` ";
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "room_category " . $where;
        $totalCount = $this->roomTypeCateMod->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;

        $sql = "select rc.id,l.type_name,rc.category_id,rc.add_time,c.`parent_id_path`,rc.sort from "
                . DB_PREFIX . "room_category as rc  left join "
                . DB_PREFIX . "room_type_lang as l on rc.room_type_id = l.type_id  left join "
                . DB_PREFIX . 'goods_category AS  c  ON  c.id = rc.`category_id`' . $where;
//        print_r($sql);exit;
        $res = $this->roomTypeCateMod->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($res['list'] as $k => $v) {
            if ($v['add_time']) {
                $res['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            } else {
                $res['list'][$k]['add_time'] = '';
            }
            $res['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $res['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        $list = $res['list'];
        foreach ($list as &$val) {
            $val['ctgpath'] = $this->getCtgPath($val['parent_id_path']);
        }
        $this->assign('room_name', $room_name);
        $this->assign('room_names', $room_names);
        $this->assign('p', $p);
        $this->assign('list', $list);
        $this->assign('page', $res['ph']);
        $this->display('roomTypeCate/roomTypeCate.html');
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
                where  l.lang_id =' . $this->lang_id . '  and  c.id in(' . $str . ')   ORDER BY  c.level  asc ';
        $data = $ctgMod->querySql($sql);
        $ctgpath = array();
        foreach ($data as $val) {
            $ctgpath[] = $val['category_name'];
        }
        $pathStr = implode(' > ', $ctgpath);
        return $pathStr;
    }

    /**
     * 业务类型对应分类页面
     * @author  wanyan
     * @date 2017-08-03
     */
    public function cateAdd() {
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $room_name =$_REQUEST['name'];
        $this->assign('p', $p);
        $this->assign('room_name', $room_name);
        $options = $this->getRoomType();
        $this->assign('options', $options);
        $ctgMod = &m('goodsClass');
        $sql = 'SELECT  c.id,l.`category_name`,c.`parent_id`  FROM  ' . DB_PREFIX . 'goods_category AS c
                 LEFT JOIN  ' . DB_PREFIX . 'goods_category_lang AS l  ON c.`id` = l.`category_id`
                 WHERE c.`parent_id` = 0  AND  l.`lang_id` =' . $this->lang_id;
        $res = $ctgMod->querySql($sql);
        $this->assign('ctglev1', $res);
        $this->display('roomTypeCate/cateAdd.html');
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
     * 获取商品业务选项
     * @param int $selected
     * @return string
     */
    public function getRoomType($selected = 0) {
        $roomType = &m('roomType');
        $sql = 'select  t.id,l.type_name  from  ' . DB_PREFIX . 'room_type  as t
                left join  ' . DB_PREFIX . 'room_type_lang  as  l  on t.id=l.type_id  where t.superior_id!=0 and  l.lang_id =' . $this->lang_id;
        $data = $roomType->querySql($sql);
            $options = '<option value="0" >--'. $this->langDataBank->project->select_business .'--</option>';
        if (empty($selected)) {
            foreach ($data as $val) {
                $options .= '<option value=' . $val['id'] . ' >' . $val['type_name'] . '</option>';
            }
        } else {
            foreach ($data as $val) {
                if ($val['id'] == $selected) {
                    $options .= '<option value=' . $val['id'] . '  selected  >' . $val['type_name'] . '</option>';
                } else {
                    $options .= '<option value=' . $val['id'] . ' >' . $val['type_name'] . '</option>';
                }
            }
        }
        return $options;
    }

    /**
     * 业务类型对应分类添加
     * @author  wanyan
     * @date 2017-08-03
     */
    public function doAdd() {
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $room_name =$_REQUEST['name'];
        $lang_id = !empty($_REQUEST['lang_id']) ? (int) ($_REQUEST['lang_id']) : '0';
        $room_id = !empty($_REQUEST['room_id']) ? htmlspecialchars(trim($_REQUEST['room_id'])) : '';
        $pro_id = !empty($_REQUEST['ctglev1']) ? intval($_REQUEST['ctglev1']) : '0';
        $city_id = !empty($_REQUEST['ctglev2']) ? intval($_REQUEST['ctglev2']) : '0';
        $area_id = !empty($_REQUEST['ctglev3']) ? intval($_REQUEST['ctglev3']) : '0';
        $sort = !empty($_REQUEST['sort']) ? intval($_REQUEST['sort']) : '0';
        if (empty($room_id)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->business_name_required);
        }
        if (empty($pro_id) || empty($city_id) || empty($area_id)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->classification_required);
        }
        if (!empty($room_id) && !empty($pro_id) && !empty($city_id) && !empty($area_id)) {
            $query = array(
                'cond' => "`room_type_id`='{$room_id}' and  `category_id`='{$area_id}'",
                'fields' => 'room_type_id'
            );
            $res = $this->roomTypeCateMod->getOne($query);
            if ($res['room_type_id']) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->project->classification_exist);
            }
        }
        if (empty($sort)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->sort_required);
        }
        $insert_data = array(
            'room_type_id' => $room_id,
            'sort' => $sort,
            'category_id' => $area_id,
            'add_time' => time()
        );
        $insert_id = $this->roomTypeCateMod->doInsert($insert_data);
        if ($insert_id) {
            $this->addLog('业务分类添加操作');
            $info['url'] = "admin.php?app=roomTypeCate&act=roomTypeCateIndex&p={$p}&name={$room_name}";
            $this->setData($info, $status = '1', $this->langDataBank->public->add_success);
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->add_error);
        }
    }

    /**
     * 业务类型对应分类添加
     * @author  wanyan
     * @date 2017-08-03
     */
    public function cateEdit() {
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '0';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $room_name =$_REQUEST['name'];
        if (empty($id)) {
            return false;
        }
        $query = array(
            'cond' => "`id`='{$id}'"
        );
        $this->assign('id', $id);
        $res = $this->roomTypeCateMod->getOne($query);
        $this->assign('res', $res);
        //业务类型
        $where = '   where  c.id= ' . $id;
        $sql = 'SELECT  c.`id` AS tname,t.id as tid FROM ' . DB_PREFIX . 'room_category AS c
                 LEFT JOIN  ' . DB_PREFIX . 'room_type  AS t  ON c.`room_type_id` = t.`id`  ' . $where;
        $data = $this->roomTypeCateMod->querySql($sql);
        $options = $this->getRoomType($data[0]['tid']);
        $this->assign('options', $options);
        //商品模型的具体信息
        $where = '  where  t.id =' . $id;
        $sql = 'SELECT  t.`id`,t.`category_id`,c.`parent_id_path`  FROM  ' . DB_PREFIX . 'room_category AS  t
                 LEFT JOIN   ' . DB_PREFIX . 'goods_category AS  c  ON  c.id = t.`category_id`' . $where;
        $data = $this->roomTypeCateMod->querySql($sql);
        //模型分类
        $Parentpath = $data[0]['parent_id_path'];
        $patharr = explode('_', $Parentpath);
        //获取一级分类选项
        $options1 = $this->getOptions(0, $patharr[1]);
        // 获取二级分类选项
        $options2 = $this->getOptions($patharr[1], $patharr[2]);
        // 获取三级分类选项
        $options3 = $this->getOptions($patharr[2], $patharr[3]);
        $this->assign('p', $p);
        $this->assign('room_name', $room_name);
        $this->assign('options1', $options1);
        $this->assign('options2', $options2);
        $this->assign('options3', $options3);
        $this->assign('lang_id', $this->lang_id);
        $this->display('roomTypeCate/cateEdit.html');
    }

    /**
     * 业务类型对应分类编辑
     * @author  wanyan
     * @date 2017-08-03
     */
    public function doEdit() {
        $lang_id = !empty($_REQUEST['lang_id']) ? (int) ($_REQUEST['lang_id']) : '0';
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '0';
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $room_name =$_REQUEST['name'];
        $room_id = !empty($_REQUEST['room_id']) ? htmlspecialchars(trim($_REQUEST['room_id'])) : '';
        $pro_id = !empty($_REQUEST['ctglev1']) ? intval($_REQUEST['ctglev1']) : '0';
        $city_id = !empty($_REQUEST['ctglev2']) ? intval($_REQUEST['ctglev2']) : '0';
        $area_id = !empty($_REQUEST['ctglev3']) ? intval($_REQUEST['ctglev3']) : '0';
        $sort = !empty($_REQUEST['sort']) ? intval($_REQUEST['sort']) : '0';
        if (empty($room_id)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->business_name_required);
        }
        if (empty($pro_id) || empty($city_id) || empty($area_id)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->classification_required);
        }
        if (!empty($room_id) && !empty($pro_id) && !empty($city_id) && !empty($area_id)) {
            $query = array(
                'cond' => "`room_type_id`='{$room_id}' and `category_id`='{$area_id}' and `id` != '{$id}'",
                'fields' => 'room_type_id'
            );
            $res = $this->roomTypeCateMod->getOne($query);
            if ($res['room_type_id']) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->project->classification_exist);
            }
        }
        if (empty($sort)) {
            $this->setData($info = array(), $status = '0', $this->langDataBank->project->sort_required);
        }
        $insert_data = array(
            'room_type_id' => $room_id,
            'sort' => $sort,
            'category_id' => $area_id,
            'add_time' => time()
        );
        $insert_id = $this->roomTypeCateMod->doEdit($id, $insert_data);
        if ($insert_id) {
            $this->addLog('业务分类编辑操作');
            $info['url'] = "admin.php?app=roomTypeCate&act=roomTypeCateIndex&p={$p}&name={$room_name}";
            $this->setData($info, $status = '1', $this->langDataBank->public->edit_success);
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->edit_fail);
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
        $query = array(
            'cond' => "`id`='{$id}'"
        );
        $aff_id = $this->roomTypeCateMod->doDelete($query);
        if ($aff_id) {
            $this->addLog('业务分类删除操作');
            $this->setData($info = array(), $status = '1', $this->langDataBank->public->drop_success);
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
            $option = '<option value="0" >'. $this->langDataBank->project->select_business .'</option>';
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