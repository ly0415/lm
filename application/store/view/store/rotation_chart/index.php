<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">小程序轮播列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form id="form-search" class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                        </form>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>轮播图类型</th>
                                <th>添加人</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['id'] ?></td>
                                    <td class="am-text-middle"><?= $item['typename'] ?>
<!--                                        --><?php //if (!empty($item['imgs'])): foreach ($item['imgs'] as $role):  ?>
<!--                                            <img src="uploads/big/--><?//= $role['img'] ?><!--" width="50" height="50" alt="">-->
<!--                                        --><?php //endforeach;endif;  ?>
                                    </td>
                                    <td class="am-text-middle"><?= $item['user_name'] ?></td>
                                    <td class="am-text-middle"><?= $item['update_time'] ?></td>
<!--                                    <td class="am-text-middle">--><?//= date('Y-m-d',$item['update_time']) ?><!--</td>-->
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('store.rotation_chart/edit')): ?>
                                                <a href="<?= url('store.rotation_chart/edit',
                                                    ['id' => $item['id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
<!--                                            --><?php //if (checkPrivilege('store.source_list/delete')): ?>
<!--                                                <a href="javascript:;" class="item-delete tpl-table-black-operation-del"-->
<!--                                                   data-id="--><?//= $item['id'] ?><!--">-->
<!--                                                    <i class="am-icon-trash"></i> 删除-->
<!--                                                </a>-->
<!--                                            --><?php //endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="9" class="am-text-center">暂无记录</td>
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
        var url = "<?= url('store.source_list/delete') ?>";
        $('.item-delete').delete('id', url, '删除后不可恢复，确定要删除吗？');

    });
</script>

