<?php

namespace app\modules\adminfstek\traits;

/**
 * Trait ExternalLogTrait
 * @package app\modules\adminfstek\components
 */
trait ExternalLogTrait
{
    /**
     * @param bool   $isSuccess
     * @param string $protocol
     * @param string $interface
     */
    public function logAuth($isSuccess = true, $protocol = null, $interface = null)
    {
        $authLogger = \Yii::$app->controller->module->get('authLogger', false);
        if ($authLogger !== null) {
            /* @var $authLogger \app\modules\adminfstek\components\ExternalLogManager */
            $authLogger->logAuth($isSuccess, $protocol, $interface);
        }
    }
}
