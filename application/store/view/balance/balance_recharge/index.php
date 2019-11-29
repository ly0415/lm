<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">余额充值日志</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form id="form-search" class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-3">
                                <div class="am-form-group">
                                    <div class="am-btn-toolbar">
                                        <div class="am-btn-group am-btn-group-xs">
                                            <?php if (checkPrivilege('store/balance.balance_recharge/export')): ?>
                                                <a class="j-export am-btn am-btn-success am-radius"
                                                   href="javascript:void(0);">
                                                    <i class="iconfont icon-daochu am-margin-right-xs"></i>数据导出
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                <div class="am-u-sm-12 am-u-md-9">
                                    <div class="am fr">
                                        <div class="am-form-group am-fl">
                                            <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                                <input type="text" class="am-form-field" name="username" placeholder="请输入用户名称" value="<?= $request->get('username') ?>">
                                                <input type="text" class="am-form-field" name="phone" placeholder="请输入手机号码" value="<?= $request->get('phone') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <div class="am-u-md-9">
                                <div class="am fr am-g am-fr">
                                    <div class="am-form-group am-fl">
                                        充值类型：<select name="type" id="province"  data-province data-am-selected="{btnSize: 'sm',btnWidth:150, placeholder: '请选择'}">
                                            <option value="-1">请选择</option>
                                            <?php if ($typelist): foreach ($typelist as $val):  ?>
                                                <option value="<?= $val['id']?>"  <?= $val['id'] == $list['type'] ? 'selected' : '' ?> ><?= $val['name']?></option>
                                            <?php endforeach;endif;?>
                                        </select>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <select name="status" id="city"  data-province data-am-selected="{btnSize: 'sm',btnWidth:150, placeholder: '请选择'}">
                                            <option value="-1">请选择</option>
                                            <?php if ($statuslist): foreach ($statuslist as $val):  ?>
                                                <option value="<?= $val['id']?>" <?= $val['id'] == $list['status'] ? 'selected' : '' ?> ><?= $val['name']?></option>
                                            <?php endforeach;endif;?>
                                        </select>
                                    </div>
                                    <div class="am-form-group am-fl">
                                       充值来源： <select name="source" id=""  data-province data-am-selected="{btnSize: 'sm',btnWidth:150, placeholder: '请选择'}">
                                            <option value="-1">请选择</option>
                                            <?php if ($sourcelist): foreach ($sourcelist as $val):  ?>
                                                <option value="<?= $val['id']?>" <?= $val['id'] == $list['source'] ? 'selected' : '' ?> ><?= $val['name']?></option>
                                            <?php endforeach;endif;?>
                                        </select>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input autocomplete="off" type="text" name="add_time"
                                               class="am-form-field"
                                               value="<?= $request->get('add_time') ?>" placeholder="发送开始时间"
                                               data-am-datepicker>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input autocomplete="off" type="text" name="end_time"
                                                   class="am-form-field"
                                                   value="<?= $request->get('end_time') ?>" placeholder="发送结束时间"
                                                   data-am-datepicker>
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search"
                                                        type="submit"></button>
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
                                <th>ID</th>
                                <th>用户名称</th>
                                <th>联系方式</th>
                                <th>充值金额</th>
                                <th>变更前余额</th>
                                <th>账户余额</th>
                                <th>充值描述</th>
                                <th>充值类型</th>
                                <th>充值状态</th>
                                <th>来源</th>
                                <th>操作时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php   if (!$list['data']->isEmpty()): foreach ($list['data'] as $item):  ?>
                                <tr>
                                    <td class="am-text-middle"><?=$item['id']?></td>
                                    <td class="am-text-middle"><?=$item['username']?></td>
                                    <td class="am-text-middle"><?=$item['phone']?></td>
                                    <td class="am-text-middle"><?=$item['c_money']?></td>
                                    <td class="am-text-middle"><?=$item['old_money']?></td>
                                    <td class="am-text-middle"><?=$item['new_money']?></td>
                                    <td class="am-text-middle"><?=$item['description']?></td>
                                    <td class="am-text-middle"><?=$item['type_name']?></td>
                                    <td class="am-text-middle"><?=$item['type_status']?></td>
                                    <td class="am-text-middle"><?=$item['source']?></td>
                                    <td class="am-text-middle"><?=date('Y-m-d',$item['add_time'])?></td>

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
                        <div class="am-fr"><?= $list['data']->render() ?> </div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录：<?= $list['data']->total() ?></div>
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
        var url = "<?= url('store.data/delete') ?>";
        $('.item-delete').delete('user_id', url, '删除后不可恢复，确定要删除吗？');

        /**
         * 订单导出
         */
        $('.j-export').click(function () {
            var data = {};
            var formData = $('#form-search').serializeArray();
            $.each(formData, function () {
                this.name !== 's' && (data[this.name] = this.value);
            });
            window.location = "<?= url('store/balance.balance_recharge/export') ?>" + '&' + $.urlEncode(data);
        });

    });
</script>
<script>

    function addItem(obj,item){
        var _html = '';
        $.each(item,function (k,v) {
            _html += "<option    value='"+v.id+"'>"+v.name+"</option>";
        })
        obj.append(_html);
        obj.change();
    }

    $(function () {
        $("#province").on('change',function () {
            var province_id = $(this).val();
            var city = $("#city");
            var region = $("#region");
            var _html = "<option value='0'>请选择</option>";
            city.html(_html);
            region.html(_html);
            if(province_id > 0){
                $.post("<?=url('api/balance/getBalanceTypeList')?>",{type_id:province_id},function (res) {
                    addItem(city,res);
                },'JSON')
            }
        });






    });
</script>
