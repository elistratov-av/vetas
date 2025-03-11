<?php

namespace app\modules\soap\v2\skeletons\rq;

/**
 * Class OrgList
 * @package app\modules\soap\v2\skeletons\rq
 */
class OrgList
{
    /**
     * @var \app\modules\soap\v2\skeletons\rq\Org[] list {minOccurs=0, maxOccurs=unbounded}
     * @soap
     */
    public $Org;
}
