<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">余额充值券</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form id="form-search" class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-3">
                                <div class="am-form-group">
                                    <div class="am-btn-toolbar">
                                        <div class="am-btn-group am-btn-group-xs">
                                            <?php if (checkPrivilege('balance.balance_coupon/export')): ?>
                                                <a class="j-export am-btn am-btn-success am-radius"
                                                   href="javascript:void(0);">
                                                    <i class="iconfont icon-daochu am-margin-right-xs"></i>数据导出
                                                </a>
                                            <?php endif; ?>
                                            <a class="am-btn am-btn-success am-btn-xs"  type="type" data-am-modal="{target: '#doc-modal-1'}"><span class="am-icon-plus"></span>添加</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-u-sm-12 am-u-md-9">
                                <div class="am fr">
                                    <div class="am-form-group am-fl">
                                        <select name="is_use" id=""  data-province data-am-selected="{btnSize: 'sm',btnWidth:150, placeholder: ''}">
                                            <option value="-1">请选择状态</option>
                                            <option value="1" <?= $list['is_use']==1?'selected':''?>>未使用</option>
                                            <option value="2" <?= $list['is_use']==2?'selected':''?>>已使用</option>
                                            <option value="3" <?= $list['is_use']==3?'selected':''?>>已删除</option>
                                        </select>
                                    </div>

                                    <div class="am-form-group am-fl">
                                        <select name="store_id" id="province"  data-province data-am-selected="{btnSize: 'sm',btnWidth:150, placeholder: '所属门店'}">
                                            <option value="-1">请选择门店</option>
                                            <?php if ($storelist): foreach ($storelist as $val):  ?>
                                                <option value="<?= $val['id']?>" <?= $val['id'] == $list['store_id'] ? 'selected' : '' ?> ><?= $val['store_name']?></option>
                                            <?php endforeach;endif;?>
                                        </select>
                                    </div>

                                    <div class="am-form-group am-fl">
                                        <select name="service_user_id"  data-city id="city" data-am-selected="{btnSize: 'sm',btnWidth:150, placeholder: '请选择店员'}">
                                            <option value="-1">请选择店员</option>
                                            <?php if ($userlist): foreach ($userlist as $val):  ?>
                                                <option   value="<?= $val['id']?>" <?= $val['id'] == $list['service_user_id'] ? 'selected' : '' ?> ><?= $val['real_name']?></option>
                                            <?php endforeach;endif;?>
                                        </select>
                                    </div>

                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input autocomplete="off" type="text" name="add_time" class="am-form-field" value="<?= $request->get('add_time') ?>" placeholder="发送开始时间" data-am-datepicker>
                                    </div>

                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input autocomplete="off" type="text" name="end_time" class="am-form-field" value="<?= $request->get('end_time') ?>" placeholder="发送结束时间" data-am-datepicker>
                                        </div>
                                    </div>

                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form" style="width:192px;">
                                            <input type="text" class="am-form-field" name="sn" placeholder="请输入券码" value="<?= $request->get('sn') ?>" style="width:150px;">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search" type="submit"></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
<!--添加弹框-->
                    <div class="am-modal am-modal-no-btn" tabindex="-1" id="doc-modal-1">
                        <div class="am-modal-dialog" style="background-color: #fff;width:450px">
                            <div class="am-modal-hd" style="padding:0 10px;">
                                <div class="widget-head am-cf" style="margin:0;">
                                    <div class="widget-title am-text-left">添加</div>
                                </div>
                                <a href="javascript: void(0)" class="am-close am-close-spin" data-am-modal-close>&times;</a>
                            </div>
                            <div class="am-modal-bd" style="height:180px;">
                                <form id="my-form1" class="am-form tpl-form-line-form" method="post" action="<?=url('balance.balance_coupon/add')?>">
                                    <div class="am-form-group am-margin-top-lg">
                                        <label class="am-u-sm-4 am-text-right" style="padding:0;font-weight:500;margin:9px 0 0 0;"> 金额： </label>
                                        <div class="am-u-sm-6 am-u-end">
                                            <input type="text" name="money" value="" placeholder="金额大于0" class="tpl-form-input" style="border:0;border-bottom: 1px solid #d6d6d6;padding: 6px 5px;outline:none;" required>
                                        </div>
                                    </div>
                                    <div class="am-form-group am-margin-top am-margin-bottom-xl">
                                        <label class="am-u-sm-4 am-text-right" style="padding:0;font-weight:500;margin:9px 0 0 0;"> 条数： </label>
                                        <div class="am-u-sm-6 am-u-end">
                                            <input type="text" name="number" value="" placeholder="每次可添加1-99" class="tpl-form-input" style="border:0;border-bottom: 1px solid #d6d6d6;padding: 6px 5px;outline:none;" required>
                                        </div>
                                    </div>
                                    <div style="border-top:1px solid #eee;padding-top:10px;text-align:right">
                                        <button type="submit" class="am-btn am-btn-secondary am-btn-xs">保存</button>
                                        <button type="button" class="am-btn am-btn-secondary am-btn-xs" data-am-modal-close>取消</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
