<?php

namespace app\modules\soap\v2\skeletons\orgs;

/**
 * Class OrgsResponse
 * @package app\modules\soap\v2\skeletons\orgs
 */
class OrgsResponse
{
    /**
     * @var \app\modules\soap\v2\skeletons\orgs\OrgsList list {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $OrgsList;
}
