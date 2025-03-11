<?php

namespace app\modules\soap\skeletons\services;


class ServicesList
{
    /**
     * @var \app\modules\soap\skeletons\services\Service Service list {minOccurs=0, maxOccurs=unbounded}
     * @soap
     */
    public $service;
}
