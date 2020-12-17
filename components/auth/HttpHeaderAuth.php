<?php
namespace api\components\auth;

use yii\base\UserException;
use api\components\User;
use yii\web\UnauthorizedHttpException;

/**
 * 重写HttpHeaderAuth组件.
 * @author whui
 * @date 2019-11-20
 */
class HttpHeaderAuth extends \yii\filters\auth\HttpHeaderAuth
{
	public $header = 'Token';

	/**
	 * 重写登陆失败处理
	 * @author whui
	 * @date 2019-12-09
	 * @param Response $response 响应对象
	 * @return null
	 * @throw UserException
	 */
	public function handleFailure($response)
	{
		throw new UnauthorizedHttpException("token校验失败,请重新登陆", LoginError::TOKEN_CHECK_ERR_CODE);
	}
}