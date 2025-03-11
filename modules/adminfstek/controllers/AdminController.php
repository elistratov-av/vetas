<?php

namespace app\modules\adminfstek\controllers;

use app\modules\adminfstek\traits\PasswordTrait;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * Class AdminController
 * @package app\modules\adminfstek\controllers
 */
abstract class AdminController extends Controller
{
    use PasswordTrait;

    public $layout = 'main';

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            /* @var $user \app\models\db\admin\AdminUser */
            $user = \Yii::$app->user->getIdentity();
            if ($user === null) {
                return true;
            }
            $actionId = $action->getUniqueId();

            if ($actionId != 'adminfstek/site/logout' && ($user->is_blocked || $user->isBlockedUntil())) {
                \Yii::$app->user->logout();
                \Yii::$app->response->redirect(['adminfstek/site/login']);
            }

            $excluded = [
                'adminfstek/site/login',
                'adminfstek/site/logout',
                'adminfstek/site/error',
                'adminfstek/profile/password-change',
            ];
            if (!in_array($actionId, $excluded, true) && $user->needsChangePassword()) {
                \Yii::$app->session->setFlash('info', 'Необходимо поменять пароль для дальнейшей работы в системе');
                \Yii::$app->response->redirect(['adminfstek/profile/password-change']);
            }

            return true;
        }

        return false;
    }
}
