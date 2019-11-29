<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">店铺配送折扣设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 折扣比例(%) </label>
                                <div class="am-u-sm-9">
                                    <div class="am-u-sm-6">
                                        <input type="number" min="0" max="300" class="tpl-form-input" name="store[percent]" value="<?=isset($list['relation_1'][STORE_ID]) ? $list['relation_1'][STORE_ID] : 100?>" required>
                                    </div>
                                    <label class="am-u-sm-6 am-form-label am-text-left"></label>
                                </div>
                            </div>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">打印权限人员设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">选择人员 </label>
                                <div class="am-u-sm-9">
                                    <div class="am-u-sm-6">
                                        <select class="am-u-sm-5 am-field-error am-active" name="store[printer]" required="">
                                            <option value="">请选择...</option>
                                            <?php if (isset($allUser)): foreach ($allUser as $first): ?>
                                                <option value="<?= $first['id'] ?>" <?php if($print_info['relation_2'] == $first['id']): ?>selected<?php endif; ?> ><?= $first['real_name'] ?></option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                    <label class="am-u-sm-6 am-form-label am-text-left"></label>
                                    <div class="help-block am-u-sm-12">
                                        <small>设置的人员登录门店后台可自动打印的必要条件之一</small>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="store[old_percent]" value="<?=isset($list['relation_1'][STORE_ID]) ? $list['relation_1'][STORE_ID] : 0?>">

                            <input type="hidden" name="store[printer_id]" value="<?= $print_info['id'] ?>">
                            <input type="hidden" name="store[old_printer]" value="<?= $print_info['relation_2'] ?>">

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
