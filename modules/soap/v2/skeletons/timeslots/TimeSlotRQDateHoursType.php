<?php

namespace app\modules\soap\v2\skeletons\timeslots;

/**
 * Class TimeSlotRQDateHoursType
 * @package app\modules\soap\v2\skeletons\timeslots
 */
class TimeSlotRQDateHoursType
{
    /**
     * @var date {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $Date;

    /**
     * @var time {nillable=1, minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $HoursSince;

    /**
     * @var time {nillable=1, minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $HoursTill;
}
