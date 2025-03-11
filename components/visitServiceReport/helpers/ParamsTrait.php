<?php

namespace app\common\components\visitServiceReport\helpers;

use app\models\db\PetIdentification;
use yii\base\DynamicModel;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\UnprocessableEntityHttpException;
use app\models\db\Dictionaries;
use app\models\db\GovServices;
use app\models\db\Params;
use app\models\db\Reports;
use app\models\db\VisitParamValues;
use app\models\db\VisitServiceParamValues;
use app\modules\v1\models\FileResource;

/**
 * Trait ParamsTrait
 *
 * @package app\modules\v1\models\reports
 */
trait ParamsTrait
{
    static $textDatatype = 'text';

    static $dttmDatatype = 'dttm';

    static $numericDatatype = 'numeric';

    static $dictDatatype = 'dict';

    static $complexDatatype = 'complex';

    static $fileDatatype = 'file';

    /**
     * @param array $attributes
     * @param array $field
     * @param string $type
     * @return string|int
     */
    protected function extractExistingValue($attributes, $field, $type)
    {
        $datatype = $field['datatype'];

        if ($datatype == self::$fileDatatype) {
            return empty($attributes['id']) ? [] : $this->prepareParamFileResources($attributes['id'], $type);
        }

        $column = $this->resolveColumnName($datatype);

        if ($column === null) {
            throw new InvalidArgumentException("Неизвестный тип данных {$datatype}");
        }

        $value = $attributes[$column];
        if ($datatype == self::$complexDatatype && !empty($value) && is_string($value)) {
            $value = Json::decode($value);
        }

        return $value;
    }

    /**
     * @param string $datatype
     * @return mixed
     */
    protected function resolveColumnName($datatype)
    {
        $types = [
            self::$textDatatype    => 'char_value',
            self::$dttmDatatype    => 'date_value',
            self::$numericDatatype => 'num_value',
            self::$dictDatatype    => 'dict_value',
            self::$complexDatatype => 'complex_value',
        ];

        return ArrayHelper::getValue($types, $datatype);
    }

    /**
     * @param array $data
     * @param array $field
     * @param string $type
     * @param bool $forPdf
     * @return array
     */
    protected function formatParamValue($data, $field, $type, $forPdf = false)
    {
        $datatype = $field['datatype'];
        if ($forPdf === true) {
            $value = empty($data) ? '' : $this->extractExistingValue($data, $field, $type);
            if (!empty($value)) {
                if ($datatype == self::$dictDatatype || $datatype == self::$complexDatatype) {
                    $config = $this->prepareDictionaryConfig($field);
                    $value = $this->findDictionaryValue($config['property'], $value);
                } elseif ($datatype == self::$dttmDatatype) {
                    $value = date('d.m.Y', $value);
                } elseif ($datatype == self::$textDatatype && !empty($field['datatype_details']) && $field['datatype_details'] > 255) {
                    $value = nl2br($value);
                }
            }

            $formatted = [
                'tech_name' => $field['tech_name'],
                'value'     => $value,
            ];
        } else {
            $formatted = [
                'type'       => $type,
                'id'         => ArrayHelper::getValue($data, 'id'),
                'attributes' => [
                    'id_param'         => $field['id'],
                    'tech_name'        => $field['tech_name'],
                    'name'             => $field['name'],
                    'value'            => empty($data) ? '' : $this->extractExistingValue($data, $field, $type),
                    'datatype'         => ($datatype == self::$complexDatatype ? self::$dictDatatype : $datatype),
                    'datatype_details' => $field['datatype_details'],
                    'multiple'         => ($field['datatype'] == self::$complexDatatype),
                    'config'           => $this->prepareParamConfig($field, $type),
                ],
            ];
        }

        return $formatted;
    }

    /**
     * @param int $id_visit
     * @return array
     */
    protected static function findVisitParamValues($idVisitService)
    {
        return (new Query())
            ->select('*')
            ->from(VisitParamValues::tableName())
            ->where(['[[id_visitservice]]' => $idVisitService])
            ->orderBy(['[[id_param]]' => SORT_ASC])
            ->all();
    }

