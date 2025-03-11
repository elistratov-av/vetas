<?php

namespace app\modules\v2\modules\reports\models;

use app\common\helpers\DateHelper;
use app\common\models\VisitStatus;
use app\common\validators\FullTrimValidator;
use app\models\db\DescriptionTypes;
use app\models\db\Discount;
use app\models\db\GovServices;
use app\models\db\GovServicesReports;
use app\models\db\Params;
use app\models\db\PetIdentification;
use app\models\db\PetOwners;
use app\models\db\Pets;
use app\models\db\Specialists;
use app\models\db\tmc\TmcBase;
use app\models\db\Users;
use app\models\db\VisitDescriptions;
use app\models\db\VisitParamValues;
use app\models\db\VisitPrice;
use app\models\db\Visits;
use app\models\db\VisitServiceParamValues;
use app\models\db\VisitServiceTmc;
use app\models\db\VisitsGovServices;
use app\models\db\VisitsSpecialists;
use app\modules\v2\modules\visit\models\ParamsTrait;
use yii\base\Model;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Class JournalModel
 * @package app\modules\v2\modules\reports\models
 */
class JournalModel extends Model
{
    use ParamsTrait;

    /**
     * @var \app\models\db\Reports
     */
    public $model;
    /**
     * @var int
     */
    public $id_organization;

    /**
     * @var array
     */
    private $filter;
    /**
     * @var int
     */
    private $limit;
    /**
     * @var int
     */
    private $page;
    /**
     * @var int
     */
    private $totalCount = 0;
    /**
     * @var int
     */
    private $pagesCount = 0;

