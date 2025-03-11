<?php

namespace app\common\components;


use yii\di\Instance;
use yii\filters\auth\AuthMethod;

/**
 * Class JwtHttpBearerAuth
 * @package app\common\components
 */
class JwtHttpBearerAuth extends AuthMethod
{
    /**
     * @var Jwt
     */
    public $jwt;

    /**
     * {@inheritdoc}
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->jwt = Instance::ensure('jwt', Jwt::class);
    }


    /**
     * {@inheritdoc}
     * @throws \yii\base\InvalidConfigException
     */
    public function authenticate($user, $request, $response)
    {
        $tokenString = $this->extractTokenFromHeaders($request);

        if ($tokenString !== null) {
            $token = $this->jwt->loadToken($tokenString);
            if ($token === null) {
                return null;
            }

            $identity = $user->loginByAccessToken($token, get_class($this));

            if ($identity === null || $identity->is_blocked) {
                return null;
            }

            // логируем псевдосессию юзера
            /* @var $sessionManager \app\modules\adminfstek\components\UserSessionManager */
            $sessionManager = \Yii::$app->get('userSessionManager');
            $sessionManager->logSession($token);

            return $identity;
        }

        return null;
    }

    /**
     * @param \yii\web\Request $request
     * @return string|null
     */
    public function extractTokenFromHeaders($request = null)
    {
        $request = $request ?? \Yii::$app->getRequest();
        $authHeader = $request->getHeaders()->get('Authorization');
        if ($authHeader !== null && preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
            return empty($matches[1]) ? null : $matches[1];
        }

        return null;
    }
}
