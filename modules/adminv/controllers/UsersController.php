<?php

namespace app\modules\adminv\controllers;

use app\common\components\rbac\Role;
use app\common\models\UserModel;
use app\modules\adminv\models\forms\UserForm;
use app\modules\adminv\models\forms\UserPasswordChangeForm;
use app\modules\adminv\models\search\UserSearch;
use app\modules\v2\common\rbac\AccessTrait;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * Class UsersController
 * @package app\modules\adminv\controllers
 */
class UsersController extends AdminController
{
    use AccessTrait;

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
                        'roles' => [Role::ROLE_SYSADMIN_GOS, Role::ROLE_MANAGEMENT_GOS],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->get());

        $auth = $this->authManager();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'auth' => $auth,
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
        if (!$user = UserModel::findOne($id)) {
            throw new NotFoundHttpException();
        }

        $model = new UserForm(compact('user'));

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
        if (!$user = UserModel::findOne($id)) {
            throw new NotFoundHttpException();
        }

        $model = new UserPasswordChangeForm(compact('user'));

        if ($model->load(\Yii::$app->request->post()) && $model->changePassword()) {
            return $this->redirect(['index']);
        }

        return $this->render('password-change', [
            'model' => $model,
            'user' => $user,
        ]);
    }
}
