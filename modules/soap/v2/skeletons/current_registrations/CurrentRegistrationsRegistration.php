<?php

namespace app\modules\soap\v2\skeletons\current_registrations;

use yii\base\Model;

/**
 * Class CurrentRegistrationsRegistration
 * @package app\modules\soap\v2\skeletons\current_registrations
 */
class CurrentRegistrationsRegistration extends Model
{
    /**
     * @var \app\modules\soap\v2\skeletons\registration_by_service_number\RegByServiceNumberRegistration[] Registration {minOccurs=0, maxOccurs=unbounded}
     * @soap
     */
    public $Registration = [];
}
