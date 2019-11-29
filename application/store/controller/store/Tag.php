<?php

namespace app\store\controller\store;

use app\store\controller\Controller;
use app\store\model\Tag as TagModel;
use app\store\model\TagValue as TagValueModel;

/**
 * 商品规格控制器
 * Class Spec
 * @package app\store\controller
 */
class Tag extends Controller
{
    /* @var Tag $TagModel */
    private $TagModel;

    /* @var TagValueModel $TagModel */
    private $TagValueModel;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->TagModel = new TagModel;
        $this->TagValueModel = new TagValueModel;
    }


    /**
     * 添加规则组
     * @param $tag_name
     * @param $tag_value
     * @return array
     */
    public function add($tag_name = null, $tag_value = null)
    {
        if(!$this->request->isAjax()){
            $list = $this->TagModel->getList();
            return $this->fetch('add',compact('list'));
        }
        // 判断标签组是否存在
        if (!$tagId = $this->TagModel->getTagIdByName($tag_name)) {
            // 新增标签组and标签值
            if ($this->TagModel->add($tag_name)
                && $this->TagValueModel->add($this->TagModel['tag_id'], $tag_value))
                return $this->renderSuccess('', '', [
                    'tag_id' => (int)$this->TagModel['tag_id'],
                    'tag_value_id' => (int)$this->TagValueModel['tag_value_id'],
                ]);
            return $this->renderError();
        }
        // 判断标签值是否存在
        if ($tagValueId = $this->TagValueModel->getTagValueIdByName($tagId, $tag_value)) {
            return $this->renderSuccess('', '', [
                'tag_id' => (int)$tagId,
                'tag_value_id' => (int)$tagValueId,
            ]);
        }
        // 添加标签值
        if ($this->TagValueModel->add($tagId, $tag_value))
            return $this->renderSuccess('', '', [
                'tag_id' => (int)$tagId,
                'tag_value_id' => (int)$this->TagValueModel['tag_value_id'],
            ]);
        return $this->renderError();
    }

    /**
     * 添加规格值
     * @param $tag_id
     * @param $tag_value
     * @return array
     */
    public function addTagValue($tag_id, $tag_value)
    {
        // 判断标签值是否存在
        if ($tagValueId = $this->TagValueModel->getTagValueIdByName($tag_id, $tag_value)) {
            return $this->renderSuccess('', '', [
                'tag_value_id' => (int)$tagValueId,
            ]);
        }
        // 添加标签值
        if ($this->TagValueModel->add($tag_id, $tag_value))
            return $this->renderSuccess('', '', [
                'tag_value_id' => (int)$this->TagValueModel['tag_value_id'],
            ]);
        return $this->renderError();
    }


    /**
     * 删除标签值
     * Created by PhpStorm.
     * Author: fup
     * Date: 2019-09-23
     * Time: 15:50
     */
    public function delTagValue($tag_id, $tag_value_id)
    {

        if ($this->TagValueModel->del($tag_id, $tag_value_id))
            return $this->renderSuccess();
        return $this->renderError();
    }

    /**
     * 删除标签组
     * @param $spec_id
     * @param $spec_value
     * @return array
     */
    public function delTag($tag_id)
    {
        if ($this->TagModel->del($tag_id))
            return $this->renderSuccess();
        return $this->renderError();
    }

}
