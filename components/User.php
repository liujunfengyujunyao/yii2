<?php
namespace api\components;

use api\components\auth\UserEvent;

/**
 * 重写User组件. 注册token验证后的刷新过期时间操作. 重写token验证登陆操作
 * @author whui
 * @date 2019-11-22
 */
class User extends \yii\web\User
{
	const EVENT_AFTER_TOKEN_LOGIN = 'afterTokenLogin';


	//重写父类init.
	public function init()
	{
		//每次登陆后都要刷新token有效期
		$this->on(User::EVENT_AFTER_TOKEN_LOGIN, function($event){
			// var_dump($event);
			$event->identity->freshTokenExpire($event->token);
		});
	}

	public function loginByAccessToken($token, $type = null)
    {
        /* @var $class IdentityInterface */
        $class = $this->identityClass;
        $identity = $class::findIdentityByAccessToken($token, $type);
        if ($identity && $this->login($identity)) {
        	$this->trigger(self::EVENT_AFTER_TOKEN_LOGIN, new UserEvent([
            'identity' => $identity,
            'token' => $token,

        ]));
            return $identity;
        }

        return null;
    }
	
}