<?php

namespace app\modules\v2\modules\specialist\skeletons\specialist;

/**
 * Class Specialist
 * @package app\modules\v2\modules\specialist\skeletons\specialist
 */
class Specialist
{
    public $specialist;
    public $time_slots;

    /**
     * Specialist constructor.
     * @param array $specialist
     * @param array $timeSlots
     */
    public function __construct(array $specialist, array $timeSlots)
    {
        $this->specialist = $specialist;
        $this->time_slots = $timeSlots;
    }

}
