<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/orderPay.css">
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget__order-detail widget-body">
                    <!-- 订单进度步骤条 -->
                    <div class="am-u-sm-12">
                        <?php
                            // 计算当前步骤位置
                            $progress = 2;
                            $orderDetail['order_state'] >= 20 && $progress += 1;
                            $orderDetail['order_state'] >= 25 && $progress += 1;
                            $orderDetail['order_state'] >= 50 && $progress += 1;    //自提流程
                            $orderDetail['evaluation_state'] == 1 && $progress += 1;
                        ?>
                        <ul class="order-detail-progress progress-<?= $progress ?>">
                            <li>
                                <span>下单时间</span>
                                <div class="tip"><?= $orderDetail['format_add_time'] ?></div>
                            </li>
                            <li>
                                <span>付款</span>
                                <?php if ($orderDetail['order_state'] >= 20): ?>
                                    <div class="tip">
                                        付款于 <?= $orderDetail['format_payment_time'] ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                            <li>
                                <span>接单</span>
                                <?php if ($orderDetail['order_state'] >= 25): ?>
                                    <div class="tip">
                                        接单于 <?= $orderDetail['format_receive_time'] ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                            <li>
                                <span>收货</span>
                                <?php if ($orderDetail['order_state'] >= 50): ?>
                                    <div class="tip">
                                        收货于 <?= $orderDetail['format_receive_time'] ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                            <li>
                                <span>评价</span>
                                <?php if ($orderDetail['evaluation_state'] == 1): ?>
                                    <div class="tip">
                                        评价于 <?= $orderDetail['format_comment_time'] ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                        </ul>
                    </div>

                    <!-- 基本信息 -->
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">基本信息</div>
                    </div>
                    <div style="overflow-x:scroll;overflow-y:hidden;">
                        <table class="regional-table am-table am-table-bordered am-table-centered
                            am-text-nowrap am-margin-bottom-xs">
                            <tbody>
                            <tr>
                                <th>订单号</th>
                                <th>买家</th>
                                <th>订单金额</th>
                                <th>支付方式</th>
                                <th>配送方式</th>
                                <th>交易状态</th>
                               <th>操作</th>
                            </tr>
                            <tr>
                                <td><?= $orderDetail['order_sn'] ?></td>
                                <td>
                                    <p><?= $orderDetail['username'] ?></p>
                                    <p class="am-link-muted">( 手机号：<?= $orderDetail['phone'] ?> )</p>
                                </td>
                                <td class="">
                                    <div class="td__order-price am-text-left">
                                        <ul class="am-avg-sm-2">
                                            <li class="am-text-right">订单总额：</li>
                                            <li class="am-text-right">￥<?= $orderDetail['goods_amount'] ?> </li>
                                        </ul>
                                        <?php if($orderDetail['sendout'] == 2): ?>
                                            <ul class="am-avg-sm-2">
                                                <li class="am-text-right">配送费：</li>
                                                <li class="am-text-right">￥<?= $orderDetail['shipping_fee'] ?> </li>
                                            </ul>
                                        <?php endif; ?>
                                        <ul class="am-avg-sm-2">
                                            <li class="am-text-right">实付金额：</li>
                                            <li class="x-color-red am-text-right">￥<?= $orderDetail['order_amount'] ?></li>
                                        </ul>
                                        <?php if($orderDetail['source'] == 3): ?>
                                            <ul class="am-avg-sm-2">
                                                <li class="am-text-right">
                                                    <?php if(!empty(intval($orderDetail['discount_num'])) && intval($orderDetail['discount_num']) != 10): ?>代客优惠折扣<?php else: ?>代客优惠金额<?php endif; ?> ：</li>
                                                <li class="x-color-red am-text-right">
                                                    <?= !empty(intval($orderDetail['discount_num']) && intval($orderDetail['discount_num']) != 10) ? $orderDetail['discount_num'].'折' : '￥ '. $orderDetail['discount']; ?>
                                                </li>
                                            </ul>
                                        <?php endif; ?>
                                        <ul class="am-avg-sm-2">
                                            <li class="am-text-right">分销优惠：</li>
                                            <li class="x-color-red am-text-right">￥<?= $orderDetail['fx_money'] ?></li>
                                        </ul>
                                        <?php if(isset($orderDetail['format_coupon_desc'])): ?>
                                            <ul class="am-avg-sm-2">
                                                <li class="am-text-right">电子券：</li>
                                                <li class="x-color-red am-text-right"><?= $orderDetail['format_coupon_desc'] ?></li>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="am-badge am-badge-<?php if($orderDetail['order_state'] == 0 || $orderDetail['order_state'] == 10): ?>default
                                        <?php else: ?>secondary
                                        <?php endif; ?>">
                                        <?= $orderDetail['format_payment_type'] ?>
                                    </span>
                                </td>
                                <td>
                                    <p>
                                        <span class="am-badge am-badge-secondary"><?= $orderDetail['format_delivery_type'] ?></span>
                                    </p>
                                    <p class="am-link-muted">
                                        <?php if($orderDetail['sendout'] == 1): ?>
                                            ( 自取时间：<?= $orderDetail['format_sendout_time']; ?> )
                                        <?php endif; ?>
                                    </p>
                                </td>
                                <td>
                                    <p>订单状态：<span class="am-badge am-badge-<?php if($orderDetail['order_state'] == 0): ?>default
                                        <?php elseif($orderDetail['order_state'] == 10): ?>warning
                                        <?php elseif($orderDetail['order_state'] == 25): ?>primary
                                        <?php elseif($orderDetail['order_state'] == 60): ?>danger
                                        <?php else: ?>success
                                        <?php endif; ?>">
                                        <?= $orderDetail['format_order_state'] ?></span>
                                    </p>
                                </td>
                                <td class="am-text-middle">
                                    <?php if(STORE_ID == 98):?>
                                        <?php if($orderDetail['order_state']):?>
                                    <div class="tpl-table-black-operation">
                                        <div class="am-btn-group" style="position:static">
                                            <div class="am-dropdown am-dropdown-flip" data-am-dropdown="" style="position:static">
                                                <button class="am-btn am-btn-secondary am-dropdown-toggle am-btn-xs">
                                                   立即付款
                                                   <span class="am-margin-left-sm am-icon-caret-down"></span>
                                                </button>
                                                <ul class="am-dropdown-content am-text-center" style="position:absolute;z-index:100;top:320px;
                                                right:60px;">
                                                    <li>
                                                        <a class="js-all-setting" href="javascript:;" data-code="432237" class="yinlian" data-am-modal="{target: '#doc-modal-11'}">聚合支付</a>
                                                    </li>
                                                    <li>
                                                        <a class="js-all-setting" href="javascript:;" data-code="432237">支付宝支付</a>
                                                    </li>
                                                    <li>
                                                        <a class="js-all-setting" href="javascript:;" data-code="432237">微信支付</a>
                                                    </li>
                                                    <li>
                                                        <a class="js-all-setting" href="javascript:;" data-code="432237" data-am-modal="{target: '#doc-modal-2'}">余额付款</a>
                                                    </li>
                                                    <li>
                                                        <a class="js-all-setting" href="javascript:;" data-code="432237" data-am-modal="{target: '#doc-modal-3'}">线下支付</a>
                                                    </li>
                                                    <li>
                                                        <a class="js-all-setting" href="javascript:;" data-code="432237">加入预购</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif;?>
                                    <?php endif;?>
                                </td>
                            </tr>
                            <?php if($orderDetail['sendout'] == 2): ?>
                                <tr>
                                    <td colspan="6" class="am-text-right am-cf">
                                        <span class="am-fl">配送地址：<?= $orderDetail['format_delivery']['address'] ?></span>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- 余额支付弹框 -->
                    <div class="am-modal am-modal-no-btn" tabindex="-1" id="doc-modal-2">
                        <div class="am-modal-dialog" style="width:450px;height:250px;background-color:white;">
                            <div class="am-modal-hd" style="padding:0;">
                                <div class="widget-head am-cf" style="margin:0;">
                                    <div class="widget-title am-fl">余额支付</div>
                                </div>
                                <a href="javascript: void(0)" class="am-close am-close-spin" data-am-modal-close>&times;</a>
                            </div>
                            <div class="am-modal-bd">
                                <div class="am-form-group" style="height:30px;">
                                    <div style="display:flex;justify-content: center;" class="am-margin-top-xl">
                                        <span style="font-size:14px;">请输入验证码：</span>
                                        <input type="text" name="order[code]" value="" style="border:0;border-bottom:1px solid #999;width:150px;">
                                        <button class="am-margin-left-xs am-btn am-btn-default am-btn-xs" id="phoneCode" type="button">获取验证码</button>
                                        <button class="am-margin-left-xs am-btn am-btn-default am-btn-xs" id="J_resetCode" type="button" style="display:none;"><span id="J_second">60</span>
                                    </div>
                                </div>
                                <button type="button" class="am-modal-btn cancelPay am-margin-top" data-am-modal-cancel>取消付款</button>
                                <button type="submit" class="j-submit am-btn am-margin-top">确认付款</button>
                            </div>
                        </div>
                    </div>

                    <!-- 线下支付弹框 -->
                    <div class="am-modal" id="doc-modal-3">
                        <div class="am-modal-dialog" style="width:450px;background-color:white;">
                            <div class="am-modal-hd am-text-left am-text-xs">
                                <div class="widget-head am-cf" style="margin-top:0;padding-top:0;">
                                    <div class="widget-title am-fl">线下支付</div>
                                </div>
                                <a href="javascript: void(0)" class="am-close am-close-spin" data-am-modal-close>&times;</a>
                            </div>
                            <div class="am-modal-bd">
                                <div class="specBox">
                                    <div class="row">
                                        <div class="specName">所属平台：</div>
                                        <div class="specVal">
                                            <input type="radio" name="order[source_id]" value="1758421" checked>
                                            <img src="upload/images/goods/cus_order/lmeri.png" alt="">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="specName">当前金额：</div>
                                        <div class="specVal">
                                            <span class="currentPrice"></span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="specName">支付金额：</div>
                                        <div class="specVal">
                                            <input type="number" class="am-fl" id="payMoney" name="order[payMoney]" style="border:1px solid #d1d1d1;padding:6px 5px 5px 10px;width:200px;height:33px;">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="specName">找零：</div>
                                        <div class="specVal">
                                            <span class="changemoney">￥0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-modal-footer" style="background-color:white;">
                                <button class="am-modal-btn" data-am-modal-cancel style="background-color:white;">取消付款</button>
                                <button type="submit" class="j-submit am-btn" style="background-color:white;">确认付款
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 聚合支付弹框 -->
                    <div class="am-modal am-modal-no-btn" tabindex="-1" id="doc-modal-11">
                        <div class="am-modal-dialog" style="width:400px;height:250px;overflow:auto;border-top:3px solid #ffa627;background:#fff;">
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">扫码支付</div>
                            </div>
                            <div class="am-modal-body " style="position: relative;" id="yinlian_img">
                                <img src="upload/images/goods/cus_order/lmeri.png" alt="">
                            </div>
                        </div>
                    </div>
                    
                    <!-- 商品信息 -->
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">商品信息</div>
                    </div>
                    <div class="am-scrollable-horizontal">
                        <table class="regional-table am-table am-table-bordered am-table-centered
                            am-text-nowrap am-margin-bottom-xs">
                            <tbody>
                            <tr>
                                <th>商品名称</th>
