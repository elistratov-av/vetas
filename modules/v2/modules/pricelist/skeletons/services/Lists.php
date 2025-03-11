<?php

namespace app\modules\v2\modules\pricelist\skeletons\services;

use app\models\db\GovServices;
use app\modules\v2\common\skeletons\CommonList;

class Lists extends CommonList
{
    /**
     * Lists constructor.
     * @param string $attribute_name
     * @param GovServices[] $elements
     * @param int $totalCount
     * @param int $page
     * @param int $limit
     */
    public function __construct(string $attribute_name, array $elements, int $totalCount = 0, int $page, int $limit)
    {
        $list = [];
        foreach ($elements as $element) {
            $list[] = [
                'id' => $element->id,
                'code' => $element->cod,
                'name' => $element->name,
                'price' => $element->price,
                'duration' => $element->duration,
                'cooldown' => $element->cooldown,
                'service_measure' => ($element->serviceMeasures) ? $element->serviceMeasures->name : '',
                'service_type' => $element->serviceType->name
            ];
        }
        parent::__construct($attribute_name, $list, $totalCount, $page, $limit);
    }
}
