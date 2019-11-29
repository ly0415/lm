
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">小程序微信银收列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form id="form-search" class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">


                            <div class="am-u-sm-12 am-u-md-3">
                                <div class="am-form-group">
                                    <div class="am-btn-toolbar">
                                        <div class="am-btn-group am-btn-group-xs">                                            <h2>实付总额：<?=$totalMoney?>元</h2>

                                            <?php if (checkPrivilege('store.wx_pay/export')): ?>
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
                                        <?php $store_id = $request->get('store_id') ? : STORE_ID ?>

                                        <select name="store_id"
                                                data-am-selected="{btnSize: 'sm', placeholder: '门店列表'}">
                                            <option value=""></option>
                                          <?php if(isset($stores)):foreach ($stores as $v):?>
                                            <option value="<?=$v['id']?>" <?=$store_id == $v['id'] ? 'selected':'';?>><?=$v['store_name']?></option>
                                            <?php endforeach;endif;?>


                                        </select>
                                    </div>

                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input autocomplete="off" type="text" name="start_time"
                                               class="am-form-field j-startTime"
                                               value="<?= $request->get('start_time') ?>" placeholder="付款开始时间"
                                               data-am-datepicker>
                                    </div>

                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input autocomplete="off" type="text" name="end_time"
                                               class="am-form-field j-endTime"
                                               value="<?= $request->get('end_time') ?>" placeholder="付款结束时间"
                                               data-am-datepicker>
                                    </div>

                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field " name="pay_sn"
                                                   placeholder="请输入支付单号" value="<?= $request->get('pay_sn') ?>">
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
                                <th>所属店铺</th>
                                <th>支付单号</th>
                                <th>实付金额</th>
                                <th>微信入账金额</th>
                                <th>对账状态</th>
                                <th>下单时间</th>
                                <th>付款时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['order_sn'] ?></td>

                                    <td class="am-text-middle">
                                        <span class="am-badge am-badge-secondary">
                                           <?=$item['store_name']?>
                                       </span>
                                    </td>

                                    <td class="am-text-middle"><?= $item['pay_sn'] ?></td>
                                    <td class="am-text-middle"><?= $item['order_amount'] ?></td>
                                    <td class="am-text-middle"><?= $item['wx_amount'] ?></td>
                                    <td class="am-text-middle"><?= $item['status']['text']?></td>
                                    <td class="am-text-middle"><?= $item['add_time'] ?></td>
                                    <td class="am-text-middle">
                                           <?= $item['payment_time'] ?>
                                    </td>

                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="7" class="am-text-center">暂无记录</td>
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
         * 数据导出
         */
        $('.j-export').click(function () {
            var data = {};
            var formData = $('#form-search').serializeArray();
            $.each(formData, function () {
                this.name !== 's' && (data[this.name] = this.value);
            });
            // console.log(data);return false;
            window.location = "<?= url('store.wx_pay/export') ?>" + '&' + $.urlEncode(data);
        });


    });
</script>

