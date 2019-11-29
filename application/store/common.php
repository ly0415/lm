<?php

// 应用公共函数库文件

use app\store\service\Auth;

/**
 * 验证指定url是否有访问权限
 * @param string|array $url
 * @param bool $strict 严格模式
 * @return bool
 */
function checkPrivilege($url, $strict = true)
{
    try {
        return Auth::getInstance()->checkPrivilege($url, $strict);
    } catch (\Exception $e) {
        return false;
    }
}

/**
 * 时间查询
 * @author  luffy
 * @date    2019-08-05
 */
function timeCond($where, $field_name, $start_time, $end_time) {
    if($start_time) $start_time = strtotime($start_time);
    if($end_time)   $end_time   = strtotime($end_time. ' 23:59:59');
    if($start_time && $end_time && $start_time > $end_time){
        return false;
    }
    if ($start_time && !$end_time) {
        $where[$field_name]  = ['EGT',$start_time];
    }
    if (!$start_time && $end_time) {
        $where[$field_name]  = ['ELT',$end_time];
    }
    if ($start_time && $end_time) {
        $where[$field_name]  = ['BETWEEN',[$start_time, $end_time]];
    }
    return $where;
}

/**
 * 选择下拉框组件
 *
 * @param    array $arr
 * @param     int or string $selected
 * @param     string $show_field 支持多个字段显示 格式field_a,field_b
 * @param   string $val_field
 * @return     string
 */
function make_option($arr, $selected='', $show_field='', $val_field='') {
    $ret = '';
    $show_field_arr = explode(',', $show_field);
    if (is_array($arr)) {
        foreach ($arr as $k => $v) {
            $show_text = '';
            if (is_array($v)) {
                foreach ($show_field_arr as $s) {
                    $show_text .= $v[$s].' ';
                }
                $show_text = substr($show_text, 0, -1);
                $val_field && $k = $v[$val_field];
            } else {
                $show_text = $v;
            }
            $sel = '';
            if ($selected && $k == $selected) {
                $sel = 'selected="selected"';
            }
            $ret .= '<option value="' . $k . '" ' . $sel . '>' . $show_text . '</option>';
        }
    }
    return $ret;
}

/**
 * 多个数组的笛卡尔积
 *
 * @param unknown_type $data
 */
function combineDika() {
    $data = func_get_args();
    $data = current($data);
    $cnt = count($data);
    $result = array();
    $arr1 = array_shift($data);
    foreach($arr1 as $key=>$item)
    {
        $result[] = array($item);
    }

    foreach($data as $key=>$item)
    {
        $result = combineArray($result,$item);
    }
    return $result;
}


/**
 * 两个数组的笛卡尔积
 * @param unknown_type $arr1
 * @param unknown_type $arr2
 */
function combineArray($arr1,$arr2) {
    $result = array();
    foreach ($arr1 as $item1)
    {
        foreach ($arr2 as $item2)
        {
            $temp = $item1;
            $temp[] = $item2;
            $result[] = $temp;
        }
    }
    return $result;
}
