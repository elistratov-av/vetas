<?php

namespace app\modules\soap\v2\models\etp;

use app\common\models\VisitStatus;
use app\common\validators\VisitEmergencyValidator;
use app\modules\soap\models\etp\ETPException;
use app\modules\soap\models\etp\status\Status1168;
use app\modules\soap\models\Visits;
use app\modules\soap\v2\models\db\ETPMessage;
use app\modules\soap\validators\CallToHomeVisitDateValidator;
use app\modules\soap\validators\VisitDateValidator;
use yii\helpers\ArrayHelper;

/**
 * Class ApplicationStatusMessage
 * @package app\modules\soap\v2\models\etp
 */
class ApplicationStatusMessage extends Message
{
    const SCENARIO_1068_TRANSFER = 'scenario_1068';
    const SCENARIO_1069_CANCEL = 'scenario_1069';

    /**
     * @var string
     */
    public $ResponseDate;
    /**
     * @var \app\modules\soap\v2\models\etp\members\Status
     */
    public $Status;
    /**
     * @var \app\modules\soap\v2\models\etp\members\Declarant
     */
    public $Declarant;
    /**
     * @var \app\modules\soap\v2\models\etp\members\ServiceProperties
     */
    public $ServiceProperties;
    /**
     * @var string
     */
    public $Note;
    /**
     * @var string
     */
    public $ServiceNumber;
    /**
     * @var string|null
     */
    public $SystemId;
    /**
     * @var string|null
     */
    public $MessageId;
    /**
     * @var \app\modules\soap\v2\models\etp\members\Reason
     */
    public $Reason;
    /**
     * @var null|string
     */
    public $StatusId;
    /**
     * @var null|string
     */
    public $VisitDate;

