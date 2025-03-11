<?php


namespace app\modules\v2\modules\tmc\controllers;


use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\tmc\models\EquipmentsModel;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

class EquipmentsController extends BaseController
{
    /**
     * @param int $id
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionGet(int $id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new EquipmentsModel())->getEquipment($id)
        ];
    }

    /**
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return array
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function actionList(int $page = 1, int $limit = 10, array $filter = [])
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
        $equipments = new EquipmentsModel();

        return [
            'result' => $equipments->list($page, $limit, $filter)
        ];
    }

    /**
     * Создание оборудования
     *
     * @param $name
     * @param null $description
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionCreate($name, $description)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
        $model = new EquipmentsModel();
        $equipments = $model->create($name, $description);

        return [
            'result' => true,
            'id' => $equipments->id
        ];
    }

    /**
     * @param $id
     * @param $name
     * @param null $description
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionEdit($id, $name, $description)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
        (new EquipmentsModel())->edit($id, $name, $description);

        return [
            'result' => true
        ];
    }

    /**
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        (new EquipmentsModel())->delete($id);

        return [
            'result' => true,
        ];
    }

}