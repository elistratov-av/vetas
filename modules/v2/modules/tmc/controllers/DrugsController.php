<?php


namespace app\modules\v2\modules\tmc\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\tmc\models\DrugsModel;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

class DrugsController extends BaseController
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
            'result' => (new DrugsModel())->getDrug($id)
        ];
    }

    /**
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return array
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionList(int $page = 1, int $limit = 10, array $filter = [])
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
        $drugs = new DrugsModel();

        return [
            'result' => $drugs->list($page, $limit, $filter)
        ];
    }

    /**
     * Создание препарата
     *
     * @param string $name
     * @param string $registered
     * @param string $produced
     * @param string $dealer
     * @param string $form_description
     * @param string $unit
     * @param int $id_measure
     * @param string $excipients
     * @param null $basis
     * @param null $packaging
     * @param $category_ids
     * @param $diseases_ids
     * @param $species_ids
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionCreate(
        $name,
        $registered,
        $produced,
        $dealer = null,
        $form_description = null,
        $unit = null,
        $id_measure = null,
        $excipients = null,
        $basis = null,
        $packaging = null,
        $category_ids = [],
        $diseases_ids = [],
        $species_ids = []
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new DrugsModel();

        $drug = $model
            ->create(
                $name,
                $registered,
                $produced,
                $dealer,
                $form_description,
                $unit,
                $id_measure,
                $excipients,
                $basis,
                $packaging,
                $category_ids,
                $diseases_ids,
                $species_ids
            );

        return [
            'result' => true,
            'id' => $drug->id,
        ];
    }

    /**
     * Редактирование препарата
     *
     * @param string $name
     * @param string $registered
     * @param string $produced
     * @param string $dealer
     * @param string $form_description
     * @param string $unit
     * @param int $id_measure
     * @param string $excipients
     * @param string $packaging
     * @param $category_ids
     * @param $diseases_ids
     * @param $species_ids
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionEdit(
        $id,
        $name,
        $registered,
        $produced,
        $dealer = null,
        $form_description = null,
        $unit = null,
        $id_measure = null,
        $excipients = null,
        $packaging = null,
        $category_ids = [],
        $diseases_ids = [],
        $species_ids = []
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        (new DrugsModel())
            ->edit(
                $id,
                $name,
                $registered,
                $produced,
                $dealer,
                $form_description,
                $unit,
                $id_measure,
                $excipients,
                $packaging,
                $category_ids,
                $diseases_ids,
                $species_ids
            );

        return [
            'result' => true,
        ];
    }

    /**
     * Удаление препарата
     *
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        (new DrugsModel())
            ->delete($id);

        return [
            'result' => true,
        ];
    }

}