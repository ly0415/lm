<?php

namespace app\api\controller;

use app\api\model\UserAddress;
use app\api\model\Store;
use think\Db;

/**
 * 收货地址管理
 * Class Address
 * @package app\api\controller
 */
class Address extends Controller
{
    //58=>23397,23560,23559,23398,23395,23394,23429,23391,23390,23389,23386,23588,23385,23384,23383,23382,23381,23380,23587,23378,23377,23411,23410,23409,23406,23400,23533,23531,23526,23527,23529,23402
    //59=>23373,23374,23375,

    public function getList($store_id = 0){
        $data = Db::name('store_goods')
            ->field('id,goods_name,code_url,store_id')
            ->where('code_url','neq' ,'null')
            ->where('mark','=',1)
            ->where('id','in',[23638,23635,23634,23633,23632,23631,23630,23629,23628,23627,23588,23587,23560,23533,23531,23529,23527,23526,23429,23411,23410,23409,23406,23402,23398,23397,23395,23394,23391,23390,23389,23386,23385,23384,23383,23382,23381,23380,23400,23378,23377,23559])
            ->where('is_on_sale','=',1)
            ->where('store_id','=',$store_id)
            ->order('id ASC')
            ->paginate(150);
//        dump($data->toArray());die;
            $i = 0;
            foreach ($data as $v){
                    $i+=1;
                    $this->getImage('www.711home.net/'.$v['code_url'],WEB_PATH . 'uploads/'.$v['store_id'].'/',str_replace(['/','\\',':','*','"','<','>','|','?'],'_',$v['goods_name']).'_'.$v['id'].'.png',1);
            }
            echo $i;
    }


    public function getImage($url,$save_dir='./',$filename='',$type=0){
        if(trim($url)==''){
            return array('file_name'=>'','save_path'=>'','error'=>1);
        }
        if(trim($save_dir)==''){
            $save_dir='./';
        }
        if(trim($filename)==''){//保存文件名
            $ext=strrchr($url,'.');
            if($ext!='.gif'&&$ext!='.jpg'){
                return array('file_name'=>'','save_path'=>'','error'=>3);
            }
            $filename=time().$ext;
        }
        //创建保存目录
        if(!is_dir($save_dir)){
            mkdir($save_dir,0777,true);
        }
        //获取远程文件所采用的方法
        if($type){
            $ch=curl_init();
            $timeout=300;
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
            $img=curl_exec($ch);
            curl_close($ch);
        }else{
            ob_start();
            readfile($url);
            $img=ob_get_contents();
            ob_end_clean();
        }
//        dump($save_dir.$filename);die;
        //$size=strlen($img);
        $s = iconv('UTF-8', 'gb2312//ignore', $save_dir.$filename);
//        dump($s);die;
        //文件大小
        $fp2=@fopen($s,'a');
        fwrite($fp2,$img);
        fclose($fp2);
        unset($img,$url);
//        return array('file_name'=>$filename,'save_path'=>$save_dir.$filename,'error'=>0);
    }
    /**
     * 收货地址列表
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    // public function lists()
    // {
    //     $user = $this->getUser();
    //     $model = new UserAddress;
    //     $list = $model->getList($user['user_id']);
    //     return $this->renderSuccess([
    //         'list' => $list,
    //         'default_id' => $user['address_id'],
    //     ]);
    // }

    /**
     * 添加收货地址
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    // public function add()
    // {
    //     $model = new UserAddress;
    //     if ($model->add($this->getUser(), $this->request->post())) {
    //         return $this->renderSuccess([], '添加成功');
    //     }
    //     return $this->renderError('添加失败');
    // }

    /**
     * 收货地址详情
     * @param $address_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
     public function detail($user_id = 0,$address_id = 0)
     {
         $detail = UserAddress::detail($user_id,$address_id);
         return $this->renderSuccess($detail);
     }

    /**
     * 编辑收货地址
     * @param $address_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    // public function edit($address_id)
    // {
    //     $user = $this->getUser();
    //     $model = UserAddress::detail($user['user_id'], $address_id);
    //     if ($model->edit($this->request->post())) {
    //         return $this->renderSuccess([], '更新成功');
    //     }
    //     return $this->renderError('更新失败');
    // }

    /**
     * 设为默认地址
     * @param $address_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    // public function setDefault($address_id) {
    //     $user = $this->getUser();
    //     $model = UserAddress::detail($user['user_id'], $address_id);
    //     if ($model->setDefault($user)) {
    //         return $this->renderSuccess([], '设置成功');
    //     }
    //     return $this->renderError('设置失败');
    // }

    /**
     * 删除收货地址
     * @param $address_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    // public function delete($address_id)
    // {
    //     $user = $this->getUser();
    //     $model = UserAddress::detail($user['user_id'], $address_id);
    //     if ($model->remove($user)) {
    //         return $this->renderSuccess([], '删除成功');
    //     }
    //     return $this->renderError('删除失败');
    // }

    /**
     * 验证用户选择地址是否在配送范围
     * author fup
     * date 2019-07-29
     */
//    public function checkAddressRange($user_id=0,$address_id=0,$store_id=0){
//        $userAddress = UserAddress::detail($user_id,$address_id);
//        $store = Store::detail($store_id);
//        if($this->doCheckAddressRange($userAddress,$store)){
//            return $this->renderSuccess($userAddress,'SUCCESS');
//        }
//        return $this->renderError('当前配送地址不在'.$store['store_name'].'配送范围内,请重新选择');
//    }


    //
    public function checkAddressRange($user_id=0,$address_id=0,$store_id=0){
        $userAddress = UserAddress::detail($user_id,$address_id);
        $model = Store::detail($store_id);
        if($model->doCheckAddressRange($userAddress)){
            return $this->renderSuccess($userAddress,'SUCCESS');
        }
        return $this->renderError('当前配送地址不在'.$model['store_name'].'配送范围内,请重新选择');
    }

    /**
     * 转换经纬度并计算是否在配送范围内
     * @param array $userAddress
     * @param array $store
     * author fup
     * date 2019-07-29
     */
    public function doCheckAddressRange($userAddress = [],$store = []){
        if($userAddress && !empty($userAddress['latlon']) && $store && $store['longitude'] && $store['latitude']){
            $latlon = explode(',',$userAddress['latlon']);
            //腾讯地图经纬度转换为百度地图经纬度
            $latlon = coordinate_switchf($latlon[1],$latlon[0]);
            //计算两点之间的距离
//            $distance = getdistance($latlon,$store);
            return getdistance($latlon,$store) < $store['distance'] ? true : false;
        }
        return false;
    }

}
