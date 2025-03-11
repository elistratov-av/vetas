<?php

namespace app\common\soap;

use yii\base\Exception;

class SoapException extends Exception
{
    protected $code = 3000;
}
