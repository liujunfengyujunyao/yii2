<?php

namespace app\service;

use app\components\sms\SmsCode;
use app\models\Sms;
use Codeception\Util\Autoload;
use Exception;
use Yii;
use yii\base\UserException;
use yii\taobao\Autoloader;
use yii\taobao\top\request\AlibabaAliqinFcFlowGradeRequest;
use yii\taobao\top\request\AlibabaAliqinFcSmsNumSendRequest;
use yii\taobao\top\TopClient;
use yii\taobao\TopSdk;

/**
 * 短信发送逻辑层
 * @author JF
 * @Date 2020-10-14
 */
class SmsService extends BaseService
{

    /**
     * API实例
     * @param [type] $mobile
     * @author JF
     * @Date 2020-10-14
     */
    public static function sendSmsOne($mobile,$event)
    {
        $code = rand(100000,999999);
        $content = "【食迅生鲜】"."您正在使用" . $event ."功能,验证码为:" . $code;
        $extno = "";
        $sendtime = "";
        $result = self::sendMO($mobile,$extno,$content,$sendtime);
        $xml = simplexml_load_string($result);
        $jsonStr = json_encode($xml);
        $jsonArray = json_decode($jsonStr,true);
//        dump($jsonArray);
        if($jsonArray['returnstatus'] == 'Faild'){
            throw new UserException('发送短信失败：' . join(json_decode(json_encode($jsonArray['message']), true)), '-400');
        }else{
            $table = new Sms();
            $table->event = $event;
            $table->mobile = $mobile;
            $table->code =(string) $code;
            $table->createtime = time();
            $table->ip = \Yii::$app->request->userIP;
            $result = $table->save();
            if(!$result){
                throw new \yii\base\UserException('失败,'.join($table->getFirstErrors()), 400);
            }else{
                return success();
            }
        }
    }


    /**
     * 发送验证码
     * @param [type] $mobile
     * @author JF
     * @Date 2020-10-14
     */
    public static function sendVerifyCode($mobile, $event)
    {
//
        $smscode = new SmsCode();

//        $templateParam = [
////            'code' => (string) Yii::$app->smscode->genCode($mobile, $type),
//            'code' => $smscode->genCode($mobile, $type),
//        ];
        // 调用短信发送接口示例
        return self::sendSmsOne($mobile,$event);
    }



    public static function sendMO($mobile,$extno,$content,$sendtime){
        $body=array(
            'action'=>'send',
            'userid'=>'',
            'account'=>'XPT30204',
            'password'=>'caycw2',
//            'account' => "AA00796",
//            'password' => "AA0079645",
            'mobile'=>$mobile,
            'extno'=>$extno,
            'content'=>$content,
            'sendtime'=>$sendtime
        );
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://dx110.ipyy.net/sms.aspx");
//        curl_setopt($ch, CURLOPT_URL, "https://dx.ipyy.net/sms.aspx");
        curl_setopt($ch, CURLOPT_TIMEOUT,10);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
