<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">列表</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form id="form-search" class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-3">
                                <div class="am-form-group">
                                    <div class="am-btn-toolbar">
                                        <div class="am-btn-group am-btn-group-xs">
                                            <?php if (checkPrivilege('store.data/export')): ?>
                                                <a class="j-export am-btn am-btn-success am-radius"
                                                   href="javascript:void(0);">
                                                    <i class="iconfont icon-daochu am-margin-right-xs"></i>数据导出
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-u-md-9">
                                <div class="am fr am-g am-fr">
                                    <div class="am-form-group am-fl">
                                        <?php $state = $request->get('state'); ?>
                                        <select name="state"
                                                data-am-selected="{btnSize: 'sm', placeholder: '使用状态'}">
                                            <option value=""></option>
                                            <option value="-1"
                                                <?= $state === '-1' ? 'selected' : '' ?>        >全部
                                            </option>
                                            <option value="1" <?=$state == 1 ? 'selected':'';?>>未使用</option>
                                            <option value="2" <?=$state == 2 ? 'selected':'';?>>已使用</option>

                                        </select>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <?php $type = $request->get('type');  ?>
                                        <select name="type"
                                                data-am-selected="{btnSize: 'sm', placeholder: '劵码类型'}">
                                            <option value=""></option>
                                            <option value="-1"
                                                <?= $type === '-1' ? 'selected' : '' ?>>全部
                                            </option>
                                            <option value="1" <?=$type == 1 ? 'selected':'';?>>抵扣卷</option>
                                            <option value="2" <?=$type == 2 ? 'selected':'';?>>兑换卷</option>

                                        </select>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input autocomplete="off" type="text" name="add_time"
                                               class="am-form-field"
                                               value="<?= $request->get('add_time') ?>" placeholder="发送开始时间"
                                               data-am-datepicker>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input autocomplete="off" type="text" name="end_time"
                                               class="am-form-field"
                                               value="<?= $request->get('end_time') ?>" placeholder="发送结束时间"
                                               data-am-datepicker>
                                    </div>

                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="phone"
                                                   placeholder="请输入手机号" value="<?= $request->get('phone') ?>">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search"
                                                        type="submit"></button>
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
                                <th>用户名</th>
                                <th>手机号</th>
                                <th>类型</th>
                                <th>状态</th>
                                <th>开始时间</th>
                                <th>结束时间</th>
                                <th>发放时间</th>
                                <th>描述</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php   if (!$list->isEmpty()): foreach ($list as $item):  ?>
                                <tr>
                                    <td class="am-text-middle"><?=$item['user']['username']?></td>
                                    <td class="am-text-middle"><?=$item['user']['phone']?></td>
                                    <td class="am-text-middle"><?=$item['coupon']['type']['text']?></td>
                                    <td class="am-text-middle"><?=empty($item['lid'])?'未使用':'已使用'?></td>
                                    <td class="am-text-middle"><?=$item['start_time']['text']?>
                                    </td>
                                    <td class="am-text-middle"><?=$item['end_time']['text']?></td>
                                    <td class="am-text-middle"><?=$item['add_time']['text']?></td>
                                    <td class="am-text-middle"><?=$item['coupon']['desc']['text']?></td>

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
        var url = "<?= url('store.data/delete') ?>";
        $('.item-delete').delete('user_id', url, '删除后不可恢复，确定要删除吗？');

        /**
         * 订单导出
         */
        $('.j-export').click(function () {
            var data = {};
            var formData = $('#form-search').serializeArray();
            $.each(formData, function () {
                this.name !== 's' && (data[this.name] = this.value);
            });
            window.location = "<?= url('store.data/export') ?>" + '&' + $.urlEncode(data);
        });

    });
</script>

