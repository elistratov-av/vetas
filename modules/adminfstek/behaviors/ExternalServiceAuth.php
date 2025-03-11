<?php

namespace modules\adminfstek\behaviors;

use app\modules\adminfstek\traits\ExternalLogTrait;
use yii\filters\auth\HttpHeaderAuth;

/**
 * Class ExternalServiceAuth
 * @package app\common\behaviors
 */
class ExternalServiceAuth extends HttpHeaderAuth
{
    use ExternalLogTrait;

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        $response = $this->response ?: \Yii::$app->getResponse();

        $identity = $this->authenticate(
            null,
            $this->request ?: \Yii::$app->getRequest(),
            $response
        );

        if ($identity === true) {
            return true;
        }

        $this->handleFailure($response);

        return false;
    }

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

    /**
     * {@inheritdoc}
     */
    public function handleFailure($response)
    {
        $this->logAuth(false);

        parent::handleFailure($response);
    }
}
