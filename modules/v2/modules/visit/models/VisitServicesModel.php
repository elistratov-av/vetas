<?php

namespace app\modules\v2\modules\visit\models;

use yii\base\Model;
use yii\validators\InlineValidator;

use app\models\db\Visits;
use app\models\db\VisitsGovServices;
use yii\helpers\ArrayHelper;

/**
 * Модель списка услуг-в-приёме
 * 
 * @package app\modules\v2\modules\visit\models
 * 
 * @property array $services
 */
class VisitServicesModel extends Model
{
    const SCENARIO_CREATE_VISIT = VisitSaveModel::SCENARIO_CREATE_VISIT;
    const SCENARIO_UPDATE_VISIT = VisitSaveModel::SCENARIO_UPDATE_VISIT;

    /** @var Visits|null */
    public $visit;

    /** @var array Данные услуг в визите */
    public $_servicesData = [];

    /** @var VisitServiceModel[] Инстанцированные модели */
    private $visitServiceModels = [];

    public function setServices(array $data = [])
    {
        $this->_servicesData = $data;
    }

    public function init()
    {
        if (self::SCENARIO_UPDATE_VISIT === $this->getScenario() && null === $this->visit) {
            throw new \LogicException('No visit set for editing visit services');
        }

        parent::init();
    }
    
    public function rules()
    {
        return [
            // ['isArray' => 'services', function (string $attrName, $params, InlineValidator $validator) use ($this) {
            //     if (!is_array($this[$attrName])) {
            //         $validator->addError($this, $attrName, 'Значение \'services\' должно быть массивом');
            //     }
            // }],
            ['default' => 'services', 'default', 'value' => []],
            [
                'notEmpty' => 'services',
                'required',
                'when' => function(VisitServicesModel $model, string $attr) {
                    // Список услуг может быть пустым только при записи в ЖО
                    return !$model->visit->isLiveQueue();
                },

            ],
            ['eachIsValid' => 'services', 'validateServices', 'skipOnError' => true],
        ];
    }

    public function beforeValidate()
    {
        // При создании приёма услуги в нём так же создаются, т.е. данные не должны содержать id
        $newIds = ArrayHelper::getColumn($this->_servicesData, 'id');
        if (self::SCENARIO_CREATE_VISIT === $this->getScenario() && !empty($newIds)) {
            $this->addError('services', 'При создании приёма в данных его услуг переданы ID: '. implode(', ', $newIds));

            return false;
        }

        // При изменении приёма данные услуг в нём должны содержать только id существующих в приёме
        if (self::SCENARIO_UPDATE_VISIT === $this->getScenario()) {
            $existingIds = $this->visit->getVisitsGovServices()->column('id');
            if (!empty($unexpectedIds = array_diff($newIds, $existingIds))) {
                $this->addError(
                    'services',
                    'При изменении приёма в данных услуг переданы ID, не связанные с приёмом: '. implode(', ', $unexpectedIds)
                );
                
                return false;
            }
        }

        $this->instantiateVisitServices();
    }

    private function instantiateVisitServices()
    {
        $existingVisitServices = $this->visit->getVisitsGovServices()
            ->indexBy('id')
            ->all()
        ;

        $this->visitServiceModels = array_map(
            function(array $serviceData) use ($existingVisitServices) {
                if (!empty($serviceData['id'])) {
                    if (!array_key_exists($serviceData['id'], $existingVisitServices)) {
                        \Yii::error(sprintf(
                            '%s record with ID %s not found while updating the visit %s',
                            VisitsGovServices::tableName(),
                            $serviceData['id'],
                            $this->visit->id
                        ));

                        $visitService = new VisitsGovServices();
                    } else {
                        $visitService = $existingVisitServices[$serviceData['id']];
                    }
                } else {
                    $visitService = new VisitsGovServices();
                }

                return (new VisitServiceModel())->load(['visitService' => $visitService] + $serviceData);
            },
            $this->_servicesData
        );
    }

    /**
     * @see \yii\validators\InlineValidator::validateAttribute()
     */
    public function validateServices(string $attrName, $params, InlineValidator $validator): void
    {
        if (!is_array($this[$attrName])) {
            $validator->addError($this, $attrName, 'Значение \'services\' должно быть массивом');
        }

        // Валидируем все модели коллекции услуг в приёме
        foreach ($this->visitServiceModels as $i => $visitServiceModel) {
            if (!$visitServiceModel->validate()) {
                $visitService = $visitServiceModel->visitService;

                $msgText = "Ошибки валидации услуги #{i}:\n - {errorsText}";
                $msgParams = [
                    'i' => $i,
                    'errorsText' => implode("\n - ", $visitService->getErrorSummary(true)),
                ];
                if (!$visitService->isNewRecord) {
                    $msgParams['id'] = $visitService->id;
                    $msgText = "Ошибки валидации услуги #{i} (id: {id}):\n - {errorsText}"; 
                }

                $validator->addError($this, 'services', $msgText, $msgParams);
            }
        }
    }
}
