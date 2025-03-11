<?php

namespace app\modules\adminfstek\controllers;

use app\modules\adminfstek\components\UserLogManager;
use app\modules\adminfstek\models\forms\LoginForm;
use yii\filters\AccessControl;

/**
 * Class SiteController
 * @package app\modules\adminfstek\controllers
 */
class SiteController extends AdminController
{
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
                        'actions' => ['login'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['error'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionLogin()
    {
        $model = new LoginForm();
        if ($model->load(\Yii::$app->request->post())) {
            if ($model->login()) {
                return $model->needsChangePassword() ? $this->redirect(['profile/password-change']) : $this->goBack();
            } else {
                // логируем ошибку логина
                UserLogManager::errorAdminLogin($model->user ?? $model->login);
            }
        }

        $this->layout = 'main-login';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        \Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * @return string
     */
    public function actionError()
    {
        $exception = \Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            return $this->render('error', [
                'exception' => $exception,
            ]);
        }
    }
}
