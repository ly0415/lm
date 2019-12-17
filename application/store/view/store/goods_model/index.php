<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">商品模型</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">

                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">

                            <div class="am-u-sm-12 am-u-md-3">
                                <div class="am-form-group">
                                    <?php if (checkPrivilege('store.goods_model/add')): ?>
                                        <div class="am-btn-group am-btn-group-xs">
                                            <a class="am-btn am-btn-default am-btn-success" href="<?= url('store.goods_model/add') ?>">
                                                <span class="am-icon-plus"></span> 新增
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="am-u-sm-12 am-u-md-9">
                                <div class="am fr">
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="name" placeholder="请输入模型名称" value="<?= $request->get('name') ?>">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search" type="submit"></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black am-text-nowrap">
                            <thead>
                                <tr>
                                    <th>模型ID</th>
                                    <th>模型名称</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($list)): foreach ($list as $first): ?>
                                    <tr>
                                        <td class="am-text-middle"><?= $first['id'] ?></td>
                                        <td class="am-text-middle"><?= $first['name'] ?></td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if (checkPrivilege('store.goods_attribute/index')): ?>
                                                    <a href="<?= url('store.goods_attribute/index',
                                                        ['model_id' => $first['id']]) ?>">
                                                        <i class="am-icon-pencil"></i> 属性列表
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (checkPrivilege('store.goods_spec/index')): ?>
                                                    <a href="<?= url('store.goods_spec/index',
                                                        ['model_id' => $first['id']]) ?>">
                                                        <i class="am-icon-pencil"></i> 规格列表
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (checkPrivilege('store.goods_model/edit')): ?>
                                                    <a href="<?= url('store.goods_model/edit',
                                                        ['id' => $first['id']]) ?>">
                                                        <i class="am-icon-pencil"></i> 编辑
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (checkPrivilege('store.goods_model/delete')): ?>
                                                    <a href="javascript:;" class="item-delete tpl-table-black-operation-del"
                                                    data-id="<?= $first['id'] ?>">
                                                        <i class="am-icon-trash"></i> 删除
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="5" class="am-text-center">暂无记录</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="am-u-lg-12 am-cf">
                        <div class="am-fr"><?= $list->render() ?> </div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录：<?= $list->total() ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        // 删除元素
        var url = "<?= url('store.goods_model/delete') ?>";
        $('.item-delete').delete('id', url);

    });
</script>

