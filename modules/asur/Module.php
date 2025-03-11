<?php

namespace app\modules\asur;

use yii\base\BootstrapInterface;
use yii\base\Module as YiiModule;
use yii\web\Application;

/**
 * Class Module
 * @package app\modules\asur
 */
class Module extends YiiModule implements BootstrapInterface
{
    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        $app->getUrlManager()->addRules([
            'asur/<controller:\w+>' => 'asur/<controller>',
            'asur/<controller:[\w-]+>/<action:[\w-]+>' => 'asur/<controller>/<action>',
        ], false);
    }
}
