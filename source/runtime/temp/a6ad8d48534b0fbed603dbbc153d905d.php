<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:91:"D:\phpStudy\WWW\lmeriPro\web/../source/application/store\view\market\spike_activity\add.php";i:1572591631;s:73:"D:\phpStudy\WWW\lmeriPro\source\application\store\view\layouts\layout.php";i:1572400948;}*/ ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <title>艾美睿零售</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="renderer" content="webkit"/>
    <meta http-equiv="Cache-Control" content="no-siteapp"/>
    <link rel="icon" type="image/png" href="assets/common/i/favicon.ico"/>
    <meta name="apple-mobile-web-app-title" content="艾美睿零售"/>
    <link rel="stylesheet" href="assets/common/css/amazeui.min.css"/>
    <link rel="stylesheet" href="assets/store/css/app.css?v=<?= $version ?>"/>
    <link rel="stylesheet" href="//at.alicdn.com/t/font_783249_fc0v7ysdt1k.css">
    <link rel="stylesheet" href="//at.alicdn.com/t/font_1282474_9pkaszims0j.css">
    <link rel="stylesheet" href="assets/store/css/tips.css">
    <script src="assets/common/js/jquery.min.js"></script>
    <script src="//at.alicdn.com/t/font_783249_e5yrsf08rap.js"></script>
    <script>
        BASE_URL = '<?= isset($base_url) ? $base_url : '' ?>';
        STORE_URL = '<?= isset($store_url) ? $store_url : '' ?>';
        MOUDEL = '<?=$request->controller()?>';
    </script>
</head>

<body data-type="">
<!--自动打印部分-->
<div id="automatic-print" style="display: none"></div>

<div class="am-g tpl-g">
    <!-- 头部 -->
    <header class="tpl-header">
        <!-- 右侧内容 -->
        <div class="tpl-header-fluid">
            <!-- 侧边切换 -->
            <div class="am-fl tpl-header-button switch-button">
                <i class="iconfont icon-menufold"></i>
            </div>
            <!-- 刷新页面 -->
            <div class="am-fl tpl-header-button refresh-button">
                <i class="iconfont icon-refresh"></i>
            </div>
            <!-- 其它功能-->
            <div class="am-fr tpl-header-navbar">
                <ul>
                    <!-- 欢迎语 -->
                    <li class="am-text-sm tpl-header-navbar-welcome">
                        <a href="<?= url('store.user/renew') ?>">欢迎你，<span><?= $store['user']['user_name'] ?></span>
                        </a>
                    </li>
                    <!-- 消息提示 -->
                    <li class="am-text-sm newsBox">
                        <a href="javascript:void(0)" class="lingdan">
                            <i class="iconfont icon-lingdang"></i>
                            <span class="tipsnum"><?= $tips_data['2'] ?></span>
                        </a>
                        <ul class="newsLists">
                            <li class="newsitem">
                                <a class="" href="<?= url('order/index',['tips'=>1]) ?>">
                                    <div style="position:relative;">
                                        <i class="iconfont icon-daifahuo"></i>&nbsp;&nbsp;待接单&nbsp;&nbsp;
                                        <span class="itemnum"><?= $tips_data['0'] ?>个</span>
                                    </div>
                                </a>
                            </li>
                            <li class="newsitem">
                                <a class="" href="<?= url('order/index',['tips'=>2]) ?>">
                                    <div style="position:relative;">
                                        <i class="iconfont icon-kucun"></i>&nbsp;&nbsp;待退款&nbsp;&nbsp;
                                        <span class="itemnum"><?= $tips_data['1'] ?>个</span>
                                    </div>
                                </a>
                            </li>
