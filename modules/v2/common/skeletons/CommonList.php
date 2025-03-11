<?php


namespace app\modules\v2\common\skeletons;


class CommonList implements \JsonSerializable
{
    private $pages_count;
    private $total_count;
    private $attribute_name;
    private $elements;
    private $extra;

    /**
     * list constructor.
     *
     * @param string $attribute_name Имя аттрибута для переданных элементов
     * @param array $elements        Массив элементов
     * @param int $totalCount
     * @param int $page
     * @param int $limit
     */
    public function __construct(string $attribute_name, array $elements, int $totalCount = 0, int $page, int $limit, array $extra = [])
    {
        $this->attribute_name = $attribute_name;
        $this->total_count = $totalCount;
        $this->elements = $elements;
        $this->extra = $extra;

        // Пагинация
        $requestCount = ($page - 1) * $limit;

        if ($this->total_count <= $limit && $requestCount <= $this->total_count) {
            $this->pages_count = 1;
        } else {
            $this->pages_count = ceil($this->total_count / $limit);
        }
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        $serialize = [
            'pages_count' => $this->pages_count,
            'total_count' => $this->total_count,
            $this->attribute_name => $this->elements
        ];
        if (count($this->extra) > 0) {
            foreach($this->extra as $key => $value) {
                $serialize[$key] = $value;
            }
        }
        return $serialize;
    }
}
