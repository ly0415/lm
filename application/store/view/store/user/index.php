<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">会员列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form id="form-search" class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-3">
                                <div class="am-form-group">
                                    <div class="am-btn-toolbar">
                                        <div class="am-btn-group am-btn-group-xs">
                                            <!--                                            --><?php //if (checkPrivilege('order.operate/export')): ?>
                                            <!--                                                <a class="j-export am-btn am-btn-success am-radius"-->
                                            <!--                                                   href="javascript:void(0);">-->
                                            <!--                                                    <i class="iconfont icon-daochu am-margin-right-xs"></i>订单导出-->
                                            <!--                                                </a>-->
                                            <!--                                            --><?php //endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-u-sm-12 am-u-md-9">
                                <div class="am fr">
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input type="text" class="am-form-field" name="username" placeholder="请输入用户名称" value="<?= $request->get('username') ?>">
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="phone" placeholder="请输入手机号" value="<?= $request->get('phone') ?>">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search" type="submit"></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>用户名称</th>
                                    <th>性别</th>
                                    <th>手机号</th>
                                    <th>睿积分</th>
                                    <th>余额</th>
                                    <th>注册时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['id'] ?></td>
                                    <td class="am-text-middle"><?= $item['username'] ?></td>
                                    <td class="am-text-middle"><?= $item['sex'] ?></td>
                                    <td class="am-text-middle"><?= $item['phone'] ?></td>
                                    <td class="am-text-middle"><?= $item['point'] ?></td>
                                    <td class="am-text-middle"><?= $item['amount'] ?></td>
                                    <td class="am-text-middle"><?= $item['add_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('store.user/edit')): ?>
                                                <a href="<?= url('store.user/edit',
                                                    ['user_id' => $item['id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('store.user/recomend')): ?>
                                                <a href="<?= url('store.user/recomend', ['user_id' => $item['id']]) ?>">
                                                    <i class="am-icon-eye"></i> 推荐
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('store.user/balanceHistory')): ?>
                                                <a href="<?= url('store.user/balanceHistory', ['user_id' => $item['id']]) ?>">
                                                    <i class="am-icon-eye"></i> 余额历史记录
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

        // 删除元素
        var url = "<?= url('store.shop/delete') ?>";
        $('.item-delete').delete('store_id', url, '删除后不可恢复，确定要删除吗？');

    });
</script>

