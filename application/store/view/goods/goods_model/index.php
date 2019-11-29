<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">商品模型</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                                <?php if (checkPrivilege('goods.goods_model/add')): ?>
                                    <div class="am-btn-group am-btn-group-xs">
                                    	<!-- <?= url('goods.goods_model/add') ?> -->
                                        <a class="am-btn am-btn-default am-btn-success am-radius" href="" onclick="return false" data-am-modal="{target: '#doc-modal-1', closeViaDimmer: 0, width: 800, height: 350}">
                                            <span class="am-icon-plus"></span> 新增
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="am-modal am-modal-no-btn" tabindex="-1" id="doc-modal-1">
                        <div class="am-modal-dialog">
                            <div class="am-modal-hd">
                                <div class="widget-head am-cf">
                                    <div class="widget-title am-fl">添加商品模型</div>
                                </div>
                                <a href="javascript: void(0)" class="am-close am-close-spin" data-am-modal-close>&times;</a>
                            </div>
                            <div class="am-modal-bd">
                                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                                    <div class="widget-body">
                                        <!-- <fieldset> -->
                                            <div class="am-form-group">
                                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 商品模型: </label>
                                                <div class="am-u-sm-4 am-u-end">
                                                    <input type="text" class="tpl-form-input" name="shop[shop_name]"
                                                        placeholder="请输入商品模型名称" required>
                                                </div>
                                            </div>
                                            <div class="am-form-group">
                                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 请选择分类: </label>
                                                <div class="x-region-select am-u-sm-7 am-u-end">
                                                    <select name="shop[province_id]" required>
                                                        <option value="">请选择分类</option>
                                                    </select>
                                                    <select name="shop[city_id]" required>
                                                        <option value="">请选择分类</option>
                                                    </select>
                                                    <select name="shop[region_id]" required>
                                                        <option value="">请选择分类</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="am-form-group">
                                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 排序: </label>
                                                <div class="am-u-sm-4 am-u-end">
                                                    <input type="text" class="tpl-form-input" name="shop[shop_name]" required>
                                                </div>
                                            </div>
                                            <div class="am-form-group">
                                                <div class="am-u-sm-6 am-u-sm-push-3 am-margin-top-lg">
                                                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交
                                                    </button>
                                                </div>
                                            </div>
                                        <!-- </fieldset> -->
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black ">
                            <thead>
                            <tr>
                                <th>模型ID</th>
                                <th>模型名称</th>
                                <th>模型排序</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($list)): foreach ($list as $first): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $first['id'] ?></td>
                                    <td class="am-text-middle"><?= $first['name'] ?></td>
                                    <td class="am-text-middle"><?= $first['sort'] ?></td>
                                    <td class="am-text-middle"><?= $first['create_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('goods.goods_category/edit')): ?>
                                                <a href="<?= url('goods.goods_category/edit',
                                                    ['id' => $first['id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('goods.goods_category/delete')): ?>
                                                <a href="javascript:;" class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $first['id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php if (isset($first['child'])): foreach ($first['child'] as $two): ?>
                                    <tr>
                                        <td class="am-text-middle"><?= $two['id'] ?></td>
                                        <td class="am-text-middle">　-- <?= $two['name'] ?></td>
                                        <td class="am-text-middle"><?= $two['sort'] ?></td>
                                        <td class="am-text-middle"><?= $two['create_time'] ?></td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if (checkPrivilege('goods.goods_category/edit')): ?>
                                                    <a href="<?= url('goods.goods_category/edit',
                                                        ['id' => $two['id']]) ?>">
                                                        <i class="am-icon-pencil"></i> 编辑
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (checkPrivilege('goods.goods_category/delete')): ?>
                                                    <a href="javascript:;"
                                                       class="item-delete tpl-table-black-operation-del"
                                                       data-id="<?= $two['id'] ?>">
                                                        <i class="am-icon-trash"></i> 删除
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php if (isset($two['child'])): foreach ($two['child'] as $three): ?>
                                        <tr>
                                            <td class="am-text-middle"><?= $three['id'] ?></td>
                                            <td class="am-text-middle">　　　-- <?= $three['name'] ?></td>
                                        <td class="am-text-middle"><?= $two['sort'] ?></td>
                                            <td class="am-text-middle"><?= $three['create_time'] ?></td>
                                            <td class="am-text-middle">
                                                <div class="tpl-table-black-operation">
                                                    <?php if (checkPrivilege('goods.goods_category/edit')): ?>
                                                        <a href="<?= url('goods.goods_category/edit',
                                                            ['category_id' => $three['id']]) ?>">
                                                            <i class="am-icon-pencil"></i> 编辑
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if (checkPrivilege('goods.goods_category/delete')): ?>
                                                        <a href="javascript:;"
                                                           class="item-delete tpl-table-black-operation-del"
                                                           data-id="<?= $three['id'] ?>">
                                                            <i class="am-icon-trash"></i> 删除
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                <?php endforeach; endif; ?>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="5" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        // 删除元素
        var url = "<?= url('goods.goods_category/delete') ?>";
        $('.item-delete').delete('id', url);

    });
</script>

