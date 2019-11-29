<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-05-31
 * Time: 上午 9:30
 */

namespace app\store\validate;


class StoreBargain extends BaseValidate
{
    protected $rule = [
        ['title' , 'require' , '活动标题不能为空'],
        ['images'     , 'require','请上传砍价活动图'],
        ['start_time'  , 'require', '请选择活动开始时间'],
        ['stop_time'  , 'require', '请选择活动结束时间'],
        ['min_price'  , 'require', '请输入砍价商品最低价'],
        ['num'  , 'require|isPositiveInteger', '请输入可购买数量'],
        ['bargain_num'  , 'require|isPositiveInteger', '请输入用户每砍价次数'],
        ['content'  , 'require', '请输入活动详情'],
        ['rule'  , 'require', '请输入活动规则'],
        ['goods_id'  , 'require', '请选择砍价商品']

    ];
}