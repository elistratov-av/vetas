<?php

namespace app\modules\admin\controllers;

use app\common\components\rbac\DbManager;
use app\common\models\UserModel;
use app\modules\admin\models\forms\UserForm;
use app\modules\admin\models\forms\UserPasswordChangeForm;
use app\modules\admin\models\search\UserSearch;
use yii\web\NotFoundHttpException;

/**
 * Class UsersController
 * @package app\modules\admin\controllers
 */
class UsersController extends AdminController
{
    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'auth' => $this->authManager(),
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
            return $this->redirect(['/admin/users/index']);
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
            return $this->redirect(['/admin/users/index']);
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
            return $this->redirect(['/admin/users/index']);
        }

        return $this->render('password-change', [
            'model' => $model,
            'user' => $user,
        ]);
    }

    /**
     * @return \app\common\components\rbac\DbManager
     * @throws \yii\base\InvalidConfigException
     */
    protected function authManager()
    {
        return \Yii::createObject(DbManager::class);
    }
}
