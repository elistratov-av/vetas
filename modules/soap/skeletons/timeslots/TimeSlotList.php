<?php

namespace app\modules\soap\skeletons\timeslots;

class TimeSlotList
{
    /**
     * @var integer duration {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $duration;

    /**
     * @var \app\modules\soap\skeletons\timeslots\TimeSlotOrg[] {minOccurs=0, maxOccurs=unbounded}
     * @soap
     */
    public $org;
}
