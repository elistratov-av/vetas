<?php

namespace app\modules\soap\skeletons\timeslots;

class TimeSlotsResponse
{
    /**
     * @var \app\modules\soap\skeletons\timeslots\TimeSlotList {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $get_time_slot_list;
}