    /**
     * @inheritDoc
     */
    public function init()
    {
        if (empty($this->requestData['ApplicationStatusData'])) {
            throw new ETPException('Empty request data');
        }

        foreach (['ResponseDate', 'Note', 'ServiceNumber', 'StatusId'] as $prop) {
            $this->$prop = ArrayHelper::getValue($this->requestData['ApplicationStatusData'], $prop);
        }

        foreach (['Status', 'Declarant', 'ServiceProperties', 'Reason'] as $prop) {
            $data = ArrayHelper::getValue($this->requestData['ApplicationStatusData'], $prop);
            if (is_array($data)) {
                $data['class'] = '\\app\\modules\\soap\\v2\\models\\etp\\members\\' . $prop;
                $this->$prop = \Yii::createObject($data);
            }
        }

        if (isset($this->ServiceProperties)) {
            $this->VisitDate = $this->getVisitDate($this->ServiceProperties->Date, $this->ServiceProperties->Slot);
        }
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['ResponseDate', 'Note', 'StatusId', 'Reason'], 'safe'],
            [
                ['ServiceNumber', 'Status'],
                'required',
                'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_1068_TRANSFER, self::SCENARIO_1069_CANCEL],
                'message' => 'Не передан параметр {attribute}',
            ],
            ['Status', 'validateNestedMember', 'on' => [self::SCENARIO_1068_TRANSFER, self::SCENARIO_1069_CANCEL]],
            ['Declarant', 'required', 'on' => [self::SCENARIO_1068_TRANSFER, self::SCENARIO_1069_CANCEL]],
            ['ServiceProperties', 'required', 'on' => [self::SCENARIO_1068_TRANSFER]],
            [
                'ServiceNumber',
                function ($attribute, $params, $validator) {
                    if (!$visit = $this->getVisit()) {
                        $this->addError($attribute, 'Не найден прием по переданному ServiceNumber');
                    }
                },
                'on' => [self::SCENARIO_1068_TRANSFER, self::SCENARIO_1069_CANCEL],
            ],
            [
                'ServiceNumber',
                function ($attribute, $params, $validator) {
                    $visit = $this->getVisit();
                    switch ($visit->status) {
                        case VisitStatus::IN_WORK:
                            $this->addError($attribute, 'Нельзя переносить прием в статусе "В работе"');
                            return;
                        case VisitStatus::FINISHED:
                            $this->addError($attribute, 'Нельзя переносить прием в статусе "Завершен"');
                            return;
                        case VisitStatus::CANCELED:
                            $this->addError($attribute, 'Нельзя переносить прием в статусе "Отменен"');
                            return;
                    }

                    $time = ($this->ServiceProperties->CallToHome) ? Visits::CALL_TO_HOME_CHANGE_TIME : Visits::IN_CLINIC_CHANGE;
                    $error = ($this->ServiceProperties->CallToHome)
                        ? "Изменение времени вызова на дом возможно за 6 часов до начала"
                        : "Изменение времени начала приема возможно за 2 часа до начала";

                    if (strtotime($visit->start_dttm) < (time() + $time)) {
                        $this->addError($attribute, $error);
                    }
                },
                'on' => [self::SCENARIO_1068_TRANSFER],
            ],
            [
                'ServiceNumber',
                function ($attribute, $params, $validator) {
                    $visit = $this->getVisit();
                    switch ($visit->status) {
                        case VisitStatus::IN_WORK:
                            $this->addError($attribute, 'Нельзя отменять прием в статусе "В работе"');
                            return;
                        case VisitStatus::FINISHED:
                            $this->addError($attribute, 'Нельзя отменять прием в статусе "Завершен"');
                            return;
                    }

                    if (strtotime($visit->start_dttm) < time()) {
                        $this->addError($attribute, 'Время начала приема уже наступило');
                    }
                },
                'on' => [self::SCENARIO_1069_CANCEL],
            ],
            ['ServiceProperties', 'validateNestedMember', 'on' => [self::SCENARIO_1068_TRANSFER]],
            [['Declarant'], 'validateNestedMember', 'on' => [self::SCENARIO_1068_TRANSFER, self::SCENARIO_1069_CANCEL]],
            [
                'VisitDate',
                VisitDateValidator::class,
                'when' => function ($model) {
                    /* @var $model $this */
                    return $model->ServiceProperties->CallToHome === false;
                },
                'message' => 'Изменение времени приема в клинике возможно за 2 часа до начала',
                'on' => [self::SCENARIO_1068_TRANSFER],
            ],
            [
                'VisitDate',
                CallToHomeVisitDateValidator::class,
                'when' => function ($model) {
                    /* @var $model $this */
                    return $model->ServiceProperties->CallToHome === true;
                },
                'message' => 'Изменение времени вызова врача на дом возможно за 6 часа до начала',
                'on' => [self::SCENARIO_1068_TRANSFER],
            ],
            [
                'VisitDate',
                VisitEmergencyValidator::class,
                'when' => function ($model) {
                    /* @var $model $this */
                    return $model->ServiceProperties->CallToHome === false;
                },
                'organization_id' => (isset($this->ServiceProperties->OrgId) ? $this->ServiceProperties->OrgId : 0),
                'message' => 'Невозможно осуществить запись на данное время в связи с экстренной ситуацией в клинике',
                'on' => [self::SCENARIO_1068_TRANSFER],
            ],
            ['VisitDate', 'validateVisitTimeRange', 'on' => [self::SCENARIO_1068_TRANSFER]],
        ];
    }

    /**
     * @return \app\modules\soap\v2\models\etp\InstantResponse
     */
    public function process()
    {
        if ($this->isMonitoring()) {
            return $this->sendMonitoringResponse();
        }

        if (empty($this->ServiceNumber)) {
            return $this->errorResponse('Не передан параметр ServiceNumber');
        }

        try {
            switch ($this->Status->StatusCode) {
                case 1068:
                    // "Перенос приема по инициативе пользователя"
                    $this->setScenario(self::SCENARIO_1068_TRANSFER);
                    if (!$this->validate() || !$this->moveVisit()) {
                        $note = null;
                        if (isset($this->ServiceProperties)) {
                            if ($this->ServiceProperties->hasErrors('OrgId') || $this->ServiceProperties->hasErrors('SpecialistId')) {
                                $note = Status1168::wrongOrgError();
                            }
                        }
                        if ($note === null && $this->hasErrors('VisitDate')) {
                            $note = Status1168::visitDateTimeError();
                        }
                        $this->getSendStatusService()->visitChangeInMosruError($this->visit->id, $this->StatusId, $this->formatErrors($this->getErrorSummary(true)), $note);
                    }
                    break;
                case 1069:
                    // "Отмена приема по инициативе пользователя"
                    $this->setScenario(self::SCENARIO_1069_CANCEL);
                    if (!$this->validate() || !$this->cancelVisit()) {
                        $this->getSendStatusService()->visitCancelInMosruError($this->visit->id, $this->StatusId, $this->formatErrors($this->getErrorSummary(true)), $this->visit->isOnlineVisit());
                    }
                    break;
                default:
                    return $this->errorResponse('Неизвестный статус');
            }
        } catch (\Throwable $e) {
            \Yii::error($e);

            return $this->errorResponse('Не удалось осуществить запись в БД');
        }

        return $this->successResponse();
    }

    /**
     * Перенос визита
     * Посылаем в очередь статус 1053 после успешного изменения записи, 1168 - в случае невозможности перенести
     *
     * Пользователь может поменять только дату/время, клинику и врача
     * Данные о животном и владельце не изменяются
     * При вызове на дом также нельзя сменить адрес
     * @return bool
     */
    protected function moveVisit(): bool
    {
        $visit = $this->getVisit();
        $visit->id_organization = $this->ServiceProperties->OrgId;
        $visit->start_dttm = $this->VisitDate;
        $visit->status = VisitStatus::CHANGED;

        $visit->call_to_home = $this->ServiceProperties->CallToHome;
        $visit->setServices($visit->services); //чтобы правильно определить время
        $visit->setSpecialist($this->getSpecialist());

        $this->updateMessage($visit);
        if (!$visit->save()) {
            return false;
        }

        $this->getSendStatusService()->visitChangeInMosru($visit->id, $this->StatusId);

        return true;
    }

    /**
     * Отмена визита
     * Посылаем статусы 1090 10190 10191 в случае успешной отмены записи, 1169 -  случае невозможности отмены
     * @return bool
     */
    protected function cancelVisit(): bool
    {
        $visit = $this->getVisit();
        $visit->cancel_initiator = \app\models\db\Visits::INITIATOR_IS_OWNER;
        $visit->status = VisitStatus::CANCELED;

        $this->updateMessage($visit);
        if (!$visit->save()) {
            return false;
        }

        $this->getSendStatusService()->visitCancelInMosru($visit->id, $this->StatusId, $visit->isOnlineVisit());

        return true;
    }

    private function updateMessage(Visits $v): void
    {
        if (!$this->SystemId || !$this->MessageId) {
            return;
        }
        ETPMessage::updateAll([
            'system_id' => $this->SystemId,
            'message_id' => $this->MessageId,
        ], ['visit_id' => $v->id]);
    }
}