<!--                                <th>商品编码</th>-->
                                <th>单价</th>
                                <th>购买数量</th>
                                <th>商品总价</th>
                            </tr>
                            <?php foreach ($orderDetail['goods'] as $goods): ?>
                                <tr>
                                    <td class="goods-detail am-text-middle">
                                        <div class="goods-image">
                                            <img src="<?= DOMAIN_NAME.$goods['goods_image'] ?>" alt="">
                                        </div>
                                        <div class="goods-info">
                                            <p class="goods-title"><?= $goods['goods_name'] ?></p>
                                            <p class="goods-spec am-link-muted">
                                                <?= $goods['spec_key_name'] ?>
                                            </p>
                                        </div>
                                    </td>
<!--                                    <td>待开发</td>-->
                                    <td>￥<?= $goods['goods_price'] ?></td>
                                    <td>×<?= $goods['goods_num'] ?></td>
                                    <td>￥<?= ($goods['goods_price']*$goods['goods_num']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="4" class="am-text-right am-cf">
                                    <span class="am-fl">买家留言：<?= $orderDetail['format_seller_msg'] ?></span>
                                    <span class="am-fr">总计金额：￥<?= $orderDetail['goods_amount'] ?></span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <?php if($orderRefundDetail): ?>
                        <!-- 退款商品信息 -->
                        <div class="widget-head am-cf">
                            <div class="widget-title am-fl">退款商品信息</div>
                        </div>
                        <div class="tips am-margin-bottom-sm am-u-sm-12">
                            <div class="pre">
                                <p>当前买家已付款并申请退款，审核通过：1、余额支付、微信支付、聚合支付的订单退款金额原路返回，线下付款需要门店长自己手动处理   2、拒绝退款不产生任何金额变更</p>
                            </div>
                        </div>
                        <div class="am-scrollable-horizontal">
                            <table class="regional-table am-table am-table-bordered am-table-centered am-text-nowrap am-margin-bottom-xs">
                                <tbody>
                                    <tr>
                                        <th width="25%">商品名称</th>
                                        <th width="20%">退款原因</th>
                                        <th>退款图片</th>
                                        <th>购买数量</th>
                                        <th>单价</th>
                                        <th>路径</th>
                                        <th>商品总价</th>
                                    </tr>
                                    <?php   $_data = $orderRefundDetail[0];
                                            $count = count($_data['refund_order']);
                                    ?>
                                    <?php foreach ($_data['refund_order'] as $key => $goods): ?>
                                        <tr>
                                            <td class="goods-detail am-text-middle">
                                                <div class="goods-image">
                                                    <img src="<?= DOMAIN_NAME.$goods['goods_image'] ?>" alt="">
                                                </div>
                                                <div class="goods-info">
                                                    <p class="goods-title"><?= $goods['goods_name'] ?></p>
                                                    <p class="goods-spec am-link-muted">
                                                        <?= $goods['spec_key_name'] ?>
                                                    </p>
                                                </div>
                                            </td>
                                            <?php if($key == 0): ?>
                                                <td rowspan="<?= $count; ?>"><?= urldecode($_data['reason_info']) ?></td>
                                                <td rowspan="<?= $count; ?>"></td>
                                            <?php endif; ?>
                                            <td>×<?= $goods['goods_num'] ?></td>
                                            <td>￥<?= $goods['goods_price'] ?></td>
                                            <td>
                                                <?php if($orderDetail['payment_type'] == 3){ ?>
                                                    余额
                                                <?php }elseif($orderDetail['payment_type'] == 2){ ?>
                                                    微信
                                                <?php }elseif($orderDetail['payment_type'] == 11){
                                                    echo $orderDetail['format_payment_type'];
                                                ?>
                                                <?php } else { ?>
                                                    线下
                                                <?php } ?>
                                            </td>
                                            <td>￥<?= ($goods['goods_price'] * $goods['goods_num']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr>
                                        <td colspan="6" class="am-text-right am-cf">
                                            <span class="am-fr">退款总计：￥<?= $orderRefundDetail[0]['refund_amount'] ?></span>
                                        </td>
                                        <?php if ($orderDetail['order_state'] == 60): ?>
                                            <td class="am-text-center am-cf">
                                                <button type="button" class="am-btn am-btn-secondary am-btn-xs am-round j-refund">审核</button>
                                            </td>
                                        <?php else: ?>
                                            <td class="am-text-center am-cf">
                                                <em>已审核</em>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 退款审核模板 -->
<script id="tpl-refund" type="text/template">
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
<!--                        <div class="am-form-group">-->
<!--                            <label class="am-u-sm-3 am-form-label">-->
<!--                                备注-->
<!--                            </label>-->
<!--                            <div class="am-u-sm-8 am-u-end">-->
<!--                                <textarea rows="2" name="refund[remark]" placeholder="请输入备注（驳回必填）" class="am-field-valid"></textarea>-->
<!--                            </div>-->
<!--                        </div>-->
                    </div>
                </div>

            </div>
        </form>
    </div>
</script>
<script>
    $(function () {
        /**
         * 退款审核
         */
        $('.j-refund').on('click', function () {
            var data        = $(this).data();
            var order_sn    = "<?= $orderDetail['order_sn'];?>";
            $.showModal({
                title: '退款审核'
                , area: '460px'
                , content: template('tpl-refund', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('order/refund') ?>',
                        data: {order_sn: order_sn}
                    });
                    return true;
                }
            });
        });

        // 获取验证码的倒计时
        function resetCode(){
            $('#phoneCode').hide();
            $('#J_second').html('60');
            $('#J_resetCode').show();
            var second = 60;
            var timer = null;
            timer = setInterval(function(){
                second -= 1;
                if(second >0 ){
                    $('#J_second').html(second);
                }else{
                    clearInterval(timer);
                    $('#phoneCode').show();
                    $('#J_resetCode').hide();
                }
            },1000);
        }

        //获取短信验证码
        $("#phoneCode").on('click',function () {
            var phone = $.trim($('#select_input').val());
            var reg = /^1\d{10}$/;    //正则表达式
            if(!reg.test(phone)){
                layer.msg('请正确填写手机号');
                return false;
            }
            layer.confirm('确定要发送短信到'+phone+'吗', function (index) {
                var load = layer.load();
                resetCode();
                var url = "<?= url('order/sms_code') ?>";
                $.post(url, {accept_phone:phone}, function (result) {
                    layer.msg(result.msg);
                    layer.close(load);
                },'JSON');
                layer.close(index);
            });
        });

        // $(document).on('click','.yinlian',function(){
        //     var phone = $.trim($('#select_input').val());
        //     var cart = [];
        //     $.each($('input[name="order[cart_ids][]"]'),function () {
        //         cart.push($(this).val());
        //     });
        //     var pay_money   = $.trim($('#pay_price').val());
        //     var order_sn    = $.trim($('#uniquecode').val());
        //     var data = {order: {
        //             phone : phone,
        //             uniquecode : order_sn,
        //             cart_ids : cart,
        //             pay_type : $('#payType').val(),
        //             sendout : $('input[name="order[sendout]"]').val(),
        //             source : $('input[name="order[source]"]').val(),
        //             sendout_time : $('input[name="order[sendout_time]"]').val(),
        //             fx_user_id : $("#fx_user_id").val(),
        //             fx_discount : $("#fx_discount").val(),
        //             fx_code : $('input[name="order[fx_code]"]').val(),
        //             discount_num : $('input[name="order[discount_num]"]').val(),
        //             coupon_id : $('input[name="order[coupon_id]"]').val(),
        //             user_coupon_id : $('input[name="order[user_coupon_id]"]').val(),
        //             discount_type : $('input[name="order[discount_type]"]').val(),
        //             reduced_price : $('input[name="order[reduced_price]"]').val(),
        //         }
        //     };
        //     $.post("<?=url('order/order_payment')?>",data,function (r) {
        //         if(r.code == 1){
        //             var imgUrl = "<?=url('api/wxapp/getYinlianCode')?>"+"/pay_money/"+pay_money+"/order_sn/"+order_sn+"/store_id/<?= STORE_ID;?>";
        //             $("#yinlian_img").html('<img src="'+imgUrl+'" alt=""/>');
        //             query_order_status(r.data.order_id,r.data.order_sn);
        //         }else if(r.code == 2){
        //             $.show_success(r.msg,r.url);
        //         }else{
        //             $.show_error(r.msg);
        //         }
        //     },'JSON');
        // });
    });
</script>