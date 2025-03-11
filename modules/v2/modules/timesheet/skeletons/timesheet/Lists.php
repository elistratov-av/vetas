<?php

namespace app\modules\v2\modules\timesheet\skeletons\timesheet;

/**
 * Class Lists
 * @package app\modules\v2\modules\timesheet\skeletons\timesheet
 */
class Lists
{
    public $pages_count;
    public $total_count;
    public $specialists;
    public $timesheet;


    /**
     * Lists constructor.
     *
     * @param array     $timeSheet
     * @param array     $specialists
     * @param int       $totalCount
     */
    public function __construct(array $timeSheet, array $specialists, int $totalCount = 0)
    {
        $this->total_count = $totalCount;
        $this->timesheet = $timeSheet;
        $this->specialists = $specialists;
    }

    /**
     * Пагинация
     *
     * @param int $page
     * @param int $limit
     */
    public function customPagination(int $page, int $limit): void
    {
        $requestCount = ($page - 1) * $limit;

        if ($this->total_count <= $limit && $requestCount <= $this->total_count) {
            $this->pages_count = 1;
        } else {
            $this->pages_count = ceil($this->total_count / $limit);
        }
    }
}
