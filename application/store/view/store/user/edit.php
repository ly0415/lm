<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑会员</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-">微信图片 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <img src="<?= $list['headimgurl'] ?>" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-">用户名 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="" value="<?= $list['username'] ?>"  disabled>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-">手机号码 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="user[phone]" value="<?= $list['phone'] ?>"  disabled>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-">邮箱 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="user[email]" value="<?= $list['email'] ?>"  >
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-u-lg-2 am-form-label form-require">性别 </label>
                                <div class="am-u-sm-10 am-u-end">
                                    <label class="am-radio-inline">
                                        <input id="man" type="radio" name="user[sex]" <?php if($list['sex']=='男') echo "checked='checked'";?> value="1" data-am-ucheck checked>男
                                    </label>
                                    <label class="am-radio-inline">
                                        <input id="woman" type="radio" name="user[sex]" <?php if($list['sex']=='女') echo "checked='checked'";?> value="2" data-am-ucheck>
                                        <span class="am-link-muted">女</span>
                                    </label>
                                </div>
                            </div>

                            <div class="am-form-group switch-expire_type expire_type__20">
                                <label class="am-u-sm-2 am-u-lg-2 am-form-label form-">生日 </label>
                                <div class="am-u-sm-10 am-u-end">
                                    <input type="text" class="j-startTime am-form-field am-margin-bottom-sm"
                                           name="user[birth]" value="<?= $list['birth']?$list['birth']:'2000-1-1' ?>" placeholder="请选择开始日期" required>

                                </div>
                            </div>

                           <div class="am-form-group">
                                <label class="am-u-sm-2 am-u-lg-2 am-form-label form-require">启用状态 </label>
                                <div class="am-u-sm-10 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="user[is_use]" value="1" <?php if($list['is_use']==1) echo "checked='checked'";?> data-am-ucheck checked>启用
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="user[is_use]" value="2" <?php if($list['is_use']==2) echo "checked='checked'";?> data-am-ucheck>
                                        <span class="am-link-muted">禁用</span>
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="user[is_use]" value="3" <?php if($list['is_use']==3) echo "checked='checked'";?> data-am-ucheck>
                                        <span class="am-link-muted">黑名单</span>
                                    </label>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
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

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
<script>
    /**
     * 时间选择
     */
    $(function () {
        var nowTemp = new Date();
        var nowDay = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0).valueOf();
        var nowMoth = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), 1, 0, 0, 0, 0).valueOf();
        var nowYear = new Date(nowTemp.getFullYear(), 0, 1, 0, 0, 0, 0).valueOf();
        var $startTime = $('.j-startTime');

        // var checkin = $startTime.datepicker({
        //     onRender: function (date, viewMode) {
        //         // 默认 days 视图，与当前日期比较
        //         var viewDate = nowDay;
        //         switch (viewMode) {
        //             // moths 视图，与当前月份比较
        //             case 1:
        //                 viewDate = nowMoth;
        //                 break;
        //             // years 视图，与当前年份比较
        //             case 2:
        //                 viewDate = nowYear;
        //                 break;
        //         }
        //         return date.valueOf() < viewDate ? 'am-' : '';
        //     }
        // }).on('changeDate.datepicker.amui', function (ev) {
        //     if (ev.date.valueOf() > checkout.date.valueOf()) {
        //         var newDate = new Date(ev.date)
        //         newDate.setDate(newDate.getDate() + 1);
        //         checkout.setValue(newDate);
        //     }
        //     checkin.close();
        // }).data('amui.datepicker');
        var checkin = $startTime.datepicker()

    });
</script>
