<?php


namespace app\modules\soap\skeletons\orgs;


class OrgsResponse
{
    /**
     * @var \app\modules\soap\skeletons\orgs\OrgsList list {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $orgs_list;
}
