<?php

namespace app\modules\v2\modules\visit\skeletons\visit;

/**
 * Class Lists
 * @package app\modules\v2\modules\visit\skeletons\visit
 */
class Lists
{
    public $pages_count;
    public $total_count;
    public $visits;

    /**
     * Lists constructor.
     * @param array $visits
     * @param int $totalCount
     */
    public function __construct(array $visits, int $totalCount)
    {
        $this->total_count = $totalCount;
        $this->visits = $visits;
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

        if ($this->total_count == 0) {
            $this->pages_count = 0;
            return;
        }

        if ($this->total_count <= $limit && $requestCount < $this->total_count) {
            $this->pages_count = 1;
        } else {
            $this->visits = \array_slice($this->visits, $requestCount, $limit);
            $this->pages_count = ceil($this->total_count / $limit);
        }
    }

    /**
     * @param int $limit
     */
    public function countPages(int $limit): void
    {
        $this->pages_count = ($this->total_count == 0) ? 0 : (int)ceil($this->total_count / $limit);
    }
}
