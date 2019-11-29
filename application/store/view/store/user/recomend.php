<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">推荐人员---【<?= $list['username'].'('.$list['phone'].')' ?>】</div>
                </div>
                <div class="widget-body am-fr">
                    <table class="am-table">
                        <thead>
                        <tr>
                            <th width="20%">直推我的</th>
                            <th>
                                <a style="margin-left: 5%" >我的直推(<?= count($list['r3']) ?>)</a><br><br>
                                <span style="margin-left: 5%;" >用户名称</span>
                                <span style="position:absolute; left: 45%;">手机号</span>
                                <span style="position:absolute; left: 60%;">睿积分</span>
                                <span style="position:absolute; left: 70%;">余额</span>
                                <span style="position:absolute; left: 80%;">消费次数</span>
                                <span style="position:absolute; left: 90%;">注册时间</span>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <?php if(!empty($list['r1'])): ?>
                                        <a href="<?= url('store.user/recomend', ['user_id' => $list['r1']['id']]) ?>"><?= $list['r1']['username'].'('.$list['r1']['phone'].')' ?></a>
                                        <br>原始推荐人：<br><a href="<?= url('store.user/recomend', ['user_id' => $list['r2']['id']]) ?>"> <?= $list['r2']['username'].'('.$list['r2']['phone'].')' ?></a>
                                    <?php else: ?>
                                        无
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(!empty($list['r3'])): foreach ($list['r3'] as $item): ?>
                                    <a style="margin-left: 5%" href="<?= url('store.user/recomend', ['user_id' => $item['id']]) ?>"><?= $item['username'] ?></a>
                                    <span style="position:absolute; left: 45%;"><?= $item['phone'] ?></span>
                                    <span style="position:absolute; left: 60%;"><?= $item['point'] ?></span>
                                    <span style="position:absolute; left: 70%;"><?= $item['amount'] ?></span>
                                    <span style="position:absolute; left: 80%;"><?= $item['order_num'] ?></span>
                                    <span style="position:absolute; left: 90%;"><?= $item['add_time'] ?></span>
                                    <br>
                                    <?php endforeach; else: ?>
                                        无
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="am-u-lg-12 am-cf">
                    <div class="am-fr"><?= $list['r3']->render() ?> </div>
                    <div class="am-fr pagination-total am-margin-right">
                        <div class="am-vertical-align-middle">总记录：<?= $list['r3']->total() ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {

        // 删除元素
        var url = "<?= url('store.shop/delete') ?>";
        $('.item-delete').delete('store_id', url, '删除后不可恢复，确定要删除吗？');

    });
</script>

