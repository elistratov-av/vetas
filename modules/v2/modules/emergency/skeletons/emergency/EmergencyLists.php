<?php

namespace app\modules\v2\modules\emergency\skeletons\emergency;

/**
 * Class EmergencyLists
 * @package app\modules\v2\modules\emergency\skeletons\emergency
 */
class EmergencyLists
{
    public $pages_count;
    public $total_count;
    public $count_live_queue_visits;
    public $count_not_live_queue_visits;
    public $visits;

    /**
     * EmergencyLists constructor.
     * @param array $visits
     * @param int $totalCount
     * @param int $countLiveQueueVisits
     * @param int $countNotLiveQueueVisits
     */
    public function __construct(array $visits, int $totalCount, int $countLiveQueueVisits, int $countNotLiveQueueVisits)
    {
        $this->total_count = $totalCount;
        $this->visits = $visits;
        $this->count_live_queue_visits = $countLiveQueueVisits;
        $this->count_not_live_queue_visits = $countNotLiveQueueVisits;
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

        if ($this->total_count <= $limit && $requestCount < $this->total_count) {
            $this->pages_count = 1;
        } else {
            $this->pages_count = ceil($this->total_count / $limit);
        }
    }
}
