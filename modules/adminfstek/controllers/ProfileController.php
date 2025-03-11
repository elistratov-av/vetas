<?php

namespace app\modules\adminfstek\controllers;

use app\models\db\admin\AdminUser;
use app\modules\adminfstek\models\forms\AdminUserForm;
use app\modules\adminfstek\models\forms\UserPasswordChangeForm;
use app\modules\adminfstek\traits\PasswordTrait;
use yii\filters\AccessControl;

/**
 * Class ProfileController
 * @package app\modules\adminfstek\controllers
 */
class ProfileController extends AdminController
{
    use PasswordTrait;

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
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        /* @var $user \app\models\db\admin\AdminUser */
        $user = \Yii::$app->user->getIdentity();

        $model = new AdminUserForm(compact('user'));

        if ($model->updateUser()) {
            \Yii::$app->session->setFlash('success', 'Данные профиля обновлены');
            return $this->redirect(['index']);
        }

        return $this->render('/admin-users/edit', [
            'model' => $model,
            'roleOptions' => AdminUser::roleOptions(),
            'statusOptions' => AdminUser::blockStatusOptions(),
            'fromProfile' => true,
        ]);
    }

    /**
     * @return string|\yii\web\Response
     * @throws \yii\base\Exception
     */
    public function actionPasswordChange()
    {
        $user = \Yii::$app->user->getIdentity();

        $model = new UserPasswordChangeForm(compact('user'));

        if ($model->load(\Yii::$app->request->post()) && $model->changePassword()) {
            \Yii::$app->session->setFlash('success', 'Пароль успешно изменен');
            return $this->redirect(['site/index']);
        }

        return $this->render('/admin-users/password-change', [
            'model' => $model,
            'user' => $user,
        ]);
    }
}
