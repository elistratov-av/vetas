<?php

namespace app\modules\v2\modules\emergency\controllers;

use app\models\db\OrganizationsEmergency;
use app\modules\v2\modules\emergency\models\EmergencyModel;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\emergency\skeletons\emergency\EmergencyLists;

class EmergencyController extends BaseController
{
    /**
     * @param int $id_organization
     * @param string $date_from
     * @param string $date_to
     * @return array
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSave(int $id_organization, string $date_from, string $date_to): array
    {
        /** @var OrganizationsEmergency $emergency */
        $emergency = (new EmergencyModel())->save($id_organization, $date_from, $date_to);

        return [
            'result' => true,
            'id' => $emergency->id
        ];
    }

    /**
     * @param int $id_organization
     * @return array
     */
    public function actionStatus(int $id_organization): array
    {
        return [
            'result' => (new EmergencyModel())->getOneOrganizationsEmergencyForPeriod($id_organization)
        ];
    }

    /**
     * @param int $id
     * @param int $page
     * @param int $limit
     * @return EmergencyLists
     */
    public function actionVisitsList(int $id, int $page = 1, int $limit = 10): EmergencyLists
    {
        return (new EmergencyModel())->visitsList($id, $page, $limit);
    }

    /**
     * для визитов, попавших в промежуток действия экстренной ситуации, проставляем статус "Оповещен"
     * https://jira.altarix.ru/browse/VETAIS-1097
     *
     * @param int $id_visit
     * @return array
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionVisitNotify(int $id_visit): array
    {
        return [
            'result' => (new EmergencyModel())->notify($id_visit),
        ];
    }
}
