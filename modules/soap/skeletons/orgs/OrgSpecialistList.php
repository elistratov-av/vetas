<?php

namespace app\modules\soap\skeletons\orgs;


class OrgSpecialistList
{
    /**
     * @var \app\modules\soap\skeletons\orgs\OrgSpecialist[] Specialists {minOccurs=0, maxOccurs=unbounded}
     * @soap
     */
    public $specialist;
}
