<?php
/**
 * @author Serge Postrash <jexy.ru@gmail.com>
 */

namespace app\common\components\visitServiceReport\helpers;

use app\models\db\GovServices;
use app\models\db\Organizations;
use app\models\db\Params;
use app\models\db\Reports;
use app\models\db\ReportsParams;
use app\models\db\Visits;
use app\models\db\VisitServiceReportSerialservicenum;
use app\models\db\VisitServiceTmc;
use app\modules\v2\modules\pets\models\IdentModel;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\UnprocessableEntityHttpException;
use app\models\db\Dictionaries;
use app\models\db\VisitServiceParamValues;
use app\modules\v1\models\FileResource;

/**
 * Class ServiceReportsDataHelper
 *
 * @package app\modules\v1\models
 */
class ServiceReportsDataHelper extends Model
{
    use ParamsTrait;

    /**
     * @var int
     */
    public $id_visitservice;

    /**
     * @var int
     */
    public $id_service;

    /**
     * @var int
     */
    public $id_visit;

    /**
     * @param \app\modules\v1\models\EntityResource[] $inputVisitServiceParamsValues Массив объектов значений параметров
     * @param bool $forPdf
     * @return array
     * @throws \yii\web\UnprocessableEntityHttpException
     */
    public function prepareOutput($inputVisitServiceParamsValues = [], $forPdf = false)
    {
        $service = GovServices::findAll(['id' => $this->id_service]);
        if ($service === null) {
            throw new InvalidConfigException("Выбраная услуга #{$this->id_service} не найдена");
        }

        // Список всех параметров для приёмов
        $visitParams = VisitParamsDataHelper::findReportParamsForVisit();

        // сохраненные значения параметров приёма
        $visitParamsValuesGroupped = $this->groupVisitParams(
            $this->findVisitParamValues($this->id_visitservice)
        );

        // Список всех параметров для услуги
        $serviceParams = $this->findReportParamsForService($this->id_service);

        // Список обязательных параметров услуги
        $reqInParams = $this->findReqInParamsForService($this->id_service);

        /*
         * !!! Пока отключим
        if ($forPdf === true) {
            // проверяем наличие обязательных исходящих параметров, так как отчет может сгенерироваться
            // просто при открытии формы и наличия хотя бы одного параметра
            // - так как фронт отправляет запрос к service-reports/{id}/files
            // (особенно актуально для услуг с входящими параметрами)
            $this->validateNotPassedRequiredParams($inputVisitServiceParamsValues, $serviceParams, $this->id_visitservice);
            if ($this->hasErrors()) {
                return false;
            }
        }
        */
        $report = null;
        $paramsList = [];
        foreach ($visitParamsValuesGroupped as $visitParamsValuesGroup) {
            $paramsValues = [];
            $changeableParams = false;
            foreach ($visitParams as $param) {
                $paramsValues[] = $this->formatParamValue(
                    ArrayHelper::getValue($visitParamsValuesGroup['params'], $param['id'], []),
                    $param,
                    'visit_param_value',
                    $forPdf
                );
            }

            $reports = [];
            foreach ($serviceParams as $param) {
                // Ищем параметра, что соответствуют указаной группе параметров ($visitParamsValuesGroup)
                // и приводим их к правильному в массиву
                $visitServiceParamsValues = ArrayHelper::map(
                    $this->findVisitServiceParamsValues(
                        $inputVisitServiceParamsValues,
                        $visitParamsValuesGroup['id_visit_service_tmc'],
                        $visitParamsValuesGroup['id_pet']
                    ),
                    'id_param',
                    function ($valueResource) use ($visitParamsValuesGroup) {
                        /** @var \app\modules\v1\models\EntityResource $valueResource */
                        return array_merge(['id' => $valueResource->getId()], $valueResource->toArray());
                    }
                );

                // проверка обязательных полей
                if ($reqInParams) {
                    $reqInIds = ArrayHelper::getColumn($reqInParams, 'id', false);
                    $visitServiceInputParams = array_filter($visitServiceParamsValues, function ($paramValue) use ($reqInIds) {
                        return in_array($paramValue['id_param'], $reqInIds);
                    });
                    if (empty($visitServiceInputParams)) {
                        throw new UnprocessableEntityHttpException("Не заданы обязательные входящие параметры для услуги #{$this->id_service}");
                    }
                    $diff = array_diff($reqInIds, array_keys($visitServiceInputParams));
                    if (count($diff) > 0) {
                        $missing = implode(', ', $diff);
                        throw new UnprocessableEntityHttpException("Не заданы обязательные входящие параметры {$missing} для услуги #{$this->id_service}");
                    }
                }

                // FIXME: Выбирает значение параметра, не учитывая животное
                $visitServiceParam = ArrayHelper::getValue($visitServiceParamsValues, $param['id'], []);
                $config = empty($param['config']) ? [] : Json::decode($param['config']);
                // проверяем, есть ли входящие параметры услуги, исключаем исходящие параметры, не относящиеся к данной услуге
                if (empty($reqInParams) || empty($visitServiceInputParams) || empty($config)) {
                    $paramsValues[] = $this->formatParamValue($visitServiceParam, $param, 'visit_service_param_value', $forPdf);
                    $changeableParams = true;
                } else {
                    foreach ($config as $confData) {
                        // используется для исходящих параметров услуги, зависящих от входящего параметра (УЗИ - система органов)
                        if (!isset($confData['req_in'])) {
                            continue;
                        }
                        $reqInParam = ArrayHelper::getValue($reqInParams, $confData['req_in']['tech_name']);
                        if ($reqInParam === null) {
                            continue;
                        }
                        if (!array_key_exists($reqInParam['id'], $visitServiceInputParams)) {
                            continue;
                        }
                        $columnName = self::resolveColumnName($reqInParam['datatype']);
                        if ($columnName === null) {
                            throw new InvalidConfigException('Неизвестный тип данных ' . $reqInParam['datatype'] . ' для параметра "' . $reqInParam['name'] . '"');
                        }

                        if ($reqInParam['datatype'] == self::$dictDatatype) {
                            $value = Dictionaries::find()
                                ->select('name')
                                ->where([
                                    'type' => $reqInParam['datatype_details'],
                                    'id' => $visitServiceInputParams[$reqInParam['id']][$columnName],
                                ])
                                ->scalar();
                        } else {
                            $value = $visitServiceInputParams[$reqInParam['id']][$columnName];
                        }
                        if ($confData['req_in']['value'] == $value) {
                            $paramsValues[] = $this->formatParamValue($visitServiceParam, $param, 'visit_service_param_value', $forPdf);
                            $changeableParams = true;
                            if (isset($confData['report'])) {
                                $reports[] = $confData['report'];
                            }
                            break;
                        }
                    }
                }
            }

            if (!empty($reports)) {
                $reports = array_unique($reports);
                if (count($reports) > 1) {
                    throw new InvalidConfigException("В параметрах задано более одного отчета для выбранной услуги #{$this->id_service}");
                }
            }

            $report = self::findReportForService($this->id_service, empty($reports) ? null : $reports[0]);

            // Номер экспертизы/исследования
            // https://jira.altarix.ru/browse/VETAIS-1369
            if ($report !== null) {
                $this->saveSerialServiceNum($report, $paramsValues, $forPdf);
            }

            if ($forPdf === true) {
                if ($report === null) {
                    throw new InvalidConfigException("Отсутствует отчет для выбранной услуги #{$this->id_service}");
                }

                if ($report['grouped'] === true) {
                    // это групповой отчет по нескольким услугам ('Результат биохимического исследования крови',
                    // 'Результат гормональных исследований крови', 'Результат общего клинического анализа крови')
                    $associatedServices = self::findServicesForReport($report['id_report'], [$this->id_service]);
                    if (!empty($associatedServices)) {
                        // находим записи для других услуг данного приема, попадающих в групповой отчет
                        $service_ids = ArrayHelper::getColumn($associatedServices, 'id');
                        $groupedVisitServiceRecords = $this->findGroupedVisitServiceRecords($service_ids);
                        if (!empty($groupedVisitServiceRecords)) {
                            $visit_service_ids = ArrayHelper::getColumn($groupedVisitServiceRecords, 'id');
                            // удаляем файлы для других услуг, так как в них отсутствуют изменения в текущей услуге
                            try {
                                $this->deleteFileResources($visit_service_ids, 'visits_gov_service');
                            } catch (\Throwable $e) {
                                Yii::error('Failed to delete pdf file for visitservice_ids: ' . implode(', ', $visit_service_ids) . "\n" . $e->getMessage());
                            }
                            // а теперь нужно засунуть в отчет параметры для других услуг, попадающих в групповой отчет
                            foreach ($groupedVisitServiceRecords as $record) {
                                // FIXME: Перезапись переменных $serviceParams и $visitServiceParamsValues
                                $serviceParams = $this->findReportParamsForService($record['id_service'], $report['id']);
                                $visitServiceParamsValues = VisitServiceParamValues::findAll([
                                    'id_visitservice' => $record['id']
                                ]);
                                if (empty($serviceParams) || empty($visitServiceParamsValues)) {
                                    // упростим себе жизнь
                                    continue;
                                }
                                $visitServiceParamsValues = ArrayHelper::map($visitServiceParamsValues, 'id_param', function ($model) {
                                    /* @var \app\models\db\VisitServiceParamValues $model */

                                    return $model->toArray();
                                });
                                // foreach ($serviceParams as $param) {
                                //     $visitServiceParamValue = ArrayHelper::getValue($visitServiceParamsValues, $param['id'], []);
                                //     $paramsValues[] = $this->formatParamValue($visitServiceParamValue, $param, 'visit_service_param_value', true);
                                // }
                            }
                        }
                    }
                }
            }

            $paramsList[] = [
                'id_visit_service_tmc' => $visitParamsValuesGroup['id_visit_service_tmc'],
                'id_pet' => $visitParamsValuesGroup['id_pet'],
                'changeable_params' => $changeableParams,
                'params' => $paramsValues,
            ];
        }

        if (!$paramsList) {
            throw new InvalidConfigException("Отсутствуют данные отчета для выбранной услуги #{$this->id_service}");
        }

        if ($forPdf) {
            return [
                'id_report' => $report['id_report'],
                'params' => $this->formatParamsListAsPrintResult($paramsList, $report),
            ];
        } else {
            return
                [
                    'id_visitservice' => $this->id_visitservice,
                    'id_service' => $this->id_service,
                    'id_visit' => $this->id_visit,
                    'id_report' => $report ? $report['id_report'] : null,
                    'animals' => $this->formatParamsListAsFrontResult($paramsList, $report),
                ];
        }
    }

