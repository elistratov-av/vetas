<?php

namespace app\modules\admin\controllers;

use app\modules\admin\models\PasswordChangeForm;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use app\modules\admin\models\LoginForm;

/**
 * Class EntriesController
 * @package app\modules\admin\controllers
 */
class EntriesController extends Controller
{
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
                    [
                        'actions' => ['profile'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['password-change'],
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
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $this->layout = 'main-login';
        return $this->render('entry', [
            'model' => $model,
        ]);
    }


    /**
     *
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        $this->goHome();
    }


    /**
     * @return string
     */
    public function actionError()
    {
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            return $this->render('error', [
                'exception' => $exception,
            ]);
        }
    }


    /**
     * @return string
     */
    public function actionProfile()
    {
        $admin = \Yii::$app->user->identity;
        return $this->render('profile', [
            'admin' => $admin,
        ]);
    }

    /**
     * @return string|\yii\web\Response
     * @throws \yii\base\Exception
     */
    public function actionPasswordChange()
    {
        $admin = \Yii::$app->user->identity;
        $model = new PasswordChangeForm($admin);

        if ($model->load(Yii::$app->request->post()) && $model->changePassword()) {
            Yii::$app->user->logout();
            return $this->redirect(Url::to('/admin/login'));
        } else {
            return $this->render('password-change', [
                'model' => $model,
            ]);
        }
    }
}
