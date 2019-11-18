<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加店员</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">用户名 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="user[user_name]" value="" placeholder="请输入用户名" required>
                                </div>
                            </div>
                            <?php if(IS_ADMIN){?>
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 所属门店 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <select name="user[store_id]" data-am-selected="{ btnSize: 'sm', placeholder:'请选择', maxHeight: 400}" id="j-store" required>
                                            <option value=""></option>
                                            <?php if (isset($shopList) && $shopList):
                                                foreach ($shopList as $item): ?>
                                                    <option value="<?= $item['id'] ?>"><?= $item['store_name'] ?></option>
                                                <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                </div>
                            <?php }?>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">所属角色 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select id="role_id" name="user[role_id][]" multiple data-am-selected="{btnSize: 'sm'}" required>
                                        <?php if (isset($roleList) && !T_ADMIN): foreach ($roleList as $role): ?>
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
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">绑定会员手机号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="user[user_phone]" value="">
                                    <div class="help-block">
                                        <small>注：请填写自己用于推广会员的手机号码，用于统计推广会员等；谨慎填写，绑定成功不能更改</small>
                                    </div>
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
                                    <?php if(!IS_ADMIN){?>
                                    <input type="hidden" value="<?=STORE_ID?>" name="user[store_id]">
                                   <?php }?>
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

        $("#j-store").on('change',function () {
            var store_id = $(this).val();
            if(store_id > 0){
                $.post("<?=url('shop.store_user/index')?>",{store_id:store_id,get_role_list:1},function (res) {
                    $('#role_id').empty();
                    addItem($('#role_id'), res.data);
                },'JSON')
            }
        });

        function addItem(obj,item){
            var _html = '';
            $.each(item,function (k,v) {
                _html += "<option value='"+v.role_id+"'>"+v.role_name_h1+"</option>";
            })
            obj.append(_html);
        }
    });

</script>
