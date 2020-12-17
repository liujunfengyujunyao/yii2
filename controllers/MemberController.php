<?php
namespace app\controllers;
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods", "*");//允许任何method
header("Access-Control-Allow-Headers", "*");//允许任何自定义header
header("Access-Control-Allow-Credentials", "true");//允许跨域cookie
use app\models\Member;
use app\models\NewProductDemand;
use \yii\base\Controller;
use app\service;
use yii\base\UserException;
use Yii;
use yii\web\UploadedFile;

class MemberController extends Controller{
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
        \Yii::$app->cache->get($token)?$this->member_id = \Yii::$app->cache->get($token):exit( header("HTTP/1.1 401 Forbidden"));

    }
    /**
     * 根据header中包含的token获取用户信息并返回购物车数量
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-16
     */
    public function actionInfo()
    {
        $result = service\MemberService::getMemberInfo($this->member_id);
        return json_encode($result,JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取收藏夹列表
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-16
     */
    public function actionFavoriteList()
    {
        $result = service\MemberService::getFavoriteList($this->member_id);
        return json_encode($result,JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取购物车列表
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-16
     */
    public function actionShopcartList()
    {
        $result = service\MemberService::getShopCartList($this->member_id);

        return json_encode($result,JSON_UNESCAPED_UNICODE);
    }

    /**
     * 删除购物车列表
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-16
     */
    public function actionDelShopcart()
    {
        $request = Yii::$app->request;
        $ids = $request->post('ids');
        $result = service\MemberService::delShopCart($ids,$this->member_id);
        return success($result);
    }


    /**
     * 编辑个人信息
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-22
     */
    public function actionEditInfo()
    {
        $request = Yii::$app->request;
        $linkman = $request->post('linkman');
        $mobile = $request->post('mobile');
        $receive_start_time = $request->post('receive_start_time');
        $receive_end_time = $request->post('receive_end_time');
        $result = service\MemberService::UpdateUserInfo($this->member_id,$linkman,$mobile,$receive_start_time,$receive_end_time);
        return $result;
    }

    /**
     * 上传头像
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-22
     */
    public function actionEditHead()
    {
        $model = new UploadedFile();
        if(Yii::$app->request->isPost){
            $uploadedFile = UploadedFile::getInstanceByName("file");
//            halt($data);
        }
        $ymd = date("Ymd");
        $save_path = Yii::$app->basePath . '/web/upload/images/' . $ymd . '/';

        $save_url = dirname(Yii::$app->homeUrl).'/upload/images/'.$ymd . "/";
        if(!file_exists($save_path)){
            mkdir($save_path);
        }
        $file_name = $uploadedFile->getBaseName();
        $file_ext = $uploadedFile->getExtension();
        //新文件名
        $new_file_name = date('YmdHis') . '_' . rand(10000,99999) . '.' . $file_ext;
        $uploadedFile->saveAs($save_path . $new_file_name);
        $img = "http://" . $_SERVER['HTTP_HOST'] . $save_url . $new_file_name;
        $member = Member::find()
            ->where(['id'=>$this->member_id])
            ->one();
        $member->head = $img;
        $member->save();
        return success($img);
    }

    /**
     * 修改密码
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-22
     */
    public function actionEditPass()
    {
        $request = Yii::$app->request;
        $old_pass = $request->post('old_pass');
        $new_pass = $request->post('new_pass');
        $rnew_pass = $request->post('rnew_pass');
        $result = service\MemberService::UpdatePassword($this->member_id,$old_pass,$new_pass,$rnew_pass);
        return $result;
    }

    /**
     * 忘记密码
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-22
     */
    public function actionForgetPass()
    {
        $request = Yii::$app->request;
        $member_id = $this->member_id;
        $code = $request->post('code');
        $event = $request->post('event');
        $new_pass = $request->post('new_pass');
        $rnew_pass = $request->post('rnew_pass');
        if($new_pass != $rnew_pass){
            return fail('两次密码不一致');
        }
        $result = Member::forgetPassword($member_id,$code,$event,$new_pass,$rnew_pass);
        return $result;
    }

    /**
     * 新增需求
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-22
     * goods_name 商品名称
     * spec 规格
     * brand 产地/品牌
     * projectedqty 预计采购/天
     * rate 采购频次 1:临时 2:短期 3:长期
     * procurement_source 原采购地
     * procurement_price 原采购价
     * remark 详细描述
     * img 商品图片
     * 懒得写对象  直接面向过程吧  - -
     */
    public function actionDemandAdd()
    {
        $request = Yii::$app->request;
        $new_product_demand = new NewProductDemand();
        $new_product_demand->member_id = $this->member_id;
        $new_product_demand->goods_name = $request->post('goods_name');
        $new_product_demand->spec = $request->post('spec');
        $new_product_demand->brand = $request->post('brand');
        $new_product_demand->projectedqty = $request->post('projectedqty');
        $new_product_demand->rate = $request->post('rate');
        $new_product_demand->procurement_source = $request->post('procurement_source');
        $new_product_demand->procurement_price = $request->post('procurement_price');
        $new_product_demand->remark = $request->post('remark');
        $model = new UploadedFile();
        if(Yii::$app->request->isPost){
            $uploadedFile = UploadedFile::getInstanceByName("file");
        }
        $ymd = date("Ymd");
        $save_path = Yii::$app->basePath . '/web/upload/images/' . $ymd . '/';

        $save_url = dirname(Yii::$app->homeUrl).'/upload/images/'.$ymd . "/";
        if(!file_exists($save_path)){
            mkdir($save_path);
        }
        $file_name = $uploadedFile->getBaseName();
        $file_ext = $uploadedFile->getExtension();
        //新文件名
        $new_file_name = date('YmdHis') . '_' . rand(10000,99999) . '.' . $file_ext;
        $uploadedFile->saveAs($save_path . $new_file_name);
        $img = "http://" . $_SERVER['HTTP_HOST'] . $save_url . $new_file_name;
        $new_product_demand->img = $img;
        $new_product_demand->create_time = time();
        $new_product_demand->save();
        return success('添加完成');

    }

    /**
     * 查看需求详情
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-22
     * METHOD : GET
     */
    public function actionDemandDetail()
    {
        $request = Yii::$app->request;
        $id = $request->get('id');
        $result = NewProductDemand::Detail($id);
        return $result;
    }

    /**
     * 查看需求列表
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-22
     * METHOD : GET
     */
    public function actionDemandList()
    {
        $request = Yii::$app->request;
        $key = $request->get('key');
        $result = NewProductDemand::getList($this->member_id,$key);
        return $result;
    }
}
