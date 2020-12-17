<?php
namespace app\controllers;
use app\models\Member;
use \yii\base\Controller;
use app\service;
use yii\base\UserException;
use Yii;
class OrderController extends Controller{
    /**
     * 此控制器需要token验证 验证失败返回http401状态码跳转至登录页
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-16
     */
    public $member_id;
    public function init(){
        parent::init();
        $headers = Yii::$app->getRequest()->getHeaders();
        $token = $headers->get('token');
//        \Yii::$app->cache->get($token)?$this->member_id = \Yii::$app->cache->get($token):exit( header("HTTP/1.1 401 Forbidden"));

    }

    /**
     * 结算页面信息
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-19
     * $ids 购物车的ids
     * price 选中购物车内商品价格总和
     * userinfo 联系人:linkman 联系电话:username 收货地址:address 送达时间:?
     * delivery_date送货日期
     */
    public function actionSettlement()
    {
        $request = Yii::$app->request;
        $ids = $request->post('ids');
        $price = service\OrderService::CalculationAmount($ids,$this->member_id);
        $userinfo = service\MemberService::getMemberInfo($this->member_id);
        $delivery_date = [
            'today' => date("Y-m-d"),
            'tomorrow' => date("Y-m-d",strtotime("+1 day")),
            'twoday' => date("Y-m-d",strtotime("+2 day")),
            'threeday' => date("Y-m-d",strtotime("+3 day"))
        ];
        $result = [
            'totalPrice' => $price,
            'userInfo' => $userinfo,
            'deliveryDate' => $delivery_date
        ];
        return success($result);


    }
    //$totalPrice,$ids,$member_id,$receive_start_time,$receive_end_time,$delivery_date,$remark

    /**
     * 提交订单
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-20
     * $ids 购物车的ids
     * price 选中购物车内商品价格总和
     * userinfo 联系人:linkman 联系电话:username 收货地址:address 送达时间:?
     * delivery_date送货日期
     */
    public function actionOrderSubmit()
    {

        $request = Yii::$app->request;
        $totalPrice = (float) $request->post('totalPrice');
        $ids = $request->post('ids');//购物车ids
        $receive_start_time = $request->post('receive_start_time');
        $receive_end_time = $request->post('receive_end_time');
        $delivery_date = $request->post('delivery_date');
        $remark = $request->post('remark');
        $result = service\OrderService::CheckOrderSubmit($totalPrice,$ids,$this->member_id,$receive_start_time,$receive_end_time,$delivery_date,$remark);

        return $result;
    }

    /**
     * 订单列表(整合)
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-20
     **/
    public function actionOrderList()
    {
        $request = \Yii::$app->request;
        $start_time = $request->get('start_time');
        $end_time = $request->get('end_time');
        $result = service\OrderService::getOrderList($start_time,$end_time,$this->member_id);
        return $result;
    }

    /**
     * 导出订单
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-21
     **/
    public function actionExcel()
    {
        $request = \Yii::$app->request;
        $order_ids = $request->get('ids');
//        halt($order_ids);
        $array = service\OrderService::getOrderGoods($order_ids);
//        halt($array);
//        $head = ['订单编号','商品ID','订单ID','商品名称','规格','单位','价格','订单数量','发货数量','退货数量'];
        $head = ['序号','一级分类','二级分类','商品名称','规格','单位','单价','下单数量','下单金额','发货数量','发货金额','退货数量','退货金额'];
        $result = service\OrderService::export2Excel($array['info'],$head,$array['title'],$array['statistics']);
        halt($result);
    }

    /**
     * 取消订单
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-22
     **/
    public function actionCancelOrder()
    {
        $request = \Yii::$app->request;
        $order_id = $request->post('id');
        $result = service\OrderService::cancelOrder($order_id);
        return $result;
    }



}
