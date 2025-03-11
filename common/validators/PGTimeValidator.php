<?php


namespace app\common\validators;

use \yii\validators\DateValidator;

class PGTimeValidator extends DateValidator
{
    public $format = 'php:H:i:s';
}
