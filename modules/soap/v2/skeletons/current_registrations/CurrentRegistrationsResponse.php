<?php

namespace app\modules\soap\v2\skeletons\current_registrations;

use yii\base\Model;

/**
 * Class CurrentRegistrationsResponse
 * @package app\modules\soap\v2\skeletons\current_registrations
 */
class CurrentRegistrationsResponse extends Model
{
    /**
     * @var \app\modules\soap\v2\skeletons\current_registrations\CurrentRegistrationsRegistration registration {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $CurrentRegistrationsList;
}
