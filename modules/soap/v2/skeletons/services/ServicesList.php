<?php

namespace app\modules\soap\v2\skeletons\services;

/**
 * Class ServicesList
 * @package app\modules\soap\v2\skeletons\services
 */
class ServicesList
{
    /**
     * @var \app\modules\soap\v2\skeletons\services\Service Service list {minOccurs=0, maxOccurs=unbounded}
     * @soap
     */
    public $Service;
}