    /**
     * @param int $id_service
     * @param string $reportName
     * @return array
     */
    protected static function findReportForService($id_service, $reportName = null)
    {
        if ($id_service instanceof GovServices) {
            $service = $id_service;
            $id_service = $service->id;
        } else {
            $service = GovServices::findOne(['id' => $id_service]);
        }
        if ($service === null) {
            throw new InvalidConfigException("Выбраная услуга #{$id_service} не найдена");
        }

        $q = new Query();
        $q->select('r.*')
            ->addSelect('[[gsr]].[[id_report]], [[gsr]].[[id_service]]')
            ->from('gov_services_reports gsr')
            ->leftJoin(Reports::tableName() . ' r', '[[gsr]].[[id_report]] = [[r]].[[id]]')
            ->where(['[[gsr]].[[id_service]]' => $id_service])
            ->andWhere(['[[r]].[[report_type]]' => 'R']);
        if (!empty($reportName)) {
            $q->andWhere(['[[r]].[[name]]' => $reportName]);
        }

        $reports = $q->all();

        if (count($reports) == 0) {
            return null;
        } elseif (count($reports) == 1) {
            return $reports[0];
        } else {
            throw new InvalidConfigException("В параметрах задано более одного отчета для выбранной услуги #{$id_service}");
        }
    }

    /**
     * @param int $id_report
     * @param array $excludeServices
     * @return array
     */
    protected static function findServicesForReport($id_report, $excludeServices = [])
    {
        $q = new Query();
        $q->select('gs.*')
            ->from(GovServices::tableName() . ' gs')
            ->leftJoin('gov_services_reports gsr', '[[gsr]].[[id_service]] = [[gs]].[[id]]')
            ->where(['[[gsr]].[[id_report]]' => $id_report]);
        if (!empty($excludeServices)) {
            $q->andWhere(['not in', '[[gs]].[[id]]', $excludeServices]);
        }

        return $q->all();
    }

    /**
     * @param array $field
     * @param string $type
     * @return array
     */
    protected function prepareParamConfig($field, $type)
    {
        $config = empty($field['config']) ? [] : Json::decode($field['config']);

        if ($field['datatype'] == self::$dictDatatype || $field['datatype'] == self::$complexDatatype) {
            $config = array_merge($config, $this->prepareDictionaryConfig($field, true));
        }

        if ($type == 'visit_param_value') {
            $config['editable'] = false;
        }
        if ($type == 'visit_service_param_value') {
            $config['required'] = (isset($field['req_out']) && $field['req_out'] === true);
        }

        return $config;
    }

    /**
     * @param array $field
     * @param bool $includeOptions
     * @return array
     */
    private function prepareDictionaryConfig($field, $includeOptions = false)
    {
        $property = strtolower($field['datatype_details']);
        $attribute = ArrayHelper::getValue($field['config'], 'attribute', 'name');

        $config = [
            'property'  => $property,
            'attribute' => $attribute,
        ];

        if ($includeOptions === true) {
            $rows = Dictionaries::findByType($property);
            $options = [];
            foreach ($rows as $row) {
                $options[] = [
                    'id'   => $row['id'],
                    'name' => $row['name'],
                ];
            }
            $config['options'] = $options;
        }

        return $config;
    }

    /**
     * @param array $data
     * @param array $fields
     * @param string $type
     * @return array
     * @throws \yii\web\UnprocessableEntityHttpException
     */
    protected function prepareRow($data, $fields, $type = 'visit_service_param_value')
    {
        $field = ArrayHelper::getValue($fields, $data['id_param']);
        if ($field === null) {
            throw new UnprocessableEntityHttpException("Не определено поле #{$data['id_param']}");
        }

        if (!isset($data['value'])) return false;

        if (!$this->validateParam($data['value'], $field, 'req_out', $this->id_visitservice)) {
            return false;
        }

        $row = array_fill_keys($this->columns($type), null);
        $row['id_param'] = $data['id_param'];
        $row['id_visit_service_tmc'] = $data['id_visit_service_tmc'] ?? null;
        $row['id_pet'] = $data['id_pet'] ?? null;

        foreach (['id_visitservice', 'id_visit'] as $attr) {
            if (array_key_exists($attr, $row)) {
                $row[$attr] = $this->$attr;
            }
        }

        if ($data['value'] !== null && $data['value'] !== '') {
            $column = $this->resolveColumnName($field['datatype']);
            if ($column !== null && array_key_exists($column, $row)) {
                $row[$column] = $data['value'];
            }
        }

        return $row;
    }

