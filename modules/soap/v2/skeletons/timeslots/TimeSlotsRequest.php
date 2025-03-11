<?php

namespace app\modules\soap\v2\skeletons\timeslots;

/**
 * Class TimeSlotsRequest
 * @package app\modules\soap\v2\skeletons\timeslots
 */
class TimeSlotsRequest
{
    /**
     * @var \app\modules\soap\v2\skeletons\timeslots\TimeSlotRQServicesIdType[] {minOccurs=1, maxOccurs=unbounded}
     * @soap
     */
    public $Services;
    /**
     * @var \app\modules\soap\v2\skeletons\timeslots\TimeSlotRQSpecialistIdType {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $Specialist;
    /**
     * @var integer {minOccurs=0, maxOccurs=1}
     * @soap
     */
    public $OrgId = null;
    /**
     * @var \app\modules\soap\v2\skeletons\timeslots\TimeSlotRQDateHoursType {minOccurs=0, maxOccurs=1}
     * @soap
     */
    public $DateHours = null;
    /**
     * @var boolean {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $CallToHome = false;
}
