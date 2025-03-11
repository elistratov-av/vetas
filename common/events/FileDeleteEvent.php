<?php

namespace app\common\events;

use yii\base\Event;

class FileDeleteEvent extends Event
{
    public $type;
    public $path;
    public $id;
}