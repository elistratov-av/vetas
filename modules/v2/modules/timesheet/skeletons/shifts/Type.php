<?php

namespace app\modules\v2\modules\timesheet\skeletons\shifts;

/**
 * Class Type
 * @package app\modules\v2\modules\timesheet\skeletons\shifts
 */
class Type
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
