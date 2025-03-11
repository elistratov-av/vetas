<?php

namespace app\modules\v2\modules\administration\controllers;

use app\models\db\Users;
use app\modules\v2\modules\administration\models\RolesModel;
use app\modules\v2\modules\BaseController;
use Throwable;
use yii\base\Exception;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

class UserController extends BaseController
{
    /**
     * @param array $filter
     * @param int $limit
     * @param int $page
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionList(array $filter = [], int $limit = 10, int $page = 1): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new Users();

        return [
            'result' => $model->all($filter, $limit, $page),
        ];
    }

    /**
     * @param int $id
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionGet(int $id): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new Users();

        return [
            'result' => $model->get($id),
        ];
    }

    /**
     * @param int $id
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionOrganization(int $id): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new Users();

        return [
            'result' => $model->organization($id),
        ];
    }

    /**
     * @param int $id
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionSpecialization(int $id): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new Users();

        return [
            'result' => $model->specialization($id),
        ];
    }

    /**
     * @param array $data
     * @return array
     * @throws Exception
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionCreate(array $data): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new Users();
        $data['auth_key'] = \Yii::$app->security->generateRandomString();
        $now = date('Y-m-d H:i:s');
        $data['created_at'] = $now;
        $data['updated_at'] = $now;
        $data['is_blocked'] = false;
        $data['is_temp_password'] = false;
        $data['is_deleted'] = false;
        $valid_till = date('Y-m-d H:i:s', strtotime($now . ' + 5 years'));
        $data['password_valid_till'] = $valid_till;
        $data['password_valid_till_min'] = $valid_till;

        return [
            'result' => $model->create($data),
        ];
    }

    /**
     * @param int $id
     * @param array $data
     * @return array
     * @throws Throwable
     * @throws StaleObjectException
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionUpdate(int $id, array $data): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new Users();

        if (!empty($data)) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        return [
            'result' => $model->updateById($id, $data),
        ];
    }

    /**
     * @param array $data
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionRegOrganization(array $data): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new Users();

        return [
            'result' => $model->regOrganization($data),
        ];
    }

    /**
     * @param array $data
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionExpelOrganization(array $data): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new Users();

        return [
            'result' => $model->expelOrganization($data),
        ];
    }

    /**
     * @param array $data
     * @return array
     * @throws Throwable
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionAddSpecialization(array $data): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $user = \Yii::$app->user->getIdentity();
        $model = new Users();

        return [
            'result' => $model->addSpecialization($data, $user->id),
        ];
    }

    /**
     * @param array $data
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionDeleteSpecialization(array $data): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new Users();

        return [
            'result' => $model->deleteSpecialization($data),
        ];
    }

    /**
     * @param array $data
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionAddRole(array $data): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new Users();

        return [
            'result' => $model->addRole($data),
        ];
    }

    /**
     * @param array $data
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionDeleteRole(array $data): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new Users();

        return [
            'result' => $model->deleteRole($data),
        ];
    }

    /**
     * @param int $limit
     * @param int $page
     * @return array
     * @throws ForbiddenHttpException
     * @throws \yii\db\Exception
     */
    public function actionRoleList(int $limit = 10, int $page = 1): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new RolesModel();

        return [
            'result' => $model->userRoleList($limit, $page),
        ];
    }
}
