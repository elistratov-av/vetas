<?php

namespace app\modules\v2\modules\specialist\skeletons\specialist;

/**
 * Class SpecialistList
 * @package app\modules\v2\modules\specialist\skeletons\specialist
 */
class SpecialistList
{
    public $pages_count;
    public $total_count;
    public $specialists;

    /**
     * SpecialistList constructor.
     * @param array $specialists
     * @param int $totalCount
     */
    public function __construct(array $specialists, int $totalCount = 0)
    {
        $this->total_count = $totalCount;
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
