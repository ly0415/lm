<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-body">
                    <div class="widget-head am-cf">
                        <div class="widget-title am-fl">推荐会员</div>
                    </div>
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form id="form-search" class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-12 am-u-end">
                                <div class="tips am-margin-bottom-sm am-u-sm-12">
                                    <div class="pre">
                                        <p> 注：初始统计当前整年所有门店店员所推荐的会员。
                                    </div>
                                </div>
                                <div class="am fr">
                                    <?php if(!T_GENERAL){?>
                                    <div class="am-form-group am-fl">
                                        <select name="store_id" id="province"  data-province data-am-selected="{btnSize: 'sm',btnWidth:150, placeholder: '所属门店'}">
                                            <option value="">请选择门店</option>
                                            <?php if ($storelist): foreach ($storelist as $val):  ?>
                                                <option value="<?= $val['id']?>" <?= $val['id'] == $storeuserlist['store_id'] ? 'selected' : '' ?>><?= $val['store_name']?></option>
                                            <?php endforeach;endif;?>
                                        </select>
                                    </div>
                                    <?php } ?>
                                    <div class="am-form-group am-fl">
                                        <select name="storeuser_id"  data-city id="city" data-am-selected="{btnSize: 'sm',btnWidth:150, placeholder: '请选择店员'}">
                                            <option value="0">请选择店员</option>
                                            <?php if ($userlist): foreach ($userlist as $val):  ?>
                                                <option <?=$storeuserlist['storeuser_id']==$val['id']?'selected' :'' ?> value="<?= $val['id']?>" ><?= $val['real_name']?></option>
                                            <?php endforeach;endif;?>
                                        </select>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form" style="width: 200px">
                                            <input type="text" class="am-form-field" name="endtime" placeholder="请选择月份" value="<?= $storeuserlist['endtime']?$storeuserlist['endtime']:''?>" data-am-datepicker="{format: 'yyyy-mm', viewMode: 'months',minViewMode: 'months'}" autocomplete="off">
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
                        <div id="piePic" style="height:550px;"></div>
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
        title: {
            text: '新增会员统计表',
            // subtext: '纯属虚构'
            x:'center',
        },
        tooltip: {
            trigger: 'axis'
        },
        legend: {
            orient: 'vertical',
            left: 'left',
            data: []
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        xAxis: [{
            type: 'category',
            data: [<?php foreach($storeuserlist['data'] as $k=>$val){  ?>
                '<?= ($k+1).(($storeuserlist['count']>12)?'日':'月') ?>',
                <?php  } ?>],
            splitLine: {
                show: false
            },
            axisTick: {
                alignWithLabel: true
            }
        }],
        yAxis: [{
            type: 'value',
            splitLine: {
                show: false
            },
            splitArea: {
                show: true,
            },
        }],
        series: [{
            name: '会员数',
            type: 'bar',
            label: {
                normal: {
                    show: true,
                    position: 'top'
                }
            },
            itemStyle: {
                normal: {
                    color: function(params) { 
                        var colorList = ['#29AAE3']; 
                        return colorList[params.dataIndex] 
                    }
                },
            },

            data: [<?php foreach($storeuserlist['data'] as $k=>$val){ ?>
                '<?= $val?>',
                <?php }?>
            ],
        }]
    };
    // 使用刚指定的配置项和数据显示图表。
    myChart.setOption(option);
</script>
<script>

    function addItem(obj,item){
        var _html = '';
        $.each(item,function (k,v) {
            _html += "<option    value='"+v.id+"'>"+v.real_name+"</option>";
        })
        obj.append(_html);
        obj.change();
    }

    $(function () {
        $("#province").on('change',function () {
            var province_id = $(this).val();
            var city = $("#city");
            var region = $("#region");
            var _html = "<option value='0'>请选择店员</option>";
            city.html(_html);
            region.html(_html);
            if(province_id > 0){
                $.post("<?=url('api/user_statistics/getStoreUser')?>",{store_id:province_id},function (res) {
                    addItem(city,res);
                },'JSON')
            }
        });






    });
</script>