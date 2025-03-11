<?php

namespace app\common\components\inform\events;

use app\models\db\Visits;

/**
 * Событие: Отмена приема не по инициативе владельца
 *
 * Class CancelVisitEvent
 * @package app\common\components\inform\events
 */
class CancelVisitEvent extends SubscriptionEvent
{
    const EVENT_CODE = 'cancel_visit';

    /** @var Visits */
    public $visit;

    public function init()
    {
        parent::init();
        $this->sso_id = $this->visit->owner->sso_id;
    }

    /**
     * @param $token
     * @return array
     * @throws \Exception
     */
    public function getEventData($token) : array
    {
        $date = new \DateTime($this->visit->start_dttm);
        return [
            'io' => $this->visit->owner->getNameForInformation(),
            'pet_name' => $this->visit->pet->name,
            'spec_fio' => $this->visit->specialists->fullname,
            'address' => $this->visit->organization->fias_addresses->full_address,
            'date' => $date->format('d.m.Y'),
            'time' => $date->format('H:i'),
            'phone' => $this->visit->organization->phone,
            'link' => $this->getUnsubscribeLink($token)
        ];
    }

}
