<?php

namespace app\components\auth;

use Yii;
use yii\base\NotSupportedException;

/**
 * 主要是实现loginableInterface登陆相关接口. 角色模型类直接use 此trait就好.
 * @author whui
 * @date 2019-11-22
 */
trait LoginableTrait
{
    public $expire = 43200;

    public function generateToken()
    {
        return Yii::$app->security->generatePasswordHash($this->getId() . '_' . time());
    }

    public function cacheToken($token)
    {
        // return Yii::$app->redis->setex($token, $this->getExpireTime(), $this->getId());
        return Yii::$app->cache->set($token, $this->getId(), $this->getExpireTime());
    }

    public function freshTokenExpire($token)
    {
        // return Yii::$app->redis->expire($token, $this->getExpireTime());
        return Yii::$app->cache->set($token, Yii::$app->cache->get($token), $this->getExpireTime());
    }

    public function getExpireTime()
    {
        return $this->expire;
    }

    public static function getIdByToken($token)
    {
        $id = Yii::$app->cache->get($token);
        return $id;
    }

    public function login()
    {
        $token = $this->generateToken();
        $this->cacheToken($token);
        return $token;
    }

    /**
     * Finds an identity by the given ID.
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * Finds an identity by the given token.
     * @param mixed $token the token to be looked for
     * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
     * For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     * @return IdentityInterface|null the identity object that matches the given token.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $id = static::getIdByToken($token);
        return $id ? static::findIdentity($id) : null;
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|int an ID that uniquely identifies a user identity.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns a key that can be used to check the validity of a given identity ID.
     *
     * The key should be unique for each individual user, and should be persistent
     * so that it can be used to check the validity of the user identity.
     *
     * The space of such keys should be big enough to defeat potential identity attacks.
     *
     * This is required if [[User::enableAutoLogin]] is enabled. The returned key will be stored on the
     * client side as a cookie and will be used to authenticate user even if PHP session has been expired.
     *
     * Make sure to invalidate earlier issued authKeys when you implement force user logout, password change and
     * other scenarios, that require forceful access revocation for old sessions.
     *
     * @return string a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     */
    public function getAuthKey()
    {
        throw new NotSupportedException('未实现通过authkey认证.');
    }

    /**
     * Validates the given auth key.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @param string $authKey the given auth key
     * @return bool whether the given auth key is valid.
     * @see getAuthKey()
     */
    public function validateAuthKey($authKey)
    {
        throw new NotSupportedException('未实现通过authkey认证');
    }

    public function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }
}