    /**
     * Значений параметров группируются по тройке (услуга в приёме, ТМЦ, животное)
     * т.е. (id_visit_service, id_visit_service_tmc, id_pet)
     *
     * @param $visitParams
     * @return array
     */
    private function groupVisitParams($visitParams)
    {
        $result = [];

        foreach ($visitParams as $visitParam) {
            $key = $visitParam['id_visitservice'] . '_' . $visitParam['id_visit_service_tmc'] . '_' . $visitParam['id_pet'];
            $result[$key]['id_visit_service_tmc'] = $visitParam['id_visit_service_tmc'];
            $result[$key]['id_pet'] = $visitParam['id_pet'];
            $result[$key]['params'][$visitParam['id_param']] = $visitParam;

        }

        return $result;
    }

    /**
     * Ищет и возвращает список параметров по указанным $visitServiceTmcId и $petId
     *
     * @param VisitServiceParamValues[] $models
     * @param int|null $visitServiceTmcId
     * @param int|null $petId
     * @return VisitServiceParamValues[]
     */
    private function findVisitServiceParamsValues(array $visitServiceParamValueModels, ?int $visitServiceTmcId, ?int $petId): array
    {
        return array_filter($visitServiceParamValueModels, function ($visitServiceParamValueModel) use ($visitServiceTmcId, $petId) {
            return
                $visitServiceParamValueModel->id_visit_service_tmc === $visitServiceTmcId &&
                $visitServiceParamValueModel->id_pet === $petId;
        });
    }

