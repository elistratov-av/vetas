<?php

namespace app\common\filters\auth;

/**
 * Trait ExternalServiceAuthTrait
 * @package app\common\filters\auth
 */
trait ExternalServiceAuthTrait
{
    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        if ($this->isOptional($action)) {
            return true;
        }

        $response = $this->response ?: \Yii::$app->getResponse();

        $identity = $this->authenticate(
            null,
            $this->request ?: \Yii::$app->getRequest(),
            $response
        );

        if ($identity === true) {
            return true;
        }

        $this->challenge($response);
        $this->handleFailure($response);

        return false;
    }
}
