<?php

namespace app\modules\soap\v2\skeletons\services;

/**
 * Class ServicesType
 * @package app\modules\soap\v2\skeletons\services
 */
class ServicesType
{
    /**
     * @var integer TypeId {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $TypeId;

    /**
     * @var string TypeValue; {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $TypeValue;

    /**
     * @var \app\modules\soap\v2\skeletons\services\ServicesList[] ServiceList {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $ServiceList;
}
