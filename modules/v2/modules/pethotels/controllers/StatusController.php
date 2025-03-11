<?php


namespace app\modules\v2\modules\pethotels\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\pethotels\models\StatusModel;
use yii\web\BadRequestHttpException;

class StatusController extends BaseController
{

    /**
     * Возвращает список всех статусов
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionAll()
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new StatusModel();
        return [
            'result' => $model->list()->all(),
        ];
    }

    /**
     * Создает новый статус
     *
     * @param $code
     * @param $name
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionCreate(
        $code, $name = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new StatusModel();
        $animal = $model->create(
            $code, $name
        );
        return [
            'result' => true,
            'id' => $animal->id,
        ];
    }

    /**
     * Удаляет статус
     *
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionDelete($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new StatusModel();
        $model->delete($id);
        return [
            'result' => true
        ];
    }

    /**
     * Редактирование статуса
     *
     * @param $id
     * @param $code
     * @param $name
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionEdit(
        $id, $code = null, $name = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new StatusModel();
        $model->edit($id, $code, $name);
        return [
            'result' => true
        ];
    }

    /**
     * Возвращает указанный статус
     *
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGet($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new StatusModel();
        return [
            'result' => $model->get($id)
        ];
    }

    /**
     * Поиск статуса
     *
     * @param $code
     * @param $name
     * @param $page
     * @param $limit
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionSearch(
        $code = null, $name = null,
        $page = null, $limit = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        if (is_null($page)) {
            $page = 1;
        }
        if (is_null($limit)) {
            $limit = 10;
        }
        $model = new StatusModel();
        return [
            'result' => $model->search(
                $code, $name,
                $page, $limit
            ),
        ];
    }
}
