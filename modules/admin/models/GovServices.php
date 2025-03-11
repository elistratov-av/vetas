<?php

namespace app\modules\admin\models;

/**
 * Class GovServices
 * @package app\modules\admin\models
 */
class GovServices extends \app\models\db\GovServices
{
    public function rules()
    {
        return [
            [[
                'name',
                'price',
                'sort_by',
                'id_service_type',
                'duration',
                'cooldown',
                'id_service_goal',
                'at_home',
                'type',
                'id_pricelist',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'id_service_measure',
                'alternative_name',
                'cod'
            ], 'safe'],
        ];
    }
}
