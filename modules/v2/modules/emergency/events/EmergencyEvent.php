<?php

namespace app\modules\v2\modules\emergency\events;

use app\models\db\OrganizationsEmergency;
use yii\base\Event;

class EmergencyEvent extends Event
{
    const EMERGENCY_CREATE_EVENT = 'emergency.create';

    /** @var OrganizationsEmergency */
    public $emergency;
}
