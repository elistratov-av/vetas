<?php

namespace app\modules\soap\v2\skeletons\rq;

/**
 * Class OrgSpecListByDateRequest
 * @package app\modules\soap\v2\skeletons\rq
 */
class OrgSpecListByDateRequest extends OrgSpecListRequest
{
    /**
     * @var \app\modules\soap\v2\skeletons\types\DateHoursType {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $DateHours = null;
}
