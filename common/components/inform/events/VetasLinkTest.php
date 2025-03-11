<?php

namespace app\common\components\inform\events;

use app\models\db\Violation;

/**
 * Ивент для отладки сбора статистики по переходу по ссылке
 */
class VetasLinkTest extends SubscriptionEvent
{
    const EVENT_CODE = 'vetas_link';

    /** @var Violation */
    public $violation;

    public function getEventData($token): array
    {
        return [
            'files_link' => sprintf($this->getService()->linkToFilePage, $this->files_token),
        ];
    }
}
