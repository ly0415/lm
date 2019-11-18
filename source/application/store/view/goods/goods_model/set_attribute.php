<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">属性设置</div>
                </div>
                <div class="widget-body am-fr">
                    <form action=""  id="my-form" class="am-form am-form-horizontal tpl-form-line-form" method="post">
                        <div class="am-form-group">
                            <div class="am-form-group">
                                <div class="am-u-sm-2 am-text-right form-require">模型名称：</div>
                                <div class="am-u-sm-3 am-u-end">
<!--                                    <select name="goods_status" data-am-selected="{btnSize: 'sm', placeholder: ''}">-->
<!--                                        <option value="0">测试</option>-->
<!--                                    </select>-->
                                    <input type="text" class="tpl-form-input" readonly name="goods_model_attr[goods_model_id]"
                                           value="<?=$model['name']?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                 <div class="am-u-sm-2 am-text-right form-require">设置属性：</div>
                                 <div class="am-u-sm-10">
                                     <div class="am-form-group">
                                         <div class="am-u-sm-5"><input type="text" name="goods_model_attr[attr_name][]" required placeholder="属性名称"></div>
                                         <div class="am-u-sm-3"><input type="text" name="goods_model_attr[sort][]" value='100' placeholder="排序"></div>
                                         <div class="am-u-sm-2 am-u-end"> 
                                            <span class="add am-btn am-btn-default am-btn-xs am-btn-secondary">+</span>
                                            <span class="del am-btn am-btn-default am-btn-xs am-btn-secondary">-</span>
                                         </div>
                                     </div>
                                     <div class="am-form-group">
                                         <div class="am-u-sm-5"><input type="text" name="goods_model_attr[attr_name][]" placeholder="属性名称"></div>
                                         <div class="am-u-sm-3"><input type="text" name="goods_model_attr[sort][]" value="100" placeholder="排序"></div>
                                         <div class="am-u-sm-2 am-u-end"> 
                                            <span class="add am-btn am-btn-default am-btn-xs am-btn-secondary">+</span>
                                            <span class="del am-btn am-btn-default am-btn-xs am-btn-secondary">-</span>
                                         </div>
                                     </div>
                                     <div class="am-form-group">
                                         <div class="am-u-sm-5"><input type="text" name="goods_model_attr[attr_name][]" placeholder="属性名称"></div>
                                         <div class="am-u-sm-3"><input type="text" name="goods_model_attr[sort][]" value="100" placeholder="排序"></div>
                                         <div class="am-u-sm-2 am-u-end"> 
                                            <span class="add am-btn am-btn-default am-btn-xs am-btn-secondary">+</span>
                                            <span class="del am-btn am-btn-default am-btn-xs am-btn-secondary">-</span>
                                         </div>
                                     </div>
                                     <div class="am-form-group">
                                         <div class="am-u-sm-5"><input type="text" name="goods_model_attr[attr_name][]" placeholder="属性名称"></div>
                                         <div class="am-u-sm-3"><input type="text" name="goods_model_attr[sort][]" value="100" placeholder="排序"></div>
                                         <div class="am-u-sm-2 am-u-end"> 
                                            <span class="add am-btn am-btn-default am-btn-xs am-btn-secondary">+</span>
                                            <span class="del am-btn am-btn-default am-btn-xs am-btn-secondary">-</span>
                                         </div>
                                     </div>
                                     <div class="am-form-group">
                                         <div class="am-u-sm-5"><input type="text" name="goods_model_attr[attr_name][]" placeholder="属性名称"></div>
                                         <div class="am-u-sm-3"><input type="text" name="goods_model_attr[sort][]" value="100" placeholder="排序"></div>
                                         <div class="am-u-sm-2 am-u-end"> 
                                            <span class="add am-btn am-btn-default am-btn-xs am-btn-secondary">+</span>
                                            <span class="del am-btn am-btn-default am-btn-xs am-btn-secondary">-</span>
                                         </div>
                                     </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-5">
                                    <button type="submit" class="am-btn am-btn-primary am-radius">保存</button>
                                </div>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $('body').on('click','.add',function(){
        html = '';
        html+=`
        <div class="am-form-group">
            <div class="am-u-sm-5"><input name="goods_model_attr[attr_name][]" type="text" placeholder="属性名称"></div>
            <div class="am-u-sm-3"><input name="goods_model_attr[sort][]" type="text" value="100" placeholder="排序"></div>
            <div class="am-u-sm-2 am-u-end"> 
            <span class="add am-btn am-btn-default am-btn-xs am-btn-secondary">+</span>
            <span class="del am-btn am-btn-default am-btn-xs am-btn-secondary">-</span>
            </div>
        </div>
        `;
        $(this).parent().parent().after(html);
    });

    $('body').on('click','.del',function(){
        $(this).parent().parent().remove();
    });
    $(function () {
        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>

