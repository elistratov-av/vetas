<?php


namespace app\modules\v2\modules\visit\controllers;


use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\visit\models\VisitViolationReport;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class ViolationController extends BaseController
{

    use VisitTrait;

    /**
     * Добавляет сообщение о нарушении в рамках визита
     *
     * @param int   $id_visit
     * @param int   $id_type
     * @param array $comments
     * @param array $ids_pet
     * @param bool  $set_visit
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionReport(int $id_visit, int $id_type, array $comments, array $ids_pet = [], bool $set_visit = true): array
    {
        $visit = $this->findVisit($id_visit);

        $this->checkAccess($this->action->getUniqueId(), $visit, $this->actionParams);

        return [
            'result' => (new VisitViolationReport())->report($visit, $id_type, $comments, $ids_pet, $set_visit),
        ];
    }
}
