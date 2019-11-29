<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">门店列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <div class="am-form-group">
                            <?php if (checkPrivilege('store.shop/add')): ?>
                                <div class="am-btn-group am-btn-group-xs">
                                    <a class="am-btn am-btn-default am-btn-success"
                                       href="<?= url('store.shop/add') ?>">
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
                                <th>门店ID</th>
                                <th>门店名称</th>
                                <th>门店logo</th>
                                <th>营业时间</th>
                                <th>联系电话</th>
                                <th>开关设置</th>
                                <th>排序</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['id'] ?></td>
                                    <td class="am-text-middle"><?= $item['store_name'] ?></td>
                                    <td class="am-text-middle">
                                        <a href="<?= BIG_IMG.$item['logo'] ?>" title="点击查看大图" target="_blank">
                                            <img src="<?= SIM_IMG.$item['logo'] ?>" width="100" height="40" alt="">
                                        </a>
                                    </td>
                                    <td class="am-text-middle"><?= $item['store_start_time']. '-' . $item['store_end_time'] ?></td>
                                    <td class="am-text-middle"><?= $item['store_mobile'] ?></td>
                                    <td class="am-text-middle">
                                       <span class="j-state am-badge x-cur-p am-badge-<?= $item['is_open'] == 1 ? 'success' : 'warning' ?>" data-id="<?= $item['id'] ?>" data-state="<?= $item['is_open'] ?>">
                                            <?= $item['format_is_open'] ?>
                                        </span>
                                    </td>
                                    <td class="am-text-middle"><?= $item['sort'] ?></td>
                                    <td class="am-text-middle"><?= $item['add_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('store.shop/edit')): ?>
                                                <a href="<?= url('store.shop/edit', ['store_id' => $item['id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('store.shop/electric_fence')): ?>
                                                <a href="<?= url('store.shop/electric_fence', ['store_id' => $item['id']]) ?>">
                                                    <i class="am-icon-cog"></i> 电子围栏
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('store.shop/setting')): ?>
                                                <a href="<?= url('store.shop/setting', ['store_id' => $item['id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 支付配置
                                                </a>
                                            <?php endif; ?>
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
        // 商品状态
        $('.j-state').click(function () {
            // 验证权限
            if (!"<?= checkPrivilege('store_goods/on')?>") {
                return false;
            }
            var data = $(this).data();
            layer.confirm('确定要' + (parseInt(data.state) === 1 ? '关闭' : '开启') + '该店铺吗？'
                , {title: '友情提示'}
                , function (index) {
                    $.post("<?= url('store.shop/on') ?>"
                        , {
                            store_id: data.id,
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
        var url = "<?= url('store.shop/delete') ?>";
        $('.item-delete').delete('store_id', url, '删除后不可恢复，确定要删除吗？');

    });
</script>

