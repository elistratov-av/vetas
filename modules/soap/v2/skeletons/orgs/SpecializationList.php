<?php

namespace app\modules\soap\v2\skeletons\orgs;

/**
 * Class SpecializationList
 * @package app\modules\soap\v2\skeletons\orgs
 */
class SpecializationList
{
    /**
     * @var \app\modules\soap\v2\skeletons\orgs\Specialization[] Specializations {minOccurs=0, maxOccurs=unbounded}
     * @soap
     */
    public $specialization;
}
