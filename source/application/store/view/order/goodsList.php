<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/hospitalityOrders.css">
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">待客下单</div>
                                <div class="am-btn-toolbar am-fr">
                                    <div class="am-btn-group am-btn-group-xs">
                                        <button type="button" class="am-btn am-btn-default am-btn-success am-radius" href="" id="doc-prompt-toggle">
                                            <span class="am-icon-plus sweepCode"></span> 扫码下单
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <ul class="topNav nav-ul am-nav am-nav-tabs">
                                <?php if(isset($business) && !empty($business)):foreach ($business as $K=>$b):?>
                                <li onclick="getList(this)" class="<?=$K ===0 ? 'am-active' :''?>" data-bid="<?=$b['id']?>"><a href="javascript:void(0)"><?=$b['name']?></a></li>
                                <?php endforeach;endif;?>
                            </ul>
                            <div class="orderBox">
                                <ul class="orderList">

                                </ul>
                            </div>

                            <!-- 扫码下单弹框的蒙板 -->
                            <div class="dialog_mask code_mask" style=""></div>
                            <!-- 扫码下单弹框 -->
                            <div class="am-modal am-modal-prompt am-modal-no-btn" tabindex="-1" id="my-prompt">
                                <div class="am-modal-dialog" style="width: 452px;height: 252px;background-color:white;">
                                    <div class="am-modal-hd scanTitle am-text-left">
                                        <span class="codeTitle">扫码下单</span>
                                        <a href="javascript: void(0)" class="am-close am-close-spin" data-am-modal-close>&times;</a>
                                    </div>
                                    <div class="am-modal-bd">
                                        <div class="inputBox">
                                            <div class="am-form-group am-margin-bottom-lg am-margin-top">
                                                <label class="am-u-sm-4 am-u-lg-3 am-form-label">条形码：</label>
                                                <div class="am-u-sm-8 am-u-end" style="padding-left:0;padding-right:0;">
                                                    <input type="text" class="codeEnterOne tpl-form-input" id="codeEnterOne" name="" value="" autofocus="autofocus" style="padding-left:0;padding-right:0;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 点击选规格的弹框 -->
                            <div class="am-modal am-modal-no-btn" tabindex="-1" id="doc-modal-1">

                            </div>
                            <!-- 右下角购物车 -->
                            <div class="dialog am-dropdown am-dropdown-up" data-am-dropdown>
                                <form id="my-form"  class="am-form tpl-form-line-form" method="post">
                                <div class="dialogcont am-dropdown-content">
                                    <div class='topcont'>
                                        <div>加入购物车</div>
                                        <div class="delAllgoods">清空</div>
                                    </div>
                                    <div class="goodsBox">
                                        <div class="allgoodsBox">
                                        
                                        </div>
                                        <div class="countPrice">
                                            <span>共<span class="portions"></span>份</span>
                                            <span>总价<span class="totalPrice"></span>元</span>
                                        </div>
                                    </div>
                                    <div class=""></div>
                                </div>
                                <div class="dialogBtn">
                                    <div class="openClose am-btn am-dropdown-toggle" data-am-dropdown-toggle>
                                        <img class="openimg" src="upload/images/goods/cus_order/up.png" alt="">
                                        <img class="closeimg" src="upload/images/goods/cus_order/down.png" alt="">
                                        <span class="openimg">打开菜单</span>
                                        <span class="closeimg" >收起菜单</span>
                                        <img src="upload/images/goods/cus_order/cart.png" alt="">
                                    </div>
                                    <p class="numDesc">0</p>
                                    <div class="sureOrder">
                                        <button type="submit" class="am-btn am-btn-default" style="width: 200px;height: 46px;line-height: 30px;text-align: center;border: none;outline: none;background-color: #bea58c;"">确认下单</button>
                                    </div>
                                </div></form>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    //获取业务分类对应商品
    function getList(obj){
        var index = layer.load();
        var businessId = $(obj).data('bid') ? $(obj).data('bid') : 0;
        var goodsList = $('.orderBox .orderList');
        $.post("<?=url('order/lists')?>",{businessId:businessId},function (res) {

            goodsList.empty().append(res);
            layer.close(index);
        })
    }
    /**
     * 获取价格和库存
     */
    function getPriceStock(){
        var spec = [];
        $.each($(".specVal>.activeAttr"),function (k,v) {
            spec.push($(v).data('item'));
        });
        var store_goods_id = $("input[name='store_goods_id']").val();
        var spec_item = spec ? spec.join('_') : '';
        $.post("<?=url('order.setting/ajax_goods_price_stock')?>",{store_goods_id:store_goods_id,key:spec_item},function (re) {
            $(".price .priceCount").find('.count').text(re.data.stock);
            $(".price .priceCount").find('.perPrice').text(re.data.price);
            var stock=re.data.stock
            if(stock<=0){
                $('.specBox .numBox').val(0)
            }else{
                $('.specBox .numBox').val(1)
            }
        })
    }

    $(function(){

        $('#doc-prompt-toggle').on('click', function() {
            $('#my-prompt').modal({
                relatedElement: this,
                onConfirm: function(data) {
                    
                },
                onCancel: function() {
                    
                }
            });
        });
        
        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

        getList(".topNav .am-active");

        
        // 扫码枪录入事件
        // var qrCode='';
        document.onkeydown = function keyDown(e){
            if (!e) var e = window.event
            if (e.keyCode) keyCode = e.keyCode;
            else if (e.which) keyCode = e.which;
            var txtInput = document.getElementById('codeEnterOne');
            if(keyCode==13){
                window.event.returnValue = false;  //设置条形码扫描后不进行自动提交
                //获取详细信息操作
                var codeData=$('.codeEnterOne').val();
                if(!codeData){
                    $.show_error('无有效条码');
                    return false;
                }
                loadData(codeData)
            }
        }

        function loadData(edata){
            $.post("<?=url('order/add_cart')?>",{bcode:edata},function (res) {
                if(res.code != 1){
                    $.show_error(res.msg);
                    return false;
                }else{
                    var unique_count=res.data.stockPrice.stock
                    var unique_perprice=res.data.stockPrice.price

                    var unique_id='';
                    var unique_name='';
                    var unique_spec_key='';
                    var unique_spec=[];
                    var unique_uniqueCode='';

                    if(res.data.bid){
                        unique_id=""+res.data.store_goods_id
                        unique_name=res.data.goods_name
                        unique_spec_key=res.data.key
                        goodkeys=res.data.key_names.substr(1).split(':')
                        $.each(goodkeys,function(idx,val){
                            unique_spec.push($.trim(val))
                        })
                        unique_uniqueCode=res.data.id+'_'+res.data.key
                    }else{
                        unique_id=res.data.id
                        unique_name=res.data.goods_name
                        unique_spec_key='';
                        unique_uniqueCode=res.data.id+""
                    }
                    var goods={
                        count:1,
                        id:unique_id,
                        name:unique_name,
                        perPrice:unique_perprice,
                        spec:unique_spec,
                        spec_key:unique_spec_key,
                        uniqueCode:unique_uniqueCode,
                    }
                    var goodsCode=unique_uniqueCode;
                    var goodsCount=1;
                    if(goodList==''){
                        goodList.push(goods);
                        showData(goodList);
                        $('.codeEnterOne').val('')
                    }else{
                        var goodsCodes=[];
                        $.each(goodList,function(i,j){
                            goodsCodes.push(j.uniqueCode);
                        })
                        if(goodsCodes.indexOf(goodsCode)==-1){
                            goodList.push(goods);
                            showData(goodList);
                        }else{
                            var index=goodsCodes.indexOf(goodsCode);
                            goodList[index].count=goodList[index].count+goodsCount;
                            showData(goodList);
                        }
                        $('.codeEnterOne').val('')
                    }
                }
            },'JSON');
        }
        

        // 内容头部切换分类
        $('.topNav a').click(function(){
            event.preventDefault();
            $(this).parent().addClass('am-active').siblings().removeClass('am-active');
        })
        // 弹框中商品数量的减
        $(document).on('click','.am-modal .reduceNum',function(){
            var val=$('.numBox').val();
            if(val>0){
                var count = Number(val) - 1;
                $('.numBox').val(count)
            }else{
                $('.numBox').val(0)
            }
        })
        // 弹框中商品数量的加
        $(document).on('click','.am-modal .addNum',function(){
            var val = $('.numBox').val();
            var stock = parseInt($(".price .priceCount").find('.count').text());
            if(stock==''||stock<=0){
                layer.msg('暂无商品！')
            }
            if(stock>0){
                if(val<stock){
                    var count = parseInt(val) + 1;
                    $('.numBox').val(count)
                }else{
                    $('.numBox').val(stock);return false;
                }
            }
        })

        var num=0
        // 右下角显示打开菜单还是收起菜单
        $('.openClose').click(function(){
            num++
            if(num%2==0){
                $('.openimg').show()
                $('.closeimg').hide()
            }else{
                $('.openimg').hide()
                $('.closeimg').show()
            }
        })

        

        var goodsName='';
        var goodsId='';
        var goodList=[];
        // 选择规格按钮弹出选择规格框
        $(document).on('click','.orderBox .selSpec',function(){
            var _this = $(this);
            var store_goods_id = _this.data('id');
            goodsName=$(this).parent().siblings().text();
            goodsId=$(this).attr('data-id')
            $('.row .numBox').val(1);
            var index = layer.load();
            $.post("<?=url('order.setting/ajax_get_spec')?>",{store_goods_id:store_goods_id},function (rex) {
                $("#doc-modal-1").empty().append(rex);
                getPriceStock();
                layer.close(index);
            });
        });
        // 选择弹框中的规格
        $(document).on('click','.specVal>span',function(){
            $(this).addClass('activeAttr').siblings().removeClass('activeAttr');
            $('.row .numBox').val(1);
            getPriceStock();
        });

        // 添加到购物车中
        $(document).on('click','.addCart',function(){
            var goodsNum = $('.specBox .count').text()
            if(goodsNum<=0||goodsNum==''){
                $.show_error('库存不足！');
            }else{
                var numBox=$('.specBox .numBox').val()
                if(numBox==0){
                    layer.msg('请选择商品数量！');
                }else{
                    $('.allgoodsBox').empty();
                    var goods={};
                    var goodsInfo=[];
                    var specCode=''
                    $('.activeAttr').each(function(v,k){
                        var spec=$(this).get(0).innerText
                        var specId=$(this).attr('data-item')
                        goodsInfo.push(spec)
                        specCode=specCode+'_'+specId
                    })
                    var goodsCount=Number($('.row .numBox').val())
                    var pergoodsPrice=$('.perPrice').text()
                    var goodsCode=goodsId+'_'+specCode.substr(1)
                    goods.id=goodsId;
                    goods.name=goodsName;//名称
                    goods.count=goodsCount;//数量
                    goods.perPrice=pergoodsPrice;//单价
                    goods.spec=goodsInfo;//规格名称
                    goods.uniqueCode=goodsCode;
                    goods.spec_key = specCode.substr(1);
                    if(goodList==''){
                        goodList.push(goods)
                        showData(goodList)
                    }else{
                        var goodsCodes=[]
                        $.each(goodList,function(i,j){
                            goodsCodes.push(j.uniqueCode)
                        })
                        if(goodsCodes.indexOf(goodsCode)==-1){
                            goodList.push(goods)
                            showData(goodList)
                        }else{
                            var index=goodsCodes.indexOf(goodsCode)
                            goodList[index].count=goodList[index].count+goodsCount
                            showData(goodList)
                        }
                    }
                }
            }
        })

        // 购物车中商品数量的减
        $(document).on('click','.allgoodsBox .reduceNum',function(){
            var count=Number($(this).parent().children('.numBox').val())
            var idn=$(this).attr('idn')
            if(count>1){
                var countnum=Number(count)-1
                $(this).parent().children('.numBox').val(countnum)
                goodList[idn].count-=1;
                showData(goodList);
                return false;
            }else{
                $(this).parent().children('.numBox').val(1)
            }
        })
        // 购物车中商品数量的加
        $(document).on('click','.allgoodsBox .addNum',function(){
            var count=Number($(this).parent().children('.numBox').val());
            var idn=$(this).attr('idn');
            goodList[idn].count++;
            showData(goodList);
            return false;
        })
        // 购物车中删除每一个商品
        $(document).on('click','.delPer',function(){
            var idn=$(this).attr('idn');
            var inx=0;
            $.each(goodList,function(id,row){
                if(id==idn){
                    inx=id
                }
            })
            goodList.splice(inx,1)
            showData(goodList)
            return false;//组织事件继续向上冒泡而触发隐藏事件
        })

        $('.countPrice').hide()
        function showData(val){
            $('.allgoodsBox').empty();
            var totalPrice=0;
            var portions=0;
            if(val==''){
                $('.countPrice').hide()
            }else{
                $('.countPrice').show()
            }
            $.each(val,function(v,k){
                var spanList=[];
                var spec_name = [];
                // 循环遍历规格数组
                $.each(val[v].spec,function(i,j){
                    spec_name.push(j);
                    var perSpec=$('<span></span>').text(j);
                    spanList.push(perSpec);
                })
                var goodspec=$('<div></div>').addClass('goodspec').html(spanList)
                var goodsTitle=$('<div></div>').addClass('goodsTitle').html(k.name)
                var goodsData=$('<div></div>').addClass('goodsData');

                goodsData.append([goodsTitle,goodspec,"<input name='cart[store_goods_name][]' value='"+k.name+"' type='hidden'>","<input name='cart[store_goods_id][]' value='"+k.id+"' type='hidden'>","<input name='cart[store_goods_spec_key][]' value='"+k.spec_key+"' type='hidden'>","<input name='cart[store_goods_price][]' value='"+k.perPrice+"' type='hidden'>","<input name='cart[store_goods_spec_name][]' value='"+spec_name.join(':')+"' type='hidden'>"])
                var reduceBtn=$('<button type="button"></button>').addClass('reduceNum').html('-').attr('idn',v)//减号
                var numInput=$('<input type="text" name="cart[store_goods_number][]" style="border-top: 1px solid rgb(196, 196, 196);width:40px;height:20px;text-align:center;padding:0;">').addClass('numBox').val(k.count)//数量输入框

                var addBtn=$('<button type="button"></button>').addClass('addNum').html('+').attr('idn',v)//加号
                var specVal=$('<div></div>').addClass('specVal').html('')
                var shopNum=$('<div></div>').addClass('shopNum').html('')
                shopNum.append(specVal.append([reduceBtn,numInput,addBtn]))
                var priceSpan=$('<span></span>').text(k.perPrice)
                var price=$('<div></div>').addClass('price').html(priceSpan)
                var delBtn=$('<div></div>').addClass('delPer').html('删除').attr('idn',v)//加号
                var delBox=$('<div></div>').addClass('delBtn').html(delBtn)
                var pergoodsBox=$('<div></div>').addClass('pergoodsBox')

                totalPrice=(Number(totalPrice)+parseFloat(k.perPrice)*parseInt(k.count)).toFixed(2)
                portions=Number(portions)+Number(k.count)

                pergoodsBox.append([goodsData,shopNum,price,delBox])
                $('.allgoodsBox').prepend(pergoodsBox)

            })
            $('.totalPrice').text(totalPrice)
            $('.portions').text(portions)
            $('.numDesc').text(portions)
        }
        // 清空购物车商品
        $('.delAllgoods').click(function(){
            goodList=[];
            $('.allgoodsBox').empty()
            showData(goodList)
        })
    })
</script>