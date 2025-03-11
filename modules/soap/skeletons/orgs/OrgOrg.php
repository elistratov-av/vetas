<?php

namespace app\modules\soap\skeletons\orgs;



class OrgOrg
{
    /**
     * @var integer id {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $id;

    /**
     * @var string Name {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $name;

    /**
     * @var string Type {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $type;

    /**
     * @var \app\modules\soap\skeletons\orgs\Address Address {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $address;

    /**
     * @var \app\modules\soap\skeletons\orgs\OrgSpecialistList Specialists {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $specialists;
}
