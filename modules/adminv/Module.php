<?php

namespace app\modules\adminv;

use app\modules\adminv\integration\bi\Api;
use app\modules\adminv\integration\bi\messages\SearchPetsReportRq;
use app\modules\adminv\integration\bi\messages\SearchPetsReportRs;
use app\modules\adminv\integration\bi\messages\UnvaccPetsReportRq;
use app\modules\adminv\integration\bi\messages\UnvaccPetsReportRs;
use app\modules\adminv\integration\bi\messages\VetServicesReportRq;
use app\modules\adminv\integration\bi\messages\VetServicesReportRs;
use yii\base\Module as YiiModule;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\ErrorHandler;
use yii\web\Request;
use yii\web\Response;
use app\modules\adminv\components\DbSession;

/**
 * Class Module
 * @package app\modules\adminv
 */
class Module extends YiiModule
{
    public $controllerNamespace = 'app\modules\adminv\controllers';

    protected $moduleComponents = [
        'request' => [
            'class' => Request::class,
            'enableCsrfValidation' => true,
            'enableCookieValidation' => true,
            'enableCsrfCookie' => true,
            'cookieValidationKey' => 'xjqW0E69sXUzFlnbXola2dtQp3e6XZ5p',
            'csrfParam'           => '__va_token',
        ],
        'response'   => [
            'class' => Response::class,
            'format' => Response::FORMAT_HTML
        ],
        'session' => [
            'class' => DbSession::class,
            'name'         => '__va_sessid',
            'cookieParams' => [
                'httpOnly' => true,
                'path'     => '/vetadmin',
            ],
        ],
        'biApi' => [
            'class' => Api::class,
            'timeout' => 240,
            'mappings' => [
                VetServicesReportRq::class => VetServicesReportRs::class,
                UnvaccPetsReportRq::class => UnvaccPetsReportRs::class,
                SearchPetsReportRq::class => SearchPetsReportRs::class,
            ],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->overrideErrorHandler();

        parent::init();
        $components = $this->moduleComponents;
        $local = implode(DIRECTORY_SEPARATOR, [__DIR__, 'config', 'components-local.php']);
        if (file_exists($local)) {
            $components = ArrayHelper::merge($components, require($local));
        }
        \Yii::$app->setComponents($components);

        \Yii::$app->homeUrl = Url::to(['/adminv/site/index']);
        \Yii::$app->user->loginUrl = ['/adminv/site/login'];
        \Yii::$app->user->enableSession = true;
        // \Yii::$app->user->enableAutoLogin = true;
        // логируем вход/выход
        \Yii::$app->user->on('afterLogin', [\app\modules\adminfstek\components\UserLogManager::class, 'successAdminLogin']);
        \Yii::$app->user->on('afterLogout', [\app\modules\adminfstek\components\UserLogManager::class, 'successAdminLogout']);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    private function overrideErrorHandler()
    {
        $errorHandler = \Yii::createObject([
            'class' => ErrorHandler::class,
            'errorAction' => '/adminv/site/error',
        ]);
        \Yii::$app->set('errorHandler', $errorHandler);
        $errorHandler->register();
    }
}
