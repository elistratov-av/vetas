<?php

namespace app\modules\soap\v2\models\etp;

use app\common\helpers\TsRangeHelper;
use app\common\validators\VisitEmergencyValidator;
use app\modules\soap\models\Booking;
use app\modules\soap\models\etp\ETPException;
use app\modules\soap\models\Visits;
use app\modules\soap\v2\models\db\ETPMessage;
use app\modules\soap\validators\CallToHomeVisitDateValidator;
use app\modules\soap\validators\VisitDateValidator;
use yii\helpers\ArrayHelper;
use yii\log\Logger;

class BookingMessage extends Message
{
    /**
     * @var string
     */
    public $ServiceNumber;
    /**
     * @var \app\modules\soap\v2\models\etp\members\ServiceProperties
     */
    public $ServiceProperties;
    /**
     * @var null|string
     */
    public $VisitDate;
    /**
     * @var string|null
     */
    public $SystemId;
    /**
     * @var string|null
     */
    public $MessageId;

    /**
     * @inheritDoc
     */
    public function init()
    {
        if (empty($this->requestData['BookingData'])) {
            throw new ETPException('Empty request data');
        }


        //$this->ServiceNumber = ArrayHelper::getValue($this->requestData['BookingData'], 'ServiceNumber');

        $this->ServiceNumber = '0000-0000000-000000-0000000/00'; //вариант подстановки левого SN

        $data = ArrayHelper::getValue($this->requestData['BookingData'], 'ServiceProperties');
        if (is_array($data)) {
            $data['class'] = '\\app\\modules\\soap\\v2\\models\\etp\\members\\ServiceProperties';
            $this->ServiceProperties = \Yii::createObject($data);
        }

        $this->VisitDate = $this->getVisitDate($this->ServiceProperties->Date, $this->ServiceProperties->Slot);
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        // Правила по датам идентичны тем же правилам, что были (и остаются, смотри класс ApplicationMessage) при
        // записи без резервирования (букинга)
        return [
            [
                'VisitDate',
                VisitDateValidator::class,
                'when' => function ($model) {
                    /* @var $model $this */
                    return $model->ServiceProperties->CallToHome === false;
                },
            ],
            [
                'VisitDate',
                CallToHomeVisitDateValidator::class,
                'when' => function ($model) {
                    /* @var $model $this */
                    return $model->ServiceProperties->CallToHome === true;
                },
            ],
            [
                'VisitDate',
                VisitEmergencyValidator::class,
                'when' => function ($model) {
                    /* @var $model $this */
                    return $model->ServiceProperties->CallToHome === false;
                },
                'organization_id' => $this->ServiceProperties->OrgId,
                'message' => 'Невозможно осуществить запись на данное время в связи с экстренной ситуацией в клинике',
            ],
            [['ServiceNumber', 'ServiceProperties'], 'required'],
            ['VisitDate', 'validateVisitTimeRange'],
        ];
    }

    public function process()
    {
        if (empty($this->ServiceNumber)) {
            return $this->errorResponse($this->formatErrors($this->getErrorSummary(true)));
        }

        if (!$this->validate()) {
            $errors = $this->getErrorSummary(true);
            $etpMessage = $this->saveEtpMessage();
            $this->log(
                "Request is not valid. Record {$etpMessage->id} in etp.message_v2 table can contain more details. See errors in extra data",
                Logger::LEVEL_WARNING,
                $errors
            );

            return $this->errorResponse($this->formatErrors($this->getErrorSummary(true)));
        }

        $mosRuServices = $this->getServices();
        $isCallToHome = $this->ServiceProperties->CallToHome;

        $booking = new Booking();
        $visitTotalLengthMinutes = Visits::getTotalVisitLengthMinutes($mosRuServices, $isCallToHome);
        $booking->setTimeRange(
            TsRangeHelper::buildTsRange(
                Visits::getStartDttm($this->VisitDate, $isCallToHome),
                $visitTotalLengthMinutes
            )
        );
        $booking->service_number = $this->ServiceNumber;
        $booking->setSpecialist($this->getSpecialist());

        try {
            if (!$booking->validate()) {
                throw new \Exception("Невалидные данные для резервации времени приёма: \n{$this->formatErrors($booking->getErrorSummary(true))}");
            }
            if (!$booking->save()) {
                throw new \Exception("Не удалось сохранить данные по резервации времени приёма: \n{$this->formatErrors($booking->getErrorSummary(true))}");
            }
        } catch (\Throwable $e) {
            \Yii::error($e);
            $this->log($e, Logger::LEVEL_ERROR);
            $this->saveEtpMessage();

            return $this->errorResponse($e->getMessage());
        }

        return $this->successResponse();
    }

    /**
     * @param \app\modules\soap\models\Visits $visit
     * @throws ETPException
     */
    protected
    function saveEtpMessage(): ETPMessage
    {
        $message = new ETPMessage();

        $message->visit_id = 0;
        $message->service_number = $this->ServiceNumber;
        $message->message = $this->requestData;

        if ($this->SystemId && $this->MessageId) {
            $message->system_id = $this->SystemId;
            $message->message_id = $this->MessageId;
        }

        if (!$message->save(false)) {
            throw new ETPException("Ошибка обработки сообщения", 422);
        }

        return $message;
    }
}
