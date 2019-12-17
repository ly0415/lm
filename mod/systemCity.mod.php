<?php
/**
 * 城市模型
 * @author zhangkx
 * @date 2019-03-22
 */
if (!defined('IN_ECM')) { die('Forbidden'); }
class SystemCityMod extends  BaseMod{
    /**
     * 构造函数
     */
    public function __construct() {
        parent::__construct("system_city");
    }

    /**
     * 无限极分类 by xt 2019.03.25
     * @param $orig
     * @return mixed
     */
    public function getTree($orig) {
        //解决下标不是1开始的问题
        $items = array();
        foreach ($orig as $key => $value) {
            $items[$value['id']] = $value;
        }
        //开始组装
        $tree = array();
        foreach ($items as $key => $item) {
            if ($item['parent_id'] == 0) {  //为0，则为1级分类
                $tree[] = &$items[$key];
            } else {
                if (isset($items[$item['parent_id']])) { //存在值则为二级分类
                    $items[$item['parent_id']]['childs'][] = &$items[$key];  //传引用直接赋值与改变
                } else { //至少三级分类
                    //由于是传引用思想，这里将不会有值
                    $tree[] = &$items[$key];
                }
            }
        }

        return $tree[0]['childs'];
    }
}