    /**
     * @param array $ids
     * @return array
     */
    public function findRecords($ids = [])
    {
        $meta = self::prepareMeta($this->model);
        $fullTrimValidator = new FullTrimValidator();

        if (empty($meta) || !isset($meta['headers']) || empty($meta['headers'])) {
            $this->addError('model', 'Не настроена конфигурация журнала "' . $this->model->name . '"');

            return false;
        }

        $perVisit = $this->isPerVisitJournal();
        $isGrouped = $this->isGroupedServicesJournal();

        if ($isGrouped === true || $perVisit === false) {
            $services = $this->findServicesForJournal($this->model->id);
            if (empty($services)) {
                $this->addError('model', 'Для журнала не назначено услуг');

                return false;
            }
        }

        if ($perVisit === true) {
            // журнал формируется в разрезе приема в целом
            $query = Visits::find()
                ->alias('v')
                ->where([
                    'v.status' => VisitStatus::FINISHED,
                    'v.id_organization' => $this->id_organization,
                ])
                ->orderBy([
                    'created_at' => SORT_ASC,
                ]);

            if ($this->isAmbulanceJournal()) {
                $query->andWhere(['type' => Visits::TYPE_AMBULANCE]);
            } else {
                $query->andWhere(['in', 'type', [Visits::TYPE_VISIT, Visits::TYPE_AT_HOME]]);
            }

            if ($isGrouped === true) {
                // в журнале группируются услуги приема
                $query->andWhere([
                    'in',
                    'v.id',
                    (new Query())
                        ->select('id_visit')
                        ->from(VisitsGovServices::tableName())
                        ->where(['in', 'id_service', ArrayHelper::getColumn($services, 'id')])
                        ->groupBy('id_visit'),
                ]);
            }

            if (!empty($ids)) {
                // собираем данные для отдельной записи журнала
                $query->andWhere(['in', 'v.id', $ids]);
            }

        } else {
            // журнал формируется в разрезе услуг приема
            $query = VisitsGovServices::find()
                ->alias('vgs')
                ->innerJoin(
                    Visits::tableName() . ' v',
                    'vgs.id_visit = v.id AND v.status = :visit_status AND v.id_organization = :id_organization',
                    [
                        'visit_status' => VisitStatus::FINISHED,
                        'id_organization' => $this->id_organization,
                    ]
                )
                ->where([
                    'id_service' => ArrayHelper::getColumn($services, 'id'),
                ])
                ->orderBy([
                    'created_at' => SORT_ASC,
                ]);

            if (!empty($ids)) {
                // собираем данные для отдельной записи журнала
                $query->andWhere(['in', 'vgs.id', $ids]);
            }
        }

        // фильтры
        if (!empty($this->filter['date_from'])) {
            $query->andWhere(['>=', 'v.fact_start_dttm', $this->filter['date_from']]);
        }
        if (!empty($this->filter['date_to'])) {
            $query->andWhere(['<=', 'v.fact_end_dttm', $this->filter['date_to']]);
        }

        if (!empty($this->filter['specialist_fio'])) {
            $specialist_fio = $fullTrimValidator->validateValue($this->filter['specialist_fio']);
            $query->innerJoin(VisitsSpecialists::tableName() . ' vs', 'vs.id_visit = v.id')
                ->innerJoin(Specialists::tableName() . ' s', 's.id = vs.id_specialist')
                ->innerJoin(Users::tableName() . ' u', 'u.id = s.id_user')
                ->andWhere(['ilike', 'u.fullname', $specialist_fio]);
        }

        if (!empty($this->filter['owner_name'])) {
            $query->innerJoin(PetOwners::tableName() . ' po', 'po.id = v.id_owner')
                ->andWhere([
                    'or',
                    ['ilike', 'po.fullname', $this->filter['owner_name']],
                    ['ilike', 'po.jur_name', $this->filter['owner_name']],
                ]);
        }

        if (!empty($this->filter['pet_name'])) {
            $query->innerJoin(Pets::tableName() . ' p', 'p.id = v.id_pet')
                ->andWhere(['ilike', 'p.name', $this->filter['pet_name']]);
        }
        // вакцина -----------------------------------------------
        if (!empty($filter['vaccines_ids'])) {
            $query->innerJoin(VisitServiceTmc::tableName() . ' vst', 'vst.id_visit = v.id_visit');
            $query->innerJoin(TmcBase::tableName() . ' tmc', 'tmc.id = vst.id_tmc');
            $query->andWhere(['tmc.id' => $filter['vaccines_ids']]);
        }

        if (!empty($filter['specialist_ids'])) {
            $query->innerJoin(VisitsSpecialists::tableName() . ' vs', 'vs.id_visit = v.id')
                ->innerJoin(Specialists::tableName() . ' s', 's.id = vs.id_specialist')
                ->innerJoin(Users::tableName() . ' u', 'u.id = s.id_user')
                 ->andWhere(['s.id' => $filter['specialist_ids']]);
        }

        if (!empty($this->filter['id_ident_type']) || !empty($this->filter['identification_code'])) {
            $query->innerJoin(PetIdentification::tableName() . ' pi', 'pi.id_pet = v.id_pet');
            if (!empty($this->filter['id_ident_type'])) {
                $query->andWhere(['=', 'pi.id_ident_type', $this->filter['id_ident_type']]);
            }
            if (!empty($this->filter['identification_code'])) {
                $query->andWhere(['ilike', 'pi.identification_code', $this->filter['identification_code']]);
            }
        }

        $this->totalCount = $query->count();

        if ($this->totalCount == 0) {
            return [];
        }

        $this->page = $this->page ?? 1;
        $this->limit = $this->limit ?? 20;
        $this->pagesCount = (int)ceil($this->totalCount / $this->limit);
        $offset = ($this->page - 1) * $this->limit;

        $params = Params::find()
            ->orderBy(['id' => SORT_ASC])
            ->asArray()
            ->indexBy('tech_name')
            ->all();

        $techNamesMap = ArrayHelper::map($params, 'id', 'tech_name');

        $headerData = ArrayHelper::getValue($meta, 'headers', []);
        $headerProps = array_filter(ArrayHelper::getColumn($headerData, 'prop_path'));

        if ($perVisit === true) {
            $visits = $query
                ->limit($this->limit)
                ->offset($offset)
                ->indexBy('id')
                ->asArray()
                ->all();

            $visitIds = array_keys($visits);

            foreach ($visits as $id => $fields) {
                $visits[$id]['data'] = array_fill_keys($headerProps, null);
                $visits[$id]['data']['ids'] = [$id];
            }
            $results = $visits;

            if ($isGrouped === true) {
                $visitServices = VisitsGovServices::find()
                    ->alias('vgs')
                    ->where([
                        'and',
                        ['id_service' => ArrayHelper::getColumn($services, 'id')],
                        ['in', 'id_visit', $visitIds],
                    ])
                    ->orderBy([
                        'created_at' => SORT_ASC,
                    ])
                    ->indexBy('id')
                    ->asArray()
                    ->all();

                $visitVisitServicesMap = ArrayHelper::map($visitServices, 'id', 'id_visit');
            }

        } else {
            $visitServices = $query
                ->limit($this->limit)
                ->offset($offset)
                ->indexBy('id')
                ->asArray()
                ->all();

            $visitServicesVisitMap = ArrayHelper::map($visitServices, 'id', 'id', 'id_visit');
            $visitIds = array_keys($visitServicesVisitMap);

            foreach ($visitServices as $id => $fields) {
                $visitServices[$id]['data'] = array_fill_keys($headerProps, null);
                $visitServices[$id]['data']['ids'] = [$id];
            }
            $results = $visitServices;
        }

        foreach ($headerData as $columnData) {
            $paramName = $columnData['prop_path'];
            if ($paramName === null || $paramName == 'NPP') {
                // пустая колонка (например "Подпись врача")
                // или '№ п/п'
                continue;
            }

            $paramQuery = null;
            $paramType = ArrayHelper::getValue($columnData, 'param_type');

            if (!in_array($paramType, ['visit_service_param_value', 'visit_param_value'])) {
                // данные, которые получаем не из params
                $paramValues = $this->findNonParamValues($paramType, $paramName, $visitIds);
                if ($paramValues === false) {
                    // не определили, что же все-таки это за тип данных
                    continue;
                }
            }

            if ($paramType == 'visit_service_param_value') {
                if (!empty($visitServices)) {
                    $paramQuery = VisitServiceParamValues::find()
                        ->where(['in', 'id_visitservice', array_keys($visitServices)]);
                }
            } elseif ($paramType == 'visit_param_value') {
                $paramQuery = VisitParamValues::find()
                    ->where(['in', 'id_visit', $visitIds]);
            }

            $multiple = false;
            if ($paramQuery !== null) {
                if (isset($columnData['params'])) {
                    // сводная колонка, в которой содержится несколько параметров
                    $multiple = true;
                    $params_ids = [];
                    foreach ($columnData['params'] as $tech_name) {
                        if (array_key_exists($tech_name, $params)) {
                            $params_ids[] = $params[$tech_name]['id'];
                        }
                    }
                    if (empty($params_ids)) {
                        continue;
                    }
                    $paramQuery->andWhere(['in', 'id_param', $params_ids]);
                } else {
                    // одиночный параметр
                    $tech_name = $columnData['prop_path'];
                    if (!array_key_exists($tech_name, $params)) {
                        continue;
                    }
                    $id_param = $params[$tech_name]['id'];
                    $paramQuery->andWhere(['id_param' => $id_param]);
                }

                $paramValues = $paramQuery
                    ->asArray()
                    ->all();
            }

            if (empty($paramValues)) {
                // пустые данные - нечего заполнять
                continue;
            }

            foreach ($paramValues as $paramValue) {
                if ($paramType == 'visit_service_param_value') {
                    // VisitServiceParamValues
                    if ($isGrouped === false) {
                        $visitserviceId = ArrayHelper::getValue($paramValue, 'id_visitservice');
                        if (empty($visitserviceId) || !array_key_exists($paramName, $results[$visitserviceId]['data'])) {
                            continue;
                        }
                        if ($multiple === true) {
                            $techName = $techNamesMap[$paramValue['id_param']];
                            $datatype = ArrayHelper::getValue($params, $techName . '.datatype');
                            if (empty($datatype)) {
                                continue;
                            }
                            $field = ArrayHelper::getValue($params, $techName, []);
                            $value = $this->prepareParamValue($paramName, $paramValue, $datatype, $paramType, $field);
                            if (!empty($value)) {
                                // каким образом возвращать несколько значений в одну колонку не согласовано
                                // Леонид сказал, что они пишут все подряд что в голову придет
                                // похоже, что этого все равно не будет
                                $recordValue = $results[$visitserviceId]['data'][$paramName];
                                $recordValue .= ($recordValue === null) ? $value : ("\n" . $value);
                                $results[$visitserviceId]['data'][$paramName] = $recordValue;
                            }
                        } else {
                            $datatype = ArrayHelper::getValue($params, $paramName . '.datatype');
                            if (empty($datatype)) {
                                continue;
                            }
                            $field = ArrayHelper::getValue($params, $paramName, []);
                            $results[$visitserviceId]['data'][$paramName] = $this->prepareParamValue($paramName, $paramValue, $datatype, $paramType, $field);
                        }
                    } else {
                        $idVisit = ArrayHelper::getValue($visitVisitServicesMap, $paramValue['id_visitservice']);
                        if (empty($idVisit) || !array_key_exists($idVisit, $results)) {
                            continue;
                        }
                        if (!array_key_exists($paramName, $results[$idVisit]['data'])) {
                            continue;
                        }
                        $datatype = ArrayHelper::getValue($params, $paramName . '.datatype');
                        if (empty($datatype)) {
                            continue;
                        }
                        $field = ArrayHelper::getValue($params, $paramName, []);
                        $results[$idVisit]['data'][$paramName] = $this->prepareParamValue($paramName, $paramValue, $datatype, $paramType, $field, $idVisit);
                    }

                } elseif ($paramType == 'visit_param_value') {
                    // VisitParamValues
                    $idVisit = $paramValue['id_visit'];
                    if ($perVisit === true) {
                        if (!isset($results[$idVisit])) {
                            continue;
                        }
                        if (!array_key_exists($paramName, $results[$idVisit]['data'])) {
                            continue;
                        }
                        $datatype = ArrayHelper::getValue($params, $paramName . '.datatype');
                        if (empty($datatype)) {
                            continue;
                        }
                        $field = ArrayHelper::getValue($params, $paramName, []);
                        $results[$idVisit]['data'][$paramName] = $this->prepareParamValue($paramName, $paramValue, $datatype, $paramType, $field, $idVisit);
                    } else {
                        if (!isset($visitServicesVisitMap[$idVisit])) {
                            continue;
                        }
                        $visitserviceIds = array_keys($visitServicesVisitMap[$idVisit]);
                        foreach ($visitserviceIds as $visitserviceId) {
                            if (!array_key_exists($paramName, $results[$visitserviceId]['data'])) {
                                continue;
                            }
                            $datatype = ArrayHelper::getValue($params, $paramName . '.datatype');
                            if (empty($datatype)) {
                                continue;
                            }
                            $field = ArrayHelper::getValue($params, $paramName, []);
                            $results[$visitserviceId]['data'][$paramName] = $this->prepareParamValue($paramName, $paramValue, $datatype, $paramType, $field, $idVisit);
                        }
                    }
                } else {
                    // данные, которые получаем не из params
                    $idVisit = $paramValue['id_visit'];
                    if ($perVisit === true) {
                        if (!isset($results[$idVisit])) {
                            continue;
                        }
                        if (!array_key_exists($paramName, $results[$idVisit]['data'])) {
                            continue;
                        }
                        $results[$idVisit]['data'][$paramName] = $paramValue['value'];
                    } else {
                        if (!isset($visitServicesVisitMap[$idVisit])) {
                            continue;
                        }
                        $visitserviceIds = array_keys($visitServicesVisitMap[$idVisit]);
                        foreach ($visitserviceIds as $visitserviceId) {
                            if (!array_key_exists($paramName, $results[$visitserviceId]['data'])) {
                                continue;
                            }
                            $results[$visitserviceId]['data'][$paramName] = $paramValue['value'];
                        }
                    }
                }
            }
        }

        $records = ArrayHelper::getColumn($results, 'data', false);

        foreach ($records as $i => &$record) {
            // псевдо-номер по порядку (N п/п)
            $record['NPP'] = ($i + 1) + $offset;

            if (!empty($record['P15_Vacexpirationdate'])) {
                // нужно возвращать без времени, только дату срока годности
                try {
                    $vacexpirationdate = \DateTime::createFromFormat('d.m.Y H:i:s', $record['P15_Vacexpirationdate'])->format('d.m.Y');
                } catch (\Throwable $e) {
                    $vacexpirationdate = false;
                }
                $record['P15_Vacexpirationdate'] = ($vacexpirationdate === false) ? null : $vacexpirationdate;
            }
        }

        return $records;
    }

