<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">分销人员列表</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>角色ID</th>
                                <th>角色名称</th>
                                <th>手机号</th>
                                <th>分销码</th>
                                <th>所属会员下单优惠</th>
                                <th>银行账号/开户银行</th>
                                <th>所属门店</th>
                                <th>账号状态</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($distributionList)): foreach ($distributionList as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['id'] ?></td>
                                    <td class="am-text-middle"><?= $item['real_name'] ?></td>
                                    <td class="am-text-middle"><?= $item['phone'] ?></td>
                                    <td class="am-text-middle"><?= $item['fx_code'] ?></td>
                                    <td class="am-text-middle"><?= $item['format_discount'] ?></td>
                                    <td class="am-text-middle">
                                        <?= $item['bank_name'] ?></br>
                                        <?= $item['bank_account'] ?>
                                    </td>
                                    <td class="am-text-middle"><?= $item['format_store_name'] ?></td>
                                    <td class="am-text-center"><span class="am-badge am-badge-<?php if($item['status'] == 1): ?>secondary<?php else: ?>danger<?php endif; ?>"><?= $item['format_status'] ?></span></td>
                                    <td class="am-text-middle"><?= $item['format_add_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('distribution/edit')): ?>
                                                <a href="<?= url('distribution/edit', ['id' => $item['id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('distribution/edit')): ?>
<!--                                                <a class="js-trim" href="javascript:;">-->
<!--                                                    <i class="am-icon-sitemap"></i> 调级-->
<!--                                                </a>-->
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('distribution/show')): ?>
                                                <a href="<?= url('distribution/show', ['id' => $item['id']]) ?>">
                                                    <i class="am-icon-eye"></i> 记录
                                                </a>
                                            <?php endif; ?>
                                            <div class="am-btn-group">
                                                <?php if (checkPrivilege('distribution/own_user')): ?>
                                                    <button class="am-btn am-btn-default am-btn-xs">所属会员</button>
                                                    <div class="am-dropdown" data-am-dropdown>
                                                        <button class="am-btn am-btn-default am-dropdown-toggle am-btn-xs" data-am-dropdown-toggle> <span class="am-icon-caret-down"></span></button>
                                                        <ul class="am-dropdown-content">
                                                            <li><a href="<?= url('distribution/own_user', ['id' => $item['id']]) ?>"><i class="am-icon-eye"></i> 查看会员</a></li>
                                                            <?php if (checkPrivilege('distribution/exchange')): ?>
                                                                <li><a class="js-all-setting" href="javascript:;"  data-code="<?= $item['fx_code'] ?>"><i class="am-icon-exchange"></i> 全部转移</a></li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="10" class="am-text-center">暂无记录</td>
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

<!-- 调级模板 -->
<script id="tpl-trim" type="text/template">
    <div class="am-padding-xs am-padding-top-sm">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="j-tabs am-tabs">
                <div class="am-tabs-bd am-padding-xs">
                    <div class="am-tab-panel am-padding-0 am-active" id="tab1">
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                分销人员调级
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <input type="text" class="am-form-field" name="fx_code" placeholder="请输入分销码" value="">
                                <small>注：1、输入分销码为上一级人员的分销码，可以升降级</small><br>
                                <small>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp2、被调级者调级前需要将所属下级人员全部转移走方能调整</small><br>
                                <small>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp3、支持多选</small><br>
                                <small>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp4、支持多选</small><br>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>

<!-- 转移会员模板 -->
<script id="tpl-recharge" type="text/template">
    <div class="am-padding-xs am-padding-top-sm">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="j-tabs am-tabs">
                <div class="am-tabs-bd am-padding-xs">
                    <div class="am-tab-panel am-padding-0 am-active" id="tab1">
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                分销人员
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <input type="text" class="am-form-field" name="fx_code" placeholder="请输入分销码" value="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>

<script>
    $(function () {

        // 全部转移
        $(document).on("click",'.js-trim',function(){
            var data = $(this).data();
            $.showModal({
                title: '分销人员调级'
                , area: '460px'
                , content: template('tpl-trim', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('distribution/exchange') ?>',
                        data: {old_fx_code: data.code}
                    });
                    return true;
                }
            });
        });

        // 全部转移
        $(document).on("click",'.js-all-setting',function(){
            var data = $(this).data();
            $.showModal({
                title: '转移全部会员'
                , area: '460px'
                , content: template('tpl-recharge', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('distribution/exchange') ?>',
                        data: {old_fx_code: data.code}
                    });
                    return true;
                }
            });
        });

        // 删除元素
        var url = "<?= url('shop.role/delete') ?>";
        $('.item-delete').delete('role_id', url, '删除后不可恢复，确定要删除吗？');

    });
</script>