    /**
     * @param array $params
     * @param array $report
     * @return array[]
     */
    public function formatParamsListAsFrontResult(array $paramsList, ?array $report)
    {
        // Если вакцинация
        if ($report && $report['id_report'] === Reports::CATEGORY_VACCINATION) {
            return $this->formatParamsListAsFrontVaccinationResult($paramsList);
        }

        return $paramsList;
    }

    /**
     * @param array $params
     * @param array $report
     * @return array[]
     */
    public function formatParamsListAsPrintResult(array $paramsList, array $report)
    {
        // Если вакцинация
        if ($report['id_report'] === Reports::CATEGORY_VACCINATION) {
            return $this->formatParamsListAsPrintVaccinationResult($paramsList);
        }

        //Другие отчеты
        return array_map(function ($item) {
            return ArrayHelper::map($item['params'], 'tech_name', 'value');
        }, $paramsList);
    }

    /**
     * @param array $params
     * @param array $report
     * @return array[]
     */
    public function formatParamsListAsFrontVaccinationResult(array $dataList)
    {
        $result = [];
        foreach ($dataList as $data) {
            $params = array_filter($data['params'], function ($p) {
                return !in_array($p['attributes']['tech_name'], [
                    'P13_Servicetext',
                    'P0_Inventorynumber',
                    'P15_Vacexpirationdate',
                    'P13_ListOfDiseases',
                ]);
            });

            $params = array_values($params);

            $findValue = function ($params, $key) {
                foreach ($params as $param) {
                    if ($param['attributes']['tech_name'] == $key) {
                        return $param['attributes']['value'];
                    }
                }
            };

            $tmc = null;
            $visitServiceTmc = VisitServiceTmc::findOne($data['id_visit_service_tmc']);
            if ($visitServiceTmc) {
                $vacexpirationDate = $findValue($data['params'], 'P15_Vacexpirationdate');
                $tmc = [
                    'name' => $findValue($data['params'], 'P13_Servicetext'),
                    'produced' => $visitServiceTmc ? $visitServiceTmc->tmc->produced : null,
                    'inventory_number' => $findValue($data['params'], 'P0_Inventorynumber'),
                    'vacexpiration_date' => $vacexpirationDate && is_numeric($vacexpirationDate) ? date('Y-m-d H:i:s', $vacexpirationDate) : $vacexpirationDate,
                    'diseases' => $findValue($data['params'], 'P13_ListOfDiseases'),
                ];
            }

            if (array_key_exists($data['id_pet'], $result)) {
                if ($tmc) {
                    $result[$data['id_pet']]['tmc'][] = $tmc;
                }
            } else {
                $result[$data['id_pet']] = [
                    'id_pet' => $data['id_pet'],
                    'changeable_params' => $data['changeable_params'],
                    'params' => $params,
                ];
                $result[$data['id_pet']]['tmc'] = $tmc ? [$tmc] : [];
            }

        }

        return array_values($result);
    }

