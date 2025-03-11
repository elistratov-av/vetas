<?php

namespace app\modules\soap\v2\skeletons\timeslots;

/**
 * Class TimeSlotList
 * @package app\modules\soap\v2\skeletons\timeslots
 */
class TimeSlotList
{
    /**
     * @var integer duration {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $Duration;

    /**
     * @var \app\modules\soap\v2\skeletons\timeslots\TimeSlotOrg[] {minOccurs=0, maxOccurs=unbounded}
     * @soap
     */
    public $Org;
}
