<?php

namespace app\modules\subscription;

use yii\base\Module as YiiModule;
use yii\web\ErrorHandler;
use yii\web\Request;
use yii\web\Response;

/**
 * Class Module
 * @package app\modules\admin
 */
class Module extends YiiModule
{
    public $controllerNamespace = 'app\modules\subscription\controllers';

    protected $moduleComponents = [
        'request' => [
            'class' => Request::class,
            'enableCsrfValidation' => true,
            'enableCookieValidation' => false,
            'enableCsrfCookie' => false,
        ],
        'response'   => [
            'class' => Response::class,
            'format' => Response::FORMAT_HTML
        ],
    ];

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        /** @var ErrorHandler $errorHandler */
        $errorHandler = \Yii::createObject([
            'class' => ErrorHandler::class,
            'errorAction' => 'subscription/default/error',
        ]);
        \Yii::$app->set('errorHandler', $errorHandler);
        $errorHandler->register();

        parent::init();
        \Yii::$app->setComponents($this->moduleComponents);
        //$this->defaultRoute = '/subscription';
        //\Yii::$app->homeUrl = '/subscription';
    }


}
