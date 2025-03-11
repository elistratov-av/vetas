<?php

namespace app\modules\soap\v2\skeletons\rq;

/**
 * Class OrgSpecListRequest
 * @package app\modules\soap\v2\skeletons\rq
 */
class OrgSpecListRequest
{
    /**
     * @var \app\modules\soap\v2\skeletons\types\ServicesIdType
     * @soap
     */
    public $Services;

    /**
     * Вызов на дом
     * 
     * @var boolean {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $CallToHome = false;

    /**
     * Код ФИАС для адреса при вызове на дом
     * 
     * @var string {minOccurs=0, maxOccurs=1}
     * @soap
     */
    public $FiasCode;
}
