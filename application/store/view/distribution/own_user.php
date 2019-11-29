<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">所属会员列表---【<?= $userList['real_name'] ?>】</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-3">
                                <div class="am-form-group">
                                    <?php if (checkPrivilege('distribution/exchange')): ?>
                                        <div class="am-btn-group am-btn-group-xs">
                                            <a class="am-btn am-btn-default am-btn-success js-all-setting" href="javascript:;"><i class="am-icon-exchange"></i> 批量转移会员
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="am-u-sm-12 am-u-md-9">
                                <div class="am fr">
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="username" placeholder="请输入用户名" value="<?= $request->get('username') ?>">
                                        </div>
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
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>序号</th>
                                <th>用户名</th>
                                <th>手机号</th>
                                <th>单独下单优惠</th>
                                <th>注册时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $key => $item): ?>
                                <tr>
                                    <td class="am-text-middle"><input type="checkbox" name="check-name" value="<?= $item['user_id'] ?>"></td>
                                    <td class="am-text-middle"><?= $item['username'] ?></td>
                                    <td class="am-text-middle"><?= $item['phone'] ?></td>
                                    <td class="am-text-middle"><?= $item['discounts'] ?></td>
                                    <td class="am-text-middle"><?= date('Y-m-d H:i:s', $item['add_time']) ?></td>
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
                                <input type="hidden" name="old_fx_code" value="<?= $userList['fx_code'] ?>">
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
        // 批量设置
        $(document).on("click",'.js-all-setting',function(){
            var data = $(this).data();
            var arr = new Array();
            var checks = $('table tr td').find('input[name="check-name"]:checked');
            if (checks.length <= 0) {
                layer.msg('请选择设置项！', {icon: 7});
            }else{
                for (var i = 0; i < checks.length; i++) {
                    arr[i] = checks[i].defaultValue;
                }
                arr = arr.join(',');
                $.showModal({
                    title: '转移会员'
                    , area: '460px'
                    , content: template('tpl-recharge', data)
                    , uCheck: true
                    , success: function ($content) {
                    }
                    , yes: function ($content) {
                        $content.find('form').myAjaxSubmit({
                            url: '<?= url('distribution/exchange') ?>',
                            data: {user_id: arr}
                        });
                        return true;
                    }
                });
            }
        });
    });
</script>

