<?php

namespace app\common\components\inform\events;

use yii\queue\JobInterface;

interface SubscriptionEventInterface
{
    const EVENT_NAME = 'subscription.event';

    /**
     * @param $token
     * @return array
     */
    public function getEventData($token) : array;

    /**
     * @param $type
     * @param $contact
     * @return array
     */
    public function getRecipient($type, $contact) : array;

    /**
     * @param $type
     * @param $contact
     * @param $token
     * @return JobInterface
     */
    public function getJob($type, $contact, $token) : JobInterface;

    /**
     *
     */
    public function makeQueueJobs() : void;
}
