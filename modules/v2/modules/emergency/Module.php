<?php

namespace app\modules\v2\modules\emergency;

use app\modules\v2\modules\emergency\events\EmergencyEvent;
use app\modules\v2\modules\emergency\events\EmergencyEventHandler;
use yii\base\Module as YiiModule;

/**
 * Class Module
 * @package app\modules\v2\modules\emergency
 */
class Module extends YiiModule
{
    public $controllerNamespace = 'app\modules\v2\modules\emergency\controllers';

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();

        \Yii::$app->on(EmergencyEvent::EMERGENCY_CREATE_EVENT, function($event){
            $handler = new EmergencyEventHandler();
            $handler->handle($event);
        });
    }
}
