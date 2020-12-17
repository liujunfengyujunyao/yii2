<?php
namespace api\components\auth;

use yii\base\BaseObject;


/**
 * 登录错误相关错误吗.统一管理
 * @author whui
 *@date 2019-12-12
 */
class LoginError extends BaseObject
{
	const TOKEN_CHECK_ERR_CODE = 20009; //token校验错误码

    const WECHAT_NOT_BIND = 20008; //微信尚未绑定错误
}