<?php

namespace app\modules\adminfstek;

use app\models\db\admin\AdminUser;
use app\modules\adminfstek\components\AccessChecker;
use yii\base\Module as YiiModule;
use yii\helpers\Url;
use yii\web\ErrorHandler;
use yii\web\Request;
use yii\web\Response;
use app\modules\adminfstek\components\DbSession;
use Yii;
use yii\web\User;

/**
 * Class Module
 * @package app\modules\adminfstek
 */
class Module extends YiiModule
{
    public $controllerNamespace = 'app\modules\adminfstek\controllers';

    protected $moduleComponents = [
        'request' => [
            'class' => Request::class,
            'enableCsrfValidation' => true,
            'enableCookieValidation' => true,
            'enableCsrfCookie' => true,
            'cookieValidationKey' => 'xjbXola2dtQp3qW0E69sXUzFlne6XZ5p',
            'csrfParam' => '__fa_token',
            'ipHeaders' => [
                'X-Real-Ip'
            ],
        ],
        'response' => [
            'class' => Response::class,
            'format' => Response::FORMAT_HTML,
        ],
        'session' => [
            'class' => DbSession::class,
            'name' => '__fa_sessid',
            'cookieParams' => [
                'httpOnly' => true,
                'path' => '/adminf',
            ],
        ],
        'user' => [
            'class' => User::class,
            'identityClass' => AdminUser::class,
            'loginUrl' => ['/adminfstek/site/login'],
            'accessChecker' => AccessChecker::class,
            'enableSession' => true,
            // 'enableAutoLogin' => true,
            'on afterLogin' => [\app\modules\adminfstek\components\UserLogManager::class, 'successAdminLogin'], // логируем вход
            'on afterLogout' => [\app\modules\adminfstek\components\UserLogManager::class, 'successAdminLogout'], // логируем выход
        ],
        'authManager' => null,
        'frontendAuthManager' => [
            'class' => 'app\common\components\rbac\DbManager',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->overrideErrorHandler();

        parent::init();

        Yii::$app->db->createCommand("DELETE FROM admin.sessions_admin WHERE valid_until < '". date('Y-m-d H:i:s') . "'")->execute();

        \Yii::$app->setComponents($this->moduleComponents);

        \Yii::$app->homeUrl = Url::to(['/adminfstek/site/index']);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    private function overrideErrorHandler()
    {
        $errorHandler = \Yii::createObject([
            'class' => ErrorHandler::class,
            'errorAction' => '/adminfstek/site/error',
        ]);
        \Yii::$app->set('errorHandler', $errorHandler);
        $errorHandler->register();
    }
}
