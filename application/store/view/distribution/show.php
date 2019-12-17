<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">所属会员列表---【<?= $list->fx_user['real_name'] ?> ：账户总佣金：<?= $list->fx_user['monery'] ?>（元） 已提现佣金：<?= $list->fx_money['y'] ?>（元） 未入账佣金：<?= $list->fx_money['w'] ?>（元）】 【 搜索佣金总计：<?= $list->fx_money['s'] ?>（元）】</div>
                </div>
                <div class="tips am-margin-bottom-sm am-u-sm-12">
                    <div class="pre">
                        <p> 注：可入账佣金核算日期从 2019-09-01 起
                    </div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-3"></div>
                            <div class="am-u-sm-12 am-u-md-9">
                                <div class="am fr">
                                    <div class="am-form-group am-fl">
                                        <?php $is_on = ($request->get('is_on') != null) ? $request->get('is_on') : null; ?>
                                        <select name="is_on" data-am-selected="{btnSize: 'sm', placeholder: '收益状态'}">
                                            <option value=""></option>
                                            <option value="1" <?= $is_on  == 1 ? 'selected' : '' ?>> 已入账</option>
                                            <option value="0" <?= ($is_on == 0 && $is_on != null) ? 'selected' : '' ?>> 未入账</option>
                                        </select>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input type="text" name="start_time" class="am-form-field" value="<?= $request->get('start_time') ?>" placeholder="起始付款日期" data-am-datepicker autocomplete="off">
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input type="text" name="end_time" class="am-form-field" value="<?= $request->get('end_time') ?>" placeholder="截止付款日期" data-am-datepicker autocomplete="off">
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="order_sn" placeholder="请输入订单号" value="<?= $request->get('order_sn') ?>">
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
                                <th>订单号</th>
                                <th>实付金额（元）</th>
                                <th>本单佣金（元）</th>
                                <th>佣金比例（%）</th>
                                <th>所属店铺</th>
                                <th>收益状态</th>
                                <th>下单时间</th>
                                <th>付款时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $key => $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['order_sn'].$item['tick'] ?></td>
                                    <td class="am-text-middle"><?= $item['pay_money'] ?></td>
                                    <td class="am-text-middle"><?= $item['fx_commission'] ?></td>
                                    <td class="am-text-middle"><?= $item['fx_commission_percent'] ?></td>
                                    <td class="am-text-middle"><?= $item['format_store_name'] ?></td>
                                    <td class="am-text-middle"><?= $item['format_is_on'] ?></td>
                                    <td class="am-text-middle"><?= $item['format_add_time'] ?></td>
                                    <td class="am-text-middle"><?= $item['format_payment_time'] ?></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="8" class="am-text-center">暂无记录</td>
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