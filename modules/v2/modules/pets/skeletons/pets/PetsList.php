<?php


namespace app\modules\v2\modules\pets\skeletons\pets;


class PetsList
{
    public $pages_count;
    public $total_count;
    public $pets;

    /**
     * SpecialistList constructor.
     * @param array $pets
     * @param int $totalCount
     */
    public function __construct(array $pets, int $totalCount = 0)
    {
        $this->total_count = $totalCount;
        $this->pets = $pets;
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
