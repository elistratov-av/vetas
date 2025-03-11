<?php

namespace app\modules\v2\modules\administration\controllers;

use app\modules\v2\modules\administration\models\RolesModel;
use app\modules\v2\modules\BaseController;
use yii\db\Exception;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;


class RoleController extends BaseController
{
    /**
     * @param array $filter
     * @param int $limit
     * @param int $page
     * @return array
     * @throws Exception
     * @throws ForbiddenHttpException
     */
    public function actionList(array $filter = [], int $limit = 10000, int $page = 1): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new RolesModel();

        return [
            'result' => $model->all($filter, $limit, $page),
        ];
    }

    /**
     * @return array
     * @throws Exception
     * @throws ForbiddenHttpException
     */
    public function actionElements(): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new RolesModel();

        return [
            'result' => $model->elements(),
        ];
    }

    /**
     * @param array $data
     * @return array
     * @throws \yii\base\Exception
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionAddElements(array $data): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new RolesModel();

        return [
            'result' => $model->addElements($data),
        ];
    }

    /**
     * @param array $data
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionDeleteElements(array $data): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new RolesModel();

        return [
            'result' => $model->deleteElements($data),
        ];
    }
}
