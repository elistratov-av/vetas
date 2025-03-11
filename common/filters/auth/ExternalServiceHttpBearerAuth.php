<?php

namespace app\common\filters\auth;

use yii\filters\auth\HttpBearerAuth;

/**
 * Class ExternalServiceHttpBearerAuth
 * @package app\common\filters\auth
 */
class ExternalServiceHttpBearerAuth extends HttpBearerAuth
{
    use ExternalServiceAuthTrait;

    /**
     * @var string
     */
    private $token;

    /**
     * @param string $token
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate($user, $request, $response)
    {
        $authHeader = $request->getHeaders()->get($this->header);

        if ($authHeader === null || $this->pattern === null || $this->token === null) {
            return null;
        }

        if (preg_match($this->pattern, $authHeader, $matches)) {
            if ($this->token === $matches[1]) {
                return true;
            }
        }

        return null;
    }
}
