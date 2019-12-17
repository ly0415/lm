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
                                        <a class="codeOrder am-btn am-btn-default am-btn-success am-radius" data-am-modal="{target: '#doc-modal-2'}" href="">
                                            <span class="am-icon-plus"></span> 扫码下单
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <ul class="topNav nav-ul am-nav am-nav-tabs">
                                <li class="am-active"><a href="#">咖啡兰蜜水</a></li>
                                <li><a href="#">果茶兰蜜水</a></li>
                                <li><a href="#">店长推荐</a></li>
                                <li><a href="#">茶饮兰蜜水</a></li>
                                <li><a href="#">奶茶兰蜜水</a></li>
                                <li><a href="#">店长推荐</a></li>
                            </ul>
                            <div class="orderBox">
                                <ul class="orderList">
                                    <li>
                                        <div class="imgBox">
                                            <img src="http://cn.bing.com/az/hprichv/LondonTrainStation_GettyRR_139321755_ZH-CN742316019.jpg" />
                                        </div>
                                        <div class="bottomPart">
                                            <div class="goodsName">冰果兰蜜水洒大苏打</div>
                                            <div class="priceBox">
                                                <span>￥28.00</span>
                                                <button class="selSpec" type='button' data-am-modal="{target: '#doc-modal-1'}">选规格</button>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="imgBox">
                                            <img src="http://cn.bing.com/az/hprichv/LondonTrainStation_GettyRR_139321755_ZH-CN742316019.jpg" />
                                        </div>
                                        <div class="bottomPart">
                                            <div class="goodsName">冰果兰蜜水解决阿斯顿啥的阿代阿打</div>
                                            <div class="priceBox">
                                                <span>￥28.00</span>
                                                <button class="selSpec" type='button' data-am-modal="{target: '#doc-modal-1'}">选规格</button>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <!-- 扫码下单弹框 -->
                            <div class="am-modal am-modal-no-btn" tabindex="-1" id="doc-modal-2">
                                <div class="am-modal-dialog" style="width:450px;height:450px;overflow:auto;border-top:3px solid #ffa627;">
                                    <div class="am-modal-hd">
                                        <a href="javascript: void(0)" class="am-close am-close-spin" data-am-modal-close>&times;</a>
                                    </div>
                                    <div class="am-modal-bd" style="position: relative;">
                                        <div class="specBox">
                                            <div class="row">
                                                <div class="specName">产品规格：</div>
                                                <div class="specVal">
                                                    <span class="activeAttr">R(500ml)</span>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="specName">温度选择：</div>
                                                <div class="specVal">
                                                    <span class="activeAttr">标准冰</span>
                                                    <span>去冰结婚后</span>
                                                    <span>少冰</span>
                                                    <span>不加冰</span>
                                                </div>
                                            </div>
                                            <div class="price">
                                                <div class="priceCount">
                                                    <span>库存 : </span>
                                                    <span class="count">1532</span>
                                                </div>
                                                <div class="priceCount">
                                                    <span>单价 : </span>
                                                    <span class="perPrice">18.00</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="btnBox">
                                            <button type="button" class="addCart" data-am-modal="{target: '#doc-modal-1'}">添加到购物车</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 点击选规格的弹框 -->
                            <div class="am-modal am-modal-no-btn" tabindex="-1" id="doc-modal-1">
                                <div class="am-modal-dialog" style="width:450px;height:450px;overflow:auto;border-top:3px solid #ffa627;">
                                    <div class="am-modal-hd">
                                        <a href="javascript: void(0)" class="am-close am-close-spin" data-am-modal-close>&times;</a>
                                    </div>
                                    <div class="am-modal-bd" style="position: relative;">
                                        <div class="specBox">
                                            <div class="row">
                                                <div class="specName">产品规格：</div>
                                                <div class="specVal">
                                                    <span class="activeAttr">R(500ml)</span>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="specName">温度选择：</div>
                                                <div class="specVal">
                                                    <span class="activeAttr">标准冰</span>
                                                    <span>去冰结婚后</span>
                                                    <span>少冰</span>
                                                    <span>不加冰</span>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="specName">加料选择：</div>
                                                <div class="specVal">
                                                    <span class="activeAttr">不加料</span>
                                                    <span>红豆</span>
                                                    <span>珍珠</span>
                                                    <span>椰果</span>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="specName">数量：</div>
                                                <div class="specVal">
                                                    <button type="button" class="reduceNum">-</button>
                                                    <input class="numBox" type="text" style="border-top: 1px solid rgb(196, 196, 196);width:80px;text-align:center;" value="1">
                                                    <button type="button" class="addNum">+</button>
                                                </div>
                                            </div>
                                            <div class="price">
                                                <div class="priceCount">
                                                    <span>库存 : </span>
                                                    <span class="count">1532</span>
                                                </div>
                                                <div class="priceCount">
                                                    <span>单价 : </span>
                                                    <span class="perPrice">18.00</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="btnBox">
                                            <button type="button" class="addCart" data-am-modal="{target: '#doc-modal-1'}">添加到购物车</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 右下角购物车 -->
                            <div class="dialog am-dropdown am-dropdown-up" data-am-dropdown>
                                <div class="dialogcont am-dropdown-content">
                                    <div class='topcont'>
                                        <div>加入购物车</div>
                                        <div class="delAllgoods">清空</div>
                                    </div>
                                    <div class="goodsBox">

                                    </div>
                                    <div class=""></div>
                                </div>
                                <div class="dialogBtn">
                                    <div class="openClose am-btn am-dropdown-toggle" data-am-dropdown-toggle>
                                        <img class="openimg" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwEAYAAAAHkiXEAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAAAgY0hSTQAAeiYAAICEAAD6AAAAgOgAAHUwAADqYAAAOpgAABdwnLpRPAAAAAZiS0dEAAAAAAAA+UO7fwAAAAlwSFlzAAAASAAAAEgARslrPgAABkZJREFUeNrtXElPFFsUrtsKJA4JDaK2uNEozggrZYkbhxgVTTS6cgNiAANBY0yM0bhVUBdqgui/0LixdaOggIobGxyWEjssGAWH+73FqdOPKm8NPVTfgvfO5ktVbp17znfuPJQwQiIAAKxeTU979hDu2kW4eTPh+vWEpaWEy5ZZtUxOEo6OEn75QvjxI2FPD2E8LoQQQoyM6PY77wIJCbliBeG5c4Rv3kCLvH5N2NLCdunmJyDC164lR2/fJpya0kO4l7Bdt24RlpcHzY8IhvDCQkMYwhDt7fT28mXCJUvcv56dJXz5kjAeJ/zwgTCRIBwZMWDAADc5QlB+S5fScyxGWFFBWFlJ6WtrKV1NDb0vKnJ2xICBqSlKf/06PXd0iIiIiMivXzmPRNbEAwA2bSJ8985fievro4DV19NzcXF+7CwuJmxooPwHBjxNlZCQb98ScmBDIGTdsWOEExPuDgwMEO7dq9tutR/79/srQOPjhHV1mg0+e5bwzx+1odPTqc4WALBokW6iPf2SkJCLF5O9bW2EP36o/fv9m9KfOZM/Ay3EO0kiQYZVVuomNDf+VlURDg+71/AAA2FtahQlXkJC9vTQA4/TF46khs8AgN5exxqR66aJFHLnqmjjmXgJCWmfGC08YT/dAzE+nnVnzcNJUujUKSUSC7XE++OHa8TQkLpg8qipoCD9DAAAly6piedOqapKNxG6hQjesYP4mJ5WB+LChTQV8sxVMWM1RzW6HQ+bEDk8arILN91r1vhUxEsGdnn/nodpuh0Om6SGr9z0KAvuzZseCrhNcyr54ZtAhU2IrAMH1PxNTjr2mdYJk136+nQ7Nl+E+BLCcYlDQkI2Nzt82Nen/qC+Xrdjanu3bmXUbc9f9pkTM3WB7u21ORKLmWxLa8KZGcLgF8k8HQIARCLkWFeXuqDw+0gkHPZGo4SzszZjJdm7apWZ8NQpdaSePQuHI0IQ3r8PX/LoUbgC8eKF2s4TJ0wDeevPLs+f6zWcCezuJvS71nL6NOGDB+EIBO9r2KWmxnT06VN1hA4fzrepqaYGAPDwoWMh5ybHqUmyCOvJfyAo37o6tV1PnpidxefP6gRbtoSWeEt6l75BcyAov23b1PYMD5sJRkfVCUpK8mOgjzZeQkJ2dzsRaNVz9657IPLXR1jnV3Z/kknTcHsvzQkKC/ND/L17mRKfXSCCrxFkf1GROv+ZmbwHICjiwxoInwEIvgnKF/FhCwT5U1am9jOZNBN8+qQ2KPtOmB2yEhs88WGxg/R4dsK5H4b6LvEBlbyw2EXfHz2qDvTjxx7Lz1euZJyx17Aw4BLvjxi/NaKrK7t8rl5VK+/sNBOcPKnO2GkG50V8ZWVYSrzvQFjscgrE9u2Z6Xdaijh+PHUq2cwl68U4Ss+LUN++WfWFZWnAKxBsJwv7kS4PJSWETotxK1faPuDTwnZpaMjMoWg005KjW6x7vemvBtN3jY3qmvTqlcMHLS3qAPT36yZkvgjxxRsyiq1JAEBTk8OHpaXWrTO77Nun28GwC/F38KC65LtsSaYUAAA6O9UBGBz8f1NeLdYzpYpzVBIS8sYNb0UAgPJy95rQ2qrb4bAJ8XX+vJovPk3NV7D8KAQAXLyoVsgHs6qrdTuuW6zDbsXBLAAAX1RJW3FBgfsFhqGhBXu3yhc/ZWXuSzn9/RkfTbRmVFFhrUp26e39bx7OdRq2j41Rug0bcpcxgH+31vgYtlMgFl6NsK5qOhHPvBw6FJwhAICGBrgKN007d+omLjf+Vle7NzU8s83j+SlrIJxqBHfWbW3zZfhqHU62t1v9UJR43QfXyJAjR9z7CBYeH/PZSZHza7KZ2c8zV55ADQ66+zE2FnhTk7YjEhJy48b0rn3yrUk+wheN5odwXiRrbHRfMrALj2py17kGdFGbh12trXTRmfcVvEZJP38S8j8d+GTe4CDh0BAhX9SemJjjiTDE8uX0Phaj5zkXtQ3D+PsfFF573qz/2jXSe+dOaC9qOwmVnFiMAtPR4T7D1ihsl3mOn16mMXOdL0KO8aJfc7P1dqV9PyIwtiUvC9NzUxNh8Oeg7KK9E0wFhjcohCEMUVtLb3fvJuTf1axbR1hWRuj0u5pkkvDrV8I5v6uBAQPxODUl37/r9vsfrrKqcz0QAYMAAAAldEVYdGRhdGU6Y3JlYXRlADIwMTktMDgtMjJUMTQ6MTU6MjUrMDg6MDAhlyp/AAAAJXRFWHRkYXRlOm1vZGlmeQAyMDE5LTA4LTIyVDE0OjE1OjI1KzA4OjAwUMqSwwAAAEh0RVh0c3ZnOmJhc2UtdXJpAGZpbGU6Ly8vaG9tZS9hZG1pbi9pY29uLWZvbnQvdG1wL2ljb25fMzB3a3phOXpkNi9zaG91cWkuc3ZnsCuRHAAAAABJRU5ErkJggg==" alt="">
                                        <img class="closeimg" src="data:image/png;base64,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" alt="">
                                        <span class="openimg">打开菜单</span>
                                        <span class="closeimg" >收起菜单</span>
                                        <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwEAYAAAAHkiXEAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAAAgY0hSTQAAeiYAAICEAAD6AAAAgOgAAHUwAADqYAAAOpgAABdwnLpRPAAAAAZiS0dEAAAAAAAA+UO7fwAAAAlwSFlzAAAASAAAAEgARslrPgAAA/JJREFUeNrtm7tLHEEcx3+zauMLg2AhEh8Y0Ag+OktB2xgQsdB/wlJIIVqYykKxFQsbTSEogoVFQHyCxheiSaEnGFEMPoKCnrq/FF9HWZO7c3dnd/bifpovczuz93vs/mZ2bo8oJCQkRBsi1gFmZuY3b9CSmojzcyGEEOL0VLdjSQsC39UFvb9nW8j+fX1QIdxb9EqQVzzUNO0FPhYfP+r2K+gY1qa8YlVduU1Nuh0MOo8JeKzdTEy8tOT6zExMXFmp28GkA6WjvBy6v++uBP3+rdufpAeBzMmxzhFSW1peloj8fN1+BBXHtd4a2J8/4/ceHoYeHup22F+iUejODnRsDKX++lr2cD3ZIhHHx2jl5el2ObAwMfHqKhq1tcIQhjCiUcPdWSXyxCExESRI1NSgIfWvZahTwgTY46lkK0rA2ppul5KDb99Qeg4O5CfuE2CpbSHx+fLl+SeKJmHjIZHn59CsLN2uBgtmaEkJVkGRiDzi+g7ACU0TrY0N3a4Gk7m554GXKJoDJGEp+jcjI7GOhAnwlNtbzJF/136J4gSsr+t2OVhMT2PVc3ISq4fiBGxtQe/udLuuHSYmjl16JMoSYN3j2N7W7b9ebm6gExOJeiq+AySvvRRNTqL0XFwk6ulRAl7xkzETE/f3v7R7mAClDAzgyp+ZeekI5W8t4Mk4NxetX7/i915YgG5u+hckVY4SE19cYJfz61fMgVNTus16si/RT5omm2z29uq2UzcelSBJnAczQYLE+/e6A6AbfQkgIqKKCt0B0E2qt6dfXo5/vKAA9ai1FTX19lZ3QOxxdIQ7eXYWc4Dc9QwAqPHZ2QhwNKrmTbsAYrLJ5uCg0zh5VoKwHJPvBY2O+n8J+IQgQaKhwelwj+cAeliudXejkfjJMDlxvvXi29vLuF/LytBqb4cWFyNBhvcXgioECXr8AWpvD/Z//ow7fn9ft3m2QQ3NzISmerwoCHm4E9raoJGIdUa7voaOjUFLSjy3x2STzbQ0fN+nT9AfP6BXV9CdHWhHh+yvO44OHe3psbfEOD7GuLdvldvDzMyGAZ2YsGfX+Dg0Cf6AggAWFsLguzu/l3nx7Wpudrb2lLj//4P3k58gQaKuDo2UFGfj6+u9saux0d1JPnxwa4ZPqw+3k6tXNTc93fFQJibOyHBrgU8JmJ9/sNr+ozoTE8/NKTeJiYkXFx2PFyRIuBjvN6iZQ0P2av/lJRrqN+2sWyV2/wkUiWB8Er0BaF3udXZCb27+7eDKCvpXVflj17t3+N7l5fiBX1pC/9JSVd+vbRklr0Dr7wJnZ9hV/P7dd3uY+WlZWV0NLSpCqdrdxZPua3/ZICQk5D/jDz3plMrinfSAAAAAJXRFWHRkYXRlOmNyZWF0ZQAyMDE5LTA4LTIyVDE0OjI4OjM2KzA4OjAwsMndzQAAACV0RVh0ZGF0ZTptb2RpZnkAMjAxOS0wOC0yMlQxNDoyODozNiswODowMMGUZXEAAABQdEVYdHN2ZzpiYXNlLXVyaQBmaWxlOi8vL2hvbWUvYWRtaW4vaWNvbi1mb250L3RtcC9pY29uX2JvNTljMnJ1dHNyL2dvdXd1Y2hlLWNvcHkuc3ZnjeZkiQAAAABJRU5ErkJggg==" alt="">
                                    </div>
                                    <div class="sureOrder">确认下单</div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
        // 顶部扫码下单按钮
        $('.codeOrder').click(function(event){
            event.preventDefault()
        })

        // 内容头部切换分类
        $('.topNav a').click(function(){
            event.preventDefault()
            $(this).parent().addClass('am-active').siblings().removeClass('am-active')
        })
        // 弹框中商品数量的减
        $('.reduceNum').click(function(){
            var val=$('.numBox').val()
            if(val>1){
                var count = Number(val) - 1;
                $('.numBox').val(count)
            }else{
                $('.numBox').val(1)
            }
        })
        // 弹框中商品数量的加
        $('.addNum').click(function(){
            var val=$('.numBox').val()
            var count = Number(val) + 1;
            $('.numBox').val(count)
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
        var goodList=[];
        // 选择规格按钮弹出选择规格框
        $('.selSpec').click(function(){
            goodsName=$(this).parent().siblings().text()
            $('.row .numBox').val(1)
        })
        // 选择弹框中的规格
        $('.specVal>span').click(function(){
            $(this).addClass('activeAttr').siblings().removeClass('activeAttr')
        })

        // 添加到购物车中
        $('.addCart').click(function(){
            $('.dialogcont>.goodsBox').empty()
            var goods={}
            var goodsInfo=[];
            // v是索引，k是选中的每个jquery对象
            $('.activeAttr').each(function(v,k){
                var spec=$(this).get(0).innerText
                goodsInfo.push(spec)
            })
            var goodsCount=$('.row .numBox').val()
            var pergoodsPrice=$('.perPrice').text()
            goods.name=goodsName//名称
            goods.count=goodsCount//数量
            goods.perPrice=pergoodsPrice//单价
            goods.spec=goodsInfo//规格名称
            goodList.push(goods)

            showData(goodList)
        })

        // 购物车中商品数量的减
        $(document).on('click','.goodsBox .reduceNum',function(){
            var count=$(this).parent().children('.numBox').val()
            if(count>1){
                var countnum=Number(count)-1
                $(this).parent().children('.numBox').val(countnum)
            }else{
                $(this).parent().children('.numBox').val(1)
            }
        })
        // 购物车中商品数量的加
        $(document).on('click','.goodsBox .addNum',function(){
            var count=$(this).parent().children('.numBox').val()
            var countnum=Number(count)+1
            $(this).parent().children('.numBox').val(countnum)
        })
        // 购物车中删除每一个商品
        $(document).on('click','.delPer',function(){
            $('.dialogcont>.goodsBox').empty()
            var idn=$(this).attr('idn')
            var inx;
            $.each(goodList,function(id,row){
                if(id===idn){
                    inx=id
                }
            })
            goodList.splice(inx,1)
            showData(goodList)
            return false;//组织事件继续向上冒泡而触发隐藏事件
        })

        function showData(val){
            $.each(val,function(v,k){
                var spanList=[];
                // 循环遍历规格数组
                $.each(val[v].spec,function(i,j){
                    var perSpec=$('<span></span>').text(j)
                    spanList.push(perSpec)
                })
                var goodspec=$('<div></div>').addClass('goodspec').html(spanList)
                var goodsTitle=$('<div></div>').addClass('goodsTitle').html(k.name)
                var goodsData=$('<div></div>').addClass('goodsData')
                goodsData.append([goodsTitle,goodspec])
                var reduceBtn=$('<button type="button"></button>').addClass('reduceNum').html('-')//减号
                var numInput=$('<input type="text" style="border-top: 1px solid rgb(196, 196, 196);width:40px;height:20px;text-align:center;padding:0;">').addClass('numBox').val(k.count)//数量输入框
                var addBtn=$('<button type="button"></button>').addClass('addNum').html('+')//加号
                var specVal=$('<div></div>').addClass('specVal').html('')
                var shopNum=$('<div></div>').addClass('shopNum').html('')
                shopNum.append(specVal.append([reduceBtn,numInput,addBtn]))
                var priceSpan=$('<span></span>').text(k.perPrice)
                var price=$('<div></div>').addClass('price').html(priceSpan)
                var delBtn=$('<div></div>').addClass('delPer').html('删除')//加号
                var delBox=$('<div></div>').addClass('delBtn').attr('idn',v).html(delBtn)
                var pergoodsBox=$('<div></div>').addClass('pergoodsBox')
                pergoodsBox.append([goodsData,shopNum,price,delBox])
                $('.dialogcont>.goodsBox').append(pergoodsBox)
            })
        }


        $('.delAllgoods').click(function(){
            goodList=[];
            $('.dialogcont>.goodsBox').empty()
        })
    })
</script>