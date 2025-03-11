<?php

namespace app\modules\soap\skeletons\rq;

class GetOrgSpecList
{
    /**
     * @var \app\modules\soap\skeletons\rq\OrgList list {minOccurs=0, maxOccurs=unbounded}
     * @soap
     */
    public $org_spec_list;

}
