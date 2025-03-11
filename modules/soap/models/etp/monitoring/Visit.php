<?php

namespace app\modules\soap\models\etp\monitoring;

use app\modules\soap\models\Visits;

/**
 * Класс заглушка для мониторинга mos.ru
 *
 * Class Visit
 * @package app\modules\soap\models\etp\monitoring
 */
class Visit extends Visits
{
    public $ticket_number = 2;

    public function getOldTicketNumber()
    {
        return 1;
    }
}