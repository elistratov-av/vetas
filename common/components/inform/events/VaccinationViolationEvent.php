<?php

namespace app\common\components\inform\events;

use app\models\db\Violation;

class VaccinationViolationEvent extends SubscriptionEvent
{
    const EVENT_CODE = 'violation_vaccination';

    /** @var Violation */
    public $violation;

    /**
     * Id пользователя инициализировавшего отправку
     * @var integer
     */
    public $id_author;

    /**
     * Дата до которой необходимо устранить нарушение, заполняется Инспектором вручную
     * @var string
     */
    public $date_exp;

    /**
     * @param $token
     * @return array
     */
    public function getEventData($token) : array
    {
        $owner = $this->violation->owner;

        $eventData = [
            'io' => $owner->f_fio .' '. $owner->i_fio .' '. $owner->o_fio,
            'pet_name' => $this->violation->pet->name,
            'date' => $this->violation->date_violation ? date('d.m.Y', strtotime($this->violation->date_violation)) : null,
            'ref' => sprintf($this->getService()->linkToVaccineFeedbackForm, $this->violation->feedback_token),
            'date_exp' => $this->date_exp ? date('d.m.Y', strtotime($this->date_exp)) : null,
            'link' => $this->getUnsubscribeLink($token),
            'link_registration' => $this->getService()->linkRegistration,
        ];
        if ($this->files_token) $eventData['files_link'] = sprintf($this->getService()->linkToFilePage, $this->files_token);

        return $eventData;
    }
}
