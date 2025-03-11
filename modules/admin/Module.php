<?php

namespace app\modules\admin;

use app\modules\admin\models\Admin;
use yii\base\Module as YiiModule;
use yii\web\ErrorHandler;
use yii\web\Request;
use yii\web\Response;
use yii\web\User;

/**
 * Class Module
 * @package app\modules\admin
 */
class Module extends YiiModule
{
    public $controllerNamespace = 'app\modules\admin\controllers';

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
        'user' => [
            'class' => User::class,
            'identityClass' => Admin::class,
            'loginUrl' => '/admin/login'
        ],
    ];

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        \Yii::$app->setComponents($this->moduleComponents);
        $this->defaultRoute = 'entries/index';
        \Yii::$app->homeUrl = '/admin';

        \Yii::configure($this, [
            'components' => [
                'errorHandler' => [
                    'class' => ErrorHandler::className(),
                    'errorAction' => 'admin/entries/error',
                ]
            ]
        ]);

        /** @var ErrorHandler $handler */
        $handler = $this->get('errorHandler');
        \Yii::$app->set('errorHandler', $handler);
        $handler->register();
    }


}
