<?php
namespace app\controllers;
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods", "*");//允许任何method
header("Access-Control-Allow-Headers", "*");//允许任何自定义header
header("Access-Control-Allow-Credentials", "true");//允许跨域cookie
use app\models\Member;
use app\models\Supplier;
use app\models\UploadForm;
use common\components\sms\SmsCode;
use GuzzleHttp\Psr7\Response;
use \yii\base\Controller;
use app\service;
use yii\base\UserException;
use Yii;
use yii\web\UploadedFile;

class SupplierController extends Controller{

    /**
     * 短信发送(发送短信)
     * @author JF <qukaliujun@163.com>
     * @Date 2020-11-3
     */
    public function actionSendSms()
    {
        $request = Yii::$app->request;
        $mobile = $request->post('mobile');
        $event = $request->post('event');//发送短信类型 忘记密码/短信登录...
        if (empty($mobile) || empty($event)) {

            throw new UserException('参数提交错误', '-100');
        }
        if (!validateMobile($mobile)) {
            throw new UserException('电话验证失败，不符合规则', '-101');
        }
        $return = service\SmsService::sendVerifyCode($mobile,$event);
        return $return;
    }

    /**
     * 短信登录(提交)
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-14
     */
    public function actionSmsLogin()
    {
        $request = Yii::$app->request;
        $username = (string) $request->post('mobile');
        $code = (string) $request->post('code');
        $event = "短信登录";
        if(empty($username) || empty($code)){
//            throw new UserException('提交数据有误',-403);
            return fail([
                'msg'=>'提交数据有误'
            ]);
        }
        $token = Supplier::loginBySms($username,$code,$event);

        return success([
            'token' => $token,
        ]);
    }
    /**
     * 账号密码登录
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-14
     */
    public function actionAccountLogin()
    {
        $request = Yii::$app->request;
        $username = (string) $request->post('mobile');
        $password = (string) $request->post('password');

        if (empty($username) || empty($password)) {
            return fail('提交参数错误');
        }
        $token = Supplier::loginByAccount($username, $password);
        return success([
            'token' => $token,
        ]);
    }
    /**
     * 忘记密码(提交)
     * @author JF <qukaliujun@163.com>
     * @Date 2020-12-17
     */
    public function actionForgetPass()
    {
        $request = Yii::$app->request;
        $code = (string) $request->post('code');
        $newpassword = (string) $request->post('newpassword');
        if(empty($newpassword) || empty($code)){
            return fail([
                'msg'=>'提交数据有误'
            ]);
        }
        $headers = Yii::$app->getRequest()->getHeaders();
        $token = $headers->get('token');
        \Yii::$app->cache->get($token)?$supplier_id = \Yii::$app->cache->get($token):exit( header("HTTP/1.1 401 Forbidden"));
        $username = Supplier::findOne($supplier_id)['username'];
        $token = Supplier::loginBySms($username,$code,'忘记密码');

        if($token){
            $result = service\SupplierService::UpdatePass($supplier_id,$newpassword);
            return $result;
        }



    }
    /**
     * 供应商首页
     * @author JF <qukaliujun@163.com>
     * @Date 2020-11-03
     */
    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $headers = Yii::$app->getRequest()->getHeaders();
        $token = $headers->get('token');
        \Yii::$app->cache->get($token)?$member_id = \Yii::$app->cache->get($token):exit( header("HTTP/1.1 401 Forbidden"));
        halt($member_id);
        $password = "123456";
        $salt = "30ad6a1398";
        $pass = md5(md5($password).$salt);
        echo $pass;
    }





    /**
     * 根据header中包含的token获取用户信息(测试用)
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-14
     */
    public function actionInfoTest()
    {
        $request = \Yii::$app->request;
        $headers = Yii::$app->getRequest()->getHeaders();
        $token = $headers->get('token');
//        halt($token);
//        var_dump(Member::findIdentity(Member::getIdByToken($token)));//获取token对应的用户信息
        halt(\Yii::$app->cache->get($token));//获取用户ID
    }


    /*
     * 上传头像
     * $author JF <qukaliujun@163.com>
     * @Date 2020-12-16
     * @file 图片
     * */
    public function actionHeadUrl()
    {
        $headers = Yii::$app->getRequest()->getHeaders();
        $token = $headers->get('token');
        \Yii::$app->cache->get($token)?$supplier_id = \Yii::$app->cache->get($token):exit( header("HTTP/1.1 401 Forbidden"));
        if(Yii::$app->request->isPost) {
            $image = UploadedFile::getInstanceByName('file');
//            $imageName = $image->getBaseName();
            $imageName = createNonceStr(8);
            $ext = $image->getExtension();
            if(!in_array($ext,['jpg','png'])){
                return fail('图片格式错误');
            }
            $rootPath = 'assets/images/';
            $path = $rootPath.date('Y/m/d/');
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            $fullName = $path.$imageName.'.'.$ext;
            if($image->saveAs($fullName)) {
                $url = "http://".$_SERVER['HTTP_HOST'] ."/".$fullName;
                $supplier = Supplier::findOne($supplier_id);
                $supplier->head_url = $url;
                $supplier->save();
                return success($url);
            } else {
                return fail($image->error);
            }
        } else {
            return fail('请求方式错误');
        }
    }


    /*
     * 修改密码
     * $author JF <qukaliujun@163.com>
     * @Date 2020-12-16
     * newpassword 新密码
     * */
    public function actionUpdatePass()
    {
        $request = Yii::$app->request;
        $headers = Yii::$app->getRequest()->getHeaders();
        $token = $headers->get('token');
        $newpassword = $request->post('newpassword');
        \Yii::$app->cache->get($token)?$supplier_id = \Yii::$app->cache->get($token):exit( header("HTTP/1.1 401 Forbidden"));
        //1f003ecbdb553f20b04b760b738f6a32
        $result = service\SupplierService::UpdatePass($supplier_id,$newpassword);
        return $result;
    }




}
