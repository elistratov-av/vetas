<?php

namespace app\modules\adminfstek\controllers;

use app\common\components\rbac\Role;
use app\common\models\UserModel;
use app\models\db\admin\AdminUser;
use app\models\db\Organizations;
use app\models\db\Specialists;
use app\modules\adminfstek\models\forms\SpecialistForm;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Class SpecialistsController
 * @package app\modules\adminfstek\controllers
 */
class SpecialistsController extends AdminController
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
                        'roles' => [AdminUser::ROLE_ADMIN, AdminUser::ROLE_ACCOUNTS_MANAGER],
                    ],
                ],
            ],
        ];
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
            return $this->redirect(['users/index']);
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

        $auth = $this->frontendAuthManager();
        $userRoles = $auth->getRolesByUser($user->id, $specialist->id);
        $roles = empty($userRoles) ? [] : ArrayHelper::getColumn($userRoles, 'name');

        $model = new SpecialistForm(compact('user', 'roles'));

        if ($model->updateSpecialist($specialist)) {
            return $this->redirect(['users/index']);
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
        $organizations = Organizations::find()
            ->orderBy(['id' => SORT_ASC])
            ->all();

        return ArrayHelper::map($organizations, 'id', 'short_name');
    }

    /**
     * @return array
     */
    private function roleOptions()
    {
        $auth = $this->frontendAuthManager();
        $options = [];
        foreach (Role::gosOrgRoles() as $roleName) {
            $options[$roleName] = Role::humanName($roleName, $auth);
        }

        return $options;
    }

    /**
     * @return \app\common\components\rbac\DbManager
     * @throws \yii\base\InvalidConfigException
     */
    protected function frontendAuthManager()
    {
        return \Yii::$app->get('frontendAuthManager');
    }
}
