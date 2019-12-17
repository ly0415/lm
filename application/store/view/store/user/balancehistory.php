<div class="row-content am-cf" xmlns="http://www.w3.org/1999/html">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">余额历史记录</div>
                </div>
                <div class="widget-body am-fr">
                    <form id="form-search" class="toolbar-form" action="">
                    <!-- 工具栏 -->
<!--                        <div >-->
<!--                            <a class="details_back" href="--><?//=url('store.user/index')?><!--" >返回</a>-->
<!--                        </div>-->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <div class="am-u-sm-12 am-u-md-2">
                            <div class="am-form-group">
                                <div class="am-btn-toolbar">
                                    <div class="am-btn-group am-btn-group-xs">
                                        <?php if (checkPrivilege('store.user/export')): ?>
                                            <a class="j-export am-btn am-btn-success am-radius"
                                               href="javascript:void(0);">
                                                <i class="iconfont icon-daochu am-margin-right-xs"></i>数据导出
                                            </a>
                                            <input type="hidden" name="user_id" value="<?= $user_id ?>">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="am-u-sm-12 am-u-md-2">
                            余额：<?= $amount?>
                        </div>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                                <tr>
                                    <th>标识</th>
                                    <th>变更金额</th>
                                    <th>变更前余额</th>
                                    <th>变更后余额</th>
                                    <th>类型</th>
                                    <th>状态</th>
                                    <th>操作时间</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['description'] ?></td>
                                    <td class="am-text-middle"><?= $item['c_money'] ?></td>
                                    <td class="am-text-middle"><?= $item['old_money'] ?></td>
                                    <td class="am-text-middle"><?= $item['new_money'] ?></td>
                                    <td class="am-text-middle"><?= $item['type_name'] ?></td>
                                    <td class="am-text-middle"><?= $item['type_status'] ?></td>
                                    <td class="am-text-middle"><?= date('Y-m-d',$item['add_time']) ?></td>
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
    </form>
</div>

<script src="assets/common/js/jquery.min.js"></script>
<script>
    $(function () {

        // 删除元素
        var url = "<?= url('store.shop/delete') ?>";
        $('.item-delete').delete('store_id', url, '删除后不可恢复，确定要删除吗？');


        /**
         * 订单导出
         */
        $('.j-export').click(function () {
            var data = {};
            var formData = $('#form-search').serializeArray();
            $.each(formData, function () {
                this.name !== 's' && (data[this.name] = this.value);
            });
            window.location = "<?= url('store.user/export') ?>" + '&' + $.urlEncode(data);
        });
    });

</script>

