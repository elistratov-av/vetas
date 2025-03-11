<?php

namespace app\modules\v2\modules\petOwners\skeletons\petOwners;


class PetOwnersList
{
    public $pages_count;
    public $total_count;
    public $pet_owners;

    /**
     * SpecialistList constructor.
     * @param array $pet_owners
     * @param int $totalCount
     */
    public function __construct(array $pet_owners, int $totalCount = 0)
    {
        $this->total_count = $totalCount;
        $this->pet_owners = $pet_owners;
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
