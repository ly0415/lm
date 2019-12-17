<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl"><?=$model['name']?></div>
                            </div>
                            <input type="hidden" name="business_category[room_type_id]" value="<?=$model['id']?>">

                            <div class="am-form-group" id="category">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">所属商品分类 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select required name="business_category[b_pid_1]" id="b_pid_1" onchange="getCateList(this.id,'b_pid_2')"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm',
                                             placeholder:'请选择商品分类', maxHeight: 400}">>
                                        <option value=""></option>

                                        <?php if(isset($category)):foreach ($category as $first):?>
                                            <option value="<?=$first['id']?>"><?=$first['name']?></option>

                                        <?php endforeach;?>
                                        <?php endif;?>


                                    </select>
                                    <select required name="business_category[b_pid_2]" id="b_pid_2" onchange="getCateList(this.id,'b_pid_3')"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm',
                                             placeholder:'请选择商品分类', maxHeight: 400}">>
                                        <option value=""></option>


                                    </select>
                                    <select required name="business_category[category_id]" id="b_pid_3"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm',
                                             placeholder:'请选择商品分类', maxHeight: 400}">>
                                        <option value=""></option>

                                    </select>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">分类排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" class="tpl-form-input" name="business_category[sort]"
                                           value="100" required>
                                    <small>数字越小越靠前</small>
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



<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script>
    /**
     * 三级联动
     * @param id
     * @param tag
     */
    function getCateList(id, tag) {
        var d = $('#' + id).find('option:selected').val();
        if (d && parseInt(d) > 0) {
            var url = "<?=url('store.goods_category/get_category');?>";
            $.post(url,{parent_id:d} ,function (res) {
                var html = '';
                html += ' <option value=""></option>';
                $.each(res.data, function (i, n) {
                    html += '<option  value=' + n.id + ' >' + n.name + '</option>';
                });
                $('#' + tag).empty().html(html);

            }, 'json');

        } else {
            $('#' + tag).empty().html('<option value=""></option>');
            $('#b_pid_3').empty().html('<option value=""></option>');
        }

    }
    $(function () {

        $('#pid').change(function () {
          var pid = $(this).find('option:selected').val();
          if(parseInt(pid) > 0){
                $("#category").show();
          }else{
              $("#category").hide();
          }
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
