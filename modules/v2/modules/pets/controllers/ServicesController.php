<?php


namespace app\modules\v2\modules\pets\controllers;

use app\modules\v2\modules\pets\models\PetServicesModel;
use app\modules\v2\modules\BaseController;
use yii\web\BadRequestHttpException;

/**
 * Class ServicesController
 * @package app\modules\v2\modules\pets\controllers
 *
 */
class ServicesController extends BaseController
{

    /**
     * Список услуг оказанных животному
     *
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \Throwable
     */
    public function actionList($page = 1, $limit = 10, $id_pet = null, $filter = NULL)
    {
        $model = new PetServicesModel();

        $this->checkAccess($this->action->getUniqueId(), $model, $this->actionParams);

        return [
            'result' => ($model)->list($page, $limit, $id_pet, $filter)
        ];
    }
}
