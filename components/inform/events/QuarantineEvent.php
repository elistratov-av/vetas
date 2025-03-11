<?php

namespace app\common\components\inform\events;

/**
 * Событие: Уведомление о проведении противоэпизоотических мероприятий
 *
 * Class QuarantineEvent
 * @package app\common\components\inform\events
 */
class QuarantineEvent extends SubscriptionEvent
{
    const EVENT_CODE = 'quarantine';

    /**
     * E-mail адрес владельца
     * @var string
     */
    public $email;

    /**
     * Уникальный ключ email’а владельца для отписки (только для отправки на email)
     * @var string
     */
    public $token;

    /**
     * ФИО владельца животных
     * @var string
     */
    public $ownerName;

    /**
     * Угрожаемая зона карантина
     * @var string
     */
    public $area;

    /**
     * Дата начала карантина
     * @var string
     */
    public $startDate;

    /**
     * @return array
     */
    public function getEventData($token) : array
    {
        return [
            'io' => $this->ownerName,
            'start_date' => $this->startDate,
            'area' => $this->area,
            'link' => $this->getUnsubscribeLink($token)
        ];
    }

}
