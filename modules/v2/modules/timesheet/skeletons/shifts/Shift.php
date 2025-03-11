<?php

namespace app\modules\v2\modules\timesheet\skeletons\shifts;

/**
 * Class Shift
 * @package app\modules\v2\modules\timesheet\skeletons\shifts
 */
class Shift
{
    public $result;

    /**
     * Type constructor.
     * @param $result
     */
    public function __construct($result)
    {
        $this->result = $result;
    }
}
