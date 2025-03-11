<?php

declare(strict_types = 1);

namespace app\common\api\events;

use app\common\api\messages\Response;
use yii\base\Event;

/**
 * Class ResponseEvent
 *
 * @package app\common\api\events
 */
class ResponseEvent extends Event
{
    /**
     * @var string Message id(can be not unique)
     */
    public $id;

    /**
     * @var Response
     */
    public $rs;
}
