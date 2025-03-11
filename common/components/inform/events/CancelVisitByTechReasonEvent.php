<?php

namespace app\common\components\inform\events;

use app\models\db\Visits;

class CancelVisitByTechReasonEvent extends SubscriptionEvent
{
    const EVENT_CODE = 'cancellation_for_technical_reasons';

    /** @var Visits */
    public $visit;

    public function init()
    {
        parent::init();
        $this->sso_id = $this->visit->owner->sso_id;
    }

    public function getEventData($token) : array
    {
        // У телевета должна быть 1 услуга
        $service = $this->visit->services[0];
        $startDatetime = new \DateTime($this->visit->fact_start_dttm);

        return [
            'io' => $this->visit->owner->i_fio .' '. $this->visit->owner->o_fio,
            'start_date' => $startDatetime->format('d.m.Y'),
            'start_time' => $startDatetime->format('H:i'),
            'type_of_service' => $service->name,
        ];
    }
}
