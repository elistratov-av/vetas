<?php

namespace app\commands;

use yii\console\Controller;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Миграция для "старых" отчетов
 * Class MigrateVsvController
 *
 * @package app\commands
 * @author Aleksandr Roik
 */
class MigrateVsvController extends Controller
{
    /**
     * Фильтр. ID приема, по которому надо обработать данных
     *
     * @var int
     */
    public $visitId;

    /**
     * Фильтр. Дата, ОТ которой (включительно) надо обработать ВСЕ приемы
     *
     * @var string
     */
    public $visitStartAt;

    /**
     * Фильтр. Дата, ДО которой (включительно) надо обработать ВСЕ приемы
     *
     * @var string
     */
    public $visitEndAt;

    /**
     * @return string[]
     */
    public function options($actionID)
    {
        return [
            'visitId',
            'visitStartAt',
            'visitEndAt',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actionSafeUp()
    {
        $dateStartLog = date('Y-m-d H:i:s');
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->updateData();
            $transaction->commit();
            $dateEndLog = date('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            $transaction->rollBack();
            $dateEndLog = date('Y-m-d H:i:s');
            throw $e;
        } finally {
            echo "date start - $dateStartLog" . PHP_EOL;
            echo "date end - $dateEndLog" . PHP_EOL;
        }

        $transaction->rollBack();
    }

    /**
     * {@inheritdoc}
     */
    public function actionSafeDown()
    {
        $transaction = Yii::$app->db->beginTransaction();
        $filter = $this->getOptionFilter();
        if ($filter) {
            $filter = ['id_visit in (SELECT id FROM public.visits WHERE ' . implode(' AND ', $filter) . ')'];
        }

        try {
            Yii::$app->db->createCommand()->update('public.visit_param_values', [
                'id_visitservice'      => null,
                'id_visit_service_tmc' => null,
                'id_pet'               => null,
                'migrate_flag'         => null,
            ],
                implode(' AND ', array_merge($filter, ['migrate_flag = 1']))
            )->execute();
            Yii::$app->db->createCommand()->update('public.visit_service_param_values', [
                'id_visit'             => null,
                'id_visit_service_tmc' => null,
                'id_pet'               => null,
                'migrate_flag'         => null,
            ],
                implode(' AND ', array_merge($filter, ['migrate_flag = 1 OR migrate_flag = 3']))
            )->execute();

            Yii::$app->db->createCommand()->delete(
                'public.visit_param_values',
                implode(' AND ', array_merge($filter, ['migrate_flag = 2']))
            )->execute();

            Yii::$app->db->createCommand()->delete(
                'public.visit_service_param_values',
                implode(' AND ', array_merge($filter, ['migrate_flag = 2']))
            )->execute();

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Обрабатывает и обновляет даннные для отчетов
     *
     * @throws \yii\db\Exception
     */
    private function updateData()
    {
        $visits = $this->findVisitIds();
        if (!$visits) {
            return;
        }

        $countVisits = count($visits);
        $l = 0;

        echo 'finded ' . $countVisits . ' visits' . PHP_EOL;

        // Идем по списку приемов и обрабатываем параметры для отчетов
        foreach ($visits as $visitId) {
            $l++;
            echo 'execute visit #' . $visitId . '. [' . $l . ' of ' . $countVisits . ']' . PHP_EOL;

            $visitParamValues = $this->findVisitParamValues($visitId); //Параметры отчета (общие)
            $visitGovServices = $this->findVisitGovService($visitId);  //Список услуг

            if (!$visitParamValues) {
                echo '  visit report params not found. Continue.' . PHP_EOL;
                continue;
            }

            //1.1. Если нет услуг у приема - клонируем параметры отчета для каждого животного
            if (!$visitGovServices) {
                $pets = $this->findPetsByVisit($visitId);
                if (!$pets) {
                    echo '  [1.1] $pets is emply' . PHP_EOL;
                    continue;
                }
                $setUpdateVp = true; // в первом только обновляем значения, дальше клонируем
                foreach ($pets as $petId) {
                    echo '  [1.1] insert or update' . PHP_EOL;
                    $this->insertOrUpdateVisitParams($setUpdateVp, $petId, $visitParamValues);
                    $setUpdateVp = false;
                }

                continue;
            }

            //2. Есть услуги - идем по ним
            $setUpdateVp = true; // в первом только обновляем значения, дальше клонируем
            foreach ($visitGovServices as $visitGovService) {
                echo '  [2] execute visit_service #' . $visitGovService['id'] . PHP_EOL;

                $visitServiceParamValues = $this->findVisitServiceParamValues($visitGovService['id']);

                //2.1
                if (!$visitServiceParamValues) {
                    // Если нет параметров отчета для услуги:
                    // если есть - просто копируем/обновляем данные значения об отчете

                    //Проверяем, есть ли вообще отчет у услуги
                    if (!$this->hasReportWithOutParams($visitGovService['id_service'])) {
                        echo '  [2.1] report not exists. Continue.' . PHP_EOL;
                        continue;
                    }

                    // Берем всех животных услуги
                    if ($visitGovService['id_pet']) {
                        $pets = (array)$visitGovService['id_pet'];
                    } else {
                        $pets = $this->findPetsByVisit($visitId);
                    }
                    if (!$pets) {
                        echo '  [2.1] $pets is emply' . PHP_EOL;
                        continue;
                    }

                    foreach ($pets as $petId) {
                        echo '  [2.1] insert or update' . PHP_EOL;
                        $this->insertOrUpdateVisitParams($setUpdateVp, $petId, $visitParamValues, $visitGovService['id']);
                        $setUpdateVp = false;
                    }

                    continue;
                }

                //или 2.2
                //Если есть ТМЦ у услуги

                $visitServiceTmcs = $this->findVisitServiceTmc($visitGovService['id']);
                $serviceIsVaccinationType = $this->serviceIsVaccinationType($visitGovService['id_service']);

                $setUpdateVsp = true; // в первом только обновляем значения, дальше клонируем
                if ($visitServiceTmcs && $serviceIsVaccinationType) {
                    foreach ($visitServiceTmcs as $visitServiceTmcId) {
                        // Берем всех животных ТМЦ услуги
                        $pets = $this->findPetsByVisitServiceTmc($visitServiceTmcId);
                        if (!$pets) {
                            echo '  [2.2] $pets is emply' . PHP_EOL;
                            continue;
                        }

                        foreach ($pets as $petId) {
                            echo '  [2.2] insert or update' . PHP_EOL;
                            $this->insertOrUpdateVisitParams($setUpdateVp, $petId, $visitParamValues, $visitGovService['id'], $visitServiceTmcId);
                            $this->insertOrUpdateVisitServiceParams($setUpdateVsp, $visitId, $petId, $visitServiceParamValues, $visitGovService['id'], $visitServiceTmcId);
                            $setUpdateVp = false;
                            $setUpdateVsp = false;
                        }
                    }

                    continue;
                }

                //или 2.3
                //Если НЕТ ТМЦ у услуги

                // Берем всех животных услуги
                if ($visitGovService['id_pet']) {
                    $pets = (array)$visitGovService['id_pet'];
                } else {
                    $pets = $this->findPetsByVisit($visitId);
                }
                if (!$pets) {
                    echo '  [2.3] $pets is emply' . PHP_EOL;
                    continue;
                }

                foreach ($pets as $petId) {
                    echo '  [2.3] insert or update' . PHP_EOL;
                    $this->insertOrUpdateVisitParams($setUpdateVp, $petId, $visitParamValues, $visitGovService['id']);
                    $this->insertOrUpdateVisitServiceParams($setUpdateVsp, $visitId, $petId, $visitServiceParamValues, $visitGovService['id']);
                    $setUpdateVp = false;
                    $setUpdateVsp = false;
                }
            }
        }
    }

    /**
     * @return array
     */
    private function getOptionFilter(): array
    {
        $filter = [];
        if ($this->visitId) {
            $filter[] = 'id = ' . $this->visitId;
        }
        if ($this->visitStartAt) {
            $filter[] = "created_at >= '" . (new \DateTime($this->visitStartAt))->format('Y-m-d H:i:s') . "'";
        }
        if ($this->visitEndAt) {
            $filter[] = "created_at <= '" . (new \DateTime($this->visitEndAt))->format('Y-m-d H:i:s') . "'";
        }

        return $filter;
    }

    /**
     * @param $setUpdate
     * @param $petId
     * @param $visitParamValues
     * @param null $visitServiceId
     * @param null $visitServiceTmcId
     */
    private function insertOrUpdateVisitParams($setUpdate, $petId, $visitParamValues, $visitServiceId = null, $visitServiceTmcId = null)
    {
        if (!$visitParamValues) {
            return;
        }

        $attributes = [
            'id_visitservice'      => $visitServiceId,
            'id_visit_service_tmc' => $visitServiceTmcId,
            'id_pet'               => $petId,
        ];

        if ($setUpdate) {
            //update
            $attributes['migrate_flag'] = 1;
            $ids = array_map(function ($visitParamValue) {
                return $visitParamValue['id'];
            }, $visitParamValues);

            Yii::$app->db->createCommand()->update(
                'visit_param_values',
                $attributes,
                "id in (" . implode(',', $ids) . ")"
            )->execute();
        } else {
            //insert
            $attributes['migrate_flag'] = 2;
            foreach ($visitParamValues as $visitParamValue) {
                $attributesToInsert = array_merge($visitParamValue, $attributes);
                unset($attributesToInsert['id']);

                Yii::$app->db->createCommand()->insert(
                    'visit_param_values',
                    $attributesToInsert
                )->execute();
            }
        }
    }

    /**
     * @param $setUpdate
     * @param $visitId
     * @param $petId
     * @param $visitParamValues
     * @param null $visitServiceId
     * @param null $visitServiceTmcId
     */
    private function insertOrUpdateVisitServiceParams($setUpdate, $visitId, $petId, $visitParamValues, $visitServiceId = null, $visitServiceTmcId = null)
    {
        if (!$visitParamValues) {
            return;
        }

        $attributes = [
            'id_visit'             => $visitId,
            'id_visitservice'      => $visitServiceId,
            'id_visit_service_tmc' => $visitServiceTmcId,
            'id_pet'               => $petId,
        ];

        if ($setUpdate) {
            //update
            $attributes['migrate_flag'] = 1;

            $ids = array_map(function ($visitParamValue) {
                return $visitParamValue['id'];
            }, $visitParamValues);

            Yii::$app->db->createCommand()->update(
                'visit_service_param_values',
                $attributes,
                "id in (" . implode(',', $ids) . ")"
            )->execute();
        } else {
            //insert
            $attributes['migrate_flag'] = 2;
            foreach ($visitParamValues as $visitParamValue) {
                $attributesToInsert = array_merge($visitParamValue, $attributes);
                unset($attributesToInsert['id']);

                Yii::$app->db->createCommand()->insert(
                    'visit_service_param_values',
                    $attributesToInsert
                )->execute();
            }
        }
    }

    /**
     * @var array
     */
    private $cacheReportsWithOutParams = [];

    /**
     * @param $govServicesId
     * @return bool
     * @throws \yii\db\Exception
     */
    private function hasReportWithOutParams($govServicesId): bool
    {
        if (array_key_exists($govServicesId, $this->cacheReportsWithOutParams)) {
            return $this->cacheReportsWithOutParams[$govServicesId];
        }

        $this->cacheReportsWithOutParams[$govServicesId] = (bool)Yii::$app->db->createCommand("
            SELECT CASE
                       WHEN count_reports > 0 OR count_params > 0 THEN 1
                       ELSE 0
                       END
                       as has_report
            FROM (
                     SELECT id,
                            (
                                SELECT count(reports.id)
                                FROM gov_services_reports
                                         LEFT JOIN reports ON reports.id = gov_services_reports.id_report
                                where id_service = gov_services.id
                                  AND reports.report_type = 'R'
                            ) as count_reports,
                            (
                                SELECT count(gov_services_params.id)
                                FROM gov_services_params
                                where id_service = gov_services.id
                                  AND gov_services_params.flag_out = true
                            ) as count_params
                     from gov_services
                     order by gov_services.id
                 ) as data
            where id = $govServicesId;
        ")->queryScalar();

        return $this->cacheReportsWithOutParams[$govServicesId];
    }

    /**
     * @return array
     * @throws \yii\db\Exception
     */
    private function findVisitIds(): array
    {
        $filter = $this->getOptionFilter();
        $filter[] = 'exists(
                    select 1
                    from visit_param_values
                    where
                     id_visit = visits.id and
                     id_visitservice is null and
                     (migrate_flag is null or migrate_flag <= 0)
                    )';

        if ($filter) {
            $filter = 'WHERE ' . implode(' AND ', $filter);
        } else {
            $filter = '';
        }

        return Yii::$app->db->createCommand("
            SELECT id FROM public.visits
            $filter
            ORDER BY id
        ")->queryColumn();
    }

    /**
     * @param $visitId
     * @return array
     * @throws \yii\db\Exception
     */
    private function findVisitGovService($visitId): array
    {
        return Yii::$app->db->createCommand("
            SELECT id, id_visit, id_pet, id_service
            FROM public.visits_gov_services
            WHERE id_visit = $visitId
        ")->queryAll();
    }

    /**
     * @param $visitServiceId
     * @return array
     * @throws \yii\db\Exception
     */
    private function findVisitServiceTmc($visitServiceId): array
    {
        return Yii::$app->db->createCommand("
            SELECT id
            FROM public.visit_service_tmc
            WHERE id_visits_gov_service = $visitServiceId AND type_tmc = 'vaccine'
        ")->queryColumn();
    }

    /**
     * @param $visitId
     * @return array
     * @throws \yii\db\Exception
     */
    private function findVisitParamValues($visitId): array
    {
        return Yii::$app->db->createCommand("
            SELECT *
            FROM public.visit_param_values
            WHERE id_visit = $visitId
        ")->queryAll();
    }

    /**
     * @param $visitServiceId
     * @return array
     * @throws \yii\db\Exception
     */
    private function findVisitServiceParamValues($visitServiceId): array
    {
        return Yii::$app->db->createCommand("
            SELECT *
            FROM public.visit_service_param_values
            WHERE id_visitservice = $visitServiceId
        ")->queryAll();
    }

    /**
     * @param $visitId
     * @return array
     * @throws \yii\db\Exception
     */
    private function findPetsByVisit($visitId): array
    {
        return Yii::$app->db->createCommand("
                SELECT id_pet
                FROM public.visit_pets
                WHERE id_visit = $visitId
            ")->queryColumn();
    }

    /**
     * Поиск животных по данные вакцинации
     *
     * @param $visitServiceTmcId
     * @return array
     * @throws \yii\db\Exception
     */
    private function findPetsByVisitServiceTmc($visitServiceTmcId): array
    {
        return Yii::$app->db->createCommand("
                SELECT id_pet
                FROM (
                         SELECT id_pet
                         FROM pet_other_vaccinations
                         WHERE id_visit_service_tmc = $visitServiceTmcId
                         union all
                         SELECT id_pet
                         FROM pet_rabies_vaccination
                         WHERE id_visit_service_tmc = $visitServiceTmcId
                     ) as data
                GROUP BY id_pet
            ")->queryColumn();
    }

    /**
     * !!! пока не используется
     * Проверка, есть ли запись в  visit_service_tmc_pet.
     * В ней должны содержаться записи только для внебалансовых ТМЦ
     *
     * @param $visitServiceTmcId
     * @return bool
     * @throws \yii\db\Exception
     */
    private function hasVisitServiceTmcPets($visitServiceTmcId): bool
    {
        return (bool)Yii::$app->db->createCommand("
             SELECT id_pet
             FROM visit_service_tmc_pet
             WHERE id_visit_service_tmc = $visitServiceTmcId
            ")->queryColumn();
    }

    /**
     * @param $tableName
     * @param $paramId
     * @param $offset
     * @param $count
     * @return array
     * @throws \yii\db\Exception
     */
    public function findVisitParamsValues($tableName, $paramId, $offset, $count, $migrateFlag = 'null'): array
    {
        return Yii::$app->db->createCommand("
                SELECT  num_value,
                        char_value,
                        date_value,
                        dict_value,
                        id_visit,
                        id_param,
                        created_by,
                        updated_by,
                        created_at,
                        updated_at,
                        id_visitservice,
                        id_visit_service_tmc,
                        id_pet,
                        $migrateFlag as migrate_flag
                 FROM $tableName
                 WHERE id_param = $paramId
                 ORDER BY id
                 LIMIT $count
                 OFFSET $offset
            ")->queryAll();
    }

    /** Список сервисов
     */
    private $cacheServices = [];

    /**
     * Проверяет и возвращает статус, что услуга по вакцинаии (id_service_type == 2)
     *
     * @param $serviceId
     * @return bool
     */
    public function serviceIsVaccinationType($serviceId): bool
    {
        if (!$this->cacheServices) {
            $dataList = Yii::$app->db->createCommand("
                SELECT  id,
                        id_service_type
                 FROM gov_services
                 ORDER BY id
            ")->queryAll();

            foreach ($dataList as $data) {
                $this->cacheServices[$data['id']] = $data;
            }
        }

        return array_key_exists($serviceId, $this->cacheServices) && $this->cacheServices[$serviceId]['id_service_type'] == 2;
    }
}

