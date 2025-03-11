<?php

namespace app\modules\soap\skeletons\registration_by_service_number;

use yii\base\Model;

class RegByServiceNumberResponse extends Model
{
    /**
     * @var \app\modules\soap\skeletons\registration_by_service_number\RegByServiceNumberRegistration registration {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $registration_by_servicenumber;

}
