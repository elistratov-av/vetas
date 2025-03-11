<?php

namespace app\modules\adminv\controllers;

use app\common\components\rbac\Role;
use app\common\models\UserModel;
use app\models\db\Specialists;
use app\modules\adminv\models\forms\SpecialistForm;
use app\modules\adminv\models\forms\UserForm;
use app\modules\adminv\models\search\UserSearch;
use app\modules\v2\common\rbac\AccessTrait;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Class SheltersUsersController
 * @package app\modules\adminv\controllers
 */
class SheltersUsersController extends UsersController
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
                        'roles' => [Role::ROLE_SHELTER_MANAGEMENT],
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
        $specialistModel = new SpecialistForm(compact('user'));
        $specialistModel->id_organization = $this->idOrganization();

        if ($model->createUser()) {
            $specialistModel->id_user = $model->getUser()->id;
            if ($specialistModel->createSpecialist()) {
                return $this->redirect(['index']);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'specialistModel' => $specialistModel,
            'roleOptions' => $this->roleOptions(),
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

        $id_organization = $this->idOrganization();
        $columns = [
            'id_user' => $user->id,
            'id_organization' => $id_organization,
        ];
        $specialist = Specialists::findOne($columns);

        if ($specialist === null) {
            $roles = [];
        } else {
            $auth = $this->authManager();
            $userRoles = $auth->getRolesByUser($user->id, $specialist->id);
            $roles = empty($userRoles) ? [] : ArrayHelper::getColumn($userRoles, 'name');
        }

        $specialistModel = new SpecialistForm(compact('user', 'roles'));
        if ($specialist !== null) {
            $specialistModel->load($specialist->attributes, '');
        }
        $specialistModel->id_organization = $id_organization;

        if ($model->updateUser()) {
            $specResult = ($specialist === null)
                ? $specialistModel->createSpecialist()
                : $specialistModel->updateSpecialist($specialist);
            if ($specResult) {
                return $this->redirect(['index']);
            }
        }

        return $this->render('edit', [
            'model' => $model,
            'specialistModel' => $specialistModel,
            'user' => $user,
            'roleOptions' => $this->roleOptions(),
        ]);
    }

    /**
     * @return mixed
     * @throws \Throwable
     */
    private function idOrganization()
    {
        return \Yii::$app->user->getIdentity()->specialist->id_organization;
    }

    /**
     * @return array
     */
    private function roleOptions()
    {
        $options = [];

        foreach (Role::shelterRoles() as $roleName) {
            $options[$roleName] = Role::humanName($roleName);
        }

        return $options;
    }
}
