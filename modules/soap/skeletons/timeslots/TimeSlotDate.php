<?php


namespace app\modules\soap\skeletons\timeslots;


class TimeSlotDate
{
    /**
     * @var string value; {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $value;

    /**
     * @var \app\modules\soap\skeletons\timeslots\TimeSlotSlot {minOccurs=0, maxOccurs=1}
     * @soap
     */
    public $slot_list;
}
