<?php

namespace app\modules\v2\modules\tmc\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\tmc\models\DosagesModel;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

class DosagesController extends BaseController
{
    /**
     * @param int $id_tmc
     * @param array $filter
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionList(int $id_tmc, array $filter = [])
    {
        $model = new DosagesModel();

        $this->checkAccess($this->action->getUniqueId(), $model, $this->actionParams);

        return [
            'result' => $model->list($id_tmc, $filter),
        ];
    }

    /**
     * @param int|null $id_tmc
     * @param int|null $id_disease
     * @param int|null $id_species
     * @param string|null $birthday
     * @param float|null $weight
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionCalculate(int $id_tmc = null,
        int $id_disease = null,
        int $id_species = null,
        string $birthday = null,
        float $weight = null)
    {
        $model = new DosagesModel();

        $this->checkAccess($this->action->getUniqueId(), $model, $this->actionParams);

        return [
            'result' => $model->calculate($id_tmc, $id_disease, $id_species, $birthday, $weight),
        ];
    }

    /**
     * @param null $name
     * @param null $id_tmc
     * @param null $type_tmc
     * @param null $id_diseases
     * @param null $id_species
     * @param null $age_from
     * @param null $age_to
     * @param null $weight_from
     * @param null $weight_to
     * @param null $dosage
     * @param null $id_dosage_measure
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionCreate(
        $name = null,
        $id_tmc = null,
        $type_tmc = null,
        $id_diseases = null,
        $id_species = null,
        $age_from = null,
        $age_to = null,
        $weight_from = null,
        $weight_to = null,
        $pet_size = null,
        $dosage = null,
        $id_dosage_measure = null
    )
    {
        $model = new DosagesModel();

        $this->checkAccess($this->action->getUniqueId(), $model, $this->actionParams);

        $ids = $model->create(
            $name,
            $id_tmc,
            $type_tmc,
            $id_diseases,
            $id_species,
            $age_from, $age_to,
            $weight_from, $weight_to,
            $pet_size,
            $dosage,
            $id_dosage_measure
        );

        return [
            'result' => true,
            'ids'    => $ids
        ];
    }

    /**
     * @param int $id
     * @param int|null $id_tmc
     * @param string|null $type_tmc
     * @param null $id_disease
     * @param null $id_species
     * @param int|null $age_from
     * @param int|null $age_to
     * @param int|null $weight_from
     * @param int|null $weight_to
     * @param null $dosage
     * @param int|null $id_dosage_measure
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionEdit(
        $name = null,
        int $id = null,
        int $id_tmc = null,
        $type_tmc = null,
        $id_disease = null,
        $id_species = null,
        int $age_from = null,
        int $age_to = null,
        int $weight_from = null,
        int $weight_to = null,
        $pet_size = null,
        $dosage = null,
        int $id_dosage_measure = null)
    {
        $model = new DosagesModel();
        $this->checkAccess($this->action->getUniqueId(), $model, $this->actionParams);

        $model->edit(
            $id,
            $name,
            $type_tmc,
            $id_tmc,
            $id_disease,
            $id_species,
            $age_from,
            $age_to,
            $weight_from,
            $weight_to,
            $pet_size,
            $dosage,
            $id_dosage_measure
        );

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
     */
    public function actionDelete($id)
    {
        $model = new DosagesModel();
        $this->checkAccess($this->action->getUniqueId(), $model, $this->actionParams);
        $model->delete($id);

        return [
            'result' => true
        ];
    }

    /**
     * @param $id
     * @param int
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws ForbiddenHttpException
     */
    public function actionGet(int $id)
    {
        $model = new DosagesModel();
        $this->checkAccess($this->action->getUniqueId(), $model, $this->actionParams);

        return [
            'result' => $model->get($id)
        ];
    }

    /**
     * @param $id
     * @return bool[]
     * @throws ForbiddenHttpException
     */
    public function actionSetDefault($id)
    {
        $model = new DosagesModel();
        $this->checkAccess($this->action->getUniqueId(), $model, $this->actionParams);
        $model->setDefault($id);

        return [
            'result' => true
        ];
    }
}
