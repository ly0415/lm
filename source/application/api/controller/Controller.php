<?php

namespace app\api\controller;

use app\api\model\User as UserModel;
use app\api\model\Wxapp as WxappModel;
use app\common\exception\BaseException;
use think\Db;

/**
 * API控制器基类
 * Class BaseController
 * @package app\store\controller
 */
class Controller extends \think\Controller
{
    const JSON_SUCCESS_STATUS = 1;
    const JSON_ERROR_STATUS = 0;

    protected $langData;

    protected $lang_id = 29;

    protected $shorthand;

    /**
     * API基类初始化
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        //获取语言简写
        $this->getShorthand();

        //获取语言包
        $this->langData = languageFun($this->shorthand);

    }

    /**
     * 获取当前小程序ID
     * @return mixed
     * @throws BaseException
     */
    private function getWxappId()
    {
        if (!$wxapp_id = $this->request->param('wxapp_id')) {
            throw new BaseException(['msg' => '缺少必要的参数：wxapp_id']);
        }
        return $wxapp_id;
    }

    public function getShorthand(){
        $this->shorthand = Db::name('language')
            ->where('id','=',$this->lang_id)
            ->value('shorthand');
    }

    /**
     * 验证当前小程序状态
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    private function checkWxapp()
    {
        $wxapp = WxappModel::detail($this->wxapp_id);
        if (empty($wxapp)) {
            throw new BaseException(['msg' => '当前小程序信息不存在']);
        }
        if ($wxapp['is_recycle'] || $wxapp['is_delete']) {
            throw new BaseException(['msg' => '当前小程序已删除']);
        }
    }

    /**
     * 获取当前用户信息
     * @param bool $is_force
     * @return UserModel|bool|null
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    protected function getUser($is_force = true)
    {
        if (!$token = $this->request->param('token')) {
            $is_force && $this->throwError('缺少必要的参数：token', -1);
            return false;
        }
        if (!$user = UserModel::getUser($token)) {
            $is_force && $this->throwError('没有找到用户信息', -1);
            return false;
        }
        return $user;
    }

    /**
     * 输出错误信息
     * @param int $code
     * @param $msg
     * @throws BaseException
     */
    protected function throwError($msg, $code = 0)
    {
        throw new BaseException(['code' => $code, 'msg' => $msg]);
    }

    /**
     * 返回封装后的 API 数据到客户端
     * @param int $code
     * @param string $msg
     * @param array $data
     * @return array
     */
    protected function renderJson($code = self::JSON_SUCCESS_STATUS, $msg = '', $data = [])
    {
        return compact('code', 'msg', 'data');
    }

    /**
     * 返回操作成功json
     * @param array $data
     * @param string|array $msg
     * @return array
     */
    protected function renderSuccess($data = [], $msg = 'success')
    {
        return $this->renderJson(self::JSON_SUCCESS_STATUS, $msg, $data);
    }
 
    /**
     * 返回操作失败json
     * @param string $msg
     * @param array $data
     * @return array
     */
    protected function renderError($msg = 'error', $data = [])
    {
        return $this->renderJson(self::JSON_ERROR_STATUS, $msg, $data);
    }

    /**
     * 获取post数据 (数组)
     * @param $key
     * @return mixed
     */
    protected function postData($key = null)
    {
        return $this->request->post(is_null($key) ? '' : $key . '/a');
    }

}
