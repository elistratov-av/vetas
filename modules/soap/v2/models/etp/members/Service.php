<?php

namespace app\modules\soap\v2\models\etp\members;

use yii\base\Model;

/**
 * Class Service
 * @package app\modules\soap\v2\models\etp\members
 */
class Service extends Model
{
    /**
     * @var int
     */
    public $ServiceId;
    /**
     * @var string
     */
    public $Name;
    /**
     * @var string
     */
    public $TypeValue;
}
