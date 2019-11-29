<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-06-17
 * Time: 下午 5:18
 */

namespace app\xcx\controller;

use app\xcx\model\StoreBargain as StoreBargainModel;
use app\xcx\model\StoreBargainUserHelp;
use app\xcx\model\StoreBargainUser;

class StoreBargain extends Base
{
    /**
     * 砍价活动
     * @param int $bargainId
     * @param int $bargain_user_id
     * @return \think\response\Json
     */
    /**
     * 获取砍价列表
     * @return \think\response\Json
     */
    public function get_bargain_list($uid=0){
        if(!$uid)return $this->renderError('缺少参数');
        $bargain = StoreBargainModel::getList($uid);
        return $this->renderSuccess('SUCCESS','',$bargain);
    }
    /**
     * 砍价详情
     * @param int $bargainId
     * @param int $bargain_user_id
     * @return \think\response\Json
     */

    public function get_bargain($bargain_id = 0,$bargain_user_id=0){
        if(!$bargain_id || !$bargain_user_id)return $this->renderError('参数错误');
        $bargain = StoreBargainModel::getBargainTerm($bargain_id);
        $userHelp = $this->get_bargain_user($bargain_id,$bargain_user_id);
        $myCut = $this->get_bargain_help_count($bargain_id,$bargain_user_id);
        if(empty($bargain)){
            return $this->renderError('砍价已结束');
        }
        return $this->renderSuccess('SUCCESS','',['bargain'=>$bargain,'userHelp'=>$userHelp,'myCut'=>$myCut]);
    }

    /**
     * 获取砍价帮
     * @param int $bargainId
     * @param int $uid
     * @return \think\response\Json
     */
    public function get_bargain_user($bargain_id = 0,$bargain_uid = 0,$limit = 15){
        if(!$bargain_id || !$bargain_uid) return $this->renderError('参数错误');
        $bargainUserTableId = StoreBargainUser::setUserBargain($bargain_id,$bargain_uid);
        $storeBargainUserHelp = StoreBargainUserHelp::getList($bargainUserTableId,$limit);
        return $storeBargainUserHelp;
    }

    /**
     * 我的砍价
     * @param int $bargainId
     * @return \think\response\Json
     */
    public function myCut($bargain_id = 0,$bargain_user_id=0){
        if(!$bargain_id ) return $this->renderError('参数错误');
        $data= StoreBargainUser::where('bargain_id',$bargain_id)->where('uid',$bargain_user_id)->where('status',1)->find();
        if($data){
            return $data->toArray();
        }
        else return [];
    }

    /**
     * 获取砍价帮总人数、剩余金额、进度条
     * @param int $bargainId
     * @param int $bargainUserId
     * @return \think\response\Json
     */
    public function get_bargain_help_count($bargain_id = 0,$bargain_user_id = 0){
        if(!$bargain_id || !$bargain_user_id) return $this->renderError('参数错误');
        $count = StoreBargainUserHelp::getBargainUserHelpPeopleCount($bargain_id,$bargain_user_id);//砍价总人数
        $price = StoreBargainUserHelp::getSurplusPrice($bargain_id,$bargain_user_id);//剩余砍价金额

        $pricePercent = StoreBargainUserHelp::getSurplusPricePercent($bargain_id,$bargain_user_id);//砍价进度条
        $bargainUserTableId = StoreBargainUser::getBargainUserTableId($bargain_id,$bargain_user_id);
        $alreadyPrice = StoreBargainUser::getBargainUserPrice($bargainUserTableId);
        $data['count'] = $count;
        $data['price'] = $price;
        $data['alreadyPrice'] = $alreadyPrice;
        $data['pricePercent'] = $pricePercent;
        return $data;
    }

    /**
     * 帮好友砍价
     * @param int $bargainId
     * @param int $bargainUserId
     * @return \think\response\Json
     */
    public function set_bargain_help($bargain_id = 0,$bargain_user_id = 0,$uid=0){
        if(!$bargain_id || !$bargain_user_id || !$uid) return $this->renderError('参数错误');
        $res = StoreBargainUserHelp::setBargainUserHelp($bargain_id,$bargain_user_id,$uid);
        if($res)
            return $this->renderSuccess('砍价成功','',$res);
        else return $this->renderError('砍价失败');
    }

    /**
     * 参与砍价产品
     * @param int $bargainId
     * @return \think\response\Json
     */
    public function set_bargain($bargain_id = 0,$uid=0){
        if(!$bargain_id) return $this->renderError('参数错误');
        $res = StoreBargainUser::setBargain($bargain_id,$uid);
        if($res) {
            $data['id'] =  $res->id;
            return $this->renderSuccess('参与成功','',$data);
        }
        else return $this->renderError('参与失败');
    }

    /**
     * 添加砍价浏览次数
     * @param int $bargainId
     */
    public function add_look_bargain($bargain_id = 0){
        if(!$bargain_id) return $this->renderError('参数错误');
        StoreBargainModel::addBargainLook($bargain_id);
        return $this->renderSuccess('SUCCESS');
    }

    /**
     * 添加砍价分享次数
     * @param int $bargainId
     */
    public function add_share_bargain($bargain_id = 0){
        if(!$bargain_id) return $this->renderError('参数错误');
        StoreBargainModel::addBargainShare($bargain_id);
        return $this->renderSuccess('SUCCESS');
    }

    /**
     * 判断当前登录人是否参与砍价
     * @param int $bargainId
     * @return \think\response\Json
     */
    public function is_bargain_user($bargain_id = 0,$uid=0){
        if(!$bargain_id) return $this->renderError('参数错误');
        $data=StoreBargainUser::isBargainUser($bargain_id,$uid);
        if($data) return $this->renderSuccess('SUCCESS','',$data);
        else return $this->renderError('没有参与砍价');
    }

}