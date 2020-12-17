<?php
namespace api\components;

use Yii;
use yii\base\UserException;
use yii\web\HttpException;

/**
 * 错误处理函数.当内部抛出异常或者其他错误则直接将错误返回给前端
 * @author whui
 * @date 2019-11-22
 */
class ErrorHandler extends \yii\web\ErrorHandler
{
	protected function renderException($exception)
	{

        $response = Yii::$app->getResponse();
        $response->isSent = false;
        $response->stream = null;
        $response->data = null;
        $response->content = null;
        //$response->setStatusCodeByException($exception);
        $response->setStatusCode(200);
        $response->data = $this->convertExceptionToArray($exception);
        $response->send();
	}

	protected function convertExceptionToArray($exception)
	{
        $array = [
            'message' => $exception->getMessage(),
            'result' => $exception->getCode()?:500,
            'data' => ['info'=>$exception->getMessage()],
        ];

        if (YII_ENV != 'prod') {
            if (!$exception instanceof UserException) {
                $array['data']['type'] = get_class($exception);
                $array['data']['file'] = $exception->getFile();
                $array['data']['line'] = $exception->getLine();
                $array['data']['stack-trace'] = explode("\n", $exception->getTraceAsString());
                if ($exception instanceof \yii\db\Exception) {
                    $array['data']['info'] = $exception->errorInfo;
                }
            }
        }
        return $array;
	}
}