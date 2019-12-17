<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加抵扣券</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">抵扣券名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="coupon[coupon_name]"
                                           value="抵扣卷" placeholder="请输入抵扣名称" required>
                                    <small>例如：满100减10</small>
                                </div>
                            </div>

                            <div class="am-form-group ">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">条件金额 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" min="0.01" class="tpl-form-input" name="coupon[money]"
                                           value="" placeholder="请输入条件金额" required>
                                </div>
                            </div>

                            <div class="am-form-group ">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">抵扣金额 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" min="0.01" class="tpl-form-input" name="coupon[discount]"
                                           value="" placeholder="请输入抵扣金额" required>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">每天使用次数 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="coupon[day_times]"
                                           value="1" placeholder="请输入每天使用次数" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">总使用次数 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="coupon[total_times]"
                                           value="0" placeholder="请输入总使用次数" >
                                    <small>0为不限制</small>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">有效天使 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="coupon[limit_times]"
                                           value="1" placeholder="请输入有效天使" required>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">充值规则 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="coupon[recharge_id]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm',
                                             placeholder:'请选择充值规则', maxHeight: 400}">
                                        <option value=""></option>
                                        <?php if (isset($reCoupon)): foreach ($reCoupon as $item): ?>
                                            <option value="<?= $item['id'] ?>"><?= $item['desc']['text'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>

                                </div>
                            </div>

                            <div class="am-form-group" data-x-switch>
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">店铺选择 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="coupon[storeValue]" value="1" checked
                                               data-am-ucheck
                                               data-switch-box="switch-coupon_type"
                                               data-switch-item="coupon_type__1">
                                        全部店铺
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="coupon[storeValue]" value="2"
                                               data-am-ucheck
                                               data-switch-box="switch-coupon_type"
                                               data-switch-item="coupon_type__2">
                                        指定店铺
                                    </label>
                                </div>
                            </div>


                            <div class="am-form-group switch-coupon_type coupon_type__2 hide">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">店铺列表 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="coupon[storeName]"
                                           value="" placeholder="" disabled required>

                                </div>
                            </div>



                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <input type="hidden" value="1" name="type">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {

        // swith切换
        var $mySwitch = $('[data-x-switch]');
        $mySwitch.find('[data-switch-item]').click(function () {
            var $mySwitchBox = $('.' + $(this).data('switch-box'));
            $mySwitchBox.hide().filter('.' + $(this).data('switch-item')).show();
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