    /**
     * @param string $paramName
     * @param array  $paramValue
     * @param string $datatype
     * @param string $paramType
     * @param array  $field
     * @param int    $idVisit
     * @return mixed
     */
    private function prepareParamValue($paramName, $paramValue, $datatype, $paramType, $field, $idVisit = null)
    {
        $value = $this->extractExistingValue($paramValue, $datatype, $paramType);

        if (empty($value)) {
            return $value;
        }

        if ($paramName == 'P8_Petsex') {
            if ($value == 'm') {
                return 'мужской';
            } elseif ($value == 'f') {
                return 'женский';
            }
        }

        switch ($datatype) {
            case ParamsTrait::$dttmDatatype:
                if ($paramName == 'P10_Petbirthday' && $idVisit !== null) {
                    $visit = Visits::findOne(['id' => $idVisit]);
                    return DateHelper::ageAtDate(date('Y-m-d', $value), $visit->fact_start_dttm ?? $visit->fact_end_dttm);
                }
                try {
                    $value = date('d.m.Y H:i:s', $value);
                } catch (\Throwable $e) {
                    $value = false;
                }
                return ($value === false) ? null : $value;
            case ParamsTrait::$dictDatatype:
                try {
                    $config = $this->prepareDictionaryConfig($field);
                    $value = $this->findDictionaryValue($config['property'], $value);
                } catch (\Throwable $e) {
                    $value = null;
                }
                return empty($value) ? null : $value;
            default:
                return $value;
        }
    }

