<?php

namespace app\modules\soap\v2\skeletons\registration_by_service_number;

/**
 * Class RegByServiceNumberAddressCall
 * @package app\modules\soap\v2\skeletons\registration_by_service_number
 */
class RegByServiceNumberAddressCall
{
    /**
     * @var string {minOccurs=0, maxOccurs=1}
     * @soap
     */
    public $FiasCode;

    /**
     * @var string {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $AddressName;
}
