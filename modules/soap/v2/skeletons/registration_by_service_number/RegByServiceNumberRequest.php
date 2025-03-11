<?php

namespace app\modules\soap\v2\skeletons\registration_by_service_number;

/**
 * Class RegByServiceNumberRequest
 * @package app\modules\soap\v2\skeletons\registration_by_service_number
 */
class RegByServiceNumberRequest
{
    /**
     * @var string {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $ServiceNumber;
}
