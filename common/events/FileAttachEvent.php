<?php

namespace app\common\events;

use yii\base\Event;

class FileAttachEvent extends Event
{
    public $file;
}