    /**
     * @return array
     */
    public function formatMeta()
    {
        return self::prepareMeta($this->model, true);
    }

    /**
     * @param array $filter
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
        $this->prepareFilter();
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @param int $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * @return int
     */
    public function getPagesCount()
    {
        return $this->pagesCount;
    }

    /**
     * Планируется конструктор для конфигурации журналов, описания нет.
     * Пока что захардкодим конфигурацию одного журнала, чтобы фронт мог работать.
     *
     * @param \app\models\db\Reports $model Модель журнала
     * @param bool                   $formatted
     * @return array
     */
    public static function prepareMeta($model, $formatted = false)
    {
        $meta = [
            'headers' => self::headersConfig($model->id),
        ];

        if ($formatted === true) {
            $headers = $meta['headers'];
            if (!empty($headers)) {
                foreach ($headers as $i => $header) {
                    foreach (['param_type', 'params'] as $field) {
                        unset($headers[$i][$field]);
                    }
                }
                $meta['headers'] = $headers;
            }
        }

        return $meta;
    }

    /**
     * @param int $reportId
     * @return void
     */
    private static function headersConfig($reportId)
    {
        $path = __DIR__ . '/configs/' . $reportId . '.php';

        return is_file($path) ? require $path : [];
    }

    /**
     * @return void
     */
    private function prepareFilter()
    {
        if (!isset($this->filter)) {
            $this->filter = null;

            return;
        }

        foreach ($this->filter as $key => $value) {
            $value = trim($value);
            if (empty($value)) {
                unset($this->filter[$key]);
            } else {
                $this->filter[$key] = $value;
            }
        }

        foreach (['date_from', 'date_to'] as $key) {
            if (!empty($this->filter[$key])) {
                $value = $this->convertDate($this->filter[$key], $key);
                if ($value === false) {
                    unset($this->filter[$key]);
                } else {
                    $this->filter[$key] = $value;
                }
            }
        }

        if (!empty($this->filter['id_ident_type'])) {
            $this->filter['id_ident_type'] = (int)$this->filter['id_ident_type'];
        }
    }

