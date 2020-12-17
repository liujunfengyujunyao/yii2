<?php
namespace app\controllers;
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods", "*");//允许任何method
header("Access-Control-Allow-Headers", "*");//允许任何自定义header
header("Access-Control-Allow-Credentials", "true");//允许跨域cookie
use app\models\Supplier;
use app\models\Supporder;
use app\models\SupporderGoods;
use \yii\base\Controller;
use app\service;
use yii\base\UserException;
use Yii;
class SupporderController extends Controller{

    /**
     * 此控制器需要token验证 验证失败返回http401状态码跳转至登录页
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-16
     */
    public $supplier_id;
    public function init(){
        parent::init();
        $headers = Yii::$app->getRequest()->getHeaders();
        $token = $headers->get('token');
        \Yii::$app->cache->get($token)?$this->supplier_id = \Yii::$app->cache->get($token):exit( header("HTTP/1.1 401 Forbidden"));

    }
    /**
     * 供货商首页
     * @author JF <qukaliujun@163.com>
     * @Date 2020-11-03
     * $send_date:今天的日期
     */
    public function actionIndex()
    {
        $send_date = date('Y-m-d',time());
        $result = service\SupplierService::IndexOrderList($this->supplier_id,$send_date);
        return $result;
    }

    /**
     * 订单配货列表
     * @author JF <qukaliujun@163.com>
     * @Date 2020-11-04
     * $send_date:今天的日期
     */
    public function actionSuppPick()
    {
//        $this->supplier_id = 71;
        $request = Yii::$app->request;
        $time = $request->post('send_date');
        $time ? $send_date=$time:$send_date=date('Y-m-d',time());
        $result = service\SupplierService::OrderPickList($this->supplier_id,$send_date);
        return $result;

    }


    /**
     * 配货子表
     * @author JF <qukaliujun@163.com>
     * @Date 2020-11-04
     * $id supporder主键
     */
    public function actionSuppPickGoods()
    {
        $request = Yii::$app->request;
        $id = $request->post('id');
        $result = service\SupplierService::SupporderGoodsPick($id);
        return $result;
    }

    /**
     * 配货
     * @author JF <qukaliujun@163.com>
     * @Date 2020-11-04
     *
     */
    public function actionPick()
    {
        $request = Yii::$app->request;
        $supporder_goods_id = $request->post('supporder_goods_id');
        $status = $request->post('status');
        $result = service\SupplierService::UpdateSupporderGoods($supporder_goods_id,$status);
        return $result;

    }

    /**
     * 配货统计
     * @author JF <qukaliujun@163.com>
     * @Date 2020-11-05
     *
     */
    public function actionPickStatistics()
    {
        $this->supplier_id = 71;
        $request = Yii::$app->request;
        $time = $request->post('send_date');
        $time ? $send_date=$time:$send_date=date('Y-m-d',time());
        $request->post('gotime_id')?$gotime_id=$request->post('gotime_id'):$gotime_id='';

        $result = service\SupplierService::SuppOrderStatistics($this->supplier_id,$send_date,$gotime_id);
        return $result;
    }



    /**
     * 报价明细
     * @author JF <qukaliujun@163.com>
     * @Date 2020-11-05
     *
     */
    public function actionSuppPriceList()
    {
        $request = Yii::$app->request;
        $id = $request->post('id');
        $time = $request->post('send_date');
        $time ? $send_date=$time:$send_date=date('Y-m-d',time());
        $result = service\SupplierService::SupporderGoodsPrice($id);
        return $result;
    }

    /**
     * 报价明细->修改报价(系统报价)
     * @author JF <qukaliujun@163.com>
     * @Date 2020-11-05
     *
     */
    public function actionSuppPriceUpdate()
    {
        $this->supplier_id = 71;
        $request = Yii::$app->request;
        $id = $request->post('supporder_goods_id');
        $price = $request->post('price');
        $result = service\SupplierService::UpdateSuppGoodsPrice($id,$this->supplier_id,$price);
        return $result;

    }

    /**
     * 细分报价列表(细分报价)
     * @author JF <qukaliujun@163.com>
     * @Date 2020-11-06
     *
     */
    public function actionSuppPrice2List()
    {
        $request = Yii::$app->request;
        $supporder_goods_id = $request->post('supporder_goods_id');
        $result = service\SupplierService::SupporderGoodsPrice2($supporder_goods_id);
        return $result;
    }

    /**
     * 细分报价列表->修改细分报价(细分报价)
     * @author JF <qukaliujun@163.com>
     * @Date 2020-11-06
     *
     */
    public function actionSuppPrice2Update()
    {
        $request = Yii::$app->request;
        $id = $request->post('id');
        $price = $request->post('price');
        $result = service\SupplierService::UpdateSuppGoodsPrice2($id,$price);
        return $result;

    }

    public function actionMaopao()
    {
        $arr = [5,3,1,6,24,66,2,678,432,52,1,1241,663];
        $count = count($arr);
        for($i=0;$i<$count-1;$i++){//5
            for($j=$i+1;$j<$count;$j++){//3
                if($arr[$j] < $arr[$i]){
                    $temp = $arr[$j];
                    $arr[$j] = $arr[$i];
                    $arr[$i] = $temp;
                }
            }
        }
        halt($arr);
    }

    /**
     * 账单管理
     * @author JF <qukaliujun@163.com>
     * @Date 2020-12-15
     *
     */
    public function actionOrderList()
    {
        $request = Yii::$app->request;
        $request->post('start_time')?$start_time = $request->post('start_time'):$start_time=date('Y-m-d',time());
        $request->post('end_time')?$end_time = $request->post('end_time'):$end_time=date('Y-m-d',time());
        $request->post('audit_status')?$audit_status = $request->post('audit_status'):$audit_status=null;
        $result = service\SupporderService::IndexOrderList($this->supplier_id,$start_time,$end_time,$audit_status);
        return $result;
    }


    /*
     * 确认账单
     * @author JF <qukaliujun@163.com>
     * @Date 2020-12-15
     *
     * */
    public function actionOrderConfirm()
    {
        $request = Yii::$app->request;
        $id = $request->post('id');

        $result = service\SupporderService::OrderConfirm($id);
        return $result;
    }

}
