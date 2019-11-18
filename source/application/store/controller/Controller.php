<?php

namespace app\store\controller;

use app\store\model\StoreConsole;
use think\Request;
use think\Session;
use app\store\service\Auth;
use app\store\service\Menus;
use app\store\model\Order as OrderModel;
use app\store\model\store\StoreUser as StoreUserModel;
use app\store\model\Store as StoreModel;
use app\common\exception\BaseException;

/**
 * 商户后台控制器基类
 * Class BaseController
 * @package app\store\controller
 */
class Controller extends \think\Controller
{
    /** @var array $store 商家登录信息 */
    protected $yoshop_store;

    protected $error;

    /** @var string $route 当前控制器名称 */
    protected $controller = '';

    /** @var string $route 当前方法名称 */
    protected $action = '';

    /** @var string $route 当前路由uri */
    protected $routeUri = '';

    /** @var string $route 当前路由：分组名称 */
    protected $group = '';

    /** @var array $allowAllAction 登录验证白名单 */
    protected $allowAllAction = [
        // 登录页面
        'passport/login',
    ];

    /* @var array $notLayoutAction 无需全局layout */
    protected $notLayoutAction = [
        // 登录页面
        'passport/login',
    ];

    /**
     * 基类
     * @author  luffy
     * @date    2019-09-02
     */
    public function _initialize()
    {
        //基础数据获取初始化
        $this->initData();
        // 当前路由信息
        $this->getRouteinfo();
        // 验证登录状态
        $this->checkLogin();
        // 验证当前页面权限
        $this->checkPrivilege();
        // 全局layout
        $this->layout();

        if(!in_array($this->action,['get_notips_order','query_order_status','login','logout','extract'])){
            //获取未提示的订单以及order_sn
            $OrderModel = new OrderModel;
            $OrderModel->getNoTipsOrder(STORE_ID);
        }
    }

    /**
     * 基础数据获取初始化
     * @author  luffy
     * @date    2019-09-02
     */
    private function initData()
    {
        //店员信息
        $this->yoshop_store     = Session::get('yoshop_store');
        $current_store_id       = $this->yoshop_store['store_id'];
        //登录用户ID（包含总站管理人员、业务后台管理人员、店铺管理人员）
        define('USER_ID',  $this->yoshop_store['user']['store_user_id']);
        //总管理人员
        define('IS_ADMIN', $this->yoshop_store['is_admin']);
        //获取业务类型
        $business_id    = (new StoreUserModel())->getUserBusiness(USER_ID);
        $business_id    = $business_id != 0 ? $business_id : 120;
        define('BUSINESS_ID', $business_id);

        define('T_ADMIN',    (IS_ADMIN && !BUSINESS_ID ) ? TRUE : FALSE);
        define('T_BUSINESS', (IS_ADMIN && BUSINESS_ID )  ? TRUE : FALSE);
        define('T_GENERAL',  !IS_ADMIN                   ? TRUE : FALSE);
        if(T_BUSINESS){
            $StoreModel = new StoreModel;
            $storeList  = $StoreModel::getStoreList(TRUE, BUSINESS_ID);
            $store_string     = implode(',', array_keys($storeList));
        } else {
            $store_string     = $current_store_id;
        }
        //品牌业务下的店铺搜索默认展示第一个店铺
        define('STORE_ID'       , $current_store_id);
        define('SELECT_STORE_ID', StoreModel::getListStoreId());
        define('STORE_IDS'      , $store_string);
        define('STORE_CATE'     , 17);
    }

    /**
     * 验证当前页面权限
     * @throws BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function checkPrivilege()
    {
        if ($this->routeUri === 'index/index') {
            return true;
        }

        if (!Auth::getInstance()->checkPrivilege($this->routeUri)) {
            throw new BaseException(['msg' => '很抱歉，没有访问权限']);
        }
        return true;
    }

    /**
     * 全局layout模板输出
     * @throws \think\exception\DbException
     * @throws \Exception
     */
    private function layout()
    {
        // 验证当前请求是否在白名单
        if (!in_array($this->routeUri, $this->notLayoutAction)) {
            //定义无需设置权限人员的不走自动打印操作
            $waring_user    = !in_array(USER_ID, [
                52
            ]);
            // 输出到view
            $this->assign([
                'base_url'  => base_url(),                        // 当前域名
                'store_url' => url('/store','',false),                // 后台模块url
                'group'     => $this->group,                      // 当前控制器分组
                'tips_data' => (new OrderModel)->tipsData(),      // 查询公共统计数据
                'menus'     => $this->menus(),                    // 后台菜单
                'store'     => $this->yoshop_store,               // 商家登录信息
//                'setting' => Setting::getAll() ?: null,         // 当前商城设置
                'request'   => Request::instance(),               // Request对象
                'version'   => get_version(),                     // 系统版本号
                'tipsAuth'  => [
                    (new StoreConsole)->getPrintAuth(USER_ID),     // 是否设置打印权限
                    $waring_user        // 白名单,store_user_id
                ]
            ]);
        }
    }

