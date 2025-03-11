<?php


namespace app\modules\v2\modules\petOwners\controllers;

use app\modules\v2\modules\pets\models\PetServicesModel;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\visit\models\VisitModel;
use yii\web\BadRequestHttpException;

/**
 * Class ServicesController
 * @package app\modules\v2\modules\pets\controllers
 *
 */
class ServicesController extends BaseController
{

    /**
     * Список услуг оказанных владельцу
     *
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \Throwable
     */
    public function actionList($page = 1, $limit = 10, $id_owner = null, $filter = NULL)
    {
        $model = new VisitModel();
        $this->checkAccess($this->action->getUniqueId(), $model, $this->actionParams);
        $answer = $model->list2($page, $limit, $id_owner, $filter);

        return [
            'result' => $answer
        ];
    }
}