    /**
     * @param mixed $value
     * @param array $field
     * @param string $reqDirection
     * @param int $id_visitservice
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function validateParam($value, $field, $reqDirection = 'req_in', $id_visitservice = null)
    {
        $rules = [];

        $fieldName = empty($field['name']) ? $field['tech_name'] : $field['name'];

        if (isset($field[$reqDirection]) && $field[$reqDirection] === true) {
            $required = ($reqDirection == 'req_in')
                || ($reqDirection == 'req_out'
                    && (!empty($id_visitservice) && $this->isRequiredOutParamReallyRequired($field, $id_visitservice)));

            if ($required === true) {
                $rules[] = [$field['tech_name'], 'required', 'message' => 'Не заполнен обязательный параметр "' . $fieldName . '"'];
            }
        }

        switch ($field['datatype']) {
            case self::$textDatatype:
                $max = empty($field['datatype_details']) ? 0 : (int)$field['datatype_details'];
                if ($max > 0) {
                    $rules[] = [
                        $field['tech_name'],
                        'string',
                        'max'     => $max,
                        'message' => 'Параметр "' . $fieldName . '" должен быть строкой',
                        'tooLong' => 'Параметр "' . $fieldName . '" должен содержать не более ' . $max . ' символов',
                    ];
                } else {
                    $rules[] = [$field['tech_name'], 'string', 'message' => 'Параметр "' . $fieldName . '" должен быть строкой'];
                }
                // дополнительная валидация для номера чипа (услуга "Электронное мечение животного (чипирование со сканированием)")
                if ($field['tech_name'] == 'P0_Petchpidentificationcode') {
                    $rules[] = [
                        $field['tech_name'],
                        'match',
                        'pattern' => PetIdentification::CHIP_REG_EXP,
                        'message' => 'Номер чипа должен содержать 15 чисел.',
                    ];
                }
                break;
            case self::$dttmDatatype:
                // TODO - возможно будут дополнительные ограничения (в диапазоне дат)
                $rules[] = [$field['tech_name'], 'integer', 'message' => 'Параметр "' . $fieldName . '" должен быть целым числом'];
                break;
            case self::$numericDatatype:
                // TODO - возможно будут дополнительные ограничения (min, max, precision и т.п.)
                $rules[] = [$field['tech_name'], 'number', 'message' => 'Параметр "' . $fieldName . '" должен быть числом'];
                break;
            case self::$dictDatatype:
                // здесь хранится ID значения в dictionaries (int)
                // проверяем также, содержится ли элемент в справочнике
                $rules[] = [$field['tech_name'], 'integer', 'message' => 'Параметр "' . $fieldName . '" должен быть целым числом'];
                $rules[] = [
                    $field['tech_name'],
                    'exist',
                    'targetClass'     => Dictionaries::class,
                    'targetAttribute' => 'id',
                    'filter'          => ['type' => $field['datatype_details']],
                    'message'         => 'Значение справочника для параметра "' . $fieldName . '" не существует',
                ];
                break;
            case self::$complexDatatype:
                // используем complex для справочников с мульти-селектом - будет передаваться как массив int
                // (на данный момент - kidneyechogenicity для УЗИ мочевыделительной системы)
                // TODO
                break;
            case self::$fileDatatype:
                // В самом значении параметра ничего не храним, просто записываем его для связи с files.
                // Теперь что касается files:
                // согласно confluence - "Для Param.datatype = `file` хранится идентификатор услуги приема (VisitService.id)"
                // (см. https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=91265804)
                // Но!
                // 1) по итогам созвона от 18.10.2018 - файлов может быть несколько!
                // 2) у нас уже хранится в files связанный с visits_gov_service файл отчета в pdf!
                // 3) потенциально могут появиться другие параметры, которые тоже будут иметь тип файл!
                // Поэтому в files мы будем связывать файл с сущностью visit_service_param_value !!!
                $rules[] = [$field['tech_name'], 'safe'];
                break;
            default:
                $this->addError($field['tech_name'], 'Неизвестный тип данных для параметра "' . $fieldName . '"');

                return false;
        }

        if (empty($rules)) {
            return true;
        }

        $model = DynamicModel::validateData([$field['tech_name'] => $value], $rules);

        if ($model->hasErrors()) {
            $this->addErrors($model->errors);

            return false;
        }

        return true;
    }

    /**
     * @param string $type
     * @param int|array $value_id
     * @param string $attribute
     * @return string|array
     */
    protected function findDictionaryValue($type, $value_id, $attribute = 'name')
    {
        if (is_array($value_id)) {
            foreach ($value_id as $key => $val) {
                $value_id[$key] = (int)$val;
            }
            $value_id = array_filter($value_id);
            $value_id = array_unique($value_id);
            $value_id = array_values($value_id);
        }

        if (empty($value_id)) {
            return '';
        }

        $q = new Query();
        $q->select($attribute)
            ->from(Dictionaries::tableName())
            ->where(['type' => $type]);
        if (is_array($value_id)) {
            $q->andWhere(['in', 'id', $value_id]);
        } else {
            $q->andWhere(['id' => (int)$value_id])
                ->limit(1);
        }

        try {
            if (is_array($value_id)) {
                $value = $q->column();
            } else {
                $value = $q->scalar();
            }
        } catch (\Throwable $e) {
            $value = '';
        }

        return empty($value) ? '' : $value;
    }

