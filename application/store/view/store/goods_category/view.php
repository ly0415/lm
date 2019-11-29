<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-body am-fr">
                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black ">
                            <thead>
                            <tr>
                                <th>业务ID</th>
                                <th>业务类型</th>
                                <th>业务排序</th>
                                <th>添加时间</th>
                            </tr>
                            </thead>
                            <tbody>
<!--                            一级业务分类-->
                            <?php if (!empty($business) && isset($business['first'])): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $business['first']['id'] ?></td>
                                    <td class="am-text-middle"><?=$business['first']['name'] ?></td>
                                    <td class="am-text-middle"><?= $business['first']['sort'] ?></td>
                                    <td class="am-text-middle"><?= $business['first']['create_time'] ?></td>

                                </tr>
<!--                            二级业务分类-->
                            <?php if (!empty($business) && isset($business['two'])):?>
                                <tr>
                                    <td class="am-text-middle"><?= $business['two']['id'] ?></td>
                                    <td class="am-text-middle">　-- <?= $business['two']['name'] ?></td>
                                    <td class="am-text-middle"><?= $business['two']['sort'] ?></td>
                                    <td class="am-text-middle"><?= $business['two']['create_time'] ?></td>
                                </tr>
                                <?php endif; ?>
                            <?php  else: ?>
                                <tr>
                                    <td colspan="5" class="am-text-center">暂无所属业务类型</td>
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

