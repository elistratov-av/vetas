<?php

namespace app\modules\soap\v2\skeletons\services;

/**
 * Class ServicesResponse
 * @package app\modules\soap\v2\skeletons\services
 */
class ServicesResponse
{
    /**
     * @var \app\modules\soap\v2\skeletons\services\ServicesTypeList list {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $ServicesTypeList;
}
