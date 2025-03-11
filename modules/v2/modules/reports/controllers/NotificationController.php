<?php

namespace app\modules\v2\modules\reports\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\reports\models\NotificationModel;
use app\modules\v2\modules\visit\controllers\VisitTrait;

class NotificationController extends BaseController
{
    use VisitTrait;

    /**
     * @param $id_visit
     * @return array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionCheck($id_visit)
    {
        $visit = $this->findVisit($id_visit);
        $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);

        return [
            'result' => (new NotificationModel())->check($id_visit)
        ];
    }

    /**
     * @param $id_visit
     * @return array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionSend($id_visit)
    {
        $visit = $this->findVisit($id_visit);
        $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);

        return [
            'result' => (new NotificationModel())->send($id_visit)
        ];
    }
}
