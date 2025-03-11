<?php


namespace app\modules\v2\modules\pets\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\pets\models\PassportModel;
use yii\web\BadRequestHttpException;

/**
 * Class ServicesController
 * @package app\modules\v2\modules\pets\controllers
 *
 */
class PassportController extends BaseController
{

    /**
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     * @throws BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \Throwable
     */
    public function actionPrint(int $id_pet = 0)
    {
        $model = new PassportModel();

        $this->checkAccess($this->action->getUniqueId(), $model, $this->actionParams);

        return [
            'result' => $model->createPdf($id_pet)
        ];
    }
}
