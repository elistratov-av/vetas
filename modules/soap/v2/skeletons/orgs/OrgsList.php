<?php

namespace app\modules\soap\v2\skeletons\orgs;

/**
 * Class OrgsList
 * @package app\modules\soap\v2\skeletons\orgs
 */
class OrgsList
{
    /**
     * @var \app\modules\soap\v2\skeletons\orgs\OrgOrg[] list {minOccurs=0, maxOccurs=unbounded}
     * @soap
     */
    public $Orgs;
}
