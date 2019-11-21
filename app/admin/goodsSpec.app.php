<?php

/**
 * 商品规格控制器
 * @author  wh
 * @date 2017-07-31
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class GoodsSpecApp extends BackendApp {

    private $goodsSpec;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->goodsSpec = &m('goodsSpec');
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 模型规格列表
     * @author wh
     * @date 2017/07/31
     */
    public function specList() {
        $goodsTypeid = $_REQUEST['goodsType'];
            if (!empty($goodsTypeid)) {
                $options = $this->getGoodsType($goodsTypeid);
            } else {
                $options = $this->getGoodsType();
            }
        $this->assign('options', $options);
        $where = '    WHERE  l.`lang_id`  = ' . $this->lang_id . '  AND tl.`lang_id` = ' . $this->lang_id . '  AND  sil.`lang_id` = ' . $this->lang_id;

        if (!empty($goodsTypeid)) {
            $where .= '  and  s.`type_id` = ' . $goodsTypeid;
        }
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "goods_spec ";
        $totalCount = $this->goodsSpec->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;

        $sql = ' SELECT  s.`id` AS sid,l.`spec_name`,s.`type_id`,s.`sort`,tl.`type_name`,si.`id` AS itemid ,sil.`item_name` ,GROUP_CONCAT( sil.`item_name`  ORDER BY sil.id ASC  SEPARATOR "；"  )  as items
                FROM  ' . DB_PREFIX . 'goods_spec AS s
                LEFT JOIN  ' . DB_PREFIX . 'goods_spec_lang AS l ON s.`id` =l.`spec_id`
                LEFT JOIN  ' . DB_PREFIX . 'goods_type_lang  AS tl  ON s.`type_id` = tl.`type_id`
                LEFT JOIN   ' . DB_PREFIX . 'goods_spec_item  AS si  ON  s.`id` = si.`spec_id`
                LEFT  JOIN  ' . DB_PREFIX . 'goods_spec_item_lang  AS sil  ON sil.`item_id` = si.`id` ' . $where;

        $sql .= ' GROUP  BY s.`id` ORDER BY s.sort';
        $data = $this->goodsSpec->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
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
        $this->assign('p', $p);
        $this->assign('list', $list);
        $this->assign('page_html', $data['ph']);
        $this->display('goodsSpec/specIndex.html');
    }

    /**
     * 获取商品模型选项
     * @param int $selected
     * @return string
     */
    public function getGoodsType($selected = 0) {
            $goodsType = &m('goodsType');
            $sql = 'select  t.id,l.type_name  from  ' . DB_PREFIX . 'goods_type  as t
                left join  ' . DB_PREFIX . 'goods_type_lang  as  l  on t.id=l.type_id  where  l.lang_id =' . $this->lang_id;
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
     * 模型规格添加
     * @author wh
     * @date 2017/07/31
     */
    public function add() {
        $options = $this->getGoodsType();
        $this->assign('options', $options);
        //多语言版本
        $langMod = &m('language');
        $sql = 'select  id,name  from   ' . DB_PREFIX . 'language  order by id asc ';
        $data = $langMod->querySql($sql);

        $html = '';
        foreach ($data as $val) {
            $html .= ' <span class="mt10 mb5 inblock">' . $val['name'] . '：</span><input type="text" class="form-control" name="name[' . $val['id'] . ']" >';
        }
        //规格值
//        $html2 = '';
//        foreach ($data as $val) {
//            $html2 .= ' <span class="mt10 mb5 inblock">' . $val['name'] . '：</span><textarea  rows="8"  cols="120" style="resize:horizontal" name="spec_item[' . $val['id'] . ']" ></textarea>';
//        }

        $this->assign('data', $data);

//        $this->assign('html2', $html2);
        $this->assign('html', $html);
        $this->assign('act', 'specList');
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $this->assign('p', $p);
        $this->display('goodsSpec/specAdd.html');
    }

    public function doAdd() {
        $name = !empty($_REQUEST['name']) ? $_REQUEST['name'] : array(); //规格名称
        $typeId = $_REQUEST['type_id'];
        $sort = $_REQUEST['sort'] ? htmlspecialchars(trim($_REQUEST['sort'])) : 5;
        $specItem = !empty($_REQUEST['item']) ? $_REQUEST['item'] : array(); //规格项
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        //数据的判断
        if (empty($typeId)) {
            $this->setData(array(), $status = '0', $this->langDataBank->project->model_required);
        }
        if (!empty($sort)) {
            if (!preg_match("/^[1-9][0-9]{0,2}$/", $sort)) {
                $this->setData(array(), '0', $this->langDataBank->project->sort_rule);//sort_rule
            }
        }
        //
        foreach ($name as $key => $val) {
            $name[$key] = trim($val);
        }
        foreach ($name as $val) {
            if (empty($val)) {
                $this->setData(array(), $status = '0', $this->langDataBank->project->specific_required);
            } else {
                $nameInfo = $this->getInfo($val, $typeId); //判断唯一性
                if (!empty($nameInfo)) {
                    $this->setData(array(), $status = '0', $this->langDataBank->project->specific_repeat);
                }
            }
        }
        //规格值
        foreach ($specItem as $key => $item) {
            foreach ($item as $k => $i) {
                if (empty($i)) {
                    $this->setData(array(), '0', $this->langDataBank->project->specific_item_required);
                } else {
                    $specItem[$key][$k] = trim($i);
                }
            }
        }
        /* 向goods_spec表加一条数据 */
        $specD = array(
            'type_id' => $typeId,
            'sort' => $sort,
        );
        $res = $this->goodsSpec->doInsert($specD); //规格id
        if ($res) {
            //向goods_spec_lang 表循环插入多语言数据
            $speclangMod = &m('goodsSpecLang');
            $dataSl = array();
            foreach ($name as $key => $valu) {
                $dataSl[] = array(
                    'spec_name' => $valu,
                    'lang_id' => $key,
                    'spec_id' => $res,
                    'add_time' => time()
                );
            }
            //
            foreach ($dataSl as $v) {
                $r = $speclangMod->doInsert($v);
                if ($r) {
                    continue;
                } else {
                    return false;
                    break;
                }
            }
            //向goods_spec_item_表插入数据
            $itemMod = &m('goodsSpecItem');
            $dataItem = array(
                'spec_id' => $res //规格id
            );
            //
            $dataItemLang = array();
            foreach ($specItem as $value) {
                //向goods_spec_item_表插入数据
                $itemid = $itemMod->doInsert($dataItem);
                foreach ($value as $k => $val) {
                    $dataItemLang[] = array(
                        'item_id' => $itemid,
                        'item_name' => $val,
                        'lang_id' => $k,
                        'add_time' => time()
                    );
                }
            }
            //向goods_spec_item_lang表循环插入多语言数据
            $itemLangMod = &m('goodsSpecItemLang');
            foreach ($dataItemLang as $item) {
                $r = $itemLangMod->doInsert($item);
                if ($r) {
                    continue;
                } else {
                    return false;
                    break;
                }
            }
        }
        //
        if ($res && $r) {
            $this->addLog('添加商品规格');
            $info['url'] = "admin.php?app=goodsSpec&act=specList&p={$p}";
            $this->setData($info, '1', $this->langDataBank->public->add_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->add_error);
        }
    }

    /**
     * 模型规格编辑
     * @author wh
     * @date 2017/07/31
     */
    public function edit() {
        $id = $_REQUEST['id'];  //规格id
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $sql = 'SELECT  s.id,s.`type_id`,l.`lang_id`,l.`spec_name`,s.`sort`
                FROM  ' . DB_PREFIX . 'goods_spec AS s
                LEFT JOIN  ' . DB_PREFIX . 'goods_spec_lang AS l ON s.`id` =l.`spec_id`   WHERE   s.`id` = ' . $id;
        $data = $this->goodsSpec->querySql($sql);
        if (!empty($data)) {
            $typeId = $data[0]['type_id'];
        } else {
            $typeId = 0;
        }
        //商品模型select
        $options = $this->getGoodsType($typeId);
        $this->assign('options', $options);
        //多语言版本
        $langMod = &m('language');
        $sql = 'select  id,name  from   ' . DB_PREFIX . 'language  order by id asc';
        $res = $langMod->querySql($sql);
        $this->assign('lang', $res);
        //规格名称的多语言
        $html = '';
        foreach ($res as $key => $val) {
            foreach ($data as $item) {
                if ($val['id'] == $item['lang_id']) {
                    $html .= ' <span class="mt10 mb5 inblock">' . $val['name'] . '：</span>
                               <input type="text" class="form-control" name="name[' . $item['lang_id'] . ']"  value="' . $item['spec_name'] . '"  >';
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

        //规格项的多语言版本
        $itemMod = &m('goodsSpecItem');
        $sql2 = 'SELECT  i.`spec_id`,l.`item_id`,GROUP_CONCAT(l.`item_name`,",",l.`lang_id`   ORDER BY l.id ASC  SEPARATOR "_"  )  AS items ,
                GROUP_CONCAT(l.`id`  ORDER BY l.id ASC  )  AS  ids
                FROM  ' . DB_PREFIX . 'goods_spec_item AS i
                LEFT JOIN  ' . DB_PREFIX . 'goods_spec_item_lang AS l ON i.`id` = l.`item_id`
                WHERE   i.`spec_id` = ' . $id . '  GROUP BY i.`id`';
        $arr = $itemMod->querySql($sql2);
        foreach ($arr as $key => $value) {
            $arr[$key]['items'] = explode('_', $value['items']);
        }
        foreach ($arr as $key => $value) {
            foreach ($value['items'] as $k => $val) {
                $arr[$key]['items'][$k] = explode(',', $val);
            }
        }
        foreach ($arr as $key => $value) {
            foreach ($value['items'] as $k => $val) {
                $arr[$key]['itemv'][$val[1]] = $val[0];
            }
            unset($arr[$key]['items']);
        }
        $hang = count($arr) + 1;

//        $sqllang = 'select  id,name  from   ' . DB_PREFIX . 'language';
//        $langArr = $langMod->querySql($sqllang);
//        $html2 = '';
//        foreach ($langArr as $key => $lang) {
//            foreach ($arr as $a) {
//                if ($lang['id'] == $a['lang_id']) {
//                    if (!empty($a['items'])) {
//                        $itemarr = explode(',', $a['items']);
//                        $str = implode("\r\n", $itemarr);
//                    } else {
//                        $str = '';
//                    }
//                    $html2 .= ' <span class="mt10 mb5 inblock">' . $lang['name'] . '：</span><textarea  rows="8"  cols="120"  style="resize:horizontal"  name="spec_item[' . $lang['id'] . '][' . $a['ids'] . ']" >' . $str . '</textarea>';
//                    unset($langArr[$key]);
//                }
//            }
//        }
//        //如果以后再添加新的语言
//        if (!empty($langArr)) {
//            foreach ($langArr as $v) {
//                $html2 .= ' <span class="mt10 mb5 inblock">' . $v['name'] . '：</span><textarea  rows="8"  cols="120"  style="resize:horizontal"  name="spec_item[' . $v['id'] . ']" ></textarea>';
//            }
//        }
//        $this->assign('html2', $html2);

        $this->assign('arr', $arr);
        $this->assign('hang', $hang);
        $this->assign('id', $id);
        $this->assign('p', $p);
        $this->assign('data', $data[0]);
        $this->assign('act', 'specList');
        $this->display('goodsSpec/specEdit.html');
    }

    public function doEdit() {
        $id = $_REQUEST['id'];  //规格id
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $name = !empty($_REQUEST['name']) ? $_REQUEST['name'] : array(); //规格名称
        $typeId = $_REQUEST['type_id'];
        $sort = $_REQUEST['sort'] ? htmlspecialchars(trim($_REQUEST['sort'])) : 5;
        $specItem = !empty($_REQUEST['item']) ? $_REQUEST['item'] : array(); //规格项
        //数据的判断
        if (empty($id)) {
            $this->setData(array(), $status = '0', $this->langDataBank->project->data_error);
        }
        if (empty($typeId)) {
            $this->setData(array(), $status = '0', $this->langDataBank->project->model_required);
        }
        if (!empty($sort)) {
            if (!preg_match("/^[1-9][0-9]{0,2}$/", $sort)) {
                $this->setData(array(), '0', $this->langDataBank->project->sort_rule);
            }
        }
        //
        foreach ($name as $key => $val) {
            $name[$key] = trim($val);
        }
        foreach ($name as $val) {
            if (empty($val)) {
                $this->setData(array(), $status = '0', $this->langDataBank->project->specific_required);
            } else {
                $nameInfo = $this->getInfo($val, $typeId, $id); //判断唯一性
                if (!empty($nameInfo)) {
                    $this->setData(array(), $status = '0', $this->langDataBank->project->specific_repeat);
                }
            }
        }
        $insertData = array(); //添加的数据
        $editData = array(); // 编辑的数据
        foreach ($specItem as $value) {
            if (isset($value['ids'])) {
                $editData[] = $value;
            } else {
                $insertData[] = $value;
            }
        }
        //
        $editIds = array();
        foreach ($editData as $k => $val) {
            $editIds[] = explode(',', $val['ids']);
            unset($editData[$k]['ids']);
        }
        //判断数据
        if (!empty($insertData)) {   //新添加的数据
            foreach ($insertData as $key => $value) {
                foreach ($value as $k => $v) {
                    if (empty($v)) {
                        $this->setData(array(), '0', $this->langDataBank->project->specific_item_required);
                    } else {
                        $insertData[$key][$k] = trim($v);
                    }
                }
            }
        }
        // 编辑的老数据
        foreach ($editData as $ke => $val) {
            foreach ($val as $i => $va) {
                if (empty($va)) {
                    $this->setData(array(), '0', $this->langDataBank->project->specific_item_required);
                } else {
                    $editData[$ke][$i] = trim($va);
                }
            }
        }

        //变成一维数组
        $arr1 = array();
        foreach ($editData as $value) {
            foreach ($value as $v) {
                $arr1[] = $v;
            }
        }
        $arr2 = array();
        foreach ($editIds as $value) {
            foreach ($value as $v) {
                $arr2[] = $v;
            }
        }
        //
        $itemLangEditD = array();
        foreach ($arr2 as $key => $val) {
            $itemLangEditD[$val] = array(
                'item_name' => $arr1[$key]
            );
        }
        //编辑主表goods_spec信息
        $specD = array(
            'type_id' => $typeId,
            'sort' => $sort,
        );
        $res = $this->goodsSpec->doEdit($id, $specD);
        if ($res) {
            //删除goods_spec_lang
            $speclangMod = &m('goodsSpecLang');
            $where2 = 'spec_id  in(' . $id . ')';
            $speclangMod->doDrops($where2);
            //向goods_spec_lang 表循环插入多语言数据
            $dataSl = array();
            foreach ($name as $key => $val) {
                $dataSl[] = array(
                    'spec_name' => $val,
                    'lang_id' => $key,
                    'spec_id' => $id,
                    'add_time' => time()
                );
            }
            foreach ($dataSl as $v) {
                $r = $speclangMod->doInsert($v);
                if ($r) {
                    continue;
                } else {
                    return false;
                    break;
                }
            }
            //向goods_spec_item_lang表循环插入或者编辑多语言数据
            $itemMod = &m('goodsSpecItem');
            $itemLangMod = &m('goodsSpecItemLang');
            //编辑数据
            foreach ($itemLangEditD as $i => $item) {
                $r = $itemLangMod->doEdit($i, $item);
                if ($r) {
                    continue;
                } else {
                    return false;
                    break;
                }
            }
            //插入数据
            $intemLangInsertD = array();
            $itemData = array(
                'spec_id' => $id //规格id
            );
            if (!empty($insertData)) {  //当增加数据的时候
                foreach ($insertData as $value) {
                    //向goods_spec_item 表插入数据
                    $itemid = $itemMod->doinsert($itemData);
                    foreach ($value as $k => $val) {
                        $intemLangInsertD[] = array(
                            'item_id' => $itemid,
                            'item_name' => $val,
                            'lang_id' => $k,
                            'add_time' => time()
                        );
                    }
                }
                //向向goods_spec_item_lang 表插入数据
                foreach ($intemLangInsertD as $itm) {
                    $r = $itemLangMod->doInsert($itm);
                    if ($r) {
                        continue;
                    } else {
                        return false;
                        break;
                    }
                }
            }
            //
            if ($r) {
                $this->addLog('编辑商品规格');
                $info['url'] = "admin.php?app=goodsSpec&act=specList&p={$p}";
                $this->setData($info, '1',  $this->langDataBank->public->edit_success);
            } else {
                $this->setData(array(), '0',  $this->langDataBank->public->edit_fail);
            }
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->edit_fail);
        }
    }

    /**
     * 模型规格名称
     * @author wh
     * @date 2017/07/31
     */
    public function getInfo($specName, $typeId, $specid = 0) {
        $where = '  where 1=1';
        if (empty($specid)) {
            $where .= '  and s.type_id=' . $typeId . '  and  l.spec_name ="' . $specName . '"';
        } else {
            $where .= '  and s.type_id=' . $typeId . '  and  l.spec_name  ="' . $specName . '"' . '  and  l.`spec_id`!=' . $specid;
        }
        $sql = 'select  l.id  from ' . DB_PREFIX . 'goods_spec  as s
                left  join   ' . DB_PREFIX . 'goods_spec_lang  as l  on  s.id =l.spec_id ' . $where;

        $data = $this->goodsSpec->querySql($sql);
        if (!empty($data)) {
            return $data[0]['id'];
        } else {
            return null;
        }
    }

    public function getItemLangIds($ids) {
        $itemMod = &m('goodsSpecItem');
        $sql = 'SELECT  GROUP_CONCAT(id) AS ids  FROM  ' . DB_PREFIX . 'goods_spec_item  WHERE  spec_id IN(' . $ids . ') ';
        $res = $itemMod->querySql($sql);
        return $res[0]['ids'];
    }

    /**
     * 模型规格删除
     * @author wh
     * @date 2017/07/31
     */
    public function dele() {
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        if (empty($id)) {
            $this->setData(array(), '0', $this->langDataBank->public->system_error);
        }
        // 删除主表数据
        $where1 = 'id  in(' . $id . ')';
        $res = $this->goodsSpec->doDrops($where1);
        // 删除主表的多语言表
        $speclangMod = &m('goodsSpecLang');
        $where2 = 'spec_id  in(' . $id . ')';
        $res2 = $speclangMod->doDrops($where2);
        // 获取  goods_spec_item 的ids
        $itemids = $this->getItemLangIds($id);
        //删除goods_spec_item表数据
        $itemMod = &m('goodsSpecItem');
        $where = 'spec_id  in(' . $id . ')';
        $res3 = $itemMod->doDrops($where);
        //删除 goods_spec_item_lang 表数据
        $itemlangMod = &m('goodsSpecItemLang');
        $where = 'item_id  in(' . $itemids . ')';
        $res4 = $itemlangMod->doDrops($where);
        //
        if ($res && $res2 && $res3 && $res4) {   //删除成功
            $this->addLog('删除商品规格');
            $this->setData(array(), '1', $this->langDataBank->public->drop_success);
        } else {
            $this->setData(array(), '0', $this->langDataBank->public->drop_fail);
        }
    }

}
