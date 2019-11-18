<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加管理员</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">用户名 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="user[user_name]"
                                           value="" placeholder="请输入用户名" required>
                                </div>
                            </div>
                            <?php if(!BUSINESS_ID): ?>
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label">所属业务品牌</label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <select name="user[business_id]" data-am-selected="{btnSize: 'sm'}" id="j-business">
                                            <option value="0">请选择</option>
                                            <?php if (isset($model['business'])): foreach ($model['business'] as $value): ?>
                                                <option value="<?= $value['id'] ?>">
                                                    <?= $value['name'] ?></option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">所属角色 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="user[role_id][]" multiple data-am-selected="{btnSize: 'sm'}" id="business_id">
                                        <?php if (isset($roleList)): foreach ($roleList as $role): ?>
                                            <option value="<?= $role['role_id'] ?>"> <?= $role['role_name_h1'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>注：支持多选</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">登录密码 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="password" class="tpl-form-input" name="user[password]"
                                           value="" placeholder="请输入登录密码" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">确认密码 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="password" class="tpl-form-input" name="user[password_confirm]"
                                           value="" placeholder="请输入确认密码" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">姓名 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="user[real_name]"
                                           value="">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <input type="hidden" name="user[is_admin]" value="1" id="">
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

        $("#j-business").on('change',function () {
            var business_id = $(this).val();
            if(business_id > 0){
                $.post("<?=url('store.role/getRole')?>",{business_id:business_id,type:1},function (res) {
                    $('#business_id').empty();
                    addItem($('#business_id'), res.data);
                },'JSON')
            }
        });

        function addItem(obj,item){
            var _html = '';
            $.each(item,function (k,v) {
                _html += "<option value='"+v.role_id+"'>"+v.role_name_h1+"</option>";
            })
            console.log(_html)
            obj.append(_html);
        }

    });
</script>
