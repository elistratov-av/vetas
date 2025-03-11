<?php

namespace app\common\components\pdfGenerator;

use app\models\db\Dictionaries;
use app\models\db\GovServicesReports;
use app\models\db\Params;
use app\models\db\PetIdentification;
use app\models\db\Pets;
use app\models\db\Reports;
use app\models\db\ReportsParams;
use app\models\db\tmc\TmcBase;
use app\models\db\VisitParamValues;
use app\models\db\Visits;
use app\models\db\VisitServiceParamValues;
use app\models\db\VisitsGovServices;
use app\common\components\visitServiceReport\helpers\ServiceReportsDataHelper;
use app\common\components\visitServiceReport\helpers\VisitParamsDataHelper;
use app\modules\v2\modules\gosvetnadzor\cron\ViolationCheckVaccination;
use app\modules\v2\modules\visit\models\BillModel;
use app\modules\v2\modules\visit\models\BillModelForAP;
use app\modules\v2\modules\visit\models\ParamsTrait;
use Throwable;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class PdfVisitGeneratorHelper
{

    private const VSD_TYPE_TECH_NAME = 'P109_Vsdtype';
    private const VSD_NUMBER_TECH_NAME = 'P110_Vsdnumber';
    private const PARAM_ORGAN_SYSTEM_TECH_NAME = 'P0_Organsystem';
    /** список отчетов по УЗИ */
    private const ULTRASOUND_REPORT_IDS = [10, 11, 12, 13, 14];
    /** связь отчет УЗИ к системе органа */
    private const ULTRASOUND_REPORT_IDS_TO_ORGAN_SYSTEM = [
        10 => 'глаза',
        11 => 'мочевыделительная система',
        12 => 'печень, желчный пузырь, поджелудочной железы, селезенки и желудочно-кишечного тракта',
        13 => 'репродуктивная система самки',
        14 => 'репродуктивная система самца',
    ];
    private const PARAM_SERIAL_SERVICE_NUM = 'P2_SerialServiceNum';
    /** ID услуг ВСД */
    private const VSD_SERVICE_IDS = [210, 211];
    /** ID услуги ВСД для печати */
    private const VSD_SERVICE_FOR_REPORT = 211;

    /**
     * Получение привилегий владельца
     *
     * @param Visits $visit
     *
     * @return string
     */
    public static function getOwnerPrivileges(Visits $visit): string
    {
        $privileges = [];
        if ($visit->is_veteran) {
            $privileges[] = 'Ветеран ВОВ';
        }
        if ($visit->is_disabled) {
            $privileges[] = 'Инвалид I группы';
        }
        if ($visit->is_blind) {
            $privileges[] = 'Слабовидящий с животным поводырем';
        }
        if ($visit->is_orphan) {
            $privileges[] = 'Сирота/ребенок без попечителя';
        }
        if ($visit->is_large_family) {
            $privileges[] = 'Многодетная семья';
        }
        if ($visit->is_veteran_of_labour) {
            $privileges[] = 'Ветеран труда';
        }

        return $privileges ? implode(',' . PHP_EOL, $privileges) : '-';
    }

    /**
     * Получение номера телефона владельца
     *
     * @param Visits $visit
     *
     * @return string
     */
    public static function getOwnerPhone(Visits $visit): string
    {
        $actual = null;
        foreach ($visit->owner->phoneContacts as $num => $phoneContact) {
            if ($phoneContact->main_flag) {
                return $phoneContact->name;
            }
            if (is_null($actual) ||
                strtotime($visit->owner->phoneContacts[$num]->updated_at) > strtotime($phoneContact->updated_at)) {
                $actual = $num;
            }
        }

        if (!is_null($actual)) {
            return $visit->owner->phoneContacts[$actual]->name;
        }

        return '-';
    }

    /**
     * Получение email владельца
     *
     * @param Visits $visit
     *
     * @return string
     */
    public static function getOwnerEmail(Visits $visit): string
    {
        $actual = null;
        foreach ($visit->owner->emailContacts as $num => $emailContact) {
            if ($emailContact->main_flag) {
                return $emailContact->name;
            }
            if (is_null($actual) ||
                strtotime($visit->owner->emailContacts[$num]->updated_at) > strtotime($emailContact->updated_at)) {
                $actual = $num;
            }
        }

        if (!is_null($actual)) {
            return $visit->owner->emailContacts[$actual]->name;
        }

        return '-';
    }

    /**
     * Получение ВСД
     *
     * @param Visits $visit
     *
     * @return array
     */
    public static function getVsd(Visits $visit): array
    {
        $vsd = [];

        $visitServiceVsdIds =  (new Query())
            ->select('id')
            ->from(VisitsGovServices::tableName())
            ->where([
                'AND',
                ['id_visit' => $visit->id],
                ['IN', 'id_service', self::VSD_SERVICE_IDS],
            ])
            ->column();

        if (empty($visitServiceVsdIds)) {
            return $vsd;
        }

        foreach ($visitServiceVsdIds as $visitServiceVsdId) {
            $paramType = self::getVisitServiceParamValueByTechName($visitServiceVsdId, self::VSD_TYPE_TECH_NAME);
            if (empty($paramType)) {
                continue;
            }
            $typeName = self::getParamValue($paramType);
            if (!is_null($typeName)) {
                $vsd[] = [
                    'type' => $typeName,
                    'value' => self::getParamValue(
                        self::getVisitServiceParamValueByTechName($visitServiceVsdId, self::VSD_NUMBER_TECH_NAME)
                    )
                ];
            }
        }

        return $vsd;
    }

    /**
     * @param int    $visitServiceId
     * @param string $techName
     *
     * @return array
     */
    private static function getVisitServiceParamValueByTechName(int $visitServiceId, string $techName): array
    {
        $param = (new Query())
            ->select([
                'vspv.num_value',
                'vspv.char_value',
                'vspv.date_value',
                'vspv.dict_value',
                'vspv.complex_value',
                'p.datatype',
                'p.datatype_details',
            ])
            ->from('visit_service_param_values vspv')
            ->innerJoin(
                VisitsGovServices::tableName() . ' vgs',
                'vgs.id = vspv.id_visitservice'
            )
            ->innerJoin(
                Params::tableName() . ' p',
                'p.id = vspv.id_param'
            )
            ->where([
                'and',
                ['vgs.id' => $visitServiceId],
                ['p.tech_name' => $techName],
            ])
            ->orderBy('vspv.id DESC')
            ->one();
        return $param ?: [];
    }

    /**
     * Получение последней даты вакцинации животного от бешенства
     *
     * @param Pets $pet
     *
     * @return string
     */
    public static function getRabiesVaccination(Pets $pet): string
    {
        $dateLastVaccination = null;
        foreach ($pet->pet_rabies_vaccinations as $vaccination) {
            if (is_null($dateLastVaccination) || strtotime($vaccination->date) > $dateLastVaccination) {
                $dateLastVaccination = strtotime($vaccination->date);
            }
        }
        return is_null($dateLastVaccination) ? '' : date('d.m.Y', $dateLastVaccination);
    }

    /**
     * Получение последней даты вакцинации животного от лептоспироза
     *
     * @param Pets $pet
     *
     * @return string
     * @throws Exception
     */
    public static function getLeptVaccination(Pets $pet): string
    {
        $sql = <<<SQL
SELECT MAX(date) AS date FROM pet_other_vaccinations
WHERE id_vaccine IN (
    SELECT tmc.tmc.id FROM tmc.tmc
    WHERE id IN (
        SELECT tmc.tmc_to_diseases.id_tmc FROM tmc.tmc_to_diseases
        JOIN diseases ON diseases.id = tmc.tmc_to_diseases.id_disease
        WHERE diseases.name = :disease_name
    ) AND tmc.tmc.type = 'vaccine'
) and id_pet = :id_pet
group by pet_other_vaccinations.id_pet;
SQL;

        $vaccination = Yii::$app->db
            ->createCommand(
                $sql,
                [
                    ':disease_name' => ViolationCheckVaccination::QUERY_CONSTANTS_DISEASES_NAME_LEPTOSPIROSIS,
                    ':id_pet' => $pet->id,
                ])
            ->queryOne();

        return $vaccination ? date('d.m.Y', strtotime($vaccination['date'])) : '';
    }

    /**
     * Получение идентификации
     *
     * @param Pets $pet
     *
     * @return PetIdentification|null
     */
    public static function getIdentification(Pets $pet): ?PetIdentification
    {
        foreach ($pet->pet_identification as $ident) {
            if ($ident->main_flag === true) {
                return $ident;
            }
        }

        return $pet->pet_identification[0] ?? null;
    }

    /**
     * @param BillModel $visitBill
     *
     * @return string
     * @throws Exception
     */
    public static function getServices(BillModel $visitBill): string
    {
        try {
            $bill = $visitBill->getBill();
        } catch (Throwable $e) {
            throw new Exception('Ошибка формирования счета');
        }
        return implode(';<br/>', array_map(
            static function (array $service) {
                $price = $service['price_with_discount'] ?? $service['service']['price'];
                $serviceRow = $service['service']['cod'] . ' ' . $service['service']['name'] . ' ' . (float)$price . ' р.';
                if ($service['count'] > 1) {
                    $serviceRow .= ' * x' . $service['count'];
                }
                return $serviceRow;
            },
            $bill['services']
        ));
    }

    /**
     * @param BillModelForAP $visitBill
     *
     * @return string
     * @throws Exception
     */
    public static function getServicesForAP(BillModelForAP $visitBill): string
    {
        try {
            $bill = $visitBill->getBill();
        } catch (Throwable $e) {
            throw new Exception('Ошибка формирования счета');
        }
        return implode(';<br/>', array_map(
            static function (array $service) {
                $price = $service['price_with_discount'] ?? $service['service']['price'];
                $serviceRow = $service['service']['cod'] . ' ' . $service['service']['name'] . ' ' . (float)$price . ' р.';
                if ($service['count'] > 1) {
                    $serviceRow .= ' * x' . $service['count'];
                }
                return $serviceRow;
            },
            $bill['services']
        ));
    }

    /**
     * Получение общей стоимости приема
     *
     * @param BillModel $visitBill
     *
     * @return string
     * @throws Exception
     */
    public static function getPriceSum(BillModel $visitBill): string
    {
        try {
            return $visitBill->getBill()['price']['sum']['total_with_discount'] . ' р.';
        } catch (Throwable $e) {
            throw new Exception('Ошибка формирования счета');
        }
    }

    /**
     * Получение общей стоимости приема
     *
     * @param BillModelForAP $visitBill
     *
     * @return string
     * @throws Exception
     */
    public static function getPriceSumForAP(BillModelForAP $visitBill): string
    {
        try {
            return $visitBill->getBill()['price']['sum']['total_with_discount'] . ' р.';
        } catch (Throwable $e) {
            throw new Exception('Ошибка формирования счета');
        }
    }
    /**
     * Получаем параметры приема
     *
     * @param int $visitId
     *
     * @return array
     */
    public static function getVisitParamValues(int $visitId): array
    {
        $prepareVisitParams = [];
        $visitParams = ArrayHelper::index(self::getVisitParams($visitId), 'tech_name');

        foreach (VisitParamsDataHelper::findReportParamsForVisit() as $defaultField) {
            $prepareVisitParams[$defaultField['tech_name']] = isset($visitParams[$defaultField['tech_name']]) ?
                self::getParamValue($visitParams[$defaultField['tech_name']])
                : '';
        }

        return $prepareVisitParams;
    }

    /**
     * @param int $visitId
     * @param int $reportId
     *
     * @return array
     * @throws Throwable
     */
    public static function getServicesParamForReport(int $visitId, int $reportId): array
    {
        $servicesParams = self::getVisitServiceParamValues($visitId, $reportId);

        $prepareVisitParams = [];
        foreach (ArrayHelper::index($servicesParams, 'tech_name') as $techName => $visitServiceParam) {
            $prepareVisitParams[$techName] = self::getParamValue($visitServiceParam);
        }

        if (array_key_exists(self::PARAM_SERIAL_SERVICE_NUM, $prepareVisitParams) === false) {
            $serialServiceNum = self::getSerialServiceNum($visitId, $reportId);
            $prepareVisitParams[self::PARAM_SERIAL_SERVICE_NUM] = $serialServiceNum['char_value'];
        }

        return $prepareVisitParams;
    }

    /**
     * @param int $visitId
     *
     * @return array
     * @throws Throwable
     */
    public static function getServiceParamsForVSDReport(int $visitId): array {
        $visitServiceVsdIds =  (new Query())
            ->select('id')
            ->from(VisitsGovServices::tableName())
            ->where([
                'AND',
                ['id_visit' => $visitId],
                ['id_service' => self::VSD_SERVICE_FOR_REPORT],
            ])
            ->column();

        $reports = [];
        foreach ($visitServiceVsdIds as $serviceVsdId) {
            $servicesParams = self::getVSDParamValues($serviceVsdId);

            $prepareVisitParams = [];
            foreach (ArrayHelper::index($servicesParams, 'tech_name') as $techName => $visitServiceParam) {
                $prepareVisitParams[$techName] = self::getParamValue($visitServiceParam);
            }

            if (array_key_exists(self::PARAM_SERIAL_SERVICE_NUM, $prepareVisitParams) === false) {
                $serialServiceNum = self::getSerialServiceNum($visitId, PdfGenerator::VSD_REPORT);
                $prepareVisitParams[self::PARAM_SERIAL_SERVICE_NUM] = $serialServiceNum['char_value'];
            }

            $reports[] = $prepareVisitParams;
        }

        return $reports;
    }
    /**
     * @param int $visitId
     * @param int $reportId
     *
     * @return array
     * @throws Exception
     * @throws Throwable
     */
    public static function getSerialServiceId(int $visitId, int $reportId): array
    {
        $visitServiceId = (new Query())
            ->select(['vgs.id'])
            ->from(VisitsGovServices::tableName() . ' vgs')
            ->innerJoin(
                GovServicesReports::tableName() . ' gsr',
                'gsr.id_service = vgs.id_service'
            )
            ->where([
                'AND',
                ['gsr.id_report' => $reportId],
                ['vgs.id_visit' => $visitId],
            ])
            ->all();
        return $visitServiceId;
    }
    /**
     * @param int $visitId
     * @param int $reportId
     *
     * @return array
     * @throws Exception
     * @throws Throwable
     */
    public static function getSerialServiceNum(int $visitId, int $reportId): array
    {
        /* @var $serialParam Params */
        $serialParam = Params::find()
            ->alias('p')
            ->innerJoin(
                ReportsParams::tableName() . ' rp',
                'rp.id_param = p.id AND rp.id_report = ' . $reportId
            )
            ->andWhere(['p.tech_name' => self::PARAM_SERIAL_SERVICE_NUM])
            ->one();
        if ($serialParam === null) {
            return [];
        }

        $visitServiceId = (new Query())
            ->select(['vgs.id'])
            ->from(VisitsGovServices::tableName() . ' vgs')
            ->innerJoin(
                GovServicesReports::tableName() . ' gsr',
                'gsr.id_service = vgs.id_service'
            )
            ->where([
                'AND',
                ['gsr.id_report' => $reportId],
                ['vgs.id_visit' => $visitId],
            ])
            ->one();

        return ServiceReportsDataHelper::generateSerialServiceNum(
            $visitId,
            $reportId,
            $serialParam->id,
            $visitServiceId['id']
        );
    }

    /**
     * @param array $paramRow
     *
     * @return string
     */
    private static function getParamValue(array $paramRow): string
    {
        switch ($paramRow['datatype']) {
            case ParamsTrait::$dttmDatatype:
                return $paramRow['date_value'] ? date('d.m.Y', $paramRow['date_value']) : '';
            case ParamsTrait::$numericDatatype:
                return $paramRow['num_value'] ?: '';
            case ParamsTrait::$dictDatatype:
                $dict = Dictionaries::findOne(['id' => $paramRow['dict_value']]);
                return $dict->name ?? '';
            case ParamsTrait::$complexDatatype:
                if (empty($paramRow['complex_value'])) {
                    return '';
                }
                try {
                    $ids = is_array($paramRow['complex_value']) ? $paramRow['complex_value'] : Json::decode($paramRow['complex_value']);
                } catch (\Throwable $e) {
                    $ids = [];
                }
                $ids = array_filter($ids);
                $dicts = Dictionaries::findAll(['id' => $ids]);
                $values = ArrayHelper::getColumn($dicts, 'name');
                return empty($values) ? '' : implode(', ', $values);
            case ParamsTrait::$textDatatype:
            default:
                if (is_null($paramRow['char_value'])) {
                    return '';
                }
                return (!empty($paramRow['datatype_details']) && $paramRow['datatype_details'] > 255) ?
                    nl2br($paramRow['char_value'])
                    : $paramRow['char_value'];
        }
    }

    /**
     * @param int $visitId
     *
     * @return array
     */
    private static function getVisitParams(int $visitId): array
    {
        return (new Query())
            ->select([
                'vpv.num_value',
                'vpv.char_value',
                'vpv.date_value',
                'vpv.dict_value',
                'p.tech_name',
                'p.datatype',
                'p.datatype_details',
            ])
            ->from(VisitParamValues::tableName() . ' vpv')
            ->innerJoin(
                Params::tableName() . ' p',
                'p.id = vpv.id_param'
            )
            ->where(['vpv.id_visit' => $visitId])
            ->all();
    }

    /**
     * Получение параметров услуг для бланка отчета
     *
     * @param int $visitId
     * @param int $report
     *
     * @return array
     */
    private static function getVisitServiceParamValues(int $visitId, int $report): array
    {
        return (new Query())
            ->select([
                'vspv.num_value',
                'vspv.char_value',
                'vspv.date_value',
                'vspv.dict_value',
                'vspv.complex_value',
                'p.tech_name',
                'p.datatype',
                'p.datatype_details',
            ])
            ->from(VisitServiceParamValues::tableName() . ' vspv')
            ->innerJoin(
                Params::tableName() . ' p',
                'p.id = vspv.id_param'
            )
            ->innerJoin(
                VisitsGovServices::tableName() . ' vgs',
                'vgs.id = vspv.id_visitservice'
            )
            ->innerJoin(
                GovServicesReports::tableName() . ' gsr',
                'gsr.id_service = vgs.id_service'
            )
            ->where([
                'AND',
                ['vgs.id_visit' => $visitId],
                ['gsr.id_report' => $report]
            ])
            ->orderBy('vspv.id')
            ->all();
    }

    /**
     * Получение параметров по ВСД услуге для бланка отчета
     *
     * @param int $visitGovServiceId
     *
     * @return array
     */
    private static function getVSDParamValues(int $visitGovServiceId): array
    {
        return (new Query())
            ->select([
                'vspv.num_value',
                'vspv.char_value',
                'vspv.date_value',
                'vspv.dict_value',
                'vspv.complex_value',
                'p.tech_name',
                'p.datatype',
                'p.datatype_details',
            ])
            ->from(VisitServiceParamValues::tableName() . ' vspv')
            ->innerJoin(
                Params::tableName() . ' p',
                'p.id = vspv.id_param'
            )
            ->innerJoin(
                VisitsGovServices::tableName() . ' vgs',
                'vgs.id = vspv.id_visitservice'
            )
            ->innerJoin(
                GovServicesReports::tableName() . ' gsr',
                'gsr.id_service = vgs.id_service'
            )
            ->where(['vgs.id' => $visitGovServiceId])
            ->all();
    }

    /**
     * @param int $visitId
     *
     * @return array
     */
    public static function getReportsForPrint(int $visitId): array
    {
        $reportIds = (new Query())
            ->select('r.id')
            ->from(VisitsGovServices::tableName() . ' vgs')
            ->innerJoin(
                GovServicesReports::tableName() . ' AS gsr',
                'gsr.id_service = vgs.id_service'
            )
            ->innerJoin(
                Reports::tableName() . ' AS r',
                'r.id = gsr.id_report'
            )
            ->where([
                'AND',
                ['vgs.id_visit' => $visitId],
                ['r.report_type' => Reports::TYPE_REPORT],
            ])
            ->groupBy('r.id')
            ->column();

        // отфильтровываем отчеты по УЗИ
        if (array_intersect($reportIds, self::ULTRASOUND_REPORT_IDS)) {
            return array_diff(
                $reportIds,
                array_diff(
                    self::ULTRASOUND_REPORT_IDS,
                    self::getUltrasoundReportIdsForPrint($visitId)
                )
            );
        }

        return $reportIds;
    }

    /**
     * Получаем список необходимых для печати отчетов по УЗИ (различных систем органов)
     *
     * @param int $visitId
     *
     * @return array
     */
    private static function getUltrasoundReportIdsForPrint(int $visitId): array
    {
        $organSystemNames = (new Query())
            ->select('d.name')
            ->from(VisitServiceParamValues::tableName() . ' vspv')
            ->innerJoin(
                VisitsGovServices::tableName() . ' vgs',
                'vgs.id = vspv.id_visitservice'
            )
            ->innerJoin(
                Params::tableName() . ' p',
                'p.id = vspv.id_param'
            )
            ->innerJoin(
                Dictionaries::tableName() . ' d',
                'vspv.dict_value = d.id'
            )
            ->where([
                'AND',
                ['vgs.id_visit' => $visitId],
                ['p.tech_name' => self::PARAM_ORGAN_SYSTEM_TECH_NAME]
            ])
            ->groupBy('d.name')
            ->column();

        $ultrasoundReports = [];

        foreach ($organSystemNames as $organSystemName) {
            if (in_array($organSystemName, self::ULTRASOUND_REPORT_IDS_TO_ORGAN_SYSTEM, true)) {
                $ultrasoundReports[] = array_search($organSystemName, self::ULTRASOUND_REPORT_IDS_TO_ORGAN_SYSTEM, true);
            }
        }

        return $ultrasoundReports;
    }
}
