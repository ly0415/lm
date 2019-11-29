<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">店员列表</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form id="form-search" class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-3">
                                <div class="am-form-group">
                                    <div class="am-btn-toolbar">
                                        <div class="am-btn-group am-btn-group-xs">
                                            <?php if (checkPrivilege('shop.store_user/add')): ?>
                                                <div class="am-btn-group am-btn-group-xs">
                                                    <a class="am-btn am-btn-default am-btn-success"
                                                       href="<?= url('shop.store_user/add') ?>">
                                                        <span class="am-icon-plus"></span> 新增
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-u-sm-12 am-u-md-9">
                                <div class="am fr">
                                    <?php if(IS_ADMIN){?>
                                    <div class="am-form-group am-fl">
                                        <select name="store_id"
                                                data-am-selected="{btnSize: 'sm', placeholder: '选择门店'}">
                                            <option value=""></option>
                                            <option value="0"
                                                <?= $request->get('store_id') === '0' ? 'selected' : '' ?>>全部
                                            </option>
                                            <?php foreach ($shopList as $shop){?>
                                                <option  <?php if($request->get('store_id') == $shop['id']){echo 'selected';} ?> value="<?=$shop['id']?>"><?=$shop['store_name']?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                    <?php }?>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="user_name" placeholder="请输入用户名" value="<?= $request->get('user_name') ?>">
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
                                <th>管理员ID</th>
                                <th>用户名</th>
                                <th>姓名</th>
                                <?php if(IS_ADMIN){?>
                                <th>所属店铺</th>
                                <?php }?>
                                <th>所属角色</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['id'] ?></td>
                                    <td class="am-text-middle"><?= $item['user_name'] ?></td>
                                    <td class="am-text-middle"><?= $item['real_name'] ?></td>
                                    <?php if(IS_ADMIN){?>
                                        <td class="am-text-middle"><?= $item['store_name'] ?></td>
                                    <?php }?>
                                    <td class="am-text-middle"><?= $item['format_role_name'] ?></td>
                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (($store['user']['store_user_id'] != $item['id']) && !$item['is_super']):?>
                                                <?php if (checkPrivilege('shop.store_user/edit')): ?>
                                                    <a href="<?= url('shop.store_user/edit', ['user_id' => $item['id']]) ?>">
                                                        <i class="am-icon-pencil"></i> 编辑
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (checkPrivilege('shop.store_user/delete')): ?>
                                                    <a href="javascript:void(0);"
                                                       class="item-delete tpl-table-black-operation-del"
                                                       data-id="<?= $item['id'] ?>">
                                                        <i class="am-icon-trash"></i> 删除
                                                    </a>
                                                <?php endif; ?>
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
        var url = "<?= url('shop.store_user/delete') ?>";
        $('.item-delete').delete('user_id', url, '删除后不可恢复，确定要删除吗？');

    });
</script>

