<?php

namespace app\modules\soap\v2\skeletons\orgs;

/**
 * Class OrgAddress
 * @package app\modules\soap\v2\skeletons\orgs
 */
class OrgAddress
{
    /**
     * @var string Name {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $Name;

    /**
     * @var float Latitude {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $Latitude;

    /**
     * @var float Longitude {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $Longitude;
}
