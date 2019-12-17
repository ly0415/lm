

1、
<input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
s 是当前访问的控制器方法（http://www.phpstudy.net/index.php?s=%2Fstore%2Fstore.source_list%2Findex.html&sourcename=）
                        http://www.phpstudy.net/index.php?s=/store/store.source_list/index.html
<input type="text" class="am-form-field" name="sourcename" placeholder="请输入来源名称" value="<?= $request->get('sourcename') ?>">

get方法访问s中的访问方法  带入参数get('sourcename')




<?php //if (isset($roionList['url'])):  ?>
<!--    <a  href="--><?//= $roionList['url']?><!--">小程序地址:</a>-->
<!---->
<!--    <input type="hidden" name="rotionc[url]" value="--><?//= $roionList['url'] ?><!--">-->
<?php //endif;  ?>
<!---->
<!---->
<!--[{"img":"20190905\/2019090513341799d8f0671.jpg","url":"sdadad1111111dad\/sdad"}]-->


2、tp5 中AJAX用法

<td class="am-text-middle">
    <div class="tpl-table-black-operation">
        <a href="javascript:;" class="<?php if( checkPrivilege('distribution.discount_change/edit')):?>j-state<?php endif;?>" data-id="<?= $item['id'] ?>" data-state="">
            <i class="am-icon-pencil"></i>审核
        </a>
<script>
    // 审核状态
    $('.j-state').click(function () {
        var data = $(this).data();
        layer.confirm('确定要审核通过么？', {
            btn: ['通过', '拒绝'] //按钮
        }, function () {
            $.ajax({
                type: 'get',
                url: "<?= url('distribution.discount_change/edit') ?>",
                data:{id:data.id,status:2},
                dataType: 'json',
                success: function (res) {
                    if (res.code) {
                        layer.msg(res.msg, {icon: 1, time: 2000});
                        setTimeout(function () {
                            window.location.reload();
                        }, 1000)
                    } else {
                        layer.msg(res.msg, {icon: 5})
                    }
                }
            })
        }, function () { $.ajax({
            type: 'get',
            url: "<?= url('distribution.discount_change/edit') ?>",
            data:{id:data.id,status:3},
            dataType: 'json',
            success: function (res) {
                if (res.code) {
                    layer.msg(res.msg, {icon: 1, time: 2000});
                    setTimeout(function () {
                        window.location.reload();
                    }, 1000)
                } else {
                    layer.msg(res.msg, {icon: 5})
                }
            }
        })
        });
    });

    3、tp5 each 函数
    这篇文章介绍的内容是关于TP5-分页类中的each函数 ，有着一定的参考价值，现在分享给大家，有需要的朋友可以参考一下
    $list = Db::name('merchant_order_detail')->alias('a')->join('merchant_order b', 'b.id = a.order_id')->where($whereStr)->field($file)->order($order)->paginate(Config::get('list_rows'), false, ['page' => $page,'query'=>$param])->each(function($item, $key){

        $item['loglist'] = Db::name('merchant_order_detail_log')->where("order_detail_id=".$item['id']." and amount>0")->field('order_sn')->select();            return $item;

    });
    $list = Db::name('merchant_order')->alias('a')

        ->join('merchant_member mm', 'mm.id=a.member_id', 'LEFT')

        ->where($whereStr)

        ->field($file)

        ->order(['a.create_time' => 'desc'])

    ->paginate(Config::get('list_rows'), false, ['page' => $page,'query'=>$param])

    ->each(function($item,$key){                    if($item['pid'] == 0) {

        $item['company_name'] = Db::name('merchant')->where(['member_id'=>$item['member_id']])->value('company_name');

        $item['sub_company_name'] = Db::name('merchant')->where(['member_id'=>$item['member_id']])->value('sub_company_name');

        $item['corporation'] = Db::name('merchant')->where(['member_id'=>$item['member_id']])->value('corporation');

    } else {

        $item['company_name'] = Db::name('merchant')->where(['member_id'=>$item['pid']])->value('company_name');

        $item['sub_company_name'] = Db::name('merchant')->where(['member_id'=>$item['pid']])->value('sub_company_name');

        $item['corporation'] = Db::name('merchant')->where(['member_id'=>$item['pid']])->value('corporation');

    }                    return $item;

    });


    $merchant_member_list = Db::name('merchant_member')->alias('mm')

        ->join('merchant m', 'm.member_id = mm.id', 'LEFT')

        ->join('merchant_type mt', 'm.merchant_type_id = mt.id', 'LEFT')

        ->where($where)

        ->field($field)

        ->order(['mm.is_lock' => 'asc','mm.create_time' => 'desc'])

    ->paginate(Config::get('list_rows'), false, ['page' => $page, 'query' => $param])

    ->each(function($item, $key){                                    if($item['mt_pid']=='0'){

        $item['cate'] = '一级';

    } else {

        $item['cate'] = Db::name('merchant_type')->where("id=".$item['mt_pid'])->value('name');

    }                                    return $item;

    });


    <?= $list['startime'].'-'.$list['endtime']?>

