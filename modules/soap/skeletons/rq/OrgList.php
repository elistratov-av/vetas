<?php

namespace app\modules\soap\skeletons\rq;

class OrgList
{
    /**
     * @var \app\modules\soap\skeletons\rq\Org[] list {minOccurs=0, maxOccurs=unbounded}
     * @soap
     */
    public $org;
}
