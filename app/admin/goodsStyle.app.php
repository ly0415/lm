<?php

/**
 * 商品风格控制器
 * @author  wh
 * @date 2017-8-3
 */
if (!defined('IN_ECM')) {
    die('Forbidden');
}

class GoodsStyleApp extends BackendApp {

    private $goodsStyle;
    private $goodsStyleLang;

    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct();
        $this->goodsStyle = &m('goodsStyle');
        $this->goodsStyleLang = &m('goodsStyleLang');
    }

    /**
     * 析构函数
     */
    public function __destruct() {
        
    }

    /**
     * 商品风格列表
     * @author  wh
     * @date 2017-8-3
     */
    public function styleList() {
        $sname = !empty($_REQUEST['sname']) ? trim($_REQUEST['sname']) : '';
        $where = ' where 1=1  and  l.lang_id =' . $this->lang_id;
        //搜索
        if(!empty($sname)){
            $where .= '  and  l.`style_name` like "%' .addslashes(addslashes($sname)) . '%"';
        }
        // 获取总数
        $totalSql = "select count(*) as totalCount from " . DB_PREFIX . "goods_style ";
        $totalCount = $this->goodsStyle->querySql($totalSql);
        $total = $totalCount[0]['totalCount'];
        $p = !empty($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        //展示页面
        $sql = "SELECT  s.`id`,l.`style_name`,s.`add_time`   FROM  " . DB_PREFIX . "goods_style  AS s
                 LEFT JOIN   " . DB_PREFIX . "goods_style_lang  l  ON  s.id = l.`style_id`" . $where;
        $sql .= '   order by s.id desc ';
        $res = $this->goodsStyle->querySqlPageData($sql, $array = array("pre_page" => 20, "is_sql" => false, "mode" => 1));
        foreach ($res['list'] as $k => $v) {
            $res['list'][$k]['add_time'] = !empty($v['add_time']) ? date('Y-m-d H:i:s', $v['add_time']) : '';
            if ($v['add_time']) {
                $res['list'][$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            } else {
                $res['list'][$k]['add_time'] = '';
            }
            $res['list'][$k]['sort_id'] = $k + 20 * ($p - 1) + 1; //正序
            $res['list'][$k]['style_name'] = stripslashes($v['style_name']); //正序
//            $rs['list'][$k]['sort'] = $total - $k - 20 * ($p - 1); //倒叙

        }
        $this->assign('sName', stripslashes($sname));
        $this->assign('res', $res['list']);
        $this->assign('page_html', $res['ph']);
        $this->display('goodsStyle/styleIndex.html');
    }

    /**
     * 商品风格添加
     * @author  wh
     * @date 2017-8-3
     */
    public function add() {
        //多语言版本
        $langMod = &m('language');
        $sql = 'select  id,name  from   ' . DB_PREFIX . 'language';
        $data = $langMod->querySql($sql);
        $html = '';
        foreach ($data as $val) {
            $html .= ' <span class="mt10 mb5 inblock">' . $val['name'] . '：</span><input type="text" class="form-control" name="name[' . $val['id'] . ']" >';
        }
        $this->assign('html', $html);
        $this->display('goodsStyle/styleAdd.html');
    }

    /**
     * 商品风格添加处理
     * @author  wangshuo
     * @date 2017-9-28
     */
    public function doAdd() {
        $name = !empty($_REQUEST['name']) ? $_REQUEST['name'] : array();
        //判断数据
        foreach ($name as $key => $val) {
            $name[$key] = addslashes(trim($val));
        }
        //判断数据
        foreach ($name as $val) {
            if (empty($val)) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->project->style_required);
                break;
            } else {
                $aff = $this->getOneInfo($val);
                if (!empty($aff)) {
                    $this->setData($info = array(), $status = '0', $this->langDataBank->project->style_exist);
                    break;
                }
            }
        }
        $insertData = array(
            'add_time' => time()
        );
        $insert_id = $this->goodsStyle->doInsert($insertData);
        if ($insert_id) {
            //添加多语言版本信息
            $this->doLangData($name, $insert_id);
            //
            $this->addLog('分类添加操作');
            $info['url'] = "admin.php?app=goodsStyle&act=styleList";
            $this->setData($info, $status = '1', $this->langDataBank->public->add_success);
        } else {
            $this->setData($info = array(), $status = '0', $this->langDataBank->public->add_error);
        }
    }

    /**
     * 添加多语言版本信息
     */
    public function doLangData($name, $insert_id) {
        $gCLangMod = &m('goodsStyleLang');
        $data = array();
        foreach ($name as $key => $val) {
            $data[] = array(
                'lang_id' => $key,
                'style_name' => $val,
                'style_id' => $insert_id,
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
        $gCLangMod = &m('goodsStyleLang');
        $where = '  where 1=1';
        if (empty($id)) {
            //添加
            $where .= '  and  l.style_name = "' . $name . '"';
        } else {
            //编辑
            $where .= '  and   id!=' . $id . '  and  l.style_name = "' . $name . '"';
        }
        $sql = 'select * from  ' . DB_PREFIX . 'goods_style as g  left join ' . DB_PREFIX . 'goods_style_lang
          as l on g.id=l.style_id' . $where;
        $res = $gCLangMod->querySql($sql);
        return $res;
    }

    /**
     * 商品风格编辑
     * @author  wangshuo
     * @date 2017-9-28
     */
    public function edit() {
        $id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : '0';
        //多语言版本
        $gCLangMod = &m('goodsStyleLang');
        $sql = 'select  id,lang_id,style_name,style_id  from   ' . DB_PREFIX . 'goods_style_lang  where  style_id =' . $id;
        $data = $gCLangMod->querySql($sql);
        $langMod = &m('language');
        $sql = 'select  id,name  from   ' . DB_PREFIX . 'language';
        $res = $langMod->querySql($sql);
        $html = '';
        foreach ($res as $key => $val) {
            foreach ($data as $item) {
                if ($val['id'] == $item['lang_id']) {
                    $html .= ' <span class="mt10 mb5 inblock">' . $val['name'] . '：</span>
                               <input type="text" class="form-control" name="name[' . $item['lang_id'] . ']"  value="' . $item['style_name'] . '"  >';
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
        $this->assign('id', $_GET['id']);
        $this->display('goodsStyle/styleEdit.html');
    }

    /**
     * 商品风格编辑处理
     * @author  wangshuo
     * @date 2017-9-28
     */
    public function doEdit() {
        $id = !empty($_REQUEST['id']) ? htmlspecialchars(trim($_REQUEST['id'])) : ''; //分类id
        $name = !empty($_REQUEST['name']) ? $_REQUEST['name'] : array();
        //判断数据
        foreach ($name as $key => $val) {
            $name[$key] = addslashes(trim($val));
        }
        //判断数据
        foreach ($name as $val) {
            if (empty($val)) {
                $this->setData($info = array(), $status = '0', $this->langDataBank->project->style_required);
                break;
            } else {
                //
                $styleId = $this->getCtgLang($val, $id);  //style_lang 表里的id
                $aff = $this->getOneInfo($val, $styleId); //判断是否命名重复 
                if (!empty($aff)) {
                    $this->setData($info = array(), $status = '0',$this->langDataBank->project->style_exist);
                    break;
                }
            }
        }

        $data = array(
            'table' => 'goods_style',
            'add_time' => time(),
        );
        $edit_id = $this->goodsStyle->doEdit($id, $data);
        if ($edit_id) {
            //删除原来的多版本信息
            $gCLangMod = &m('goodsStyleLang');
            $where = '  style_id =' . $id;
            $gCLangMod->doDrops($where);
            //添加多语言版本信息

            $this->doLangData($name, $id);
            //
            $this->addLog('分类更新操作');
            $info['url'] = "admin.php?app=goodsStyle&act=styleList";
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
        $gCLangMod = &m('goodsStyleLang');
        $sql = 'select  id,lang_id,style_name,style_id  from   ' . DB_PREFIX . 'goods_style_lang
                 where  style_id =' . $id . '   and   style_name = "' . stripslashes($name) . '"';
        $item = $gCLangMod->querySql($sql);
        if (!empty($item)) {
            return $item[0]['id'];
        } else {
            return 0;
        }
    }

    /**
     * 商品风格删除
     * @author  wangshuo
     * @date 2017-9-29
     */
    public function dele() {
        $id = $_REQUEST['id'] ? htmlspecialchars(trim($_REQUEST['id'])) : '';
        if (empty($id)) {
            $this->setData(array(), '0', $this->langDataBank->public->system_error);
        }
        // 删除主表数据
        $where = 'attr_id  in(' . $id . ')';
        $res = $this->goodsStyle->doDrops($where);
        if ($res) {
            //删除子表的信息
            $where2 = 'style_id  in(' . $id . ')';
            $this->goodsStyleLang->doDrops($where2);
            $this->addLog('分类删除操作');
            $this->setData($info = array(), $status = '1',  $this->langDataBank->public->drop_success);
        } else {
            $this->setData($info = array(), $status = '0',  $this->langDataBank->public->drop_fail);
        }
    }

}
