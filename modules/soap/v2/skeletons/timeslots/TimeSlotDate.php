<?php

namespace app\modules\soap\v2\skeletons\timeslots;

/**
 * Class TimeSlotDate
 * @package app\modules\soap\v2\skeletons\timeslots
 */
class TimeSlotDate
{
    /**
     * @var string value; {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $Value;

    /**
     * @var \app\modules\soap\v2\skeletons\timeslots\TimeSlotSlot {minOccurs=0, maxOccurs=1}
     * @soap
     */
    public $SlotList;
}
