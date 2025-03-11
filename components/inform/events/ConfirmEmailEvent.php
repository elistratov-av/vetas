<?php

namespace app\common\components\inform\events;

use app\common\components\inform\SpkService;
use yii\queue\db\Queue;

/**
 * Событие: Подтверждение почты
 *
 * Class ConfirmEmailEvent
 * @package app\common\components\inform\events
 */
class ConfirmEmailEvent extends SubscriptionEvent
{
    const EVENT_CODE = 'confirm_email';

    /**
     * E-mail для подтверждения
     * @var string
     */
    public $email;

    /**
     * SsoId владельца
     * @var string
     */
    public $sso_id;

    /**
     * Уникальный ключ email’а для подтверждения
     * @var string
     */
    public $token;

    /**
     * Имя и Отчество владельца животных
     *
     * @var string
     */
    public $ownerName;

    /**
     * @param $token
     * @return array
     */
    public function getEventData($token) : array
    {
        return [
            'io' => $this->ownerName,
            'link' => $this->getSubscribeLink($token)
        ];
    }

    /**
     * @throws \Exception
     */
    public function makeQueueJobs() : void
    {
        /** @var Queue $queue */
        $queue = \Yii::$app->subscription_queue;
        $queue->push($this->getJob(SpkService::CHANNEL_EMAIL, $this->email, $this->token));
    }

}
