<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑管理员</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 用户名 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="user[user_name]" value="<?= $model['user_name'] ?>" placeholder="请输入用户名" required>
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
                                                    <option <?php if($model['store_id'] == $item['id']) echo 'selected';?> value="<?= $item['id'] ?>"><?= $item['store_name'] ?></option>
                                                <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                </div>
                            <?php }?>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 所属角色 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select id="role_id" name="user[role_id][]" multiple data-am-selected="{btnSize: 'sm'}" required>
                                        <?php if (isset($roleList)): foreach ($roleList as $role): ?>
                                            <option value="<?= $role['role_id'] ?>"
                                                <?= in_array($role['role_id'], $model['roleIds']) ? 'selected' : '' ?>>
                                                <?= $role['role_name_h1'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>注：支持多选</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 登录密码 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="password" class="tpl-form-input" name="user[password]" value="" placeholder="请输入登录密码">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 确认密码 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="password" class="tpl-form-input" name="user[password_confirm]" value="" placeholder="请输入确认密码">
                                </div>
                            </div>
                            <?php if(empty($model['user_id'])){?>
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-u-lg-2 am-form-label">绑定会员手机号 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <input type="text" class="tpl-form-input" name="user[user_phone]" value="">
                                        <div class="help-block">
                                            <small>注：请填写自己用于推广会员的手机号码，用于统计推广会员等；谨慎填写，绑定成功不能更改</small>
                                        </div>
                                    </div>
                                </div>
                            <?php }?>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">姓名 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="user[real_name]" value="<?= $model['real_name'] ?>">
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

    function getRole(store_id) {
        //获取选定的一级分类名称
        //根据一级分类查二级数据
        $.ajax({
            //取消异步，也就是必须完成上面才能走下面
            async:false,
            url:"<?php echo url('shop.user/getRoleList');?>",
            data:{store_id:store_id},
            type:"get",
            success: function(data){
                console.log(data);
                $("#role_id").html(data);
            }
        });
    }
</script>
