<?php

namespace app\common\components\inform\events;

use app\models\db\Visits;

/**
 * Событие: Оповещение о переносе Осмотра
 *
 * Class ConfirmEmailEvent
 * @package app\common\components\inform\events
 */
class VisitTransferedEvent extends SubscriptionEvent
{
    const EVENT_CODE = 'vetas_perenos_priema';

    /** @var \DateTime */
    public $olddate;
    /** @var string */
    public $oldvet;
    /** @var string */
    public $newvet;
    /** @var Visits */
    public $visit;

    public function getEventData($token): array
    {
        return [
            'io' => $this->visit->owner->getNameForInformation(),
            'name' => $this->visit->organization->name,
            'olddate' => $this->olddate->format('j.n.Y'),
            'oldtime' => $this->olddate->format('G:i'),
            'oldvet' => $this->oldvet,
            'date' => $this->visit->start_dttm,
            'time' => $this->visit->start_dttm,
            'vet' => $this->newvet,
            'type' => $this->visit->getGovServicesAsString(),
        ];
    }
}
