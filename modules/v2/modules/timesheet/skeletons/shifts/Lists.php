<?php

namespace app\modules\v2\modules\timesheet\skeletons\shifts;

/**
 * Class Lists
 * @package app\modules\v2\modules\timesheet\skeletons\shifts
 */
class Lists
{
    public $pages_count;
    public $total_count;
    public $shifts;

    /**
     * Lists constructor.
     * @param int $count
     * @param array $shifts
     * @param int $totalCount
     */
    public function __construct(int $count, array $shifts, int $totalCount)
    {
        $this->pages_count = $count;
        $this->total_count = $totalCount;
        $this->shifts = $shifts;
    }
}
