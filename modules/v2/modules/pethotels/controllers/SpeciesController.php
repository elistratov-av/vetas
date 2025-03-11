<?php


namespace app\modules\v2\modules\pethotels\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\pethotels\models\SpeciesModel;
use yii\web\BadRequestHttpException;

class SpeciesController extends BaseController
{

    /**
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionAll()
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new SpeciesModel();
        return [
            'result' => $model->list()->all(),
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

        $model = new SpeciesModel();
        return [
            'result' => $model->get($id)
        ];
    }

    /**
     * @param $name
     * @param $page
     * @param $limit
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionSearch(
        $name = null,
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
        $model = new SpeciesModel();
        return [
            'result' => $model->search(
                $name, $page, $limit
            ),
        ];
    }
}
