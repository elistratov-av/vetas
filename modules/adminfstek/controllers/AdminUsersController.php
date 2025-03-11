<?php

namespace app\modules\adminfstek\controllers;

use app\models\db\admin\AdminUser;
use app\modules\adminfstek\components\UserLogManager;
use app\modules\adminfstek\models\forms\AdminUserForm;
use app\modules\adminfstek\models\search\AdminUsersSearch;
use app\modules\adminfstek\traits\PasswordTrait;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Class AdminUsersController
 * @package app\modules\adminfstek\controllers
 */
class AdminUsersController extends AdminController
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
                        'roles' => [AdminUser::ROLE_ADMIN, AdminUser::ROLE_ACCOUNTS_MANAGER, AdminUser::ROLE_SECURITY],
                        'actions' => ['index', 'edit'],
                    ],
                    [
                        'allow' => true,
                        'roles' => [AdminUser::ROLE_ADMIN, AdminUser::ROLE_ACCOUNTS_MANAGER],
                        'actions' => ['create', 'password-change'],
                    ],
                    [
                        'allow' => true,
                        'roles' => [AdminUser::ROLE_ACCOUNTS_MANAGER, AdminUser::ROLE_SECURITY],
                        'actions' => ['block', 'unblock'],
                    ],
                    [
                        'allow' => true,
                        'roles' => [AdminUser::ROLE_ACCOUNTS_MANAGER],
                        'actions' => ['mark-deleted'],
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
        $searchModel = new AdminUsersSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'roleOptions' => AdminUser::roleOptions(),
            'statusOptions' => AdminUser::blockStatusOptions(),
        ]);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $user = new AdminUser();
        $model = new AdminUserForm(compact('user'));

        if ($model->createUser()) {
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
            'roleOptions' => AdminUser::roleOptions(),
            'statusOptions' => AdminUser::blockStatusOptions(),
        ]);
    }

    /**
     * @param int $id
     * @return string|\yii\web\Response
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionEdit($id)
    {
        $user = $this->findUser($id);

        $model = new AdminUserForm(compact('user'));
        if (\Yii::$app->user->can(AdminUser::ROLE_SECURITY)) {
            $model->scenario = $model::SCENARIO_BLOCK_UNBLOCK;
        }

        if ($model->updateUser()) {
            return $this->redirect(['index']);
        }

        return $this->render('edit', [
            'model' => $model,
            'roleOptions' => AdminUser::roleOptions(),
            'statusOptions' => AdminUser::blockStatusOptions(),
            'fromProfile' => ($user->id == \Yii::$app->user->getId())
        ]);
    }

    /**
     * @param int $id
     * @return string|\yii\web\Response
     * @throws \yii\base\Exception
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionPasswordChange($id)
    {
        $user = $this->findUser($id);

        if (\Yii::$app->user->id == $user->id) {
            return $this->redirect(['profile/password-change']);
        }

        if (empty($user->email)) {
            \Yii::$app->session->setFlash('error', 'У пользователя не указан email. Сброс пароля недоступен');

            return $this->redirect(['index']);
        }
        // автогенерация временного пароля и отправка по email
        $password = $this->generatePassword();
        if (!$user->setPassword($password, true)) {
            \Yii::$app->session->setFlash('error', $user->getFirstError('password'));

            return $this->redirect(['index']);
        }

        if ($user->savePassword()) {
            if ($this->sendPassword($user->email, $password)) {
                \Yii::$app->session->setFlash('success', 'Временный пароль отправлен на email пользователя');
            } else {
                \Yii::$app->session->setFlash('error', 'Ошибка при отправке временного пароля');
            }
        } else {
            \Yii::$app->session->setFlash('error', 'Ошибка при сохранении временного пароля');
        }

        return $this->redirect(['index']);
    }

    /**
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionBlock($id)
    {
        if ($id == \Yii::$app->user->id) {
            throw new ForbiddenHttpException();
        }

        $model = $this->findUser($id);

        $model->is_blocked = AdminUser::STATUS_BLOCKED;
        $result = $model->save(true, ['is_blocked', 'updated_at', 'updated_by']);
        UserLogManager::manualAdminUserBlock($model, $result);

        return $this->redirect(['index']);
    }

    /**
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionUnblock($id)
    {
        if ($id == \Yii::$app->user->id) {
            throw new ForbiddenHttpException();
        }

        $model = $this->findUser($id);

        $model->is_blocked = AdminUser::STATUS_NOT_BLOCKED;
        $result = $model->save(true, ['is_blocked', 'updated_at', 'updated_by']);
        UserLogManager::manualAdminUserUnblock($model, $result);

        return $this->redirect(['index']);
    }

    /**
     * @param int $id
     * @return \yii\web\Response
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionMarkDeleted($id)
    {
        $user = $this->findUser($id);

        if ($user->is_deleted) {
            \Yii::$app->session->setFlash('error', 'Пользователь уже удален');
        } else {
            $result = $user->markDeleted();
            if ($result) {
                \Yii::$app->session->setFlash('success', 'Пользователь успешно удален');
            } else {
                \Yii::$app->session->setFlash('error', 'Ошибка при удалении пользователя');
            }
            UserLogManager::deleteAdminUser($user, $result);
        }

        return $this->redirect(['index']);
    }

    /**
     * @param int $id
     * @return \app\models\db\admin\AdminUser|null
     * @throws \yii\web\NotFoundHttpException
     */
    private function findUser($id)
    {
        $model = AdminUser::findOne(['id' => $id]);

        if ($model === null) {
            throw new NotFoundHttpException();
        }

        return $model;
    }
}
