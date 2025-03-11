<?php

namespace app\modules\soap\v2\skeletons\orgs;

/**
 * Class OrgSpecialistList
 * @package app\modules\soap\v2\skeletons\orgs
 */
class OrgSpecialistList
{
    /**
     * @var \app\modules\soap\v2\skeletons\orgs\OrgSpecialist[] Specialists {minOccurs=0, maxOccurs=unbounded}
     * @soap
     */
    public $Specialist;
}
