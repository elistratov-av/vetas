<?php

namespace app\modules\adminv\controllers;

use app\common\components\rbac\Role;
use app\common\models\UserModel;
use app\models\db\Organizations;
use app\models\db\Specialists;
use app\modules\adminv\models\forms\SpecialistForm;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Class SpecialistsController
 * @package app\modules\adminv\controllers
 */
class SpecialistsController extends AdminController
{
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
            return $this->redirect(['/adminv/users/index']);
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

        if (!\Yii::$app->user->can('admin.users.manage.W', ['model' => $specialist])) {
            \Yii::$app->session->setFlash('error', 'У вас недостаточно прав для редактирования специалиста в этой организации');
            return $this->redirect(['/adminv/users/index']);
        }

        $auth = $this->authManager();
        $userRoles = $auth->getRolesByUser($user->id, $specialist->id);
        $roles = empty($userRoles) ? [] : ArrayHelper::getColumn($userRoles, 'name');

        $model = new SpecialistForm(compact('user', 'roles'));

        if ($model->updateSpecialist($specialist)) {
            return $this->redirect(['/adminv/users/index']);
        }

        return $this->render('edit', [
            'model' => $model,
            'organizations' => $this->organizationsOptions(),
            'roleOptions' => $this->roleOptions(),
        ]);
    }

    /**
     * @return array
     */
    private function organizationsOptions()
    {
        $options = [];

        if (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS)) {
            $organizations = Organizations::find()
                ->orderBy(['id' => SORT_ASC])
                ->all();
            $options = ArrayHelper::map($organizations, 'id', 'short_name');
        } elseif (\Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS)) {
            /* @var $currentUser UserModel */
            $currentUser = \Yii::$app->user->getIdentity();
            $organizations = $currentUser->specialist->getAllOrganizations(!(\Yii::$app->user->can(Role::ROLE_SHELTER_MANAGEMENT) || \Yii::$app->user->can(Role::ROLE_SHELTER_SPECIALIST)));
            $options = ArrayHelper::map($organizations, 'id', 'short_name');
        }

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
}
