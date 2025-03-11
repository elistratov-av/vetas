<?php


namespace app\modules\v2\modules\pethotels\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\pethotels\models\BreedModel;
use yii\web\BadRequestHttpException;

class BreedController extends BaseController
{

    /**
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionAll($id_species)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new BreedModel();
        return [
            'result' => $model->list($id_species)->all(),
        ];
    }

    /**
     * @param $id_species
     * @param $name
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionCreate(
        $id_species, $name
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new BreedModel();
        $animal = $model->create(
            $id_species, $name
        );
        return [
            'result' => true,
            'id' => $animal->id,
        ];
    }

    /**
     * @param $id
     * @param $id_species
     * @param $name
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionEdit(
        $id, $id_species = null, $name = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new BreedModel();
        $model->edit($id, $id_species, $name);
        return [
            'result' => true
        ];
    }

    /**
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGet($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new BreedModel();
        return [
            'result' => $model->get($id)
        ];
    }

    /**
     * @param $id_species
     * @param $name
     * @param $page
     * @param $limit
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionSearch(
        $id_species = null, $name = null,
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
        $model = new BreedModel();
        return [
            'result' => $model->search(
                $id_species, $name,
                $page, $limit
            ),
        ];
    }
}
