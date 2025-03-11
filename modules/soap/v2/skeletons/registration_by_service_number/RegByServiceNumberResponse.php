<?php

namespace app\modules\soap\v2\skeletons\registration_by_service_number;

use yii\base\Model;

/**
 * Class RegByServiceNumberResponse
 * @package app\modules\soap\v2\skeletons\registration_by_service_number
 */
class RegByServiceNumberResponse extends Model
{
    /**
     * @var \app\modules\soap\v2\skeletons\registration_by_service_number\RegByServiceNumberRegistration registration {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $RegistrationByServiceNumber;
}
