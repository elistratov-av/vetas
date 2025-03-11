<?php

namespace app\common\components\inform\events;

class SubscriptionEventHandler
{
    /**
     * @param SubscriptionEventInterface $event
     * @throws \Exception
     */
    public function handle($event)
    {
        $event->makeQueueJobs();
    }
}
