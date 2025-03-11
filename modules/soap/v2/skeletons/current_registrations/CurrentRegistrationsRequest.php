<?php

namespace app\modules\soap\v2\skeletons\current_registrations;

/**
 * Class CurrentRegistrationsRequest
 * @package app\modules\soap\v2\skeletons\current_registrations
 */
class CurrentRegistrationsRequest
{
    /**
     * @var string {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $LastName;
    /**
     * @var string {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $FirstName;
    /**
     * @var string {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $Phone;
    /**
     * @var string {minOccurs=0, maxOccurs=1}
     * @soap
     */
    public $MiddleName;
}
