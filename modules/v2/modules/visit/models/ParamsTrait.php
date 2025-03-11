<?php

namespace app\modules\v2\modules\visit\models;

use app\models\db\GovServicesParams;
use app\models\db\Pets;
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
 * @package app\modules\v2\modules\visit\models
 */
trait ParamsTrait
{
    static $textDatatype = 'text';
    static $dttmDatatype = 'dttm';
    static $numericDatatype = 'numeric';
    static $dictDatatype = 'dict';
    static $complexDatatype = 'complex';
    static $fileDatatype = 'file';

    // пропуск проверки при создании приема
    // Вакцинация животных с проведением клинического осмотра, идентификации,
    // консультации, инъекции (без стоимости вакцины)
    // в идеале в бд прода убрать обязательный параметр
    static $serviceNumberPass = 379;


    /**
     * @param array  $attributes
     * @param string $datatype
     * @param string $type
     * @return string|int
     */
    protected function extractExistingValue($attributes, $datatype, $type)
    {
        if ($datatype == self::$fileDatatype) {
            return empty($attributes['id']) ? [] : $this->prepareParamFileResources($attributes['id'], $type);
        }

        $column = $this->resolveColumnName($datatype);

        if ($column === null) {
            throw new InvalidArgumentException("Неизвестный тип данных {$datatype}");
        }

        return $attributes[$column];
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
     * @param array  $data
     * @param array  $field
     * @param string $type
     * @param bool   $forPdf
     * @return array
     */
    protected function formatParamValue($data, $field, $type, $forPdf = false)
    {
        if ($forPdf === true) {
            $value = empty($data) ? '' : $this->extractExistingValue($data, $field['datatype'], $type);
            if (!empty($value)) {
                if ($field['datatype'] == self::$dictDatatype) {
                    $config = $this->prepareDictionaryConfig($field);
                    $value = $this->findDictionaryValue($config['property'], $value);
                } elseif ($field['datatype'] == self::$dttmDatatype) {
                    $value = date('d.m.Y', $value);
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
                    'value'            => empty($data) ? '' : $this->extractExistingValue($data, $field['datatype'], $type),
                    'datatype'         => $field['datatype'],
                    'datatype_details' => $field['datatype_details'],
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
    protected static function findVisitParamValues($id_visit)
    {
        $q = new Query();
        $q->select('*')
            ->from(VisitParamValues::tableName())
            ->where(['[[id_visit]]' => $id_visit]);

        $q->orderBy(['[[id_param]]' => SORT_ASC])
            ->indexBy('id_param');

        return $q->all();
    }

    /**
     * @param int    $id_service
     * @param string $reportName
     * @return array
     */
    protected static function findReportForService($id_service, $reportName = null)
    {
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
     * @param int   $id_report
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
     * @param array  $field
     * @param string $type
     * @return array
     */
    protected function prepareParamConfig($field, $type)
    {
        $config = empty($field['config']) ? [] : Json::decode($field['config']);

        if ($field['datatype'] == self::$dictDatatype) {
            $config = array_merge($config, $this->prepareDictionaryConfig($field, true));
        }

        if ($type == 'visit_param_value') {
            $config['editable'] = false;
        }
        if ($type == 'visit_service_param_value') {
            $config['required'] = ($field['req_out'] === true);
        }

        return $config;
    }

    /**
     * @param array $field
     * @param bool  $includeOptions
     * @return array
     */
    private function prepareDictionaryConfig($field, $includeOptions = false)
    {
        $property = strtolower($field['datatype_details']);
        $attribute = ArrayHelper::getValue($field['config'], 'attribute', 'name');

        $config = [
            'property' => $property,
            'attribute' => $attribute,
        ];

        if ($includeOptions === true) {
            $rows = Dictionaries::findByType($property);
            $options = [];
            foreach ($rows as $row) {
                $options[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                ];
            }
            $config['options'] = $options;
        }

        return $config;
    }

    /**
     * @param array  $data
     * @param array  $fields
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

        if (!$this->validateParam($data['value'], $field, 'req_out', $this->id_visitservice)) {
            return false;
        }

        $row = array_fill_keys($this->columns($type), null);
        $row['id_param'] = $data['id_param'];
        foreach (['id_visitservice', 'id_visit'] as $attr) {
            if (array_key_exists($attr, $row)) {
                $row[$attr] = $this->$attr;
            }
        }

        if (!empty($data['value'])) {
            $column = $this->resolveColumnName($field['datatype']);
            if ($column !== null && array_key_exists($column, $row)) {
                $row[$column] = $data['value'];
            }
        }

        return $row;
    }

    /**
     * @param mixed  $value
     * @param array  $field
     * @param string $reqDirection
     * @param int    $id_visitservice
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
                $rules[] = [
                    $field['tech_name'],
                    'required',
                    'message' => 'Не заполнен обязательный параметр "' . $fieldName . '"',
                    'skipOnEmpty' => false
                ];
            }
        }

        switch ($field['datatype']) {
            case self::$textDatatype:
                $max = empty($field['datatype_details']) ? 0 : (int)$field['datatype_details'];
                if ($max > 0) {
                    $rules[] = [
                        $field['tech_name'],
                        'string',
                        'max' => $max,
                        'message' => 'Параметр "' . $fieldName . '" должен быть строкой',
                        'tooLong' => 'Параметр "' . $fieldName . '" должен содержать не более ' . $max . ' символов',
                    ];
                } else {
                    $rules[] = [$field['tech_name'], 'string', 'message' => 'Параметр "' . $fieldName . '" должен быть строкой'];
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
                // здесь хранится ID значения в disctionaries (int)
                // проверяем также, содержится ли элемент в справочнике
                $rules[] = [$field['tech_name'], 'integer', 'message' => 'Параметр "' . $fieldName . '" должен быть целым числом'];
                $rules[] = [
                    $field['tech_name'],
                    'exist',
                    'targetClass' => Dictionaries::class,
                    'targetAttribute' => 'id',
                    'filter' => ['type' => $field['datatype_details']],
                    'message' => 'Значение справочника для параметра "' . $fieldName . '" не существует',
                ];
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
            case self::$complexDatatype:
                // TODO - для complex до сих пор ничего не сформулировано
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
     * @param int    $value_id
     * @param string $attribute
     * @return false|null|string
     */
    protected function findDictionaryValue($type, $value_id, $attribute = 'name')
    {
        $q = new Query();
        $q->select($attribute)
            ->from(Dictionaries::tableName())
            ->where([
                'id' => (int)$value_id,
                'type' => $type,
            ])
            ->limit(1);

        try {
            $value = $q->scalar();
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
            'visit_param_value' => VisitParamValues::class,
            'visit_service_param_value' => VisitServiceParamValues::class,
        ];
    }

    /**
     * @var string $type
     * @param bool $byClassName
     * @return array
     */
    protected function columns($type, $byClassName = false)
    {
        $className = ($byClassName === true) ? $type : ArrayHelper::getValue(static::typesClassMap(), $type);

        $columns = [
            VisitParamValues::class => [
                'id_param',
                'num_value',
                'char_value',
                'date_value',
                'dict_value',
                'id_visit',
            ],
            VisitServiceParamValues::class => [
                'id_param',
                'num_value',
                'char_value',
                'date_value',
                'dict_value',
                'complex_value',
                'id_visitservice',
            ],
        ];

        return ArrayHelper::getValue($columns, $className);
    }

    /**
     * @param int    $id
     * @param string $type
     * @return array
     */
    protected function prepareParamFileResources($id, $type)
    {
        $output = [];

        $files = $this->findFileResources($id, $type);
        foreach ($files as $file) {
            $output[] = [
                'id' => $file->id,
                'type' => 'file',
                'attributes' => [
                    'path' => $file->path,
                    'name' => $file->name,
                    'created' => $file->created,
                    'entity_id' => $file->entity_id,
                    'entity_type' => $file->entity_type,
                ],
            ];
        }

        return $output;
    }

    /**
     * TODO: поменять, когда FileResource переведем на v2
     * @param int    $entity_id
     * @param string $type
     * @return \app\modules\v1\models\FileResource[]
     */
    protected function findFileResources($entity_id, $type)
    {
        return FileResource::find()
            ->where([
                'entity_id' => $entity_id,
                'entity_type' => $type,
            ])
            ->all();
    }

    /**
     * TODO: поменять, когда FileResource переведем на v2
     * @param int|array $entity_id
     * @param string    $type
     * @return int
     */
    protected function deleteFileResources($entity_id, $type)
    {
        return FileResource::deleteAll([
            'entity_id' => $entity_id,
            'entity_type' => $type,
        ]);
    }

    /**
     * @param int $id_service
     * @param int $id_report
     * @return array
     */
    protected function findReportParamsForService($id_service, $id_report = null)
    {
        $q = new Query();
        $q->select('p.*')
            ->addSelect('[[sp]].[[id_param]], [[sp]].[[id_service]], [[sp]].[[req_in]], [[sp]].[[req_out]]')
            ->from(Params::tableName() . ' p')
            ->leftJoin('gov_services_params sp', '[[sp]].[[id_param]] = [[p]].[[id]]')
            ->where(['[[sp]].[[id_service]]' => $id_service])
            ->andWhere(['not', ['[[sp]].[[req_in]]' => true]])
            ->andWhere([
                    'or',
                    ['[[p]].[[visit_flag]]' => false],
                    [
                        'and',
                        ['[[p]].[[visit_flag]]' => true],
                        ['[[sp]].[[req_out]]' => true],
                    ],
                ]
            );

        if (!empty($id_report)) {
            $q->addSelect('[[sr]].[[id_report]]');
            $q->leftJoin('gov_services_reports sr', '[[sr]].[[id_service]] = [[sp]].[[id_service]]');
            $q->andWhere(['[[sr]].[[id_report]]' => $id_report]);
        }

        $q->orderBy([
            '[[sp]].[[sort_by]]' => SORT_ASC,
            '[[p]].[[id]]' => SORT_ASC,
        ])
            ->indexBy('id');

        return $q->all();
    }

    /**
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
            ->andWhere(['[[sp]].[[req_in]]' => true]);

        $q->orderBy([
            '[[sp]].[[sort_by]]' => SORT_ASC,
            '[[p]].[[id]]' => SORT_ASC,
        ])
            ->indexBy('tech_name');

        return $q->all();
    }

    /**
     * Фронт может вообще не передать обязательных параметров в запросе - в результате это будет упущено при валидации параметров
     * @param array $post
     * @param array $fields
     * @param int   $id_visitservice
     * @param bool  $addError
     * @param bool  $returnFieldNames
     * @return array|void
     */
    protected function validateNotPassedRequiredParams($post, $fields, $id_visitservice, $addError = true, $returnFieldNames = false)
    {
        $postMapped = ArrayHelper::map($post, 'id_param', function ($el) {
            foreach ($el as $key => $value) {
                if (strpos($key, '_value') !== false && $value !== null && $value !== '') {
                    return $value;
                }
            }
            return null;
        });
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
                    $paramValue = ArrayHelper::getValue($postMapped, $id_param);
                    $fieldName = empty($field['name']) ? $field['tech_name'] : $field['name'];
                    $hasError = false;
                    foreach ($requiredFor as $relatedTechName) {
                        $relatedId = array_search($relatedTechName, $fieldsMapped, true);
                        if ($relatedId === false) {
                            continue;
                        }
                        $relatedValue = ArrayHelper::getValue($postMapped, $relatedId);
                        $relatedFieldname = ArrayHelper::getValue($fieldNamesMapped, $relatedId, $relatedTechName);
                        if (empty($paramValue) && !empty($relatedValue)) {
                            $hasError = true;
                            // if ($addError === true) {
                                $this->addError($field['tech_name'], 'Не заполнен обязательный параметр "' . $fieldName . '"');
                            // }
                            break;
                        } elseif (!empty($paramValue) && empty($relatedValue)) {
                            $hasError = true;
                            // if ($addError === true) {
                                $this->addError($field['tech_name'], 'Для заполнения параметра "' . $fieldName . '" необходимо указать "' . $relatedFieldname . '"');
                            // }
                            break;
                        }
                    }
                    if ($hasError === true) {
                        continue;
                    }
                }
            }

            if (array_key_exists($id_param, $postMapped)) {
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
     * @param array $field
     * @param int   $id_visitservice
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
                'id_param' => $reqInParam['id'],
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

    /**
     * @param                            $attribute
     * @param                            $params
     * @param                            $id_service
     * @param \app\models\db\GovServices $govService
     * @return bool
     */
    private function validateVisitServiceParams($attribute, $params, $id_service, GovServices $govService = null): bool
    {
        if ($govService === null) {
            $govService = GovServices::findOne(['id' => $id_service]);
            if ($govService === null) {
                $this->addError(
                    $attribute,
                    'Услуга с id "' . $id_service . '" не существует'
                );
                return false;
            }
        }

        // для услуг mosru проверку не делаем
        if ($govService->type == GovServices::TYPE_MOSRU) {
            return true;
        }

        // проверяем обязательные входящие параметры услуг
        $reqInParams = (new Query())
            ->select('gsp.*')
            ->addSelect('p.name')
            ->from(GovServicesParams::tableName() . ' gsp')
            ->leftJoin(Params::tableName() . ' p', '[[p]].[[id]] = [[gsp]].[[id_param]]')
            ->where([
                'id_service' => $id_service,
                'req_in' => true,
            ])
            ->all();

        if (!empty($reqInParams) && $govService->type != GovServices::TYPE_MOSRU) {
            $params = $this->fixReqInParamsWithVisitFlag($govService, $params);
            if ($this->hasErrors()) {
                // при ошибке с пустыми входящими параметрами 'Пол', 'Возраст' возвращаем ошибку сразу
                return false;
            }
            if (empty($params) && ($govService->id != self::$serviceNumberPass)) {
                $paramNames = implode(', ', ArrayHelper::getColumn($reqInParams, 'name', false));
                $this->addError(
                    $attribute,
                    'Не заполнены обязательные параметры: ' . $paramNames . ' для услуги "' . $govService->name . '"'
                );

                return false;
            }
        } elseif (empty($reqInParams) && !empty($params)) {
            $this->addError(
                $attribute,
                'Для услуги "' . $govService->name . '" не предусмотрены входящие параметры'
            );

            return false;
        }

        if (!is_array($params)) {
            $this->addError(
                $attribute,
                'Некорректный формат входящих параметров для услуги "' . $govService->name . '"'
            );

            return false;
        }

        $reqInIds = ArrayHelper::getColumn($reqInParams, 'id_param', false);

        foreach ($params as $param) {
            if (!isset($param['value']) || !isset($param['id_param'])) {
                $this->addError(
                    $attribute,
                    'Некорректный формат входящего параметра для услуги "' . $govService->name . '"'
                );

                return false;
            }

            if (!in_array($param['id_param'], $reqInIds)) {
                $this->addError($attribute, 'Переданный параметр ' . $param['id_param'] . ' не привязан к указанной услуге "' . $govService->name . '"');

                return false;
            }

            if (trim($param['value']) === '') {
                // значение не заполнено
                // @see https://jira.altarix.ru/browse/VETAIS-1401
                continue;
            }

            ArrayHelper::remove($reqInIds, array_search($param['id_param'], $reqInIds));

            $paramModel = new VisitServiceParamModel([
                'id_param' => $param['id_param'],
                'value' => $param['value'],
                'idService' => $id_service,
            ]);
            if (!$paramModel->validate(['value'])) {
                $this->addError(
                    $attribute,
                    'Некорректный входящий параметр для услуги "' . $govService->name . '"'
                    . ': '
                    . implode("\n", $paramModel->getErrorSummary(true))
                );

                return false;
            }
        }

        if (count($reqInIds) > 0 && ($govService->id != self::$serviceNumberPass)) {
            $paramNames = implode(', ', array_intersect_key(ArrayHelper::map($reqInParams, 'id_param', 'name'), array_flip($reqInIds)));
            $this->addError(
                $attribute,
                'Заполнены не все обязательные параметры: ' . $paramNames . ' для услуги "' . $govService->name . '"'
            );

            return false;
        }

        return true;
    }

    /**
     * Костыль для входящих параметров, которые одновременно имеют visit_flag === true и не передаются со фронта
     * пока что их два: 'P8_Petsex', 'P10_Petbirthday'
     * @param GovServices $service
     * @param array       $params
     * @return array
     */
    private function fixReqInParamsWithVisitFlag($service, $params = null)
    {
        $params = $params ?? [];
        $ids = ArrayHelper::getColumn($params, 'id_param', false);

        $q = (new Query())
            ->select(['gsp.id_param', 'gsp.id_service', 'gsp.req_in'])
            ->addSelect(['p.tech_name', 'p.visit_flag', 'p.datatype', 'p.datatype_details'])
            ->from(GovServicesParams::tableName() . ' gsp')
            ->leftJoin(
                Params::tableName() . ' p',
                '[[gsp]].[[id_param]] = [[p]].[[id]] AND [[gsp]].[[id_service]] = :id_service',
                ['id_service' => $service->id]
            )
            ->where([
                '[[p]].[[visit_flag]]' => true,
                '[[gsp]].[[req_in]]' => true,
            ])
            ->orderBy(['[[gsp]].[[id_param]]' => SORT_ASC]);
        if (!empty($ids)) {
            $q->andWhere(['not in', '[[gsp]].[[id_param]]', $ids]);
        }

        $rows = $q->all();

        if (empty($rows)) {
            return $params;
        }

        $pet = Pets::findOne(['id' => $this->visit->id_pet]);

        foreach ($rows as $row) {
            switch ($row['tech_name']) {
                case 'P8_Petsex':
                    if (!empty($pet->sex)) {
                        $params[] = [
                            'id_param' => $row['id_param'],
                            'value' => $pet->sex,
                        ];
                    } else {
                        $this->addError(
                            'services',
                            'Для записи на услугу "' . $service->name . '" необходимо заполнить "Пол" в карточке животного'
                        );
                    }
                    break;
                case 'P10_Petbirthday':
                    $birth_date = empty($pet->birthday) ? false : date_create_from_format('Y-m-d', $pet->birthday);
                    if ($birth_date !== false) {
                        $params[] = [
                            'id_param' => $row['id_param'],
                            'value' => $birth_date->getTimestamp(),
                        ];
                    } else {
                        $this->addError(
                            'services',
                            'Для записи на услугу "' . $service->name . '" необходимо заполнить "Возраст" в карточке животного'
                        );
                    }
                    break;
                default:
                    break;
            }
        }

        return $params;
    }
}
