<?php

namespace app\components\sms;

use Codeception\Util\Autoload;
use common\components\sms\SmsSendInterface;
use yii\base\Component;
use Yii;

/**
 * 短信验证码相关组件.包括生成验证码,
 * @author whui
 * @date 2019-12-10
 */
class SmsCode extends Component
{
    const TYPE_REGIST = 'regist'; //注册验证码
    const TYPE_LOGIN = 'login'; //登陆验证码
    const TYPE_WITHDRAW = 'withdraw'; // 提现

    public $duration = 300; //验证码缓存时间300s

    /**
     * 生成并缓存一个手机验证码
     * @author whui
     * @date 2019-12-10
     * @param string $mobile 手机号
     * @param string $type 类型.用于区分不同用途的验证码,防止相互覆盖.
     */
    public function genCode(string $mobile, string $type)
    {
        $code = random_int(10000, 99999);
        $key = $this->getKey($mobile, $type);
        Yii::$app->cache->set($key, $code, $this->duration);
        return $code;
    }

    /**
     * 校验手机验证码
     * @author whui
     * @date 2019-12-10
     * @param string $mobile
     * @param string $type
     * @param string|int $code
     */
    public function validateCode($mobile, $type, $code)
    {
        $key = $this->getKey($mobile, $type);
        $store = Yii::$app->cache->get($key);
        return $store == $code;
    }

    /**
     * 根据手机号,类型生成统一缓存key
     * @author whui
     */
    public function getKey($mobile, $type)
    {
        return $mobile . '_' . $type;
    }

    /**
     * 验证注册时的验证码
     * @author whui
     * @date 2019-12-10
     * @param string $mobile
     * @param string|int $code
     */
    public function validateRegistCode($mobile, $code)
    {
        return $this->validateCode($mobile, static::TYPE_REGIST, $code);
    }

    /**
     * 生成注册验证码
     */
    public function genRegistCode($mobile)
    {
        return $this->genCode($mobile, static::TYPE_REGIST);
    }
}
