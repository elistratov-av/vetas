<?php

namespace app\modules\v2\modules\discount\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\discount\models\DiscountModel;
use yii\web\BadRequestHttpException;

/**
 * Class DiscountController
 * @package app\modules\v2\modules\discount\controllers
 */
class DiscountController extends BaseController
{
    /**
     * Возвращает список всех НЕ удаленных скидок
     *
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionAll()
    {
        $model = new DiscountModel();

        $this->checkAccess($this->action->getUniqueId(), $model, $this->actionParams);

        return [
            'result' => $model->all(),
        ];
    }

    /**
     * Создает новую скидку
     *
     * @param string $name
     * @param int    $value
     * @param int    $id_organization
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionCreate($name, $value, $id_organization = null)
    {
        $model = new DiscountModel();

        $this->checkAccess($this->action->getUniqueId(), $model, $this->actionParams);

        $discount = $model->create($name, $value, $id_organization);

        return [
            'result' => true,
            'id' => $discount->id,
        ];
    }

    /**
     * Помечает скидку как удаленную
     *
     * @param int $id
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionDelete($id)
    {
        $model = new DiscountModel();

        $this->checkAccess($this->action->getUniqueId(), $model, $this->actionParams);

        $model->delete($id);

        return [
            'result' => true,
        ];
    }

    /**
     * Редактирование скидки (кроме удаленных)
     *
     * @param int    $id
     * @param string $name
     * @param int    $value
     * @param int    $id_organization
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionEdit($id, $name = null, $value = null, $id_organization = null)
    {
        $model = new DiscountModel();

        $this->checkAccess($this->action->getUniqueId(), $model, $this->actionParams);

        $model->edit($id, $name, $value, $id_organization);

        return [
            'result' => true,
        ];
    }

    /**
     * Возвращает указанную скидку
     *
     * @param int $id
     * @param int $id_organization
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGet($id, int $id_organization = null)
    {
        $model = new DiscountModel();

        $this->checkAccess($this->action->getUniqueId(), $model, $this->actionParams);

        return [
            'result' => $model->get($id),
        ];
    }
}
