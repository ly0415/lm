<link rel="stylesheet" href="assets/admin/css/set_attribute.css">
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title a m-cf">控制管理</div>
                </div>
                <div class="widget-body am-fr">
                    <form action="" class="am-form am-form-horizontal tpl-form-line-form" method="post">
                        <div class="am-form-group">
                            <div style="font-size: 1.5rem;">领券中心</div>
                            <div class="am-margin-top">
                                <div class="am-u-sm-3 am-text-right form-require am-form-label">注册新用户</div>
                                    <div class="am-u-sm-9 am-form-label am-text-left" id="recharge">
                                            <input type="text" value="30">天内可领取&nbsp;&nbsp;
                                            <input type="text" value="5"/>元抵扣券（&nbsp;券名称： 
                                            <input type="text" value=""/>&nbsp;）&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            <label class="am-radio-inline">
                                                <input type="radio" name="sms[engine][aliyun][order_pay][is_enable]" value="1" data-am-ucheck required>开启
                                            </label>
                                            <label class="am-radio-inline">
                                                <input type="radio" name="sms[engine][aliyun][order_pay][is_enable]" value="0" data-am-ucheck checked>关闭
                                            </label>
                                    </div>
                            </div>   
                        </div>
                        <hr/>
                        <div class="am-form-group">
                            <div style="font-size: 1.5rem;">门店下单开关设置</div>
                            <div class="am-margin-top">
                                <div class="am-u-sm-3 am-text-right form-require am-form-label">选择关闭下单的店铺</div>
                                <div class="am-u-sm-6 am-u-end">
                                    <div class="select-content am-form-group">
                                        <input type="hidden" name="newMachineId" id="newMachineId0">
                                        <input type="text" name="select_input0" id="select_input0" class="select-input" value="" autocomplete="off" placeholder="请输入要关闭的店铺" />
                                        <div id="search_select0" class="search-select">
                                            <ul id="select_ul0" class="select-ul"> 
                                            </ul>
                                        </div>
                                    </div>
                                    <ul class='shopsContent' id="shopsContent0">
                                    </ul>
                                </div>
                            </div>   
                        </div>
                        <hr/>
                        <div class="am-form-group">
                            <div style="font-size: 1.5rem;">门店支付开关设置</div>
                            <div class="am-margin-top">
                                <div class="am-u-sm-3 am-text-right form-require am-form-label">选择关闭余额支付的店铺</div>
                                <div class="am-u-sm-6 am-u-end">
                                    <div class="select-content am-form-group">
                                        <input type="hidden" name="newMachineId" id="newMachineId1">
                                        <input type="text" name="select_input1" id="select_input1" class="select-input" value="" autocomplete="off" placeholder="请输入要关闭的店铺" />
                                        <div id="search_select1" class="search-select">
                                            <ul id="select_ul1" class="select-ul"> 
                                            </ul>
                                        </div>
                                    </div>
                                    <ul class='shopsContent' id="shopsContent1">
                                    </ul>
                                </div>
                            </div>   
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
 var tempArr = [
                 {shopName: '艾美睿®小站--浙江衢州柯城道前街店',shopId: '001'},
                 {shopName: '艾美睿小站-衢州柯城下街店',shopId: '002'},
                 {shopName: '艾美睿®生活--浙江衢州工厂店',shopId: '003'},
                 {shopName: '艾美睿®生活--浙江衢州柯城上街店',shopId: '010'},
                 {shopName: '艾美睿®书吧--浙江衢州柯城万达三楼',shopId: '011'}, 
                 {shopName: '艾美睿®小站--浙江衢州东港海力大道店',shopId: '010'}
               ] 
             

for(var i=0;i<2;i++){
    searchInput(tempArr,i);
    $('#shopsContent'+i).on('click','.insideI',function(){
    $(this).parent().remove();
})
}


function newOptions(tempArr,index){
    //遍历数据，判断输入框的文本内容在数组中是否存在，存在则将该数组元素push到新数组中
    var listArr = [];
    tempArr.forEach(function(v,i){
        if(v.shopName.indexOf($('#select_input'+index).val())>-1){
            listArr.push(v);
        }
    })
    //遍历新数组，将数组中的元素以dom的形式插入ul标签里
    var options = '';
    listArr.forEach(function(val,idx){
        var opt = '<li class="li-select" data-newMachineId="' + val.shopId + '">' + val.shopName + '</li>';
        options +=opt;
    })
    
    //判断列表中有无数据，没有则隐藏ui列表，有就显示列表，并且将options加入到列表
    if(options == ''){
        $('#search_select'+index).hide();
    }else{
        $('#search_select'+index).show();
        $('#select_ul'+index).html('').append(options);
    }
}

function searchInput(tempArr,index){
    //鼠标按下触发方法
    $('#select_input'+index).on('keyup',function(){
        newOptions(tempArr,index);
    });
    //input框获取焦点触发（鼠标点击）：下拉框显示，调用newOptions方法
    $('#select_input'+index).on('focus',function(){
        $('#search_select'+index).show();
        newOptions(tempArr,index);
    });


    $('#select_ul'+index).delegate('.li-select', 'click',function(){
        $('#select_ul'+index+' .li-select').removeClass('li-hover');
        var selectText = $(this).html();
        var newMachineIdVal = $($(this)[0]).attr("data-newMachineId");
        $('#select_input'+index).val(selectText);
        var html = '<li>'+ $('#select_input'+index).val()+' <i class="iconfont icon-shanchu1 insideI"></i></li>';
        $('#shopsContent'+index).append(html);                           
        $('#search_select'+index).hide();
        $("#newMachineId"+index).val(newMachineIdVal);
    });

    //鼠标移入移出事件（选择框）
    $('#search_select'+index).on('mouseover',function(){
        $(this).addClass('ul-hover');
    });
    $('#search_select'+index).on('mouseout',function(){
        $(this).removeClass('ul-hover');
    });
    //input框失去焦点
    $('#select_input'+index).on('blur',function(){
        if($('#search_select'+index).hasClass('ul-hover')){
            $('#search_select'+index).show();
        }else{
            $('#search_select'+index).hide();
        }
    });

    $('#select_ul'+index).delegate('.li-select', 'mouseover',function(){
        $('#select_ul'+index+' .li-select').removeClass('li-hover');
        $(this).addClass('li-hover');
    });

}

 
 
 

 

</script>

