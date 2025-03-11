<?php


namespace app\modules\soap\skeletons\current_registrations;


use yii\base\Model;

class CurrentRegistrationsResponse extends Model
{
    /**
     * @var \app\modules\soap\skeletons\current_registrations\CurrentRegistrationsRegistration registration {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $current_registation_list;
}
