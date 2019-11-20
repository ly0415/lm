<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">出售中的商品</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-3">
                                <div class="am-form-group">
                                    <?php if (checkPrivilege('goods/add')): ?>
                                        <div class="am-btn-group am-btn-group-xs">
                                            <a class="j-selectUser am-btn am-btn-default am-btn-success"
                                               href="javascript:void(0)">
                                                <span class="am-icon-plus"></span> 普通商品
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (checkPrivilege('goods/joint')): ?>
                                        <div class="am-btn-group am-btn-group-xs">
                                            <?php if(IS_ADMIN):?>
                                            <a class=" am-btn am-btn-default am-btn-success"
                                               href="<?=url('goods/joint')?>">
                                                <span class="am-icon-plus"></span> 组合商品
                                            </a>
                                            <?php else:?>
                                                <a class="j-selectGoods am-btn am-btn-default am-btn-success"
                                                   href="javascript:void(0)">
                                                    <span class="am-icon-plus"></span> 组合商品
                                                </a>
                                            <?php endif;?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (checkPrivilege('goods/bcode')): ?>
                                        <div class="am-btn-group am-btn-group-xs">
                                            <a class="am-btn  am-btn-default am-btn-success" id="bcode"
                                               href="javascript:void(0);">
                                                <span> </span> 导出条码
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="am-u-sm-12 am-u-md-9">
                                <div class="am fr">
                                    <?php if (isset($business) && $business): ?>
                                        <div class="am-form-group am-fl">
                                            <?php $business_id = $request->get('business_id') ?: null; ?>
                                            <select name="business_id" data-am-selected="{searchBox: 1, btnSize: 'sm',  placeholder: '业务类型', maxHeight: 400}">
                                                <option value="0">请选择业务类型</option>
                                                <?php  foreach ($business as $first): ?>
                                                    <option value="<?= $first['id'] ?>"
                                                        <?= $business_id == $first['id'] ? 'selected' : '' ?>>
                                                        <?= $first['name'] ?>
                                                    </option>
                                                <?php endforeach;  ?>
                                            </select>
                                        </div>
                                    <?php endif; ?>
                                    <div class="am-form-group am-fl">
                                        <?php $category_id = $request->get('category_id') ?: null; ?>
                                        <select name="category_id" data-am-selected="{searchBox: 1, btnSize: 'sm',  placeholder: '商品分类', maxHeight: 400}">
                                            <option value="0">请选择商品分类</option>
                                            <?php if (isset($category)): foreach ($category as $first): ?>
                                                <option value="<?= $first['id'] ?>"
                                                    <?= $category_id == $first['id'] ? 'selected' : '' ?>>
                                                    <?= $first['name'] ?></option>
                                                <?php if (isset($first['child'])): foreach ($first['child'] as $two): ?>
                                                    <option value="<?= $two['id'] ?>"
                                                        <?= $category_id == $two['id'] ? 'selected' : '' ?>>
                                                        　　<?= $two['name'] ?></option>
                                                    <?php if (isset($two['child'])): foreach ($two['child'] as $three): ?>
                                                        <option value="<?= $three['id'] ?>"
                                                            <?= $category_id == $three['id'] ? 'selected' : '' ?>>
                                                            　　　<?= $three['name'] ?></option>
                                                    <?php endforeach; endif; ?>
                                                <?php endforeach; endif; ?>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <?php $goods_status = $request->get('goods_status') ?: null; ?>
                                        <select name="goods_status" data-am-selected="{btnSize: 'sm', placeholder: '商品状态'}">
                                            <option value="0">商品状态</option>
                                            <option value="1" <?= $goods_status == 1 ? 'selected' : '' ?>>上架</option>
                                            <option value="2" <?= $goods_status == 2 ? 'selected' : '' ?>>下架</option>
                                        </select>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <?php $order_sn = $request->get('goods_sn') ?: null; ?>
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="goods_sn" placeholder="请输入商品型号" value="<?= $request->get('goods_sn') ?>">
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="goods_name" placeholder="请输入商品名称" value="<?= $request->get('goods_name') ?>">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search" type="submit"></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>商品ID</th>
                                <th>商品图片</th>
                                <th>商品名称</th>
                                <th>商品型号</th>
                                <th>业务类型</th>
                                <th>商品分类</th>
                                <th>商品排序</th>
                                <th>商品状态</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <input type="hidden" name="store_goods_id[]" value="<?=$item['id']?>">
                                    <td class="am-text-middle"><?= $item['goods_id'] ?></td>
                                    <td class="am-text-middle">
                                        <a href="http://www.lmeri.com/<?= $item['original_img'] ?>" title="点击查看大图" target="_blank">
                                            <img src="http://www.lmeri.com/<?= $item['original_img'] ?>" width="50" height="50" alt="商品图片">
                                        </a>
                                    </td>
                                    <td class="am-text-middle">
                                        <p class="item-title"><?= $item['goods_name'] ?></p>
                                    </td>
                                    <td class="am-text-middle">
                                        <p class="item-title"><?= $item['goods_sn'] ?></p>
                                    </td>
                                    <td class="am-text-middle">
                                        <?= $item['format_business_name'] ?><br>
                                        <?= $item['format_auxiliarys'] ?>
                                    </td>
                                    <td class="am-text-middle"><?= isset($item['format_category'][0])?$item['format_category'][0]:''; ?></td>
                                    <td class="am-text-middle"><?= $item['sort'] ?></td>
                                    <td class="am-text-middle">
                                        <span class="<?php if( checkPrivilege('goods/on')):?>j-state<?php endif;?> am-badge x-cur-p am-badge-<?= $item['is_on_sale']['value'] == 1 ? 'success' : 'warning' ?>" data-id="<?= $item['id'] ?>" data-state="<?= $item['is_on_sale']['value'] ?>">
                                            <?= $item['is_on_sale']['text'] ?>
                                        </span>
                                    </td>
                                    <td class="am-text-middle"><?= $item['add_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('goods/edit')): ?>
                                                <a href="<?= url('goods/edit', ['goods_id' => $item['id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
<!--                                            --><?php //if (checkPrivilege('goods/delete')): ?>
<!--                                                <a href="javascript:;" class="item-delete tpl-table-black-operation-del" data-id="--><?//= $item['id'] ?><!--">-->
<!--                                                    <i class="am-icon-trash"></i> 删除-->
<!--                                                </a>-->
<!--                                            --><?php //endif; ?>
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
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>
    $(function () {

        $('#bcode').on('click',function () {
            var store_goods = [];
            $.each($("input[name='store_goods_id[]']"),function (k,v) {
                store_goods.push($(v).val());
            });
            if(!store_goods)return false;
            window.location.href = "index.php?s=/store/goods/bcode/store_goods_id/"+store_goods.join(',')+"/test/666";
        });


        // 商品状态
        $('.j-state').click(function () {
            var data = $(this).data();
            layer.confirm('确定要' + (parseInt(data.state) === 1 ? '下架' : '上架') + '该商品吗？'
                , {title: '友情提示'}
                , function (index) {
                    $.post("<?= url('goods/on') ?>"
                        , {
                            goods_id: data.id,
                            state: Number(!(parseInt(data.state) === 1))
                        }
                        , function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        });
                    layer.close(index);
                });

        });

        //选择商品
        $('.j-selectUser').click(function () {
            $.selectData({
                title: '选择商品',
                uri: "<?= url('data.goods/lists') ?>",
                dataIndex: 'goods_id',
                done: function (data) {
                    var list = {goods:data};
                    $.post("<?= url('goods/add') ?>", list, function (result) {
                        result.code === 1 ? $.show_success(result.msg, result.url) : $.show_error(result.msg);
                    });
                }
            });
        });

        // 删除元素
        var url = "<?= url('goods/delete') ?>";
        $('.item-delete').delete('goods_id', url);
    });
</script>

