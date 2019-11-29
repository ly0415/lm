<?php

namespace app\common\model;

use think\Model;
use think\Request;
use think\Session;

/**
 * 模型基类
 * Class BaseModel
 * @package app\common\model
 */
class BaseModel extends Model
{
    public static $base_url;

    protected $alias = '';

    /**
     * 模型基类初始化
     */
    public static function init()
    {
        parent::init();
        // 获取当前域名
        self::$base_url = base_url();
    }

    /**
     * 获取当前调用的模块名称
     * 例如：admin, api, store, task
     * @return string|bool
     */
    protected static function getCalledModule()
    {
        if (preg_match('/app\\\(\w+)/', get_called_class(), $class)) {
            return $class[1];
        }
        return false;
    }

    /**
     * 设置默认的检索数据
     * @param $query
     * @param array $default
     * @return array
     */
    protected function setQueryDefaultValue(&$query, $default = [])
    {
        $data = array_merge($default, $query);
        foreach ($query as $key => $val) {
            if (empty($val) && isset($default[$key])) {
                $data[$key] = $default[$key];
            }
        }
        return $data;
    }

    /**
     * 设置基础查询条件（用于简化基础alias和field）
     * @test 2019-4-25
     * @param string $alias
     * @param array $join
     * @return BaseModel
     */
    protected function setBaseQuery($alias = '', $join = [])
    {
        // 设置别名
        $aliasValue = $alias ?: $this->alias;
        $model = $this->alias($aliasValue)->field("{$aliasValue}.*");
        // join条件
        if (!empty($join)) : foreach ($join as $item):
            $model->join($item[0], "{$item[0]}.{$item[1]}={$aliasValue}."
                . (isset($item[2]) ? $item[2] : $item[1]));
        endforeach; endif;
        return $model;
    }

}
