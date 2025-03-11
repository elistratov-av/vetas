<?php

namespace app\modules\foundPet;

use yii\base\BootstrapInterface;
use yii\base\Module as YiiModule;
use yii\console\Application as ConsoleApplication;

/**
 * Class Module
 *
 * @package app\modules\foundPet
 */
class Module extends YiiModule implements BootstrapInterface
{
    public const LOG_CATEGORY = 'found_pet_module';

    public $controllerNamespace = 'app\modules\foundPet\controllers';

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        $this->params = require __DIR__ . '/config/params.php';
    }

    /**
     * @inheritDoc
     */
    public function bootstrap($app)
    {
        if ($app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'app\modules\foundPet\commands';

            return;
        }
        $app->getUrlManager()->addRules([
            'found-pet/<action:[\w-]+>' => 'foundPet/default/<action>',
            'found-pet/<controller:[\w-]+>' => 'foundPet/<controller>',
            'found-pet/<controller:[\w-]+>/<action:[\w-]+>' => 'foundPet/<controller>/<action>',
        ], false);
    }
}
