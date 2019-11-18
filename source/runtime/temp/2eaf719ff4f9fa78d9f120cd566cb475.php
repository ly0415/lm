<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:92:"D:\phpStudy\WWW\lmeriPro\web/../source/application/store\view\market\spike_activity\edit.php";i:1572669615;s:73:"D:\phpStudy\WWW\lmeriPro\source\application\store\view\layouts\layout.php";i:1572400948;}*/ ?>
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
                                           value="<?=$model['name']?>"  placeholder="请输入活动名称" required>
                                </div>
                            </div>
                            <div class="am-form-group switch-expire_type expire_type__20">
                                <label class="am-u-sm-2 am-u-lg-2 am-form-label form-require">时间范围 </label>
                                <div class="am-u-sm-10 am-u-end">
                                    <input type="text" class="j-startTime am-form-field am-margin-bottom-sm"
                                           name="spike[start_time]" value="<?=$model['start_time']['text']?>" placeholder="请选择开始日期" required>
                                    <input type="text" value="<?=$model['end_time']['text']?>" class="j-endTime am-form-field" name="spike[end_time]"
                                           placeholder="请选择结束日期" required>
                                    <small>&nbsp;&nbsp;如开始时间:2019-01-01，结束时间2019-12-31</small>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-2 am-u-lg-2 am-form-label form-require">到期退款（天） </label>
                                <div class="am-u-sm-10 am-u-end">
                                    <input type="number" min="1" max="30" class="tpl-form-input"  name="spike[refund_time]"
                                           value="<?=$model['refund_time']?>" placeholder="请输入到期退款时间" required>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label for="marketValue" class="am-u-sm-2 am-u-lg-2 am-form-label "> 活动特点： </label>
                                <div class="am-u-sm-10 am-u-end">
                                    <?php if(isset($type)):foreach ($type as $item):?>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" <?=in_array($item['id'],$model['type']['text']) ? 'checked' : '';?>  name="spike[type][]"   value="<?=$item['id']?>" class="am-ucheck-checkbox am-field-valid">
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
                                        <input type="radio" name="spike[status]" value="1" data-am-ucheck <?=$model['status']['value'] == 1 ? 'checked' : '';?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" <?=$model['status']['value'] == 2 ? 'checked' : '';?> name="spike[status]" value="2" data-am-ucheck>
                                        <span class="am-link-muted">关闭</span>
                                    </label>
                                </div>
                            </div>

                            <div class="am-form-group am-padding-top">
                                <label class="am-u-sm-2 am-u-lg-2 am-form-label form-require">时间段 </label>
                                <div class="am-u-sm-10 am-u-end">

<?php if(isset($model) && !empty($model['format_data'])):foreach ($model['format_data'] as $k => $value):?>
                                    <div class="am-form-group">
                                        <div class="am-u-sm-5"><input type="text" time_point="<?=$k?>" value="<?=$time[$k]?>:00" readonly></div>
                                        <!-- <div class="widget-become-goods am-form-file am-margin-top-xs">
                                            <button type="button" btn-id='0'
                                                     class="j-selectUser  am-btn am-btn-secondary am-radius" style="    font-size: 1.22rem;
    padding: .5rem .9rem;">
                                                <i class="am-icon-cloud-upload"></i> 选择商品
                                            </button>
                                        </div> -->
                                        <div class="user-list am-u-sm-10 am-scrollable-horizontal uploader-list">
                                            <table class="am-table am-text-nowrap am-margin-top" style="width:100%">

                                                <thead>
                                                <?php if(!empty($value) && is_array($value)):?>

                                                <tr>
                                                        <th class="am-text-center">商品ID</th>
                                                        <th class="am-text-center">图片</th>
                                                        <th class="am-text-center">名称</th>
                                                        <th class="am-text-center">规格</th>
                                                        <th class="am-text-center">本店售价</th>
                                                        <th class="am-text-center">秒杀价</th>
                                                        <th class="am-text-center">秒杀数量</th>
                                                        <th class="am-text-center">限购</th>
                                                    </tr>
                                                <?php endif;?>
                                                </thead>
                                                <tbody>
                                                <?php if(!empty($value) && is_array($value)):foreach ($value as $kk => $item):?>

                                                    <tr data-goods="" style="border-top: 1px solid rgb(204, 204, 204);">
                                                        <td style="height:67px;line-height:67px;">
                                                            <input type="hidden" name="spike[spike_goods][<?=$k?><?=$kk?>][time_point]" readonly="" value="<?=$item['time_point']?>">
                                                            <input type="text" name="spike[spike_goods][<?=$k?><?=$kk?>][store_goods_id]" readonly="" style="width:60px;border:none;outline:none;margin:9px 0;background-color:#eeeeee" value="<?=$item['store_goods_id']?>">
                                                            <input type="hidden" name="spike[spike_goods][<?=$k?><?=$kk?>][goods_img]" value="<?=$item['goods_img']?>">
                                                        </td>
                                                        <td>
                                                            <img style="width:50px;height:50px;" src="../<?=$item['goods_img']?>">
                                                            <input type="hidden" name="spike[spike_goods][<?=$k?><?=$kk?>][goods_key]" value="<?=$item['goods_key']?>">
                                                        </td>
                                                        <td>
                                                            <input type="text" style="width:120px;text-align:center;border:none;outline:none;margin:9px 0;background-color:#eee;" name="spike[spike_goods][<?=$k?><?=$kk?>][goods_name]" readonly="" value="<?=$item['goods_name']?>">
                                                        </td>
                                                        <td style="border-top: 0px; display: flex; justify-content: center; align-items: center;">
                                                            <input type="text" readonly="" style="width:150px;border:none;outline:none;background-color:#eee;margin:9px 0;" name="spike[spike_goods][<?=$k?><?=$kk?>][goods_key_name]" value="<?=$item['goods_key_name'] ? : '无规格'?>" placeholder="请选择规格" required="">

                                                        </td>
                                                        <td style="padding:18px 10px;">
                                                            <input readonly="" style="width:70px;border:none;outline:none;" type="number" value="<?=$item['goods_price']?>" name="spike[spike_goods][<?=$k?><?=$kk?>][goods_price]">
                                                        </td>
                                                        <td style="padding:18px 10px;">
                                                            <input required="" style="width:70px;border:none;outline:none;background-color:#eee;" type="number"  value="<?=$item['discount_price']?>" name="spike[spike_goods][<?=$k?><?=$kk?>][discount_price]">
                                                        </td>
                                                        <td style="padding:18px 10px;">
                                                            <input type="number" readonly required="" style="width:65px;border:none;outline:none;background-color:#eee;" value="<?=$item['goods_num']?>" name="spike[spike_goods][<?=$k?><?=$kk?>][goods_num]">
                                                        </td>
                                                        <td style="padding:18px 10px;">
                                                            <input type="number" readonly value="<?=$item['limit_num']?>" style="width:65px;border:none;outline:none;background-color:#eee;"  name="spike[spike_goods][<?=$k?><?=$kk?>][limit_num]">
                                                        </td>
                                                    </tr>
                                                <?php endforeach;endif;?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <?php endforeach;endif;?>

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
        $.post("<?=url('order/ajax_goods_price_stock')?>",{store_goods_id:store_goods_id,key:spec_item},function (re) {
            $(".specdialog input[name='stock']").val(re.data.stock);
            $(".specdialog input[name='price']").val(re.data.price);
        })
    }

    $(function () {
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
