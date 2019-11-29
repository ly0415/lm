<?php

namespace app\store\controller\store;

use app\store\controller\Controller;
use app\store\model\Coupon;
use app\store\model\Store;
use app\store\model\StoreConsole as StoreConsoleModel;
use app\store\model\Coupon as CouponModel;


/**
 * 商家用户控制器
 * Class StoreUser
 * @package app\store\controller
 */
class StoreConsole extends Controller
{
    /**
     * 控制台
     * author fup
     * date 2019-07-11
     */
    public function index()
    {
        $model = new StoreConsoleModel();
        if($this->request->isAjax()){
            if($model->addArticleCoupon($this->postData('coupon'))){
                return $this->renderSuccess('设置成功');
            }
            return $this->renderError($model->getError() ?: '添加失败');
        }
        $coupon     = Coupon::getListALL(['type'=>1,'is_special'=>0]);
        $list1      = $model->get(['type'=>2,'mark'=>1],'image');
        $storeList  = array_values(Store::getStoreList());
        $list       = [];
        foreach (StoreConsoleModel::$consoleType as $k => $v){
            if($data = StoreConsoleModel::detail($k)){
                $list[$k] = $data->toArray();
            }
        }
        return $this->fetch('index',compact('list','list1','coupon','storeList'));
    }

    /**
     * 设置新用户领取抵扣卷注册天数||开启关闭
     * author fup
     * date 2019-07-11
     */
    public function setConsole(){
        if($this->request->isAjax()){
            $model = new StoreConsoleModel;
            if(!$list = $model->getInfo(['type'=>1,'mark'=>1])){
                if($model->addConsole($this->postData('console'))){
                    return $this->renderSuccess('设置成功');
                }
                return $this->renderError('设置失败');
            }

            if($model->addConsole($this->postData('console'),$list->toArray())){
                return $this->renderSuccess('设置成功');
            }
            return $this->renderError('设置失败');
        }
    }

    /**
     * 客服开关
     * author fup
     * date 2019-07-25
     */
    public function setCustomer(){
        $model = new StoreConsoleModel();
        if($this->request->isAjax()){
            if($model->addKeFu($this->postData('kufu'))){
                return $this->renderSuccess('设置成功');
            }
            return $this->renderError('设置失败');
        }
    }

    /**
     * 设置新用户领取抵扣卷注册天数
     * author fup
     * date 2019-07-11
     */
    public function setCoupon(){
        if($this->request->isAjax()){
            $model = new CouponModel;
            if(!$list = $model->getInfo(['type'=>1,'is_special'=>1,'mark'=>1])){
                if($model->add($this->postData('console'))){
                    return $this->renderSuccess('设置成功');
                }
                return $this->renderError('设置失败');
            }
            if($model->edit($this->postData('console'),$list->toArray())){
                return $this->renderSuccess('设置成功');
            }
            return $this->renderError('设置失败');

        }
    }

    /**
     * 添加关闭店铺
     * author fup
     * date 2019-07-10
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function addCloseStore($store_id,$type){
        if($this->request->isAjax()){
            $model = new StoreConsoleModel;
            if(!$list = $model->get(['type'=>$type,'mark'=>1])){
                if ($model->add($store_id,$type)) {
                    return $this->renderSuccess('添加成功');
                }
                return $this->renderError($list->getError() ?: '添加失败');
            }
            if(in_array($store_id,explode(',',$list['relation_1']))){
                return $this->renderError('请勿重复添加', '', [
                    'id' => (int)$list['id'],
                ]);
            }
            if($list->edit($store_id)){
                return $this->renderSuccess('添加成功');
            }
            return $this->renderError('添加失败');
        }
    }

    /**
     * 添加使用余额支付开关
     * author fup
     * date 2019-07-10
     * @return mixed
     * @throws \think\exception\DbException
     */
//    public function addCloseBalancePay($store_id){
//        if($this->request->isGet()){
//            $model = new StoreConsoleModel;
//            if(!$list = $model->get(['type'=>3,'mark'=>1])){
//                if ($model->add($store_id)) {
//                    echo 1;die;
//                    return $this->renderSuccess('添加成功', url('store_console/index'));
//                }
//                echo 2;die;
//                return $this->renderError($list->getError() ?: '添加失败');
//            }
//            if(in_array($store_id,explode(',',$list->relation_1))){
//                echo 11;die;
//                return $this->renderSuccess('', '', [
//                    'store_console_id' => (int)$list['id'],
//                ]);
//            }
//            if($list->edit($store_id)){
//                echo 111;die;
//                $this->renderSuccess('添加成功',url('store_console/index'));
//            }
//            echo 222;die;
//            $this->renderError('添加失败');
//        }
//    }

    /**
     * 删除使用余额支付开关
     * author fup
     * date 2019-07-10
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function delCloseStore($store_id,$type){
        if($this->request->isAjax()){
            $model = new StoreConsoleModel;
            if($list = $model->get(['type'=>$type,'mark'=>1])){
                if ($list->setDelete($store_id)) {
                    return $this->renderSuccess('删除成功');
                }
                return $this->renderError('删除失败');
            }
        }
        return $this->renderError('删除失败');
    }

    /**
     * 设置文章领取抵扣券
     * author fup
     * date 2019-07-16
     */
    public function addCoupon(){
        $model = new StoreConsoleModel;
        if(!$this->request->isAjax()){
            $coupon = Coupon::getListALL(['type'=>1,'is_special'=>0]);
            $list = $model->get(['type'=>2,'mark'=>1],'image');
            return $this->fetch('add_coupon',compact('coupon','list'));
        }
        if($model->addArticleCoupon($this->postData('coupon'))){
            return $this->renderSuccess('设置成功');
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }


    /**
     * 店铺的小程序二维码
     * @author tangp
     * @date 2018-10-25
     */
    public function getStoreXcxCode()
    {
        $access_token = $this->getAccessToken();
        $post_data = json_encode(array(
            'width' => 120,
            "scene"=>"1",
            "page"=>"pages/articleCoupon/articleCoupon"
        ));
        // $access_token = $this->getAccessToken();
        // 为二维码创建一个文件
        $mainPath = ROOT_PATH . '/upload';
        if(!is_dir($mainPath)){
            mkdir($mainPath,0777,true);
        }
        $timePath = date('Ymd');
        $savePath = $mainPath . '/' . $timePath;
        if(!is_dir($savePath)){
            mkdir($savePath,0777,true);
        }
        $newFileName = time() . ".png";
        $pathName = $savePath . '/' . $newFileName;
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$access_token;
        $result = $this->httpRequest($url,$post_data,'POST');
//        dump($result);die;
        $res = file_put_contents($pathName,$result);
    }

    /**
     * 读取access_token
     */
    public function getAccessToken()
    {
        $appid = 'wxd483c388c3d545f3';
        $secret = 'd19b0561679a32122f10d524153f7ea5';
        return $this->getNewToken($appid,$secret);
    }

    /**
     * 获取微信accesstoken
     * @param $appid
     * @param $secret
     * @return mixed
     */
    public function getNewToken($appid,$secret)
    {
        $tokenUrl = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $secret;
        $access_token_arr = $this->httpRequest($tokenUrl);
        $access = json_decode($access_token_arr,true);
        return $access['access_token'];
    }

    /**
     * curl方法
     * @param $url
     * @param string $data
     * @param string $method
     * @return mixed
     */
    public function httpRequest($url, $data='', $method='GET'){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        if($method=='POST')
        {
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data != '')
            {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
        }

        curl_setopt($curl, CURLOPT_TIMEOUT, 120);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

}
