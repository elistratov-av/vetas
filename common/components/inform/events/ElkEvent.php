<?php

namespace app\common\components\inform\events;

use app\common\components\inform\SpkService;
use app\models\db\Contacts;
use yii\queue\db\Queue;

/**
 * Class SubscriptionEvent
 * @package app\common\components\inform\events
 *
 * @property string $sso_id
 * @property Contacts[] $contacts
 */
abstract class ElkEvent extends SubscriptionEvent
{
    /**
     * @throws \Exception
     */
    public function makeQueueJobs() : void
    {
        parent::makeQueueJobs();

        if (empty($this->sso_id)) {
            return;
        }

        $spkService = $this->getService();
        if ($spkService->enableELK !== true) {
            return;
        }

        /** @var Queue $queue */
        $queue = \Yii::$app->subscription_queue;

        // чтобы избежать дублирования на стороне ИС ПК (в случае отправки в ЛК по sso_id) - отправляем в токене time
        // для почты/телефона генерируется уникальная ссылка и дублирования не происходит
        $queue->push($this->getJob(SpkService::CHANNEL_SSO_ID, $this->sso_id, microtime(true)));
    }
}
