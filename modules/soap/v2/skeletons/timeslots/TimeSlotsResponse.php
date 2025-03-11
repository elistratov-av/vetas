<?php

namespace app\modules\soap\v2\skeletons\timeslots;

/**
 * Class TimeSlotsResponse
 * @package app\modules\soap\v2\skeletons\timeslots
 */
class TimeSlotsResponse
{
    /**
     * @var \app\modules\soap\v2\skeletons\timeslots\TimeSlotList {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $TimeSlotList;
}
