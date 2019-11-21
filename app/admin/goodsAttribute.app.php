<?php

/**
 * 商品属性控制器
 * @author  wh
 * @date 2017-07-31
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class GoodsAttributeApp extends BackendApp {

    private $goodsAttr;
    private $attrLang;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->goodsAttr = &m('goodsAttri');
        $this->attrLang = &m('goodsAttriLang');
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 模型属性列表
     * @author wh
     * @date 2017/07/31
     */
    public function attrList() {
        $goodsTypeid = $_REQUEST['goodsType'];
            if (!empty($goodsTypeid)) {
                $options = $this->getGoodsType($goodsTypeid);
            } else {
                $options = $this->getGoodsType();
            }
        $this->assign('options', $options);
        $where = '   where 1=1 and  t.mark=1 and l.lang_id=' . $this->lang_id . ' and tl.lang_id=' . $this->lang_id;
        if (!empty($goodsTypeid)) {
            $where .= '  and t.id=' . $goodsTypeid;
        }
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "goods_attribute ";
        $totalCount = $this->goodsAttr->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $sql = 'SELECT  a.`attr_id`,a.`attr_name`,a.`attr_values`,t.`name` AS tname,l.`name` as lname,l.lang_id,tl.type_name
                 FROM ' . DB_PREFIX . 'goods_attribute AS a
                 LEFT JOIN ' . DB_PREFIX . 'goods_attr_lang as l on a.`attr_id`=l.`a_id`
                 LEFT JOIN  ' . DB_PREFIX . 'goods_type  AS t  ON a.`type_id` = t.`id`
                 LEFT JOIN ' . DB_PREFIX . 'goods_type_lang As tl ON tl.`type_id` = t.`id`
                 ' . $where;
        $sql .= '   order by  a.attr_id  desc  ';
        $data = $this->goodsAttr->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($data['list'] as $k => $v) {
            if ($v['add_time']) {
                $data['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            } else {
                $data['list'][$k]['add_time'] = '';
            }
            $data['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙
        }
        $list = $data['list'];
//        foreach ($list as $k => $v) {
//            if ($v['lang_id'] != $this->lang_id) {
//                unset($list[$k]);
//            }
//        }
        $this->assign('list', $list);
        $this->assign('page_html', $data['ph']);
        $this->display('goodsAttribute/attriIndex.html');
    }

    /**
     * 获取商品模型选项
     * @param int $selected
     * @return string
     */
    public function getGoodsType($selected = 0) {
            $goodsType = &m('goodsType');
            $sql = 'select  t.* ,l.type_name from  ' . DB_PREFIX . 'goods_type as t  left join ' . DB_PREFIX . 'goods_type_lang as l on  t.id=l.type_id where  t.mark =1 and l.lang_id=' . $this->lang_id;
            $data = $goodsType->querySql($sql);
            $options = '<option value="0" >--' . $this->langDataBank->public->please_select . '--</option>';
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
     * 模型属性添加
     * @author wh
     * @date 2017/07/31
     */
    public function add() {
        $options = $this->getGoodsType();
        $lang_list = $this->getLanguage();
        $this->assign('lang_list', $lang_list);
        $this->assign('options', $options);
        $this->display('goodsAttribute/attrAdd.html');
    }

    public function doAdd() {
        $name = !empty($_REQUEST['lang_name']) ? $_REQUEST['lang_name'] : '';
        $typeId = $_REQUEST['type_id'];
        $attrVal = !empty($_REQUEST['attr_values']) ? htmlspecialchars(trim($_REQUEST['attr_values'])) : '';
        if (empty($typeId)) {
            $this->setData(array(), '0', $this->langDataBank->project->model_required);
        }
        if (!empty($attrVal)) {
            $attrArr = explode("\r\n", $attrVal);
            $str = implode(',', $attrArr);
        } else {
            $str = '';
        }
        //判断数据
        foreach ($name as $key => $val) {
            $name[$key] = trim($val);
        }
        /* 判断唯一性 */
        foreach ($name as $k => $v) {
            if (empty($v)) {
                $this->setData(array(), '0', $this->langDataBank->project->attribute_required);
            }
            $has = $this->getInfo($v, $typeId, $k);
            if (!empty($has)) {
                $this->setData(array(), '0', $this->langDataBank->project->attribute_repeat);
            }
        }
        $attrD = array(
//            'attr_name' => $name,
            'type_id' => $typeId,
            'attr_values' => $str
        );
        $res = $this->goodsAttr->doInsert($attrD);
        foreach ($name as $k => $v) {
            if ($v) {
                $data = array(
                    "name" => addslashes($v),
                    "a_id" => $res,
                    "lang_id" => $k,
                    "add_time" => time()
                );
                $this->attrLang->doInsert($data);
            }
        }
        if ($res) {
            $this->addLog('添加商品属性');
            $this->setData(array(), '1', $this->langDataBank->public->add_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->add_error);
        }
    }

    /**
     * 模型属性名称
     * @author wh
     * @date 2017/07/31
     */
    public function getInfo($attrName, $typeId, $langId, $id = 0) {
        $where = '  where 1=1';
        if (empty($id)) {
            $where .= '  and a.type_id=' . $typeId . '  and  l.name ="' . $attrName . '" and l.lang_id=' . $langId;
        } else {
            $where .= '  and a.type_id=' . $typeId . '  and  l.name ="' . $attrName . '"' . '  and  attr_id !=' . $id . ' and l.lang_id=' . $langId;
        }
        $sql = 'select attr_id  from  ' . DB_PREFIX . 'goods_attribute as a  left join ' . DB_PREFIX . 'goods_attr_lang
          as l on a.attr_id=l.a_id' . $where;
        $data = $this->goodsAttr->querySql($sql);
        if (!empty($data)) {
            return $data[0]['attr_id'];
        } else {
            return null;
        }
    }

    /**
     * 模型属性编辑
     * @author wh
     * @date 2017/07/31
     */
    public function edit() {
        $id = $_REQUEST['id'];
        if (empty($id)) {
            return array();
        }
        $where = '   where  a.attr_id= ' . $id;
        $sql = 'SELECT  a.`attr_id`,a.`attr_name`,a.`attr_values`,t.`name` AS tname,t.id as tid
                 FROM ' . DB_PREFIX . 'goods_attribute AS a
                 LEFT JOIN  ' . DB_PREFIX . 'goods_type  AS t  ON a.`type_id` = t.`id`  ' . $where;
        $data = $this->goodsAttr->querySql($sql);
        //
//        if(!empty($data[0]['attr_values'])){
//            $arr = explode(',',$data[0]['attr_values']);
//            $str = implode("\r\n",$arr);
//        }else{
//            $str = '';
//        }
//        $this -> assign('str',$str);
        //
        //语言遍历 modify by lee 2017-9-27 15:04:49
        $attrLang = $this->attrLang->getData(array("cond" => "a_id=" . $id));
        //end
        $lang_list = $this->getLanguage();
        foreach ($lang_list as $k => $v) {
            foreach ($attrLang as $key => $val) {
                if ($v['id'] == $val['lang_id']) {
                    $lang_list[$k]['a_name'] = $val['name'];
                }
            }
        }
        $this->assign('lang_list', $lang_list);
        $options = $this->getGoodsType($data[0]['tid']);
        $this->assign('options', $options);
        $this->assign('data', $data[0]);
        $this->display('goodsAttribute/attrEdit.html');
    }

    /*
     * 编辑
     */

    public function doEdit() {
        $name = !empty($_REQUEST['lang_name']) ? $_REQUEST['lang_name'] : '';
        $typeId = $_REQUEST['type_id'];
        $attrId = $_REQUEST['attr_id'];
//        $attrVal = !empty($_REQUEST['attr_values']) ? htmlspecialchars(trim($_REQUEST['attr_values'])) : '';

        if (empty($typeId)) {
            $this->setData(array(), '0', $this->langDataBank->project->model_required);
        }
//        if(!empty($attrVal)){
//            $attrArr = explode("\r\n",$attrVal);
//            $str = implode(',',$attrArr);
//        }else{
//            $str = '';
//        }
        //判断数据
        foreach ($name as $key => $val) {
            $name[$key] = trim($val);
        }
        /* 判断唯一性 */
        foreach ($name as $k => $v) {
            if (empty($v)) {
                $this->setData(array(), '0', $this->langDataBank->project->attribute_required);
            }
            $data = $this->getInfo($v, $typeId, $k, $attrId);
            if (!empty($data)) {
                $this->setData(array(), '0', $this->langDataBank->project->attribute_repeat);
            }
        }
        $attrD = array(
            'type_id' => $typeId,
        );
        $res = $this->goodsAttr->doEdit($attrId, $attrD);
        //编辑语言信息 modify  by lee 2017-9-27 15:20:33
        $langList = $this->attrLang->getData(array("cond" => "a_id=" . $attrId));
        foreach ($name as $k => $v) {
            foreach ($langList as $key => $val) {
                if ($val['lang_id'] == $k) {
                    $data = array(
                        "name" => addslashes($v)
                    );
                     $this->attrLang->doEdit($val['id'], $data);
                }
                $lang_arr[] = $val['lang_id'];
            }
            if(!in_array($k,$lang_arr)){
                 $i_data = array(
                     'name'=>addslashes($v),
                     'a_id'=>$attrId,
                     'lang_id'=>$k,
                     'add_time'=>time()
                 );
                $this->attrLang->doInsert($i_data);
            }
        }
        //end
        if ($res) {
            $this->addLog('编辑商品属性');
            $this->setData(array(), '1', $this->langDataBank->public->edit_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->edit_fail);
        }
    }

    /**
     * 模型属性删除
     * @author wh
     * @date 2017/07/31
     */
    public function dele() {
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        if (empty($id)) {
            $this->setData(array(), '0', $this->langDataBank->public->system_error);
        }
        // 删除主表数据
        $where = 'attr_id  in(' . $id . ')';
        $res = $this->goodsAttr->doDrops($where);
        if ($res) {   //删除成功
            //删除对应语言表
            $where2 = 'a_id  in(' . $id . ')';
            $this->attrLang->doDrops($where2);
            //
            $this->addLog('删除商品属性');
            $this->setData(array(), '1', $this->langDataBank->public->drop_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->drop_fail);
        }
    }

}
