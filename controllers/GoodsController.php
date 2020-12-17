<?php
namespace app\controllers;
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods", "*");//允许任何method
header("Access-Control-Allow-Headers", "*");//允许任何自定义header
header("Access-Control-Allow-Credentials", "true");//允许跨域cookie

use app\service;
use \yii\base\Controller;
use yii\base\UserException;
use Yii;
class GoodsController extends Controller{
    public $member_id;
    public function init(){
        parent::init();

        $headers = Yii::$app->getRequest()->getHeaders();
        $token = $headers->get('token');

        $this->member_id = \Yii::$app->cache->get($token)?\Yii::$app->cache->get($token):0;
    }

    /**
     * 获取全部商品列表 (三层目录结构 1:cate 2:scate 3:goods)
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-15
     */
    public function actionIndex()
    {
        $list = service\GoodsService::getGoodsList($this->member_id);
        return json($list);
    }

    /**
     * 重写获取商品分类 cate_id为一级分类 scate_id为二级分类
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-19
     */
    public function actionCateGory()
    {
        $list = service\GoodsService::getCateGory();
        return $list;
    }


    /**
     * 重写获取商品列表  根据scate_id和token获取商品列表
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-19
     */
    public function actionGoodsList()
    {
        $request = Yii::$app->request;

        $cate_id = $request->get('cate_id');
        $scate_id = $request->get('scate_id')?$request->get('scate_id'):0;
        $pages = $request->get('pages')?$request->get('pages'):0;
        $list = service\GoodsService::getGoodsList($cate_id,$scate_id,$this->member_id,$pages);
        return $list;
    }

    /**
     * 商品详情
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-15
     */
    public function actionGoodsDetail()
    {

        $request = Yii::$app->request;
        $goods_id = $request->get('goods_id');

        $result = service\GoodsService::getGoodsDetail($goods_id,$this->member_id);
        return json($result);
    }

    /**
     * 搜索完成展示列表
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-15
     */
    public function actionSearch()
    {

        $request = Yii::$app->request;
        $key = $request->get('key');
        $result = service\GoodsService::search($key,$this->member_id);
//        halt($result);
        return json_encode($result,JSON_UNESCAPED_UNICODE);
    }

    /**
     * 购物车操作
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-16
     * $type 0:未知(用户直接输入数量) 1:增加 2:减少
     */
    public function actionShopcartOptions()
    {
        if($this->member_id){
            $request = Yii::$app->request;
            $number = $request->post('number');
            $type = $request->post('type');
            $goods_id = $request->post('goods_id');
            $member_id = $this->member_id;
            $result = service\GoodsService::EditShopCart($number,$type,$goods_id,$member_id);
            halt($result);
        }else{
            exit(header("HTTP/1.1 401 Forbidden"));
        }
    }


    /**
     * 收藏/取消收藏操作
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-16
     *
     */
    public function actionFavorite()
    {
        if($this->member_id){
            $request = Yii::$app->request;
            $goods_id = $request->get('goods_id');
            $result = service\GoodsService::IsFavorite($goods_id,$this->member_id);
            return success();
        }else{
            exit(header("HTTP/1.1 401 Forbidden"));
        }
    }

    /**
     * 编辑购物车备注
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-27
     *
     */
    public function actionCartRemark()
    {
        if($this->member_id){
            $request = Yii::$app->request;
            $cart_id = $request->post('id');
            $remark = $request->post('remark');
            $result = service\GoodsService::editCartRemark($cart_id,$remark);
            return $result;
        }else{
            exit(header("HTTP/1.1 401 Forbidden"));
        }
    }


}
