<?php

namespace app\modules\soap\models\etp;

use app\common\models\VisitStatus;
use app\models\MosruNotification;
use app\modules\soap\models\etp\status\Status10190;
use app\modules\soap\models\etp\status\Status10191;
use app\modules\soap\models\etp\status\Status1090;
use app\modules\soap\models\etp\status\Status1169;
use app\modules\soap\Module;

/**
 * Класс для обработки статусного сообщения 1069 от ЕТП - "Отмена приема по инициативе пользователя"
 *
 * Class CoordinateStatusMessage1069
 * @package app\modules\soap\models\etp
 */
class CoordinateStatusMessage1069 extends CoordinateStatusMessage
{
    /** @var string  */
    protected $errorStatus = Status1169::class;

    public function initAttributes() : void
    {
        $this->service_number = $this->data['ServiceNumber'];
        $this->responsible = $this->data['Responsible'];
        $this->department = $this->data['Department'];
        $this->status_id = (isset($this->data['StatusId']) && !empty($this->data['StatusId']))
            ? $this->data['StatusId'] : null;
    }

    public function rules()
    {
        return [
            [['service_number', 'department'], 'required'],
            [
                'service_number',
                function($attribute, $params, $validator) {
                    if (!$visit = $this->getVisit()) {
                        $this->addError($attribute, 'Не найден прием по переданному ServiceNumber');
                        return;
                    }

                    if ($visit->status == VisitStatus::IN_WORK) {
                        $this->addError($attribute, 'Нельзя отменять прием в статусе "В работе"');
                        return;
                    }

                    if ($visit->status == VisitStatus::FINISHED) {
                        $this->addError($attribute, 'Нельзя отменять прием в статусе "Завершен"');
                        return;
                    }

                    if (strtotime($visit->start_dttm) < time()) {
                        $this->addError($attribute,'Время начала приема уже наступило');
                        return;
                    }
                }
            ]
        ];
    }

    /**
     * Обработка входящего сообщения
     */
    public function process(): void
    {
        $this->initAttributes();
        if ($this->validate()) {
            $this->cancelVisit();
        } else {
            $this->sendErrorMessage(implode("\r\n", $this->getErrorSummary(true)));
        }
    }

    /**
     * Отмена визита
     * Посылаем статусы 1090 10190 10191 в очередь ЕТП
     */
    protected function cancelVisit() : void
    {
        $visit = $this->getVisit();
        $visit->cancel_initiator = \app\models\db\Visits::INITIATOR_IS_OWNER;
        $visit->status = VisitStatus::CANCELED;
        if (!$visit->save()) {
            $this->sendErrorMessage('Не удалось осуществить запись в БД');
        }

        MosruNotification::visitCancelInMosru($this->visit->id, $this->getStatusId());
    }

    /**
     * @return array
     */
    public function getCustomAttributes()
    {
        return [];
    }
}
