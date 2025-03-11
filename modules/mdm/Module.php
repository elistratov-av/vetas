<?php

namespace app\modules\mdm;

use yii\base\BootstrapInterface;
use yii\base\Module as YiiModule;
use yii\helpers\ArrayHelper;

/**
 * Class Module
 * @package app\modules\mdm
 */
class Module extends YiiModule implements BootstrapInterface
{
    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        // Инициализируем компоненты
        if (\Yii::$app instanceof \yii\web\Application) {
            \Yii::configure($this, require(__DIR__ . '/config/web.php'));
        }
    }

    /**
     * @inheritDoc
     */
    public function bootstrap($app)
    {
        $enabled = ArrayHelper::getValue($this->params, 'enabled');
        if ($enabled === true) {
            $app->getUrlManager()->addRules([
                'mdm/<controller:\w+>' => 'mdm/<controller>',
                'mdm/<controller:[\w-]+>/<action:[\w-]+>' => 'mdm/<controller>/<action>',
            ], false);
        }
    }
}
