<?php

namespace app\modules\soap\v2\skeletons\timeslots;

/**
 * Class TimeSlotOrg
 * @package app\modules\soap\v2\skeletons\timeslots
 */
class TimeSlotOrg
{
    /**
     * @var integer duration {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $OrgId;

    /**
     * @var \app\modules\soap\v2\skeletons\timeslots\TimeSlotDates[] {minOccurs=0, maxOccurs=unbounded}
     * @soap
     */
    public $Dates;
}
