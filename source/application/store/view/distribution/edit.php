<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑分销人员【<?= $model['real_name'] ?>】</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-u-lg-2 am-form-label form-require">所属门店 </label>
                                <div class="am-u-sm-10">
                                    <div class="am-u-sm-6">
                                        <select name="fx[store_id]" data-am-selected="{btnWidth: 400, searchBox: 1, btnSize: 'sm'}" required>
                                            <option value="0">请选择所属门店</option>
                                            <?php if (isset($storeList)): foreach ($storeList as $first): ?>
                                                <option value="<?= $first['id'] ?>"
                                                    <?= $model['store_id'] == $first['id'] ? 'selected' : '' ?>>
                                                    <?= $first['store_name'] ?></option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <?php if (isset($model) && $model['level'] == 3): ?>
                                <div class="am-form-group">
                                    <label class="am-u-sm-2 am-form-label form-require">下单优惠比例 </label>
                                    <div class="am-u-sm-10">
                                        <div class="am-u-sm-6">
                                            <input type="number" class="tpl-form-input" name="fx[discount]" value="<?= $model['discount'] ?>" required>
                                        </div>
                                        <div class="help-block am-u-sm-12">
                                            <small></small>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

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
