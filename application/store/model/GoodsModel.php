<?php

namespace app\store\model;

use think\Cache;
use app\common\model\GoodsModel as GoodsModelModel;

/**
 * 商品分类模型
 * Class Category
 * @package app\store\model
 */
class GoodsModel extends GoodsModelModel
{
    /**
     * 获取模型列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-12
     * Time: 17:29
     */
    public function getList($name = ''){
        $filter = [];
//        !empty($model_id) && $filter['id'] = $model_id;
        !empty($name) && $filter['name'] = ['like','%'.trim($name).'%'];
        $list = $this->with(['goodsSpec','goodsAttribute'])
            ->where($filter)
            ->where('mark', '=', 1)
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
        return $list;
    }

    /**
     * 获取全部模型列表
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-13
     * Time: 13:58
     */
    public function getListAll(){

       return self::all(['mark'=>1]);
    }

    /**
     * 添加新记录
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-13
     * Time: 09:59
     */
    public function add($data)
    {


        if(!$data['name']){
            $this->error = '请填写模型名称';
            return false;
        }
        return $this->allowField(true)->save($data);
    }

    /**
     * 编辑记录
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-13
     * Time: 10:01
     */
    public function edit($data)
    {
        if(!$data['name']){
            $this->error = '请填写模型名称';
            return false;
        }
        return $this->allowField(true)->save($data) !== false;
    }

    /**
     * 记录详情
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-13
     * Time: 10:08
     */
    public static function detail($id)
    {
        return self::get($id,['goodsSpec.item','goodsAttribute']);
    }

    /**
     * 删除商品分类
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-12-13
     * Time: 10:09
     */
    public function remove()
    {
        return $this->allowField(true)->save(['mark'=>0]);
    }

    /**
     * 获取所有商品模型
     * @return bool|int
     * @throws \think\Exception
     */
    public static function getAllModel(){
        return self::where('mark','=',1)
            ->order('sort','ASC')
            ->select();
    }




}