<!--                            <li class="newsitem">-->
<!--                                <a class="" href="">-->
<!--                                    <div style="position:relative;">-->
<!--                                        <i class="iconfont icon-pinglun"></i>&nbsp;&nbsp;新评论&nbsp;&nbsp;-->
<!--                                        <span class="itemnum">0个</span>-->
<!--                                    </div>-->
<!--                                </a>-->
<!--                            </li>-->
<!--                            <li class="newsitem">-->
<!--                                <a class="" href="">-->
<!--                                    <div style="position:relative;">-->
<!--                                        <i class="iconfont icon-money"></i>&nbsp;&nbsp;申请提现&nbsp;&nbsp;-->
<!--                                        <span class="itemnum">0个</span>-->
<!--                                    </div>-->
<!--                                </a>-->
<!--                            </li>-->
                        </ul>
                    </li>
                    <!-- 退出 -->
                    <li class="am-text-sm">
                        <a href="<?= url('passport/logout') ?>">
                            <i class="iconfont icon-tuichu"></i> 退出
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>
    <!-- 侧边导航栏 -->
    <div class="left-sidebar dis-flex">
        <?php $menus = $menus ?: []; $group = $group ?: 0; ?>
        <!-- 一级菜单 -->
        <ul class="sidebar-nav">
            <li class="sidebar-nav-heading">艾美睿零售</li>
            <?php foreach ($menus as $key => $item): ?>
                <li class="sidebar-nav-link">
                    <a href="<?= isset($item['index']) ? url($item['index']) : 'javascript:void(0);' ?>"
                       class="<?= $item['active'] ? 'active' : '' ?>">
                        <?php if (isset($item['is_svg']) && $item['is_svg'] == true): ?>
                            <svg class="icon sidebar-nav-link-logo" aria-hidden="true">
                                <use xlink:href="#<?= $item['icon'] ?>"></use>
                            </svg>
                        <?php else: ?>
                            <i class="iconfont sidebar-nav-link-logo <?= $item['icon'] ?>"
                               style="<?= isset($item['color']) ? "color:{$item['color']};" : '' ?>"></i>
                        <?php endif; ?>
                        <?= $item['name'] ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <!-- 子级菜单-->
        <?php $second = isset($menus[$group]['submenu']) ? $menus[$group]['submenu'] : []; if (!empty($second)) : ?>
            <ul class="left-sidebar-second">
                <li class="sidebar-second-title"><?= $menus[$group]['name'] ?></li>
                <li class="sidebar-second-item">
                    <?php foreach ($second as $item) : if (!isset($item['submenu'])): ?>
                            <!-- 二级菜单-->
                            <a href="<?= url($item['index']) ?>"
                               class="<?= (isset($item['active']) && $item['active']) ? 'active' : '' ?>">
                                <?= $item['name']; ?>
                            </a>
                        <?php else: ?>
                            <!-- 三级菜单-->
                            <div class="sidebar-third-item">
                                <a href="javascript:void(0);"
                                   class="sidebar-nav-sub-title <?= $item['active'] ? 'active' : '' ?>">
                                    <i class="iconfont icon-caret"></i>
                                    <?= $item['name']; ?>
                                </a>
                                <ul class="sidebar-third-nav-sub">
                                    <?php foreach ($item['submenu'] as $third) : ?>
                                        <li>
                                            <a class="<?= $third['active'] ? 'active' : '' ?>"
                                               href="<?= url($third['index']) ?>">
                                                <?= $third['name']; ?></a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; endforeach; ?>
                </li>
            </ul>
        <?php endif; ?>
    </div>

    <!-- 内容区域 start -->
    <div class="tpl-content-wrapper <?= empty($second) ? 'no-sidebar-second' : '' ?>">
        <div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">秒杀活动</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-u-lg-2 am-form-label form-require">活动名称 </label>
                                <div class="am-u-sm-10 am-u-end">
                                    <input type="text" class="tpl-form-input" name="spike[name]"
                                           value="" placeholder="请输入活动名称" required>
                                </div>
                            </div>
                            <div class="am-form-group switch-expire_type expire_type__20">
                                <label class="am-u-sm-2 am-u-lg-2 am-form-label form-require">时间范围 </label>
                                <div class="am-u-sm-10 am-u-end">
                                    <input type="text" class="j-startTime am-form-field am-margin-bottom-sm"
                                           name="spike[start_time]" placeholder="请选择开始日期" required>
                                    <input type="text" class="j-endTime am-form-field" name="spike[end_time]"
                                           placeholder="请选择结束日期" required>
                                    <small>&nbsp;&nbsp;如开始时间:2015-06-15，结束时间2015-06-16</small>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-u-lg-2 am-form-label form-require">到期退款（天） </label>
                                <div class="am-u-sm-10 am-u-end">
                                    <input type="number" min="1" max="30" class="tpl-form-input" name="spike[refund_time]"
                                           value="30" placeholder="请输入到期退款时间" required>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label for="marketValue" class="am-u-sm-2 am-u-lg-2 am-form-label "> 活动类型： </label>
                                <div class="am-u-sm-10 am-u-end">
                                    <?php if(isset($type)):foreach ($type as $item):?>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" checked name="spike[type][]"  value="<?=$item['id']?>" class="am-ucheck-checkbox am-field-valid">
                                        <span class="am-ucheck-icons">
                                        <i class="am-icon-unchecked"></i>
                                        <i class="am-icon-checked"></i>
                                        </span>
                                        <?=$item['name']?>
                                    </label>
                                    <?php endforeach;endif;?>

                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-u-lg-2 am-form-label form-require">活动状态 </label>
                                <div class="am-u-sm-10 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="spike[status]" value="1" data-am-ucheck checked>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="spike[status]" value="2" data-am-ucheck>
                                        <span class="am-link-muted">关闭</span>
                                    </label>
                                </div>
                            </div>

                            <div class="am-form-group am-padding-top">
                                <label class="am-u-sm-2 am-u-lg-2 am-form-label form-require">时间段 </label>
                                <div class="am-u-sm-10 am-u-end">
                                    <div class="am-form-group">
                                        <div class="am-u-sm-5"><input type="text" time_point="1" value="08:00" disabled></div>
                                        <div class="widget-become-goods am-form-file am-margin-top-xs">
                                            <button type="button" btn-id='0'
                                                     class="j-selectUser  am-btn am-btn-secondary am-radius" style="    font-size: 1.22rem;
    padding: .5rem .9rem;">
                                                <i class="am-icon-cloud-upload"></i> 选择商品
                                            </button>
                                        </div>
                                        <div class="user-list am-u-sm-9 am-scrollable-horizontal uploader-list">
                                        </div>
                                    </div>

                                    <div class="am-form-group">
                                        <div class="am-u-sm-5"><input type="text" time_point="5" value="10:00" disabled></div>
                                        <div class="widget-become-goods am-form-file am-margin-top-xs">
                                            <button type="button" btn-id='1'
                                                     class="j-selectUser  am-btn am-btn-secondary am-radius" style="    font-size: 1.22rem;
    padding: .5rem .9rem;">
                                                <i class="am-icon-cloud-upload"></i> 选择商品
                                            </button>
                                        </div>
                                        <div class="user-list am-u-sm-9 am-scrollable-horizontal uploader-list">
                                        </div>
                                    </div>

                                    <div class="am-form-group">
                                        <div class="am-u-sm-5"><input type="text" time_point="10" value="12:00" disabled></div>
                                        <div class="widget-become-goods am-form-file am-margin-top-xs">
                                            <button type="button" btn-id='2'
                                                     class="j-selectUser  am-btn am-btn-secondary am-radius" style="    font-size: 1.22rem;
    padding: .5rem .9rem;">
                                                <i class="am-icon-cloud-upload"></i> 选择商品
                                            </button>
                                        </div>
                                        <div class="user-list am-u-sm-9 am-scrollable-horizontal uploader-list">
                                        </div>
                                    </div>

                                    <div class="am-form-group">
                                        <div class="am-u-sm-5"><input type="text" time_point="15" value="14:00" disabled></div>
                                        <div class="widget-become-goods am-form-file am-margin-top-xs">
                                            <button type="button" btn-id='3'
                                                     class="j-selectUser  am-btn am-btn-secondary am-radius" style="    font-size: 1.22rem;
    padding: .5rem .9rem;">
                                                <i class="am-icon-cloud-upload"></i> 选择商品
                                            </button>
                                        </div>
                                        <div class="user-list am-u-sm-9 am-scrollable-horizontal uploader-list">
                                        </div>
                                    </div>

                                    <div class="am-form-group">
                                        <div class="am-u-sm-5"><input type="text" time_point="20" value="16:00" disabled></div>
                                        <div class="widget-become-goods am-form-file am-margin-top-xs">
                                            <button type="button" btn-id='4'
                                                     class="j-selectUser  am-btn am-btn-secondary am-radius" style="    font-size: 1.22rem;
    padding: .5rem .9rem;">
                                                <i class="am-icon-cloud-upload"></i> 选择商品
                                            </button>
                                        </div>
                                        <div class="user-list am-u-sm-9 am-scrollable-horizontal uploader-list">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 规格弹框-->
                            <div class="am-modal am-modal-alert" tabindex="-1" id="my-alert">
                                    </div>

                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button id="submit" type="submit" class="j-submit am-btn am-btn-secondary">提交
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>
    /**
     * 时间选择
     */
    $(function () {
        var nowTemp = new Date();
        var nowDay = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0).valueOf();
        var nowMoth = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), 1, 0, 0, 0, 0).valueOf();
        var nowYear = new Date(nowTemp.getFullYear(), 0, 1, 0, 0, 0, 0).valueOf();
        var $startTime = $('.j-startTime');
        var $endTime = $('.j-endTime');

        var checkin = $startTime.datepicker({
            onRender: function (date, viewMode) {
                // 默认 days 视图，与当前日期比较
                var viewDate = nowDay;
                switch (viewMode) {
                    // moths 视图，与当前月份比较
                    case 1:
                        viewDate = nowMoth;
                        break;
                    // years 视图，与当前年份比较
                    case 2:
                        viewDate = nowYear;
                        break;
                }
                return date.valueOf() < viewDate ? 'am-disabled' : '';
            }
        }).on('changeDate.datepicker.amui', function (ev) {
            if (ev.date.valueOf() > checkout.date.valueOf()) {
                var newDate = new Date(ev.date)
                newDate.setDate(newDate.getDate() + 1);
                checkout.setValue(newDate);
            }
            checkin.close();
            $endTime[0].focus();
        }).data('amui.datepicker');

        var checkout = $endTime.datepicker({
            onRender: function (date, viewMode) {
                var inTime = checkin.date;
                var inDay = inTime.valueOf();
                var inMoth = new Date(inTime.getFullYear(), inTime.getMonth(), 1, 0, 0, 0, 0).valueOf();
                var inYear = new Date(inTime.getFullYear(), 0, 1, 0, 0, 0, 0).valueOf();
                // 默认 days 视图，与当前日期比较
                var viewDate = inDay;
                switch (viewMode) {
                    // moths 视图，与当前月份比较
                    case 1:
                        viewDate = inMoth;
                        break;
                    // years 视图，与当前年份比较
                    case 2:
                        viewDate = inYear;
                        break;
                }
                return date.valueOf() <= viewDate ? 'am-disabled' : '';
            }
        }).on('changeDate.datepicker.amui', function (ev) {
            checkout.close();
        }).data('amui.datepicker');
    });
