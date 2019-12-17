<style>
    .laydate-time-list>li{width:50%!important;}
    .laydate-time-list>li:last-child { display: none;}
</style>
<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/orderPay.css">
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加活动</div>
                            </div>

                            <div>
                                <div class="am-u-sm-8 am-u-lg-8">
                                    <div class="am-form-group">
                                        <label class="am-u-sm-4 am-u-lg-3 am-form-label form-require">选择店铺：</label>
                                        <div class="am-u-sm-8 am-u-end">
                                            <input  type="text" name="order[phone]" id="select_input" class="select-input" value="" autocomplete="off" placeholder="请输入店铺名" />
                                            <div id="search_select" class="search-select">
                                                <ul id="select_ul" class="select-ul" style="display:none;border: 1px solid rgb(236, 236, 236);min-width: 350px;max-height: 200px;overflow-y: scroll;">
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="am-form-group">
                                        <label class="am-u-sm-4 am-u-lg-3 am-form-label form-require">活动名称：</label>
                                        <div class="am-u-sm-8 am-u-end">
                                            <input type="text" class="tpl-form-input" name="spike[name]"
                                                value="" placeholder="请输入活动名称" required>
                                        </div>
                                    </div>

                                    <div class="am-form-group am-margin-bottom-lg">
                                        <label class="am-u-sm-4 am-u-lg-3 am-form-label form-require">活动时间：</label>
                                        <div class="am-u-sm-8 am-u-end">
                                            <input type="text" id="spike_end_time" class="tpl-form-input" name="" value="" placeholder="请选择时间范围" required>
                                            <small>注：如2012/11/11 08:22 - 2012/09/21 13:35</small>
                                        </div>
                                    </div>

                                    <div class="am-form-group">
                                        <label class="am-u-sm-4 am-u-lg-3 am-form-label form-require">活动类型：</label>
                                        <div class="am-u-sm-8 am-u-end">
                                            <select name="goods_status" data-am-selected="{btnSize: 'sm', placeholder: '活动类型'}">
                                                <option value="0">活动类型</option>
                                                <option value="1">上架</option>
                                                <option value="2">下架</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="am-form-group">
                                        <label class="am-u-sm-4 am-u-lg-3 am-form-label">活动状态：</label>
                                        <div class="am-u-sm-8 am-u-end">
                                            <label class="am-radio-inline">
                                                <input type="radio" name="spike[status]" value="1" data-am-ucheck checked>
                                                开启
                                            </label>
                                            <label class="am-radio-inline">
                                                <input type="radio" name="spike[status]" value="0" data-am-ucheck>
                                                <span class="am-link-muted">关闭</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="am-form-group">
                                        <label class="am-u-sm-4 am-u-lg-3 am-form-label">已报人数：</label>
                                        <div class="am-u-sm-8 am-u-end">
                                            <input type="text" class="tpl-form-input" name=""
                                                value="" placeholder="">
                                        </div>
                                    </div>

                                    <div class="am-form-group">
                                        <label class="am-u-sm-4 am-u-lg-3 am-form-label">限制人数：</label>
                                        <div class="am-u-sm-8 am-u-end">
                                            <input type="text" class="tpl-form-input" name=""
                                                value="" placeholder="" >
                                        </div>
                                    </div>

                                    <div class="am-form-group">
                                        <label class="am-u-sm-4 am-u-lg-3 am-form-label form-require">商品详情：</label>
                                        <div class="am-u-sm-8 am-u-end">
                                            <!-- 加载编辑器的容器 -->
                                            <textarea id="container" name="goods[content]" type="text/plain"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="am-u-sm-4 am-u-lg-4">
                                    <ul id="show_shopname_ul" class="show_shopname_ul">

                                    </ul>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <div class="am-u-sm-8 am-u-sm-push-3 am-margin-top-lg">
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
<script src="assets/common/plugins/umeditor/umeditor.config.js?v=<?= $version ?>"></script>
<script src="assets/common/plugins/umeditor/umeditor.min.js"></script>
<script src="assets/common/plugins/laydate/laydate.js"></script>
<script src="assets/store/js/select.data.js"></script>
<script>
    $(function () {

        $(document).on('keyup','#select_input',function(){
            var _this = $(this);
            var shopname = _this.val();
            if(shopname.length >= 2){
                $('.select-ul').show().css({"border":"1px solid #ececec"});
                var _html = '';
                var shopNames = [
                    {
                        name:'测试南京中华门站点',
                        id:123
                    },{
                        name:'赤兔创业咖啡--上海环球店',
                        id:124
                    },{
                        name:'赤兔创业咖啡--上海徐汇区万科中心店',
                        id:125
                    },{
                        name:'浙里美农副产品--衢州市旗舰店',
                        id:126
                    },{
                        name:'艾美睿@小站--浙江衢州柯城吾悦四楼',
                        id:126
                    },{
                        name:'艾美睿生活--浙江衢州吾悦生活馆',
                        id:127
                    },{
                        name:'浙里美--衢州西区新华书店体验店',
                        id:128
                    },{
                        name:'艾美睿小站--衢州柯城下街店',
                        id:129
                    },{
                        name:'艾美睿@生活--衢州柯城上街店',
                        id:130
                    },{
                        name:'艾美睿@小站--浙江衢州东港海力大道店',
                        id:131
                    },{
                        name:'浙江衢州衢江东港海力大道6号便利店（潘式家具公司内）',
                        id:132
                    },
                ]
                $.each(shopNames,function (k,v) {
                    if(v.name.indexOf(shopname)!=-1){
                        _html += '<li style="font-size:14px;" data-id='+v.id+'>'+v.name+'</li>';
                    }
                });
                $('.select-ul').empty().append(_html);
            }else{
                $('.select-ul').hide().empty();
            }
        });

        var shop_list=[]
        $(document).on('click','.select-ul li',function(){
            var txt = $(this).text();
            var shopObj={}
            shopObj.name=txt
            shopObj.id=$(this).attr('data-id')
            shop_list.push(shopObj)
            show_shop_name(shop_list)
        })

        function show_shop_name(val){
            $('#show_shopname_ul').empty()
            $.each(val,function(v,k){
                $('.select-ul').hide();
                var shopname_span=$('<span></span>').css({'font-size':'14px',}).text(k.name)
                var delimg=$('<img class="delimg" src="upload/images/goods/cus_order/chahao.png">').css({'width':'20px','height':'20px','position':'absolute','top':'0','right':'-10px','z-index':'10',}).attr('delimg-id',k.id)
                var divBox=$('<div></div>').css({'padding':'0 5px','overflow':'hidden','text-overflow':'ellipsis','white-space':'nowrap','height':'30px','line-height':'30px','border':'1px solid #3bb4f2','color':'#3bb4f2',}).append([shopname_span,delimg])
                var liBox=$('<li></li>').css({'padding':'10px 0','position':'relative'}).append(divBox)
                $('#show_shopname_ul').append(liBox)
                $('#select_input').val('')
            })
        }

        $(document).on('click','.delimg',function(){
            var delimg_id=$(this).attr('delimg-id');
            var inx=0;
            $.each(shop_list,function(id,row){
                if(id==delimg_id){
                    inx=id
                }
            })
            shop_list.splice(inx,1)
            show_shop_name(shop_list)
        })

        // 富文本编辑器
        UM.getEditor('container', {
            initialFrameWidth: 375 + 15,
            initialFrameHeight: 600
        });

        laydate.render({
            elem: '#spike_start_time'
            ,type: 'datetime'
            ,format:'yyyy-MM-dd HH:mm'
    
        });
        laydate.render({
            elem: '#spike_end_time'
            ,type: 'datetime'
            ,format:'yyyy/MM/dd HH:mm'
            ,range:true
            ,done: function(value, date, endDate){
                console.log(value); //得到日期生成的值，如：2017-08-18
                console.log(date); //得到日期时间对象：{year: 2017, month: 8, date: 18, hours: 0, minutes: 0, seconds: 0}
                console.log(endDate); //得结束的日期时间对象，开启范围选择（range: true）才会返回。对象成员同上。
            }
        });
    });
</script>
