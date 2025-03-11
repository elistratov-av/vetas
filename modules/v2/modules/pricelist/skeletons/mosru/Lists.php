<?php

namespace app\modules\v2\modules\pricelist\skeletons\mosru;

/**
 * Class Lists
 * @package app\modules\v2\modules\pricelist\skeletons\mosru
 */
class Lists
{
    /**
     * @var array
     */
    public $mosru_services = [];

    /**
     * Lists constructor.
     * @param array $services
     */
    public function __construct(array $services)
    {
        if (empty($services)) {
            return;
        }

        foreach ($services as $service) {
            $key = (int)$service['id_service_type'];
            if (!isset($this->mosru_services[$key])) {
                $this->mosru_services[$key] = [
                    'id' => $key,
                    'type' => $service['type_name'],
                    'list' => []
                ];
            }

            $this->mosru_services[$key]['list'][] = [
                'id' => $service['id'],
                'name' => $service['name'],
                'at_home' => $service['at_home'],
                'provided' => $service['provided']
            ];
        }

        $this->mosru_services = array_values($this->mosru_services);
    }
}