    /**
     * @return array
     */
    protected function typesClassMap()
    {
        return [
            'visit_param_value'         => VisitParamValues::class,
            'visit_service_param_value' => VisitServiceParamValues::class,
        ];
    }

    /**
     * @param bool $byClassName
     * @return array
     * @var string $type
     */
    protected function columns($type, $byClassName = false)
    {
        $className = ($byClassName === true) ? $type : ArrayHelper::getValue(static::typesClassMap(), $type);

        $columns = [
            VisitParamValues::class        => [
                'id_param',
                'num_value',
                'char_value',
                'date_value',
                'dict_value',
                'id_visit',
                'id_visitservice',
                'id_visit_service_tmc',
                'id_pet',
            ],
            VisitServiceParamValues::class => [
                'id_param',
                'num_value',
                'char_value',
                'date_value',
                'dict_value',
                'complex_value',
                'id_visit',
                'id_visitservice',
                'id_visit_service_tmc',
                'id_pet',
            ],
        ];

        return ArrayHelper::getValue($columns, $className);
    }

    /**
     * @param int $id
     * @param string $type
     * @return array
     */
    protected function prepareParamFileResources($id, $type)
    {
        $output = [];

        $files = $this->findFileResources($id, $type);
        foreach ($files as $file) {
            $output[] = [
                'id'         => $file->id,
                'type'       => 'file',
                'attributes' => [
                    'path'        => $file->path,
                    'name'        => $file->name,
                    'created'     => $file->created,
                    'entity_id'   => $file->entity_id,
                    'entity_type' => $file->entity_type,
                ],
            ];
        }

        return $output;
    }

    /**
     * @param int $entity_id
     * @param string $type
     * @return \app\modules\v1\models\FileResource[]
     */
    protected function findFileResources($entity_id, $type)
    {
        return FileResource::find()
            ->where([
                'entity_id'   => $entity_id,
                'entity_type' => $type,
            ])
            ->all();
    }

    /**
     * @param int|array $entity_id
     * @param string $type
     * @return int
     */
    protected function deleteFileResources($entity_id, $type)
    {
        return FileResource::deleteAll([
            'entity_id'   => $entity_id,
            'entity_type' => $type,
        ]);
    }

