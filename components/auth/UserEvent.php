<?php
namespace api\components\auth;

/**
 * 扩展用户组件,登陆事件带上对应的token
 * @author whui
 * @date 2019-11-22
 */
class UserEvent extends \yii\web\UserEvent
{
	public $token;
}