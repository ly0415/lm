
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">全部订单列表</div>
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
<!--                                            -->
<!--                                                <a class="j-export am-btn am-btn-success am-radius"-->
<!--                                                   href="javascript:void(0);">-->
<!--                                                    <i class="iconfont icon-daochu am-margin-right-xs"></i>订单导出-->
<!--                                                </a>-->
<!--                                            -->
                                            <?php if(checkPrivilege('order/extract') && checkPrivilege('order/state')):?>
                                            <button type="button" class="am-btn am-btn-default am-btn-success am-radius"   id="doc-prompt-toggle">核销订单</button>
                                            <?php endif;?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 扫码下单弹框 -->
                            <div class="am-modal am-modal-prompt am-modal-no-btn" tabindex="-1" id="my-prompt">
                                <div class="am-modal-dialog" style="width: 452px;height: 252px;background-color:white;">
                                    <div class="am-modal-hd scanTitle am-text-left" style="padding-top:0;">
                                        <div class="widget-head am-cf" style="margin-top:0;">
                                            <div class="widget-title am-fl">订单核销</div>
                                        </div>
                                        <a href="javascript: void(0)" class="am-close am-close-spin" data-am-modal-close>&times;</a>
                                    </div>
                                    <div class="am-modal-bd" style="display:flex;justify-content: center;">
                                        <div class="am-form-group" style="width:432px;height:60px;margin:30px 0 10px 0;">
                                            <label class="am-u-sm-5 am-u-lg-4 am-form-label" style="margin-bottom:0;font-weight:500;font-size:14px;">核销码：</label>
                                            <div class="am-u-sm-7 am-u-end" style="padding-left:0;padding-right:0;">
                                                <input type="text" class="codeEnterOne tpl-form-input" autocomplete="off" id="codeEnterOne" name="" value="" autofocus="autofocus" style="padding-left:0;padding-right:0;border:0;outline:none;border-bottom:1px solid #5eb95e;">
                                                <small style="color:#aaa;">注：手动输入请按回车键结束</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-u-sm-12 am-u-md-9">
                                <div class="am fr">
                                    <?php if(!T_GENERAL): ?>
                                        <div class="am-form-group am-fl">
                                            <?php
                                                $searchStoreId = $request->get('search_store_id');
                                                $searchStoreId = empty($searchStoreId) ? SELECT_STORE_ID : $searchStoreId;
                                            ?>
                                            <select name="search_store_id" data-am-selected="{btnSize: 'sm',btnWidth:150, placeholder: '所属门店'}">
                                                <option value=""></option>
                                                <?php if (isset($storeList)): foreach ($storeList as $item): ?>
                                                    <option value="<?= $item['id'] ?>"
                                                        <?= $item['id'] == $searchStoreId ? 'selected' : '' ?>><?= $item['store_name'] ?>
                                                    </option>
                                                <?php endforeach; endif; ?>
                                            </select>
                                        </div>
                                    <?php endif; ?>
                                    <div class="am-form-group am-fl">
                                        <?php $order_state = $request->param('order_state'); ?>
                                        <select name="order_state" data-am-selected="{btnSize: 'sm',btnWidth:100, placeholder: '订单状态'}">
                                            <option value="-1"
                                                <?= $order_state === '-1' ? 'selected' : '' ?>>全部
                                            </option>
                                            <?php foreach ($orderState as $key => $item): ?>
                                                <option value="<?= $key ?>"
                                                    <?= isset($order_state) && $key == $order_state ? 'selected' : '' ?>><?= $item ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <?php $searchDeliveryType = $request->get('delivery_type'); ?>
                                        <select name="delivery_type" data-am-selected="{btnSize: 'sm',btnWidth:100, placeholder: '配送方式'}">
                                            <option value=""></option>
                                            <option value="-1"
                                                <?= $searchDeliveryType === '-1' ? 'selected' : '' ?>>全部
                                            </option>
                                            <?php foreach ($deliveryType as $key => $item): ?>
                                                <option value="<?= $key ?>"
                                                    <?= $key == $searchDeliveryType ? 'selected' : '' ?>><?= $item ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input type="text" name="start_time" class="am-form-field" style="width: 150px" value="<?= $request->get('start_time') ?>" placeholder="请选择起始日期" data-am-datepicker autocomplete="off">
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input type="text" name="end_time" class="am-form-field" style="width: 150px" value="<?= $request->get('end_time') ?>" placeholder="请选择截止日期" data-am-datepicker autocomplete="off">
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl" style="width: 150px">
                                        <input type="text" class="am-form-field" name="phone" placeholder="请输入手机号" value="<?= $request->get('phone') ?>">
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form" style="width: 200px">
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
                    <div class="order-list am-scrollable-horizontal am-u-sm-12 am-margin-top-xs">
                        <table width="100%" class="am-table am-table-centered
                        am-text-nowrap am-margin-bottom-xs">
                            <thead>
                            <tr>
                                <th width="25%" class="goods-detail">商品信息</th>
                                <th width="8%">单价/数量</th>
                                <th width="8%">实付款</th>
                                <th width="15%">买家</th>
                                <th>支付方式</th>
                                <th>配送方式</th>
                                <th>订单状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $colspan = 8; ?>
                            <?php if (!$orderList->isEmpty()): foreach ($orderList as $order): ?>
                                <tr class="order-empty">
                                    <td colspan="<?= $colspan ?>"></td>
                                </tr>
                                <tr>
                                    <td class="am-text-middle am-text-left" colspan="<?= $colspan ?>">
                                        <span class="am-margin-right-lg"> <?= $order['format_add_time'] ?></span>
                                        <span class="am-margin-right-lg">订单号：<?= $order['order_sn'] ?></span>
                                        <span class="am-margin-right-lg"><b><?= $order['number_order'] ?></b></span>
                                        <span class="am-margin-right-lg"><img width="60" height="25" src="<?= BIG_IMG.$order['source_img'] ?>"></span>
                                        <div class="am-fr tpl-table-black-operation" style="display:flex;">
                                            <?php if (checkPrivilege('order/appoint') && $order['format_fx_user'] == TRUE): ?>
                                                <a href="javascript:void(0);" data-store="<?=$order['store_id']?>" class="j-appoint" data-buyer="<?=$order['buyer_id']?>">指定分销</a>
                                            <?php endif;?>
                                            <?php if (checkPrivilege('order/state') && $order['order_state'] == 20 && $order['sendout'] != 1) : ?>
                                                <a href="javascript:void(0);" data-sn="<?=$order['order_sn']?>" data-store="<?=$order['store_id']?>" class="j-receive">接单</a>
                                            <?php endif;?>
                                            <?php if (checkPrivilege('order/order_print') && $order['order_state'] >= 20): ?>
                                                <a href="<?= url('order/order_print', ['order_sn' => $order['order_sn']]) ?>">票据打印</a>
                                            <?php endif;?>
                                            <?php if (checkPrivilege('order.tag/index')): ?>
                                                <a href="<?= url('order.tag/add', ['order_sn' => $order['order_sn']]) ?>">顾客画像</a>
                                            <?php endif;?>
                                        </div>
                                    </td>
                                </tr>
                                <?php $i = 0;
                                foreach ($order['goods'] as $goods): $i++; ?>
                                    <tr>
                                        <td class="goods-detail am-text-middle">
                                            <div class="goods-image">
                                                <img src="<?= DOMAIN_NAME.$goods['goods_image'] ?>" alt="">
                                            </div>
                                            <div class="goods-info">
                                                <p class="goods-title"><?= $goods['goods_name'] ?></p>
                                                <p class="goods-spec am-link-muted"><?= $goods['spec_key_name'] ?></p>
                                            </div>
                                        </td>
                                        <td class="am-text-middle">
                                            <p>￥<?= $goods['goods_price'] ?></p>
                                            <p>×<?= $goods['goods_num'] ?></p>
                                        </td>
                                        <?php if ($i === 1) : $goodsCount = count($order['goods']); ?>
                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <p>￥<?= $order['order_amount'] ?></p>
