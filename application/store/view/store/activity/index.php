<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">活动列表</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <div class="am-form-group">
                            <?php if (checkPrivilege('store.activity/add')): ?>
                                <div class="am-btn-group am-btn-group-xs">
                                    <a class="am-btn am-btn-default am-btn-success"
                                       href="<?= url('store.activity/add') ?>">
                                        <span class="am-icon-plus"></span> 新增
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>活动名称</th>
                                <th>所属店铺</th>
                                <th class="am-text-middle">活动类型</th>
                                <th>开始时间</th>
                                <th>结束时间</th>
                                <th>活动状态</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['id'] ?></td>
                                    <td class="am-text-middle">
                                    <?= $item['name'] ?>
                                    </td>
                                    <td class="am-text-middle">
                                        <?= $item['store_name'] ?>
                                    </td>
                                    <td class="am-text-middle">
                                        <p class="item-title"><?= $item['type']['text'] ?></p>
                                    </td>
                                    <td class="am-text-middle">
                                        <p class=""><?= $item['start_time']['text'] ?></p>

                                    </td>
                                    <td class="am-text-middle">
                                        <?= $item['end_time']['text'] ?>
                                    </td>
                                    <td class="am-text-middle">
                                           <span class="j-state am-badge x-cur-p
                                           am-badge-<?= $item['status']['value'] == 1 ? 'success' : 'warning' ?>"
                                                 data-id="<?= $item['id'] ?>"
                                                 data-state="<?= $item['status']['value'] ?>">
                                               <?= $item['status']['text'] ?>
                                           </span>
                                    </td>

                                    <td class="am-text-middle">
                                       <?= $item['create_time'] ?>
                                    </td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">

                                            <?php if (checkPrivilege('store.activity/edit')): ?>
                                                <a class="tpl-table-black-operation-default"
                                                   href="<?= url('store.activity/edit', ['id' => $item['id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>

                                            <?php if (checkPrivilege('store.activity/delete')): ?>
                                                <a href="javascript:void(0);"
                                                   class="item-delete tpl-table-black-operation-default"
                                                   data-id="<?= $item['id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('store.activity/view')): ?>
                                                <a class="tpl-table-black-operation-default"
                                                   href="<?= url('store.activity/view', ['activity_id' => $item['id']]) ?>">
                                                    <i class="iconfont icon-order-o"></i> 报名人员
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="11" class="am-text-center">暂无记录</td>
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

        // 活动状态
        $('.j-state').click(function () {
            // 验证权限
            if (!"<?= checkPrivilege('store.activity/state')?>") {
                return false;
            }
            var data = $(this).data();
            layer.confirm('确定要' + (parseInt(data.state) === 1 ? '关闭' : '开启') + '该活动吗？'
                , {title: '友情提示'}
                , function (index) {
                    $.post("<?= url('store.activity/state') ?>"
                        , {
                            activity_id: data.id,
                            state: Number(!(parseInt(data.state) === 1))
                        }
                        , function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        });
                    layer.close(index);
                });

        });

        // 删除元素
        var url = "<?= url('store.activity/delete') ?>";
        $('.item-delete').delete('id', url);
    });
</script>