    /**
     * Возвращает список выходящих (?) параметров для услуги
     * 
     * @param int $id_service
     * @param int $id_report
     * @return array
     */
    protected function findReportParamsForService($id_service, $id_report = null)
    {
        $q = new Query();
        $q
            ->select('p.*')
            ->addSelect([
                '[[sp]].[[id_param]]',
                '[[sp]].[[id_service]]',
                '[[sp]].[[flag_in]]',
                '[[sp]].[[flag_out]]',
                '[[sp]].[[req_in]]',
                '[[sp]].[[req_out]]'
            ])
            ->from(Params::tableName() . ' p')
            ->leftJoin('gov_services_params sp', '[[sp]].[[id_param]] = [[p]].[[id]]')
            ->where(['[[sp]].[[id_service]]' => $id_service])
            ->andWhere(['[[sp]].[[flag_out]]' => true]);

        if (!empty($id_report)) {
            if (is_array($id_report)) {
                $q->addSelect('[[rp]].[[id_report]]');
                $q->leftJoin('reports_params rp', '[[rp]].[[id_param]] = [[sp]].[[id_param]]');
                $q->andWhere(['[[rp]].[[id_report]]' => $id_report]);
            } else {
                $q->addSelect('[[sr]].[[id_report]]');
                $q->leftJoin('gov_services_reports sr', '[[sr]].[[id_service]] = [[sp]].[[id_service]]');
                $q->andWhere(['[[sr]].[[id_report]]' => $id_report]);
            }
        }

        $orderBy = [];
        if (!empty($id_report) && is_array($id_report)) {
            $orderBy = ['[[rp]].[[id_report]]' => SORT_ASC];
        }

        $orderBy = array_merge(
            $orderBy,
            [
                '[[sp]].[[sort_by]]' => SORT_ASC,
                '[[p]].[[id]]'       => SORT_ASC,
            ]
        );

        $q->orderBy($orderBy)
            ->indexBy('id');

        return $q->all();
    }

    /**
     * Возвращает список обязательных входящих (?) параметров услуги
     * 
     * @param int $id_service
     * @return array
     */
    protected function findReqInParamsForService($id_service)
    {
        $q = new Query();
        $q->select('p.*')
            ->addSelect('[[sp]].[[id_param]], [[sp]].[[id_service]], [[sp]].[[req_in]], [[sp]].[[req_out]]')
            ->from(Params::tableName() . ' p')
            ->leftJoin('gov_services_params sp', '[[sp]].[[id_param]] = [[p]].[[id]]')
            ->where(['[[sp]].[[id_service]]' => $id_service])
            ->andWhere([
                '[[sp]].[[flag_in]]' => true,
                '[[sp]].[[req_in]]'  => true
            ]);

        $q->orderBy([
            '[[sp]].[[sort_by]]' => SORT_ASC,
            '[[p]].[[id]]'       => SORT_ASC,
        ])
            ->indexBy('tech_name');

        return $q->all();
    }

