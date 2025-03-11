<?php


namespace app\modules\soap\skeletons\current_registrations;


use yii\base\Model;

class CurrentRegistrationsRegistration extends Model
{
    /**
     * @var \app\modules\soap\skeletons\registration_by_service_number\RegByServiceNumberRegistration[] registration {minOccurs=0, maxOccurs=unbounded}
     * @soap
     */
    public $registration = [];
}
