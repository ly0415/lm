<link rel="stylesheet" href="assets/admin/css/set_attribute.css">
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div   class="am-tabs widget am-cf">
                <ul class="am-tabs-nav am-nav am-nav-tabs">
                    <?php $type = $request->param('type') ? : 1; ?>
                    <li class="<?php if($type == 1)echo 'am-active';?>"><a href="<?php echo  url('index',array('type'=>1))?>">抵扣卷</a></li>
                    <li class="<?php if($type == 2)echo 'am-active';?>"><a href="<?php echo  url('index',array('type'=>2))?>">兑换券</a></li>
                </ul>
                <div class="am-tabs-bd">
                    <?php if($type == 1):?>
                <div class="am-tab-panel am-active am-in">
                    <div class="widget am-cf">
                        <div class="widget-body am-fr">
                            <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                                <div class="am-form-group">
                                    <div class="am-btn-toolbar">
                                        <?php if (checkPrivilege('store.coupon/addDeduction')): ?>
                                            <div class="am-btn-group am-btn-group-xs">
                                                <a class="am-btn am-btn-default am-btn-success am-radius"
                                                   href="<?= url('store.coupon/addDeduction') ?>">
                                                    <span class="am-icon-plus"></span> 新增
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="am-u-sm-12 am-scrollable-horizontal">
                                <table width="100%"
                                       class="am-table am-table-compact am-table-striped tpl-table-black am-text-nowrap">
                                    <thead>
                                    <tr>
                                        <th>优惠券ID</th>
                                        <th>优惠券名称</th>
                                        <th>优惠券类型</th>
                                        <th>最低消费金额</th>
                                        <th>优惠方式</th>
                                        <th>有效期</th>
                                        <th>添加时间</th>
                                        <th>操作</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (!$list->isEmpty()): ?>
                                        <?php foreach ($list as $item): ?>
                                            <tr>
                                                <td class="am-text-middle"><?= $item['id'] ?></td>
                                                <td class="am-text-middle"><?= $item['coupon_name'] ?></td>
                                                <td class="am-text-middle"><?= $item['type']['text'] ?></td>
                                                <td class="am-text-middle"><?= $item['money'] ?></td>
                                                <td class="am-text-middle">
                                                    <?php if ($item['type']['value'] == 1) : ?>
                                                        <span>立减 <strong><?= $item['discount'] ?></strong> 元</span>
                                                    <?php elseif ($item['type']['value'] == 2) : ?>
                                                        <span>打 <strong><?= $item['discount'] ?></strong> 折</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="am-text-middle">
                                                    <span>领取 <strong><?= $item['limit_times'] ?></strong> 天内有效</span>

                                                </td>

                                                <td class="am-text-middle"><?= $item['add_time'] ?></td>
                                                <td class="am-text-middle">
                                                    <div class="tpl-table-black-operation">
                                                        <?php if (checkPrivilege('store.coupon/edit')): ?>
                                                            <a href="<?= url('store.coupon/edit', ['coupon_id' => $item['id']]) ?>">
                                                                <i class="am-icon-pencil"></i> 编辑
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if (checkPrivilege('store.coupon/delete')): ?>
                                                            <a href="javascript:void(0);"
                                                               class="item-delete tpl-table-black-operation-del"
                                                               data-id="<?= $item['id'] ?>">
                                                                <i class="am-icon-trash"></i> 删除
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="11" class="am-text-center">暂无记录</td>
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
                    <?php endif;?>

<!--            兑换券        -->
                    <?php if($type == 2):?>
                        <div class="am-tab-panel am-active am-in">
                            <div class="widget am-cf">
                                <div class="widget-body am-fr">
                                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                                        <div class="am-form-group">
                                            <div class="am-btn-toolbar">
                                                <?php if (checkPrivilege('store.coupon/addExchange')): ?>
                                                    <div class="am-btn-group am-btn-group-xs">
                                                        <a class="am-btn am-btn-default am-btn-success am-radius"
                                                           href="<?= url('store.coupon/addExchange') ?>">
                                                            <span class="am-icon-plus"></span> 新增
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="am-u-sm-12 am-scrollable-horizontal">
                                        <table width="100%"
                                               class="am-table am-table-compact am-table-striped tpl-table-black am-text-nowrap">
                                            <thead>
                                            <tr>
                                                <th>优惠券ID</th>
                                                <th>优惠券名称</th>
                                                <th>优惠券类型</th>
                                                <th>最低消费金额</th>
                                                <th>优惠方式</th>
                                                <th>有效期</th>
                                                <th>添加时间</th>
                                                <th>操作</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php if (!$list->isEmpty()): ?>
                                                <?php foreach ($list as $item): ?>
                                                    <tr>
                                                        <td class="am-text-middle"><?= $item['id'] ?></td>
                                                        <td class="am-text-middle"><?= $item['coupon_name'] ?></td>
                                                        <td class="am-text-middle"><?= $item['type']['text'] ?></td>
                                                        <td class="am-text-middle"><?= $item['money'] ?></td>
                                                        <td class="am-text-middle">
                                                            <?php if ($item['type']['value'] == 1) : ?>
                                                                <span>立减 <strong><?= $item['discount'] ?></strong> 元</span>
                                                            <?php elseif ($item['type']['value'] == 2) : ?>
                                                                <span>打 <strong><?= $item['discount'] ?></strong> 折</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="am-text-middle">
                                                            <span>领取 <strong><?= $item['limit_times'] ?></strong> 天内有效</span>

                                                        </td>

                                                        <td class="am-text-middle"><?= $item['add_time'] ?></td>
                                                        <td class="am-text-middle">
                                                            <div class="tpl-table-black-operation">
                                                                <?php if (checkPrivilege('store.coupon/edit')): ?>
                                                                    <a href="<?= url('store.coupon/edit', ['coupon_id' => $item['id']]) ?>">
                                                                        <i class="am-icon-pencil"></i> 编辑
                                                                    </a>
                                                                <?php endif; ?>
                                                                <?php if (checkPrivilege('store.coupon/delete')): ?>
                                                                    <a href="javascript:void(0);"
                                                                       class="item-delete tpl-table-black-operation-del"
                                                                       data-id="<?= $item['id'] ?>">
                                                                        <i class="am-icon-trash"></i> 删除
                                                                    </a>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="11" class="am-text-center">暂无记录</td>
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
                    <?php endif;?>

                </div>
            </div>
        </div>
    </div>
</div>
<!-- 图片文件列表模板 -->
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script>
    $(function () {
        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
<script>






</script>