    /**
     * Фронт может вообще не передать обязательных параметров в запросе - в результате это будет упущено при валидации параметров
     *
     * @param array $post
     * @param array $fields
     * @param int $id_visitservice
     * @param bool $addError
     * @param bool $returnFieldNames
     * @return array|void
     */
    protected function validateNotPassedRequiredParams($models, $fields, $id_visitservice, $addError = true, $returnFieldNames = false)
    {
        if (!$fields) {
            return;
        }

        $modelsMapped = ArrayHelper::map($models, 'id_param', 'value');
        $fieldsMapped = ArrayHelper::map($fields, 'id_param', 'tech_name');
        $fieldNamesMapped = ArrayHelper::map($fields, 'id_param', 'name');
        $fieldNames = [];

        foreach ($fields as $id_param => $field) {
            if (!empty($field['config'])) {
                // временное решение для условно-обязательных взаимосвязанных параметров (для ВСД)
                try {
                    $config = is_array($field['config']) ? $field['config'] : Json::decode($field['config']);
                } catch (\Throwable $e) {
                    $config = [];
                }
                $requiredFor = ArrayHelper::getValue($config, 'req_out.for', []);
                if (!empty($requiredFor)) {
                    $paramValue = ArrayHelper::getValue($modelsMapped, $id_param);
                    $fieldName = empty($field['name']) ? $field['tech_name'] : $field['name'];
                    $hasError = false;
                    foreach ($requiredFor as $relatedTechName) {
                        $relatedId = array_search($relatedTechName, $fieldsMapped, true);
                        if ($relatedId === false) {
                            continue;
                        }
                        $relatedValue = ArrayHelper::getValue($modelsMapped, $relatedId);
                        $relatedFieldname = ArrayHelper::getValue($fieldNamesMapped, $relatedId, $relatedTechName);
                        if (empty($paramValue) && !empty($relatedValue)) {
                            $hasError = true;
                            if ($addError === true) {
                                $this->addError($field['tech_name'], 'Не заполнен обязательный параметр "' . $fieldName . '"');
                            }
                            break;
                        } elseif (!empty($paramValue) && empty($relatedValue)) {
                            $hasError = true;
                            if ($addError === true) {
                                $this->addError($field['tech_name'], 'Для заполнения параметра "' . $fieldName . '" необходимо указать "' . $relatedFieldname . '"');
                            }
                            break;
                        }
                    }
                    if ($hasError === true) {
                        continue;
                    }
                }
            }

            if (array_key_exists($id_param, $modelsMapped)) {
                // обязательность параметра, который был передан в запросе, будет проверяться при валидации соответствующего параметра
                continue;
            }

            if (isset($field['req_out']) && $field['req_out'] === true) {
                if ($this->isRequiredOutParamReallyRequired($field, $id_visitservice)) {
                    $fieldName = empty($field['name']) ? $field['tech_name'] : $field['name'];
                    if ($addError === true) {
                        $this->addError($field['tech_name'], 'Не заполнен обязательный параметр "' . $fieldName . '"');
                    }
                    if ($returnFieldNames === true) {
                        $fieldNames[] = $fieldName;
                    }
                }
            }
        }

        if ($returnFieldNames === true) {
            return $fieldNames;
        }
    }

    /**
     * Фикс для исходящих параметров для которых req_out === true,
     * но обязательность которых зависит от значений входящих параметров (например, для УЗИ)
     *
     * @param array $field
     * @param int $id_visitservice
     * @return bool
     */
    protected function isRequiredOutParamReallyRequired($field, $id_visitservice)
    {
        $config = empty($field['config'])
            ? []
            : (is_array($field['config']) ? $field['config'] : Json::decode($field['config']));

        if (empty($config) || !is_array($config)) {
            return true;
        }

        foreach ($config as $confData) {
            if (!isset($confData['req_in'])) {
                continue;
            }
            $reqInParam = Params::findOne([
                'tech_name' => $confData['req_in']['tech_name'],
            ]);
            if ($reqInParam === null) {
                continue;
            }
            $inputParam = VisitServiceParamValues::findOne([
                'id_visitservice' => $id_visitservice,
                'id_param'        => $reqInParam['id'],
            ]);
            if ($inputParam === null) {
                continue;
            }

            $columnName = self::resolveColumnName($reqInParam['datatype']);
            if ($columnName === null) {
                throw new InvalidConfigException('Неизвестный тип данных ' . $reqInParam['datatype'] . ' для параметра "' . $reqInParam['name'] . '"');
            }

            if ($reqInParam['datatype'] == self::$dictDatatype && !empty($reqInParam['datatype_details'])) {
                $reqInParamValue = Dictionaries::find()
                    ->select('id')
                    ->where([
                        'type' => $reqInParam['datatype_details'],
                        'name' => $confData['req_in']['value'],
                    ])
                    ->scalar();
            } else {
                $reqInParamValue = $confData['req_in']['value'];
            }

            // в конечном итоге, исходящий параметр услуги обязателен, если связан с входящим

            return $inputParam[$columnName] == $reqInParamValue;
        }

        return true;
    }
}
