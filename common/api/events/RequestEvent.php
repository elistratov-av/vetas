<?php

declare(strict_types = 1);

namespace app\common\api\events;

use app\common\api\messages\Request;
use yii\base\Event;

/**
 * Class RequestEvent
 *
 * @package app\common\api\events
 */
class RequestEvent extends Event
{
    /**
     * @var string Message id(can be not unique)
     */
    public $id;

    /**
     * @var Request
     */
    public $rq;
}