<!--  指派弹框 -->
                    <div class="am-modal am-modal-no-btn" tabindex="-1" id="doc-modal-2">
                        <div class="am-modal-dialog" style="background-color: #fff;width:500px">
                            <div class="am-modal-hd" style="padding:0 10px;">
                                <div class="widget-head am-cf" style="margin:0;">
                                    <div class="widget-title am-text-left">指派</div>
                                </div>
                                <a href="javascript: void(0)" class="am-close am-close-spin" data-am-modal-close>&times;</a>
                            </div>
                            <div class="am-modal-bd" style="min-height:100px;">
                                <form id="my-form2" class="am-form tpl-form-line-form" method="post" action="<?=url('balance.balance_coupon/designate')?>">
                                    <div class="am-form-group am-margin-top-lg am-margin-bottom">
                                        <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 店员 </label>
                                        <div class="am-u-sm-9 am-u-end">
                                            <div class="x-region-select am-text-left" >
                                                <select name="store_id1" id="province1"  data-province data-am-selected="{btnSize: 'sm',btnWidth:150, placeholder: '所属门店'}">
                                                    <option value="-1">请选择门店</option>
                                                    <?php if ($storelist): foreach ($storelist as $val):  ?>
                                                        <option value="<?= $val['id']?>"  ><?= $val['store_name']?></option>
                                                    <?php endforeach;endif;?>
                                                </select>
                                                <select name="service_user_id1"  data-city id="city1" data-am-selected="{btnSize: 'sm',btnWidth:150, placeholder: '请选择店员'}">
                                                    <option value="-1">请选择店员</option>
                                                    <?php if ($userlist): foreach ($userlist as $val):  ?>
                                                        <option   value="<?= $val['id']?>"  ><?= $val['real_name']?></option>
                                                    <?php endforeach;endif;?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="border-top:1px solid #eee;padding-top:10px;text-align:right">
                                        <input type="hidden" name="designate_type" value="">
                                        <input type="hidden" name="_type" value="">
                                        <button type="submit" class="am-btn am-btn-secondary am-btn-xs">保存</button>
                                        <button type="button" class="am-btn am-btn-secondary am-btn-xs" data-am-modal-close>取消</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th><input type="checkbox" name="itemsall" id="check_all"></th>
                                <th>序号</th>
                                <th>券码</th>
                                <th>金额</th>
                                <th>状态</th>
                                <th>使用来源</th>
                                <th>用户名</th>
                                <th>手机号</th>
                                <th>指派人员</th>
                                <th>操作人员</th>
                                <th>操作时间</th>
                                <th>充值时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php   if (!$list['data']->isEmpty()): foreach ($list['data'] as $k=>$item):  ?>
                                <tr>
                                    <td class="am-text-middle"><input type="checkbox" name="items" class="check_item" value="<?=$item['id']?>"></td>
                                    <td class="am-text-middle"><?=$k+1?></td>
