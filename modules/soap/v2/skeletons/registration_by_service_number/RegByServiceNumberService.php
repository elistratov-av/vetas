<?php

namespace app\modules\soap\v2\skeletons\registration_by_service_number;

/**
 * Class RegByServiceNumberService
 * @package app\modules\soap\v2\skeletons\registration_by_service_number
 */
class RegByServiceNumberService
{
    /**
     * @var integer {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $Id;

    /**
     * @var string {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $Name;
}
