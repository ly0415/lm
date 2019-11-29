<?php
namespace app\store\controller\shop;

use app\store\controller\Controller;
use app\store\model\Store;
use app\store\model\StoreSource;
use app\store\model\StoreUser;
use think\Csv;
use app\store\model\Order as OrderModel;
use think\Db;

/**
 * 后台用户管理控制器
 * @author zhangkx
 * @date 2019/4/28
 */
class OrderReport extends Controller {

    protected $model, $service;

    public function _initialize(){
        parent::_initialize();
        $this->model = model('Order');
//        $this->service = model('Order', 'service');
    }

    /**
     * 列表页
     * @author fup
     * @date 2019/07/22
     */
    public function index() {
        $list = (new OrderModel)->getExpList($this->request->param());
        $czRy = StoreUser::all(['mark'=>1,'enable'=>1]);
        $send = $this->model->delivery_type;
        //订单来源
        $laiYuan = (new StoreSource)->getList();
        //店铺列表
        if(IS_ADMIN){
            $StoreModel = new Store;
            $stores  = $StoreModel -> getStoreList();
        }
        return $this->fetch('index',compact('list','stores','send','czRy','laiYuan'));
    }

    public function excelOut()
    {
        $orderModel = new OrderModel;
        //获取订单数据
        $orderModel->excelOut($this->request->param());

    }

    /**
     * 导出
     * @author fup
     * @date 2019/07/22
     */
    public function excelOutz()
    {
        $where = ['a.mark'=>1,'a.order_state'=>['in',[20,25,30,40,50,60,70]]];
        $list = db('order_'.STORE_ID)->alias('a')
            ->join('order_details_'.STORE_ID.' b','a.id = b.order_id','LEFT')
            ->join('order_relation_'.STORE_ID.' c','a.id = c.order_id','LEFT')
            ->join('user d','a.buyer_id = d.id','LEFT')
            ->join('store_source e','c.payment_source = e.id','LEFT')
            ->join('order_goods f','a.order_sn = f.order_id','LEFT')
            ->join('store_goods g','f.goods_id = g.id','LEFT')
            ->field('a.order_sn,a.order_amount,a.order_state,a.sendout,a.evaluation_state,b.discount,b.fx_money,b.point_discount,b.coupon_discount,b.shipping_fee,c.payment_type,c.payment_source,c.payment_time,d.username,d.phone,e.name as sourceName,sum(g.market_price) as market_prices')
            ->where($where)
            ->order(['a.id'=>'desc'])
            ->group('a.id')
            ->limit(0,99999)
            ->select();
//        dump($sendOut);die;
        $data = [];
        foreach ($list as $key => &$value) {
            if ($value['payment_source'] == 1758421) {
                $value['sourceName'] = '艾美睿';
            }
            $value['format_send_out'] = $value['sendout'] ? $this->model->delivery_type[$value['sendout']] : '---';
            $value['format_source'] = $value['payment_source'] == 1758421 ? '艾美睿' : $value['payment_source'];
            $value['format_payment_time'] = $value['payment_time'] ? date('Y-m-d H:i') : '---';
            $value['format_payment_type'] = $value['payment_type'] ? $this->model->payment_type[$value['payment_type']] : '---';
            $data[$key][] = $this->filterValue($value['order_sn']);
            $data[$key][] = $value['format_send_out'];
            $data[$key][] = $value['username'];
            $data[$key][] = $this->filterValue($value['phone']);
            $data[$key][] = $value['format_source'];
            $data[$key][] = $value['format_payment_type'];
            $data[$key][] = $value['market_prices'];
            $data[$key][] = $this->filterValue($value['format_payment_time']);
            $data[$key][] = $value['order_amount'];
            $data[$key][] = $value['sourceName'];
            $data[$key][] = $value['shipping_fee'];
            $data[$key][] = $value['discount'];
            $data[$key][] = $value['fx_money'];
            $data[$key][] = $value['point_discount'];
            $data[$key][] = $value['coupon_discount'];
        }
        $csv = new Csv();
        $csv_title = array('订单编号','配送方式','买家姓名','买家手机','所属平台','支付方式','商品市场价','付款时间','实付金额','订单状态','订单运费','优惠额抵扣','分销码抵扣','睿积分抵扣','优惠券抵扣');
        $csv->put_csv($data,$csv_title,'交班报表'.time().'.csv');
    }


    /**
     * 表格值过滤
     * @param $value
     * @return string
     */
    private function filterValue($value)
    {
        return "\t" . $value . "\t";
    }
}
