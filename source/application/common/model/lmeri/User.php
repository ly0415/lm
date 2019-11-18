<?php

namespace app\common\model\lmeri;

use think\Session;
use app\common\model\BaseModel;

/**
 * 商家用户模型
 * Class User
 * @package app\common\model
 */
class User extends BaseModel
{
    protected $name = 'lmeri_user';

    /**
     * 保存登录状态
     * @param $user
     * @throws \think\Exception
     */
    public function loginState($user)
    {
        /** @var \app\common\model\Wxapp $wxapp */
        // 保存登录状态
        Session::set('yoshop_lmeri', [
            'user' => [
                'id'        => $user['id'],
                'user_name' => $user['user_name'],
            ],
            'is_login' => true,
        ]);
    }

    /**
     * 验证用户名是否重复
     * @param $user_name
     * @return bool
     */
    public static function checkExist($user_name)
    {
        return !!static::useGlobalScope(false)
            ->where('user_name', '=', $user_name)
            ->value('id');
    }

    /**
     * 关联用户角色表
     * @return \think\model\relation\BelongsToMany
     */
    public function role()
    {
        return $this->belongsToMany('Role', 'LmeriUserRole');
    }

    /**
     * 超管用户详情
     * @param $where
     * @param array $with
     * @return static|null
     * @throws \think\exception\DbException
     */
    public static function detail($where, $with = [])
    {
        !is_array($where) && $where = ['id' => (int)$where];
        return static::get(array_merge(['mark' => 1], $where), $with);
    }
}
