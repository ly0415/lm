<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">交班报表</div>
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
                                            <?php if (checkPrivilege('shop.order/excelout')): ?>
                                                <a class="j-export am-btn am-btn-success am-radius"
                                                   href="javascript:void(0);">
                                                    <i class="iconfont icon-daochu am-margin-right-xs"></i>报表导出
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-u-sm-12 am-u-md-9">
                                <div class="am" style="display:flex;justify-content:flex-end;">
                                    <?php if(!T_GENERAL):?>
                                    <div class="am-form-group am-fl">
                                        <?php
                                            $storeId = $request->get('store_id');
                                            $storeId = empty($storeId) ? SELECT_STORE_ID : $storeId;
                                        ?>
                                        <select name="store_id" data-am-selected="{btnSize: 'sm', placeholder: '选择店铺'}"><option value=""></option>
                                            <?php if(isset($stores)):foreach ($stores as $item):?>
                                                <option value="<?=$item['id']?>" <?=$storeId == $item['id'] ? 'selected' : ''?>>
                                                    <?=$item['store_name']?>
                                                </option>
                                            <?php endforeach;endif;?>
                                        </select>
                                    </div>
                                    <?php endif;?>
                                    <div class="am-form-group am-fl">
                                        <?php $sendout = $request->get('sendout'); ?>
                                        <select name="sendout"
                                                data-am-selected="{btnSize: 'sm', placeholder: '配送属性'}">
                                            <option value=""></option>
                                            <option value="-1"
                                                <?= $sendout === '-1' ? 'selected' : '' ?>>全部
                                            </option>
                                            <?php if (isset($send)): foreach ($send as $k=> $items): ?>
                                                <option value="<?= $k ?>"
                                                    <?= $k == $sendout ? 'selected' : '' ?>><?= $items ?>
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input type="text" name="start_time" class="am-form-field" value="<?= $request->get('start_time') ? : date('Y-m-d')?>" placeholder="请选择起始日期" data-am-datepicker>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input type="text" name="end_time" class="am-form-field" value="<?= $request->get('end_time') ? : date('Y-m-d')?>" placeholder="请选择截止日期" data-am-datepicker>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
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
                                <th>订单编号</th>
                                <?php if(!T_GENERAL):?>
                                    <th>所属店铺</th>
                                <?php endif; ?>
                                <th>配送属性</th>
                                <th>买家姓名</th>
                                <th>买家手机</th>
                                <th>所属平台</th>
                                <th>支付方式</th>
                                <th>付款时间</th>
                                <th>实付金额</th>
                                <th>订单状态</th>
                                <th>订单运费</th>
                                <th>优惠额抵扣</th>
                                <th>分销码抵扣</th>
                                <th>睿积分抵扣</th>
                                <th>优惠劵抵扣</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['order_sn'] ?></td>
                                    <?php if(!T_GENERAL):?>
                                        <td class="am-text-middle">
                                            <span class="am-badge am-badge-secondary">
                                               <?= $item['format_store_name'] ?>
                                           </span>
                                        </td>
                                    <?php endif; ?>
                                    <td class="am-text-middle"><?= $item['sendoutName'] ?></td>
                                    <td class="am-text-middle"><?= $item['username'] ?></td>
                                    <td class="am-text-middle"><?= $item['phone'] ?></td>
                                    <td class="am-text-middle">
                                        <?= $item['sourceName'] ?>
                                    </td>
                                    <td class="am-text-middle"><?= $item['paymentName'] ?></td>
                                    <td class="am-text-middle"><?= $item['payment_time'] ?></td>
                                    <td class="am-text-middle">￥<?= $item['order_amount'] ?></td>
                                    <td class="am-text-middle"><?= $item['statusName'] ?></td>
                                    <td class="am-text-middle"><?= $item['shipping_fee'] ?></td>
                                    <td class="am-text-middle"><?= $item['discount'] ?></td>
                                    <td class="am-text-middle"><?= $item['fx_money'] ?></td>
                                    <td class="am-text-middle"><?= $item['point_discount'] ?></td>
                                    <td class="am-text-middle"><?= $item['coupon_discount'] ?>
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
        var url = "<?= url('shop.clerk/delete') ?>";
        $('.item-delete').delete('clerk_id', url, '删除后不可恢复，确定要删除吗？');

        /**
         * 订单导出
         */
        $('.j-export').click(function () {
            var data = {};
            var formData = $('#form-search').serializeArray();
            $.each(formData, function () {
                this.name !== 's' && (data[this.name] = this.value);
            });
            // console.log(data);return false;
            window.location = "<?= url('shop.order/excelOut') ?>" + '&' + $.urlEncode(data);
        });


    });
</script>

