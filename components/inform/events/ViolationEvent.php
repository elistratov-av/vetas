<?php

namespace app\common\components\inform\events;

use app\models\db\Violation;

/**
 * Событие: Нарушение
 *
 * Class ViolationEvent
 * @package app\common\components\inform\events
 */
class ViolationEvent extends SubscriptionEvent
{
    const EVENT_CODE = 'violation';

    /** @var Violation */
    public $violation;

    /**
     * Id пользователя инициализировавшего отправку
     * @var integer
     */
    public $id_author;

    /**
     * Блок информации по нарушению для владельца, заполняемый инспектором при работе с нарушением
     * @var string
     */
    public $text;

    public function init()
    {
        parent::init();
        $this->sso_id = $this->violation->owner->sso_id;
    }

    /**
     * @param $token
     * @return array
     */
    public function getEventData($token) : array
    {
        $eventData = [
            'text' => $this->text,
            'link' => $this->getUnsubscribeLink($token),
        ];
        if ($this->files_token) $eventData['files_link'] = sprintf($this->getService()->linkToFilePage, $this->files_token);

        return $eventData;
    }
}
