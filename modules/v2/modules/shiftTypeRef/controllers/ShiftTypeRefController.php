<?php

namespace app\modules\v2\modules\shiftTypeRef\controllers;

use app\models\db\ShiftTypeRef;
use app\modules\v2\modules\BaseController;
use Throwable;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;


/**
 * Class ShiftTypeRefController
 * @package app\modules\v2\modules\shiftTypeRef\controllers
 */
class ShiftTypeRefController extends BaseController
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

        $model = new ShiftTypeRef();

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
    public function actionGetById(int $id): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new ShiftTypeRef();
        return [
            'result' => $model->get($id)
        ];
    }

    /**
     * @param array $data
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws Throwable
     */
    public function actionCreate(array $data = []): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = (new ShiftTypeRef())
            ->create($data);

        return [
            'result' => true,
            'id' => $model['shiftTypeRef'],
            'errors' => $model['errors'],
        ];
    }

    /**
     * @param int $id
     * @return bool[]
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionDelete(int $id): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new ShiftTypeRef();
        $model->deleteById($id);

        return [
            'result' => true,
        ];
    }
}
