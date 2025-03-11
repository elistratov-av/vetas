<?php

namespace app\modules\soap\v2\skeletons\orgs;

/**
 * Class OrgSpecialist
 * @package app\modules\soap\v2\skeletons\orgs
 */
class OrgSpecialist
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
}
