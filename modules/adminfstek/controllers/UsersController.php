<?php

namespace app\modules\adminfstek\controllers;

use app\common\models\UserModel;
use app\models\db\admin\AdminUser;
use app\models\db\Files;
use app\modules\adminfstek\components\UserLogManager;
use app\modules\adminfstek\models\forms\UserForm;
use app\modules\adminfstek\models\search\UsersSearch;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Class UsersController
 * @package app\modules\adminfstek\controllers
 */
class UsersController extends AdminController
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
        $searchModel = new UsersSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'auth' => $this->frontendAuthManager(),
        ]);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $user = new UserModel();
        $model = new UserForm(compact('user'));

        if ($model->createUser()) {
            \Yii::$app->session->setFlash('warning', 'Выберите организацию и назначьте роль пользователю');
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
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

        $model = new UserForm(compact('user'));
        if (\Yii::$app->user->can(AdminUser::ROLE_SECURITY)) {
            $model->scenario = $model::SCENARIO_BLOCK_UNBLOCK;
        }

        if ($model->updateUser()) {
            return $this->redirect(['index']);
        }

        return $this->render('edit', [
            'model' => $model,
            'user' => $user,
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

        $model->is_blocked = true;
        $result = $model->save(true, ['is_blocked', 'updated_at', 'updated_by']);
        UserLogManager::manualApiUserBlock($model, $result);

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

        $model->is_blocked = false;
        $model->block_until = null;
        $result = $model->save(true, ['is_blocked', 'block_until', 'updated_at', 'updated_by']);
        UserLogManager::manualApiUserUnblock($model, $result);

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
            if (!empty($user->specialists)) {
                foreach ($user->specialists as $specialist) {
                    if ($specialist->isExpelledAtDate() === false) {
                        \Yii::$app->session->setFlash('error', 'До увольнения специалиста удаление пользователя невозможно');
                        return $this->redirect(['index']);
                    }
                }
            }
            $result = $user->markDeleted();
            if ($result) {
                \Yii::$app->session->setFlash('success', 'Пользователь успешно удален');
                $this->deleteUserPhotos($user);
            } else {
                \Yii::$app->session->setFlash('error', 'Ошибка при удалении пользователя');
            }
            UserLogManager::deleteApiUser($user, $result);
        }

        return $this->redirect(['index']);
    }

    /**
     * @param int $id
     * @return \app\common\models\UserModel|null
     * @throws \yii\web\NotFoundHttpException
     */
    private function findUser($id)
    {
        $model = UserModel::findOne(['id' => $id]);

        if ($model === null) {
            throw new NotFoundHttpException();
        }

        return $model;
    }

    /**
     * @return \app\common\components\rbac\DbManager
     * @throws \yii\base\InvalidConfigException
     */
    protected function frontendAuthManager()
    {
        return \Yii::$app->get('frontendAuthManager');
    }

    /**
     * @param \app\common\models\UserModel $user
     */
    private function deleteUserPhotos($user)
    {
        if (!empty($user->specialists)) {
            foreach ($user->specialists as $specialist) {
                \Yii::$app->db
                    ->createCommand()
                    ->delete(Files::tableName(), ['entity_id' => $specialist->id, 'entity_type' => 'specialist'])
                    ->execute();
            }
        }
    }
}
