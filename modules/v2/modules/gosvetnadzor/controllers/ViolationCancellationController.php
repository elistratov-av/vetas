<?php


namespace app\modules\v2\modules\gosvetnadzor\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\gosvetnadzor\models\ViolationCancellationModel;

class ViolationCancellationController extends BaseController
{
    /**
     * Возвращает весь справочник причины отмены работы над нарушением
     *
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionAll()
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new ViolationCancellationModel())->getAllNonSystem()
        ];
    }
}
