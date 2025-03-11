<?php

namespace  app\modules\v2\modules\visit\skeletons\specializations;

/**
 * Class Lists
 * @package app\modules\v2\modules\visit\skeletons\specializations
 */
class Lists
{
    public $pages_count;
    public $total_count;
    public $specializations;


    /**
     * Lists constructor.
     *
     * @param array $specializations
     */
    public function __construct(array $specializations)
    {
        $this->pages_count = 0;
        $this->total_count = 0;
        $this->specializations = $specializations;
    }

    /**
     * Пагинация
     *
     * @param int $page
     * @param int $limit
     */
    public function customPagination(int $page, int $limit): void
    {
        $count = \count($this->specializations);
        $requestCount = ($page - 1) * $limit;
        $this->total_count = $count;
        if ($count <= $limit && $requestCount <= $count) {
            $this->pages_count = 1;
        }
        else {
            $this->specializations = \array_slice($this->specializations, $requestCount, $limit);
            $this->pages_count = ceil($count / $limit);
        }
    }
}
