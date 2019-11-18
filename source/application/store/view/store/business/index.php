<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">业务类型</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                                <?php if (checkPrivilege('store.business/add')): ?>
                                    <div class="am-btn-group am-btn-group-xs">
                                        <a class="am-btn am-btn-default am-btn-success am-radius"
                                           href="<?= url('store.business/add') ?>">
                                            <span class="am-icon-plus"></span> 新增
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black ">
                            <thead>
                            <tr>
                                <th>业务ID</th>
                                <th>业务类型</th>
                                <th>业务排序</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
<!--                            一级业务分类-->
                            <?php if (!empty($list)): foreach ($list as $first): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $first['id'] ?></td>
                                    <td class="am-text-middle"><?= $first['name'] ?><?if(!empty($first['child'])){foreach ($first['child'] as $v){
                                        echo $v['name'];
                                        }}?></td>
                                    <td class="am-text-middle"><?= $first['sort'] ?></td>
                                    <td class="am-text-middle"><?= $first['create_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('store.business/edit')): ?>
                                                <a href="<?= url('store.business/edit',
                                                    ['id' => $first['id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('store.business/delete')): ?>
                                                <a href="javascript:;" class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $first['id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
<!--                            二级业务分类-->
                            <?php if (isset($first['child'])): foreach ($first['child'] as $two): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $two['id'] ?></td>
                                    <td class="am-text-middle">　-- <?= $two['name'] ?></td>
                                    <td class="am-text-middle"><?= $two['sort'] ?></td>
                                    <td class="am-text-middle"><?= $two['create_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('store.business/view')): ?>
                                                <a href="<?= url('store.business/view',
                                                    ['id' => $two['id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 所属分类
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('store.business/edit')): ?>
                                                <a href="<?= url('store.business/edit',
                                                    ['id' => $two['id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('store.business/delete')): ?>
                                                <a href="javascript:;"
                                                   class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $two['id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; endif; ?>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="5" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        // 删除元素
        var url = "<?= url('store.business/delete') ?>";
        $('.item-delete').delete('id', url);

    });
</script>

