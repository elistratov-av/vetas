<?php

namespace app\modules\elk;

use yii\base\BootstrapInterface;
use yii\base\Module as YiiModule;

/**
 * Class Module
 * @package app\modules\elk
 */
class Module extends YiiModule implements BootstrapInterface
{
    public $defaultRoute = 'wsdl';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Инициализируем компоненты
        if (\Yii::$app instanceof \yii\web\Application) {
            \Yii::configure($this, require(__DIR__ . '/config/web.php'));
        }

        if (\Yii::$app instanceof \yii\console\Application) {
            \Yii::configure($this, require(__DIR__ . '/config/console.php'));
        }
    }

    public function bootstrap($app)
    {
        $app->getUrlManager()->addRules([
            'elk/<controller:\w+>' => 'elk/<controller>',
            'elk/<controller:[\w-]+>/<action:[\w-]+>' => 'elk/<controller>/<action>',
        ], false);

        if ($app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'app\modules\elk\commands';
        }
    }
}
