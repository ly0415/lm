<?php

namespace app\store\model;

use app\common\model\StoreBargain as StoreBargainModel;

/**
 * 砍价模型
 * Class Article
 * @package app\store\model
 */
class StoreBargain extends StoreBargainModel
{
    /**
     * 获取文章列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        return $this->with(['user','ActivityGoods.goods'])
            ->where('is_delete', '=', 0)
            ->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);

    }

    /**
     * 新增记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        if (empty($data['title'])) {
            $this->error = '请输入活动标题';
            return false;
        }
        if (empty($data['start_time'])) {
            $this->error = '请选择活动开始时间';
            return false;
        }
        if (empty($data['end_time'])) {
            $this->error = '请选择活动结束时间';
            return false;
        }
        if (empty($data['expiry_time'])) {
            $this->error = '请输入砍价有效期';
            return false;
        }
        if (empty($data['bargain_min_price'])) {
            $this->error = '请输入砍价最小金额';
            return false;
        }
        if (empty($data['bargain_max_price'])) {
            $this->error = '请输入砍价最大金额';
            return false;
        }
        if (empty($data['min_price'])) {
            $this->error = '请输入砍价底价';
            return false;
        }
//        if (empty($data['peoples'])) {
//            $this->error = '请输入砍价人数';
//            return false;
//        }
        if (empty($data['share_title'])) {
            $this->error = '请输入分享标题';
            return false;
        }
        if (empty($data['prompt_words'])) {
            $this->error = '请输入砍价助力语';
            return false;
        }
        if (empty($data['goods_id'])) {
            $this->error = '请选择砍价商品';
            return false;
        }
        if(strtotime($data['start_time']) >= strtotime($data['end_time'])){
            $this->error = '结束时间应大于开始时间';
            return false;
        }
        $data['wxapp_id'] = self::$wxapp_id;
        $data['add_user'] = session('yoshop_store.user')['store_user_id'];

        $this->startTrans();
        try {
//            dump($data);die;

            // 添加商品
            $this->allowField(true)->save($data);
            // 商品规格
            // 商品图片
            $this->addActivityGoods($data['goods_id'],$data['goods_price'],$data['stock']);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 更新记录
     * @param $data
     * @return bool|int
     */
    public function edit($data)
    {
        if (empty($data['title'])) {
            $this->error = '请输入活动标题';
            return false;
        }
        if (empty($data['start_time'])) {
            $this->error = '请选择活动开始时间';
            return false;
        }
        if (empty($data['end_time'])) {
            $this->error = '请选择活动结束时间';
            return false;
        }
        if (empty($data['expiry_time'])) {
            $this->error = '请输入砍价有效期';
            return false;
        }
        if (empty($data['bargain_min_price'])) {
            $this->error = '请输入砍价最小金额';
            return false;
        }
        if (empty($data['bargain_max_price'])) {
            $this->error = '请输入砍价最大金额';
            return false;
        }
        if (empty($data['min_price'])) {
            $this->error = '请输入砍价底价';
            return false;
        }
//        if (empty($data['peoples'])) {
//            $this->error = '请输入砍价人数';
//            return false;
//        }
        if (empty($data['share_title'])) {
            $this->error = '请输入分享标题';
            return false;
        }
        if (empty($data['prompt_words'])) {
            $this->error = '请输入砍价助力语';
            return false;
        }
        if (empty($data['goods_id'])) {
            $this->error = '请选择砍价商品';
            return false;
        }
        if(strtotime($data['start_time']) >= strtotime($data['end_time'])){
            $this->error = '结束时间应大于开始时间';
            return false;
        }
//        dump($data);
        $data['wxapp_id'] = self::$wxapp_id;
        $data['add_user'] = session('yoshop_store.user')['store_user_id'];
        $this->startTrans();
        try {
            // 更新砍价活动
            $this->allowField(true)->save($data);
            // 商品
            $this->addActivityGoods($data['goods_id'],$data['goods_price'],$data['stock']);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 软删除
     * @return false|int
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]);
    }

    /**
     * 添加活动产品
     * @param $goods 产品id
     * @param $initial_sales 产品虚拟销量
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    private function addActivityGoods($goods,$goods_price,$stock)
    {
        $this->goodsId()->delete();
        $data = [];
        foreach ($goods as $k=>$v){
            $data['goods_id'] = $v;
            $data['goods_price'] = $goods_price[$v][0] ? :0;
            $data['stock'] = $stock[$v][0] ? : 0;
            $data['wxapp_id'] = self::$wxapp_id;
            $this->goodsId()->save($data);
        }
    }

}