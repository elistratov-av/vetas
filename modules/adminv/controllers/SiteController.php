<?php

namespace app\modules\adminv\controllers;

use app\models\db\audit\LogUsersAuth;
use app\modules\adminfstek\components\UserLogManager;
use app\modules\adminv\models\forms\LoginForm;
use yii\filters\AccessControl;

/**
 * Class SiteController
 * @package app\modules\adminv\controllers
 */
class SiteController extends AdminController
{
    /**
     * @var string
     */
    public $layout = 'main';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setViewPath('@modules/admin/views/entries');
    }

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
                        'actions' => ['error', 'select-organization'],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        if ($action->id == 'login') {
            \Yii::$app->session->remove('__login');
            \Yii::$app->session->remove('__organizations');
        }

        return parent::beforeAction($action);
    }

    /**
     * @return string|\yii\web\Response
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
                return $this->goHome();
            } elseif ($model->requireSelectOrganization === true) {
                return $this->redirect(['select-organization']);
            } else {
                // логируем ошибку логина
                UserLogManager::errorAdminLogin($model->user ?? $model->login, LogUsersAuth::TARGET_VETADMIN);
            }
        }

        $this->layout = 'main-login';

        return $this->render('entry', [
            'model' => $model,
        ]);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionLogout()
    {
        if (\Yii::$app->user->logout()) {
            \Yii::$app->session->remove('__id_specialist');
            \Yii::$app->session->remove('__login');
            \Yii::$app->session->remove('__organizations');
        }

        return $this->goHome();
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionSelectOrganization()
    {
        $model = new LoginForm([
            'scenario' => LoginForm::SCENARIO_SELECT_ORGANIZATION
        ]);
        if ($model->load(\Yii::$app->request->post())) {
            if ($model->selectOrganization()) {
                return $this->goHome();
            } else {
                return $this->redirect(['login']);
            }
        }

        $this->layout = 'main-login';

        return $this->render('select-organization', [
            'model' => $model,
        ]);
    }

    /**
     * @return bool|string
     */
    public function getViewPath()
    {
        return \Yii::getAlias('@modules/admin/views/entries');
    }
}
