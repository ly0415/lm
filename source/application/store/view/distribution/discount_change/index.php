<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">优惠变更列表</div>
                </div>
                <form id="form-search" class="toolbar-form" action="">
                    <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                <div style="display:flex;justify-content:flex-end" class="am-margin-bottom">
<!--                    --><?php //$discountchange = $request->get('discountchange') ?: null; ?>
                    <select name="check" data-am-selected="{btnSize: 'sm', placeholder: '审核状态'}">
                        <option value="0">状态</option>
                        <option value="1" <?= $check == 1 ? 'selected' : '' ?>>审核中</option>
                        <option value="2" <?= $check == 2 ? 'selected' : '' ?>>通过</option>
                        <option value="3" <?= $check == 3 ? 'selected' : '' ?>>拒绝</option>
                    </select>
<!--                    <div class="am-input-group-btn">-->
                        <button class="am-btn am-btn-default am-icon-search" style='width:43px;height;17px;border:1px solid #c2cad8;padding:5px 14px;background-color:white;border-left:none;outline:none;margin-right:30px;' type="submit"></button>
<!--                    </div>-->
                </div>
                <div class="widget-body am-fr">
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>编号</th>
                                <th>用户名称</th>
                                <th>三级分销比例</th>
                                <th>原分销优惠</th>
                                <th>申请状态</th>
                                <th>申请时间</th>
                                <th>审核人</th>
                                <th>审核时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($discountchangeList)): foreach ($discountchangeList as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['id'] ?></td>
                                    <td class="am-text-middle"><?= $item['real_name'] ?></td>
                                    <td class="am-text-middle"><?= $item['fx_discount'] ?>%</td>
                                    <td class="am-text-middle"><?= $item['old_discount'] ?>%</td>
                                    <td class="am-text-middle"><?php if($item['is_check'] == 1): ?>
                                        <span class="am-badge am-badge-success">审核中</span>
                                        <?php elseif(($item['is_check'] == 2)): ?>
                                        <span class="am-badge am-badge-secondary">通过</span>
                                        <?php elseif(($item['is_check'] == 3)): ?>
                                        <span class="am-badge am-badge-blue">拒绝</span>
                                        <?php endif; ?>

                                    </td>

                                    <td class="am-text-middle"><?= date('Y-m-d',$item['add_time']) ?></td>
                                    <td class="am-text-middle"><?= $item['user_name'] ?></td>
                                    <td class="am-text-middle"><?= date('Y-m-d',$item['check_time']) ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if($item['is_check'] == 1): ?>
                                            <a href="javascript:;" class="<?php if( checkPrivilege('distribution.discount_change/edit')):?>j-state<?php endif;?>" data-id="<?= $item['id'] ?>" data-state="">
                                                    <i class="am-icon-pencil"></i>审核
                                                </a>
                                            <?php endif; ?>
                                        <?php if (checkPrivilege('distribution.discount_change/delete')): ?>
                                            <a href="javascript:;" class="item-delete tpl-table-black-operation-del"
                                               data-id="<?= $item['id'] ?>">
                                                <i class="am-icon-trash"></i> 删除
                                            </a>
                                        <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else:  ?>
                                <tr>
                                    <td colspan="10" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="am-u-lg-12 am-cf">
                        <div class="am-fr"><?= $discountchangeList->render() ?> </div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录：<?= $discountchangeList->total() ?></div>
                        </div>
                    </div>
                </div>
                </form>
            </div>
                


        </div>
    </div>
</div>

<script>
    // 审核状态
    $('.j-state').click(function () {
        var data = $(this).data();
        layer.confirm('确定要审核通过么？', {
            btn: ['通过', '拒绝'] //按钮
        }, function () {
            $.ajax({
                type: 'get',
                url: "<?= url('distribution.discount_change/edit') ?>",
                data:{id:data.id,status:2},
                dataType: 'json',
                success: function (res) {
                    if (res.code) {
                        layer.msg(res.msg, {icon: 1, time: 2000});
                        setTimeout(function () {
                            window.location.reload();
                        }, 1000)
                    } else {
                        layer.msg(res.msg, {icon: 5})
                    }
                }
            })
        }, function () { $.ajax({
            type: 'get',
            url: "<?= url('distribution.discount_change/edit') ?>",
            data:{id:data.id,status:3},
            dataType: 'json',
            success: function (res) {
                if (res.code) {
                    layer.msg(res.msg, {icon: 1, time: 2000});
                    setTimeout(function () {
                        window.location.reload();
                    }, 1000)
                } else {
                    layer.msg(res.msg, {icon: 5})
                }
            }
        })
        });
    });
    $(function () {

        // 删除元素
        var url = "<?= url('distribution.discount_change/delete') ?>";
        $('.item-delete').delete('id', url, '删除后不可恢复，确定要删除吗？');

    });
</script>

