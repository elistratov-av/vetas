<?php

namespace app\common\filters\auth;

use yii\filters\auth\HttpHeaderAuth;

/**
 * Class ExternalServiceHttpHeaderAuth
 * @package app\common\filters\auth
 */
class ExternalServiceHttpHeaderAuth extends HttpHeaderAuth
{
    use ExternalServiceAuthTrait;

    /**
     * {@inheritdoc}
     */
    public function authenticate($user, $request, $response)
    {
        $authHeader = $request->getHeaders()->get($this->header);

        if ($authHeader === null || $this->pattern === null) {
            return null;
        }

        if ($this->pattern === $authHeader) {
            return true;
        }

        return null;
    }
}
