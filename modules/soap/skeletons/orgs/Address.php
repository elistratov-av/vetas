<?php

namespace app\modules\soap\skeletons\orgs;


class Address
{
    /**
     * @var string Name {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $name;

    /**
     * @var float Latitude {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $latitude;

    /**
     * @var float Longitude {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $longitude;
}