    /**
     * 解析当前路由参数 （分组名称、控制器名称、方法名）
     */
    protected function getRouteinfo()
    {
        // 控制器名称
        $this->controller = toUnderScore($this->request->controller());
        // 方法名称
        $this->action = $this->request->action();
        // 控制器分组 (用于定义所属模块)
        $groupstr = strstr($this->controller, '.', true);
        $this->group = $groupstr !== false ? $groupstr : $this->controller;
        // 当前uri
        $this->routeUri = $this->controller . '/' . $this->action;
    }

    /**
     * 后台菜单配置
     * @return mixed
     * @throws \think\exception\DbException
     */
    protected function menus()
    {
        static $menus = [];
        if (empty($menus)) {
            $menus = Menus::getInstance()->getMenus($this->routeUri, $this->group);
        }
        if(isset($menus['store']) && !$this->yoshop_store['is_admin'])
            unset($menus['store']);
        return $menus;
    }

    /**
     * 验证登录状态
     * @return bool
     */
    private function checkLogin()
    {
        // 验证当前请求是否在白名单
        if (in_array($this->routeUri, $this->allowAllAction)) {
            return true;
        }
        // 验证登录状态
        if (empty($this->yoshop_store)
            || (int)$this->yoshop_store['is_login'] !== 1
        ) {
            $this->redirect('passport/login');
            return false;
        }
        return true;
    }

    /**
     * 返回封装后的 API 数据到客户端
     * @param int $code
     * @param string $msg
     * @param string $url
     * @param array $data
     * @return array
     */
    protected function renderJson($code = 1, $msg = '', $url = '', $data = [])
    {
        return compact('code', 'msg', 'url', 'data');
    }

    /**
     * 返回操作成功json
     * @param string $msg
     * @param string $url
     * @param array $data
     * @return array
     */
    protected function renderSuccess($msg = 'success', $url = '', $data = [])
    {
        return $this->renderJson(1, $msg, $url, $data);
    }

    /**
     * 返回操作失败json
     * @param string $msg
     * @param string $url
     * @param array $data
     * @return array|bool
     */
    protected function renderError($msg = 'error', $url = '', $data = [])
    {
        if ($this->request->isAjax()) {
            return $this->renderJson(0, $msg, $url, $data);
        }
        $this->error($msg);
        return false;
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

    /**
     * 获取post数据 (数组)
     * @param $key
     * @return mixed
     */
    protected function getData($key = null)
    {
        return $this->request->get(is_null($key) ? '' : $key);
    }

    /**
     * 模糊查询
     */
    protected function fuzzyCond(&$cond=[], $field_name='name'){
        $show_field = '';
        if (strpos($field_name, '.') !== false) {           //连表查询条件
            $field_name_arr = explode('.', $field_name);
            $show_field     = $field_name_arr[1];
        } else {
            $show_field     = $field_name;
        }
        $field = input("?get.{$show_field}") ? input("get.{$show_field}/s", '', 'trim') : '';
        if ($field) {
            $cond[$field_name]  = ['LIKE', "%{$field}%"];
        }
        $this->assign($show_field, $field);
    }

    /**
     * 精准查询
     */
    protected function queryCond(&$cond=[], $field_name='status', $type='select', $field_arr=[]){
        $show_field = '';
        if (strpos($field_name, '.') !== false) {//连表查询条件
            $field_name_arr = explode('.', $field_name);
            $show_field = $field_name_arr[1];
        } else {
            $show_field = $field_name;
        }
        $field = input("?get.{$show_field}") ? trim(input("get.{$show_field}")) : '';
        if ($field) {
            $cond[$field_name] = $field;
            $this->assign("{$show_field}", $field);
        }
        if ($type == 'select') {
            if (empty($field_arr) && isset($this->model->$show_field)) {
                $field_arr = $this->model->$show_field;
            }
            $field_option = make_option($field_arr, $field);
            $this->assign("{$show_field}", $field_arr);
            $this->assign("{$show_field}_option", $field_option);
        }
    }


    /**
     * 格式化规格
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-08-21
     * Time: 21:43
     */
    public function formatData($store_goods_id,$data){
        $specInfo = array();
        foreach ($data as $v) {
            if (isset($specInfo[$v['id']])) {
                $specInfo[$v['id']]['itemInfo'][] = array(
                    'item_id' => $v['item_id'],
                    'item_name' => $v['item_name']
                );
            } else {
                $specInfo[$v['id']] = array(
                    'id' => $v['id'],
                    'store_goods_id' => $store_goods_id,
                    'spec_name' => $v['name'],
                    'itemInfo' => array(array(
                        'item_id' => $v['item_id'],
                        'item_name' => $v['item_name']
                    ))
                );
            }
        }
        return $specInfo;
    }


    /**
     * 获取跳转地址
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-05
     * Time: 17:37
     */
    public function getJumpUrl($payType = null,$data = []){
        $url = '';
        switch ($payType){
            case 1:
                $url = url('alipay/pay',$data);
                break;
            case 2:
                $url = url('wxpay/pay',$data);
                break;
            case 3:
            case 4:
                $url = url('order/index',$data);
                break;
        }
        return $url;
    }

    /**
     * 提示信息
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-11
     * Time: 16:24
     */
    public function getMsg($payType = 1){
        $msg = '';
        switch ($payType){
            case 1:
            case 2:
                $msg = '订单提交成功,前往支付';
                break;
            case 3:
            case 4:
                $msg = '支付成功';
                break;
        }
        return $msg;
    }



}
