<?php

namespace app\common\components\inform\events;

use app\common\components\inform\SpkService;
use app\models\db\Visits;
use yii\queue\db\Queue;

/**
 * Событие: Оповещение о переносе Осмотра
 *
 * Class ConfirmEmailEvent
 * @package app\common\components\inform\events
 */
class VisitToTransferEvent extends SubscriptionEvent
{
    const EVENT_CODE = 'vetas_k_perenosu';

    /** @var int */
    public $count;
    /** @var Visits */
    public $id_visit;
    /** @var bool */
    public $isAdminNotification = true;
    /** @var string */
    public $email;

    public function getEventData($token = null): array
    {
        return [
            'count' => $this->count,
            'link' => sprintf($this->getService()->linkToVisit, $this->id_visit),
        ];
    }
}
