<?php
namespace app\components\auth;

/**
 * 登陆生成token, 再次请求刷新token过期时间相关的方法. 各种可登陆模型包括医生, 辅医, 家长, 管理员等都需要实现此接口
 * @author whui
 * @date 2019-11-22
 */
interface LoginableInterface
{
	/**
	 * 产生一个token,token将被缓存起来用于标示当前登陆用户
	 */
	public function generateToken();

	/**
	 * 将产生的token缓存起来
	 */
	public function cacheToken($token);

	/**
	 * 刷新token过期时间
	 */
	public function freshTokenExpire($token);


	/**
	 * 获取登陆过期秒数
	 */
	public function getExpireTime();


	/**
	 * 登陆
	 */
	public function login();


}