<!--                                    <td class="am-text-middle">--><?//=$item['id']?><!--</td>-->
                                    <td class="am-text-middle"><?=$item['sn']?></td>
                                    <td class="am-text-middle"><?=$item['money']?></td>
                                    <td class="am-text-middle"><?= ($item['is_use']==1)?'未使用':'已使用'?></td>
                                    <td class="am-text-middle"><?php if($item['use_source']==1){ echo '公众号'; }elseif($item['use_source']==2){echo '小程序';}else{echo '---';} ?></td>
                                    <td class="am-text-middle"><?= !empty($item['username'])?$item['username']:'---'?></td>
                                    <td class="am-text-middle"><?= !empty($item['phone'])?$item['phone']:'---'?></td>
                                    <td class="am-text-middle"><?= !empty($item['store_user_name'])?$item['store_user_name']:'---'?></td>
                                    <td class="am-text-middle"><?= !empty($item['account_name'])?$item['account_name']:'---'?></td>
                                    <td class="am-text-middle"><?= !empty($item['add_time'])?date('Y-m-d H:i',$item['add_time']):'---'?></td>
                                    <td class="am-text-middle"><?= !empty($item['use_time'])?date('Y-m-d H:i',$item['use_time']):'---'?></td>
                                    <td class="am-text-middle"><div class="tpl-table-black-operation">
                                            <div class="tpl-table-black-operation">
                                                <?php if(!empty($item['is_use']) && $item['is_use']==1): ?>
                                                <?php if (checkPrivilege('balance.balance_coupon/designate')): ?>
                                                    <a class="am-btn am-btn-default am-btn-xs adesignate"  data-id="<?= $item['id'] ?>" type="type" data-am-modal="{target: '#doc-modal-2'}"><i class="am-icon-eye"></i> 指派</a>
                                                <?php endif; endif; ?>

                                                <?php if(!empty($item['mark']) && $item['mark']==1): ?>
                                                <?php if (checkPrivilege('balance.balance_coupon/delete')): ?>
                                                    <a href="javascript:;" class="item-delete tpl-table-black-operation-del"
                                                       data-id="<?= $item['id'] ?>">
                                                        <i class="am-icon-trash"></i> 删除
                                                    </a>
                                                <?php endif; endif; ?>
                                            </div>
                                    </td>

                                </tr>


                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="5" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>

                        </table>
                    </div>
                    <?php if (checkPrivilege('balance.balance_coupon/delete')): ?>
                        <a href="javascript:;" class="am-btn am-round am-btn-secondary am-btn-xs j-isgood"   >
                            <i class="am-icon-trash"></i> 批量删除
                        </a>
                    <?php endif; ?>

                    <?php if (checkPrivilege('balance.balance_coupon/designate')): ?>
                        <a href="javascript:;" class="am-btn am-round am-btn-secondary am-btn-xs j-isdesignate"  data-am-modal="{target: '#doc-modal-2'}" >
                            <i class="am-icon-eye"></i> 批量指派
                        </a>
                    <?php endif; ?>
                    <div class="am-u-lg-12 am-cf">
                        <div class="am-fr"><?= $list['data']->render() ?> </div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录：<?= $list['data']->total() ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {


        $('.adesignate').on('click',function(){
            var user_id = $(this).data('id');
            $("input[name='designate_type']").val(user_id);
            $("input[name='_type']").val(1);
        });

        $('.j-isdesignate').on('click',function(){
            var arr1=[];
            $("input[name='items']:checked").each(function() {
                arr1.push(this.value);// 将值加到数组里面
            });
            $("input[name='designate_type']").val(arr1);
            $("input[name='_type']").val(2);
        });

        $('#my-form1').superForm();


        $('#my-form2').superForm();
        // 删除元素
        var url = "<?= url('balance.balance_coupon/delete') ?>";
        $('.item-delete').delete('id', url, '删除后不可恢复，确定要删除吗？');
        // 批量删除元素

//        var url = "<?//= url('balance.balance_coupon/delete') ?>//";
//        $('.item-alldelete').delete('id', url, '删除后不可恢复，确定要删除吗？');

        $("#check_all").click(function () {//鼠标点击事件
            $(".check_item").prop("checked", $(this).prop("checked"))//所有类为check_item的属性打√
            //选中的时候返回true，否则为false
            //使得id为check_all的原生属性值与class为check_item的保持一致
        });

        /**
         * 订单导出
         */
        $('.j-export').click(function () {
            var data = {};
            var formData = $('#form-search').serializeArray();
            $.each(formData, function () {
                this.name !== 's' && (data[this.name] = this.value);
            });
            window.location = "<?= url('store/balance.balance_coupon/export') ?>" + '&' + $.urlEncode(data);
        });
        //选择商品



    });
</script>
<script>

    function addItem(obj,item){
        var _html = '';
        $.each(item,function (k,v) {
            _html += "<option    value='"+v.id+"'>"+v.real_name+"</option>";
        })
        obj.append(_html);
        obj.change();
    }

    $(function () {
        $("#province").on('change',function () {
            var province_id = $(this).val();
            var city = $("#city");
            var region = $("#region");
            var _html = "<option value='-1'>请选择店员</option>";
            city.html(_html);
            region.html(_html);
            if(province_id > 0){
                $.post("<?=url('api/user_statistics/getStoreUser')?>",{store_id:province_id},function (res) {
                    addItem(city,res);
                },'JSON')
            }
        });
    });
//弹框
    function addItem(obj,item){
        var _html = '';
        $.each(item,function (k,v) {
            _html += "<option    value='"+v.id+"'>"+v.real_name+"</option>";
        })
        obj.append(_html);
        obj.change();
    }

    $(function () {
        $("#province1").on('change',function () {
            var province_id = $(this).val();
            var city = $("#city1");
            var region = $("#region");
            var _html = "<option value='-1'>请选择店员</option>";
            city.html(_html);
            region.html(_html);
            if(province_id > 0){
                $.post("<?=url('api/user_statistics/getStoreUser')?>",{store_id:province_id},function (res) {
                    addItem(city,res);
                },'JSON')
            }
        });
    });
</script>

<!-- 批量删除元素-->

<script>
    $('.j-isgood').click(function () {
        var arr=[];
        $("input[name='items']:checked").each(function() {
            arr.push(this.value);// 将值加到数组里面
        });
        var data = $(this).data();
        layer.confirm('删除后不可恢复，确定要删除吗？', {
            btn: ['确定', '取消'] //按钮
        }, function () {
            $.ajax({
                type: 'get',
                url: "<?= url('balance.balance_coupon/delete') ?>",
                data:{id:arr},
                dataType: 'json',
                success: function (res) {
                    if (res.code) {
                        layer.msg(res.msg, {icon: 1, time: 1000});
                        setTimeout(function () {
                            window.location.reload();
                        }, 1000)
                    } else {
                        layer.msg(res.msg, {icon: 5})
                    }
                }
            })
        }, function () {
        });
    });

</script>




