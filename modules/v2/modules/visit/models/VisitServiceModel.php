<?php

namespace app\modules\v2\modules\visit\models;

use app\common\models\VisitStatus;
use app\models\db\GovServices;
use app\models\db\Pets;
use app\models\db\Visits;
use app\models\db\VisitServiceParamValues;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class VisitServiceModel
 *
 * @package app\modules\v2\modules\visit\models
 * 
 * TODO:model-inheritance Наследовать классам \app\models\db\*!
 */
class VisitServiceModel extends Model
{
    use ParamsTrait, VisitTrait;

    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_DELETE = 'delete';

    /**
     * @var \app\models\db\Visits
     */
    public $visit;

    /**
     * @var \app\models\db\VisitsGovServices
     */
    public $visitService;

    /**
     * @var int
     */
    public $id_service;

    /**
     * @var integer|array
     */
    public $id_pet;

    /**
     * @var int
     */
    public $count;

    /**
     * @var array
     */
    public $params;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!isset($this->visit)) {
            throw new InvalidConfigException();
        }
        if (($this->scenario == self::SCENARIO_UPDATE || $this->scenario == self::SCENARIO_DELETE) && !isset($this->visitService)) {
            throw new InvalidConfigException();
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['visit', 'validateVisit', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE, self::SCENARIO_DELETE]],
            [['id_service', 'count', 'id_pet'], 'integer', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            ['id_service', 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [
                'id_service',
                'exist',
                'skipOnError'     => true,
                'targetClass'     => GovServices::class,
                'targetAttribute' => ['id_service' => 'id'],
                'on'              => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE],
            ],
            [
                'id_pet',
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Pets::class,
                'targetAttribute' => ['id_pet' => 'id'],
                'on'              => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE],
            ],
            ['count', 'default', 'value' => 1, 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            ['count', 'validateCount', 'skipOnError' => true, 'skipOnEmpty' => false, 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            ['params', 'validateParams', 'skipOnError' => true, 'skipOnEmpty' => false, 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
        ];
    }

    /**
     * @param string $attribute is the name of the attribute to be validated
     * @param array $params contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param \yii\validators\InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     * @see \yii\validators\InlineValidator
     */
    public function validateVisit($attribute, $params, $validator)
    {
        // Работа с перечнем услуг доступна для неоплаченных приемов в состоянии "В работе"
        if ($this->visit->status != VisitStatus::IN_WORK) {
            $this->addError($attribute, 'Редактирование набора услуг не доступно для текущего статуса приема');

            return;
        }
        if ($this->visit->is_paid !== false) {
            $this->addError($attribute, 'Редактирование набора услуг доступно только для неоплаченного приема');

            return;
        }
    }

    /**
     * @param string $attribute is the name of the attribute to be validated
     * @param array $params contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param \yii\validators\InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     * @see \yii\validators\InlineValidator
     */
    public function validateCount($attribute, $params, $validator)
    {
        //костыль для услуги 0495 Содержание животных в стационаре (id = 622 на тесте и id = 1751 на проде)
        $PROD_0495 = 1751;
        $TEST_0495 = 622;

        // если не передан - по умолчанию будем считать 1
        if ($this->$attribute < 1) {
            $this->addError(
                $attribute,
                'Некорректный параметр ' . $attribute . ':'
                . ' для услуги ID ' . $this->id_service . ' параметр count должен быть не менее 1'
            );

            return;
        }

        //!!!
        //Подразумиваем, что реальное значение count передает только при обновлении услуги.
        //При создании всегда передается 1, а перед сохранением устанавливается истинное значение
        if($this->scenario == self::SCENARIO_CREATE){
            return;
        }

        // если ALL и  count != 1
        $serviceForType = $this->getServiceForTypeByGovService();
        if ($serviceForType === GovServices::FOR_ALL && $this->$attribute != 1 && !in_array($this->id_service,[$PROD_0495, $TEST_0495])) {
            $this->addError(
                $attribute,
                'Некорректный параметр ' . $attribute . ':'
                . ' для услуги ID ' . $this->id_service . ' параметр count должен быть равен 1'
            );

            return;
        } 

        if ($this->id_pet) {
            $countServicePets = count(array_unique((array)$this->id_pet));
        } else {
            $countServicePets = count(array_unique(
                ArrayHelper::getColumn($this->visit->pets, 'id')
            ));
        }

        // если count_flag == false &&  count < переданого количества животных в услуге

        $countFlag = GovServices::findCountFlag($this->id_service);
        if (!$countFlag){
            $govService = GovServices::findOne($this->id_service);
            if(
                !($govService->for_broods == GovServices::FOR_ALL && $govService->for_multiple == GovServices::FOR_ALL) AND
                $this->$attribute != $countServicePets && !in_array($this->id_service,[$PROD_0495, $TEST_0495])
            ){
                $this->addError(
                    $attribute,
                    'Некорректный параметр ' . $attribute . ':'
                    . ' для услуги ID ' . $this->id_service . ' параметр count должен быть равен количеству животных в услуге'
                );

                return;
            } else {
                return;
            }
        }

        // Для всех остальных значение count должно быть равно или больше количества животных в услуге
        if ($this->visitService->id_pet === null && $this->$attribute < $countServicePets && !in_array($this->id_service,[$PROD_0495, $TEST_0495])) {
            $this->addError(
                $attribute,
                'Некорректный параметр ' . $attribute . ':'
                . ' для услуги ID ' . $this->id_service . ' параметр count должен быть равен или больше количества животных в услуге'
            );

            return;
        }
    }

    /**
     * Возвращает возможные значения типа для услуги при ее создании
     *
     * @param $govService
     * @return null|string Возможны значения: GovServices::FOR_NULL, GovServices::FOR_HEAD, GovServices::FOR_ALL
     */
    private function getServiceForTypeByGovService()
    {
        $visitService = $this->visitService->isNewRecord == false ? $this->visitService->service : GovServices::findOne($this->id_service);
        switch ($this->visit->variety) {
            case Visits::VISIT_BROOD:
                return $visitService->for_broods;
            case Visits::VISIT_MULTIPLE:
                return $visitService->for_multiple;
        }

        return null;
    }

    /**
     * @param string $attribute is the name of the attribute to be validated
     * @param array $params contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param \yii\validators\InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     * @see \yii\validators\InlineValidator
     */
    public function validateParams($attribute, $params, $validator)
    {
        $visitServiceParams = $this->$attribute;

        // проверяем входящие параметры услуги
        if (!$this->validateVisitServiceParams($attribute, $visitServiceParams, $this->id_service)) {
            return;
        }

        $this->$attribute = $visitServiceParams;
    }

    /**
     * @return bool
     * @throws \Throwable
     */
    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        // при редактировании подтягивает id_pet (при наличии)
        if ($this->scenario === self::SCENARIO_UPDATE && $this->visitService->id_pet !== null) {
            $this->id_pet = $this->visitService->id_pet;
        }

        if (is_null($this->id_pet)) {
            if ($this->visit->variety === Visits::VISIT_SINGLE) {
                $this->addError(
                    'id_pet',
                    'Некорректный параметр id_pet:'
                    . ' для приема с одним животным параметр id_pet обязателен'
                );

                return false;
            }
            if ($this->isRequiredIdPet()) {
                $this->addError(
                    'id_pet',
                    'Некорректный параметр id_pet:'
                    . ' для услуги ID ' . $this->id_service . ' параметр id_pet обязателен'
                );

                return false;
            }
        }

        $this->visitService->setAttributes([
            'id_visit'   => $this->visit->id,
            'id_service' => $this->id_service,
            'id_pet'     => $this->id_pet,
            'count'      => $this->count,
        ]);

        if (!$this->visitService->save()) {
            $this->addErrors($this->visitService->getErrors());

            return false;
        }

        // Для услуг которые не считаются 1 на осмотр (GovServices::FOR_ALL) линкуем many-to-many
        if ($this->scenario == self::SCENARIO_CREATE
            && !$this->isGovServiceForAllPets()) {

            $pet = Pets::find()->where(['id' => $this->id_pet])->one();
            $this->visitService->link('pets', $pet);
        }

        if (!$this->linkVisitServiceParams()) {
            return false;
        }

        //Пишем данные об отчете (в т.ч. будущие) в базу
        if($this->scenario == self::SCENARIO_CREATE) {
            (new VisitParamsModel())
                ->saveVisitParamsByGeneral($this->visitService);
        }

        return true;
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    private function linkVisitServiceParams(): bool
    {
        if ($this->scenario === self::SCENARIO_UPDATE) {
            // проверим, не нужно ли удалить часть параметров
            $paramsToUpdate = empty($this->params) ? [] : ArrayHelper::getColumn($this->params, 'id', false);
            $paramsToUpdate = array_filter($paramsToUpdate);
            $condition = empty($paramsToUpdate)
                ? ['id_visitservice' => $this->visitService->id]
                : [
                    'and',
                    ['id_visitservice' => $this->visitService->id],
                    ['not in', 'id', $paramsToUpdate],
                ];
            Yii::$app->db
                ->createCommand()
                ->delete(VisitServiceParamValues::tableName(), $condition)
                ->execute();
        }

        if (!empty($this->params)) {
            foreach ($this->params as $param) {
                $paramModel = new VisitServiceParamModel([
                    'id'        => isset($param['id']) ? $param['id'] : null,
                    'id_param'  => $param['id_param'],
                    'value'     => $param['value'],
                    'idService' => $this->id_service,
                ]);

                if (
                    !$paramModel->validate() ||
                    !$paramModel->save(
                        $this->visitService->id_visit,
                        $this->visitService->id,
                        $this->visitService->id_pet
                    )
                ) {
                    $this->addErrors($paramModel->getErrors());

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Проверяет прием на тип для выводка/нескольких животных и осуществляется ли услуга на голову
     *
     * @return bool
     */
    private function isMultiVisitAndServiceOnHead(): bool
    {
        return
            ($this->visit->variety === Visits::VISIT_BROOD && GovServices::findOne($this->id_service)->for_broods === 'HEAD') ||
            ($this->visit->variety === Visits::VISIT_MULTIPLE && GovServices::findOne($this->id_service)->for_multiple === 'HEAD');
    }

    /**
     * Указывает необходим ли ID питомца
     *
     * @return bool
     */
    private function isRequiredIdPet(): bool
    {
        return
            ($this->visit->variety === Visits::VISIT_BROOD && GovServices::findOne($this->id_service)->for_broods === null) ||
            ($this->visit->variety === Visits::VISIT_MULTIPLE && GovServices::findOne($this->id_service)->for_multiple === null);
    }

    /**
     * Услуга считается на всю группу животных
     *
     * @return bool
     */
    private function isGovServiceForAllPets(): bool
    {
        return
            ($this->visit->variety === Visits::VISIT_BROOD && GovServices::findOne($this->id_service)->for_broods === GovServices::FOR_ALL) ||
            ($this->visit->variety === Visits::VISIT_MULTIPLE && GovServices::findOne($this->id_service)->for_multiple === GovServices::FOR_ALL);
    }
}