    /**
     * @param array $params
     * @param array $report
     * @return array[]
     */
    public function formatParamsListAsPrintVaccinationResult(array $dataList)
    {
        $result = [];
        foreach ($dataList as $data) {
            $params = array_filter($data['params'], function ($item) {
                return !in_array($item['tech_name'], [
                    'P13_Servicetext',
                    'P0_Inventorynumber',
                    'P15_Vacexpirationdate',
                    'P13_ListOfDiseases',
                ]);
            });

            $params = array_values($params);

            $findValue = function ($params, $key) {
                foreach ($params as $param) {
                    if ($param['tech_name'] == $key) {
                        return $param['value'];
                    }
                }
            };

            $tmc = null;
            $visitServiceTmc = VisitServiceTmc::findOne($data['id_visit_service_tmc']);
            if ($visitServiceTmc) {
                $vacexpirationDate = $findValue($data['params'], 'P15_Vacexpirationdate');
                $tmc = [
                    'name' => $findValue($data['params'], 'P13_Servicetext'),
                    'produced' => $visitServiceTmc->tmc->produced,
                    'inventory_number' => $findValue($data['params'], 'P0_Inventorynumber'),
                    'vacexpiration_date' => $vacexpirationDate && is_numeric($vacexpirationDate) ? date('Y-m-d H:i:s', $vacexpirationDate) : $vacexpirationDate,
                    'diseases' => $findValue($data['params'], 'P13_ListOfDiseases'),
                ];
            }

            if (!array_key_exists($data['id_pet'], $result)) {
                $result[$data['id_pet']] = ArrayHelper::map($params, 'tech_name', 'value');
            }

            if ($tmc) {
                $result[$data['id_pet']]['tmc'][] = $tmc;
            }
        }

        return array_values($result);
    }

