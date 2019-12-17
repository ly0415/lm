<?php

namespace app\common\library;

/**
 * 工具类
 * Class helper
 * @package app\common\library
 */
class helper
{
    /**
     * 获取数组中指定的列
     * @param $source
     * @param $column
     * @return array
     */
    public static function getArrayColumn($source, $column)
    {
        $columnArr = [];
        foreach ($source as $item) {
            $columnArr[] = $item[$column];
        }
        return $columnArr;
    }

    /**
     * 获取数组中指定的列
     * @param $source
     * @param $columns
     * @return array
     */
    public static function getArrayColumns($source, $columns)
    {
        $columnArr = [];
        foreach ($source as $item) {
            $temp = [];
            foreach ($columns as $index) {
                $temp[$index] = $item[$index];
            }
            $columnArr[] = $temp;
        }
        return $columnArr;
    }

    /**
     * 把二维数组中某列设置为key返回
     * @param $source
     * @param $index
     * @return array
     */
    public static function arrayColumn2Key($source, $index)
    {
        $data = [];
        foreach ($source as $item) {
            $data[$item[$index]] = $item;
        }
        return $data;
    }

}