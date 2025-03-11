<?php


namespace app\modules\v2\modules\gosvetnadzor\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\gosvetnadzor\models\ViolationTypeModel;

class ViolationTypeController extends BaseController
{
    /**
     * Возвращает весь справочник типов нарушений
     *
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionAll()
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new ViolationTypeModel())->getAll()
        ];
    }
}
