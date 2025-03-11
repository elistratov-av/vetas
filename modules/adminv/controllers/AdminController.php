<?php

namespace app\modules\adminv\controllers;

use yii\filters\AccessControl;
use yii\web\Controller;

abstract class AdminController extends Controller
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
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        if (!\Yii::$app->user->isGuest) {
            /* @var $user \app\common\models\UserModel */
            $user = \Yii::$app->user->getIdentity();
            $id_specialist = \Yii::$app->session->get('__id_specialist');
            if (!empty($id_specialist)) {
                $user->setId_specialist($id_specialist);
            }
            if ($user->specialist === null) {
                \Yii::$app->session->remove('__id_specialist');

                return false;
            }
        }

        return parent::beforeAction($action);
    }

    /**
     * @return \app\common\components\rbac\DbManager
     * @throws \yii\base\InvalidConfigException
     */
    protected function authManager()
    {
        return \Yii::$app->get('authManager');
    }
}
