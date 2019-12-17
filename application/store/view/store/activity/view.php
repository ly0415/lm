<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">所属活动列表---【<?= $list['name'] ?>】</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-3">

                            </div>
                            <div class="am-u-sm-12 am-u-md-9">
                                <div class="am fr">
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="hidden" name="activity_id" value="<?= $request->get('activity_id') ?>">
                                            <input type="text" class="am-form-field" name="username" placeholder="请输入用户名" value="<?= $request->get('username') ?>">
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="phone" placeholder="请输入手机号" value="<?= $request->get('phone') ?>">
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
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>序号</th>
                                <th>用户名</th>
                                <th>手机号</th>
                                <th>报名时间</th>
<!--                                <th>操作</th>-->
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$user->isEmpty()): foreach ($user as $key => $item): ?>
                                <tr>
                                    <td class="am-text-middle"><input type="checkbox" name="check-name" value="<?= $item['user_id'] ?>"></td>
                                    <td class="am-text-middle"><?= $item['username'] ?></td>
                                    <td class="am-text-middle"><?= $item['phone'] ?></td>
                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>
<!--                                    <td class="am-text-middle">-->
<!--                                        <div class="tpl-table-black-operation">-->
<!--                                            --><?php //if (checkPrivilege('store_activity_user/view')): ?>
<!--                                                <a class="js-edit-discount" href="javascript:;"  >-->
<!--                                                    <i class="am-icon-edit"></i> 查看-->
<!--                                                </a>-->
<!--                                            --><?php //endif; ?>
<!--                                        </div>-->
<!--                                    </td>-->
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
                        <div class="am-fr"><?= $user->render() ?> </div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录：<?= $user->total() ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

