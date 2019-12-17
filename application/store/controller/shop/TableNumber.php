<?php

namespace app\store\controller\shop;

use app\store\controller\Controller;
use app\store\model\shop\TableNumber as TableNumberModel;

/**
 * 桌号设置
 * @author
 * @date    2019-12-06
 */
class TableNumber extends Controller{

    /**
     * 桌号设置
     * @author  ly
     * @date    2019-12-06
     */
    public function index() {
        $tablenumberModel = new TableNumberModel;
        $list = $tablenumberModel->getList();
        return $this->fetch('index',compact('list','stores','send'));
    }

    /**
     *添加
     * @author ly
     * @date 2019-12-06
     */
    public function add($number='')
    {
        $tablenumberModel = new TableNumberModel;
        // 新增记录
        if ($tablenumberModel->add($number)) {
            return $this->renderSuccess('添加成功', url('shop.table_number/index'));
        }
        return $this->renderError($tablenumberModel->getError() ?: '添加失败');
    }

    /**
     *删除 软删
     * @author ly
     * @date 2019-12-06
     */
    public function delete($id='')
    {
        $tablenumberModel = new TableNumberModel;
        // 新增记录
        if ($tablenumberModel->delete($id)) {
            return $this->renderSuccess('删除成功', url('shop.table_number/index'));
        }
        return $this->renderError($tablenumberModel->getError() ?: '删除失败');
    }

}