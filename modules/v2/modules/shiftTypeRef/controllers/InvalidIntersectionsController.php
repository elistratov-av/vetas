<?php

namespace app\modules\v2\modules\shiftTypeRef\controllers;

use app\models\db\ShiftTypeInvalidIntersections;
use app\modules\v2\modules\BaseController;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

/**
 * Class InvalidIntersectionsController
 * @package app\modules\v2\modules\timesheet\controllers
 */
class InvalidIntersectionsController extends BaseController
{
    /**
     * @param array $filter
     * @param int $limit
     * @param int $page
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionAll(array $filter = [], int $limit = 10, int $page = 1): array
    {
        \Yii::info("access id: {$this->action->getUniqueId()}", __METHOD__);

        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new ShiftTypeInvalidIntersections();

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

        $model = new ShiftTypeInvalidIntersections();

        return [
            'result' => $model->get($id)
        ];
    }

    /**
     * @param int $id
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionGetByIdType(int $id): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new ShiftTypeInvalidIntersections();

        return $model->getIdByIdType($id);
    }

    /**
     * @param int $id_type
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionCreate(int $id_type): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = (new ShiftTypeInvalidIntersections())
            ->create($id_type);

        return [
            'result' => true,
            'id' => $model->id,
        ];
    }
}