</script>

<script>

    /**
     * 获取价格和库存
     */
    function getPriceStock(){
        var spec = [];
        $.each($(".specdialog input[type='radio']:checked"),function (k,v) {
            spec.push($(v).val());
        });
        var store_goods_id = $(".specdialog input[name='store_goods_id']").val();
        var spec_item = spec ? spec.join('_') : '';
        $.post("<?=url('order.setting/ajax_goods_price_stock')?>",{store_goods_id:store_goods_id,key:spec_item},function (re) {
            $(".specdialog input[name='stock']").val(re.data.stock);
            $(".specdialog input[name='price']").val(re.data.price);
        })
    }

    $(function () {
        // 循环数据，添加table的thead 数组arr表示table中thead的名称
        function ergodic(arr){
            let tr=$('<tr></tr>')
            var th0=$('<th class="am-text-center"></th>').text('商品ID')
            var th1=$('<th class="am-text-center"></th>').text('图片')
            var th2=$('<th class="am-text-center"></th>').text('名称')
            var th4=$('<th class="am-text-center"></th>').text('规格')
            var th5=$('<th class="am-text-center"></th>').text('本店售价')
            var th6=$('<th class="am-text-center"></th>').text('秒杀价')
            var th7=$('<th class="am-text-center"></th>').text('秒杀数量')
            var th8=$('<th class="am-text-center"></th>').text('限购')
            var th9=$('<th class="am-text-center"></th>').text('操作')
            tr.append([th0,th1,th2,th4,th5,th6,th7,th8,th9])
            return tr
        }

        // 选择弹框中的规格
        $(document).on('change','.specdialog input[type="radio"]',function(){
            getPriceStock();
        });

        //选择商品
        var select_goods=[[],[],[],[],[]];
        $('.j-selectUser').click(function () {
            var btn_id=$(this).attr('btn-id')
            var time_point = $(this).parent().prev().find('input').attr('time_point');
            var goods_ids = [];
            $("input[name='spike[goods_id][]']").each(function(){
                goods_ids.push($(this).val());
            });
            $.selectData({
                title: '选择商品',
                uri: "<?=url('data.store_goods/lists')?>",
                dataIndex: 'id',
                done: function (data) {
                    var selData=data
                    $.each(selData,function(k,v){
                        v.specName='';
                        v.specNames=[];
                        v.specId='';
                        v.specIds=[];
                        v.stock='';
                        v.price='';
                        v.spike_nums='';
                        v.timepoint=time_point;
                        v.has_sel_spec=false
                    })
                    if(select_goods[btn_id]==''){
                        $.each(selData,function(k,v){
                            select_goods[btn_id].push(v);
                        })
                        showData(select_goods)
                    }else{
                        let arr1=select_goods[btn_id];
                        let arr2=data;
                        let arr3=arr1.concat(arr2);
                        let arr4=[]
                        for (item1 of arr3){
                            let flag=true;
                            for(item2 of arr4){
                                if(item1.id==item2.id){
                                    flag = false;
                                }
                            }
                            if(flag){
                                arr4.push(item1)
                            }
                        }
                        select_goods[btn_id]=arr4
                        showData(select_goods)
                    }
                }
            });
        });

        function showData(dataArr){
            console.log(select_goods)
            var packBox=$('.user-list')
            $.each(packBox,function(k,v){
                if(dataArr[k]!=''){
                    var user = [];
                    $(v).empty();
                    var table=$('<table class="am-table am-text-nowrap am-margin-top" style="width:100%"></table>')
                    let thead=$('<thead></thead>')
                    var tbody=$('<tbody></tbody>')
                    $.each(dataArr[k],function (idx,val) {
                        user.push(v);
                        var td0=$('<td style="height:67px;line-height:67px;"><input type="hidden" name="spike[spike_goods]['+k+idx+'][time_point]" readonly value="'+val.timepoint+'"><input type="text" name="spike[spike_goods]['+k+idx+'][store_goods_id]" readonly style="width:60px;border:none;outline:none;margin:9px 0;background-color:#eeeeee" value="'+val.id+'"><input type="hidden" name="spike[spike_goods]['+k+idx+'][goods_img]"  value="'+val.original_img+'"></td>')
                        var td1=$('<td></td>').html('<img style="width:50px;height:50px;" src="../'+val.original_img+'"><input type="hidden" name="spike[spike_goods]['+k+idx+'][goods_key]" value="'+val.specId+'">');
                        var td2=$('<td style="text-align:center;"></td>').html('<input type="text" style="width:150px;text-align:center;border:none;outline:none;margin:9px 0;background-color:#eee;" name="spike[spike_goods]['+k+idx+'][goods_name]" readonly value="'+val.goods_name+'">')

                        var td4='';
                        if(val.has_spec){
                            var inputBox=$('<input type="text" readonly style="width:120px;border:none;outline:none;background-color:#eee;margin:9px 0;" name="spike[spike_goods]['+k+idx+'][goods_key_name]" placeholder="请选择规格" required>').val(val.specName)
                            var guige_btn=$('<button type="button" data-id="'+val.id+'" class="shezhi am-btn am-btn-secondary" style="width:78px;height:31px;font-size:13px;padding:6px 12px;margin:9px 0;" data-am-modal="{target: \'#my-alert\'}"></button>').text('选择规格').attr({'idn':idx,'parent_id':k,'timepoint':val.timepoint})
                            td4=$("<td style='border-top:0;'></td>").append([inputBox,guige_btn]).css({'display':'flex','justify-content':'center','align-items': 'center'});
                        }else{
                            var spanBox=$('<div>无规格</div>').css({'text-align':'center','font-size':'14px'});
                            td4=$("<td></td>").append(spanBox).css({'text-align':'center','padding':'22px 8px'});
                        }

                        var salePrice=$('<input required style="width:70px;border:none;outline:none;background-color:#eee;" type="number" name="spike[spike_goods]['+k+idx+'][discount_price]" />')
                        var shopPrice=$('<input readonly style="width:70px;border:none;outline:none;" type="number" value="'+val.shop_price+'" name="spike[spike_goods]['+k+idx+'][goods_price]" />')
                        var stock=$('<input class="spikeNums" style="width:70px;height:31px;padding:6px 5px;border:none;outline:none;background-color:#eee;" value="'+val.spike_nums+'" name="spike[spike_goods]['+k+idx+'][goods_num]" />')
                        var limit=$('<input type="number" class="limitNum" value="1" style="width:65px;border:none;outline:none;background-color:#eee;" name="spike[spike_goods]['+k+idx+'][limit_num]" />')
                        var td5=$('<td style="padding:18px 10px;"></td>').html(shopPrice);
                        var td6=$('<td style="padding:18px 10px;"></td>').html(salePrice);
                        var td7=$('<td style="padding:18px 10px;"></td>').html(stock);
                        var td8=$('<td style="padding:18px 10px;"></td>').html(limit);
                        var delBtn=$("<button type='button' class='delBtn am-btn am-btn-danger am-btn-xs'>删除</button>").attr({'del_id':val.id,'idn':idx,'parent_id':k})
                        var td9=$("<td style='padding:18px 10px;text-align:center'></td>").html(delBtn);
                        tbody.append($('<tr data-goods='+val.id+'></tr>').append([td0,td1,td2,td4,td5,td6,td7,td8,td9]).css('border-top','1px solid #ccc'))
                    });
                    $(v).append(table.append([thead.append(ergodic()),tbody]))
                }
            })
        }

        // 设置秒杀数量的校验
        $(document).on('keyup','.spikeNums',function(){
            var idn = $(this).parent().parent().find('.delBtn').attr('idn')
            var parent_id = $(this).parent().parent().find('.delBtn').attr('parent_id')
            var spike_nums=$(this).val()
            if(!select_goods[parent_id][idn].has_spec){
                if(spike_nums>select_goods[parent_id][idn].goods_count){
                    layer.msg('当前商品库存为：'+select_goods[parent_id][idn].goods_count+'，'+'您已超出，请重新设置')
                    $(this).val('')
                }
            }else{
                if(!select_goods[parent_id][idn].has_sel_spec){
                    layer.msg('请选择商品规格')
                    $(this).val('')
                }else{
                    if(spike_nums>select_goods[parent_id][idn].goods_count){
                        layer.msg('当前商品库存为'+select_goods[parent_id][idn].goods_count+','+'您已超出,请重新设置')
                        $(this).val('')
                    }
                }
            }
        })

        // 限制数量的校验
        $(document).on('keyup','.limitNum',function(){
            var idn = $(this).parent().parent().find('.delBtn').attr('idn')
            var parent_id = $(this).parent().parent().find('.delBtn').attr('parent_id')
            var spike_nums=$(this).val()
            if(!select_goods[parent_id][idn].has_spec){
                if(spike_nums>select_goods[parent_id][idn].goods_count){
                    layer.msg('当前商品库存为：'+select_goods[parent_id][idn].goods_count+'，'+'您已超出，请重新设置')
                    $(this).val('')
                }
            }else{
                if(!select_goods[parent_id][idn].has_sel_spec){
                    layer.msg('请选择商品规格')
                    $(this).val('')
                }else{
                    if(spike_nums>select_goods[parent_id][idn].goods_count){
                        layer.msg('当前商品库存为'+select_goods[parent_id][idn].goods_count+','+'您已超出,请重新设置')
                        $(this).val('')
                    }
                }
            }
        })


        $(document).on('click','.delBtn',function(){
            var parentId=$(this).attr('parent_id')
            var idn=$(this).attr('idn')
            if($(this).parent().parent().parent().children().length==1){
                $(this).parent().parent().parent().parent().parent('').empty()
                select_goods[parentId]=[];
            }else{
                var inx=0;
                $.each(select_goods[parentId],function(id,row){
                    if(id==idn){
                        inx=id
                    }
                })
                select_goods[parentId].splice(inx,1)
                showData(select_goods)
            }
        })

        $.activities = {};
        $.extend($.activities,{
            aIndex:0,//设置的index值
            parent_id:0,
            chkArry:[],
            timepoint:'',
        });

        $(document).off('click.shezhi').on('click.shezhi','.shezhi',function(){
			var dataIndex = $(this).attr('idn');
			var parent_id = $(this).attr('parent_id');
			var store_goods_id = $(this).data('id');
			var timepoint = $(this).attr('timepoint');
			$.activities.aIndex = dataIndex;
			$.activities.parent_id = parent_id;
            var index = layer.load();
			$.post("<?=url('goods/ajax_get_specs')?>",{store_goods_id:store_goods_id},function (res) {
                layer.close(index);
                $("#my-alert").empty().append(res);
                getPriceStock();
            })
        })
		
        // 点击确定后，选中的属性回填到对应的设置按钮的td中并把设置按钮隐藏
        $(document).off('click.am-modal-btn').on('click.am-modal-btn',' .am-modal-btn',function(){
            console.log(select_goods)
            $.extend($.activities,{
                specName:'',
                specNames:[],
                specId:'',
                specIds:[],
                stock:$(".specdialog input[name='stock']").val(),
                price:$(".specdialog input[name='price']").val()
            });
			var inputs = $('.specChk input[type="radio"]');
			$.activities.chkArry=[];
			$('.sku_value:checked').each(function(i,v){
				var chkVal = $(this).attr('data-specname');
				var chkId = $(this).val();
				var chkData = {};
                $.activities.specName=$.activities.specName+'_'+chkVal
                $.activities.specNames.push(chkVal)
                $.activities.specId=$.activities.specId+'_'+chkId
                $.activities.specIds.push(chkId)
				chkData.chkVal = chkVal;
				chkData.chkId = chkId;
				$.activities.chkArry.push(chkData);
			})
            $.activities.specName=$.activities.specName.substr(1,$.activities.specName.length)
            $.activities.specId=$.activities.specId.substr(1,$.activities.specId.length)

            console.log($.activities.stock)

            if($.activities.stock!=0){
                $('.shezhi').each(function(j,k){
                    var dataIndex = $(this).attr('idn');
                    var parentId = $(this).attr('parent_id')
                    if($.activities.aIndex == dataIndex&&$.activities.parent_id==parentId){
                        select_goods[parentId][dataIndex].specName=$.activities.specName
                        select_goods[parentId][dataIndex].specNames=$.activities.specNames
                        select_goods[parentId][dataIndex].specId=$.activities.specId
                        select_goods[parentId][dataIndex].stock=$.activities.stock
                        select_goods[parentId][dataIndex].shop_price=$.activities.price
                        select_goods[parentId][dataIndex].has_sel_spec=true
                    }
                    console.log(select_goods)
                    showData(select_goods)
                })
            }else{
                layer.msg('当前规格暂无库存,请重新选择')
            }
        })

        /**
         * 表单验证提交
         * @type {*}
         */
        // $('#submit').click(function () {
        //     var data = [];
        //
        // })
        $('#my-form').superForm();

    });
