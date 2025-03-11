<?php

namespace app\modules\soap\skeletons\timeslots;

class TimeSlotOrg
{
    /**
     * @var integer duration {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $org_id;

    /**
     * @var \app\modules\soap\skeletons\timeslots\TimeSlotDates[] {minOccurs=0, maxOccurs=unbounded}
     * @soap
     */
    public $dates;
}
