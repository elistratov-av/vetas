<?php


namespace app\modules\soap\skeletons\timeslots;


class TimeSlotRQ
{
    /**
     * @var \app\modules\soap\skeletons\timeslots\TimeSlotRQServicesIdType[] {minOccurs=1, maxOccurs=unbounded}
     * @soap
     */
    public $services;

    /**
     * @var \app\modules\soap\skeletons\timeslots\TimeSlotRQSpecialistIdType {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $specialist;

    /**
     * @var integer {minOccurs=0, maxOccurs=1}
     * @soap
     */
    public $org_id;

    /**
     * @var \app\modules\soap\skeletons\timeslots\TimeSlotRQDateHoursType list {minOccurs=0, maxOccurs=1}
     * @soap
     */
    public $date_hours;

    /**
     * @var boolean
     * @soap
     */
    public $call_to_home;

}
