<?php

namespace app\common\filters\auth;

use yii\filters\auth\HttpBasicAuth;

/**
 * Class ExternalServiceHttpBasicAuth
 * @package app\common\filters\auth
 */
class ExternalServiceHttpBasicAuth extends HttpBasicAuth
{
    use ExternalServiceAuthTrait;

    /**
     * @var string
     */
    private $username;
    /**
     * @var string
     */
    private $password;

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate($user, $request, $response)
    {
        list($username, $password) = $request->getAuthCredentials();

        if ($username === null || $password === null) {
            return null;
        }

        if ($username === $this->username && $password === $this->password) {
            return true;
        }

        if ($this->auth) {
            return call_user_func($this->auth, $username, $password);
        } else {
            if ($username === $this->username && $password === $this->password) {
                return true;
            }
        }

        return null;
    }
}
