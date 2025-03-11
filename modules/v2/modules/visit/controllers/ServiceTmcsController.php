<?php


namespace app\modules\v2\modules\visit\controllers;


use app\models\db\Visits;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\visit\models\ServiceTmcsModel;
use app\modules\v2\modules\visit\models\ServiceTmcsModelSave;

class ServiceTmcsController extends BaseController
{
    /**
     * Сохраняем тмц (кроме оборудования)
     *
     * @param int $id_visit
     * @param int $id_visit_service
     * @param array $balance_tmcs
     * @param array $other_tmcs
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionSave(int $id_visit, int $id_visit_service, $balance_tmcs, $other_tmcs)
    {
        $this->checkAccess($this->action->getUniqueId(), Visits::findOne(['id' => $id_visit]), $this->actionParams);

        (new ServiceTmcsModelSave())->save(
            $id_visit,
            $id_visit_service,
            $balance_tmcs,
            $other_tmcs
        );
        return [
            'result' => true
        ];
    }

    /**
     * @param $id_visit
     * @param $id_visit_service
     * @return array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGet($id_visit, $id_visit_service)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new ServiceTmcsModel)->getBalanceTMC($id_visit, $id_visit_service),
        ];
    }
}
