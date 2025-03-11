<?php

namespace app\modules\v2\modules\visit\models;

/**
 * Class ServicesModel
 * @package app\modules\v2\modules\visit\models
 */
class ServicesModel
{
    /**
     * работа с записью услуг, перечень данных, доступных при создании новой услуги
     * https://jira.altarix.ru/browse/VETAIS-827
     *
     * @param int $idOrganization
     * @return ServicesListModel
     */
    public function newList($idOrganization): ServicesListModel
    {
        $serviceListModel = new ServicesListModel($idOrganization);
        $serviceListModel
            ->addServiceTypes()
            ->addServiceMeasures()
            ->addServiceParams();

        return $serviceListModel;
    }

    /**
     * работа с записью услуг, перечень данных, доступных при редактировании услуги
     * https://jira.altarix.ru/browse/VETAIS-827
     *
     * @param int         $idOrganization
     * @param int         $idVisit
     * @param array|int   $idPet
     * @param string|null $code
     * @param string|null $name
     * @return ServicesListModel
     */
    public function editList(
        int $idOrganization,
        int $idVisit,
        $idPet,
        string $code = null,
        string $name = null
    ): ServicesListModel {
        $serviceListModel = new ServicesListModel($idOrganization, $idVisit, $idPet, $code, $name);
        $serviceListModel
            ->addServiceTypes()
            ->addServiceMeasures()
            ->addServiceParams()
            ->addVisitsGovServices($idOrganization, $idPet)
            ->addVisitServiceParamValues();
        // Альтернативный способ отправки данных о визитах, сохраненнных по входящему запросу мосру
        // foreach ($serviceListModel->services as &$item) {
        //     $rows = (new \yii\db\Query())
        //         ->select(['type'])
        //         ->from('gov_services')
        //         ->where([
        //             'id' => $item['id'],
        //         ])
        //         ->all();
        //     if (count($rows) > 0) {
        //         $item["type_service_is_mosru"] = $rows[0]['type'];
        //     } else {
        //         $item["type_service_is_mosru"] = null;
        //     }
        //     ;
        // }

        return $serviceListModel;
    }
}