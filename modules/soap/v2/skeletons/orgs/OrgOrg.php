<?php

namespace app\modules\soap\v2\skeletons\orgs;

/**
 * Class OrgOrg
 * @package app\modules\soap\v2\skeletons\orgs
 */
class OrgOrg
{
    /**
     * @var integer Id {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $Id;

    /**
     * @var string Name {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $Name;

    /**
     * @var string Type {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $Type;

    /**
     * @var \app\modules\soap\v2\skeletons\orgs\OrgAddress Address {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $Address;

    /**
     * @var \app\modules\soap\v2\skeletons\orgs\OrgSpecialistList Specialists {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $Specialists;
}
