<?php


namespace app\modules\v2\modules\gosvetnadzor\controllers;


use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\gosvetnadzor\models\ViolationHistoryModel;

class ViolationHistoryController extends BaseController
{

    /**
     * Возвращает историю работы над нарушением
     *
     * @param $id_violation
     * @return array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGet($id_violation)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new ViolationHistoryModel())->getHistory($id_violation)
        ];
    }

}
