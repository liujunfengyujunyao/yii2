<?php
namespace app\controllers;
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods", "*");//允许任何method
header("Access-Control-Allow-Headers", "*");//允许任何自定义header
header("Access-Control-Allow-Credentials", "true");//允许跨域cookie
use \yii\base\Controller;
use app\service;
use yii\base\UserException;
use Yii;
class ComplainController extends Controller{

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
     * 奖惩单列表
     * @author JF <qukaliujun@163.com>
     * @Date 2020-12-17
     * $send_date:今天的日期
     * $status:状态 0已取消、1、待确认2、已确认3、已完成
     */
    public function actionIndex()
    {
        $request = Yii::$app->request;
        $start_time = $request->post('start_time');
        $end_time = $request->post('end_time');
        if(!$start_time){
            $start_time = strtotime('2015-01-01');
        }else{
            $start_time = strtotime($start_time);
        }
        if(!$end_time){
            $end_time = time();
        }else{
            $end_time = strtotime($end_time)+60*60*24;
        }
//        halt($start_time);
        $result = service\ComplainService::OrderComplainList($this->supplier_id,$start_time,$end_time);
        return $result;
    }

    /**
     * 确认奖惩单
     * @author JF <qukaliujun@163.com>
     * @Date 2020-12-17
     * id reward主键
     */
    public function actionConfirm()
    {
        $request = Yii::$app->request;
        $id = $request->post('id');
        $result = service\RewardService::RewardConfirm($id);
        return $result;
    }


}
