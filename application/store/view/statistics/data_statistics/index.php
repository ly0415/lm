<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-body">
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">销售统计</div>
                    </div>
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form id="form-search" class="toolbar-form" action="" class="am-fr">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-12 am-u-end">
                                <div class="am fr">
                                    <?php if(!T_GENERAL){?>
                                        <div class="am-form-group am-fl">
                                            <select name="store_id" id="province"  data-province data-am-selected="{btnSize: 'sm',btnWidth:150, placeholder: '所属门店'}">
                                                <option value="">请选择门店</option>
                                                <?php if ($storeList): foreach ($storeList as $val):  ?>
                                                    <option value="<?= $val['id']?>" <?= $val['id'] == $list['store_id'] ? 'selected' : '' ?> ><?= $val['store_name']?></option>
                                                <?php endforeach;endif;?>
                                            </select>
                                        </div>
                                    <?php } ?>
                                    <div class="am-form-group am-fl">
                                        <select name="cat_id"  data-city id="city" data-am-selected="{btnSize: 'sm',btnWidth:150, placeholder: '请选择业务类型'}">
                                            <option value="0">请选择业务类型</option>
                                            <?php if ($store_category): foreach ($store_category as $val):  ?>
                                                <option value="<?= $val['id']?>" <?= $val['id'] == $list['cat_id'] ? 'selected' : '' ?>><?= $val['name']?></option>
                                            <?php endforeach;endif;?>
                                        </select>
                                    </div>
                                
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <input type="text" name="starttime" class="am-form-field" style="width: 150px" value="<?= $list['startime']?$list['startime']:''?>" placeholder="请选择起始日期" data-am-datepicker autocomplete="off">
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form" style="width: 200px">
                                            <input type="text" class="am-form-field" name="endtime" placeholder="请选择截止日期" value="<?= $list['endtime']?$list['endtime']:''?>" data-am-datepicker autocomplete="off">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search" type="submit"></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="am-form-group am-text-right am-margin-top-lg">
                        <div id="piePic" style="width:100%;height:350px;"></div>
                    </div>

                    <div class="widget-head am-cf">
                        <div class="widget-title a m-cf">商品列表</div>
                    </div>
                    <div class="widget-body am-fr">
                        <div class="am-scrollable-horizontal am-u-sm-12">
                            <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black am-text-nowrap">
                                <thead>
                                    <tr>
                                        <th>编号</th>
                                        <th>商品名称</th>
                                        <th>业务类型</th>
                                        <?php if(!T_GENERAL){?>
                                        <th>站点名称</th>
                                        <?php } ?>
                                        <th>销售数量</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (!$list['data']->isEmpty()): foreach ($list['data'] as $k=>$item): ?>
                                    <tr>
                                        <td class="am-text-middle"><?= $k+1?></td>
                                        <td class="am-text-middle"><?= $item['goods_name']?></td>
                                        <td class="am-text-middle"><?= $item['name']?></td>
                                    <?php if(!T_GENERAL){?>
                                        <td class="am-text-middle"><?= $item['store_name']?></td>
                                    <?php } ?>
                                        <td class="am-text-middle"><?= $item['count']?></td>
                                    </tr>
                                <?php endforeach; else:  ?>
                                    <tr>
                                        <td colspan="10" class="am-text-center">暂无记录</td>
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
</div>

<script src="assets/store/js/echarts.min.js"></script>
<script type="text/javascript">
    // 基于准备好的dom，初始化echarts实例
    var myChart = echarts.init(document.getElementById('piePic'));

    // 指定图表的配置项和数据
    var option = {
    title : {
        text: '销售信息统计图',
//         subtext: '<?//= $list['startime'].'-'.$list['endtime']?>//',
        x:'center',
    },
    tooltip : {
        trigger: 'item',
        formatter: "{a} <br/>{b} : {c} ({d}%)"
    },
    legend: {
        orient: 'vertical',
        left: 'left',
        data: [<?php foreach($list['data'] as $val){  ?>
            '<?= $val['goods_name']?>',
            <?php  } ?>]
    },
    series : [
        {
            name: '销售统计',
            type: 'pie',
            radius : '60%',
            center: ['65%', '50%'],
            data:[<?php foreach($list['data'] as $val){  ?>
                    {value:<?= $val['count']?>, name:'<?= $val['goods_name']?>'},
                <?php  } ?>
            ],
            itemStyle: {
                emphasis: {
                    shadowBlur: 10,
                    shadowOffsetX: 0,
                    shadowColor: 'rgba(0, 0, 0, 0.5)'
                }
            }
        }
    ]
};


    // 使用刚指定的配置项和数据显示图表。
    myChart.setOption(option);
</script>
