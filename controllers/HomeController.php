<?php
namespace app\controllers;
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods", "*");//允许任何method
header("Access-Control-Allow-Headers", "*");//允许任何自定义header
header("Access-Control-Allow-Credentials", "true");//允许跨域cookie
use app\models\Member;
use app\models\Supplier;
use app\models\Z;
use common\components\sms\SmsCode;
use \yii\base\Controller;
use app\service;
use yii\base\UserException;
use Yii;
class HomeController extends Controller{


    public function actionIndexTest()
    {
        $request = \Yii::$app->request;
        $id = $request->get('id');
//        echo $request->userIP;
        $sql = "select * from z where id=:id";//防止sql注入
//        dd(Z::findBySql($sql)->all()->asArray());//执行原生sql
//        dump(Z::findBySql($sql,[':id'=>$id])->asArray()->all());
//        dump(Z::find()->where(['between','id',2,5])->asArray()->all());
        $data = new Z();
        $data->title = "测试6";
        $data->number = 100;
        $data->save();
        halt($data->attributes['id']);//获取最后一次添加的ID
        dump(Z::updateAllCounters(['number'=>1],['id'=>1]));//id=1 number+1
    }

    /**
     * 短信发送(发送短信)
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-14
     */
    public function actionSendSms()
    {
        $request = Yii::$app->request;
        $mobile = $request->post('mobile');
        $event = $request->post('event');//发送短信类型 注册/忘记密码/短信登录...
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
        $token = Member::loginBySms($username,$code,$event);

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
        $username = (string) $request->post('username');
        $password = (string) $request->post('password');
        if (empty($username) || empty($password)) {
            throw new UserException('提交数据有误', -401);
        }
        $token = Member::loginByAccount($username, $password);
//        return $token;
        return success([
            'token' => $token,
        ]);
    }

    /**
     * 注册账户
     * @author JF <qukaliujun@163.com>
     * @Date 2020-10-16
     */
    public function actionRegister()
    {
        $request = Yii::$app->request;
        $username = (string) $request->post('mobile');
        $password = (string) $request->post('password');
        $rpassword = (string) $request->post('rpassword');
        $code = (string) $request->post('code');
        $linkman = (string) $request->post('linkman');
        $company = (string) $request->post('company');
        $event = "注册";
        if($password !== $rpassword){
            throw new UserException('两次密码不一致', -401);
        }
        $result = Member::register($username,$password,$code,$linkman,$company,$event);
        return $result;
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
        var_dump(Member::findIdentity(Member::getIdByToken($token)));//获取token对应的用户信息
        halt(\Yii::$app->cache->get($token));//获取用户ID
    }



}