    /**
     * @param string $date
     * @param string $type
     * @return bool|string
     */
    private function convertDate($date, $type)
    {
        try {
            $dateTime = date_create_from_format('d.m.Y', $date);
            if ($dateTime === false) {
                return false;
            }
            $newDate = $dateTime->format('Y-m-d');
            if ($newDate === false) {
                return false;
            }
            $newDate .= ($type == 'date_to') ? ' 23:59:59' : ' 00:00:00';

            return $newDate;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param string $paramType
     * @param string $paramName
     * @param array  $visitIds
     * @return array|false
     */
    private function findNonParamValues($paramType, $paramName, $visitIds)
    {
        $query = new Query();

        switch ($paramType) {
            case 'visit_description':
                // см. 21, 25, 26
                $name = self::visitDescriptionName($paramName);
                if ($name === null) {
                    return false;
                }
                $query->select('vd.id_visit, vd.description AS value')
                    ->from(VisitDescriptions::tableName() . ' vd')
                    ->leftJoin(DescriptionTypes::tableName() . ' dt', 'vd.id_description_type = dt.id')
                    ->where(['in', 'vd.id_visit', $visitIds])
                    ->andWhere(['=', 'dt.entity_type', 'visit'])
                    ->andWhere(['=', 'dt.name', $name]);
                break;
            case 'visit_price_bill_num':
                // см. 25, 26
                $query->select('vp.id_visit, vp.bill_num AS value')
                    ->from(VisitPrice::tableName() . ' vp')
                    ->where(['in', 'vp.id_visit', $visitIds]);
                break;
            case 'visit_price_price_with_discount':
                // см. 25, 26
                $query->select('vp.id_visit, vp.price_with_discount AS value')
                    ->from(VisitPrice::tableName() . ' vp')
                    ->where(['in', 'vp.id_visit', $visitIds]);
                break;
            case 'visit_services_with_discount':
                // см. 25
                // Вывести список кодов услуг, их количество и стоимость этих услуг в разрезе услуги (кода услуги).
                // <>_<'*'_visits_gov_services>'='<стоимость с учетом скидки>
                $query->select('vgs.id, vgs.id_visit, vgs.id_service, vgs.count, vgs.price_with_discount, gs.cod')
                    ->from(VisitsGovServices::tableName() . ' vgs')
                    ->leftJoin(GovServices::tableName() . ' gs', 'vgs.id_service = gs.id')
                    ->where(['in', 'vgs.id_visit', $visitIds])
                    ->orderBy(['vgs.id_visit' => SORT_ASC]);
                $rows = $query->all();
                $values = [];
                foreach ($rows as $row) {
                    if (!array_key_exists($row['id_visit'], $values)) {
                        $values[$row['id_visit']] = [
                            'id_visit' => $row['id_visit'],
                            'values' => [
                                $row['id'] => ($row['cod'] . ($row['count'] > 1 ? ('*' . $row['count']) : '') . ' = ' . $row['count'] * $row['price_with_discount']),
                            ],
                        ];
                    } else {
                        $values[$row['id_visit']]['values'][$row['id']] = ($row['cod'] . ($row['count'] > 1 ? ('*' . $row['count']) : '') . ' = ' . $row['count'] * $row['price_with_discount']);
                    }
                }
                foreach ($values as $id_visit => $serviceData) {
                    $values[$id_visit]['value'] = implode("<br>", array_values($serviceData['values']));
                }

                return array_values($values);
            case 'visit_price_discount_name':
                // см. 26
                $query->select('vp.id_visit, d.name AS value')
                    ->from(VisitPrice::tableName() . ' vp')
                    ->leftJoin(Discount::tableName() . ' d', 'vp.id_discount = d.id')
                    ->where(['in', 'vp.id_visit', $visitIds]);
                break;
            case 'visit_service_tmc':
                // см. 25
                // Вывести список вне балансовых ТМЦ, использованных в приеме, в следующем виде:
                // <visit_service_tmc.name>_<visit_service_tmc.measure_name>, ...., n.
                // отобрать ТМЦ, где visit_service_tmc.entity_type = 'drugs' or 'vaccines'
                if (!in_array($paramName, ['owner_drugs_list', 'P0_Vaccinename', 'P0_VisitServiceTMCcount'], true)) {
                    return false;
                }
                $query->select([
                    'id_visit',
                    'name',
                    'measure_name',
                    'count',
                ])
                    ->from(VisitServiceTmc::tableName())
                    ->where(['in', 'id_visit', $visitIds]);
                if ($paramName == 'owner_drugs_list') {
                    $query->andWhere(['in', 'entity_type', [VisitServiceTmc::TYPE_VACCINE, VisitServiceTmc::TYPE_DRUG]]);
                } else {
                    $query->andWhere(['in', 'entity_type', [VisitServiceTmc::TYPE_VACCINE, VisitServiceTmc::TYPE_BALANCE_VACCINES]]);
                }
                $rows = $query->all();
                $values = [];
                foreach ($rows as $row) {
                    if ($paramName == 'owner_drugs_list') {
                        $value = $row['name'] . ' ' . $row['measure_name'] . ' ' . $row['count'];
                    } elseif ($paramName == 'P0_Vaccinename') {
                        $value = $row['name'];
                    } else {
                        // P0_VisitServiceTMCcount
                        $value = $row['measure_name'] . ' ' . $row['count'];
                    }

                    if (!array_key_exists($row['id_visit'], $values)) {
                        $values[$row['id_visit']] = [
                            'id_visit' => $row['id_visit'],
                            'value' => $value,
                        ];
                    } else {
                        $values[$row['id_visit']]['value'] = $values[$row['id_visit']]['value'] . '; ' . $value;
                    }
                }

                return array_values($values);
            case 'gov_services_com_class_journal':
                // см. 25, 26
                // Дополнительные исследования – вывести сокращенные наименования услуг (GovService.briefname),
                // для которых установлен классификатор additional. Если сокращенного наименования нет (GovService.briefname),
                // то вывести полное наименование (GovService.name)
                // Формат:<gov_services.briefname>
                // Лечебная помощь - вывести сокращенные наименования услуг(GovService.briefname),
                // для которых установлен классификатор medicalAssistance и использованную вакцину\препарат
                // (вывести все использованные вакцины, препараты). Если сокращенного наименования нет (GovService.briefname),
                // то вывести полное наименование (GovService.name)
                // Формат:<gov_services.briefname>_-_<visit_service_tmc.name>_</_visit_service_tmc.name>/..n
                if (!in_array($paramName, ['additional', 'medicalAssistance'], true)) {
                    return false;
                }
                $query->select('vgs.id, vgs.id_visit, vgs.id_service, gs.name, gs.briefname')
                    ->from(VisitsGovServices::tableName() . ' vgs')
                    ->leftJoin(GovServices::tableName() . ' gs', 'vgs.id_service = gs.id')
                    ->where(['in', 'vgs.id_visit', $visitIds])
                    ->andWhere(['=', 'gs.com_class_journal', $paramName])
                    ->orderBy(['vgs.id_visit' => SORT_ASC]);
                $rows = $query->all();
                $values = [];
                $visits_gov_service_ids = [];
                foreach ($rows as $row) {
                    $visits_gov_service_ids[] = $row['id'];
                    if (!array_key_exists($row['id_visit'], $values)) {
                        $values[$row['id_visit']] = [
                            'id_visit' => $row['id_visit'],
                            'values' => [
                                $row['id'] => (empty($row['briefname']) ? $row['name'] : $row['briefname']),
                            ],
                        ];
                    } else {
                        $values[$row['id_visit']]['values'][$row['id']] = (empty($row['briefname']) ? $row['name'] : $row['briefname']);
                    }
                }
                if ($paramName == 'medicalAssistance' && !empty($values)) {
                    $drugRows = (new Query())
                        ->select('vst.id, vst.id_visits_gov_service, vst.id_entity, vst.name')
                        ->from(VisitServiceTmc::tableName() . ' vst')
                        ->where(['in', 'vst.entity_type', [VisitServiceTmc::TYPE_BALANCE_DRUGS, VisitServiceTmc::TYPE_BALANCE_VACCINES]])
                        ->andWhere(['in', 'vst.id_visits_gov_service', $visits_gov_service_ids])
                        ->all();
                    $drugs = ArrayHelper::index($drugRows, 'id', 'id_visits_gov_service');
                    if (!empty($drugs)) {
                        foreach ($values as $id_visit => $serviceData) {
                            foreach ($serviceData['values'] as $id_visitservice => $serviceName) {
                                if (array_key_exists($id_visitservice, $drugs)) {
                                    $values[$id_visit]['values'][$id_visitservice] = $serviceName . ' - ' . implode(' / ', ArrayHelper::getColumn(array_values($drugs[$id_visitservice]), 'name'));
                                }
                            }
                        }
                    }
                }
                foreach ($values as $id_visit => $serviceData) {
                    $values[$id_visit]['value'] = implode("<br>", array_values($serviceData['values']));
                }

                return array_values($values);
            case 'visit_created_at':
            case 'visit_fact_start_dttm':
            case 'visit_fact_end_dttm':
                // см. 26
                $query->select('v.id AS id_visit, v.' . $paramName . ' AS value')
                    ->from(Visits::tableName() . ' v')
                    ->where(['in', 'v.id', $visitIds]);
                break;
            case 'visit_services_list':
                // см. 26
                // Вывести список услуг связанных с данных приемом. В качестве разделителя используется символ ';'
                $query->select('vgs.id_visit, vgs.id_service, gs.name')
                    ->from(VisitsGovServices::tableName() . ' vgs')
                    ->leftJoin(GovServices::tableName() . ' gs', 'vgs.id_service = gs.id')
                    ->where(['in', 'vgs.id_visit', $visitIds])
                    ->orderBy(['vgs.id_visit' => SORT_ASC]);
                $rows = $query->all();
                $values = [];
                foreach ($rows as $row) {
                    if (!array_key_exists($row['id_visit'], $values)) {
                        $values[$row['id_visit']] = [
                            'id_visit' => $row['id_visit'],
                            'value' => $row['name'],
                        ];
                    } else {
                        $values[$row['id_visit']]['value'] = $values[$row['id_visit']]['value'] . '; ' . $row['name'];
                    }
                }

                return array_values($values);
            default:
                return false;
        }

        return $query->all();
    }

    /**
     * @param string $key
     * @return string
     */
    private static function visitDescriptionName($key)
    {
        $map = [
            'visit_description_anamnesis' => 'Анамнез',
            'visit_description_conclusion' => 'Заключение',
            'visit_description_date_disease' => 'Дата заболевания',
            'visit_description_diagnosis' => 'Диагноз',
            'visit_description_pre_diagnosis' => 'Предварительный диагноз',
            'visit_description_final_diagnosis' => 'Заключительный диагноз',
            'visit_description_clinical_signs' => 'Клинические признаки',
            'visit_description_add_research' => 'Дополнительные исследования',
            'visit_description_medical_aid' => 'Лечебная помощь',
            'visit_description_recommendations' => 'Рекомендации',
        ];

        return ArrayHelper::getValue($map, $key);
    }

    /**
     * Журнал формируется в разрезе приемов в целом
     * (на данный момент это
     * 'Журнал регистрации платных ветеринарных услуг животным',
     * 'Журнал по оказанию ветеринарных услуг бригадами неотложной ветеринарной помощи')
     *
     * @return bool
     */
    private function isPerVisitJournal()
    {
        return in_array($this->model->id, [25, 26, 22, 24]);
    }

    /**
     * В журнале группируются услуги приема
     * (на данный момент это
     * 'Журнал биохимического исследования крови',
     * 'Журнал гематологических исследований')
     *
     * @return bool
     */
    private function isGroupedServicesJournal()
    {
        return in_array($this->model->id, [22, 24]);
    }

    /**
     * Будет дополнительное условие для выездных бригад, когда будет введен признак в прием
     * ('Журнал по оказанию ветеринарных услуг бригадами неотложной ветеринарной помощи')
     *
     * @return bool
     */
    private function isAmbulanceJournal()
    {
        return $this->model->id == 26;
    }

    /**
     * @param int $id_report
     * @return \app\models\db\GovServices[]
     */
    private function findServicesForJournal(int $id_report)
    {
        $services = GovServices::find()
            ->alias('gs')
            ->innerJoin(
                GovServicesReports::tableName() . ' gsr',
                'gsr.id_service = gs.id AND gsr.id_report = :id_report',
                ['id_report' => $id_report]
            )
            ->all();

        return $services;
    }

    /**
     * @param array $ids
     * @return false|string
     */
    public function createPdf(array $ids)
    {
        /* @var $generator \app\common\components\pdfGenerator\PdfGenerator */
        $generator = \Yii::$app->get('pdfGenerator');

        $records = $this->findRecords($ids);

        if (empty($records)) {
            return false;
        }

        $data = [
            'title' => $this->model->name,
            'meta' => $this->formatMeta(),
            'data' => $records,
        ];

        try {
            $path = $generator->createDocument('journals', $data);
        } catch (\Exception $e) {
            return false;
        }

        return \Yii::getAlias('@web') . '/upload/pdf/' . $path[1] . '.' . $path[2];
    }
}