</script>

    </div>
    <!-- 内容区域 end -->

</div>

<!--供音频提示音使用-->
<audio src="http://www.lmeri.com/assets/admin/dist/remind.mp3" id="myaudio"></audio>

<script src="assets/common/plugins/layer/layer.js"></script>
<script src="assets/common/js/jquery.form.min.js"></script>
<script src="assets/common/js/amazeui.min.js"></script>
<script src="assets/common/js/webuploader.html5only.js"></script>
<script src="assets/common/js/art-template.js"></script>
<script src="assets/store/js/app.js?v=<?= $version ?>"></script>
<script src="assets/store/js/file.library.js?v=<?= $version ?>"></script>
<?php if($tipsAuth): ?>
    <script src="http://www.lmeri.com/assets/plugin/lodop/LodopFuncs.js"></script>
<?php endif; ?>
<script>
   $('.newsBox').hover(
       function(){
           $(this).css('background','#eaeaea');
       },
       function(){
        $(this).css('background','#fff'); 
       }
   )

   var tipsAuth     = "<?= $tipsAuth[0]; ?>";
   var tipsAuthUser = "<?= $tipsAuth[1]; ?>";
   if( tipsAuth && tipsAuthUser){
       setInterval(function(){
           if( LODOP.VERSION ){
               //页面轮询---查询订单
               var url = "<?=url('order/get_notips_order');?>";
               $.post(url,{} ,function (res) {
                   if(res.code == 1){
                       var myAuto = document.getElementById('myaudio');
                       myAuto.play();
                       $('#automatic-print').html(res.data);
                       setTimeout(function(){
                           printerShow();
                           PrintByPrinterIndex();
                           PrintByPrinterIndex_biaoqian();
                       }, 1000);
                   }
               }, 'json');
           }
       }, 10000);
   }

   $('.newsitem').hover(
       function(){
          $(this).css('background','#ececec'); 
       },
       function(){
        $(this).css('background','#fff');  
       }
   )

    $('.newsBox').on('click',function(e){
        e.stopPropagation();
        $('.newsLists').toggle();
        var tag = $('.newsLists');
        var flag = true;
        $(document).bind('click',function(e){
            var target = $(e.target);
            if(target.closest(tag).length==0&&flag==true){
                tag.hide();
                flag=false;
            }
        })
    })
</script>
</body>

</html>
