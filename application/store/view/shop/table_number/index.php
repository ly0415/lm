<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">桌号列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-3">
                                <div class="am-form-group">
                                    <?php if (checkPrivilege('shop.table_number/add')): ?>
                                        <a class="am-btn am-btn-success am-btn-xs"  type="type" data-am-modal="{target: '#doc-modal-1'}"><span class="am-icon-plus"></span>添加</a>
                                    <?php endif; ?>
                                </div>
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
                                        <form id="my-form1" class="am-form tpl-form-line-form" method="post" action="<?=url('shop.table_number/add')?>">
<!--                                            <div class="am-form-group am-margin-top-lg">-->
<!--                                                <label class="am-u-sm-4 am-text-right" style="padding:0;font-weight:500;margin:9px 0 0 0;"> 金额： </label>-->
<!--                                                <div class="am-u-sm-6 am-u-end">-->
<!--                                                    <input type="text" name="money" value="" placeholder="金额大于0" class="tpl-form-input" style="border:0;border-bottom: 1px solid #d6d6d6;padding: 6px 5px;outline:none;" required>-->
<!--                                                </div>-->
<!--                                            </div>-->
                                            <div class="am-form-group am-margin-top am-margin-bottom-xl">
                                                <label class="am-u-sm-4 am-text-right" style="padding:0;font-weight:500;margin:9px 0 0 0;"> 桌号： </label>
                                                <div class="am-u-sm-6 am-u-end">
                                                    <input type="text" name="number" value="" placeholder="请添加桌号" class="tpl-form-input" style="border:0;border-bottom: 1px solid #d6d6d6;padding: 6px 5px;outline:none;" required>
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
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>桌号</th>
                                <th>添加人</th>
<!--                                <th>店铺</th>-->
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $k=>$item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $k+1 ?></td>
                                    <td class="am-text-middle"><?= $item['number'] ?></td>
                                    <td class="am-text-middle"><?= $item['real_name'] ?></td>
<!--                                    <td class="am-text-middle">--><?//= $item['store_name'] ?><!--</td>-->
                                    <td class="am-text-middle"><?= date('Y-m-d',$item['add_time']) ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('shop.table_number/delete')): ?>
                                                <a href="javascript:;" class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $item['id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="9" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="am-u-lg-12 am-cf">
                        <div class="am-fr"><?= $list->render() ?> </div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录：<?= $list->total() ?></div>
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
        var url = "<?= url('shop.table_number/delete') ?>";
        $('.item-delete').delete('id', url, '删除后不可恢复，确定要删除吗？');

    });
</script>