piginate(15,false,['query'=>$xss1])
    piginate(15,false,['query'-=])

!empty($store_id) $this->where('')
Db::name('')->where([''])





    $userAddressMod = &m('userAddress');
    $sql = "select  distance from " . DB_PREFIX . "user_address where parent_id = 0";
    $city = $userAddressMod->querySql($sql);


4、 emoji表情
    将Mysql的编码从utf8转换成utf8mb4
    image.png
    //接受emoji表情
    function addEmoji($content){
        $return= preg_replace_callback('/[\xf0-\xf7].{3}/', function ($r){return '@E' . base64_encode($r[0]);}, $content);
        return $return;
    }
    //解析emoji表情
    function getEmoji($content){
        $return=preg_replace_callback('/@E(.{6}==)/',function ($r){return base64_decode($r[1]);},$content);
        return $return;
    }



    ->paginate(15, false, ['query' => \request()->request()]);

5、单选 复选框
    <script type="text/javascript" src=../js/jquery-2.1.4.js></script>
        <script type="text/javascript">
            $(function() {
                var checked_items = $("input[name='items']");

                // 全选
                $("#all").click(function() {
                    for (var i = 0; i < checked_items.length; i++) {
                        checked_items[i].checked = true;
                    }
                });

                // 全不选
                $("#allnot").click(function() {
                    for (var i = 0; i < checked_items.length; i++) {
                        checked_items[i].checked = false;
                    }
                });

                // 反选
                $("#back").click(function() {
                    for (var i = 0; i < checked_items.length; i++) {
                        checked_items[i].checked = !checked_items[i].checked;
                    }
                });
        </script>

        <div id="select2">
            <button id="all">全选</button>
            <button id="allnot">全不选</button>
            <button id="back">反选</button>
        </div>

        <div id="inputs">
            <input type="checkbox" name="items" value="1" />1#
            <input type="checkbox" name="items" value="2" />2#
            <input type="checkbox" name="items" value="3" />3#
            <input type="checkbox" name="items" value="4" />4#
            <input type="checkbox" name="items" value="5" />5#
            <input type="checkbox" name="items" value="6" />6#
            <input type="checkbox" name="items" value="7" />7#
            <input type="checkbox" name="items" value="8" />8#
            <input type="checkbox" name="items" value="9" />9#
            <input type="checkbox" name="items" value="10" />10#
        </div>

        传递选中的复选框的值提交到服务器端页面
        var arr=[];
        $("input[name='items']:checked").each(function() {
        arr.push(this.value);// 将值加到数组里面
        });

6、github 清楚账号密码
        运行一下命令缓存输入的用户名和密码：
        git config --global credential.helper wincred
        清除掉缓存在git中的用户名和密码
        git credential-manager uninstall

7、





<!--                                    <div class="am-form-group am-fl">-->
<!--                                        <select name="store_id" data-am-selected="{btnSize: 'sm',btnWidth:150, placeholder: '所属门店'}">-->
<!--                                            <option value="0">总站</option>-->
<!--                                            --><?php //if ($storeId): foreach ($storeId as $val):  ?>
<!--                                            <option value="--><?//= $val['id']?><!--">--><?//= $val['store_name']?><!--</option>-->
<!--                                            --><?php //endforeach; endif; ?>
<!--                                        </select>-->
<!--                                    </div>-->
<!--                                    <div class="am-form-group am-fl">-->
<!--                                        <select name="cat_id" data-am-selected="{btnSize: 'sm',btnWidth:150, placeholder: '请选择业务类型'}">-->
<!--                                            <option value="0">请选择业务类型</option>-->
<!--<!--                                            -->--><?php ////if($storecategory): foreach ($storecategory as $it): ?>
<!--<!--                                            <option value="-->--><?////= $it['room_id']?><!--<!--">-->--><?////= $it['name']?><!--<!--</option>-->-->
<!--<!--                                            -->--><?php ////endforeach; endif; ?>
<!--                                        </select>-->
<!--                                    </div>-->
        <!--<script>-->
        <!---->
        <!--    function addItem(obj,item){-->
        <!--        var _html = '';-->
        <!--        $.each(item,function (k,v) {-->
    <!--            _html += "<option value='"+v.room_id+"'>"+v.name+"</option>";-->
        <!--        })-->
        <!--        obj.append(_html);-->
        <!--        obj.change();-->
        <!--    }-->
        <!---->
        <!--    $(function () {-->
        <!--        $("#province").on('change',function () {-->
        <!--            var province_id = $(this).val();-->
        <!--            var city = $("#city");-->
        <!--            var region = $("#region");-->
    <!--            var _html = "<option value='0'>请选择业务类型</option>";-->
        <!--            city.html(_html);-->
        <!--            region.html(_html);-->
        <!--            if(province_id > 0){-->
        <!--                $.post("--><?//=url('statistics.data_statistics/getStoreCategory')?><!--//",{store_id:province_id},function (res) {-->
    //                    addItem(city,res);
    //                },'JSON')
    //            }
    //        });
    //
    //
    //$('#ewe').on('click',function(){
    // $(input[name=['']])
    //
    // })
    //
    //
    //
    //    });
    //</script>

        //        //当天的开始时间
        //        $starttime = strtotime(date("Y-m-d",time()));
        //        //当天结束时间
        //        $endtime = $starttime+60*60*24;
        //        if($end_time){
        //            if($end_time  > $endtime){
        //                $end_time = $endtime;
        //            }
        //            if($add_time  > $end_time){
        //                $add_time = $end_time-86399;
        //            }
        //
        //        }else{
        //            $end_time=$endtime;
        //        }
        //        (!empty($add_time) && !empty($end_time)) && $this->where('g.add_time','between',[$add_time,$end_time]);

        /**
        * 图表--获取余额交易趋势
        * @param $store_cate_id    区域ID
        * @param $store_id         店铺ID
        * @param $tm
        * @author: wangshuo
        * @date  : 2019-3-21
        */
        public function zxTransactionTrend($store_cate_id = 0, $store_id = 0, $tm = 'week') {
        //获取时间组件
        $result = array();
        switch ($tm) {
        case 'week':
        $timeArr = array('周一', '周二', '周三', '周四', '周五', '周六', '周日');
        $timesArr = array();
        $y = date('Y', time());
        $w = date('W', time());
        $weekStart = date("Y-m-d", strtotime("{$y}-W{$w}-1"));
        $weekStart_1 = strtotime($weekStart);
        $weekStart_2 = strtotime($weekStart . ' 23:59:59');
        $diff = 24 * 3600;
        for ($i = 0; $i <= 6; $i++) {
        $timesArr[$i][0] = $weekStart_1 + $diff * $i;
        $timesArr[$i][1] = $weekStart_2 + $diff * $i;
        }
        $result = $this->qxTransactionTrend($timesArr, $store_cate_id, $store_id);
        $result['xAxis'] = $timeArr;
        break;
        case 'month':
        $timeArr = array();
        $month_1 = mktime(0, 0, 0, date('m'), 1, date('Y'));
        $diff = 24 * 3600;
        for ($i = 1; $i <= date('t'); $i++) {
        $timeArr = array_merge($timeArr, array(date('Y/m/') . $i));
        $timesArr[$i][0] = $month_1 + $diff * ($i - 1);
        $timesArr[$i][1] = ($month_1 + $diff * $i) - 1;
        }
        $result = $this->qxTransactionTrend($timesArr, $store_cate_id, $store_id);
        $result['xAxis'] = $timeArr;
        break;
        case 'year':
        $timeArr = array();
        for ($i = 1; $i <= 12; $i++) {
        $timeArr = array_merge($timeArr, array(date('Y/') . $i));
        $timesArr[$i][0] = mktime(0, 0, 0, $i, 1, date('Y'));
        $timesArr[$i][1] = mktime(23, 59, 59, $i, date('t'), date('Y'));
        }
        $result = $this->qxTransactionTrend($timesArr, $store_cate_id, $store_id);
        $result['xAxis'] = $timeArr;
        break;
        }
        return $result;
        }



        /**
        *获得当前店铺的所有业务类型
        * Created by PhpStorm.
        * Author:ly
        * Date:2019-11-11
        */
        public function getStoreCategory($store_id=''){
        $rtid=BUSINESS_ID;
        $business_tree = Business::getCacheTree();
        if(isset($business_tree[$rtid])) {
        $business = $business_tree[$rtid]['child'];
        }
        return $business;
        //        if(IS_ADMIN){
        //
        //
        ////            if($store_id){
        //                $store_id=$store_id;
        ////                return $this->getStoreCategoryList($store_id);
        ////
        ////            }else{
        ////                $store_id='';
        ////                return $this->getStoreCategoryList($store_id);
        ////            }
        //        }else{
        //            $store_id=STORE_ID;
        ////            return $this->getStoreCategoryList($store_id);
        //        }
        //        !empty($store_id) && $this->where('store_id', $store_id);
        //        $list=$this->alias('a')
        //            ->field('a.room_id,b.name')
        //            ->join('business b','a.room_id=b.id')
        //            ->group('a.room_id')
        //            ->where('a.is_on_sale','=',1)
        //            ->where('a.mark','=',1)
        //            ->select();
        //        $item=[];
        //        if($list){
        //            foreach($list as $value){
        //                $data['room_id']=$value['room_id'];
        //                $data['name']=$value['name'];
        //                $item[]=$data;
        //            }
        //        }
        //        return $item;

        }

        /**
        *统计按天、按月 的会员数
        * @author ly
        * @date 2019-11-11
        */
        public function getuserListAll($store_id='',$storeuser_id='',$starttime='',$endtimee=''){
        !empty($store_id) && $this->where('a.store_id','=',$store_id) ;
        !empty($storeuser_id) && $this->where('a.id','=',$storeuser_id);
        $list = $this->alias('a')
        ->field('a.id as storeuserid,a.store_id,a.real_name,u.phone,u.add_time')
        ->join('user u','a.user_id=u.id')
        //            ->join('user us','u.phone=u.phone_email')
        //            ->where('u.phone=u.phone_email')
        //            ->where('a.user_id',43)
        //            ->join('user u','u.phone_email=a.mobile')
        //            ->where('a.mobile','<>','')
        //            ->where('u.add_time','between',[$starttime,$endtimee])
        //            ->where('u.phone_email','<>','')
        //            ->where('a.mark','=',1)
        ->select();
        //            ->each(function($item){
        //                $item['add_time']=date("Y-m-d H:i:s",$item['add_time']);
        //            });
        $item=[];
        foreach($list as $val){
        $data=Db::name('user u')->field('count(id) as count')->where('phone_email',$val['phone'])
        ->where('u.add_time','between',[$starttime,$endtimee])
        ->select();
        foreach($data as $value){
        $item[]=$value['count'];
        }
        }
        return array_sum($item);

        }




        //        echo $endtime;die;
        //        $store_id=98;
        //        $storeuser_id=203;
        //        $storeuser_id=222;
        //        $storeuser_id=225;

        //        !empty($store_id) && $this->where('a.store_id','=',$store_id) ;
        //        !empty($storeuser_id) && $this->where('a.user_id','=',$storeuser_id)&& $this->group('a.mobile');
        //        $list=$this->alias('a')
        ////            ->field('u.id,u.username,u.add_time,u.phone_email,count(u.id) as count')
        //            ->field('u.*')
        //            ->join('user u','u.phone_email=a.mobile')
        ////            ->group('u.add_time')
        //            ->where('a.mobile','<>','')
        ////            ->where('u.add_time','between',[$starttime,$endtime])
        //            ->where('u.phone_email','<>','')
        //            ->where('a.mark','=',1)
        //            ->select()->each(function($item){
        //                $item['add_time']=date("Y-m-d H:i:s",$item['add_time']);
        //            });
        //        print_r($list->toArray());die;
时间
        //        $nowMonthStartTime=strtotime("first day of this month 00:00:00");
        //        $nowMonthEndTime=strtotime("last day of this month 23:59:59");
        //        $startmonth=$endtime?strtotime($endtime):$nowMonthStartTime;
        //        $endtmonth=$endtime?(strtotime(date('Y-m-t',strtotime('2018-8')).' 23:59:59')):$nowMonthEndTime;
    //        $a='2018-8-8';
    //        echo date("Y-m-d H:i:s",(strtotime($a)+86399));die;
    //        echo date("Y-m-d H:i:s",(strtotime(date("Y-m-d",time()))+(60*60*24)-1));die;
    //        $now = date('Y-m-d H:i:s',time());
    //        echo date('Y-m-d H:i:s',strtotime("next day",strtotime($now)));die;


        //            data: [<?php //foreach($storeuserlist['data'] as $k=>$val){ if($val){ foreach($val as $v){?>
        //                '<?//= $v['count']?>//',
        //                <?php //} } else{ ?>
        //                ' ',
        //           <?php //}} ?>
qqqqqqqqqqqqqqqqqqqqqqqqq

    //        $user_id=1053;
    //        $order_sn='201910121612298324';
    //        $store_id=98;
    //        $evaluete_content="阿萨德卡军阿大萨大大阿萨德卡军阿大萨大大阿萨德卡军阿大萨大大阿萨德卡军阿大萨大大阿萨德卡军阿大 .萨大大阿萨德卡军阿大萨大大阿萨德卡军阿大萨大大阿萨德卡军阿大萨大大阿萨德卡军阿大萨大大阿萨德卡军阿大萨大大";
    //        $goods_images=['1111111111111111111.jpg','2222222222222.jpg','333333333.jpg'];
    //        $star_num=[1,2,3];



    //        $userid=34317;
    //        $data['headimgurl']='sdadad';
    //        $data['username']='达到第三色图分身乏术';
    //        $data['phone']='18612844870';
    //        $data['email']='16822222465@qq.com';
    //        $data['sex']=2;
    //        $data['birth']='2018-15-12';

    //                if(!empty($item['sendout'])){
    //                    if($item['sendout']==1){
    //                        $it['service_attitude']='服务态度';
    //                        $it['store_environment']='门店环境';
    //                    }else {
    //                        //
    //                        $it['product_packaging'] = '产品包装';
    //                        $it['delivery_speed'] = '配送速度';
    //                        $it['distribution_personnel'] = ' 配送人员';
    //                    }
    //
    //                    $item['sendtype']=$it;
    //                }else{
    //                    $item['sendtype']='';
    //                }


    //        if((!empty($image)) && $data){
    //            foreach($data as $val){
    //                if(empty($val['image'])) continue;
    //                    $date[]=$val;
    //            }
    //            print_r($date);die;
    //            return $date;
    //        }
        # GitHub Start
        192.30.253.112 github.com
        192.30.253.119 gist.github.com
        151.101.100.133 assets-cdn.github.com
        151.101.100.133 raw.githubusercontent.com
        151.101.100.133 gist.githubusercontent.com
        151.101.100.133 cloud.githubusercontent.com
        151.101.100.133 camo.githubusercontent.com
        151.101.100.133 avatars0.githubusercontent.com
        151.101.100.133 avatars1.githubusercontent.com
        151.101.100.133 avatars2.githubusercontent.com
        151.101.100.133 avatars3.githubusercontent.com
        151.101.100.133 avatars4.githubusercontent.com
        151.101.100.133 avatars5.githubusercontent.com
        151.101.100.133 avatars6.githubusercontent.com
        151.101.100.133 avatars7.githubusercontent.com
        151.101.100.133 avatars8.githubusercontent.com
        # GitHub End
        .

        echo $cart_ids;
        echo $seller_msg;
        echo $addressId;
        echo $fxPhone;
        echo $storeid;
        echo $discount_rate;
        echo $sendout;
        echo $shippingfee;
        echo $couponId;
        echo $userCouponId;
        echo $discount_price;
        echo $fx_user_id;
        echo $daifu;
        echo $post_sendout;
        echo 3;die;



        'langData'=>$langData,
            'recommendGoodsData'=>$recommendGoods,
            'articleData'=>$articleData,
            'bannerData'=>$bannerData,
            'naichaData'=>$naichaData,
            'businessData'=>$businessData,
            'youhuiData'=>$youhuiData,
            'recommYou'=>$recommYou,
            'store_id'=>$this->store_id,
            'lang_id'=>$this->lang_id





        public function getGoodsList($store_id,$is_recom,$limit){
            $goodsList = $this
                ->alias('s')
                ->field('s.id,s.cat_id,s.store_id,s.shop_price,s.market_price,s.is_free_shipping,gs.original_img,l.goods_name')
                ->join('goods gs','s.goods_id = gs.goods_id')
                ->join('goods_lang l','l.goods_id = s.goods_id')
                ->order(['s.add_time'=>'desc','s.goods_id'=>'desc'])
            ->limit(0,4)
            ->where(['s.is_recom'=>$is_recom,'s.store_id'=>$store_id])
            ->select();
            print_r($goodsList);die;
            echo $limit;die;

        $post_sendout='',$cart_ids='',$seller_msg='',$addressId=0,$fxPhone='',$storeid='',$discount_rate='',$sendout=1,$shippingfee=0,$couponId=0,$userCouponId=0,$discount_price=0,$fx_user_id='',$daifu=''

        链接
        https://www.runoob.com/php/func-string-sprintf.html     //PHP sprintf() 函数

