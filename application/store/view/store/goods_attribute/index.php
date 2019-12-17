<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">商品属性</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-3">
                                <div class="am-form-group">
                                    <?php if (checkPrivilege('store.goods_attribute/add')): ?>
                                        <div class="am-btn-group am-btn-group-xs">
                                            <a class="am-btn am-btn-default am-btn-success"
                                               href="<?= url('store.goods_attribute/add') ?>">
                                                <span class="am-icon-plus"></span> 新增
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="am-u-sm-12 am-u-md-9">
                                <div class="am fr">
                                    <div class="am-form-group am-fl">
                                        <?php $category_id = $request->get('model_id') ?: null; ?>
                                        <select name="model_id1"
                                                data-am-selected="{searchBox: 1, btnSize: 'sm',  placeholder: '商品模型', maxHeight: 400}">
                                            <option value=""></option>
                                            <option value="0">全部</option>
                                            <?php if (isset($category)): foreach ($category as $first): ?>
                                                <option value="<?= $first['id'] ?>"
                                                    <?= $models == $first['id'] ? 'selected' : '' ?>>
                                                    <?= $first['name'] ?></option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>

                                    <input type="hidden" name="type" value="1">
                                    <div class="am-input-group-btn am-fl">
                                        <button class="am-btn am-btn-default" style="width:44px;height:32px;border:1px solid #ccc;padding:7px 14px;background:white;display:flex;" type="submit">
                                            <i style="font-size:14px;" class="am-icon-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black ">
                            <thead>
                            <tr>
                                <th><input type="checkbox" name="itemsall" id="check_all"></th>
                                <th>属性ID</th>
                                <th>属性名称</th>
                                <th>所属模型</th>
                                <th>排序</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($list)): foreach ($list as $first): ?>
                                <tr>
                                    <td class="am-text-middle"><input type="checkbox" name="items" class="check_item" value="<?=$first['attr_id']?>"></td>
                                    <td class="am-text-middle"><?= $first['attr_id'] ?></td>
                                    <td class="am-text-middle"><?= $first['attr_name'] ?></td>
                                    <td class="am-text-middle"><?= $first['goods_model']['name'] ?></td>
                                    <td class="am-text-middle"><?= $first['order'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('store.goods_attribute/edit')): ?>
                                                <a href="<?= url('store.goods_attribute/edit',
                                                    ['id' => $first['attr_id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('store.goods_attribute/delete')): ?>
                                                <a href="javascript:;" class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $first['attr_id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
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

                    <?php if (checkPrivilege('store.goods_attribute/delete')): ?>
                        <a href="javascript:;" class="am-btn am-round am-btn-secondary am-btn-xs j-isgood"   >
                            <i class="am-icon-trash"></i> 批量删除
                        </a>
                    <?php endif; ?>
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
        // 删除元素
        var url = "<?= url('store.goods_attribute/delete') ?>";
        $('.item-delete').delete('attr_id', url);

        $("#check_all").click(function () {//鼠标点击事件
            $(".check_item").prop("checked", $(this).prop("checked"))//所有类为check_item的属性打√
            //选中的时候返回true，否则为false
            //使得id为check_all的原生属性值与class为check_item的保持一致
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
                url: "<?= url('store.goods_attribute/delete') ?>",
                data:{attr_id:arr},
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