    /**
     * @param array $post
     * @param string $action_id
     * @param array $models
     * @return bool
     */
    public function save($post, $action_id, $models = null)
    {

        $fields = $this->findReportParamsForService($this->id_service);

        if (empty($fields)) {
            throw new UnprocessableEntityHttpException("Не определены поля для заполнения отчета по услуге #{$this->id_service}");
        }

        foreach($post as $idx => $item) {
            if ($item['id_param'] == 528 && $item['value'] == '') $post[$idx]['value'] = null;
        }

        $this->validateNotPassedRequiredParams($post, $fields, $this->id_visitservice);
        if (!empty($this->errors)) {
            return false;
        }

        $rows = [];
        if ($action_id == 'update'
//            && empty($models)
        ) {
            foreach ($post as $data) {
                $row = $this->prepareRow($data, $fields);
                if ($row !== false) {
                    // при PUT может быть ситуация, когда приходит часть параметров с id === null
                    // (новые параметры - по какой-то причине не были сохранены раньше)
                    $rows[] = empty($data['id']) ? $row : array_merge($row, ['id' => $data['id']]);
                }
            }

            if (!empty($this->errors)) {
                return false;
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($rows as $row) {
                    if ($row['id_param'] === 1
                        && (isset($row['char_value']))
                    ) {
                        (new IdentModel)->save($row['id_pet'],
                            [
                                [
                                    '__TEMP_UID__' => strval($this->id_visit),
                                    'id' => $this->id_visit,
                                    'main_flag' => true,
                                    'identification_code' => $row['char_value'],
                                    'id_ident_type' => 1,
                                    'id_pet' => $row['id_pet']
                                ]
                            ]
                        );
                    }
                    if ($row['id_param'] === 1
                        && (!isset($row['char_value']))) {
                        continue;
                    }
                    Yii::$app->db->createCommand()
                        ->upsert(VisitServiceParamValues::tableName(), $row)
                        ->execute();
                }
                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
            
        } else {
            foreach ($post as $data) {
                $row = $this->prepareRow($data, $fields);
                if ($row !== false) {
                    $rows[] = $row;
                }
            }
            

            if (!empty($this->errors)) {
                return false;
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $columns = $this->columns(VisitServiceParamValues::class, true);
                Yii::$app->db->createCommand()
                    ->batchInsert(VisitServiceParamValues::tableName(), $columns, $rows)
                    ->execute();
                $transaction->commit();

            } catch (\Throwable $e) {

                $transaction->rollBack();
                throw $e;
            }
        }

        // нужно дополнительно обработать параметры с datatype == 'file'
        $this->processFileResources($fields, $post);

        return true;
    }

    /**
     * @param array $fields
     * @param array $post
     * @throws Exception
     */
    private function processFileResources($fields, $post)
    {
        $fileFields = array_filter($fields, function ($field) {
            return $field['datatype'] == self::$fileDatatype;
        });

        if (empty($fileFields)) {
            return;
        }

        $param_ids = ArrayHelper::getColumn($fileFields, 'id');
        foreach ($post as $data) {
            if (!in_array($data['id_param'], $param_ids)) {
                continue;
            }
            $id = empty($data['id'])
                ? (new Query())
                    ->select('id')
                    ->from(VisitServiceParamValues::tableName())
                    ->where([
                        'id_visitservice' => $this->id_visitservice,
                        'id_param' => $data['id_param'],
                    ])
                    ->scalar()
                : $data['id'];
            if (empty($id)) {
                continue;
            }

            $filesIds = empty($data['value']) ? [] : ArrayHelper::getColumn($data['value'], 'id');
            if (empty($filesIds)) {
                Yii::$app->db
                    ->createCommand()
                    ->delete(FileResource::tableName(), [
                        'entity_id' => $id,
                        'entity_type' => 'visit_service_param_value',
                    ])
                    ->execute();
            } else {
                Yii::$app->db
                    ->createCommand()
                    ->delete(FileResource::tableName(), [
                        'and',
                        ['entity_id' => $id],
                        ['entity_type' => 'visit_service_param_value'],
                        ['not in', 'id', $filesIds],
                    ])
                    ->execute();
                // нужно прилинковать новые файлы
                $files = FileResource::findAll(['id' => $filesIds]);
                foreach ($files as $file) {
                    if ($file->entity_id == $id && $file->entity_type == 'visit_service_param_value') {
                        continue;
                    }
                    $file->entity_id = $id;
                    $file->entity_type = 'visit_service_param_value';
                    /** @var \app\common\components\FileService $fileService */
                    $fileService = Yii::$app->fileService;
                    $fileService->attach($file);
                }
            }
        }
    }

    /**
     * @param array $service_ids
     * @return array
     */
    private function findGroupedVisitServiceRecords(array $service_ids)
    {
        return (new Query())
            ->select('*')
            ->from('visits_gov_services')
            ->where(['in', 'id_service', $service_ids])
            ->andWhere(['id_visit' => $this->id_visit])
            ->all();
    }

    /**
     * Номер экспертизы/исследования
     *
     * @see https://jira.altarix.ru/browse/VETAIS-1369
     * @param array $report
     * @param array $params
     * @param bool $fromPdf
     */
    private function saveSerialServiceNum($report, &$params, $fromPdf = false)
    {
        /* @var $serialParam \app\models\db\Params */
        $serialParam = Params::find()
            ->alias('params')
            ->innerJoin(
                ReportsParams::tableName(),
                ReportsParams::tableName() . '.[[id_param]] = ' . '[[params]].[[id]] AND ' . ReportsParams::tableName() . '.[[id_report]] = :id_report',
                ['id_report' => $report['id']]
            )
            ->andWhere(['[[params]].[[tech_name]]' => 'P2_SerialServiceNum'])
            ->one();

        if ($serialParam === null) {
            return;
        }

        // проверяем, был ли он уже сохранен
        /* @var $exists \app\models\db\VisitServiceParamValues */
        $exists = VisitServiceParamValues::find()
            ->where([
                'id_param' => $serialParam->id,
                'id_visitservice' => $this->id_visitservice,
            ])
            ->one();

        if ($exists) {
            if ($fromPdf === true) {
                // так как параметры были уже выбраны, нам нужно это поле
                $params[] = $this->formatParamValue($exists->toArray(), $serialParam->toArray(), 'visit_service_param_value', true);
            }

            return;
        }

        if ($report['grouped'] === true) {
            // это групповой отчет по нескольким услугам ('Результат биохимического исследования крови' и т.п.)
            // проверим, не был ли сохранен этот параметр ранее для другой групповой услуги
            $associatedServices = self::findServicesForReport($report['id_report'], [$this->id_service]);
            if (!empty($associatedServices)) {
                // находим записи для других услуг данного приема, попадающих в групповой отчет
                $service_ids = ArrayHelper::getColumn($associatedServices, 'id');
                $groupedVisitServiceRecords = $this->findGroupedVisitServiceRecords($service_ids);
                if (!empty($groupedVisitServiceRecords)) {
                    $visit_service_ids = ArrayHelper::getColumn($groupedVisitServiceRecords, 'id');
                    // находим хотя бы один (подразумевается что у них у всех будет одинаковое значение)
                    /* @var $exists \app\models\db\VisitServiceParamValues */
                    $exists = VisitServiceParamValues::find()
                        ->where([
                            'id_param' => $serialParam->id,
                            'id_visitservice' => $visit_service_ids,
                        ])
                        ->one();
                    if ($exists !== null) {
                        $model = new VisitServiceParamValues([
                            'id_param' => $serialParam->id,
                            'id_visitservice' => $this->id_visitservice,
                            'char_value' => $exists->char_value,
                        ]);
                        if ($model->save()) {
                            if ($fromPdf === true) {
                                // так как параметры были уже выбраны, нам нужно это поле
                                $params[] = $this->formatParamValue($model->toArray(), $serialParam->toArray(), 'visit_service_param_value', true);
                            }
                        }

                        return;
                    }
                }
            }
        }

        // не нашли - генерируем и сохраняем
        $serialServiceNum = self::generateSerialServiceNum(
            $this->id_visit,
            $report['id'],
            $serialParam->id,
            $this->id_visitservice
        );

        if ($fromPdf === true) {
            // так как параметры были уже выбраны, нам нужно это поле
            $params[] = $this->formatParamValue(
                $serialServiceNum,
                $serialParam->toArray(),
                'visit_service_param_value',
                true
            );
        }
    }

    /**
     * @param int $visitId
     * @param int $reportId
     * @param int $serialParamId
     * @param int $visitServiceId
     * @return array
     * @throws \Throwable
     * @throws Exception
     */
    public static function generateSerialServiceNum(
        int $visitId,
        int $reportId,
        int $serialParamId,
        int $visitServiceId
    ): array
    {
        $visit = Visits::findOne(['id' => $visitId]);
        $organization = Organizations::findOne(['id' => $visit->id_organization]);
        $year = (int)date('Y');

        // ищем последний номер для данного отчета и данной организации
        $last = VisitServiceReportSerialservicenum::findOne([
            'year' => $year,
            'id_organization' => $organization->id,
            'id_report' => $reportId,
        ]);
        if ($last === null) {
            $last = new VisitServiceReportSerialservicenum([
                'year' => $year,
                'id_organization' => $organization->id,
                'id_report' => $reportId,
                'last_number' => 0,
            ]);
        }

        $last_number = $last->last_number + 1;
        $prefix = '';
        if (!empty($organization->reg_number)) {
            $prefix .= str_pad($organization->reg_number, 6, '0', STR_PAD_LEFT);
            $prefix .= '-';
        }
        $serialNum = $prefix . $last_number;

        $visitServiceParamValue = new VisitServiceParamValues([
            'id_param' => $serialParamId,
            'id_visitservice' => $visitServiceId,
            'char_value' => $serialNum,
        ]);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $result = $visitServiceParamValue->save();
            if ($result !== true) {
                $transaction->rollBack();
            } else {
                $last->last_number = $last_number;
                $result = $last->save();
                if ($result !== true) {
                    $transaction->rollBack();
                } else {
                    $transaction->commit();

                    return $visitServiceParamValue->toArray();
                }
            }
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
