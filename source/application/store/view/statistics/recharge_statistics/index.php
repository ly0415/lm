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
                                                <?php if ($storelist): foreach ($storelist as $val):  ?>
                                                    <option value="<?= $val['id']?>" <?= $val['id'] == $storeuserlist['store_id'] ? 'selected' : '' ?> ><?= $val['store_name']?></option>
                                                <?php endforeach;endif;?>
                                            </select>
                                        </div>
                                    <?php } ?>
                                    <div class="am-form-group am-fl">
                                        <select name="storeuser_id"  data-city id="city" data-am-selected="{btnSize: 'sm',btnWidth:150, placeholder: '请选择店员'}">
                                            <option value="0">请选择店员</option>
                                            <?php if ($userlist): foreach ($userlist as $val):  ?>
                                                <option  <?=$storeuserlist['storeuser_id']==$val['id']?'selected' :'' ?> value="<?= $val['id']?>" ><?= $val['real_name']?></option>
                                            <?php endforeach;endif;?>
                                        </select>
                                    </div>
                                    <div class="am-form-group tpl-form-border-form am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form" style="width: 200px">
                                            <input type="text" class="am-form-field" name="endtime" placeholder="请选择起始月份" value="<?= $storeuserlist['endtime']?$storeuserlist['endtime']:''?>" data-am-datepicker="{format: 'yyyy-mm', viewMode: 'months',minViewMode: 'months'}" autocomplete="off">
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
                        <div id="piePic" style="width:100%;height:600px;"></div>
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
        tooltip : {
            trigger: 'axis',
            axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
            }
        },
        legend: {
//            data:['充值总额','邮件营销','联盟广告','视频广告']
            data:['充值总额',<?php foreach($storeuserlist['type'] as $val){  ?>
                '<?= $val.'面额' ?>',
                <?php  } ?>]

        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        xAxis : [
            {
                type : 'category',
                data : [<?php foreach($storeuserlist['data'] as $k=>$val){  ?>
                    '<?= ($k+1).(($storeuserlist['count']>12)?'日':'月') ?>',
                    <?php  } ?>]
            }
        ],
        yAxis : [
            {
                type : 'value',
                axisLabel: {
                    formatter: '{value} °C'
                }
            }
        ],
        series : [
            {
                name:'充值总额',
                type:'bar',
                data:[<?php foreach($storeuserlist['data'] as $k=>$val){  ?>
                    {value:<?= !empty($val['total'])?($val['total']):0?>,name:'张'},
                    <?php  } ?>]
            },
            <?php if(!empty($storeuserlist['type'])){ foreach($storeuserlist['type'] as $val){?>
            {
                name:'<?= $val.'面额'?>',
                type:'bar',
                stack: '广告',
                data:[<?php foreach($storeuserlist['data'] as $item){ if(!empty($item['list'])){  ?>
                    {value:<?= !empty($item['list'][$val])?($item['list'][$val]['total']):0 ?>,name:'张'},
                    <?php      }else{?>
                    '0',
            <?php } }?>
                ]
//                data:[120, 132, 101, 134, 90, 230, 210]
            },
            <?php }} ?>
        ]
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
                $.post("<?=url('statistics.user_statistics/getStoreUser')?>",{store_id:province_id},function (res) {
                    addItem(city,res);
                },'JSON')
            }
        });






    });
</script>