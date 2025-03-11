<?php

namespace app\modules\admin\controllers;

use app\common\components\rbac\DbManager;
use app\common\components\rbac\Role;
use app\common\models\UserModel;
use app\models\db\Organizations;
use app\models\db\Specialists;
use app\modules\admin\models\search\SpecialistSearch;
use app\modules\admin\models\forms\SpecialistForm;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Class SpecialistsController
 * @package app\modules\admin\controllers
 */
class SpecialistsController extends AdminController
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new SpecialistSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'auth' => $this->authManager(),
        ]);
    }

    /**
     * @param int $id_user
     * @return string|\yii\web\Response
     */
    public function actionCreate($id_user)
    {
        if (!$user = UserModel::findOne($id_user)) {
            throw new NotFoundHttpException();
        }

        $model = new SpecialistForm(compact('user'));

        if ($model->createSpecialist()) {
            return $this->redirect(['/admin/users/index']);
        }

        return $this->render('create', [
            'model' => $model,
            'organizations' => $this->organizationsOptions(),
            'roleOptions' => $this->roleOptions(),
        ]);
    }

    /**
     * @param int $id
     * @return string|\yii\web\Response
     */
    public function actionEdit($id)
    {
        if (!$specialist = Specialists::findOne($id)) {
            throw new NotFoundHttpException();
        }

        if (!$user = UserModel::findOne($specialist->id_user)) {
            throw new NotFoundHttpException();
        }

        $auth = $this->authManager();
        $userRoles = $auth->getRolesByUser($user->id, $specialist->id);
        $roles = empty($userRoles) ? [] : ArrayHelper::getColumn($userRoles, 'name');

        $model = new SpecialistForm(compact('user', 'roles'));

        if ($model->updateSpecialist($specialist)) {
            $referrer = \Yii::$app->request->getReferrer();

            return $this->redirect((strpos($referrer, '/specialists')) === false ? ['/admin/users/index'] : ['/admin/specialists/index']);
        }

        return $this->render('edit', [
            'model' => $model,
            'organizations' => $this->organizationsOptions(),
            'roleOptions' => $this->roleOptions(),
        ]);
    }
    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionProfile($id)
    {
        if (!$model = Specialists::find()->where(['id' => $id])->one()) {
            throw new NotFoundHttpException('Специалист не найден');
        }

        return $this->render('profile', [
            'model' => $model,
            'auth' => $this->authManager(),
        ]);
    }

    /**
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionRemove($id)
    {
        /* @var $specialist Specialists */
        $specialist = Specialists::findOne(['id' => $id]);
        if (!empty($specialist->visits)) {
            \Yii::$app->session->addFlash('danger', 'Удаление невозможно, так как у этого специалиста существуют визиты');

            return $this->redirect(['index']);
        }
        try {
            $specialist->delete();
            \Yii::$app->session->addFlash('info', 'Запись успешно удалена');
        } catch (\Exception $e) {
            \Yii::$app->session->addFlash('danger', 'Не удалось удалить запись, обратитесь к администратору');
        }

        return $this->redirect(['index']);
    }

    /**
     * @return array
     */
    private function organizationsOptions()
    {
        $organizations = Organizations::find()
            ->orderBy(['id' => SORT_ASC])
            ->all();
        $options = ArrayHelper::map($organizations, 'id', 'short_name');

        return $options;
    }

    /**
     * @return array
     */
    private function roleOptions()
    {
        $options = [];

        foreach (Role::gosOrgRoles() as $roleName) {
            $options[$roleName] = Role::humanName($roleName);
        }

        return $options;
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
