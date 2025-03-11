<?php


namespace app\modules\v2\modules\tmc\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\tmc\models\ProductionFormsModel;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

class ProductionFormsController extends BaseController
{
    /**
     * @param int $id
     * @return array
     * @throws ForbiddenHttpException|BadRequestHttpException|ForbiddenHttpException
     */
    public function actionGet(int $id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new ProductionFormsModel())->getProductionForm($id)
        ];
    }

    /**
     * Список форм выпуска для указанного тмц
     *
     * @param int $id_tmc
     * @param string $type_tmc
     * @return array
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function actionList(int $id_tmc, $type_tmc)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
        $pf = new ProductionFormsModel();

        return [
            'result' => $pf->getList($id_tmc, $type_tmc)
        ];
    }

    /**
     * Создание формы выпуска
     *
     * @param int $id_tmc
     * @param $type_tmc
     * @param $name
     * @param $volume
     * @param $is_utilize
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionCreate($id_tmc, $type_tmc, $name, $volume, $is_utilize)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
        $model = new ProductionFormsModel();
        $pf = $model->create($id_tmc, $type_tmc, $name, $volume, $is_utilize);

        return [
            'result' => true,
            'id' => $pf->id
        ];
    }

    /**
     * Редактирование формы выпуска
     *
     * @param $id
     * @param $name
     * @param $volume
     * @param $is_utilize
     * @return array
     */
    public function actionEdit($id, $name, $volume, $is_utilize)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
        (new ProductionFormsModel())->edit($id, $name, $volume, $is_utilize);

        return [
            'result' => true
        ];
    }

    /**
     * Удаление формы выпуска
     *
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

        (new ProductionFormsModel())->delete($id);

        return [
            'result' => true,
        ];
    }
}