<?php

namespace app\modules\soap\skeletons\orgs;


class SpecializationList
{
    /**
     * @var \app\modules\soap\skeletons\orgs\Specialization[] Specializations {minOccurs=0, maxOccurs=unbounded}
     * @soap
     */
    public $specialization;
}
