<!-- <link rel="stylesheet" href="https://a.amap.com/jsapi_demos/static/demo-center/css/demo-center.css" /> -->
<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/eleFence.css">
<link rel="stylesheet" href="https://cache.amap.com/lbs/static/main1119.css"/>
<script type="text/javascript" src="https://webapi.amap.com/maps?v=1.4.15&key=c8ff4e9ec502960a4c0c5631b96a6d0b&plugin=AMap.Autocomplete,AMap.PlaceSearch,AMap.MouseTool,AMap.Geocoder,AMap.PolyEditor"></script>
<script type="text/javascript" src="https://a.amap.com/jsapi_demos/static/demo-center/js/demoutils.js"></script>
<script type="text/javascript" src="https://cache.amap.com/lbs/static/addToolbar.js"></script>
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post" action="<?= url('store.shop/edit_ef') ?>">
                    <div class="widget-body" style="height: 800px">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">配送范围</div>
                            </div>
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-end">
                                    <div id="container" style="height: 650px;width: 100%;margin-left: 10%">
                                        <div id="myPageTop" style="top: 3%;right:3%;z-index:999">
                                            <table>
                                                <tr>
                                                    <td>
                                                        <label>请输入关键字：</label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <input id="tipinput" autocomplete="off"/>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="input-card" style="width: 200px;right: 6%;bottom:15%;z-index:999">
                                            <button class="btn draw" type="button" style="margin-bottom: 5px">开始编辑</button>
                                            <?php if ($model['delivery_area']): ?>
                                                <button class="btn draw" type="button" style="margin-bottom: 5px" onclick="polyEditor.close()">结束编辑</button>
                                            <?php endif; ?>
                                            <button class="btn" type="submit" style="margin-bottom: 5px">保存</button>
                                        </div>
                                    </div>

                                    <div class="am-form-group">
                                        <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                            <input type="hidden" name="ef[store_id]" value="<?= $model['id'] ?>">
                                            <input type="hidden" name="ef[delivery_area]" id="area" value="<?= $model['delivery_area'] ?>">
                                            </button>
                                        </div>
                                    </div>
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
    $(function () {
        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
<script type="text/javascript">
    var path = '<?= $model['delivery_area']?>';
    var map = new AMap.Map("container", {
        zoom: 14
    });
    var mouseTool = new AMap.MouseTool(map)

    if (path) {
        //显示已有范围
        var num = 1;
        var oldPath = [];
        arr = path.split(';');
        $.each(arr,function(i,e){
            var str = e.split(',');
            oldPath.push(new AMap.LngLat(str[0],str[1]));
        });
        var polygon = new AMap.Polygon({
            path: oldPath,
            strokeColor: "#FF33FF",
            strokeWeight: 6,
            strokeOpacity: 0.2,
            fillOpacity: 0.4,
            fillColor: '#1791fc',
            zIndex: 50,
        })
        map.add(polygon)
        map.setFitView([ polygon ])
    } else {
        var num = 0;
    }
    var polyEditor = new AMap.PolyEditor(map, polygon)
    polyEditor.on('end', function(event) {
        log.info('配送区域绘制完成')
        var path = event.target.B.path;
        var newPath = [];
        $.each(path,function(i,e){
            newPath.push(e.lng+','+e.lat);
        });
        newPath = newPath.join(';');
        // console.log(newPath)
        $('#area').val(newPath);
    })
    $(".draw").click(function(){
        if (path) {
            // map.on('click', mapClear);
            var polyEditor = new AMap.PolyEditor(map, polygon)
            polyEditor.open()
            polyEditor.on('end', function(event) {
                log.info('配送区域绘制完成')
                var path = event.target.B.path;
                var newPath = [];
                $.each(path,function(i,e){
                    newPath.push(e.lng+','+e.lat);
                });
                newPath = newPath.join(';');
                console.log(newPath)
                $('#area').val(newPath);
            })
        } else {
            mouseTool.polygon({
                strokeColor: "#FF33FF",
                strokeOpacity: 1,
                strokeWeight: 6,
                strokeOpacity: 0.2,
                fillColor: '#25b8fc',
                fillOpacity: 0.4,
                // 线样式还支持 'dashed'
                strokeStyle: "solid",
                // strokeStyle是dashed时有效
                // strokeDasharray: [30,10],
            })
        }
    });

    //点击地图事件
    function mapClear () {
        if (num != 0) {
            map.clearMap();
            num = 0 ;
            path = '';
        }
        return false;
    }

    mouseTool.on('draw', function(event) {
        // event.obj 为绘制出来的覆盖物对象
        num = num+1;
        //新绘制配送区域的坐标
        var polygonItem = event.obj;
        var path = polygonItem.getPath();//取得绘制的多边形的每一个点坐标
        var newPath = [];
        var newPathArr = [];
        $.each(path,function(i,e){
            newPath.push(e.lng+','+e.lat);
        });
        newPath = newPath.join(';');
        var arr = newPath.split(';');
        $.each(arr,function(i,e){
            var str = e.split(',');
            newPathArr.push(new AMap.LngLat(str[0],str[1]));
        });
        mapClear();
        var polygon = new AMap.Polygon({
            path: newPathArr,
            strokeColor: "#FF33FF",
            strokeWeight: 6,
            strokeOpacity: 0.2,
            fillOpacity: 0.4,
            fillColor: '#1791fc',
            zIndex: 50,
        })
        num = 1;
        map.add(polygon)
        map.setFitView([ polygon ])
        var polyEditor = new AMap.PolyEditor(map, polygon)
        polyEditor.open()
        $('#area').val(newPath);
        log.info('配送区域绘制完成')
    })

    //输入提示
    var autoOptions = {
        input: "tipinput"
    };
    var auto = new AMap.Autocomplete(autoOptions);
    var placeSearch = new AMap.PlaceSearch({
        map: map
    });  //构造地点查询类
    AMap.event.addListener(auto, "select", select);//注册监听，当选中某条记录时会触发
    function select(e) {
        placeSearch.setCity(e.poi.adcode);
        placeSearch.search(e.poi.name);  //关键字查询查询
    }
</script>