<!--                                                <p class="am-link-muted">(含运费：￥--><?//= $order['express_price'] ?><!--)</p>-->
                                            </td>
                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <p><?= $order['username'] ?></p>
                                                <p class="am-link-muted">( 手机号：<?= $order['format_phone'] ?> )</p>
                                            </td>
                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <span class="am-badge am-badge-<?php if($order['order_state'] == 0 || $order['order_state'] == 10): ?>default
                                                    <?php else: ?>secondary
                                                    <?php endif; ?>
                                                ">
                                                    <?= $order['format_payment_type'] ?>
                                                </span>
                                            </td>
                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <p>
                                                    <span class="am-badge am-badge-<?php if($order['sendout'] == 1): ?>secondary
                                                    <?php else: ?>warning
                                                    <?php endif; ?>"><?= $order['format_delivery_type'] ?></span>
                                                </p>
                                                <p class="am-link-muted">
                                                    <?php if($order['sendout'] == 1): ?>
                                                        ( 自取时间：<?= $order['format_sendout_time']; ?> )
                                                    <?php endif; ?>
                                                </p>
                                            </td>
                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <p>
                                                    <span class="am-badge am-badge-<?php if($order['order_state'] == 0): ?>default
                                                    <?php elseif($order['order_state'] == 10): ?>warning
                                                    <?php elseif($order['order_state'] == 25): ?>primary
                                                    <?php elseif($order['order_state'] == 60): ?>danger
                                                    <?php else: ?>success
                                                    <?php endif; ?>">
                                                        <?= $order['format_order_state'] ?>
                                                    </span>
                                                </p>
                                            </td>
                                            <td class="am-text-middle" rowspan="<?= $goodsCount ?>">
                                                <div class="tpl-table-black-operation">
                                                    <?php if (checkPrivilege('order/detail')): ?>
                                                        <a class="tpl-table-black-operation-green" href="<?= url('order/detail', ['order_sn' => $order['order_sn']]) ?>">订单详情</a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="<?= $colspan ?>" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="am-u-lg-12 am-cf">
                        <div class="am-fr"><?= $orderList->render() ?> </div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录：<?= $orderList->total() ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 退款审核模板 -->
<script id="tpl-appoint" type="text/template">
    <div class="am-padding-xs am-padding-top-sm">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="j-tabs am-tabs">
                <div class="am-tabs-bd am-padding-xs">
                    <div class="am-tab-panel am-padding-0 am-active" id="tab1">
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                状态
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <label class="am-radio-inline">
                                    <input type="radio" name="refund[status]" value="1" data-am-ucheck checked> 同意
                                </label>
                                <label class="am-radio-inline">
                                    <input type="radio" name="refund[status]" value="2" data-am-ucheck> 驳回
                                </label>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">
                                备注
                            </label>
                            <div class="am-u-sm-8 am-u-end">
                                <textarea rows="2" name="refund[remark]" placeholder="请输入备注（驳回必填）" class="am-field-valid"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</script>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>

    //核销
    function extract(order_sn){
        $.post("<?=url('order/state')?>",{order_sn:order_sn},function (res) {
            res.code === 1 ? $.show_success(res.msg,res.url) : $.show_error(res.msg);
        },'JSON');
    }


    $(function () {
        $('#doc-prompt-toggle').on('click', function() {
            $('#my-prompt').modal({
                relatedElement: this,
                onConfirm: function(data) {
                    
                },
                onCancel: function() {
                    
                }
            });
        });

        document.onkeydown = function keyDown(e){
            if (!e) var e = window.event
            if (e.keyCode) keyCode = e.keyCode;
            else if (e.which) keyCode = e.which;
            if(keyCode==13){
                window.event.returnValue = false;  //设置条形码扫描后不进行自动提交
                // console.log(1);return false;
                //获取详细信息操作
                var codeData=$('#codeEnterOne').val();
                if(!codeData || codeData.length != 18){
                    $.show_error('请输入有效订单号');
                    return false;
                }
                extract(codeData)
            }
        }

        /**
         * 订单导出
         */
        $('.j-export').click(function () {
            var data = {};
            var formData = $('#form-search').serializeArray();
            $.each(formData, function () {
                this.name !== 's' && (data[this.name] = this.value);
            });
            window.location = "<?= url('order.operate/export') ?>" + '&' + $.urlEncode(data);
        });

        //指定分销
        $('.j-appoint').click(function () {
            var buyer_id = $(this).attr('data-buyer');
            $.selectData({
                title: '分销人员',
                uri: "<?= url('data.distribution/lists') ?>",
                done: function (data) {
                    $.post("<?= url('order/appoint') ?>", {id:data,buyer_id:buyer_id}, function (result) {
                        result.code === 1 ? $.show_success(result.msg, result.url) : $.show_error(result.msg);
                    });
                }
            });
        });

        //接单
        $('.j-receive').click(function () {
            var data = $(this).data();
            layer.confirm('您确定要接单吗？'
                , {title: '友情提示'}
                , function (index) {
                    $.post("<?= url('order/state') ?>"
                        , {
                            order_sn: data.sn,
                            store_id: data.store,
                        }
                        , function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        });
                    layer.close(index);
                });

        });
    });

</script>